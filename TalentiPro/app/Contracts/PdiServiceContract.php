<?php

namespace App\Contracts;

use App\Models\Pdi;
use App\Models\PdiAcao;
use App\Models\PdiComentario;
use App\Models\PdiGrupo;

interface PdiServiceContract
{
    /**
     * Cria um PDI individual com suas respectivas ações/etapas.
     */
    public function criarPdi(array $dados, array $acoes, int $empresaId, int $gestorId): Pdi;

    /**
     * Cria um lote de PDIs (PdiGrupo) contendo um PDI individual para cada colaborador selecionado.
     */
    public function criarPdiGrupo(array $dados, array $colaboradoresIds, array $acoes, int $empresaId, int $gestorId): PdiGrupo;

    /**
     * Altera o status da ação (etapa) e aciona o recálculo do progresso do PDI.
     */
    public function atualizarAcaoStatus(PdiAcao $acao, string $status): Pdi;

    /**
     * Recalcula a porcentagem de progresso geral e ajusta o status e data de conclusão se aplicável.
     */
    public function recalcularProgresso(Pdi $pdi): void;

    /**
     * Adiciona um comentário no chat de alinhamento do PDI.
     */
    public function adicionarComentario(Pdi $pdi, string $comentario, int $userId): PdiComentario;

    /**
     * Faz upload do comprovante de conclusão do PDI.
     */
    public function salvarComprovante(Pdi $pdi, $file): Pdi;
}
