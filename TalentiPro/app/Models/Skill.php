<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = [
        'competencia_id',
        'nome',
        'descricao',
    ];

    /**
     * Competência à qual esta skill pertence
     */
    public function competencia()
    {
        return $this->belongsTo(Competencia::class);
    }

    /**
     * Respostas de avaliação para esta skill
     */
    public function avaliacaoRespostas()
    {
        return $this->hasMany(AvaliacaoResposta::class);
    }
}
