<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerguntaVaga extends Model
{
    use HasFactory;

    protected $table = 'perguntas_vaga';

    protected $fillable = [
        'vaga_id',
        'texto_pergunta',
        'eliminatoria',
    ];

    protected function casts(): array
    {
        return [
            'eliminatoria' => 'boolean',
        ];
    }

    public function vaga(): BelongsTo
    {
        return $this->belongsTo(Vaga::class);
    }

    public function respostas(): HasMany
    {
        return $this->hasMany(RespostaCandidato::class, 'pergunta_id');
    }
}
