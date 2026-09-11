<?php

namespace App\Contracts;

use App\Models\Avaliacao;
use App\Models\AvaliacaoFechamento;
use App\Models\AvaliacaoParticipante;

interface AvaliacaoServiceContract
{
    /**
     * Cria a avaliação, vincula as competências e gera a matriz de participantes em lote.
     */
    public function criar(array $dados, int $empresaId, int $createdById): Avaliacao;

    /**
     * Atualiza a avaliação, vincula as competências e recria os participantes.
     */
    public function atualizar(Avaliacao $avaliacao, array $dados): Avaliacao;

    /**
     * Salva as respostas (notas) de um avaliador para um avaliado em lote.
     */
    public function salvarRespostas(AvaliacaoParticipante $participante, array $respostas): void;

    /**
     * Fecha a avaliação para um avaliado, computando a nota final (opcional).
     */
    public function fechar(Avaliacao $avaliacao, int $avaliadoId, array $dados, int $responsavelId): AvaliacaoFechamento;
}
