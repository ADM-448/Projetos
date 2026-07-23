<?php

declare(strict_types=1);

namespace App\Actions;

use App\Jobs\SendClimateSurveyNotificationJob;
use App\Models\ClimateSurvey;
use App\Models\User;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

/**
 * Abre uma pesquisa de clima e dispara notificações em lote.
 *
 * Responsabilidade única: ativação + notificação.
 * Não submete respostas (responsabilidade da SubmitClimateSurveyAction).
 */
final class OpenClimateSurveyAction
{
    public function handle(ClimateSurvey $survey): void
    {
        $survey->update(['status' => 'ativa']);

        $this->dispatchNotifications($survey);
    }

    /**
     * Busca usuários ativos em chunks de 50 para não estourar memória.
     * Cada usuário vira um Job individual no batch (fix auditoria #1).
     */
    private function dispatchNotifications(ClimateSurvey $survey): void
    {
        // Carrega apenas colunas necessárias — sem SELECT *
        User::withoutGlobalScope('empresa')
            ->where('empresa_id', $survey->empresa_id)
            ->where('status', 'ativo')
            ->whereNotNull('email')
            ->select(['id', 'name', 'email', 'telefone', 'empresa_id'])
            ->chunk(50, function ($users) use ($survey): void {
                $jobs = $users
                    ->map(fn(User $user) => new SendClimateSurveyNotificationJob($survey, $user))
                    ->toArray();

                Bus::batch($jobs)
                    ->name("Clima: {$survey->titulo}")
                    ->allowFailures() // Um job com falha não cancela os demais
                    ->dispatch();
            });
    }
}
