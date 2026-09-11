<?php

namespace App\Services;

use App\Contracts\CargoServiceContract;
use App\Models\Cargo;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Serviço de Cargos.
 *
 * Concentra toda a lógica de negócio relacionada a cargos.
 * Implementa o contrato CargoServiceContract (Princípio D do SOLID).
 */
class CargoService implements CargoServiceContract
{
    /**
     * Cria um novo cargo.
     * O empresa_id é injetado automaticamente pela Trait BelongsToEmpresa.
     *
     * @param  array<string, mixed>  $dados
     */
    public function criar(array $dados): Cargo
    {
        return DB::transaction(fn () => Cargo::create($dados));
    }

    /**
     * Atualiza um cargo existente.
     *
     * @param  array<string, mixed>  $dados
     */
    public function atualizar(Cargo $cargo, array $dados): Cargo
    {
        DB::transaction(fn () => $cargo->update($dados));

        return $cargo->fresh();
    }

    /**
     * Exclui um cargo após validar as regras de negócio.
     *
     * @throws ValidationException se houver colaboradores ou cargos subordinados vinculados
     */
    public function excluir(Cargo $cargo): void
    {
        $this->garantirQueNaoTemColaboradores($cargo);
        $this->garantirQueNaoTemCargosSubordinados($cargo);

        DB::transaction(fn () => $cargo->delete());
    }

    // ─────────────────────────── Regras de Negócio ────────────────────────────

    /**
     * Lança ValidationException se o cargo possuir colaboradores vinculados.
     *
     * @throws ValidationException
     */
    private function garantirQueNaoTemColaboradores(Cargo $cargo): void
    {
        if (! $cargo->colaboradores()->exists()) {
            return;
        }

        throw ValidationException::withMessages([
            'cargo' => 'Não é possível excluir: Existem colaboradores vinculados a este cargo.',
        ]);
    }

    /**
     * Lança ValidationException se houver outros cargos subordinados a este.
     *
     * @throws ValidationException
     */
    private function garantirQueNaoTemCargosSubordinados(Cargo $cargo): void
    {
        if (! $cargo->cargosSubordinados()->exists()) {
            return;
        }

        throw ValidationException::withMessages([
            'cargo' => 'Não é possível excluir: Existem outros cargos que se reportam a este cargo (subordinados).',
        ]);
    }
}
