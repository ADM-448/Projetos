<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Candidatura extends Model
{
    use HasFactory;

    protected $fillable = [
        'vaga_id',
        'candidato_id',
        'status', // 'pendente', 'analisando', 'aprovado', 'reprovado', 'cancelada'
        'data_candidatura',
    ];

    protected function casts(): array
    {
        return [
            'data_candidatura' => 'datetime',
        ];
    }

    public function vaga(): BelongsTo
    {
        return $this->belongsTo(Vaga::class);
    }

    public function candidato(): BelongsTo
    {
        return $this->belongsTo(Candidato::class);
    }

    public function respostas(): HasMany
    {
        return $this->hasMany(RespostaCandidato::class);
    }

    public function chat(): HasOne
    {
        return $this->hasOne(Chat::class);
    }
}
