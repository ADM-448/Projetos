<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClimateSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'titulo',
        'descricao',
        'data_inicio',
        'data_fim',
        'status',
        'anonima',
        'created_by',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim'    => 'date',
        'anonima'     => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function perguntas(): HasMany
    {
        return $this->hasMany(ClimateQuestion::class)->orderBy('ordem');
    }

    public function respostas(): HasMany
    {
        return $this->hasMany(ClimateResponse::class);
    }

    public function participacoes(): HasMany
    {
        return $this->hasMany(ClimateSurveyParticipation::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(ClimateSurveyAuditLog::class);
    }

    public function isAtiva(): bool
    {
        return $this->status === 'ativa';
    }

    public function isEncerrada(): bool
    {
        return $this->status === 'encerrada';
    }

    public function isRascunho(): bool
    {
        return $this->status === 'rascunho';
    }

    public function isAgendada(): bool
    {
        return $this->status === 'agendada';
    }
}
