<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    /**
     * Resolve o ID do tenant em tempo de execução:
     * 1. Se houver 'active_tenant_id' na sessão e usuário for superadmin/admin, utiliza o tenant selecionado.
     * 2. Caso contrário, utiliza o tenant_id do usuário autenticado.
     */
    public static function resolveCurrentTenantId(): ?int
    {
        if (session()->has('active_tenant_id')) {
            $activeId = (int) session('active_tenant_id');
            if ($activeId > 0 && auth()->check() && in_array(auth()->user()->role, ['superadmin', 'admin'])) {
                return $activeId;
            }
        }

        if (auth()->check() && auth()->user()->tenant_id) {
            return (int) auth()->user()->tenant_id;
        }

        return null;
    }

    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant_id', function (Builder $builder) {
            $tenantId = static::resolveCurrentTenantId();
            if ($tenantId) {
                $builder->where($builder->getModel()->getTable().'.tenant_id', $tenantId);
            }
        });

        static::creating(function (Model $model) {
            if (! $model->tenant_id) {
                $model->tenant_id = static::resolveCurrentTenantId();
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
