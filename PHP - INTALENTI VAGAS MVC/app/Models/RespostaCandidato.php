<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RespostaCandidato extends Model
{
    use HasFactory;

    protected $table = 'respostas_candidato';

    protected $fillable = [
        'candidatura_id',
        'pergunta_id',
        'resposta',
    ];

    public function candidatura(): BelongsTo
    {
        return $this->belongsTo(Candidatura::class);
    }

    public function pergunta(): BelongsTo
    {
        return $this->belongsTo(PerguntaVaga::class, 'pergunta_id');
    }
}
