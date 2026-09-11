<?php

namespace Tests\Feature;

use App\Enums\StatusAvaliacao;
use App\Enums\StatusEmpresa;
use App\Enums\StatusUsuario;
use App\Enums\TipoAvaliacao;
use App\Enums\UserRole;
use App\Models\Avaliacao;
use App\Models\AvaliacaoParticipante;
use App\Models\AvaliacaoResposta;
use App\Models\Competencia;
use App\Models\Empresa;
use App\Models\Skill;
use Illuminate\Support\Facades\DB;
use App\Models\Solicitacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Suite: AvaliacaoRespostaSegurancaTest
 *
 * Cobre a correção do BUG #2 — Edição de Resposta Sem Solicitação Aprovada.
 * Garante que um colaborador NÃO consiga reeditar sua avaliação após respondê-la
 * sem uma Solicitação de Reabertura aprovada válida.
 *
 * Cenários testados:
 *   1. Avaliação pendente → resposta aceita normalmente.
 *   2. Avaliação já respondida → GET bloqueado sem solicitação.
 *   3. Avaliação já respondida → POST bloqueado sem solicitação.
 *   4. Avaliação já respondida + solicitação aprovada → reedição permitida.
 *   5. Após reedição com solicitação aprovada → solicitação marcada como 'utilizada'.
 *   6. Tentativa de nova reedição com solicitação já utilizada → bloqueada.
 *   7. Solicitação pendente (não aprovada) não habilita reedição.
 */
class AvaliacaoRespostaSegurancaTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private User $master;
    private User $colaborador;
    private Avaliacao $avaliacao;
    private AvaliacaoParticipante $participante;
    private Competencia $competencia;
    private Skill $skill;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::factory()->create(['status' => StatusEmpresa::ATIVA]);

        $this->master = User::factory()->create([
            'empresa_id'    => $this->empresa->id,
            'perfil_acesso' => UserRole::MASTER,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $this->colaborador = User::factory()->create([
            'empresa_id'    => $this->empresa->id,
            'perfil_acesso' => UserRole::COLABORADOR,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $this->actingAs($this->master);
        $this->competencia = Competencia::create(['nome' => 'Comunicação']);
        $this->skill       = Skill::create(['competencia_id' => $this->competencia->id, 'nome' => 'Ouve ativamente']);

        $this->avaliacao = Avaliacao::create([
            'empresa_id'  => $this->empresa->id,
            'titulo'      => 'Avaliação de Segurança',
            'data_inicio' => now(),
            'data_fim'    => now()->addDays(10),
            'status'      => StatusAvaliacao::ANDAMENTO,
            'created_by'  => $this->master->id,
        ]);

        $this->avaliacao->competencias()->attach($this->competencia->id);

        $this->participante = AvaliacaoParticipante::create([
            'avaliacao_id' => $this->avaliacao->id,
            'avaliado_id'  => $this->colaborador->id,
            'avaliador_id' => $this->colaborador->id,
            'tipo'         => TipoAvaliacao::AUTOAVALIACAO,
            'status'       => 'pendente',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. Fluxo normal — avaliação pendente → aceita resposta
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_colaborador_pode_acessar_avaliacao_pendente(): void
    {
        $this->actingAs($this->colaborador)
             ->get(route('avaliacoes.responder', $this->participante))
             ->assertStatus(200);
    }

    /** @test */
    public function test_colaborador_pode_responder_avaliacao_pendente(): void
    {
        $this->actingAs($this->colaborador)
             ->post(route('avaliacoes.store-resposta', $this->participante), [
                 'respostas' => [$this->skill->id => 4],
             ])
             ->assertRedirect(route('avaliacoes.minhas'))
             ->assertSessionHas('success');

        $this->assertDatabaseHas('avaliacao_participantes', [
            'id'     => $this->participante->id,
            'status' => 'respondido',
        ]);

        $this->assertDatabaseHas('avaliacao_respostas', [
            'avaliacao_participante_id' => $this->participante->id,
            'skill_id'                  => $this->skill->id,
            'nota'                      => 4,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. GET bloqueado após resposta, sem solicitação aprovada
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_colaborador_nao_consegue_acessar_formulario_de_avaliacao_ja_respondida(): void
    {
        // Marca a participação como já respondida
        $this->participante->update(['status' => 'respondido']);

        $this->actingAs($this->colaborador)
             ->get(route('avaliacoes.responder', $this->participante))
             ->assertRedirect(route('avaliacoes.minhas'))
             ->assertSessionHas('error');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. POST bloqueado após resposta, sem solicitação aprovada
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_colaborador_nao_consegue_reeditar_avaliacao_sem_solicitacao(): void
    {
        // Insere resposta original
        $this->participante->update(['status' => 'respondido']);
        AvaliacaoResposta::create([
            'avaliacao_participante_id' => $this->participante->id,
            'skill_id'                  => $this->skill->id,
            'nota'                      => 3,
        ]);

        // Tenta sobrescrever com nota diferente
        $this->actingAs($this->colaborador)
             ->post(route('avaliacoes.store-resposta', $this->participante), [
                 'respostas' => [$this->skill->id => 5], // Nota adulterada
             ])
             ->assertRedirect(route('avaliacoes.minhas'))
             ->assertSessionHas('error');

        // Nota original deve permanecer intacta
        $this->assertDatabaseHas('avaliacao_respostas', [
            'avaliacao_participante_id' => $this->participante->id,
            'skill_id'                  => $this->skill->id,
            'nota'                      => 3,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. Reedição PERMITIDA com solicitação aprovada
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_colaborador_consegue_reeditar_com_solicitacao_aprovada(): void
    {
        $this->participante->update(['status' => 'respondido']);
        AvaliacaoResposta::create([
            'avaliacao_participante_id' => $this->participante->id,
            'skill_id'                  => $this->skill->id,
            'nota'                      => 3,
        ]);

        // Cria solicitação de reabertura aprovada para o colaborador
        Solicitacao::create([
            'empresa_id'    => $this->empresa->id,
            'user_id'       => $this->colaborador->id,
            'avaliacao_id'  => $this->avaliacao->id,
            'tipo'          => 'reabertura',
            'justificativa' => 'Errei uma resposta e gostaria de corrigir.',
            'status'        => 'aprovada',
        ]);

        // Acessa o formulário — deve estar permitido
        $this->actingAs($this->colaborador)
             ->get(route('avaliacoes.responder', $this->participante))
             ->assertStatus(200);

        // Submete nova resposta
        $this->actingAs($this->colaborador)
             ->post(route('avaliacoes.store-resposta', $this->participante), [
                 'respostas' => [$this->skill->id => 5],
             ])
             ->assertRedirect(route('avaliacoes.minhas'))
             ->assertSessionHas('success');

        // Verifica que a nova nota foi salva
        $this->assertDatabaseHas('avaliacao_respostas', [
            'avaliacao_participante_id' => $this->participante->id,
            'skill_id'                  => $this->skill->id,
            'nota'                      => 5,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. Solicitação aprovada deve ser invalidada após uso
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_solicitacao_e_marcada_como_utilizada_apos_reeditar(): void
    {
        $this->participante->update(['status' => 'respondido']);

        $solicitacao = Solicitacao::create([
            'empresa_id'    => $this->empresa->id,
            'user_id'       => $this->colaborador->id,
            'avaliacao_id'  => $this->avaliacao->id,
            'tipo'          => 'reabertura',
            'justificativa' => 'Preciso corrigir.',
            'status'        => 'aprovada',
        ]);

        $this->actingAs($this->colaborador)
             ->post(route('avaliacoes.store-resposta', $this->participante), [
                 'respostas' => [$this->skill->id => 5],
             ]);

        // Solicitação deve ter sido marcada como 'utilizada'
        $this->assertDatabaseHas('solicitacoes', [
            'id'     => $solicitacao->id,
            'status' => 'utilizada',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6. Segunda reedição com mesma solicitação já utilizada — bloqueada
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_segunda_reedição_com_solicitacao_utilizada_e_bloqueada(): void
    {
        $this->participante->update(['status' => 'respondido']);

        // Insere diretamente via DB para contornar o unique constraint do Eloquent
        // (o status 'utilizada' não passa pela validação do Model, mas existe no banco)
        DB::table('solicitacoes')->insert([
            'empresa_id'    => $this->empresa->id,
            'user_id'       => $this->colaborador->id,
            'avaliacao_id'  => $this->avaliacao->id,
            'tipo'          => 'reabertura',
            'justificativa' => 'Preciso corrigir.',
            'status'        => 'utilizada', // Já foi usada!
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $this->actingAs($this->colaborador)
             ->post(route('avaliacoes.store-resposta', $this->participante), [
                 'respostas' => [$this->skill->id => 5],
             ])
             ->assertRedirect(route('avaliacoes.minhas'))
             ->assertSessionHas('error');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 7. Solicitação pendente (não aprovada) NÃO habilita reedição
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_solicitacao_pendente_nao_habilita_reedição(): void
    {
        $this->participante->update(['status' => 'respondido']);

        // Solicitação pendente (não foi aprovada ainda)
        Solicitacao::create([
            'empresa_id'    => $this->empresa->id,
            'user_id'       => $this->colaborador->id,
            'avaliacao_id'  => $this->avaliacao->id,
            'tipo'          => 'reabertura',
            'justificativa' => 'Aguardando aprovação.',
            'status'        => 'pendente',
        ]);

        $this->actingAs($this->colaborador)
             ->post(route('avaliacoes.store-resposta', $this->participante), [
                 'respostas' => [$this->skill->id => 5],
             ])
             ->assertRedirect(route('avaliacoes.minhas'))
             ->assertSessionHas('error');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 8. Solicitação recusada NÃO habilita reedição
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_solicitacao_recusada_nao_habilita_reedição(): void
    {
        $this->participante->update(['status' => 'respondido']);

        Solicitacao::create([
            'empresa_id'    => $this->empresa->id,
            'user_id'       => $this->colaborador->id,
            'avaliacao_id'  => $this->avaliacao->id,
            'tipo'          => 'reabertura',
            'justificativa' => 'Não foi aprovada.',
            'status'        => 'recusada',
        ]);

        $this->actingAs($this->colaborador)
             ->post(route('avaliacoes.store-resposta', $this->participante), [
                 'respostas' => [$this->skill->id => 5],
             ])
             ->assertRedirect(route('avaliacoes.minhas'))
             ->assertSessionHas('error');
    }
}
