<?php

namespace App\Services;

use App\Http\Controllers\CadenceController;
use App\Jobs\SendWhatsappMessageJob;
use App\Models\Activity;
use App\Models\CadenceStep;
use App\Models\Company;
use App\Models\CompanyCadence;
use App\Models\EmailAccount;
use App\Models\Lead;
use App\Models\Message;
use App\Models\PipelineStage;
use App\Models\Task;
use App\Models\WhatsappInstance;
use Exception;
use Illuminate\Support\Facades\Log;

class CadenceExecutionService
{
    public function __construct(
        protected WhatsappClientService $whatsappClient,
        protected GmailOAuthService $gmailService
    ) {}

    /**
     * Processa todas as cadências ativas que possuem passos/tarefas prontos para execução.
     */
    public function processPendingCadences(bool $dryRun = false): array
    {
        $now = now();

        // Busca tarefas de cadência pendentes cujo prazo já venceu ou vence agora
        $pendingTasks = Task::withoutGlobalScopes()
            ->whereNotNull('company_cadence_id')
            ->where('status', 'pending')
            ->where('due_at', '<=', $now)
            ->with(['company', 'lead', 'cadence', 'cadenceStep', 'companyCadence'])
            ->get();

        $executed = [];
        $skipped = [];
        $errors = [];

        foreach ($pendingTasks as $task) {
            $enrollment = $task->companyCadence;

            // Se a matrícula da empresa não estiver ativa (ex: pausada ou o lead já respondeu), ignora
            if (!$enrollment || $enrollment->status !== 'active') {
                $skipped[] = [
                    'task_id' => $task->id,
                    'reason' => 'Enrollment status is ' . ($enrollment?->status ?? 'missing'),
                ];
                continue;
            }

            if ($dryRun) {
                $executed[] = [
                    'task_id' => $task->id,
                    'type' => $task->type,
                    'company' => $task->company?->name,
                    'dry_run' => true,
                ];
                continue;
            }

            try {
                $success = $this->executeTask($task);

                if ($success) {
                    $executed[] = [
                        'task_id' => $task->id,
                        'type' => $task->type,
                        'company' => $task->company?->name,
                        'lead' => $task->lead?->name,
                        'title' => $task->title,
                    ];
                } else {
                    $skipped[] = [
                        'task_id' => $task->id,
                        'reason' => 'Manual action required (waiting for SDR)',
                    ];
                }
            } catch (Exception $e) {
                Log::error("[CadenceEngine] Falha ao executar tarefa {$task->id}: " . $e->getMessage(), [
                    'task_id' => $task->id,
                    'exception' => $e,
                ]);

                $errors[] = [
                    'task_id' => $task->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'total_pending' => $pendingTasks->count(),
            'executed' => $executed,
            'skipped' => $skipped,
            'errors' => $errors,
            'processed_at' => $now->toDateTimeString(),
        ];
    }

    /**
     * Executa uma tarefa específica de cadência com base no canal configurado.
     */
    public function executeTask(Task $task): bool
    {
        $type = strtolower($task->type ?? 'other');
        $enrollment = $task->companyCadence;
        $company = $task->company;
        $lead = $task->lead ?? $company?->contacts()->first();

        switch ($type) {
            case 'whatsapp':
                $this->executeWhatsappStep($task, $company, $lead);
                break;

            case 'email':
                $this->executeEmailStep($task, $company, $lead);
                break;

            default:
                // Passos manuais (call, meeting, followup, research) não são auto-completados;
                // eles permanecem pendentes para o SDR atuar.
                return false;
        }

        // Marca a tarefa como concluída
        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Avança a matrícula para o próximo passo da régua se todos os contatos do passo atual foram concluídos
        if ($enrollment && $enrollment->status === 'active') {
            $hasPendingSameStep = Task::where('company_cadence_id', $enrollment->id)
                ->where('cadence_step_id', $task->cadence_step_id)
                ->where('status', 'pending')
                ->exists();

            if (! $hasPendingSameStep) {
                CadenceController::advanceNextStep($enrollment);
            }
        }

        return true;
    }

    protected function executeWhatsappStep(Task $task, ?Company $company, ?Lead $lead): void
    {
        $tenantId = $task->tenant_id;
        $messageBody = $task->description ?: $task->title;

        // Procura instância do WhatsApp ativa no tenant
        $instance = WhatsappInstance::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('status', 'connected')
            ->first() ?? WhatsappInstance::withoutGlobalScopes()->where('tenant_id', $tenantId)->first();

        $phone = $lead?->phone ?? $company?->phone;

        if ($instance && $phone) {
            if ($lead) {
                // Em vez de envio síncrono, delegamos para a fila (onde Spintax, limites e chamadas HTTP acontecem sem travar)
                \App\Jobs\SendWhatsappMessageJob::dispatch($lead, $instance, $messageBody);
            } else {
                Log::warning("[CadenceEngine] Tarefa #{$task->id} ignorada. Envio de WhatsApp exige um Lead vinculado.");
            }
        } else {
             Log::warning("[CadenceEngine] Disparo ignorado para tarefa #{$task->id}: Instância ou telefone ausente.");
        }

        // Registra atividade na timeline de que o envio foi engatilhado pelo motor
        Activity::withoutGlobalScopes()->create([
            'tenant_id' => $tenantId,
            'company_id' => $company?->id,
            'lead_id' => $lead?->id,
            'user_id' => $task->user_id,
            'type' => 'whatsapp',
            'description' => "🤖 [Motor Automático] Disparo de WhatsApp enfileirado para " . ($lead?->name ?? 'decisor') . ": \"{$task->title}\"",
        ]);
    }

    /**
     * Dispara envio automático de E-mail.
     */
    protected function executeEmailStep(Task $task, ?Company $company, ?Lead $lead): void
    {
        $tenantId = $task->tenant_id;
        $toEmail = $lead?->email ?? $company?->email;
        $subject = $task->title;
        $body = $task->description ?: "Olá,\n\nEntrando em contato sobre sua empresa.";

        // Procura conta de e-mail conectada
        $emailAccount = EmailAccount::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('status', 'connected')
            ->first();

        if ($emailAccount && $toEmail) {
            try {
                $this->gmailService->sendEmail($emailAccount, $toEmail, $subject, nl2br($body));
            } catch (Exception $e) {
                Log::warning("[CadenceEngine] Envio Gmail API falhou: " . $e->getMessage());
            }
        }

        // Registra atividade na timeline
        Activity::withoutGlobalScopes()->create([
            'tenant_id' => $tenantId,
            'company_id' => $company?->id,
            'lead_id' => $lead?->id,
            'user_id' => $task->user_id,
            'type' => 'email',
            'description' => "🤖 [Motor Automático] Disparo de E-mail enviado para " . ($toEmail ?: ($lead?->name ?? 'lead')) . ": \"{$task->title}\"",
        ]);
    }

    /**
     * Interrompe a cadência ativa e cancela tarefas pendentes quando o lead responde.
     */
    public function handleLeadReply(Lead $lead, string $source = 'whatsapp', ?string $content = null): array
    {
        $companyId = $lead->company_id;
        $tenantId = $lead->tenant_id;

        $enrollment = null;

        if ($companyId) {
            $enrollment = CompanyCadence::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('status', 'active')
                ->first();
        }

        $stoppedCadenceName = null;

        if ($enrollment) {
            $stoppedCadenceName = $enrollment->cadence?->name ?? 'Cadência Ativa';

            // Marca matrícula como 'replied' para indicar que o lead engajou
            $enrollment->update([
                'status' => 'replied',
                'completed_at' => now(),
            ]);

            // Cancela tarefas pendentes automáticas para não mandar mensagens frias repetidas
            Task::withoutGlobalScopes()
                ->where('company_cadence_id', $enrollment->id)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            // Registra atividade de sucesso
            Activity::withoutGlobalScopes()->create([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'lead_id' => $lead->id,
                'type' => 'note',
                'description' => "🎯 Lead respondeu via {$source}! Cadência '{$stoppedCadenceName}' pausada automaticamente para atendimento humanizado.",
            ]);
        }

        // Avança Lead e Empresa para a coluna de "Respondeu" ou "Em Conversa" no Kanban
        $targetStage = PipelineStage::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where(function ($query) {
                $query->where('name', 'like', '%Respondeu%')
                    ->orWhere('name', 'like', '%Conversa%')
                    ->orWhere('name', 'like', '%Qualifica%')
                    ->orWhere('position', 3);
            })
            ->first();

        if ($targetStage) {
            $lead->update(['pipeline_stage_id' => $targetStage->id]);

            if ($companyId) {
                Company::withoutGlobalScopes()
                    ->where('id', $companyId)
                    ->update(['pipeline_stage_id' => $targetStage->id]);
            }
        }

        return [
            'status' => 'success',
            'stopped' => (bool) $enrollment,
            'cadence_name' => $stoppedCadenceName,
            'stage_name' => $targetStage?->name,
            'lead_id' => $lead->id,
            'company_id' => $companyId,
        ];
    }
}
