<?php

namespace App\Services;

use App\Contracts\ColaboradorServiceContract;
use App\Models\Avaliacao;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Serviço de Colaboradores.
 *
 * Concentra toda a lógica de negócio relacionada a colaboradores,
 * seguindo o Princípio S do SOLID (Responsabilidade Única).
 *
 * O Controller apenas coordena: recebe a request, chama este serviço e retorna a response.
 * Este serviço apenas executa: valida regras de negócio e persiste dados.
 */
class ColaboradorService implements ColaboradorServiceContract
{
    /**
     * Cria um novo colaborador vinculado à empresa fornecida.
     * Usa DB::transaction para garantir atomicidade.
     *
     * @param  array<string, mixed>  $dados
     */
    public function criar(array $dados, int $empresaId): User
    {
        $empresa = Empresa::findOrFail($empresaId);
        $totalUsuarios = User::where('empresa_id', $empresaId)->count();

        if ($totalUsuarios >= $empresa->max_usuarios) {
            throw ValidationException::withMessages([
                'email' => "Limite de usuários da empresa excedido. O limite atual é {$empresa->max_usuarios}.",
            ]);
        }

        $dados['empresa_id'] = $empresaId;

        return DB::transaction(fn () => User::create($dados));
    }

    /**
     * Atualiza os dados de um colaborador.
     * Aplica proteção de auto-lockout se o colaborador for o próprio usuário logado.
     *
     * @param  array<string, mixed>  $dados
     */
    public function atualizar(User $colaborador, array $dados, int $usuarioLogadoId): User
    {
        if ($this->estaEditandoASiMesmo($colaborador, $usuarioLogadoId)) {
            $dados = $this->preservarPerfilEStatus($dados, $colaborador);
        }

        $this->removerSenhaSeVazia($dados);

        DB::transaction(function () use ($colaborador, $dados) {
            $colaborador->update($dados);
        });

        return $colaborador->fresh();
    }

    /**
     * Exclui um colaborador após validar as regras de negócio.
     *
     * Antes de deletar, desfaz manualmente as FKs sem onDelete configurado
     * para evitar Integrity Constraint Violations (erro 500).
     * As avaliações criadas pelo usuário são PRESERVADAS — apenas o vínculo
     * com o criador é removido (created_by = null).
     *
     * @throws ValidationException se a exclusão for bloqueada por uma regra de negócio
     */
    public function excluir(User $colaborador, int $usuarioLogadoId): void
    {
        $this->garantirQueNaoExcluiASiMesmo($colaborador, $usuarioLogadoId);
        $this->garantirQueNaoTemSubordinados($colaborador);

        DB::transaction(function () use ($colaborador) {
            // ─── Desvincula FKs sem onDelete configurado ───────────────────
            // avaliacoes.created_by não tem nullOnDelete nativo no SQLite em algumas versões
            // Usamos DB::table para ignorar Global Scopes e garantir a execução do update.
            \Illuminate\Support\Facades\DB::table('avaliacoes')
                ->where('created_by', $colaborador->id)
                ->update(['created_by' => null]);

            $colaborador->delete();
        });
    }

    // ─────────────────────────── Regras de Negócio ────────────────────────────

    /**
     * Verifica se o usuário logado está editando o seu próprio perfil.
     */
    private function estaEditandoASiMesmo(User $colaborador, int $usuarioLogadoId): bool
    {
        return $colaborador->id === $usuarioLogadoId;
    }

    /**
     * Força que o perfil_acesso e o status permaneçam inalterados quando o usuário
     * está editando a si mesmo (prevenção de auto-lockout).
     *
     * @param  array<string, mixed>  $dados
     * @return array<string, mixed>
     */
    private function preservarPerfilEStatus(array $dados, User $colaborador): array
    {
        $dados['perfil_acesso'] = $colaborador->perfil_acesso->value;
        $dados['status']        = $colaborador->status->value ?? 'ativo';

        return $dados;
    }

    /**
     * Remove a chave 'password' dos dados caso ela esteja vazia,
     * evitando sobrescrever a senha atual.
     *
     * @param  array<string, mixed>  $dados
     */
    private function removerSenhaSeVazia(array &$dados): void
    {
        if (empty($dados['password'])) {
            unset($dados['password']);
        }
    }

    /**
     * Lança ValidationException se o usuário tentar se auto-excluir.
     *
     * @throws ValidationException
     */
    private function garantirQueNaoExcluiASiMesmo(User $colaborador, int $usuarioLogadoId): void
    {
        if ($colaborador->id !== $usuarioLogadoId) {
            return;
        }

        throw ValidationException::withMessages([
            'colaborador' => 'Não é possível excluir: Você não pode excluir a sua própria conta.',
        ]);
    }

    /**
     * Lança ValidationException se o colaborador tiver subordinados ativos.
     *
     * @throws ValidationException
     */
    private function garantirQueNaoTemSubordinados(User $colaborador): void
    {
        if (! $colaborador->subordinados()->exists()) {
            return;
        }

        throw ValidationException::withMessages([
            'colaborador' => 'Não é possível excluir: Este gestor possui colaboradores associados a ele.',
        ]);
    }
}
