<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Competencia;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CompetenciaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->perfil_acesso, [UserRole::MASTER, UserRole::GESTOR, UserRole::CONSULTOR]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Competencia $competencia): bool
    {
        return $this->viewAny($user) && $user->empresa_id === $competencia->empresa_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->perfil_acesso, [UserRole::MASTER, UserRole::GESTOR]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Competencia $competencia): bool
    {
        return $this->create($user) && $user->empresa_id === $competencia->empresa_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Competencia $competencia): bool
    {
        return $this->update($user, $competencia);
    }
}
