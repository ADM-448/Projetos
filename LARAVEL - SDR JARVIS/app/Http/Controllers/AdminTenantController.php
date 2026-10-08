<?php

namespace App\Http\Controllers;

use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminTenantController extends Controller
{
    /**
     * Garante que o usuário atual seja Super Admin (gestão de tenants e múltiplos CRMs).
     */
    protected function authorizeSuperAdmin(): void
    {
        $user = Auth::user();
        if (! $user || ! $user->isSuperAdmin()) {
            abort(403, 'Acesso restrito ao Painel de Super Administrador.');
        }
    }

    /**
     * Garante que o usuário atual tenha privilégios para gerenciar colaboradores (Super Admin ou Gestor).
     */
    protected function authorizeUserManagement(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isSuperAdmin() && ! $user->isManager())) {
            abort(403, 'Acesso restrito a Gestores Comerciais e Administradores.');
        }
    }

    /**
     * Alterna o perfil de CRM ativo do Super Admin.
     */
    public function switchTenant(Request $request, $tenantId): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $tenant = Tenant::findOrFail($tenantId);
        session(['active_tenant_id' => $tenant->id]);

        return redirect()->back()->with('success', "Alternado para o CRM: {$tenant->name}");
    }

    /**
     * Cria um novo Perfil de CRM (Tenant) com Pipeline, Etapas e IA exclusiva.
     */
    public function storeTenant(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'document' => 'nullable|string|max:50',
            'ai_name' => 'nullable|string|max:100',
            'ai_persona' => 'nullable|string|max:255',
            'ai_model' => 'nullable|string|max:50',
            'ai_prompt' => 'nullable|string|max:2000',
            'ai_api_key' => 'nullable|string|max:255',
        ]);

        $slug = ! empty($validated['slug']) 
            ? Str::slug($validated['slug']) 
            : Str::slug($validated['name']);

        $settings = [
            'ai_name' => $validated['ai_name'] ?: 'Agente SDR ' . $validated['name'],
            'ai_persona' => $validated['ai_persona'] ?: 'Consultor Comercial B2B Especialista em Prospecção',
            'ai_model' => $validated['ai_model'] ?: 'gpt-4o-mini',
            'ai_prompt' => $validated['ai_prompt'] ?: 'Você é o SDR da operação comercial. Conecte-se com o lead e busque qualificar o interesse para agendar uma demonstração.',
            'ai_api_key' => $validated['ai_api_key'] ?: null,
            'timezone' => 'America/Sao_Paulo',
            'max_instances' => 5,
        ];

        $tenant = Tenant::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'document' => $validated['document'] ?? null,
            'status' => 'active',
            'settings' => $settings,
        ]);

        // Cria o Pipeline de Vendas padrão deste novo tenant
        $pipeline = Pipeline::create([
            'tenant_id' => $tenant->id,
            'name' => 'Pipeline Comercial B2B',
            'is_default' => true,
        ]);

        $defaultStages = [
            ['name' => 'Base Fria / Descoberta', 'position' => 1, 'color' => '#5f5c6e'],
            ['name' => '1º Toque WhatsApp', 'position' => 2, 'color' => '#3b82f6'],
            ['name' => 'Em Conversa / Qualificação', 'position' => 3, 'color' => '#8b5cf6'],
            ['name' => 'Reunião Agendada', 'position' => 4, 'color' => '#ffe228'],
            ['name' => 'Fechado / Ganho', 'position' => 5, 'color' => '#10b981'],
        ];

        foreach ($defaultStages as $stage) {
            PipelineStage::create([
                'tenant_id' => $tenant->id,
                'pipeline_id' => $pipeline->id,
                'name' => $stage['name'],
                'position' => $stage['position'],
                'color' => $stage['color'],
            ]);
        }

        return redirect()->back()->with('success', "Novo CRM '{$tenant->name}' criado com sucesso e pipeline inicial ativado!");
    }

    /**
     * Atualiza as configurações e IA de um Perfil de CRM.
     */
    public function updateTenant(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'document' => 'nullable|string|max:50',
            'status' => 'required|in:active,suspended,cancelled',
            'ai_name' => 'nullable|string|max:100',
            'ai_persona' => 'nullable|string|max:255',
            'ai_model' => 'nullable|string|max:50',
            'ai_prompt' => 'nullable|string|max:2000',
            'ai_api_key' => 'nullable|string|max:255',
        ]);

        $currentSettings = $tenant->settings ?? [];

        $settings = array_merge($currentSettings, [
            'ai_name' => $validated['ai_name'] ?: ($currentSettings['ai_name'] ?? 'JARVIS SDR Agent'),
            'ai_persona' => $validated['ai_persona'] ?: ($currentSettings['ai_persona'] ?? 'Consultor Comercial'),
            'ai_model' => $validated['ai_model'] ?: ($currentSettings['ai_model'] ?? 'gpt-4o-mini'),
            'ai_prompt' => $validated['ai_prompt'] ?: ($currentSettings['ai_prompt'] ?? ''),
        ]);

        if (! empty($validated['ai_api_key'])) {
            $settings['ai_api_key'] = $validated['ai_api_key'];
        }

        $tenant->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ? Str::slug($validated['slug']) : $tenant->slug,
            'description' => $validated['description'] ?? null,
            'document' => $validated['document'] ?? null,
            'status' => $validated['status'],
            'settings' => $settings,
        ]);

        return redirect()->back()->with('success', "Configurações e IA do CRM '{$tenant->name}' atualizadas!");
    }

    /**
     * Exclui um tenant (se não for o único).
     */
    public function destroyTenant(Tenant $tenant): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        if (Tenant::count() <= 1) {
            return redirect()->back()->with('error', 'Não é possível excluir o único Perfil de CRM ativo da plataforma.');
        }

        $name = $tenant->name;
        if (session('active_tenant_id') == $tenant->id) {
            session()->forget('active_tenant_id');
        }

        $tenant->delete();

        return redirect()->back()->with('success', "CRM '{$name}' removido.");
    }

    /**
     * Cria e delega acesso a um novo usuário / colaborador.
     * Super Admin: pode criar em qualquer tenant e com qualquer perfil.
     * Gestor: cria colaboradores exclusivamente vinculados à sua base (tenant).
     */
    public function storeUser(Request $request): RedirectResponse
    {
        $this->authorizeUserManagement();
        $currentUser = Auth::user();
        $isManagerOnly = $currentUser->isManager() && ! $currentUser->isSuperAdmin();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
        ];

        if ($isManagerOnly) {
            $rules['role'] = 'required|in:sdr,manager';
            $tenantId = $currentUser->tenant_id;
            $isSuperAdmin = false;
        } else {
            $rules['tenant_id'] = 'required|exists:tenants,id';
            $rules['role'] = 'required|in:admin,manager,sdr';
            $rules['is_superadmin'] = 'nullable|boolean';
            $tenantId = $request->input('tenant_id');
            $isSuperAdmin = ! empty($request->input('is_superadmin'));
        }

        $rules['sip_extension'] = 'nullable|string|max:50';
        $rules['sip_secret'] = 'nullable|string|max:255';

        $validated = $request->validate($rules);

        $userData = [
            'tenant_id' => $tenantId,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_superadmin' => $isSuperAdmin,
            'is_active' => true,
            'sip_extension' => $validated['sip_extension'] ?? null,
        ];

        if (! empty($validated['sip_secret'])) {
            $userData['sip_secret'] = $validated['sip_secret'];
        }

        $user = User::create($userData);

        return redirect()->back()->with('success', "Colaborador '{$user->name}' adicionado à equipe com sucesso!");
    }

    /**
     * Atualiza dados e permissões de um usuário.
     */
    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $this->authorizeUserManagement();
        $currentUser = Auth::user();
        $isManagerOnly = $currentUser->isManager() && ! $currentUser->isSuperAdmin();

        // O Gestor só pode alterar membros de sua própria base
        if ($isManagerOnly && $user->tenant_id !== $currentUser->tenant_id) {
            abort(403, 'Você só tem permissão para gerenciar colaboradores da sua própria base.');
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'is_active' => 'nullable|boolean',
            'password' => 'nullable|string|min:6',
            'sip_extension' => 'nullable|string|max:50',
            'sip_secret' => 'nullable|string|max:255',
        ];

        if ($isManagerOnly) {
            $rules['role'] = 'required|in:sdr,manager';
            $tenantId = $user->tenant_id;
            $isSuperAdmin = false;
        } else {
            $rules['tenant_id'] = 'required|exists:tenants,id';
            $rules['role'] = 'required|in:admin,manager,sdr';
            $rules['is_superadmin'] = 'nullable|boolean';
            $tenantId = $request->input('tenant_id', $user->tenant_id);
            $isSuperAdmin = ! empty($request->input('is_superadmin'));
        }

        $validated = $request->validate($rules);

        $data = [
            'tenant_id' => $tenantId,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_superadmin' => $isSuperAdmin,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : true,
            'sip_extension' => $validated['sip_extension'] ?? $user->sip_extension,
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        if (! empty($validated['sip_secret'])) {
            $data['sip_secret'] = $validated['sip_secret'];
        }

        $user->update($data);

        return redirect()->back()->with('success', "Dados do colaborador '{$user->name}' atualizados!");
    }

    /**
     * Remove um usuário / colaborador da base.
     */
    public function destroyUser(User $user): RedirectResponse
    {
        $this->authorizeUserManagement();
        $currentUser = Auth::user();

        if ($user->id === $currentUser->id) {
            return redirect()->back()->with('error', 'Você não pode excluir seu próprio usuário logado.');
        }

        // O Gestor só pode remover usuários de sua própria base
        if ($currentUser->isManager() && ! $currentUser->isSuperAdmin()) {
            if ($user->tenant_id !== $currentUser->tenant_id) {
                abort(403, 'Você só pode remover colaboradores da sua base.');
            }
            if ($user->isSuperAdmin()) {
                abort(403, 'Você não tem permissão para remover administradores globais.');
            }
        }

        $name = $user->name;
        $user->delete();

        return redirect()->back()->with('success', "Colaborador '{$name}' removido da equipe.");
    }
}
