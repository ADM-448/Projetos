<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ClimateSurvey;
use Illuminate\Support\Facades\DB;

/**
 * Calcula métricas da pesquisa de clima.
 *
 * Todas as consultas são delegadas ao banco de dados (sem hidratar Models ou Collections)
 * para máxima performance e contorno do N+1.
 * Além disso, o escopo da empresa_id é garantido para prevenir vazamento de dados.
 */
class CalculateClimateMetricsService
{
    /**
     * @param int      $surveyId     ID da pesquisa
     * @param int      $empresaId    ID da empresa para garantir isolamento
     * @param int|null $departmentId Filtro opcional de departamento
     * @return array<string, mixed>
     */
    public function getMetricsBySurvey(int $surveyId, int $empresaId, ?int $departmentId = null): array
    {
        // Certifica de que a survey existe (BelongsToEmpresa global scope já garante isolamento)
        $survey = ClimateSurvey::findOrFail($surveyId);

        return [
            'survey'                 => $survey,
            'averages_by_category'   => $this->getAveragesByCategory($surveyId, $empresaId, $departmentId),
            'averages_by_department' => $this->getAveragesByDepartment($surveyId, $empresaId), // Dept geralmente quer ver o geral por dept
            'enps'                   => $this->calculateENps($surveyId, $empresaId, $departmentId),
            'response_rate'          => $this->getResponseRate($surveyId, $empresaId), // Taxa de resposta é sobre o total da empresa
            'text_responses'         => $this->getTextResponses($surveyId, $empresaId, $departmentId),
        ];
    }

    private function getAveragesByCategory(int $surveyId, int $empresaId, ?int $departmentId): array
    {
        $result = DB::table('climate_responses')
            ->join('climate_questions', 'climate_responses.question_id', '=', 'climate_questions.id')
            ->leftJoin('climate_categories', 'climate_questions.category_id', '=', 'climate_categories.id')
            ->selectRaw('
                COALESCE(climate_categories.nome, "Sem Categoria") as categoria,
                COALESCE(climate_categories.cor, "#999999") as cor,
                ROUND(AVG(climate_responses.nota), 1) as media,
                COUNT(climate_responses.id) as total_respostas
            ')
            ->where('climate_responses.survey_id', $surveyId)
            ->where('climate_responses.empresa_id', $empresaId)
            ->whereNotNull('climate_responses.nota')
            ->when($departmentId, fn($q) => $q->where('climate_responses.department_id', $departmentId))
            ->groupBy('climate_categories.nome', 'climate_categories.cor')
            ->orderByDesc('media')
            ->get();

        return $result->toArray();
    }

    private function getAveragesByDepartment(int $surveyId, int $empresaId): array
    {
        $result = DB::table('climate_responses')
            ->leftJoin('departamentos', 'climate_responses.department_id', '=', 'departamentos.id')
            ->selectRaw('
                COALESCE(departamentos.nome, "Sem Departamento") as departamento,
                ROUND(AVG(climate_responses.nota), 1) as media
            ')
            ->where('climate_responses.survey_id', $surveyId)
            ->where('climate_responses.empresa_id', $empresaId)
            ->whereNotNull('climate_responses.nota')
            ->groupBy('departamentos.nome')
            ->orderByDesc('media')
            ->get();

        return $result->toArray();
    }

    /**
     * Calcula o eNPS em uma ÚNICA query de agregação condicional. (Fix auditoria #3 e #2)
     */
    private function calculateENps(int $surveyId, int $empresaId, ?int $departmentId): float
    {
        $result = DB::table('climate_responses')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN nota >= 9 THEN 1 ELSE 0 END) as promotores,
                SUM(CASE WHEN nota BETWEEN 7 AND 8 THEN 1 ELSE 0 END) as neutros,
                SUM(CASE WHEN nota <= 6 THEN 1 ELSE 0 END) as detratores
            ')
            ->where('survey_id', $surveyId)
            ->where('empresa_id', $empresaId)
            ->whereNotNull('nota')
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->first();

        $total = (int) ($result->total ?? 0);

        if ($total === 0) {
            return 0.0;
        }

        $promotores = (int) $result->promotores;
        $detratores = (int) $result->detratores;

        return round((($promotores - $detratores) / $total) * 100, 1);
    }

    private function getResponseRate(int $surveyId, int $empresaId): array
    {
        $totalUsers = DB::table('users')
            ->where('empresa_id', $empresaId)
            ->where('status', 'ativo')
            ->count();

        if ($totalUsers === 0) {
            return ['rate' => 0, 'responded' => 0, 'total' => 0];
        }

        $participations = DB::table('climate_survey_participations')
            ->join('users', 'climate_survey_participations.user_id', '=', 'users.id')
            ->where('climate_survey_participations.survey_id', $surveyId)
            ->where('users.empresa_id', $empresaId)
            ->count();

        $rate = round(($participations / $totalUsers) * 100, 1);

        return [
            'rate'      => $rate,
            'responded' => $participations,
            'total'     => $totalUsers,
        ];
    }

    private function getTextResponses(int $surveyId, int $empresaId, ?int $departmentId): array
    {
        $result = DB::table('climate_responses')
            ->join('climate_questions', 'climate_responses.question_id', '=', 'climate_questions.id')
            ->select('climate_questions.texto as pergunta', 'climate_responses.texto_resposta as resposta')
            ->where('climate_responses.survey_id', $surveyId)
            ->where('climate_responses.empresa_id', $empresaId)
            ->whereNotNull('climate_responses.texto_resposta')
            ->where('climate_responses.texto_resposta', '!=', '')
            ->when($departmentId, fn($q) => $q->where('climate_responses.department_id', $departmentId))
            ->orderBy('climate_responses.created_at', 'desc')
            ->get();

        // Agrupa por pergunta
        $grouped = [];
        foreach ($result as $row) {
            $grouped[$row->pergunta][] = $row->resposta;
        }

        return $grouped;
    }
}
