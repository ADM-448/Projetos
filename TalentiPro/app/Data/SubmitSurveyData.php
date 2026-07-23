<?php

declare(strict_types=1);

namespace App\Data;

/**
 * DTO imutável que transporta os dados validados de uma submissão de pesquisa.
 *
 * userId é usado APENAS para:
 *   1. Registrar a participação em climate_survey_participations
 *   2. Gerar o encrypted_user_id para auditoria admin
 * Nunca é exposto nas respostas (climate_responses) em texto claro.
 */
final readonly class SubmitSurveyData
{
    /**
     * @param int                                                        $surveyId
     * @param int                                                        $userId       Usado só para participação + audit hash
     * @param int                                                        $empresaId
     * @param int|null                                                   $departmentId Departamento do respondente (para segmentação)
     * @param array<int, array{question_id: int, nota: int|null, texto_resposta: string|null}> $respostas
     */
    public function __construct(
        public readonly int    $surveyId,
        public readonly int    $userId,
        public readonly int    $empresaId,
        public readonly ?int   $departmentId,
        public readonly array  $respostas,
    ) {}
}
