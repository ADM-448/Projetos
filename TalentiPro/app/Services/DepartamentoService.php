<?php

namespace App\Services;

use App\Contracts\DepartamentoServiceContract;
use App\Models\Departamento;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Serviço de Departamentos.
 *
 * Concentra toda a lógica de negócio relacionada a departamentos.
 * Implementa o contrato DepartamentoServiceContract (Princípio D do SOLID).
 */
class DepartamentoService implements DepartamentoServiceContract
{
    /**
     * Cria um novo departamento.
     * O empresa_id é injetado automaticamente pela Trait BelongsToEmpresa.
     *
     * @param  array<string, mixed>  $dados
     */
    public function criar(array $dados): Departamento
    {
        return DB::transaction(fn () => Departamento::create($dados));
    }

    /**
     * Atualiza um departamento existente.
     *
     * @param  array<string, mixed>  $dados
     */
    public function atualizar(Departamento $departamento, array $dados): Departamento
    {
        DB::transaction(fn () => $departamento->update($dados));

        return $departamento->fresh();
    }

    /**
     * Exclui um departamento após validar as regras de negócio.
     *
     * @throws ValidationException se houver colaboradores vinculados
     */
    public function excluir(Departamento $departamento): void
    {
        $this->garantirQueNaoTemColaboradores($departamento);

        DB::transaction(fn () => $departamento->delete());
    }

    // ─────────────────────────── Regras de Negócio ────────────────────────────

    /**
     * Lança ValidationException se o departamento possuir colaboradores vinculados.
     *
     * @throws ValidationException
     */
    private function garantirQueNaoTemColaboradores(Departamento $departamento): void
    {
        if (! $departamento->colaboradores()->exists()) {
            return;
        }

        throw ValidationException::withMessages([
            'departamento' => 'Não é possível excluir: Existem colaboradores vinculados a este departamento.',
        ]);
    }
}
