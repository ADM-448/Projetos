<?php

namespace Tests\Feature;

use App\Models\Departamento;
use App\Models\DiscAssessment;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes para a tela DISC da Equipe.
 *
 * Cobre o bug onde o gestor não via nenhum colaborador porque o filtro
 * usava 'gestor_id' (inexistente na tabela users) ao invés de filtrar
 * pelo departamento_id, como fazem Avaliações e PDIs.
 */
class DiscEquipeGestorTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresa;
    protected Departamento $departamento;
    protected User $gestor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::factory()->create();

        $this->gestor = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => 'gestor',
        ]);

        $this->departamento = Departamento::create([
            'nome' => 'Recursos Humanos',
            'empresa_id' => $this->empresa->id,
            'gestor_id' => $this->gestor->id,
        ]);

        // Associa o gestor ao departamento
        $this->gestor->update(['departamento_id' => $this->departamento->id]);
    }

    // ─── Bug Fix: Gestor deve ver colaboradores do departamento ────────

    /**
     * Gestor deve ver os colaboradores do seu departamento na tela DISC.
     */
    public function test_gestor_ve_colaboradores_do_seu_departamento(): void
    {
        $colab1 = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'departamento_id' => $this->departamento->id,
            'name' => 'Colaborador No Depto',
        ]);

        $this->actingAs($this->gestor);
        $response = $this->get(route('disc.equipe'));

        $response->assertStatus(200);
        $response->assertSee('Colaborador No Depto');
    }

    /**
     * Gestor NÃO deve ver colaboradores sem departamento (null).
     */
    public function test_gestor_nao_ve_colaboradores_sem_departamento(): void
    {
        $colabSemDepto = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'departamento_id' => null,
            'name' => 'Colaborador Sem Depto',
        ]);

        $this->actingAs($this->gestor);
        $response = $this->get(route('disc.equipe'));

        $response->assertStatus(200);
        $response->assertDontSee('Colaborador Sem Depto');
    }

    /**
     * Gestor deve ver colaboradores de departamento onde ele é o gestor responsável.
     */
    public function test_gestor_ve_colaboradores_de_departamento_onde_e_responsavel(): void
    {
        $segundoDepto = Departamento::create([
            'nome' => 'Operações',
            'empresa_id' => $this->empresa->id,
            'gestor_id' => $this->gestor->id,
        ]);

        $colabOperacoes = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'departamento_id' => $segundoDepto->id,
            'name' => 'Colaborador Operacoes',
        ]);

        $this->actingAs($this->gestor);
        $response = $this->get(route('disc.equipe'));

        $response->assertStatus(200);
        $response->assertSee('Colaborador Operacoes');
    }

    /**
     * Gestor NÃO deve ver colaboradores de OUTRO departamento.
     */
    public function test_gestor_nao_ve_colaboradores_de_outro_departamento(): void
    {
        $outroDepto = Departamento::create([
            'nome' => 'Financeiro',
            'empresa_id' => $this->empresa->id,
        ]);

        $colabOutroDepto = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'departamento_id' => $outroDepto->id,
            'name' => 'Colaborador Financeiro',
        ]);

        $this->actingAs($this->gestor);
        $response = $this->get(route('disc.equipe'));

        $response->assertStatus(200);
        $response->assertDontSee('Colaborador Financeiro');
    }

    /**
     * Gestor NÃO deve ver a si mesmo na lista.
     */
    public function test_gestor_nao_ve_a_si_mesmo(): void
    {
        // Cria um colaborador no depto para garantir que a lista não está vazia
        User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'departamento_id' => $this->departamento->id,
        ]);

        $this->actingAs($this->gestor);
        $response = $this->get(route('disc.equipe'));

        $response->assertStatus(200);

        // Verifica via viewData que o gestor não está na lista de colaboradores
        // (assertDontSee não funciona pois o nome aparece no header "Olá, Nome")
        $colaboradores = $response->viewData('colaboradores');
        $this->assertFalse(
            $colaboradores->contains('id', $this->gestor->id),
            'O gestor não deve aparecer na própria lista de colaboradores'
        );
    }

    // ─── Estatísticas da Equipe ────────────────────────────────────────

    /**
     * Os contadores (Total, Concluídos, Pendentes) devem refletir
     * apenas os colaboradores do departamento do gestor.
     */
    public function test_estatisticas_refletem_apenas_equipe_do_gestor(): void
    {
        // 2 colaboradores no depto do gestor
        $colab1 = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'departamento_id' => $this->departamento->id,
        ]);
        $colab2 = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'departamento_id' => $this->departamento->id,
        ]);

        // 1 colaborador em outro depto (não deve contar)
        $outroDepto = Departamento::create([
            'nome' => 'TI',
            'empresa_id' => $this->empresa->id,
        ]);
        User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'departamento_id' => $outroDepto->id,
        ]);

        // Colab1 fez o DISC
        DiscAssessment::create([
            'user_id' => $colab1->id,
            'primary_profile' => 'DOMINANCE',
            'secondary_profile' => 'INFLUENCE',
            'score_d' => 30,
            'score_i' => 25,
            'score_s' => 20,
            'score_c' => 15,
        ]);

        $this->actingAs($this->gestor);
        $response = $this->get(route('disc.equipe'));

        $response->assertStatus(200);
        // Total deve ser 2 (apenas do depto do gestor, excluindo ele mesmo)
        $response->assertViewHas('totalColaboradores', 2);
        $response->assertViewHas('totalConcluidos', 1);
        $response->assertViewHas('totalPendentes', 1);
    }

    // ─── Controle de Acesso ────────────────────────────────────────────

    /**
     * Master deve ver todos os colaboradores da empresa.
     */
    public function test_master_ve_todos_colaboradores(): void
    {
        $master = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => 'master',
            'name' => 'Master User',
        ]);

        $outroDepto = Departamento::create([
            'nome' => 'Marketing',
            'empresa_id' => $this->empresa->id,
        ]);

        $colab1 = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'departamento_id' => $this->departamento->id,
            'name' => 'Colab RH',
        ]);
        $colab2 = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'departamento_id' => $outroDepto->id,
            'name' => 'Colab Marketing',
        ]);

        $this->actingAs($master);
        $response = $this->get(route('disc.equipe'));

        $response->assertStatus(200);
        $response->assertSee('Colab RH');
        $response->assertSee('Colab Marketing');
    }

    /**
     * Colaborador não pode acessar a tela DISC da equipe.
     */
    public function test_colaborador_nao_acessa_disc_equipe(): void
    {
        $colaborador = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => 'colaborador',
        ]);

        $this->actingAs($colaborador);
        $response = $this->get(route('disc.equipe'));

        $response->assertStatus(403);
    }

    // ─── Filtros ───────────────────────────────────────────────────────

    /**
     * Filtro de busca por nome funciona para o gestor.
     */
    public function test_gestor_pode_filtrar_por_nome(): void
    {
        $colab1 = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'departamento_id' => $this->departamento->id,
            'name' => 'Ana Silva',
        ]);
        $colab2 = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'departamento_id' => $this->departamento->id,
            'name' => 'Bruno Santos',
        ]);

        $this->actingAs($this->gestor);
        $response = $this->get(route('disc.equipe', ['search' => 'Ana']));

        $response->assertStatus(200);
        $response->assertSee('Ana Silva');
        $response->assertDontSee('Bruno Santos');
    }
}
