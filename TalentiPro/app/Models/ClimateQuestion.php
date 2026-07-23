<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClimateQuestion extends Model
{
    use HasFactory;

    // Sem BelongsToEmpresa: escopo garantido via survey_id → ClimateSurvey tem BelongsToEmpresa.

    protected $table = 'climate_questions';

    protected $fillable = [
        'survey_id',
        'category_id',
        'texto',
        'tipo',
        'obrigatoria',
        'ordem',
    ];

    protected $casts = [
        'obrigatoria' => 'boolean',
        'ordem'       => 'integer',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(ClimateSurvey::class, 'survey_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ClimateCategory::class, 'category_id');
    }

    public function isTipoNota(): bool
    {
        return $this->tipo === 'nota';
    }

    public function isTipoTexto(): bool
    {
        return $this->tipo === 'texto';
    }
}
