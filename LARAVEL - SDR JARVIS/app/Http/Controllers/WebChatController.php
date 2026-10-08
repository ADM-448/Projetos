<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Lead;
use App\Models\Message;
use App\Models\Tenant;
use App\Models\WhatsappInstance;
use App\Traits\BelongsToTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class WebChatController extends Controller
{
    /**
     * Resolve o tenant atual.
     */
    protected function getCurrentTenantId(): int
    {
        return BelongsToTenant::resolveCurrentTenantId() ?? Auth::user()?->tenant_id ?? 1;
    }

    /**
     * Renderiza a interface do WebChat estilo WhatsApp Web.
     */
    public function index()
    {
        $tenantId = $this->getCurrentTenantId();

        // Obtém todos os Leads (contatos) do tenant, incluindo mensagens
        // Ordena pelos que tem mensagens mais recentes primeiro
        $leads = Lead::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->with(['messages' => function ($q) {
                $q->latest();
            }, 'company'])
            ->get()
            ->sortByDesc(function ($lead) {
                return $lead->messages->first()?->created_at ?? $lead->created_at;
            })
            ->values();

        // Obtém empresas para o dropdown de "Vincular a Empresa"
        $companies = Company::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        // Obtém as instâncias do WhatsApp (para saber de onde enviar a msg caso haja várias)
        $instances = WhatsappInstance::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->get(['id', 'instance_name', 'status', 'phone_number']);

        return Inertia::render('WebChat/Index', [
            'leads' => $leads,
            'companies' => $companies,
            'instances' => $instances,
        ]);
    }

    /**
     * Vincula um contato (Lead) a uma Empresa (Company).
     */
    public function linkCompany(Request $request, int $leadId)
    {
        $tenantId = $this->getCurrentTenantId();
        
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id'
        ]);

        $lead = Lead::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->findOrFail($leadId);

        $company = Company::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->findOrFail($validated['company_id']);

        $lead->update([
            'company_id' => $company->id,
            'company_name' => $company->name,
            'pipeline_stage_id' => $company->pipeline_stage_id // Sincroniza a fase do funil
        ]);

        return back()->with('success', 'Contato vinculado à empresa com sucesso!');
    }

    /**
     * Cria uma nova Empresa e vincula o contato (Lead) a ela.
     */
    public function createCompanyAndLink(Request $request, int $leadId)
    {
        $tenantId = $this->getCurrentTenantId();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'segment' => 'nullable|string|max:255',
            'document' => 'nullable|string|max:255',
        ]);

        $firstStage = \App\Models\PipelineStage::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('order', 'asc')
            ->first();

        $company = Company::withoutGlobalScopes()->create([
            'tenant_id' => $tenantId,
            'name' => $validated['name'],
            'segment' => $validated['segment'] ?? null,
            'document' => $validated['document'] ?? null,
            'pipeline_stage_id' => $firstStage?->id,
            'assigned_user_id' => Auth::id(),
            'status' => 'open',
        ]);

        $lead = Lead::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->findOrFail($leadId);

        $lead->update([
            'company_id' => $company->id,
            'company_name' => $company->name,
            'pipeline_stage_id' => $company->pipeline_stage_id,
        ]);

        return back()->with('success', 'Empresa criada e contato vinculado com sucesso!');
    }

    /**
     * Sincroniza o histórico de mensagens do WhatsApp via Evolution API para um contato.
     */
    public function syncHistory(int $leadId)
    {
        $lead = Lead::withoutGlobalScopes()->findOrFail($leadId);
        $tenantId = $lead->tenant_id;

        $instance = WhatsappInstance::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('status', 'connected')
            ->first() ?? WhatsappInstance::withoutGlobalScopes()->where('tenant_id', $tenantId)->first();

        if (! $instance) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhuma instância de WhatsApp encontrada para este tenant.',
            ], 422);
        }

        $baseUrl = config('services.whatsapp.api_url', 'http://localhost:8080');
        $apiKey = config('services.whatsapp.api_key');

        $cleanPhone = preg_replace('/[^0-9]/', '', $lead->phone);
        $candidates = [];

        // Monta variações com DDI 55
        $with55 = str_starts_with($cleanPhone, '55') ? $cleanPhone : '55' . $cleanPhone;
        $candidates[] = "{$with55}@s.whatsapp.net";

        // Variação sem 9º dígito (se tiver 13 dígitos: 55 + 2 DDD + 9 dígitos -> 55 + 2 DDD + 8 dígitos)
        if (strlen($with55) === 13) {
            $without9 = substr($with55, 0, 4) . substr($with55, 5);
            $candidates[] = "{$without9}@s.whatsapp.net";
        }
        // Variação com 9º dígito (se tiver 12 dígitos: 55 + 2 DDD + 8 dígitos -> 55 + 2 DDD + 9 + 8 dígitos)
        if (strlen($with55) === 12) {
            $with9 = substr($with55, 0, 4) . '9' . substr($with55, 4);
            $candidates[] = "{$with9}@s.whatsapp.net";
        }

        $candidates = array_unique($candidates);
        $imported = 0;

        foreach ($candidates as $jid) {
            try {
                $resp = Http::withHeaders(['apikey' => $apiKey])
                    ->timeout(12)
                    ->post("{$baseUrl}/chat/findMessages/{$instance->instance_name}", [
                        'where' => [
                            'key' => [
                                'remoteJid' => $jid
                            ]
                        ],
                        'limit' => 50
                    ]);

                if ($resp->successful()) {
                    $records = $resp->json()['messages']['records'] ?? [];

                    foreach ($records as $r) {
                        $extId = $r['key']['id'] ?? null;
                        if (! $extId) continue;

                        $exists = Message::withoutGlobalScopes()->where('external_id', $extId)->exists();
                        if ($exists) continue;

                        $fromMe = (bool) ($r['key']['fromMe'] ?? false);
                        $msgContent = $r['message'] ?? [];
                        $body = $msgContent['conversation'] 
                            ?? $msgContent['extendedTextMessage']['text'] 
                            ?? $msgContent['imageMessage']['caption'] 
                            ?? ($fromMe ? '[Mídia enviada]' : '[Mídia recebida]');

                        $timestamp = $r['messageTimestamp'] ?? null;
                        $sentAt = $timestamp ? date('Y-m-d H:i:s', $timestamp) : now();

                        Message::withoutGlobalScopes()->create([
                            'tenant_id' => $lead->tenant_id,
                            'lead_id' => $lead->id,
                            'whatsapp_instance_id' => $instance->id,
                            'direction' => $fromMe ? 'outbound' : 'inbound',
                            'body' => $body,
                            'status' => $fromMe ? 'sent' : 'read',
                            'external_id' => $extId,
                            'sent_at' => $sentAt,
                            'created_at' => $sentAt,
                        ]);
                        $imported++;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("[SyncHistory] Erro ao sincronizar mensagens: " . $e->getMessage());
            }
        }

        $messages = $lead->messages()->withoutGlobalScopes()->orderBy('created_at', 'asc')->get();

        return response()->json([
            'success' => true,
            'imported_count' => $imported,
            'messages' => $messages,
        ]);
    }
}
