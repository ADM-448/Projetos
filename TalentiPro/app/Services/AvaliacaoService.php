<?php

namespace App\Services;

use App\Contracts\AvaliacaoServiceContract;
use App\Enums\StatusAvaliacao;
use App\Enums\TipoAvaliacao;
use App\Models\Avaliacao;
use App\Models\AvaliacaoFechamento;
use App\Models\AvaliacaoParticipante;
use App\Models\AvaliacaoResposta;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AvaliacaoService implements AvaliacaoServiceContract
{
    public function criar(array $dados, int $empresaId, int $createdById): Avaliacao
    {
        return DB::transaction(function () use ($dados, $empresaId, $createdById) {
            
            $dataInicioCarbon = Carbon::parse($dados['data_inicio'])->startOfDay();
            $status = $dataInicioCarbon->isFuture() ? StatusAvaliacao::AGENDADA : StatusAvaliacao::ANDAMENTO;

            // 1. Cria a Avaliação
            $avaliacao = Avaliacao::create([
                'empresa_id'        => $empresaId,
                'titulo'            => $dados['titulo'],
                'grau'              => $dados['grau'] ?? '180',
                'descricao'         => $dados['descricao'] ?? null,
                'data_inicio'       => $dados['data_inicio'],
                'data_fim'          => $dados['data_fim'],
                'status'            => $status,
                'created_by'        => $createdById,
                'filtros_aplicados' => $dados['filtros'] ?? null,
                'mediadores'        => $dados['mediadores'] ?? null,
            ]);

            // 2. Vincula Competências
            if (!empty($dados['competencias'])) {
                $avaliacao->competencias()->sync($dados['competencias']);
            }

            // 3. Produto Cartesiano: Cada avaliador avalia todos os avaliados selecionados
            $participantesBatch = [];
            $now = Carbon::now();

            if (!empty($dados['avaliados'])) {
                foreach ($dados['avaliados'] as $avaliadoId) {
                    
                    // 1. Cria a Autoavaliação para o Avaliado APENAS se não for 90 graus
                    if (!isset($dados['grau']) || $dados['grau'] != '90') {
                        $participantesBatch[] = [
                            'avaliacao_id' => $avaliacao->id,
                            'avaliado_id'  => $avaliadoId,
                            'avaliador_id' => $avaliadoId, // Ele avalia ele mesmo
                            'tipo'         => 'autoavaliacao',
                            'status'       => 'pendente',
                            'created_at'   => $now,
                            'updated_at'   => $now,
                        ];

                        if ($status === StatusAvaliacao::ANDAMENTO) {
                            app(\App\Services\AtividadeService::class)->registrar(
                                $empresaId,
                                $avaliadoId,
                                $avaliacao->titulo,
                                "Você tem uma autoavaliação pendente na campanha: {$avaliacao->titulo}.",
                                'fa-solid fa-clipboard-user',
                                '#3182ce',
                                route('avaliacoes.minhas'),
                                'Avaliação',
                                'responder'
                            );

                            app(\App\Services\EmailService::class)->notificar(
                                $avaliadoId,
                                "Autoavaliação Pendente: {$avaliacao->titulo}",
                                "Olá! Você tem uma autoavaliação aguardando suas respostas na campanha de avaliação de desempenho.",
                                route('avaliacoes.minhas'),
                                'Acessar Minhas Avaliações'
                            );
                        }
                    }

                    // 2. Cria as avaliações para os Avaliadores selecionados (Gestores/Masters)
                    if (!empty($dados['avaliadores'])) {
                        foreach ($dados['avaliadores'] as $avaliadorId) {
                            if ($avaliadoId != $avaliadorId) {
                                $participantesBatch[] = [
                                    'avaliacao_id' => $avaliacao->id,
                                    'avaliado_id'  => $avaliadoId,
                                    'avaliador_id' => $avaliadorId,
                                    'tipo'         => 'gestor',
                                    'status'       => 'pendente',
                                    'created_at'   => $now,
                                    'updated_at'   => $now,
                                ];

                                $avaliadoObj = \App\Models\User::find($avaliadoId);
                                if ($status === StatusAvaliacao::ANDAMENTO) {
                                    app(\App\Services\AtividadeService::class)->registrar(
                                        $empresaId,
                                        $avaliadorId,
                                        $avaliacao->titulo,
                                        "Você foi designado para avaliar {$avaliadoObj->name} na campanha: {$avaliacao->titulo}.",
                                        'fa-solid fa-list-check',
                                        '#e53e3e',
                                        route('avaliacoes.show', $avaliacao->id),
                                        'Avaliação',
                                        'avaliar'
                                    );

                                    app(\App\Services\EmailService::class)->notificar(
                                        $avaliadorId,
                                        "Nova Avaliação Pendente",
                                        "Você foi designado como avaliador para o(a) colaborador(a) {$avaliadoObj->name} na campanha {$avaliacao->titulo}.",
                                        route('avaliacoes.show', $avaliacao->id),
                                        'Acessar Avaliação'
                                    );
                                }
                            }
                        }
                    }

                    // 3. Cria as avaliações para os Mediadores selecionados
                    if (!empty($dados['mediadores'])) {
                        foreach ($dados['mediadores'] as $mediadorId) {
                            if ($avaliadoId != $mediadorId) {
                                $participantesBatch[] = [
                                    'avaliacao_id' => $avaliacao->id,
                                    'avaliado_id'  => $avaliadoId,
                                    'avaliador_id' => $mediadorId,
                                    'tipo'         => 'mediador',
                                    'status'       => 'pendente',
                                    'created_at'   => $now,
                                    'updated_at'   => $now,
                                ];

                                $avaliadoObj = \App\Models\User::find($avaliadoId);
                                if ($status === StatusAvaliacao::ANDAMENTO) {
                                    app(\App\Services\AtividadeService::class)->registrar(
                                        $empresaId,
                                        $mediadorId,
                                        $avaliacao->titulo,
                                        "Você foi designado como mediador para acompanhar a avaliação de {$avaliadoObj->name} na campanha: {$avaliacao->titulo}.",
                                        'fa-solid fa-people-arrows',
                                        '#f39c12',
                                        route('avaliacoes.show', $avaliacao->id),
                                        'Avaliação',
                                        'acompanhar'
                                    );

                                    app(\App\Services\EmailService::class)->notificar(
                                        $mediadorId,
                                        "Mediação de Avaliação",
                                        "Você foi designado como mediador para acompanhar a avaliação de {$avaliadoObj->name} na campanha {$avaliacao->titulo}.",
                                        route('avaliacoes.show', $avaliacao->id),
                                        'Acessar Avaliação'
                                    );
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($participantesBatch)) {
                AvaliacaoParticipante::insert($participantesBatch);
            }

            return $avaliacao;
        });
    }

    public function atualizar(Avaliacao $avaliacao, array $dados): Avaliacao
    {
        return DB::transaction(function () use ($avaliacao, $dados) {

            // 1. Atualiza dados básicos da avaliação
            $avaliacao->update([
                'titulo'            => $dados['titulo'],
                'descricao'         => $dados['descricao'] ?? null,
                'data_inicio'       => $dados['data_inicio'],
                'data_fim'          => $dados['data_fim'],
                'filtros_aplicados' => $dados['filtros'] ?? null,
                'mediadores'        => $dados['mediadores'] ?? null,
            ]);

            // 2. Sync de Competências — atualiza apenas o pivot, não toca nas respostas existentes
            if (!empty($dados['competencias'])) {
                $avaliacao->competencias()->sync($dados['competencias']);
            } else {
                $avaliacao->competencias()->detach();
            }

            // 3. DIFF de participantes
            // Regras:
            //  - Avaliado REMOVIDO  → apaga tudo dele (participantes + respostas + fechamento)
            //  - Avaliador REMOVIDO → apaga só aquele participante + suas respostas
            //  - Combinação MANTIDA → não toca (preserva status e respostas intactos)
            //  - Combinação NOVA    → insere como 'pendente'

            $avaliados   = collect($dados['avaliados']  ?? []);
            $avaliadores = collect($dados['avaliadores'] ?? []);
            $mediadores  = collect($dados['mediadores']  ?? []);

            // Monta o conjunto DESEJADO de participantes
            $desejados = collect();
            foreach ($avaliados as $avaliadoId) {
                $desejados->push(['avaliado_id' => $avaliadoId, 'avaliador_id' => $avaliadoId, 'tipo' => 'autoavaliacao']);
                foreach ($avaliadores as $avaliadorId) {
                    if ($avaliadoId != $avaliadorId) {
                        $desejados->push(['avaliado_id' => $avaliadoId, 'avaliador_id' => $avaliadorId, 'tipo' => 'gestor']);
                    }
                }
                foreach ($mediadores as $mediadorId) {
                    if ($avaliadoId != $mediadorId) {
                        $desejados->push(['avaliado_id' => $avaliadoId, 'avaliador_id' => $mediadorId, 'tipo' => 'mediador']);
                    }
                }
            }

            // Chave única para comparação: "avaliado_id|avaliador_id|tipo"
            $chave = fn($p) => "{$p['avaliado_id']}|{$p['avaliador_id']}|{$p['tipo']}";
            $desejadosMap = $desejados->keyBy($chave);

            // Busca existentes normalizando o cast de Enum para string
            $existentes = $avaliacao->participantes()
                ->select(['id', 'avaliado_id', 'avaliador_id', 'tipo'])
                ->get()
                ->map(fn($p) => [
                    'id'           => $p->id,
                    'avaliado_id'  => $p->avaliado_id,
                    'avaliador_id' => $p->avaliador_id,
                    'tipo'         => $p->tipo instanceof \BackedEnum ? $p->tipo->value : (string) $p->tipo,
                ]);
            $existentesMap = $existentes->keyBy($chave);

            // ── REMOVER: estão no banco mas não são mais desejados ──
            $paraRemover = $existentesMap->filter(fn($p) => !$desejadosMap->has($chave($p)));

            if ($paraRemover->isNotEmpty()) {
                $idsRemover = $paraRemover->pluck('id');

                // Apaga respostas vinculadas aos participantes removidos
                AvaliacaoResposta::whereIn('avaliacao_participante_id', $idsRemover)->delete();

                // Se o avaliado saiu completamente da avaliação, apaga o fechamento dele
                $avaliadosRemovidos = $paraRemover->pluck('avaliado_id')
                    ->diff($avaliados)
                    ->unique();
                if ($avaliadosRemovidos->isNotEmpty()) {
                    \App\Models\AvaliacaoFechamento::where('avaliacao_id', $avaliacao->id)
                        ->whereIn('avaliado_id', $avaliadosRemovidos)
                        ->delete();
                }

                $avaliacao->participantes()->whereIn('id', $idsRemover)->delete();
            }

            // ── INSERIR: desejados que ainda não existem no banco ──
            $paraInserir = $desejadosMap->filter(fn($p) => !$existentesMap->has($chave($p)));

            if ($paraInserir->isNotEmpty()) {
                $now = Carbon::now();
                $batch = $paraInserir->map(fn($p) => [
                    'avaliacao_id' => $avaliacao->id,
                    'avaliado_id'  => $p['avaliado_id'],
                    'avaliador_id' => $p['avaliador_id'],
                    'tipo'         => $p['tipo'],
                    'status'       => 'pendente',
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ])->values()->toArray();

                AvaliacaoParticipante::insert($batch);

                // Notifica APENAS os novos participantes adicionados na edição.
                // Participantes que já existiam NÃO são re-notificados.
                foreach ($paraInserir as $p) {
                    if ($p['tipo'] === 'autoavaliacao') {
                        app(\App\Services\AtividadeService::class)->registrar(
                            $avaliacao->empresa_id,
                            $p['avaliado_id'],
                            $avaliacao->titulo,
                            "Você tem uma autoavaliação pendente na campanha: {$avaliacao->titulo}.",
                            'fa-solid fa-clipboard-user',
                            '#3182ce',
                            route('avaliacoes.minhas'),
                            'Avaliação',
                            'responder'
                        );

                        app(\App\Services\EmailService::class)->notificar(
                            $p['avaliado_id'],
                            "Autoavaliação Pendente: {$avaliacao->titulo}",
                            "Olá! Você tem uma autoavaliação aguardando suas respostas na campanha de avaliação de desempenho.",
                            route('avaliacoes.minhas'),
                            'Acessar Minhas Avaliações'
                        );
                    } elseif ($p['tipo'] === 'gestor') {
                        $avaliadoObj = \App\Models\User::find($p['avaliado_id']);
                        app(\App\Services\AtividadeService::class)->registrar(
                            $avaliacao->empresa_id,
                            $p['avaliador_id'],
                            $avaliacao->titulo,
                            "Você foi designado para avaliar {$avaliadoObj->name} na campanha: {$avaliacao->titulo}.",
                            'fa-solid fa-list-check',
                            '#e53e3e',
                            route('avaliacoes.show', $avaliacao->id),
                            'Avaliação',
                            'avaliar'
                        );

                        app(\App\Services\EmailService::class)->notificar(
                            $p['avaliador_id'],
                            "Nova Avaliação Pendente",
                            "Você foi designado como avaliador para o(a) colaborador(a) {$avaliadoObj->name} na campanha {$avaliacao->titulo}.",
                            route('avaliacoes.show', $avaliacao->id),
                            'Acessar Avaliação'
                        );
                    } elseif ($p['tipo'] === 'mediador') {
                        $avaliadoObj = \App\Models\User::find($p['avaliado_id']);
                        app(\App\Services\AtividadeService::class)->registrar(
                            $avaliacao->empresa_id,
                            $p['avaliador_id'],
                            $avaliacao->titulo,
                            "Você foi designado como mediador para acompanhar a avaliação de {$avaliadoObj->name} na campanha: {$avaliacao->titulo}.",
                            'fa-solid fa-people-arrows',
                            '#f39c12',
                            route('avaliacoes.show', $avaliacao->id),
                            'Avaliação',
                            'acompanhar'
                        );

                        app(\App\Services\EmailService::class)->notificar(
                            $p['avaliador_id'],
                            "Mediação de Avaliação",
                            "Você foi designado como mediador para acompanhar a avaliação de {$avaliadoObj->name} na campanha {$avaliacao->titulo}.",
                            route('avaliacoes.show', $avaliacao->id),
                            'Acessar Avaliação'
                        );
                    }
                }
            }

            return $avaliacao;
        });
    }

    public function salvarRespostas(AvaliacaoParticipante $participante, array $respostas): void
    {
        DB::transaction(function () use ($participante, $respostas) {
            $now = Carbon::now();
            $respostasBatch = [];

            foreach ($respostas as $skillId => $nota) {
                $respostasBatch[] = [
                    'avaliacao_participante_id' => $participante->id,
                    'skill_id'                  => $skillId,
                    'nota'                      => $nota,
                    'created_at'                => $now,
                    'updated_at'                => $now,
                ];
            }

            if (!empty($respostasBatch)) {
                // Remove as antigas para evitar duplicação (caso permita editar resposta)
                $participante->respostas()->delete();
                
                \App\Models\AvaliacaoResposta::insert($respostasBatch);
                
                $participante->update(['status' => 'respondido']);
            }
        });
    }

    public function fechar(Avaliacao $avaliacao, int $avaliadoId, array $dados, int $responsavelId): AvaliacaoFechamento
    {
        return DB::transaction(function () use ($avaliacao, $avaliadoId, $dados, $responsavelId) {
            // 1. Obtém ou cria o participante do tipo mediador para o responsável que está fechando
            $participante = AvaliacaoParticipante::firstOrCreate(
                [
                    'avaliacao_id' => $avaliacao->id,
                    'avaliado_id'  => $avaliadoId,
                    'avaliador_id' => $responsavelId,
                    'tipo'         => 'mediador',
                ],
                [
                    'status' => 'respondido',
                ]
            );

            // 2. Salva as respostas individuais por skill para o mediador
            $respostas = $dados['respostas'] ?? [];
            if (!empty($respostas)) {
                $this->salvarRespostas($participante, $respostas);
            }

            // 3. Calcula a média das notas para a nota_final do fechamento
            $notaFinal = !empty($respostas) ? round(array_sum($respostas) / count($respostas), 2) : null;

            // 4. Cria ou atualiza o fechamento
            $fechamento = AvaliacaoFechamento::updateOrCreate(
                [
                    'avaliacao_id' => $avaliacao->id,
                    'avaliado_id'  => $avaliadoId,
                ],
                [
                    'responsavel_id' => $responsavelId,
                    'nota_final'     => $notaFinal,
                    'comentario'     => $dados['comentario'] ?? null,
                ]
            );

            // Garante que o status do participante seja atualizado
            $participante->update(['status' => 'respondido']);

            return $fechamento;
        });
    }
}
