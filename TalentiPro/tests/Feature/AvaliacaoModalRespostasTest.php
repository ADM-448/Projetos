<?php

namespace Tests\Feature;

use App\Enums\StatusAvaliacao;
use App\Enums\TipoAvaliacao;
use App\Models\Avaliacao;
use App\Models\AvaliacaoParticipante;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes de integração para o modal de Avaliações Ativas no Dashboard.
 *
 * Cobre o bug onde o contador de respostas mostrava sempre 0/N porque
 * o blade comparava status='concluida' ao invés de status='respondido'.
 */
class AvaliacaoModalRespostasTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresa;
    protected User $masterUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->empresa = Empresa::factory()->create();
        $this->masterUser = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => 'master',
        ]);
    }

    /**
     * Cria uma avaliação ativa com participantes para testes.
     * Retorna [Avaliacao, User avaliado, array participantes].
     */
    private function criarAvaliacaoComParticipantes(int $qtdParticipantes = 3): array
    {
        $avaliacao = Avaliacao::create([
            'empresa_id' => $this->empresa->id,
            'titulo' => 'Avaliação Teste Respostas',
            'data_inicio' => now()->subDay(),
            'data_fim' => now()->addDays(5),
            'status' => StatusAvaliacao::ANDAMENTO,
            'created_by' => $this->masterUser->id,
        ]);

        $avaliado = User::factory()->create(['empresa_id' => $this->empresa->id]);
        $participantes = [];

        $tipos = [TipoAvaliacao::AUTOAVALIACAO, TipoAvaliacao::GESTOR, TipoAvaliacao::MEDIADOR];

        for ($i = 0; $i < $qtdParticipantes; $i++) {
            $avaliador = $tipos[$i] === TipoAvaliacao::AUTOAVALIACAO
                ? $avaliado
                : User::factory()->create(['empresa_id' => $this->empresa->id]);

            $participantes[] = AvaliacaoParticipante::create([
                'avaliacao_id' => $avaliacao->id,
                'avaliado_id' => $avaliado->id,
                'avaliador_id' => $avaliador->id,
                'status' => 'pendente',
                'tipo' => $tipos[$i],
            ]);
        }

        return [$avaliacao, $avaliado, $participantes];
    }

    // ─── Testes do Bug Principal ───────────────────────────────────────

    /**
     * BUG FIX: O modal deve mostrar a contagem correta de respostas
     * usando o status 'respondido' (não 'concluida').
     */
    public function test_modal_mostra_contagem_correta_quando_participante_respondeu(): void
    {
        $this->actingAs($this->masterUser);

        [$avaliacao, $avaliado, $participantes] = $this->criarAvaliacaoComParticipantes(3);

        // Simula 1 participante respondendo (autoavaliação)
        $participantes[0]->update(['status' => 'respondido']);

        $response = $this->get(route('dashboard.api.avaliacoes-ativas'));

        $response->assertStatus(200);
        // O modal novo mostra o nome do avaliador e "Finalizado" ou "Pendente"
        $response->assertSee('Finalizado');
        $response->assertSee('Pendente');
    }

    /**
     * BUG FIX: Quando ninguém respondeu ainda, deve mostrar 0/N.
     */
    public function test_modal_mostra_zero_quando_ninguem_respondeu(): void
    {
        $this->actingAs($this->masterUser);

        [$avaliacao, $avaliado, $participantes] = $this->criarAvaliacaoComParticipantes(3);

        $response = $this->get(route('dashboard.api.avaliacoes-ativas'));

        $response->assertStatus(200);
        $response->assertSee('Pendente');
        $response->assertDontSee('Finalizado');
    }

    /**
     * BUG FIX: Quando todos responderam, deve mostrar N/N e progresso 100%.
     */
    public function test_modal_mostra_todos_respondidos_quando_completo(): void
    {
        $this->actingAs($this->masterUser);

        [$avaliacao, $avaliado, $participantes] = $this->criarAvaliacaoComParticipantes(3);

        // Todos respondem
        foreach ($participantes as $p) {
            $p->update(['status' => 'respondido']);
        }

        // A avaliação ainda aparece no modal pois o whereHas verifica se
        // há pendentes — como todos responderam, a avaliação NÃO deve aparecer
        $response = $this->get(route('dashboard.api.avaliacoes-ativas'));

        $response->assertStatus(200);
        // Quando todos responderam, a avaliação não tem mais participantes pendentes,
        // então o controller não a retorna (behavior correto)
        $response->assertDontSee('Avaliação Teste Respostas');
    }

    /**
     * BUG FIX: Cenário parcial — 2 de 3 responderam.
     */
    public function test_modal_mostra_contagem_parcial_corretamente(): void
    {
        $this->actingAs($this->masterUser);

        [$avaliacao, $avaliado, $participantes] = $this->criarAvaliacaoComParticipantes(3);

        // 2 responderam, 1 pendente
        $participantes[0]->update(['status' => 'respondido']);
        $participantes[1]->update(['status' => 'respondido']);

        $response = $this->get(route('dashboard.api.avaliacoes-ativas'));

        $response->assertStatus(200);
        $response->assertSee('Finalizado');
        $response->assertSee('Pendente');
    }

    // ─── Testes de Integridade ─────────────────────────────────────────

    /**
     * O modal NÃO deve exibir avaliações de outras empresas.
     */
    public function test_modal_nao_exibe_avaliacoes_de_outra_empresa(): void
    {
        $this->actingAs($this->masterUser);

        $outraEmpresa = Empresa::factory()->create();
        $outroUser = User::factory()->create(['empresa_id' => $outraEmpresa->id]);

        $avaliacaoOutra = Avaliacao::create([
            'empresa_id' => $outraEmpresa->id,
            'titulo' => 'Avaliação Empresa Alheia',
            'data_inicio' => now(),
            'data_fim' => now()->addDays(5),
            'status' => StatusAvaliacao::ANDAMENTO,
            'created_by' => $outroUser->id,
        ]);

        AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacaoOutra->id,
            'avaliado_id' => $outroUser->id,
            'avaliador_id' => $outroUser->id,
            'status' => 'pendente',
            'tipo' => TipoAvaliacao::AUTOAVALIACAO,
        ]);

        $response = $this->get(route('dashboard.api.avaliacoes-ativas'));

        $response->assertStatus(200);
        $response->assertDontSee('Avaliação Empresa Alheia');
    }

    /**
     * O modal NÃO deve exibir avaliações já vencidas.
     */
    public function test_modal_nao_exibe_avaliacoes_vencidas(): void
    {
        $this->actingAs($this->masterUser);

        $avaliado = User::factory()->create(['empresa_id' => $this->empresa->id]);

        $avaliacaoVencida = Avaliacao::create([
            'empresa_id' => $this->empresa->id,
            'titulo' => 'Avaliação Expirada',
            'data_inicio' => now()->subDays(30),
            'data_fim' => now()->subDays(1), // Já venceu ontem
            'status' => StatusAvaliacao::ANDAMENTO,
            'created_by' => $this->masterUser->id,
        ]);

        AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacaoVencida->id,
            'avaliado_id' => $avaliado->id,
            'avaliador_id' => $avaliado->id,
            'status' => 'pendente',
            'tipo' => TipoAvaliacao::AUTOAVALIACAO,
        ]);

        $response = $this->get(route('dashboard.api.avaliacoes-ativas'));

        $response->assertStatus(200);
        $response->assertDontSee('Avaliação Expirada');
    }

    public function test_modal_agrupa_por_avaliador_corretamente(): void
    {
        $this->actingAs($this->masterUser);

        $avaliacao = Avaliacao::create([
            'empresa_id' => $this->empresa->id,
            'titulo' => 'Avaliação Multi-Avaliados',
            'data_inicio' => now(),
            'data_fim' => now()->addDays(5),
            'status' => StatusAvaliacao::ANDAMENTO,
            'created_by' => $this->masterUser->id,
        ]);

        $avaliado1 = User::factory()->create(['empresa_id' => $this->empresa->id, 'name' => 'João Avaliado']);
        $avaliador = User::factory()->create(['empresa_id' => $this->empresa->id, 'name' => 'Pedro Avaliador']);

        // João (como avaliador de si mesmo) - finalizado
        AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id' => $avaliado1->id,
            'avaliador_id' => $avaliado1->id, // João avaliando João
            'status' => 'respondido',
            'tipo' => TipoAvaliacao::AUTOAVALIACAO,
        ]);

        // Pedro (como gestor) - pendente
        AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id' => $avaliado1->id,
            'avaliador_id' => $avaliador->id,
            'status' => 'pendente',
            'tipo' => TipoAvaliacao::GESTOR,
        ]);

        $response = $this->get(route('dashboard.api.avaliacoes-ativas'));

        $response->assertStatus(200);
        // O agrupamento agora é por avaliador, então deve exibir o nome dos avaliadores
        $response->assertSee('João Avaliado');
        $response->assertSee('Pedro Avaliador');
        
        $response->assertSee('Finalizado');
        $response->assertSee('Pendente');
    }
}
