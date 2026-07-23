<?php

namespace App\Models\Traits;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

trait BelongsToEmpresa
{
    /**
     * O "booting" do model.
     */
    protected static function bootBelongsToEmpresa()
    {
        // 1. Adiciona Global Scope para filtrar por empresa
        static::addGlobalScope('empresa', function (Builder $builder) {
            // Evita recursão infinita verificando se temos um usuário autenticado sem invocar o User model
            if (app()->runningInConsole() || !Auth::hasUser()) {
                return;
            }

            // Acessa o usuário já carregado na sessão/token
            $user = Auth::user();

            if (!$user) {
                return;
            }

            // Admin (perfil_acesso = 'admin') vê TODAS as empresas
            if ($user->perfil_acesso === 'admin') {
                return; // Não aplica filtro
            }
            
            // Outros perfis: filtra pela empresa do usuário
            if ($user->empresa_id) {
                $builder->where('empresa_id', $user->empresa_id);
            }
        });

        // 2. Evento "creating": preenche o empresa_id automaticamente
        static::creating(function ($model) {
            // Se o campo já estiver preenchido, não sobrescreve
            if (!isset($model->empresa_id)) {
                if (Auth::check() && Auth::user()->empresa_id) {
                    $model->empresa_id = Auth::user()->empresa_id;
                }
            }
        });
    }

    /**
     * Relacionamento com a Empresa
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
