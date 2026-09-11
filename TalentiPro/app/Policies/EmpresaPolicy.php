<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;

class EmpresaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->perfil_acesso === UserRole::ADMIN;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Empresa $empresa): bool
    {
        // Admin pode ver todas. Master pode ver a própria empresa.
        if ($user->perfil_acesso === UserRole::ADMIN) {
            return true;
        }

        return $user->perfil_acesso === UserRole::MASTER && $user->empresa_id === $empresa->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->perfil_acesso === UserRole::ADMIN;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Empresa $empresa): bool
    {
        // Admin pode atualizar. Master poderia atualizar os próprios dados se permitido futuramente.
        // Por ora, vamos restringir ao Admin.
        return $user->perfil_acesso === UserRole::ADMIN;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Empresa $empresa): bool
    {
        return $user->perfil_acesso === UserRole::ADMIN;
    }
}
