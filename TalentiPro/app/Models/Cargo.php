<?php

namespace App\Models;

use App\Models\Traits\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    use BelongsToEmpresa;

    protected $fillable = [
        'empresa_id',
        'nome',
        'descricao',
        'cbo',               // Certifique-se que sua tabela 'cargos' tem essa coluna
        'departamento_id',
        'cargo_superior_id', // Certifique-se que sua tabela 'cargos' tem essa coluna
        'senioridade',       // Certifique-se que sua tabela 'cargos' tem essa coluna
    ];

    // --- SEUS RELACIONAMENTOS ORIGINAIS (MANTENHA TODOS) ---

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function cargoSuperior()
    {
        return $this->belongsTo(Cargo::class, 'cargo_superior_id');
    }

    public function subordinados()
    {
        return $this->hasMany(Cargo::class, 'cargo_superior_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function competencias()
    {
        // Atenção: Isso exige que a tabela pivô se chame 'cargo_competencia'
        return $this->belongsToMany(Competencia::class, 'cargo_competencia');
    }

    public function hardskills()
    {
        return $this->hasMany(CargoHardskill::class);
    }
}
