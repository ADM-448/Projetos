<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClimateSurveyParticipation extends Model
{
    use HasFactory;

    protected $fillable = [
        'climate_survey_id',
        'user_id',
        'status',
        'respondido_em',
    ];

    protected $casts = [
        'respondido_em' => 'datetime',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(ClimateSurvey::class, 'climate_survey_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
