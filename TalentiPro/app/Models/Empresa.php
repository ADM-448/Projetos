<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;
    protected $fillable = [
        'nome',
        'cnpj',
        'razao_social',
        'email',
        'telefone',
        'logo',
        'status',
        'ativo',
        'max_usuarios',
        'data_contratacao',
    ];

    protected $casts = [
        'data_contratacao' => 'date',
    ];

    /**
     * Usuários da empresa
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Departamentos da empresa
     */
    public function departamentos()
    {
        return $this->hasMany(Departamento::class);
    }

    /**
     * Cargos da empresa
     */
    public function cargos()
    {
        return $this->hasMany(Cargo::class);
    }

    /**
     * Competências da empresa
     */
    public function competencias()
    {
        return $this->hasMany(Competencia::class);
    }
}
