<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessScheduledAssessments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'avaliacoes:processar-agendamentos';

    protected $description = 'Processa avaliações e pesquisas de clima agendadas, ativando-as e enviando notificações quando a data de início chegar.';

    public function handle()
    {
        $hoje = \Carbon\Carbon::now()->startOfDay();

        $this->info("Processando avaliações agendadas para: {$hoje->toDateString()}");

        $this->processarAvaliacoesDesempenho($hoje);
        $this->processarPesquisasClima($hoje);

        $this->info("Processamento concluído.");
    }

    private function processarAvaliacoesDesempenho(\Carbon\Carbon $hoje)
    {
        $avaliacoes = \App\Models\Avaliacao::where('status', \App\Enums\StatusAvaliacao::AGENDADA)
            ->whereDate('data_inicio', '<=', $hoje)
            ->get();

        foreach ($avaliacoes as $avaliacao) {
            $avaliacao->update(['status' => \App\Enums\StatusAvaliacao::ANDAMENTO]);
            $this->info("Avaliação de Desempenho [{$avaliacao->id}] ativada.");

            $participantes = \App\Models\AvaliacaoParticipante::where('avaliacao_id', $avaliacao->id)
                ->where('status', 'pendente')
                ->with('avaliador', 'avaliado')
                ->get();

            foreach ($participantes as $participante) {
                $avaliador = $participante->avaliador;
                $avaliado = $participante->avaliado;

                if (!$avaliador || !$avaliado) continue;

                if ($participante->tipo === 'autoavaliacao') {
                    app(\App\Services\AtividadeService::class)->registrar(
                        $avaliacao->empresa_id,
                        $avaliador->id,
                        $avaliacao->titulo,
                        "Você tem uma autoavaliação pendente na campanha: {$avaliacao->titulo}.",
                        'fa-solid fa-clipboard-user',
                        '#3182ce',
                        route('avaliacoes.minhas'),
                        'Avaliação',
                        'responder'
                    );

                    app(\App\Services\EmailService::class)->notificar(
                        $avaliador->id,
                        "Autoavaliação Pendente: {$avaliacao->titulo}",
                        "Olá! Você tem uma autoavaliação aguardando suas respostas na campanha de avaliação de desempenho.",
                        route('avaliacoes.minhas'),
                        'Acessar Minhas Avaliações'
                    );
                } elseif ($participante->tipo === 'gestor') {
                    app(\App\Services\AtividadeService::class)->registrar(
                        $avaliacao->empresa_id,
                        $avaliador->id,
                        $avaliacao->titulo,
                        "Você foi designado para avaliar {$avaliado->name} na campanha: {$avaliacao->titulo}.",
                        'fa-solid fa-list-check',
                        '#e53e3e',
                        route('avaliacoes.show', $avaliacao->id),
                        'Avaliação',
                        'avaliar'
                    );

                    app(\App\Services\EmailService::class)->notificar(
                        $avaliador->id,
                        "Nova Avaliação Pendente",
                        "Você foi designado como avaliador para o(a) colaborador(a) {$avaliado->name} na campanha {$avaliacao->titulo}.",
                        route('avaliacoes.show', $avaliacao->id),
                        'Acessar Avaliação'
                    );
                } elseif ($participante->tipo === 'mediador') {
                    app(\App\Services\AtividadeService::class)->registrar(
                        $avaliacao->empresa_id,
                        $avaliador->id,
                        $avaliacao->titulo,
                        "Você foi designado como mediador para acompanhar a avaliação de {$avaliado->name} na campanha: {$avaliacao->titulo}.",
                        'fa-solid fa-people-arrows',
                        '#f39c12',
                        route('avaliacoes.show', $avaliacao->id),
                        'Avaliação',
                        'acompanhar'
                    );

                    app(\App\Services\EmailService::class)->notificar(
                        $avaliador->id,
                        "Mediação de Avaliação",
                        "Você foi designado como mediador para acompanhar a avaliação de {$avaliado->name} na campanha {$avaliacao->titulo}.",
                        route('avaliacoes.show', $avaliacao->id),
                        'Acessar Avaliação'
                    );
                }
            }
        }
    }

    private function processarPesquisasClima(\Carbon\Carbon $hoje)
    {
        $pesquisas = \App\Models\ClimateSurvey::where('status', 'agendada')
            ->whereDate('data_inicio', '<=', $hoje)
            ->get();

        foreach ($pesquisas as $survey) {
            $survey->update(['status' => 'ativa']);
            $this->info("Pesquisa de Clima [{$survey->id}] ativada.");

            $participacoes = \App\Models\ClimateSurveyParticipation::where('climate_survey_id', $survey->id)
                ->where('status', 'pendente')
                ->get();

            foreach ($participacoes as $participacao) {
                \App\Models\Atividade::create([
                    'user_id'    => $participacao->user_id,
                    'empresa_id' => $survey->empresa_id,
                    'icone'      => 'fa-solid fa-heart-pulse',
                    'cor'        => '#ec4899',
                    'titulo'     => 'Pesquisa de Clima Ativa',
                    'descricao'  => 'Uma nova pesquisa de clima foi aberta. Sua resposta é 100% anônima!',
                    'url'        => route('clima.responder', $survey->id),
                    'lida'       => false,
                ]);

                app(\App\Services\EmailService::class)->notificar(
                    $participacao->user_id,
                    "Nova Pesquisa de Clima Ativa",
                    "Uma nova pesquisa de clima foi aberta. Sua resposta é 100% anônima e muito importante para nós!",
                    route('clima.responder', $survey->id),
                    'Responder Pesquisa'
                );
            }
        }
    }
}
