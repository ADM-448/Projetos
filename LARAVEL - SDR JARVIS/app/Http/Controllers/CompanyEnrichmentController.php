<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Company;
use App\Models\Lead;
use App\Services\CompanyEnrichmentService;
use App\Traits\BelongsToTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyEnrichmentController extends Controller
{
    protected CompanyEnrichmentService $enrichmentService;

    public function __construct(CompanyEnrichmentService $enrichmentService)
    {
        $this->enrichmentService = $enrichmentService;
    }

    /**
     * Consulta os dados cadastrais da empresa na Receita Federal via CNPJ.
     */
    public function enrichByCnpj(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cnpj' => 'required|string|min:11|max:20',
        ]);

        $data = $this->enrichmentService->fetchByCnpj($validated['cnpj']);

        if (!($data['success'] ?? false)) {
            return response()->json($data, 422);
        }

        return response()->json($data);
    }

    /**
     * Aplica o enriquecimento em uma empresa existente e opcionalmente cadastra os sócios selecionados como contatos.
     */
    public function applyEnrichment(Request $request, int $companyId): JsonResponse
    {
        $validated = $request->validate([
            'cnpj' => 'required|string',
            'create_contacts' => 'nullable|array',
            'create_contacts.*.name' => 'required|string',
            'create_contacts.*.job_title' => 'nullable|string',
            'create_contacts.*.phone' => 'nullable|string',
            'create_contacts.*.email' => 'nullable|string',
        ]);

        $company = Company::with('contacts')->findOrFail($companyId);
        $enrichment = $this->enrichmentService->fetchByCnpj($validated['cnpj']);

        if (!($enrichment['success'] ?? false)) {
            return response()->json(['success' => false, 'message' => 'Falha ao enriquecer CNPJ.'], 422);
        }

        // Atualiza campos cadastrais da empresa
        $company->document = $enrichment['cnpj'];
        if (!empty($enrichment['segment'])) {
            $company->segment = $enrichment['segment'];
        }

        // Salva metadados ricos em custom_attributes
        $custom = $company->custom_attributes ?? [];
        $custom['enrichment'] = $enrichment;
        $custom['legal_name'] = $enrichment['legal_name'];
        $custom['trade_name'] = $enrichment['trade_name'];
        $custom['capital_social'] = $enrichment['capital_social_formatted'];
        $custom['status_receita'] = $enrichment['status'];
        $custom['address'] = $enrichment['full_address'];
        $custom['enriched_at'] = now()->toIso8601String();

        $company->custom_attributes = $custom;
        $company->save();

        // Cadastra sócios selecionados como contatos da empresa
        $createdContactsCount = 0;
        if (!empty($validated['create_contacts'])) {
            $existingPhoneList = $company->contacts->pluck('phone')->toArray();
            $existingNameList = $company->contacts->pluck('name')->map(fn($n) => mb_strtolower($n))->toArray();

            foreach ($validated['create_contacts'] as $partner) {
                $pName = trim($partner['name']);
                if (in_array(mb_strtolower($pName), $existingNameList)) {
                    continue; // já existe contato com esse nome
                }

                $pPhone = !empty($partner['phone']) ? $partner['phone'] : ($enrichment['phone'] ?? '11999990000');
                $pEmail = !empty($partner['email']) ? $partner['email'] : ($enrichment['email'] ?? null);

                Lead::create([
                    'tenant_id' => $company->tenant_id,
                    'company_id' => $company->id,
                    'pipeline_stage_id' => $company->pipeline_stage_id,
                    'assigned_user_id' => $company->assigned_user_id ?? Auth::id(),
                    'name' => $pName,
                    'job_title' => $partner['job_title'] ?? 'Sócio / Decisor',
                    'company_name' => $company->name,
                    'phone' => $pPhone,
                    'email' => $pEmail,
                    'status' => 'open',
                    'is_primary' => false,
                ]);

                $createdContactsCount++;
            }
        }

        // Registra atividade na timeline da conta
        Activity::create([
            'tenant_id' => $company->tenant_id,
            'company_id' => $company->id,
            'user_id' => Auth::id(),
            'type' => 'note',
            'title' => '⚡ Conta Enriquecida via Receita Federal (CNPJ)',
            'description' => "Dados cadastrais atualizados pela consulta pública. Razão Social: {$enrichment['legal_name']}, Capital: {$enrichment['capital_social_formatted']}. " . ($createdContactsCount > 0 ? "Foram adicionados {$createdContactsCount} decisor(es) do Quadro de Sócios (QSA)." : ""),
            'occurred_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Conta enriquecida com sucesso!' . ($createdContactsCount > 0 ? " ({$createdContactsCount} decisor(es) adicionados do QSA)" : ''),
            'company' => $company->fresh(['contacts']),
        ]);
    }
}
