<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    protected $fillable = [
        'nome',
        'admissao',
        'cargo_id',
        'salario_atual',
        'tempo_de_casa_meses',
        'salario_mais_proximo_tabela',
        'nivel_mais_proximo',
    ];

    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }
}
