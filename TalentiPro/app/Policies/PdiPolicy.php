<?php

namespace App\Policies;

use App\Models\Pdi;
use App\Models\PdiGrupo;
use App\Models\User;

class PdiPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Controlado pelo escopo do tenant e filtrado no controller
    }

    /**
     * Determine whether the user can view the model.
     *
     * Um usuário pode ver um PDI se:
     * - For o próprio colaborador do PDI, OU
     * - For o gestor registrado no campo gestor_id do PDI, OU
     * - For o gestor direto do colaborador (colaborador.gestor_id), mesmo que
     *   o PDI tenha sido criado pelo Master/RH — corrige o 403 neste cenário, OU
     * - For admin ou master da empresa.
     */
    public function view(User $user, Pdi $pdi): bool
    {
        $perfil = $user->perfil_acesso->value ?? $user->perfil_acesso;

        if ($perfil !== 'admin' && $user->empresa_id != $pdi->empresa_id) {
            return false;
        }

        // Verifica se o usuário é gestor direto do colaborador deste PDI
        $eGestorDireto = $pdi->colaborador?->gestor_id === $user->id;

        return $pdi->colaborador_id === $user->id
            || $pdi->gestor_id === $user->id
            || $eGestorDireto
            || in_array($perfil, ['admin', 'master']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $perfil = $user->perfil_acesso->value ?? $user->perfil_acesso;
        return in_array($perfil, ['admin', 'master', 'gestor']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Pdi $pdi): bool
    {
        $perfil = $user->perfil_acesso->value ?? $user->perfil_acesso;

        if ($perfil !== 'admin' && $user->empresa_id != $pdi->empresa_id) {
            return false;
        }

        $perfil = $user->perfil_acesso->value ?? $user->perfil_acesso;

        if ($perfil === 'gestor') {
            return $pdi->gestor_id === $user->id;
        }

        return in_array($perfil, ['admin', 'master']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pdi $pdi): bool
    {
        $perfil = $user->perfil_acesso->value ?? $user->perfil_acesso;

        if ($perfil !== 'admin' && $user->empresa_id != $pdi->empresa_id) {
            return false;
        }

        $perfil = $user->perfil_acesso->value ?? $user->perfil_acesso;

        if ($perfil === 'gestor') {
            return $pdi->gestor_id === $user->id;
        }

        return in_array($perfil, ['admin', 'master']);
    }

    /**
     * Determine whether the user can toggle action status.
     */
    public function toggleAcao(User $user, Pdi $pdi): bool
    {
        $perfil = $user->perfil_acesso->value ?? $user->perfil_acesso;

        if ($perfil !== 'admin' && $user->empresa_id != $pdi->empresa_id) {
            return false;
        }

        // Apenas o próprio avaliado pode marcar suas tarefas como concluídas
        return $pdi->colaborador_id == $user->id;
    }

    /**
     * Determine whether the user can comment on the PDI.
     *
     * Mesma lógica do view(): gestor direto do colaborador também pode comentar,
     * mesmo que não seja quem criou o PDI.
     */
    public function comentar(User $user, Pdi $pdi): bool
    {
        $perfil = $user->perfil_acesso->value ?? $user->perfil_acesso;

        if ($perfil !== 'admin' && $user->empresa_id != $pdi->empresa_id) {
            return false;
        }

        $eGestorDireto = $pdi->colaborador?->gestor_id === $user->id;

        return $pdi->colaborador_id === $user->id
            || $pdi->gestor_id === $user->id
            || $eGestorDireto
            || in_array($perfil, ['admin', 'master']);
    }

    /**
     * Determine whether the user can delete the PdiGrupo.
     */
    public function deleteGrupo(User $user, PdiGrupo $grupo): bool
    {
        $perfil = $user->perfil_acesso->value ?? $user->perfil_acesso;

        if ($perfil !== 'admin' && $user->empresa_id != $grupo->empresa_id) {
            return false;
        }

        $perfil = $user->perfil_acesso->value ?? $user->perfil_acesso;

        if ($perfil === 'gestor') {
            return $grupo->gestor_id === $user->id;
        }

        return in_array($perfil, ['admin', 'master']);
    }
}
