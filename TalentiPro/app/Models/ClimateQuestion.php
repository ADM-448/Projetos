<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClimateQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'climate_survey_id',
        'dimensao',
        'texto_pergunta',
        'tipo_resposta',
        'ordem',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(ClimateSurvey::class, 'climate_survey_id');
    }

    public function respostas(): HasMany
    {
        return $this->hasMany(ClimateResponse::class, 'climate_question_id');
    }
}
