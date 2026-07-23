<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\SubmitSurveyData;
use App\Exceptions\DuplicateParticipationException;
use App\Models\ClimateSurveyParticipation;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Processa o envio das respostas de uma pesquisa de clima.
 *
 * Responsabilidade única: submissão atômica de respostas.
 * Não calcula métricas, não envia notificações.
 */
final class SubmitClimateSurveyAction
{
    /**
     * @throws DuplicateParticipationException
     */
    public function handle(SubmitSurveyData $data): void
    {
        $this->ensureNotAlreadyParticipated($data->surveyId, $data->userId);

        DB::transaction(function () use ($data): void {
            $this->insertResponses($data);
            $this->recordParticipation($data->surveyId, $data->userId);
        });
    }

    private function ensureNotAlreadyParticipated(int $surveyId, int $userId): void
    {
        $alreadyParticipated = ClimateSurveyParticipation::where('survey_id', $surveyId)
            ->where('user_id', $userId)
            ->exists();

        if ($alreadyParticipated) {
            throw new DuplicateParticipationException();
        }
    }

    /**
     * INSERT em lote: 1 única query para N respostas (fix auditoria #4).
     * Usa DB::table() direto para contornar a ausência de encrypted_user_id
     * no $fillable do Model (fix auditoria #7).
     */
    private function insertResponses(SubmitSurveyData $data): void
    {
        // Criptografa uma única vez fora do loop
        $encryptedUserId = Crypt::encryptString((string) $data->userId);
        $now             = now();

        $rows = collect($data->respostas)
            ->map(fn(array $resposta) => [
                'survey_id'         => $data->surveyId,
                'question_id'       => $resposta['question_id'],
                'department_id'     => $data->departmentId,
                'empresa_id'        => $data->empresaId,
                'nota'              => $resposta['nota'] ?? null,
                'texto_resposta'    => $resposta['texto_resposta'] ?? null,
                'encrypted_user_id' => $encryptedUserId,
                'created_at'        => $now,
            ])
            ->toArray();

        // 1 único INSERT com múltiplas linhas — sem N+1
        DB::table('climate_responses')->insert($rows);
    }

    private function recordParticipation(int $surveyId, int $userId): void
    {
        ClimateSurveyParticipation::create([
            'survey_id' => $surveyId,
            'user_id'   => $userId,
        ]);
    }
}
