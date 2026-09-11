<?php

namespace App\Policies;

use App\Models\Avaliacao;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AvaliacaoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->perfil_acesso->value, ['admin', 'master', 'gestor']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Avaliacao $avaliacao): bool
    {
        if ($user->empresa_id != $avaliacao->empresa_id) return false;
        
        $perfil = $user->perfil_acesso->value ?? $user->perfil_acesso;
        
        if ($perfil === 'gestor') {
            if ($avaliacao->created_by === $user->id) {
                return true;
            }
            if (is_array($avaliacao->mediadores) && in_array($user->id, $avaliacao->mediadores)) {
                return true;
            }
        }

        return in_array($perfil, ['admin', 'master']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->perfil_acesso->value, ['admin', 'master', 'gestor']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Avaliacao $avaliacao): bool
    {
        if ($user->empresa_id != $avaliacao->empresa_id) return false;

        $perfil = $user->perfil_acesso->value ?? $user->perfil_acesso;
        
        if ($perfil === 'gestor') {
            return $avaliacao->created_by === $user->id;
        }

        return in_array($perfil, ['admin', 'master']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Avaliacao $avaliacao): bool
    {
        if ($user->empresa_id != $avaliacao->empresa_id) return false;

        $perfil = $user->perfil_acesso->value ?? $user->perfil_acesso;
        
        if ($perfil === 'gestor') {
            return $avaliacao->created_by === $user->id;
        }

        return in_array($perfil, ['admin', 'master']);
    }

    /**
     * Determine whether the user can mediate the model.
     */
    public function mediar(User $user, Avaliacao $avaliacao): bool
    {
        if ($user->empresa_id != $avaliacao->empresa_id) return false;
        
        if (is_array($avaliacao->mediadores) && in_array($user->id, $avaliacao->mediadores)) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Avaliacao $avaliacao): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Avaliacao $avaliacao): bool
    {
        return false;
    }
}
