<?php

namespace App\Models;

use App\Enums\StatusAvaliacao;
use App\Models\Traits\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Avaliacao extends Model
{
    use HasFactory, BelongsToEmpresa;

    protected $table = 'avaliacoes';

    protected $fillable = [
        'empresa_id',
        'grau',
        'titulo',
        'descricao',
        'filtros_aplicados',
        'mediadores',
        'data_inicio',
        'data_fim',
        'status',
        'created_by',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'status' => StatusAvaliacao::class,
        'filtros_aplicados' => 'array',
        'mediadores' => 'array',
    ];

    public function criador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function competencias(): BelongsToMany
    {
        return $this->belongsToMany(Competencia::class, 'avaliacao_competencias', 'avaliacao_id', 'competencia_id')
                    ->withTimestamps();
    }

    public function participantes(): HasMany
    {
        return $this->hasMany(AvaliacaoParticipante::class, 'avaliacao_id');
    }

    public function fechamentos(): HasMany
    {
        return $this->hasMany(AvaliacaoFechamento::class, 'avaliacao_id');
    }
}
