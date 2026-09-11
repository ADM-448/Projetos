<?php

namespace App\Contracts;

use App\Models\User;

/**
 * Contrato (Interface) do Serviço de Colaboradores.
 *
 * Define o que o serviço DEVE fazer sem ditar COMO ele faz.
 * Permite inversão de dependência (Princípio D do SOLID):
 * o Controller depende desta abstração, não da implementação concreta.
 */
interface ColaboradorServiceContract
{
    /**
     * Cria um novo colaborador vinculado à empresa fornecida.
     *
     * @param  array<string, mixed>  $dados
     */
    public function criar(array $dados, int $empresaId): User;

    /**
     * Atualiza os dados de um colaborador.
     * Garante a proteção de auto-lockout se o colaborador for o próprio usuário logado.
     *
     * @param  array<string, mixed>  $dados
     */
    public function atualizar(User $colaborador, array $dados, int $usuarioLogadoId): User;

    /**
     * Exclui um colaborador após validar as regras de negócio.
     *
     * @throws \Illuminate\Validation\ValidationException se a exclusão for bloqueada
     */
    public function excluir(User $colaborador, int $usuarioLogadoId): void;
}
