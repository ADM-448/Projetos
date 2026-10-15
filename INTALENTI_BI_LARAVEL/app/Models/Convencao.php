<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Convencao extends Model
{
    use HasFactory;

    protected $table = 'convencoes';

    protected $fillable = [
        'sindicato_nome',
        'ano_vigencia',
        'data_base',
        'reajuste_geral_percentual'
    ];

    public function regras()
    {
        return $this->hasMany(ConvencaoRegra::class);
    }
}
