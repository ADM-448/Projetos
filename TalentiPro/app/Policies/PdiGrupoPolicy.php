<?php

namespace App\Policies;

use App\Models\PdiGrupo;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PdiGrupoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can delete the PdiGrupo.
     */
    public function delete(User $user, PdiGrupo $pdiGrupo): bool
    {
        $perfil = $user->perfil_acesso->value ?? $user->perfil_acesso;

        if ($perfil !== 'admin' && $user->empresa_id != $pdiGrupo->empresa_id) {
            return false;
        }

        $perfil = $user->perfil_acesso->value ?? $user->perfil_acesso;

        if ($perfil === 'gestor') {
            return $pdiGrupo->gestor_id === $user->id;
        }

        return in_array($perfil, ['admin', 'master']);
    }

    /**
     * Determine whether the user can update the PdiGrupo.
     */
    public function update(User $user, PdiGrupo $pdiGrupo): bool
    {
        return $this->delete($user, $pdiGrupo); // Mesma regra da exclusão
    }
}
