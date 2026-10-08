<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Cadence;
use App\Models\CadenceStep;
use App\Models\Company;
use App\Models\CompanyCadence;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CadenceController extends Controller
{
    /**
     * Garante autenticação em dev.
     */
    protected function ensureAuthenticatedUser(): void
    {
        if (! Auth::check()) {
            $user = User::withoutGlobalScopes()->first();
            if ($user) {
                Auth::login($user);
            }
        }
    }

    /**
     * Lista todas as cadências com passos e contagem de matrículas.
     */
    public function index(Request $request): JsonResponse
    {
        $this->ensureAuthenticatedUser();

        $cadences = Cadence::with(['steps'])
            ->withCount(['enrollments', 'activeEnrollments'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'cadences' => $cadences,
        ]);
    }

    /**
     * Cria uma nova cadência personalizada.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->ensureAuthenticatedUser();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_segment' => 'nullable|string',
            'color' => 'nullable|string',
            'steps' => 'required|array|min:1',
            'steps.*.step_number' => 'required|integer',
            'steps.*.day_offset' => 'nullable|integer|min:0',
            'steps.*.delay_value' => 'nullable|integer|min:0',
            'steps.*.delay_unit' => 'nullable|string|in:seconds,minutes,hours,days',
            'steps.*.task_type' => 'required|string',
            'steps.*.title' => 'required|string',
            'steps.*.instructions' => 'nullable|string',
            'steps.*.template_content' => 'nullable|string',
            'steps.*.priority' => 'nullable|string',
        ]);

        $user = Auth::user();
        $tenantId = $user?->tenant_id ?? 1;

        $cadence = Cadence::create([
            'tenant_id' => $tenantId,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'target_segment' => $validated['target_segment'] ?? null,
            'color' => $validated['color'] ?? '#130e30',
            'is_active' => true,
        ]);

        foreach ($validated['steps'] as $stepData) {
            $delayValue = isset($stepData['delay_value']) ? (int)$stepData['delay_value'] : ($stepData['day_offset'] ?? 0);
            $delayUnit = $stepData['delay_unit'] ?? 'days';

            CadenceStep::create([
                'cadence_id' => $cadence->id,
                'step_number' => $stepData['step_number'],
                'day_offset' => $delayValue,
                'delay_value' => $delayValue,
                'delay_unit' => $delayUnit,
                'task_type' => $stepData['task_type'],
                'title' => $stepData['title'],
                'instructions' => $stepData['instructions'] ?? null,
                'template_content' => $stepData['template_content'] ?? null,
                'priority' => $stepData['priority'] ?? 'medium',
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'cadence' => $cadence->load('steps'),
                'message' => 'Cadência criada com sucesso!',
            ]);
        }

        return back()->with('success', 'Cadência criada com sucesso!');
    }

    /**
     * Matrícula uma empresa em uma cadência.
     */
    public function enroll(Request $request, Company $company): RedirectResponse|JsonResponse
    {
        $this->ensureAuthenticatedUser();

        $validated = $request->validate([
            'cadence_id' => 'required|exists:cadences,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $cadence = Cadence::withoutGlobalScopes()->with('steps')->findOrFail($validated['cadence_id']);
        $user = Auth::user();
        $assignedUserId = $validated['user_id'] ?? ($company->assigned_user_id ?? $user?->id);

        // Desativa qualquer cadência ativa prévia para esta empresa
        CompanyCadence::where('company_id', $company->id)
            ->where('status', 'active')
            ->update([
                'status' => 'stopped',
                'completed_at' => now(),
            ]);

        // Cria a matrícula na nova cadência
        $enrollment = CompanyCadence::create([
            'tenant_id' => $company->tenant_id ?? ($user?->tenant_id ?? 1),
            'company_id' => $company->id,
            'cadence_id' => $cadence->id,
            'user_id' => $assignedUserId,
            'current_step_number' => 1,
            'status' => 'active',
            'started_at' => now(),
            'next_due_at' => now(),
        ]);

        // Primeiro passo da cadência
        $firstStep = $cadence->steps->where('step_number', 1)->first() ?? $cadence->steps->first();

        if ($firstStep) {
            $contacts = $company->contacts()->withoutGlobalScopes()->get();
            if ($contacts->isEmpty()) {
                $contacts = collect([null]);
            }

            $dueDate = self::calculateDueDate($firstStep, now());

            foreach ($contacts as $lead) {
                $processedDescription = $this->replaceTemplatePlaceholders($firstStep->template_content, $company, $lead, $user);

                Task::create([
                    'tenant_id' => $enrollment->tenant_id,
                    'company_id' => $company->id,
                    'lead_id' => $lead?->id,
                    'company_cadence_id' => $enrollment->id,
                    'cadence_id' => $cadence->id,
                    'cadence_step_id' => $firstStep->id,
                    'user_id' => $assignedUserId,
                    'title' => "Passo 1: {$firstStep->title}" . ($lead ? " ({$lead->name})" : ''),
                    'description' => $processedDescription ?: $firstStep->instructions,
                    'type' => $firstStep->task_type,
                    'priority' => $firstStep->priority,
                    'due_at' => $dueDate,
                    'status' => 'pending',
                ]);
            }

            $enrollment->update(['next_due_at' => $dueDate]);
        }

        // Registra atividade na timeline
        Activity::create([
            'tenant_id' => $enrollment->tenant_id,
            'company_id' => $company->id,
            'lead_id' => $company->contacts()->first()?->id,
            'user_id' => $user?->id,
            'type' => 'note',
            'description' => "🚀 Conta matriculada na cadência: {$cadence->name} (Passo 1 gerado)",
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Conta matriculada na cadência {$cadence->name}!",
                'enrollment' => $enrollment->fresh(['cadence.steps', 'tasks']),
            ]);
        }

        return back()->with('success', "Conta matriculada na cadência {$cadence->name}!");
    }

    /**
     * Pausa ou cancela a cadência ativa da empresa.
     */
    public function unenroll(Request $request, Company $company): RedirectResponse|JsonResponse
    {
        $this->ensureAuthenticatedUser();

        $activeCadence = CompanyCadence::where('company_id', $company->id)
            ->where('status', 'active')
            ->first();

        if ($activeCadence) {
            $activeCadence->update([
                'status' => 'stopped',
                'completed_at' => now(),
            ]);

            // Cancela tarefas pendentes geradas pela cadência
            Task::where('company_cadence_id', $activeCadence->id)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            Activity::create([
                'tenant_id' => $company->tenant_id,
                'company_id' => $company->id,
                'user_id' => Auth::id(),
                'type' => 'note',
                'description' => "⏸️ Cadência interrompida manualmente para a conta.",
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cadência interrompida com sucesso!',
            ]);
        }

        return back()->with('success', 'Cadência interrompida com sucesso!');
    }

    /**
     * Avança para o próximo passo da cadência.
     */
    public static function advanceNextStep(CompanyCadence $enrollment): void
    {
        $cadence = $enrollment->cadence()->with('steps')->first();
        if (! $cadence) {
            return;
        }

        $nextStepNumber = $enrollment->current_step_number + 1;
        $nextStep = $cadence->steps->where('step_number', $nextStepNumber)->first();

        if (! $nextStep) {
            // Fim da cadência!
            $enrollment->update([
                'status' => 'completed',
                'completed_at' => now(),
                'next_due_at' => null,
            ]);

            Activity::create([
                'tenant_id' => $enrollment->tenant_id,
                'company_id' => $enrollment->company_id,
                'user_id' => $enrollment->user_id,
                'type' => 'note',
                'description' => "🏆 Cadência '{$cadence->name}' concluída com sucesso em todos os passos!",
            ]);

            return;
        }

        $company = $enrollment->company;
        $contacts = $company ? $company->contacts()->withoutGlobalScopes()->get() : collect();
        if ($contacts->isEmpty()) {
            $contacts = collect([null]);
        }

        $user = $enrollment->user;
        $dueDate = self::calculateDueDate($nextStep, now());

        $controller = new self();

        // Cria a tarefa do próximo passo para cada contato da empresa
        foreach ($contacts as $lead) {
            $processedDescription = $controller->replaceTemplatePlaceholders($nextStep->template_content, $company, $lead, $user);

            Task::create([
                'tenant_id' => $enrollment->tenant_id,
                'company_id' => $enrollment->company_id,
                'lead_id' => $lead?->id,
                'company_cadence_id' => $enrollment->id,
                'cadence_id' => $cadence->id,
                'cadence_step_id' => $nextStep->id,
                'user_id' => $enrollment->user_id,
                'title' => "Passo {$nextStep->step_number}: {$nextStep->title}" . ($lead ? " ({$lead->name})" : ''),
                'description' => $processedDescription ?: $nextStep->instructions,
                'type' => $nextStep->task_type,
                'priority' => $nextStep->priority,
                'due_at' => $dueDate,
                'status' => 'pending',
            ]);
        }

        $enrollment->update([
            'current_step_number' => $nextStepNumber,
            'next_due_at' => $dueDate,
        ]);

        $dueLabel = $dueDate->isToday() ? $dueDate->format('H:i') : $dueDate->format('d/m/Y H:i');

        Activity::create([
            'tenant_id' => $enrollment->tenant_id,
            'company_id' => $enrollment->company_id,
            'lead_id' => $lead?->id,
            'user_id' => $enrollment->user_id,
            'type' => 'note',
            'description' => "⚡ Cadência avançou para o Passo {$nextStepNumber}: {$nextStep->title} (Prazo: {$dueLabel})",
        ]);
    }

    /**
     * Calcula a data de vencimento com suporte granular a segundos, minutos, horas e dias.
     */
    public static function calculateDueDate(CadenceStep $step, mixed $from = null): \Illuminate\Support\Carbon
    {
        $carbon = $from ? \Illuminate\Support\Carbon::parse($from)->copy() : now();
        $value = (int) ($step->delay_value ?? $step->day_offset ?? 0);
        $unit = $step->delay_unit ?? 'days';

        if ($value <= 0) {
            return $carbon;
        }

        return match ($unit) {
            'seconds' => $carbon->addSeconds($value),
            'minutes' => $carbon->addMinutes($value),
            'hours' => $carbon->addHours($value),
            default => $carbon->addDays($value),
        };
    }

    /**
     * Substitui variáveis de modelo ({{empresa}}, {{decisor}}, {{cargo}}, {{setor}}, {{sdr}}).
     */
    protected function replaceTemplatePlaceholders(?string $template, ?Company $company, mixed $lead, mixed $user): ?string
    {
        if (! $template) {
            return null;
        }

        $replacements = [
            '{{empresa}}' => $company?->name ?? 'sua empresa',
            '{{decisor}}' => $lead?->name ?? 'decisor',
            '{{setor}}' => $company?->segment ?? 'seu setor',
            '{{sdr}}' => $user?->name ?? 'nossa equipe',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Dispara manualmente o motor de processamento das cadências sob demanda.
     */
    public function processNow(Request $request, \App\Services\CadenceExecutionService $service): JsonResponse
    {
        $this->ensureAuthenticatedUser();

        $result = $service->processPendingCadences();

        return response()->json([
            'success' => true,
            'message' => 'Motor de cadências executado!',
            'result' => $result,
        ]);
    }

    /**
     * Simula a resposta de um lead de uma empresa para validar a pausa automática da cadência.
     */
    public function simulateReply(Request $request, Company $company, \App\Services\CadenceExecutionService $service): JsonResponse
    {
        $this->ensureAuthenticatedUser();

        $lead = $company->contacts()->first();
        if (! $lead) {
            $lead = \App\Models\Lead::create([
                'tenant_id' => $company->tenant_id,
                'company_id' => $company->id,
                'name' => 'Decisor ' . $company->name,
                'status' => 'open',
            ]);
        }

        $result = $service->handleLeadReply($lead, 'simulacao', 'Olá, recebi sua mensagem e gostaria de agendar uma demonstração!');

        return response()->json([
            'success' => true,
            'message' => 'Simulação de resposta processada com sucesso! Cadência pausada e lead movido no Kanban.',
            'result' => $result,
        ]);
    }

    /**
     * Exclui uma cadência e limpa matrículas e tarefas associadas.
     */
    public function destroy(int $cadenceId): RedirectResponse|JsonResponse
    {
        $this->ensureAuthenticatedUser();

        $cadence = Cadence::withoutGlobalScopes()->findOrFail($cadenceId);

        // 1. Remover tarefas pendentes geradas pelas matrículas desta cadência
        $enrollmentIds = $cadence->enrollments()->pluck('id');
        Task::whereIn('company_cadence_id', $enrollmentIds)->delete();

        // 2. Remover matrículas
        $cadence->enrollments()->delete();

        // 3. Remover passos da cadência
        $cadence->steps()->delete();

        // 4. Remover a cadência
        $cadence->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cadência excluída com sucesso!',
            ]);
        }

        return back()->with('success', 'Cadência excluída com sucesso!');
    }
}

