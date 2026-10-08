<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Lead;
use App\Models\PipelineStage;
use App\Models\Tenant;
use App\Services\AiAgentService;
use App\Traits\BelongsToTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiAgentController extends Controller
{
    /**
     * Retorna a instância do serviço com o tenant ativo.
     */
    protected function getAiService(): AiAgentService
    {
        $user = Auth::user();
        $tenantId = BelongsToTenant::resolveCurrentTenantId() ?? $user?->tenant_id;
        $tenant = $tenantId ? Tenant::find($tenantId) : null;

        return new AiAgentService($tenant);
    }

    /**
     * Gera 3 opções de respostas de vendas contextuais e persuasivas para o SDR.
     */
    public function suggestReplies(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'context' => 'nullable|string|max:500',
        ]);

        $lead = Lead::findOrFail($validated['lead_id']);

        $service = $this->getAiService();
        $suggestions = $service->generateSuggestions($lead, $validated['context'] ?? null);

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Qualifica o lead com base no sentimento, objeções, fit comercial e sugere próxima ação.
     */
    public function qualifyLead(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
        ]);

        $lead = Lead::findOrFail($validated['lead_id']);

        $service = $this->getAiService();
        $qualification = $service->qualifyLead($lead);

        return response()->json([
            'success' => true,
            'qualification' => $qualification,
        ]);
    }

    /**
     * Melhora ou ajusta o tom de um rascunho de mensagem digitado pelo SDR.
     */
    public function improveDraft(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'text' => 'required|string|max:2000',
            'tone' => 'required|string|in:persuasive,short,executive,friendly',
        ]);

        $lead = Lead::findOrFail($validated['lead_id']);

        $service = $this->getAiService();
        $improved = $service->improveDraft($validated['text'], $validated['tone'], $lead);

        return response()->json([
            'success' => true,
            'text' => $improved,
        ]);
    }

    /**
     * Aceita a sugestão de etapa da IA e move a empresa/lead no Kanban com registro de atividade.
     */
    public function autoAdvanceStage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'target_stage_id' => 'nullable|exists:pipeline_stages,id',
            'target_stage_name' => 'nullable|string',
        ]);

        $lead = Lead::with('company')->findOrFail($validated['lead_id']);
        $company = $lead->company;

        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Lead não está associado a uma conta/empresa.'], 422);
        }

        $targetStage = null;

        if (!empty($validated['target_stage_id'])) {
            $targetStage = PipelineStage::find($validated['target_stage_id']);
        } elseif (!empty($validated['target_stage_name'])) {
            // Busca por similaridade no pipeline do tenant
            $targetStage = PipelineStage::where('name', 'like', '%' . $validated['target_stage_name'] . '%')->first();
        }

        if (!$targetStage) {
            return response()->json(['success' => false, 'message' => 'Etapa de destino não encontrada no pipeline.'], 404);
        }

        $oldStageId = $company->pipeline_stage_id;
        $company->pipeline_stage_id = $targetStage->id;
        $company->save();

        $lead->pipeline_stage_id = $targetStage->id;
        $lead->save();

        // Registra atividade automática no histórico da empresa
        Activity::create([
            'tenant_id' => $company->tenant_id,
            'company_id' => $company->id,
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'type' => 'note',
            'title' => 'Avanço Automático por IA (JARVIS Copilot)',
            'description' => "O lead foi movido para a etapa '{$targetStage->name}' após recomendação da inteligência comercial.",
            'occurred_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Conta avançada para a etapa '{$targetStage->name}' com sucesso!",
            'stage' => $targetStage,
        ]);
    }

    /**
     * Retorna as instruções e configurações de IA do perfil de CRM ativo.
     */
    public function getSettings(Request $request): JsonResponse
    {
        $user = Auth::user();
        $tenantId = BelongsToTenant::resolveCurrentTenantId() ?? $user?->tenant_id;
        $tenant = $tenantId ? Tenant::find($tenantId) : null;

        if (!$tenant) {
            return response()->json(['success' => false, 'message' => 'Perfil de CRM não localizado.'], 404);
        }

        return response()->json([
            'success' => true,
            'settings' => $tenant->getAiConfig(),
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
            ],
        ]);
    }

    /**
     * Salva as diretrizes, prompt, guardrails e configurações de treinamento da IA.
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $user = Auth::user();
        $tenantId = BelongsToTenant::resolveCurrentTenantId() ?? $user?->tenant_id;
        $tenant = $tenantId ? Tenant::find($tenantId) : null;

        if (!$tenant) {
            return response()->json(['success' => false, 'message' => 'Perfil de CRM não localizado.'], 404);
        }

        $validated = $request->validate([
            'ai_name' => 'required|string|max:100',
            'ai_persona' => 'required|string|max:255',
            'company_value_prop' => 'required|string|max:1000',
            'instructions' => 'required|string|max:5000',
            'guardrails' => 'nullable|string|max:3000',
            'faq' => 'nullable|array',
            'faq.*.q' => 'required_with:faq|string|max:500',
            'faq.*.a' => 'required_with:faq|string|max:1000',
            'tone' => 'required|string|in:consultative,direct,friendly,formal',
            'ai_prompt' => 'nullable|string|max:3000',
            'ai_model' => 'nullable|string|max:50',
            'ai_api_key' => 'nullable|string|max:255',
            'temperature' => 'nullable|numeric|min:0|max:1',
            // Configurações do SDR Autônomo
            'ai_autonomous_enabled' => 'nullable|boolean',
            'autonomous_delay_seconds' => 'nullable|integer|min:1|max:60',
            'autonomous_auto_advance' => 'nullable|boolean',
            'autonomous_auto_disqualify' => 'nullable|boolean',
            'autonomous_handoff_on_stress' => 'nullable|boolean',
            'autonomous_working_hours_only' => 'nullable|boolean',
            'autonomous_working_hours_start' => 'nullable|string|max:5',
            'autonomous_working_hours_end' => 'nullable|string|max:5',
        ]);

        $currentSettings = $tenant->settings ?? [];

        // Atualiza campos
        $currentSettings['ai_name'] = $validated['ai_name'];
        $currentSettings['ai_persona'] = $validated['ai_persona'];
        $currentSettings['company_value_prop'] = $validated['company_value_prop'];
        $currentSettings['instructions'] = $validated['instructions'];
        $currentSettings['guardrails'] = $validated['guardrails'] ?? '';
        $currentSettings['faq'] = $validated['faq'] ?? [];
        $currentSettings['tone'] = $validated['tone'];
        if (!empty($validated['ai_prompt'])) {
            $currentSettings['ai_prompt'] = $validated['ai_prompt'];
        }
        if (!empty($validated['ai_model'])) {
            $currentSettings['ai_model'] = $validated['ai_model'];
        }
        if (!empty($validated['temperature'])) {
            $currentSettings['temperature'] = (float) $validated['temperature'];
        }

        // Configurações de Autonomia
        $currentSettings['ai_autonomous_enabled'] = (bool) ($validated['ai_autonomous_enabled'] ?? false);
        if (isset($validated['autonomous_delay_seconds'])) {
            $currentSettings['autonomous_delay_seconds'] = (int) $validated['autonomous_delay_seconds'];
        }
        if (isset($validated['autonomous_auto_advance'])) {
            $currentSettings['autonomous_auto_advance'] = (bool) $validated['autonomous_auto_advance'];
        }
        if (isset($validated['autonomous_auto_disqualify'])) {
            $currentSettings['autonomous_auto_disqualify'] = (bool) $validated['autonomous_auto_disqualify'];
        }
        if (isset($validated['autonomous_handoff_on_stress'])) {
            $currentSettings['autonomous_handoff_on_stress'] = (bool) $validated['autonomous_handoff_on_stress'];
        }
        if (isset($validated['autonomous_working_hours_only'])) {
            $currentSettings['autonomous_working_hours_only'] = (bool) $validated['autonomous_working_hours_only'];
        }
        if (!empty($validated['autonomous_working_hours_start'])) {
            $currentSettings['autonomous_working_hours_start'] = $validated['autonomous_working_hours_start'];
        }
        if (!empty($validated['autonomous_working_hours_end'])) {
            $currentSettings['autonomous_working_hours_end'] = $validated['autonomous_working_hours_end'];
        }

        // Se digitou uma nova chave da OpenAI (não mascarada)
        if (!empty($validated['ai_api_key']) && !str_starts_with($validated['ai_api_key'], '••••')) {
            $currentSettings['ai_api_key'] = $validated['ai_api_key'];
        }

        $tenant->settings = $currentSettings;
        $tenant->save();

        return response()->json([
            'success' => true,
            'message' => 'Instruções e diretrizes da IA salvas com sucesso!',
            'settings' => $tenant->getAiConfig(),
        ]);
    }

    /**
     * Pausa ou retoma o atendimento autônomo da IA para um lead específico.
     */
    public function toggleLeadAutonomous(Lead $lead): JsonResponse
    {
        $currentlyPaused = $lead->isAiAutonomousPaused();

        if ($currentlyPaused) {
            $lead->resumeAiAutonomous();
            $message = "IA Autônoma reativada para {$lead->name}!";
            $paused = false;
        } else {
            $lead->pauseAiAutonomous('Pausado manualmente pelo vendedor');
            $message = "IA Autônoma pausada para {$lead->name}. O vendedor humano está no controle.";
            $paused = true;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_ai_paused' => $paused,
        ]);
    }

    /**
     * Arena de Teste & Estresse da IA: processa o diálogo em tempo real e emite telemetria.
     */
    public function simulateConversation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array',
            'options' => 'nullable|array',
        ]);

        $service = $this->getAiService();
        $result = $service->simulateConversation(
            $validated['history'] ?? [],
            $validated['message'],
            $validated['options'] ?? []
        );

        return response()->json([
            'success' => true,
            'reply' => $result['reply'],
            'xray' => $result['xray'],
        ]);
    }

    /**
     * Testa a conectividade da chave de API da IA (OpenAI ou Groq).
     */
    public function testApiKey(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'api_key' => 'nullable|string',
            'model' => 'nullable|string',
        ]);

        $key = $validated['api_key'] ?? null;
        if (!empty($key) && str_starts_with($key, '••••')) {
            $key = null; // Usa a chave configurada no tenant
        }

        $service = $this->getAiService();
        $result = $service->testConnection($key, $validated['model'] ?? null);

        return response()->json($result);
    }
}
