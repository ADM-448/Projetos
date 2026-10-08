<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanManageUser
{
    /**
     * Intercepta e valida operações de gerenciamento de usuários (store, update, destroy).
     * Impede expressamente que Gestores alterem ou excluam Administradores e Super Admins.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentUser = $request->user();

        if (! $currentUser) {
            return redirect()->route('login');
        }

        // SDRs não têm qualquer permissão de gerenciamento de usuários
        if (! $currentUser->isSuperAdmin() && ! $currentUser->isManager()) {
            abort(403, 'Acesso negado: seu perfil não tem permissão para gerenciar usuários.');
        }

        // Se for Super Admin, tem permissão plena
        if ($currentUser->isSuperAdmin()) {
            return $next($request);
        }

        // ================= REGRAS RESTRITIVAS PARA GESTORES =================

        // 1. Obter o usuário alvo da rota (se for update ou destroy)
        /** @var User|null $targetUser */
        $targetUser = $request->route('user');
        if (is_numeric($targetUser) || is_string($targetUser)) {
            $targetUser = User::withoutGlobalScopes()->find($targetUser);
        }

        // Operações sobre um usuário existente (UPDATE ou DESTROY)
        if ($targetUser instanceof User) {
            // Impedir auto-exclusão
            if ($request->isMethod('delete') && $targetUser->id === $currentUser->id) {
                return redirect()->back()->with('error', 'Você não pode excluir sua própria conta de acesso.');
            }

            // SEGURANÇA MÁXIMA: Gestor NUNCA pode excluir nem editar Administradores ou Super Admins
            if ($targetUser->isSuperAdmin() || $targetUser->isAdmin()) {
                return redirect()->back()->with('error', 'Permissão negada: Gestores não podem alterar ou remover contas de Administrador.');
            }

            // O alvo DEVE pertencer à mesma base (tenant) do gestor
            if ($targetUser->tenant_id !== $currentUser->tenant_id) {
                return redirect()->back()->with('error', 'Permissão negada: você só pode gerenciar colaboradores da sua própria base.');
            }
        }

        // 2. Criação (POST) ou Atualização (PUT/PATCH): validar os campos submetidos
        if ($request->isMethod('post') || $request->isMethod('put') || $request->isMethod('patch')) {
            // Gestor não pode definir is_superadmin
            if ($request->boolean('is_superadmin')) {
                return redirect()->back()->with('error', 'Permissão negada: Gestores não podem conceder privilégios de Super Admin.');
            }

            // Gestor não pode definir role como 'admin'
            if ($request->input('role') === 'admin') {
                return redirect()->back()->with('error', 'Permissão negada: Gestores não podem criar ou promover usuários a Administradores.');
            }

            // Forçar o tenant_id para o tenant do próprio gestor
            $request->merge([
                'tenant_id' => $currentUser->tenant_id,
                'is_superadmin' => false,
            ]);
        }

        return $next($request);
    }
}
