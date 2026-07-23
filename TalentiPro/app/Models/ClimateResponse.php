<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model de resposta anônima da pesquisa de clima.
 *
 * ATENÇÃO DE ARQUITETURA:
 * - Sem BelongsToEmpresa: isolamento intencional para garantir anonimato.
 * - Sem updated_at: respostas são imutáveis após o envio.
 * - 'encrypted_user_id' não está no $fillable: preenchido exclusivamente
 *   via DB::table()->insert() na SubmitClimateSurveyAction para evitar
 *   exposição acidental via Eloquent mass assignment.
 * - Sem HasFactory: respostas criadas apenas via SubmitClimateSurveyAction.
 */
class ClimateResponse extends Model
{
    // Respostas são imutáveis — sem coluna updated_at
    const UPDATED_AT = null;

    protected $table = 'climate_responses';

    protected $fillable = [
        'survey_id',
        'question_id',
        'department_id',
        'empresa_id',
        'nota',
        'texto_resposta',
        // encrypted_user_id INTENCIONALMENTE ausente do fillable
    ];

    protected $casts = [
        'nota' => 'integer',
    ];

    // encrypted_user_id é ocultado de toda serialização (JSON, arrays)
    protected $hidden = ['encrypted_user_id'];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(ClimateSurvey::class, 'survey_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ClimateQuestion::class, 'question_id');
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'department_id');
    }
}
