<?php

namespace App\Models;

use App\Models\Traits\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    use BelongsToEmpresa;

    protected $fillable = [
        'empresa_id',
        'nome',
        'gestor_id',
    ];

    /**
     * Gestor responsável pelo departamento
     */
    public function gestor()
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    /**
     * Colaboradores do departamento
     */
    public function colaboradores()
    {
        return $this->hasMany(User::class, 'departamento_id');
    }
    // Relacionamento: Um departamento tem muitos Colaboradores (Users)
    // <--- O ERRO ESTAVA AQUI (FALTAVA ISSO)
    public function users()
    {
        return $this->hasMany(User::class);
    }
    /**
     * Cargos disponíveis no departamento
     */
    public function cargos()
    {
        return $this->hasMany(Cargo::class);
    }
}
