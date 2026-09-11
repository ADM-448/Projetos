<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvaliacaoResposta extends Model
{
    use HasFactory;

    protected $table = 'avaliacao_respostas';

    protected $fillable = [
        'avaliacao_participante_id',
        'skill_id',
        'nota',
    ];

    public function participante(): BelongsTo
    {
        return $this->belongsTo(AvaliacaoParticipante::class, 'avaliacao_participante_id');
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}
