<?php

namespace App\Models;

use App\Models\Traits\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cargo extends Model
{
    use BelongsToEmpresa; // Injeta o Global Scope do tenant!

    protected $fillable = [
        'nome',
        'descricao',
        'empresa_id',
        'departamento_id',
        'cbo',
        'senioridade',
        'cargo_superior_id',
    ];

    /**
     * O cargo pertence a um departamento (opcional).
     */
    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    /**
     * Relacionamento recursivo: O cargo superior (líder).
     */
    public function cargoSuperior(): BelongsTo
    {
        return $this->belongsTo(Cargo::class, 'cargo_superior_id');
    }

    /**
     * Relacionamento recursivo inverso: Cargos subordinados a este.
     */
    public function cargosSubordinados(): HasMany
    {
        return $this->hasMany(Cargo::class, 'cargo_superior_id');
    }

    /**
     * Um cargo possui vários colaboradores (usuários).
     */
    public function colaboradores(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
