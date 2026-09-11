<?php

namespace App\Services;

use App\Contracts\PdiServiceContract;
use App\Enums\StatusPdi;
use App\Models\Pdi;
use App\Models\PdiAcao;
use App\Models\PdiComentario;
use App\Models\PdiGrupo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PdiService implements PdiServiceContract
{
    /**
     * Cria um PDI individual com suas respectivas ações/etapas.
     */
    public function criarPdi(array $dados, array $acoes, int $empresaId, int $gestorId): Pdi
    {
        return DB::transaction(function () use ($dados, $acoes, $empresaId, $gestorId) {
            $pdi = Pdi::create([
                'empresa_id' => $empresaId,
                'colaborador_id' => $dados['colaborador_id'],
                'gestor_id' => $gestorId,
                'pdi_grupo_id' => $dados['pdi_grupo_id'] ?? null,
                'titulo' => $dados['titulo'],
                'descricao' => $dados['descricao'] ?? null,
                'data_inicio' => $dados['data_inicio'] ?? null,
                'data_fim' => $dados['data_fim'] ?? null,
                'status' => StatusPdi::NAO_INICIADO,
                'progresso' => 0,
            ]);

            foreach ($acoes as $acaoDados) {
                $pdi->acoes()->create([
                    'titulo' => $acaoDados['titulo'],
                    'descricao' => $acaoDados['descricao'] ?? null,
                    'status' => 'pendente',
                ]);
            }

            // Registra atividade no sistema
            app(\App\Services\AtividadeService::class)->registrar(
                $empresaId,
                $dados['colaborador_id'],
                $pdi->titulo,
                "Você recebeu um novo Plano de Desenvolvimento Individual (PDI): {$pdi->titulo}.",
                'fa-solid fa-graduation-cap',
                '#6a4c7c',
                route('pdis.show', $pdi->id),
                'PDI',
                'responder'
            );

            // Dispara E-mail
            app(\App\Services\EmailService::class)->notificar(
                $dados['colaborador_id'],
                "Novo PDI Atribuído: {$pdi->titulo}",
                "Você recebeu um novo Plano de Desenvolvimento Individual (PDI). Acesse a plataforma para visualizar os detalhes e acompanhar o seu progresso.",
                route('pdis.show', $pdi->id),
                'Acessar PDI'
            );

            return $pdi;
        });
    }

    /**
     * Cria um lote de PDIs (PdiGrupo) contendo um PDI individual para cada colaborador selecionado.
     */
    public function criarPdiGrupo(array $dados, array $colaboradoresIds, array $acoes, int $empresaId, int $gestorId): PdiGrupo
    {
        return DB::transaction(function () use ($dados, $colaboradoresIds, $acoes, $empresaId, $gestorId) {
            $grupo = PdiGrupo::create([
                'empresa_id' => $empresaId,
                'gestor_id' => $gestorId,
                'titulo' => $dados['titulo'],
                'descricao' => $dados['descricao'] ?? null,
                'categoria' => $dados['categoria'] ?? null,
                'data_inicio' => $dados['data_inicio'] ?? null,
                'data_fim' => $dados['data_fim'] ?? null,
            ]);

            foreach ($colaboradoresIds as $colaboradorId) {
                $pdiDados = [
                    'colaborador_id' => $colaboradorId,
                    'pdi_grupo_id' => $grupo->id,
                    'titulo' => $dados['titulo'],
                    'descricao' => $dados['descricao'] ?? null,
                    'data_inicio' => $dados['data_inicio'] ?? null,
                    'data_fim' => $dados['data_fim'] ?? null,
                ];

                $this->criarPdi($pdiDados, $acoes, $empresaId, $gestorId);
            }

            return $grupo;
        });
    }

    /**
     * Atualiza um lote de PDIs (PdiGrupo) e propaga as alterações para os PDIs individuais (Opção A).
     */
    public function atualizarPdiGrupo(PdiGrupo $grupo, array $dados, array $colaboradoresIds, array $acoesSubmitted): PdiGrupo
    {
        return DB::transaction(function () use ($grupo, $dados, $colaboradoresIds, $acoesSubmitted) {
            $grupo->update([
                'titulo' => $dados['titulo'],
                'descricao' => $dados['descricao'] ?? null,
                'categoria' => $dados['categoria'] ?? null,
                'data_inicio' => $dados['data_inicio'] ?? null,
                'data_fim' => $dados['data_fim'] ?? null,
            ]);

            $pdisAtuais = $grupo->pdis()->get();
            $idsAtuais = $pdisAtuais->pluck('colaborador_id')->toArray();

            $idsParaRemover = array_diff($idsAtuais, $colaboradoresIds);
            $idsParaAdicionar = array_diff($colaboradoresIds, $idsAtuais);
            $idsParaManter = array_intersect($idsAtuais, $colaboradoresIds);

            // Remover PDIs de colaboradores removidos
            if (!empty($idsParaRemover)) {
                $grupo->pdis()->whereIn('colaborador_id', $idsParaRemover)->each(function ($pdi) {
                    $pdi->acoes()->delete();
                    $pdi->comentarios()->delete();
                    $pdi->delete();
                });
            }

            // Adicionar PDIs para novos colaboradores
            foreach ($idsParaAdicionar as $colaboradorId) {
                $pdiDados = [
                    'colaborador_id' => $colaboradorId,
                    'pdi_grupo_id' => $grupo->id,
                    'titulo' => $dados['titulo'],
                    'descricao' => $dados['descricao'] ?? null,
                    'data_inicio' => $dados['data_inicio'] ?? null,
                    'data_fim' => $dados['data_fim'] ?? null,
                ];

                $novasAcoes = array_map(function($acao) {
                    return [
                        'titulo' => $acao['titulo'],
                        'descricao' => $acao['descricao'] ?? null,
                    ];
                }, $acoesSubmitted);

                $this->criarPdi($pdiDados, $novasAcoes, $grupo->empresa_id, $grupo->gestor_id);
            }

            // Atualizar PDIs e Ações dos colaboradores mantidos
            foreach ($pdisAtuais as $pdi) {
                if (in_array($pdi->colaborador_id, $idsParaManter)) {
                    $pdi->update([
                        'titulo' => $dados['titulo'],
                        'descricao' => $dados['descricao'] ?? null,
                        'data_inicio' => $dados['data_inicio'] ?? null,
                        'data_fim' => $dados['data_fim'] ?? null,
                    ]);

                    $acoesPdi = $pdi->acoes()->get();
                    $acoesProcessed = [];
                    
                    foreach ($acoesSubmitted as $acaoSubmitted) {
                        $originalTitulo = $acaoSubmitted['original_titulo'] ?? null;
                        
                        $acaoExistente = null;
                        if ($originalTitulo) {
                            $acaoExistente = $acoesPdi->firstWhere('titulo', $originalTitulo);
                        }
                        
                        if ($acaoExistente) {
                            $acaoExistente->update([
                                'titulo' => $acaoSubmitted['titulo'],
                                'descricao' => $acaoSubmitted['descricao'] ?? null,
                            ]);
                            $acoesProcessed[] = $acaoExistente->id;
                        } else {
                            $nova = $pdi->acoes()->create([
                                'titulo' => $acaoSubmitted['titulo'],
                                'descricao' => $acaoSubmitted['descricao'] ?? null,
                                'status' => 'pendente'
                            ]);
                            $acoesProcessed[] = $nova->id;
                        }
                    }

                    // Ações removidas no form, apagadas incondicionalmente (Opção A)
                    $pdi->acoes()->whereNotIn('id', $acoesProcessed)->delete();

                    $this->recalcularProgresso($pdi);
                }
            }

            return $grupo;
        });
    }

    /**
     * Altera o status da ação (etapa) e aciona o recálculo do progresso do PDI.
     */
    public function atualizarAcaoStatus(PdiAcao $acao, string $status): Pdi
    {
        return DB::transaction(function () use ($acao, $status) {
            $acao->update([
                'status' => $status === 'concluido' ? 'concluido' : 'pendente',
            ]);

            $pdi = $acao->pdi;
            $this->recalcularProgresso($pdi);

            return $pdi;
        });
    }

    /**
     * Recalcula a porcentagem de progresso geral e ajusta o status e data de conclusão se aplicável.
     */
    public function recalcularProgresso(Pdi $pdi): void
    {
        $acoes = $pdi->acoes;
        $total = $acoes->count();

        if ($total === 0) {
            $progresso = 0;
        } else {
            $concluidas = $acoes->where('status', 'concluido')->count();
            $progresso = (int) round(($concluidas / $total) * 100);
        }

        $dadosAtualizacao = [
            'progresso' => $progresso,
        ];

        // Regra de transição de status automático
        if ($progresso === 100) {
            $dadosAtualizacao['status'] = StatusPdi::CONCLUIDO;
            $dadosAtualizacao['data_conclusao'] = now();
        } elseif ($progresso === 0) {
            $dadosAtualizacao['status'] = StatusPdi::NAO_INICIADO;
            $dadosAtualizacao['data_conclusao'] = null;
        } else {
            // Se o progresso regredir ou for menor que 100 (e maior que 0), mudamos para em_andamento
            $dadosAtualizacao['status'] = StatusPdi::EM_ANDAMENTO;
            $dadosAtualizacao['data_conclusao'] = null;
        }

        $pdi->update($dadosAtualizacao);
    }

    /**
     * Adiciona um comentário no chat de alinhamento do PDI.
     */
    public function adicionarComentario(Pdi $pdi, string $comentario, int $userId): PdiComentario
    {
        return $pdi->comentarios()->create([
            'user_id' => $userId,
            'comentario' => $comentario,
        ]);
    }

    /**
     * Faz upload do comprovante de conclusão do PDI.
     */
    public function salvarComprovante(Pdi $pdi, $file): Pdi
    {
        if ($pdi->comprovante) {
            Storage::disk('public')->delete($pdi->comprovante);
        }

        $path = $file->store('comprovantes_pdi', 'public');

        $pdi->update([
            'comprovante' => $path,
        ]);

        return $pdi;
    }
}
