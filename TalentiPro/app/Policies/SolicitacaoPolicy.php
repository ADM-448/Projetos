<?php

namespace App\Policies;

use App\Models\Solicitacao;
use App\Models\User;
use App\Enums\UserRole;

class SolicitacaoPolicy
{
    /**
     * Admin bypassa todas as policies.
     */
    public function before(User $user): ?bool
    {
        if ($user->perfil_acesso === UserRole::ADMIN) {
            return true;
        }
        return null;
    }

    /**
     * Colaboradores e gestores podem ver solicitações.
     * Master vê todas da empresa (garantido pelo Global Scope).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Qualquer usuário pode criar solicitação se for para reabrir sua própria avaliação.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Regras para aprovar uma solicitação:
     * - Master: pode aprovar qualquer solicitação (inclusive DISC)
     * - Gestor: pode aprovar apenas se for o criador da avaliação referenciada
     */
    public function aprovar(User $user, Solicitacao $solicitacao): bool
    {
        // Master pode aprovar qualquer solicitação da empresa
        if ($user->perfil_acesso === UserRole::MASTER) {
            return true;
        }

        // Gestor pode aprovar apenas se for o criador da avaliação (não se aplica ao DISC)
        if ($user->perfil_acesso === UserRole::GESTOR && $solicitacao->avaliacao) {
            return $solicitacao->avaliacao->created_by === $user->id;
        }

        return false;
    }

    /**
     * Mesmas regras de aprovação valem para recusa.
     */
    public function recusar(User $user, Solicitacao $solicitacao): bool
    {
        return $this->aprovar($user, $solicitacao);
    }
}
