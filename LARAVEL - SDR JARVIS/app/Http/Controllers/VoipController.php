<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\CallLog;
use App\Models\Company;
use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Voip\AsteriskAmiService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VoipController extends Controller
{
    /**
     * Retorna as credenciais de WebRTC / SIP do usuário logado para o Webphone.
     */
    public function credentials(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Não autenticado'], 401);
        }

        $tenant = $user->tenant ?? Tenant::find($user->tenant_id) ?? Tenant::first();

        // Se o usuário não tiver ramal configurado, gera um ramal virtual ou sugestão
        $extension = $user->sip_extension ?: (string)(1000 + $user->id);
        $serverHost = $tenant?->voip_server_host ?: 'pabx.local';
        $wsUrl = $tenant?->voip_webrtc_ws_url ?: "wss://{$serverHost}:8089/ws";

        return response()->json([
            'success' => true,
            'voip_enabled' => (bool) ($tenant?->voip_enabled ?? false),
            'credentials' => [
                'server_host' => $serverHost,
                'webrtc_ws_url' => $wsUrl,
                'extension' => $extension,
                'secret' => $user->sip_secret ?: 'jarvis123',
                'display_name' => $user->name,
                'webrtc_enabled' => (bool) ($user->sip_webrtc_enabled ?? true),
            ],
            'tenant' => [
                'id' => $tenant?->id,
                'name' => $tenant?->name,
            ],
        ]);
    }

    /**
     * Salva as configurações de FreePBX / Asterisk do Tenant.
     */
    public function saveSettings(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isManager())) {
            abort(403, 'Acesso não autorizado.');
        }

        $validated = $request->validate([
            'tenant_id' => 'nullable|exists:tenants,id',
            'voip_enabled' => 'required|boolean',
            'voip_server_host' => 'nullable|string|max:255',
            'voip_webrtc_ws_url' => 'nullable|string|max:255',
            'voip_ami_host' => 'nullable|string|max:255',
            'voip_ami_port' => 'nullable|integer|min:1|max:65535',
            'voip_ami_user' => 'nullable|string|max:255',
            'voip_ami_secret' => 'nullable|string|max:255',
            'voip_recordings_url' => 'nullable|string|max:255',
        ]);

        $tenantId = $validated['tenant_id'] ?? $user->tenant_id;
        $tenant = Tenant::findOrFail($tenantId);

        $tenant->update([
            'voip_enabled' => $validated['voip_enabled'],
            'voip_server_host' => $validated['voip_server_host'] ?? null,
            'voip_webrtc_ws_url' => $validated['voip_webrtc_ws_url'] ?? null,
            'voip_ami_host' => $validated['voip_ami_host'] ?? null,
            'voip_ami_port' => $validated['voip_ami_port'] ?? 5038,
            'voip_ami_user' => $validated['voip_ami_user'] ?? null,
            'voip_ami_secret' => $validated['voip_ami_secret'] ?? null,
            'voip_recordings_url' => $validated['voip_recordings_url'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Configurações de telefonia FreePBX atualizadas!',
                'tenant' => $tenant,
            ]);
        }

        return back()->with('success', 'Configurações de telefonia FreePBX atualizadas!');
    }

    /**
     * Testa a conexão com o Asterisk Manager Interface (AMI).
     */
    public function testAmi(Request $request, AsteriskAmiService $amiService): JsonResponse
    {
        $validated = $request->validate([
            'host' => 'required|string',
            'port' => 'nullable|integer',
            'user' => 'required|string',
            'secret' => 'required|string',
        ]);

        $result = $amiService->testConnection(
            $validated['host'],
            $validated['port'] ?? 5038,
            $validated['user'],
            $validated['secret']
        );

        return response()->json($result);
    }

    /**
     * Atribui ramal SIP a um colaborador da equipe.
     */
    public function saveUserExtension(Request $request, User $user): JsonResponse
    {
        $currentUser = Auth::user();
        if (!$currentUser || (!$currentUser->isAdmin() && !$currentUser->isManager())) {
            abort(403, 'Acesso não autorizado.');
        }

        // Se for gestor, só altera usuários do mesmo tenant
        if (!$currentUser->isSuperAdmin() && $currentUser->tenant_id !== $user->tenant_id) {
            abort(403, 'Você só pode gerenciar usuários da sua própria base.');
        }

        $validated = $request->validate([
            'sip_extension' => 'nullable|string|max:50',
            'sip_secret' => 'nullable|string|max:255',
            'sip_webrtc_enabled' => 'nullable|boolean',
        ]);

        $user->update([
            'sip_extension' => $validated['sip_extension'] ?? null,
            'sip_secret' => $validated['sip_secret'] ?? null,
            'sip_webrtc_enabled' => $validated['sip_webrtc_enabled'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ramal SIP atualizado com sucesso para ' . $user->name,
            'user' => $user->only(['id', 'name', 'sip_extension', 'sip_webrtc_enabled']),
        ]);
    }

    /**
     * Dispara chamada via AMI (Originate para ramal físico/softphone externo).
     */
    public function originate(Request $request, AsteriskAmiService $amiService): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Não autenticado'], 401);
        }

        $validated = $request->validate([
            'phone' => 'required|string',
            'company_id' => 'nullable|exists:companies,id',
            'lead_id' => 'nullable|exists:leads,id',
        ]);

        $tenant = $user->tenant ?? Tenant::first();
        $extension = $user->sip_extension ?: '1001';

        $result = $amiService->originateCall($tenant, $extension, $validated['phone'], [
            'JARVIS_USER_ID' => $user->id,
            'JARVIS_COMPANY_ID' => $validated['company_id'] ?? '',
            'JARVIS_LEAD_ID' => $validated['lead_id'] ?? '',
        ]);

        return response()->json($result);
    }

    /**
     * Registra o log de uma chamada realizada no Webphone e cria atividade na timeline.
     */
    public function logCall(Request $request): JsonResponse
    {
        $user = Auth::user();
        $tenantId = $user?->tenant_id ?? 1;

        $validated = $request->validate([
            'phone_dialed' => 'required|string',
            'company_id' => 'nullable|exists:companies,id',
            'lead_id' => 'nullable|exists:leads,id',
            'uniqueid' => 'nullable|string',
            'duration' => 'nullable|integer',
            'billable_seconds' => 'nullable|integer',
            'status' => 'nullable|string|in:ringing,answered,busy,no_answer,failed,completed',
            'outcome' => 'nullable|string|in:connected,meeting_scheduled,gatekeeper,busy,no_answer,voicemail,other',
            'notes' => 'nullable|string',
            'recording_url' => 'nullable|string',
        ]);

        $callLog = CallLog::create([
            'tenant_id' => $tenantId,
            'company_id' => $validated['company_id'] ?? null,
            'lead_id' => $validated['lead_id'] ?? null,
            'user_id' => $user?->id,
            'uniqueid' => $validated['uniqueid'] ?? null,
            'phone_dialed' => $validated['phone_dialed'],
            'direction' => 'outbound',
            'status' => $validated['status'] ?? 'completed',
            'duration' => $validated['duration'] ?? 0,
            'billable_seconds' => $validated['billable_seconds'] ?? ($validated['duration'] ?? 0),
            'outcome' => $validated['outcome'] ?? 'connected',
            'notes' => $validated['notes'] ?? null,
            'recording_url' => $validated['recording_url'] ?? null,
        ]);

        // Formata descrição amigável para a timeline
        $outcomeLabels = [
            'connected' => '📞 Conversa Realizada',
            'meeting_scheduled' => '🎯 Reunião Agendada na Chamada',
            'gatekeeper' => '🛑 Barrou na Secretária / Recepção',
            'busy' => '📵 Número Ocupado',
            'no_answer' => '⏳ Não Atendeu',
            'voicemail' => '📼 Caixa Postal',
            'other' => '📞 Ligação Telefônica',
        ];

        $outcomeLabel = $outcomeLabels[$callLog->outcome] ?? '📞 Ligação Telefônica';
        $durationFormatted = gmdate($callLog->duration >= 3600 ? 'H:i:s' : 'i:s', $callLog->duration);

        $description = "{$outcomeLabel} ({$durationFormatted}) para {$callLog->phone_dialed}";
        if ($callLog->notes) {
            $description .= " — Notas: \"{$callLog->notes}\"";
        }

        // Grava na timeline da empresa
        if ($callLog->company_id) {
            Activity::withoutGlobalScopes()->create([
                'tenant_id' => $tenantId,
                'company_id' => $callLog->company_id,
                'lead_id' => $callLog->lead_id,
                'user_id' => $user?->id,
                'type' => 'call',
                'description' => $description,
            ]);
        }

        // Se solicitado auto_analyze, executa a análise de inteligência comercial por IA
        $aiAnalysis = null;
        if ($request->boolean('auto_analyze')) {
            $aiService = new \App\Services\AiCallAnalyticsService(Tenant::find($tenantId));
            $analysisResult = $aiService->analyzeCall($callLog, null, $callLog->notes);
            $aiAnalysis = $analysisResult['analysis'] ?? null;
        }

        return response()->json([
            'success' => true,
            'message' => 'Chamada registrada com sucesso!' . ($aiAnalysis ? ' (Ata gerada por IA)' : ''),
            'call_log' => $callLog->fresh(['user:id,name', 'company:id,name', 'lead:id,name']),
            'ai_analysis' => $aiAnalysis,
        ]);
    }

    /**
     * Executa a análise de IA e transcrição para um CallLog existente.
     */
    public function analyzeCall(Request $request, int $callLogId, \App\Services\AiCallAnalyticsService $service): JsonResponse
    {
        $callLog = CallLog::findOrFail($callLogId);
        $userObservations = $request->input('notes');

        $result = $service->analyzeCall($callLog, null, $userObservations);

        return response()->json($result);
    }

    /**
     * Webhook chamado pelo FreePBX / Asterisk para envio do CDR e gravação de áudio.
     */
    public function cdrWebhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        Log::info('[FreePBX CDR Webhook] Registro recebido:', $payload);

        $uniqueid = $payload['uniqueid'] ?? $payload['linkedid'] ?? null;
        $duration = (int) ($payload['duration'] ?? 0);
        $billableSeconds = (int) ($payload['billableseconds'] ?? $duration);
        $disposition = strtoupper((string) ($payload['disposition'] ?? ''));
        $recordingUrl = $payload['recording_url'] ?? $payload['audio_url'] ?? null;

        if ($uniqueid) {
            $callLog = CallLog::where('uniqueid', $uniqueid)->first();
            if ($callLog) {
                $status = match ($disposition) {
                    'ANSWERED' => 'answered',
                    'BUSY' => 'busy',
                    'NO ANSWER' => 'no_answer',
                    default => 'completed',
                };

                $callLog->update([
                    'duration' => $duration ?: $callLog->duration,
                    'billable_seconds' => $billableSeconds ?: $callLog->billable_seconds,
                    'status' => $status,
                    'recording_url' => $recordingUrl ?: $callLog->recording_url,
                ]);

                return response()->json(['status' => 'updated', 'call_log_id' => $callLog->id]);
            }
        }

        return response()->json(['status' => 'received', 'uniqueid' => $uniqueid]);
    }
}
