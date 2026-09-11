<?php

namespace App\Models\Traits;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToEmpresa
{
    /**
     * O Laravel executa automaticamente métodos boot[NomeDaTrait] ao inicializar o Model.
     */
    protected static function bootBelongsToEmpresa(): void
    {
        // 1. Aplica o filtro automático em todas as consultas (Global Scope)
        static::addGlobalScope('empresa', function (Builder $builder) {
            if (Auth::check() && Auth::user()->empresa_id !== null) {
                $builder->where($builder->getQuery()->from.'.empresa_id', '=', Auth::user()->empresa_id);
            }
        });

        // 2. Preenche automaticamente o empresa_id ao cadastrar novos registros
        static::creating(function ($model) {
            if (Auth::check() && Auth::user()->empresa_id !== null) {
                // Só preenche se não tiver sido definido manualmente antes
                if (empty($model->empresa_id)) {
                    $model->empresa_id = Auth::user()->empresa_id;
                }
            }
        });
    }

    /**
     * Define o relacionamento padrão com a Empresa.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
