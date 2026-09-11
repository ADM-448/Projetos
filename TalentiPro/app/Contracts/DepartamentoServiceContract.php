<?php

namespace App\Contracts;

use App\Models\Departamento;

/**
 * Contrato (Interface) do Serviço de Departamentos.
 *
 * Inversão de Dependência (Princípio D do SOLID).
 */
interface DepartamentoServiceContract
{
    /**
     * Cria um novo departamento.
     *
     * @param  array<string, mixed>  $dados
     */
    public function criar(array $dados): Departamento;

    /**
     * Atualiza um departamento existente.
     *
     * @param  array<string, mixed>  $dados
     */
    public function atualizar(Departamento $departamento, array $dados): Departamento;

    /**
     * Exclui um departamento após validar as regras de negócio.
     *
     * @throws \Illuminate\Validation\ValidationException se houver colaboradores vinculados
     */
    public function excluir(Departamento $departamento): void;
}
