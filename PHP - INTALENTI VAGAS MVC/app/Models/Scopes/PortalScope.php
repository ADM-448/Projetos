<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class PortalScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $portalId = config('portal.current_id');

        if ($portalId && !app()->runningInConsole()) {
            $builder->where($model->qualifyColumn('portal_id'), $portalId);
        }
    }
}
