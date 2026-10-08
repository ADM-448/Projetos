<?php

namespace App\Http\Controllers;

use App\Jobs\SendWhatsappMessageJob;
use App\Models\Activity;
use App\Models\Company;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WhatsappInstance;
use App\Models\Message;
use App\Traits\BelongsToTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class KanbanController extends Controller
{
    /**
     * Mantido para compatibilidade interna. A autenticação é gerida pelo middleware auth.
     */
    protected function ensureAuthenticatedUser(): void
    {
        // No-op: autenticação garantida pelo middleware de rotas
    }

    /**
     * Exibe o quadro Kanban completo com colunas e cards de leads.
     */
    public function index(): Response
    {
        $user = Auth::user();
        $isSuperAdmin = (bool) ($user?->isSuperAdmin() ?? false);
        $isManager = (bool) ($user?->isManager() ?? false);
        $canManageTenants = $isSuperAdmin;
        $canManageTeam = $isSuperAdmin || $isManager;

        // Se for superadmin, pode usar o tenant selecionado na sessão. Se for gestor ou SDR, usa estritamente o seu próprio tenant
        if ($isSuperAdmin) {
            $tenantId = BelongsToTenant::resolveCurrentTenantId() ?? $user?->tenant_id ?? 1;
        } else {
            $tenantId = $user?->tenant_id ?? 1;
        }

        $activeTenant = Tenant::find($tenantId) ?? Tenant::first();
        if ($activeTenant) {
            $tenantId = $activeTenant->id;
        }

        // Tenants para gestão (apenas Super Admin pode ver todos os perfis de CRM)
        $allTenants = [];
        if ($isSuperAdmin) {
            $allTenants = Tenant::withCount(['companies', 'users', 'leads'])
                ->get()
                ->map(function ($t) {
                    $t->ai_config = $t->getAiConfig();
                    return $t;
                });
        }

        // Usuários para gestão (Super Admin vê todos; Gestor vê apenas a sua própria equipe da base)
        $allUsers = [];
        if ($isSuperAdmin) {
            $allUsers = User::with('tenant:id,name')->get();
        } elseif ($isManager) {
            $allUsers = User::withoutGlobalScopes()
                ->where('tenant_id', $user->tenant_id)
                ->with('tenant:id,name')
                ->get();
        }

        // Pipeline principal do tenant
        $pipeline = Pipeline::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->with(['stages' => fn ($q) => $q->orderBy('position', 'asc')])
            ->first();

        // Se não houver pipeline, busca qualquer um ou cria padrão
        if (! $pipeline) {
            $pipeline = Pipeline::withoutGlobalScopes()->first();
        }

        $stages = $pipeline?->stages ?? collect();

        // Contas / Empresas do tenant com seus contatos, atividades, tarefas, cadências e mensagens
        $companies = Company::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->with([
                'stage',
                'assignedUser',
                'contacts' => fn ($q) => $q->with(['messages' => fn ($m) => $m->latest()->limit(1)]),
                'activities' => fn ($q) => $q->with(['user:id,name', 'lead:id,name,job_title'])->latest(),
                'tasks' => fn ($q) => $q->with(['user:id,name', 'lead:id,name'])->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END, due_at ASC"),
                'activeCadence' => fn ($q) => $q->with(['cadence.steps', 'user:id,name']),
                'latestCadence' => fn ($q) => $q->with(['cadence.steps', 'user:id,name']),
            ])
            ->get();

        // Leads isolados para compatibilidade
        $leads = Lead::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->with([
                'stage',
                'assignedUser',
                'messages' => fn ($q) => $q->latest()->limit(1),
            ])
            ->get();

        // Instâncias de WhatsApp do tenant
        $instances = WhatsappInstance::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->get();

        // Contas de E-mail (Gmail OAuth) do tenant
        $emailAccounts = \App\Models\EmailAccount::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->get();

        $hasGoogleConfig = ! empty(config('services.google.client_id')) || ! empty(env('GOOGLE_CLIENT_ID'));

        // Cálculo de Métricas do Funil / Dashboard (Account-Based)
        $totalCompanies = $companies->count();
        $totalContacts = $leads->count();
        $sentToday = (int) $instances->sum('sent_today');
        $dailyLimit = (int) $instances->sum('daily_limit');
        $dailyPercent = $dailyLimit > 0 ? min(100, round(($sentToday / $dailyLimit) * 100, 1)) : 0;
        $emailsSentToday = (int) $emailAccounts->sum('sent_today');

        // Empresas que responderam ou avançaram para fases de conversa
        $respondedCompanies = $companies->filter(function ($company) {
            $stageName = mb_strtolower((string) $company->stage?->name);
            $hasInbound = $company->contacts->some(fn ($c) => $c->messages->contains('direction', 'inbound'));
            return $hasInbound || str_contains($stageName, 'respondeu') || str_contains($stageName, 'qualificado') || str_contains($stageName, 'reunião');
        })->count();

        // Empresas contactadas (não estão na base fria inicial ou possuem mensagens)
        $initialStageId = $stages->first()?->id;
        $contactedCompanies = $companies->filter(function ($company) use ($initialStageId) {
            $hasMessages = $company->contacts->some(fn ($c) => $c->messages->isNotEmpty());
            return $company->pipeline_stage_id !== $initialStageId || $hasMessages;
        })->count();

        $responseRate = $contactedCompanies > 0 
            ? round(($respondedCompanies / $contactedCompanies) * 100, 1) 
            : 0;

        // Reuniões marcadas
        $meetingsCount = $companies->filter(function ($company) {
            $stageName = mb_strtolower((string) $company->stage?->name);
            return str_contains($stageName, 'reunião') || str_contains($stageName, 'agendad');
        })->count();

        $metrics = [
            'total_companies' => $totalCompanies,
            'total_leads' => $totalContacts,
            'sent_today' => $sentToday,
            'daily_limit' => $dailyLimit,
            'daily_percent' => $dailyPercent,
            'emails_sent_today' => $emailsSentToday,
            'contacted_leads' => $contactedCompanies,
            'responded_leads' => $respondedCompanies,
            'response_rate' => $responseRate,
            'meetings_count' => $meetingsCount,
        ];

        // Usuários SDRs do tenant
        $users = User::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->get(['id', 'name', 'email']);

        // Métricas analíticas do Dashboard de Produtividade dos SDRs
        $dashboardService = new \App\Services\DashboardMetricsService();
        $dashboardMetrics = $dashboardService->getMetrics($tenantId);

        // Cadências e sequências de prospecção do tenant
        $cadences = \App\Models\Cadence::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->with(['steps'])
            ->withCount(['enrollments', 'activeEnrollments'])
            ->latest()
            ->get();

        // Templates de mensagens e scripts de vendas do tenant
        $messageTemplates = \App\Models\MessageTemplate::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('is_favorite', 'desc')
            ->latest()
            ->get();

        return Inertia::render('Kanban/Index', [
            'pipeline' => $pipeline,
            'stages' => $stages,
            'companies' => $companies,
            'leads' => $leads,
            'instances' => $instances,
            'emailAccounts' => $emailAccounts,
            'hasGoogleConfig' => $hasGoogleConfig,
            'metrics' => $metrics,
            'dashboardMetrics' => $dashboardMetrics,
            'users' => $users,
            'currentUser' => $user,
            'cadences' => $cadences,
            'messageTemplates' => $messageTemplates,
            'activeTenant' => $activeTenant,
            'allTenants' => $allTenants,
            'allUsers' => $allUsers,
            'isSuperAdmin' => $isSuperAdmin,
            'isManager' => $isManager,
            'canManageTenants' => $canManageTenants,
            'canManageTeam' => $canManageTeam,
        ]);
    }

    /**
     * Retorna métricas dinâmicas do Dashboard de Produtividade filtradas por período (JSON).
     */
    public function dashboardMetrics(Request $request, \App\Services\DashboardMetricsService $service): JsonResponse
    {
        $this->ensureAuthenticatedUser();
        $tenantId = Auth::user()?->tenant_id ?? 1;
        $period = $request->get('period', 'all');

        return response()->json($service->getMetrics($tenantId, $period));
    }

    /**
     * Atualiza a etapa da Empresa via Drag & Drop no Kanban e sincroniza seus contatos.
     */
    public function updateCompanyStage(Request $request, int $companyId): RedirectResponse
    {
        $this->ensureAuthenticatedUser();

        $validated = $request->validate([
            'pipeline_stage_id' => 'required|exists:pipeline_stages,id',
        ]);

        $company = \App\Models\Company::withoutGlobalScopes()->findOrFail($companyId);
        $company->update([
            'pipeline_stage_id' => $validated['pipeline_stage_id'],
        ]);

        // Sincroniza a etapa de todos os contatos da empresa
        $company->contacts()->update([
            'pipeline_stage_id' => $validated['pipeline_stage_id'],
        ]);

        return back();
    }

    /**
     * Atualiza dados e notas de qualificação da Empresa (Conta).
     */
    public function updateCompany(Request $request, int $companyId): RedirectResponse
    {
        $this->ensureAuthenticatedUser();

        $company = \App\Models\Company::withoutGlobalScopes()->findOrFail($companyId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document' => 'nullable|string|max:30',
            'segment' => 'nullable|string|max:100',
            'status' => 'required|in:open,won,lost,unqualified',
            'pipeline_stage_id' => 'required|exists:pipeline_stages,id',
            'assigned_user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:5000',
        ]);

        $customAttributes = $company->custom_attributes ?? [];
        if (array_key_exists('notes', $validated)) {
            $customAttributes['notes'] = $validated['notes'];
        }

        $company->update([
            'name' => $validated['name'],
            'document' => $validated['document'] ?? null,
            'segment' => $validated['segment'] ?? null,
            'status' => $validated['status'],
            'pipeline_stage_id' => $validated['pipeline_stage_id'],
            'assigned_user_id' => $validated['assigned_user_id'] ?? null,
            'custom_attributes' => $customAttributes,
        ]);

        // Sincroniza o status para os contatos vinculados
        $company->contacts()->update([
            'status' => $validated['status'],
            'pipeline_stage_id' => $validated['pipeline_stage_id'],
        ]);

        return back()->with('success', 'Empresa e negociação atualizadas com sucesso!');
    }

    /**
     * Exclui uma Empresa e todos os seus contatos vinculados.
     */
    public function destroyCompany(int $companyId): RedirectResponse
    {
        $this->ensureAuthenticatedUser();

        $company = \App\Models\Company::withoutGlobalScopes()->findOrFail($companyId);
        $company->contacts()->delete();
        $company->delete();

        return back()->with('success', 'Empresa e contatos excluídos com sucesso!');
    }

    /**
     * Adiciona um novo contato/decisor a uma Empresa existente.
     */
    public function storeCompanyContact(Request $request, int $companyId): RedirectResponse
    {
        $this->ensureAuthenticatedUser();

        $company = \App\Models\Company::withoutGlobalScopes()->findOrFail($companyId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'job_title' => 'nullable|string|max:100',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'is_primary' => 'boolean',
        ]);

        $cleanPhone = preg_replace('/[^0-9]/', '', $validated['phone']);

        Lead::withoutGlobalScopes()->create([
            'tenant_id' => $company->tenant_id,
            'company_id' => $company->id,
            'pipeline_stage_id' => $company->pipeline_stage_id,
            'assigned_user_id' => $company->assigned_user_id,
            'name' => $validated['name'],
            'job_title' => $validated['job_title'] ?? null,
            'company_name' => $company->name,
            'phone' => $cleanPhone,
            'email' => $validated['email'] ?? null,
            'is_primary' => $validated['is_primary'] ?? false,
            'status' => $company->status ?? 'open',
        ]);

        return back()->with('success', 'Novo contato adicionado à empresa com sucesso!');
    }

    /**
     * Registra uma atividade (ligação, observação, reunião) no histórico da Empresa.
     */
    public function storeCompanyActivity(Request $request, int $companyId): RedirectResponse
    {
        $this->ensureAuthenticatedUser();

        $company = Company::withoutGlobalScopes()->findOrFail($companyId);

        $validated = $request->validate([
            'type' => 'required|in:call,note,meeting,whatsapp',
            'call_outcome' => 'nullable|string|max:50',
            'lead_id' => 'nullable|exists:leads,id',
            'title' => 'nullable|string|max:255',
            'description' => 'required|string|max:5000',
        ]);

        Activity::create([
            'tenant_id' => $company->tenant_id,
            'company_id' => $company->id,
            'lead_id' => $validated['lead_id'] ?? null,
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'call_outcome' => $validated['call_outcome'] ?? null,
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'],
        ]);

        return back()->with('success', 'Atividade registrada com sucesso no histórico!');
    }

    /**
     * Agenda uma reunião com a Empresa/Lead, atualiza a data/link, move para 'Reunião Marcada' e registra atividade.
     */
    public function scheduleCompanyMeeting(Request $request, int $companyId): RedirectResponse
    {
        $this->ensureAuthenticatedUser();

        $company = Company::withoutGlobalScopes()->findOrFail($companyId);

        $validated = $request->validate([
            'meeting_at' => 'required|date',
            'meeting_link' => 'nullable|url|max:500',
            'lead_id' => 'nullable|exists:leads,id',
            'qualification_notes' => 'nullable|string|max:5000',
        ]);

        // Procura estágio de 'Reunião Marcada' ou usa fallback
        $meetingStage = PipelineStage::withoutGlobalScopes()
            ->where(function ($q) {
                $q->where('name', 'like', '%Reunião%')
                  ->orWhere('name', 'like', '%Agendad%');
            })
            ->first();

        $stageId = $meetingStage?->id ?? $company->pipeline_stage_id;

        // Atualiza a empresa
        $customAttributes = $company->custom_attributes ?? [];
        if (!empty($validated['qualification_notes'])) {
            $customAttributes['qualification_notes'] = $validated['qualification_notes'];
        }

        $company->update([
            'meeting_at' => $validated['meeting_at'],
            'meeting_link' => $validated['meeting_link'] ?? null,
            'pipeline_stage_id' => $stageId,
            'status' => 'open',
            'custom_attributes' => $customAttributes,
        ]);

        // Sincroniza a etapa de todos os contatos da empresa
        $company->contacts()->update([
            'pipeline_stage_id' => $stageId,
        ]);

        // Formata data e hora para o log da atividade
        $dt = \Carbon\Carbon::parse($validated['meeting_at'])->format('d/m/Y \à\s H:i');
        $lead = isset($validated['lead_id']) ? Lead::find($validated['lead_id']) : $company->contacts->first();
        $leadName = $lead ? "{$lead->name} (" . ($lead->job_title ?? 'Decisor') . ")" : 'Decisor da Empresa';

        $desc = "📅 Reunião agendada para {$dt} com {$leadName}.";
        if (!empty($validated['meeting_link'])) {
            $desc .= "\n🔗 Link da sala: " . $validated['meeting_link'];
        }
        if (!empty($validated['qualification_notes'])) {
            $desc .= "\n\n📝 Resumo de Qualificação:\n" . $validated['qualification_notes'];
        }

        // Registra atividade na timeline
        Activity::create([
            'tenant_id' => $company->tenant_id,
            'company_id' => $company->id,
            'lead_id' => $lead?->id,
            'user_id' => Auth::id(),
            'type' => 'meeting',
            'call_outcome' => 'meeting_scheduled',
            'title' => 'Reunião Agendada',
            'description' => $desc,
        ]);

        return back()->with('success', "Reunião agendada com sucesso para {$dt}!");
    }

    /**
     * Exclui uma atividade do histórico da conta.
     */
    public function destroyActivity(int $activityId): RedirectResponse
    {
        $this->ensureAuthenticatedUser();

        $activity = Activity::findOrFail($activityId);
        $activity->delete();

        return back()->with('success', 'Atividade removida com sucesso!');
    }

    /**
     * Atualiza a etapa do lead via Drag & Drop ou seleção direta.
     */
    public function updateStage(Request $request, int $leadId): RedirectResponse
    {
        $this->ensureAuthenticatedUser();

        $validated = $request->validate([
            'pipeline_stage_id' => 'required|exists:pipeline_stages,id',
        ]);

        $lead = Lead::withoutGlobalScopes()->findOrFail($leadId);
        $lead->update([
            'pipeline_stage_id' => $validated['pipeline_stage_id'],
        ]);

        // Se o lead tiver empresa, atualiza a empresa também
        if ($lead->company_id) {
            $lead->company()->update(['pipeline_stage_id' => $validated['pipeline_stage_id']]);
        }

        return back();
    }

    /**
     * Retorna todas as mensagens do WhatsApp do lead em ordem cronológica.
     */
    public function messages(int $leadId): JsonResponse
    {
        $this->ensureAuthenticatedUser();

        $lead = Lead::withoutGlobalScopes()->findOrFail($leadId);

        $messages = $lead->messages()
            ->withoutGlobalScopes()
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'lead' => $lead,
            'messages' => $messages,
        ]);
    }

    /**
     * Envia uma mensagem manual direta do Kanban para o WhatsApp do Lead.
     */
    public function sendMessage(Request $request, int $leadId): JsonResponse|RedirectResponse
    {
        $this->ensureAuthenticatedUser();

        $validated = $request->validate([
            'text' => 'required|string|max:4000',
            'whatsapp_instance_id' => 'nullable|exists:whatsapp_instances,id',
        ]);

        $lead = Lead::withoutGlobalScopes()->findOrFail($leadId);

        $instance = isset($validated['whatsapp_instance_id'])
            ? WhatsappInstance::withoutGlobalScopes()->find($validated['whatsapp_instance_id'])
            : WhatsappInstance::withoutGlobalScopes()->where('tenant_id', $lead->tenant_id)->first();

        // Fallback automático para garantir que qualquer tenant possa operar sem travas
        if (! $instance) {
            $instance = WhatsappInstance::withoutGlobalScopes()->create([
                'tenant_id' => $lead->tenant_id,
                'instance_name' => 'Instância WhatsApp ' . ($lead->tenant_id ?? 'Principal'),
                'phone_number' => '5511999998888',
                'status' => 'connected',
                'daily_limit' => 100,
                'sent_today' => 0,
                'last_active_at' => now(),
            ]);
        }

        // Executa o envio síncrono para retorno imediato na tela
        SendWhatsappMessageJob::dispatchSync($lead, $instance, $validated['text']);

        if ($request->wantsJson()) {
            $latestMessage = Message::where('lead_id', $lead->id)->latest()->first();
            return response()->json([
                'success' => true,
                'message' => $latestMessage,
            ]);
        }

        return back();
    }

    /**
     * Simula o recebimento de uma mensagem do Lead via WhatsApp (Sandbox / Ambiente de Testes).
     * Registra como inbound, dispara a lógica de interrupção de cadência e move a conta para a etapa "Respondeu".
     */
    public function simulateReply(Request $request, int $leadId): JsonResponse
    {
        $this->ensureAuthenticatedUser();

        $validated = $request->validate([
            'text' => 'required|string|max:4000',
        ]);

        $lead = Lead::withoutGlobalScopes()->findOrFail($leadId);

        $instance = WhatsappInstance::withoutGlobalScopes()
            ->where('tenant_id', $lead->tenant_id)
            ->first();

        if (! $instance) {
            $instance = WhatsappInstance::withoutGlobalScopes()->create([
                'tenant_id' => $lead->tenant_id,
                'instance_name' => 'Instância WhatsApp ' . ($lead->tenant_id ?? 'Principal'),
                'phone_number' => '5511999998888',
                'status' => 'connected',
                'daily_limit' => 100,
                'sent_today' => 0,
                'last_active_at' => now(),
            ]);
        }

        // 1. Grava a mensagem inbound simulada
        $message = Message::withoutGlobalScopes()->create([
            'tenant_id' => $lead->tenant_id,
            'lead_id' => $lead->id,
            'whatsapp_instance_id' => $instance->id,
            'direction' => 'inbound',
            'body' => $validated['text'],
            'status' => 'read',
            'external_id' => 'sim_' . uniqid(),
            'sent_at' => now(),
        ]);

        // 2. Dispara a lógica de engajamento de cadência (pausa cadência e move empresa no Kanban)
        $cadenceResult = app(\App\Services\CadenceExecutionService::class)->handleLeadReply($lead, 'whatsapp', $validated['text']);

        // 3. Dispara o SDR Autônomo (se o piloto automático estiver ativo e o lead não estiver pausado)
        $autonomousResult = null;
        try {
            $autonomousResult = app(\App\Services\AutonomousAiResponderService::class)->processInbound($lead, $instance, $validated['text']);
        } catch (\Throwable $aiErr) {
            \Illuminate\Support\Facades\Log::error('[Autonomous AI] Erro na resposta autônoma: ' . $aiErr->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'cadence_result' => $cadenceResult,
            'autonomous_result' => $autonomousResult,
            'lead' => $lead->fresh(['stage']),
        ]);
    }

    /**
     * Cadastra um novo Lead rapidamente pelo Kanban.
     */
    public function storeLead(Request $request): RedirectResponse
    {
        $this->ensureAuthenticatedUser();

        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'pipeline_stage_id' => 'required|exists:pipeline_stages,id',
            'job_title' => 'nullable|string|max:100',
            'document' => 'nullable|string|max:30',
            'segment' => 'nullable|string|max:100',
            'custom_attributes' => 'nullable|array',
        ]);

        $cleanPhone = preg_replace('/[^0-9]/', '', $validated['phone']);
        $tenantId = $user?->tenant_id ?? 1;

        $company = null;
        if (! empty($validated['company_name'])) {
            $company = Company::withoutGlobalScopes()->firstOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'name' => $validated['company_name'],
                ],
                [
                    'pipeline_stage_id' => $validated['pipeline_stage_id'],
                    'assigned_user_id' => $user?->id,
                    'document' => $validated['document'] ?? null,
                    'segment' => $validated['segment'] ?? null,
                    'status' => 'open',
                    'custom_attributes' => $validated['custom_attributes'] ?? null,
                ]
            );

            // Se a empresa já existia mas não tinha documento/segmento, atualiza
            if ($company && empty($company->document) && ! empty($validated['document'])) {
                $company->document = $validated['document'];
                $company->save();
            }
        }

        Lead::withoutGlobalScopes()->create([
            'tenant_id' => $tenantId,
            'company_id' => $company?->id,
            'pipeline_stage_id' => $validated['pipeline_stage_id'],
            'assigned_user_id' => $user?->id,
            'name' => $validated['name'],
            'job_title' => $validated['job_title'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'phone' => $cleanPhone,
            'email' => $validated['email'] ?? null,
            'status' => 'open',
            'is_primary' => true,
        ]);

        return back();
    }

    /**
     * Importa múltiplos leads via planilha CSV.
     */
    public function importLeads(Request $request, \App\Services\LeadImportService $importer): RedirectResponse
    {
        $this->ensureAuthenticatedUser();

        $validated = $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
            'pipeline_stage_id' => 'required|exists:pipeline_stages,id',
        ]);

        $user = Auth::user();
        $tenantId = $user?->tenant_id ?? 1;

        $result = $importer->import(
            $validated['file'],
            $tenantId,
            (int) $validated['pipeline_stage_id'],
            $user?->id
        );

        if (! $result['success']) {
            return back()->withErrors(['file' => $result['message']]);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Atualiza os dados cadastrais, responsável e notas de qualificação do Lead.
     */
    public function updateLead(Request $request, int $leadId): RedirectResponse
    {
        $this->ensureAuthenticatedUser();

        $lead = Lead::withoutGlobalScopes()->findOrFail($leadId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:open,won,lost,unqualified',
            'pipeline_stage_id' => 'required|exists:pipeline_stages,id',
            'assigned_user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:5000',
        ]);

        $cleanPhone = preg_replace('/[^0-9]/', '', $validated['phone']);

        $customAttributes = $lead->custom_attributes ?? [];
        if (array_key_exists('notes', $validated)) {
            $customAttributes['notes'] = $validated['notes'];
        }

        $lead->update([
            'name' => $validated['name'],
            'company_name' => $validated['company_name'] ?? null,
            'phone' => $cleanPhone,
            'email' => $validated['email'] ?? null,
            'status' => $validated['status'],
            'pipeline_stage_id' => $validated['pipeline_stage_id'],
            'assigned_user_id' => $validated['assigned_user_id'] ?? null,
            'custom_attributes' => $customAttributes,
        ]);

        return back()->with('success', 'Lead e notas de qualificação atualizados com sucesso!');
    }

    /**
     * Remove um Lead e seu histórico de mensagens do sistema.
     */
    public function destroyLead(int $leadId): RedirectResponse
    {
        $this->ensureAuthenticatedUser();

        $lead = Lead::withoutGlobalScopes()->findOrFail($leadId);
        $lead->delete();

        return back()->with('success', 'Lead excluído com sucesso!');
    }
}
