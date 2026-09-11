<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClimateResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'climate_survey_id',
        'climate_question_id',
        'nota',
        'comentario',
        'departamento_id',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(ClimateSurvey::class, 'climate_survey_id');
    }

    public function pergunta(): BelongsTo
    {
        return $this->belongsTo(ClimateQuestion::class, 'climate_question_id');
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }
}
