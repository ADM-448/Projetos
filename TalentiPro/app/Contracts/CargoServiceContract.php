<?php

namespace App\Contracts;

use App\Models\Cargo;

/**
 * Contrato (Interface) do Serviço de Cargos.
 *
 * Inversão de Dependência (Princípio D do SOLID).
 */
interface CargoServiceContract
{
    /**
     * Cria um novo cargo.
     *
     * @param  array<string, mixed>  $dados
     */
    public function criar(array $dados): Cargo;

    /**
     * Atualiza um cargo existente.
     *
     * @param  array<string, mixed>  $dados
     */
    public function atualizar(Cargo $cargo, array $dados): Cargo;

    /**
     * Exclui um cargo após validar as regras de negócio.
     *
     * @throws \Illuminate\Validation\ValidationException se houver vínculos ativos
     */
    public function excluir(Cargo $cargo): void;
}
