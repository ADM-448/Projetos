<?php

namespace App\Models;

use App\Models\Traits\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departamento extends Model
{
    use HasFactory;
    use BelongsToEmpresa; // Injeta o Global Scope do tenant!

    protected $fillable = [
        'nome',
        'descricao',
        'empresa_id',
        'gestor_id',
    ];

    /**
     * O gestor do departamento é um Usuário (User).
     */
    public function gestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    /**
     * Um departamento possui vários cargos.
     */
    public function cargos(): HasMany
    {
        return $this->hasMany(Cargo::class);
    }

    /**
     * Um departamento possui vários colaboradores (usuários).
     */
    public function colaboradores(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
