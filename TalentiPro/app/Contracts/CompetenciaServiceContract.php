<?php

namespace App\Contracts;

use App\Models\Competencia;

interface CompetenciaServiceContract
{
    /**
     * Cria uma nova competência.
     */
    public function criar(array $dados): Competencia;

    /**
     * Atualiza uma competência existente.
     */
    public function atualizar(Competencia $competencia, array $dados): Competencia;

    /**
     * Exclui uma competência.
     */
    public function excluir(Competencia $competencia): void;

    /**
     * Adiciona uma skill à competência.
     */
    public function adicionarSkill(Competencia $competencia, string $nomeSkill): void;

    /**
     * Remove uma skill pelo ID (garantindo pertencer à competência).
     */
    public function removerSkill(Competencia $competencia, int $skillId): void;
}
