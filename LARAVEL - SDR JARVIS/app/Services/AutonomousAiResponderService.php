<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Lead;
use App\Models\Message;
use App\Models\PipelineStage;
use App\Models\Tenant;
use App\Models\WhatsappInstance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutonomousAiResponderService
{
    public function __construct(
        protected WhatsappClientService $whatsappClient,
    ) {}

    /**
     * Processa uma mensagem de entrada do WhatsApp e responde autonomamente se as regras permitirem.
     */
    public function processInbound(Lead $lead, WhatsappInstance $instance, string $inboundText): array
    {
        $tenant = $lead->tenant ?? Tenant::find($lead->tenant_id);

        if (! $tenant) {
            return ['status' => 'error', 'reason' => 'tenant_not_found'];
        }

        $aiConfig = $tenant->getAiConfig();

        // 1. Verifica se o Piloto Automático está habilitado para o Tenant
        if (! ($aiConfig['ai_autonomous_enabled'] ?? false)) {
            Log::info("[Autonomous AI] Piloto Automático desativado para o tenant {$tenant->id}. Ignorando.");
            return ['status' => 'disabled', 'reason' => 'autonomous_mode_off'];
        }

        // 2. Verifica se a IA está pausada para este lead (atendimento humano assumido)
        if ($lead->isAiAutonomousPaused()) {
            Log::info("[Autonomous AI] IA pausada para o lead #{$lead->id} ({$lead->phone}). Atendimento humano em andamento.");
            return ['status' => 'skipped', 'reason' => 'lead_paused_for_human'];
        }

        // 3. Verifica horário comercial se a opção estiver ativada (Horário de Brasília)
        if ($aiConfig['autonomous_working_hours_only'] ?? false) {
            $tz = config('app.timezone') === 'UTC' ? 'America/Sao_Paulo' : config('app.timezone');
            $now = now()->setTimezone($tz);
            $startTime = Carbon::createFromTimeString($aiConfig['autonomous_working_hours_start'] ?? '08:00', $tz);
            $endTime = Carbon::createFromTimeString($aiConfig['autonomous_working_hours_end'] ?? '18:30', $tz);

            if (! $now->between($startTime, $endTime)) {
                Log::info("[Autonomous AI] Fora do horário de atendimento configurado ({$now->format('H:i')} em {$tz}). Lead #{$lead->id}.");
                return ['status' => 'skipped', 'reason' => 'outside_working_hours'];
            }
        }

        // 4. Ativa presença "digitando..." (composing) no WhatsApp
        try {
            $this->whatsappClient->sendPresence($instance, $lead->phone, 'composing');
        } catch (\Throwable $e) {
            Log::warning("[Autonomous AI] Falha ao enviar presença composing: " . $e->getMessage());
        }

        // Delay humanizado se configurado (em ambiente síncrono aguarda 1 a 3 segundos de segurança)
        $delaySec = min(3, max(1, (int) ($aiConfig['autonomous_delay_seconds'] ?? 2)));
        if ($delaySec > 0 && ! app()->environment('testing')) {
            sleep($delaySec);
        }

        // 5. Instancia o serviço de IA com as instruções e persona do Tenant
        $aiService = new AiAgentService($tenant);
        $lower = mb_strtolower(trim($inboundText));

        // 6. Verifica Transbordo Humano (Handoff)
        // Se o cliente pedir expressamente para falar com pessoa/humano/atendente
        $humanKeywords = ['falar com atendente', 'falar com humano', 'falar com uma pessoa', 'pessoa real', 'atendente humano', 'falar com vendedor', 'humano por favor'];
        $wantsHuman = false;
        foreach ($humanKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                $wantsHuman = true;
                break;
            }
        }

        if ($wantsHuman || (($aiConfig['autonomous_handoff_on_stress'] ?? true) && $this->isSevereStress($lower))) {
            return $this->triggerHumanHandoff($lead, $instance, $tenant, $inboundText);
        }

        // 7. Monta histórico recente das últimas 6 mensagens trocadas
        $history = [];
        $recentMessages = $lead->messages()->orderBy('created_at', 'desc')->take(6)->get()->reverse();

        foreach ($recentMessages as $msg) {
            $history[] = [
                'role' => $msg->direction === 'outbound' ? 'agent' : 'user',
                'content' => $msg->body,
            ];
        }

        // 8. Gera a resposta da IA respeitando persona, guardrails, FAQ e proposta de valor
        $simulationResult = $aiService->simulateConversation($history, $inboundText, [
            'ai_name' => $aiConfig['ai_name'],
            'ai_persona' => $aiConfig['ai_persona'],
            'company_value_prop' => $aiConfig['company_value_prop'],
            'instructions' => $aiConfig['instructions'],
            'guardrails' => $aiConfig['guardrails'],
            'faq' => $aiConfig['faq'] ?? [],
            'tone' => $aiConfig['tone'] ?? 'consultative',
        ]);

        $reply = $simulationResult['reply'] ?? null;

        if (empty($reply)) {
            Log::warning("[Autonomous AI] Resposta vazia gerada para o lead #{$lead->id}.");
            return ['status' => 'error', 'reason' => 'empty_reply'];
        }

        // 9. Envia a resposta pelo WhatsApp
        try {
            $sendResult = $this->whatsappClient->sendTextMessage($instance, $lead->phone, $reply);
            $externalId = $sendResult['key']['id'] ?? ('AUTO_' . strtoupper(bin2hex(random_bytes(6))));

            // Registra a mensagem outbound no histórico
            $createdMsg = Message::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'lead_id' => $lead->id,
                'whatsapp_instance_id' => $instance->id,
                'direction' => 'outbound',
                'body' => $reply,
                'status' => 'sent',
                'external_id' => $externalId,
                'sent_at' => now(),
            ]);

            Log::info("[Autonomous AI] Resposta autônoma enviada com sucesso para {$lead->phone}.", [
                'lead_id' => $lead->id,
                'reply' => $reply,
            ]);
        } catch (\Throwable $e) {
            Log::error("[Autonomous AI] Erro ao enviar mensagem WhatsApp: " . $e->getMessage(), [
                'lead_id' => $lead->id,
                'phone' => $lead->phone,
            ]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }

        // 10. Ações inteligentes de CRM (Avanço automático de etapa ou desqualificação)
        $this->handleAutonomousCrmActions($lead, $tenant, $inboundText, $aiConfig, $simulationResult['xray'] ?? []);

        return [
            'status' => 'replied',
            'lead_id' => $lead->id,
            'reply' => $reply,
            'xray' => $simulationResult['xray'] ?? [],
        ];
    }

    /**
     * Dispara o transbordo para atendimento humano (pausa o robô e notifica o SDR).
     */
    protected function triggerHumanHandoff(Lead $lead, WhatsappInstance $instance, Tenant $tenant, string $triggerText): array
    {
        // 1. Pausa a IA para o lead
        $lead->pauseAiAutonomous('Solicitação expressa de atendimento humano ou estresse elevado');

        // 2. Registra atividade no CRM
        $this->recordActivity(
            $tenant, 
            $lead, 
            '🚨 Transbordo Humano Acionado (IA Pausada)', 
            "O lead solicitou falar com uma pessoa ou demonstrou estresse crítico (\"{$triggerText}\"). O robô autônomo foi pausado para permitir atendimento humano direto."
        );

        // 3. Envia mensagem suave avisando da transferência
        $handoffMessage = "Com certeza! Estou passando seu atendimento agora mesmo para o nosso especialista humano da equipe para dar continuidade com você. Um instante por favor! 👍";

        try {
            $sendResult = $this->whatsappClient->sendTextMessage($instance, $lead->phone, $handoffMessage);
            $externalId = $sendResult['key']['id'] ?? ('HANDOFF_' . strtoupper(bin2hex(random_bytes(6))));

            Message::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'lead_id' => $lead->id,
                'whatsapp_instance_id' => $instance->id,
                'direction' => 'outbound',
                'body' => $handoffMessage,
                'status' => 'sent',
                'external_id' => $externalId,
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning("[Autonomous AI] Falha ao enviar aviso de handoff: " . $e->getMessage());
        }

        Log::info("[Autonomous AI] Transbordo humano concluído para o lead #{$lead->id}.");

        return [
            'status' => 'handoff_triggered',
            'lead_id' => $lead->id,
            'message' => $handoffMessage,
        ];
    }

    /**
     * Executa ações automáticas de CRM (avanço de etapa no Kanban ou desqualificação).
     */
    protected function handleAutonomousCrmActions(Lead $lead, Tenant $tenant, string $inboundText, array $aiConfig, array $xray): void
    {
        $lower = mb_strtolower($inboundText);

        // Desqualificação Automática se o cliente rejeitar expressamente
        if (($aiConfig['autonomous_auto_disqualify'] ?? true) && (
            str_contains($lower, 'não tenho interesse') ||
            str_contains($lower, 'não quero') ||
            str_contains($lower, 'retire meu número') ||
            str_contains($lower, 'para de me mandar mensagem')
        )) {
            $lead->status = 'disqualified';
            $lead->save();

            $this->recordActivity(
                $tenant,
                $lead,
                '🚫 Lead Desqualificado pelo SDR Autônomo',
                "O lead expressou recusa formal (\"{$inboundText}\"). O SDR JARVIS encerrou o contato e atualizou o status para Desqualificado."
            );

            return;
        }

        // Avanço Automático de Etapa se aceitar reunião ou demonstrar alto interesse
        if ($aiConfig['autonomous_auto_advance'] ?? true) {
            $meetingSignals = ['pode ser', 'vamos marcar', 'quinta às', 'quarta às', 'terça às', 'sexta às', 'segunda às', 'horário bom', 'me liga', 'fechado', 'topo', 'marca aí'];
            $wantsMeeting = false;

            foreach ($meetingSignals as $sig) {
                if (str_contains($lower, $sig)) {
                    $wantsMeeting = true;
                    break;
                }
            }

            if ($wantsMeeting) {
                $targetStage = PipelineStage::withoutGlobalScopes()
                    ->where('tenant_id', $tenant->id)
                    ->where(function ($q) {
                        $q->where('name', 'like', '%Reunião%')
                          ->orWhere('name', 'like', '%Agendada%')
                          ->orWhere('name', 'like', '%Demo%');
                    })
                    ->first();

                if ($targetStage && $lead->pipeline_stage_id !== $targetStage->id) {
                    $lead->pipeline_stage_id = $targetStage->id;
                    $lead->save();

                    // Se o lead tiver uma conta/empresa vinculada, atualiza também a etapa da empresa se aplicável
                    if ($lead->company) {
                        $lead->company->pipeline_stage_id = $targetStage->id;
                        $lead->company->save();
                    }

                    $this->recordActivity(
                        $tenant,
                        $lead,
                        "📅 Avanço Automático para {$targetStage->name}",
                        "O SDR Autônomo identificou confirmação de interesse/agenda (\"{$inboundText}\") e moveu o lead automaticamente no Kanban."
                    );
                }
            }
        }
    }

    /**
     * Registra com segurança uma atividade no CRM garantindo que company_id não seja nulo.
     */
    protected function recordActivity(Tenant $tenant, Lead $lead, string $title, string $description): void
    {
        $companyId = $lead->company_id;
        if (! $companyId) {
            $company = \App\Models\Company::withoutGlobalScopes()->where('tenant_id', $tenant->id)->first();
            $companyId = $company?->id;
        }

        if ($companyId) {
            try {
                Activity::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'company_id' => $companyId,
                    'lead_id' => $lead->id,
                    'type' => 'note',
                    'title' => $title,
                    'description' => $description,
                ]);
            } catch (\Throwable $e) {
                Log::warning("[Autonomous AI] Não foi possível registrar Activity: " . $e->getMessage());
            }
        }
    }

    /**
     * Detecta se a mensagem possui nível de hostilidade/estresse severo.
     */
    protected function isSevereStress(string $lower): bool
    {
        $severeKeywords = ['golpe', 'lixo', 'péssimo', 'ridículo', 'processar', 'polícia', 'procon', 'denunciar', 'odeio', 'fraude'];
        foreach ($severeKeywords as $w) {
            if (str_contains($lower, $w)) {
                return true;
            }
        }
        return false;
    }
}
