<?php

namespace App\Models;

use App\Models\Traits\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competencia extends Model
{
    use HasFactory, BelongsToEmpresa;

    protected $fillable = ['nome', 'descricao', 'empresa_id']; // Adicione 'tipo' se tiver no banco

    // Relacionamento com Skills (Itens da competência)
    public function skills()
    {
        return $this->hasMany(Skill::class);
    }

    // Relacionamento com Cargos (Muitos para Muitos)
    public function cargos()
    {
        return $this->belongsToMany(Cargo::class, 'cargo_competencia');
    }
}
