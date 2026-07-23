<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClimateSurvey extends Model
{
    use HasFactory, BelongsToEmpresa;

    protected $table = 'climate_surveys';

    protected $fillable = [
        'empresa_id',
        'titulo',
        'descricao',
        'data_inicio',
        'data_fim',
        'status',
        'visibilidade_gestor',
        'resultado_liberado_colaboradores',
        'created_by',
    ];

    protected $casts = [
        'data_inicio'                     => 'date',
        'data_fim'                        => 'date',
        'resultado_liberado_colaboradores' => 'boolean',
    ];

    // --- Scopes ---

    public function scopeAtiva(Builder $query): Builder
    {
        return $query->where('status', 'ativa');
    }

    public function scopeEncerrada(Builder $query): Builder
    {
        return $query->where('status', 'encerrada');
    }

    public function scopeRascunho(Builder $query): Builder
    {
        return $query->where('status', 'rascunho');
    }

    // --- Relacionamentos ---

    public function questions(): HasMany
    {
        return $this->hasMany(ClimateQuestion::class, 'survey_id')
                    ->orderBy('ordem');
    }

    public function participations(): HasMany
    {
        return $this->hasMany(ClimateSurveyParticipation::class, 'survey_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // --- Helpers ---

    public function isAtiva(): bool
    {
        return $this->status === 'ativa';
    }

    public function gestorVeApenasDepto(): bool
    {
        return $this->visibilidade_gestor === 'apenas_departamento';
    }
}
