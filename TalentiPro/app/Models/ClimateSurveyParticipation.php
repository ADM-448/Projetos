<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Controle de participação: registra APENAS que o usuário respondeu.
 * Sem vínculo direto com as respostas (climate_responses).
 */
class ClimateSurveyParticipation extends Model
{
    // Participações são imutáveis
    const UPDATED_AT = null;

    protected $table = 'climate_survey_participations';

    protected $fillable = [
        'survey_id',
        'user_id',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(ClimateSurvey::class, 'survey_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
