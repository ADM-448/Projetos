<?php

namespace App\Models;

use App\Enums\TipoAvaliacao;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AvaliacaoParticipante extends Model
{
    use HasFactory;

    protected $table = 'avaliacao_participantes';

    protected $fillable = [
        'avaliacao_id',
        'avaliado_id',
        'avaliador_id',
        'tipo',
        'status',
        'observacao',
    ];

    protected $casts = [
        'tipo' => TipoAvaliacao::class,
    ];

    public function avaliacao(): BelongsTo
    {
        return $this->belongsTo(Avaliacao::class);
    }

    public function avaliado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'avaliado_id');
    }

    public function avaliador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'avaliador_id');
    }

    public function respostas(): HasMany
    {
        return $this->hasMany(AvaliacaoResposta::class, 'avaliacao_participante_id');
    }
}
