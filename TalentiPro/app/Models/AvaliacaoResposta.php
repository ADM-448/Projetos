<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvaliacaoResposta extends Model
{

    protected $table = 'avaliacao_respostas';
    protected $fillable = [
        'avaliacao_participante_id',
        'skill_id',
        'nota',
        'comentario',
    ];

    public function avaliacaoParticipante()
    {
        return $this->belongsTo(AvaliacaoParticipante::class);
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }
}
