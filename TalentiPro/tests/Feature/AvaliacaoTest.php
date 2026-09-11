<?php

namespace Tests\Feature;

use App\Enums\StatusAvaliacao;
use App\Enums\TipoAvaliacao;
use App\Enums\UserRole;
use App\Models\Avaliacao;
use App\Models\Competencia;
use App\Models\Empresa;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvaliacaoTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private User $master;
    private User $colab1;
    private User $colab2;
    private Competencia $competencia;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::factory()->create();

        $this->master = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => UserRole::MASTER,
        ]);

        $this->colab1 = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => UserRole::COLABORADOR,
        ]);

        $this->colab2 = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => UserRole::COLABORADOR,
        ]);

        $this->actingAs($this->master);

        $this->competencia = Competencia::create([
            'nome' => 'Comunicação',
        ]);

        Skill::create([
            'competencia_id' => $this->competencia->id,
            'nome' => 'Ouve ativamente',
        ]);
    }

    public function test_master_pode_criar_avaliacao(): void
    {
        $payload = [
            'grau' => '360',
            'titulo' => 'Avaliação 360 - Q3',
            'descricao' => 'Seja sincero.',
            'data_inicio' => now()->format('Y-m-d'),
            'data_fim' => now()->addDays(30)->format('Y-m-d'),
            'competencias' => [$this->competencia->id],
            'avaliadores' => [$this->colab1->id, $this->colab2->id],
            'avaliados' => [$this->colab1->id],
            'mediadores' => [],
            'filtros' => []
        ];

        $response = $this->actingAs($this->master)->post(route('avaliacoes.store'), $payload);

        $response->assertRedirect(route('avaliacoes.index'));
        
        $this->assertDatabaseHas('avaliacoes', [
            'titulo' => 'Avaliação 360 - Q3',
            'empresa_id' => $this->empresa->id,
            'status' => StatusAvaliacao::ANDAMENTO->value,
        ]);

        // Valida que inseriu na pivot
        $this->assertDatabaseHas('avaliacao_competencias', [
            'competencia_id' => $this->competencia->id,
        ]);

        // Valida que gerou 2 participantes
        $this->assertDatabaseCount('avaliacao_participantes', 2);
    }

    public function test_isolamento_tenant_na_listagem_avaliacoes(): void
    {
        $empresaB = Empresa::factory()->create();
        $masterB = User::factory()->create([
            'empresa_id' => $empresaB->id,
            'perfil_acesso' => UserRole::MASTER,
        ]);

        Avaliacao::create([
            'empresa_id' => $this->empresa->id,
            'titulo' => 'Avaliação Empresa A',
            'data_inicio' => now(),
            'data_fim' => now()->addDays(10),
            'status' => StatusAvaliacao::ANDAMENTO,
            'created_by' => $this->master->id,
        ]);

        $response = $this->actingAs($masterB)->get(route('avaliacoes.index'));

        $response->assertDontSee('Avaliação Empresa A');
        $this->assertCount(0, Avaliacao::where('empresa_id', $empresaB->id)->get());
    }

    public function test_colaborador_nao_pode_ver_painel_de_gestao_de_avaliacoes(): void
    {
        $response = $this->actingAs($this->colab1)->get(route('avaliacoes.index'));
        $response->assertForbidden();
    }

    public function test_master_pode_criar_avaliacao_com_mediadores(): void
    {
        $mediador = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => UserRole::GESTOR,
        ]);

        $payload = [
            'grau' => '360',
            'titulo' => 'Avaliação 360 - Com Mediação',
            'descricao' => 'Seja sincero.',
            'data_inicio' => now()->format('Y-m-d'),
            'data_fim' => now()->addDays(30)->format('Y-m-d'),
            'competencias' => [$this->competencia->id],
            'avaliadores' => [$this->colab1->id],
            'avaliados' => [$this->colab2->id],
            'mediadores' => [$mediador->id],
            'filtros' => []
        ];

        $response = $this->actingAs($this->master)->post(route('avaliacoes.store'), $payload);

        $response->assertRedirect(route('avaliacoes.index'));

        // Valida que inseriu o participante do tipo mediador
        $this->assertDatabaseHas('avaliacao_participantes', [
            'avaliado_id' => $this->colab2->id,
            'avaliador_id' => $mediador->id,
            'tipo' => 'mediador',
            'status' => 'pendente',
        ]);
    }

    public function test_mediador_designado_pode_acessar_tela_de_mediacao(): void
    {
        $mediador = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => UserRole::GESTOR,
        ]);

        $avaliacao = Avaliacao::create([
            'empresa_id' => $this->empresa->id,
            'titulo' => 'Avaliação com Mediador',
            'data_inicio' => now(),
            'data_fim' => now()->addDays(10),
            'status' => StatusAvaliacao::ANDAMENTO,
            'created_by' => $this->master->id,
            'mediadores' => [$mediador->id]
        ]);

        $response = $this->actingAs($mediador)->get(route('avaliacoes.mediar', [$avaliacao->id, $this->colab1->id]));

        $response->assertStatus(200);
        $response->assertViewIs('avaliacoes.mediar');
    }

    public function test_usuario_nao_mediador_recebe_403_ao_acessar_mediacao(): void
    {
        $mediador = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => UserRole::GESTOR,
        ]);

        $outroGestor = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => UserRole::GESTOR,
        ]);

        $avaliacao = Avaliacao::create([
            'empresa_id' => $this->empresa->id,
            'titulo' => 'Avaliação com Mediador',
            'data_inicio' => now(),
            'data_fim' => now()->addDays(10),
            'status' => StatusAvaliacao::ANDAMENTO,
            'created_by' => $this->master->id,
            'mediadores' => [$mediador->id]
        ]);

        // Outro gestor tenta acessar a mediação
        $response = $this->actingAs($outroGestor)->get(route('avaliacoes.mediar', [$avaliacao->id, $this->colab1->id]));

        $response->assertForbidden();
    }

    public function test_mediador_pode_realizar_fechamento(): void
    {
        $mediador = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => UserRole::GESTOR,
        ]);

        $avaliacao = Avaliacao::create([
            'empresa_id' => $this->empresa->id,
            'titulo' => 'Avaliação com Mediador',
            'data_inicio' => now(),
            'data_fim' => now()->addDays(10),
            'status' => StatusAvaliacao::ANDAMENTO,
            'created_by' => $this->master->id,
            'mediadores' => [$mediador->id]
        ]);

        // Vincula a competência à avaliação
        $avaliacao->competencias()->sync([$this->competencia->id]);

        $skill = \App\Models\Skill::where('competencia_id', $this->competencia->id)->first();

        $payload = [
            'grau' => '360',
            'respostas' => [
                $skill->id => 4,
            ],
            'comentario' => 'Colaborador com ótimo desempenho.',
        ];

        $response = $this->actingAs($mediador)->post(route('avaliacoes.salvar-fechamento', [$avaliacao->id, $this->colab1->id]), $payload);

        $response->assertRedirect(route('avaliacoes.show', $avaliacao->id));

        $this->assertDatabaseHas('avaliacao_fechamentos', [
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id' => $this->colab1->id,
            'responsavel_id' => $mediador->id,
            'nota_final' => 4.00,
            'comentario' => 'Colaborador com ótimo desempenho.',
        ]);

        // Valida que inseriu a resposta individual do mediador na tabela de respostas
        $this->assertDatabaseHas('avaliacao_respostas', [
            'nota' => 4,
            'skill_id' => $skill->id,
        ]);
    }

    public function test_mediador_deve_pertencer_a_mesma_empresa(): void
    {
        $empresaB = Empresa::factory()->create();
        $mediadorB = User::factory()->create([
            'empresa_id' => $empresaB->id,
            'perfil_acesso' => UserRole::GESTOR,
        ]);

        $payload = [
            'grau' => '360',
            'titulo' => 'Avaliação Invalida',
            'descricao' => 'Seja sincero.',
            'data_inicio' => now()->format('Y-m-d'),
            'data_fim' => now()->addDays(30)->format('Y-m-d'),
            'competencias' => [$this->competencia->id],
            'avaliadores' => [$this->colab1->id],
            'avaliados' => [$this->colab2->id],
            'mediadores' => [$mediadorB->id], // Mediador de outra empresa!
            'filtros' => []
        ];

        $response = $this->actingAs($this->master)->post(route('avaliacoes.store'), $payload);

        // Deve retornar erro de validação (rejeitar mediadorB)
        $response->assertSessionHasErrors(['mediadores.0']);
    }

    public function test_gestor_pode_criar_avaliacao_para_colaborador_do_seu_setor(): void
    {
        $departamento = \App\Models\Departamento::create([
            'nome' => 'Tecnologia',
            'empresa_id' => $this->empresa->id,
        ]);

        $gestor = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => UserRole::GESTOR,
            'departamento_id' => $departamento->id,
        ]);

        $colabMesmoSetor = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => UserRole::COLABORADOR,
            'departamento_id' => $departamento->id,
        ]);

        $payload = [
            'grau' => '360',
            'titulo' => 'Avaliação Setor Tecnologia',
            'descricao' => 'Teste mesmo setor.',
            'data_inicio' => now()->format('Y-m-d'),
            'data_fim' => now()->addDays(30)->format('Y-m-d'),
            'competencias' => [$this->competencia->id],
            'avaliadores' => [$colabMesmoSetor->id],
            'avaliados' => [$colabMesmoSetor->id],
            'mediadores' => [],
            'filtros' => []
        ];

        $response = $this->actingAs($gestor)->post(route('avaliacoes.store'), $payload);

        $response->assertRedirect(route('avaliacoes.index'));
        $this->assertDatabaseHas('avaliacoes', [
            'titulo' => 'Avaliação Setor Tecnologia',
            'empresa_id' => $this->empresa->id,
            'created_by' => $gestor->id,
        ]);
    }

    public function test_gestor_pode_criar_avaliacao_para_colaborador_sem_setor(): void
    {
        $departamento = \App\Models\Departamento::create([
            'nome' => 'Tecnologia',
            'empresa_id' => $this->empresa->id,
        ]);

        $gestor = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => UserRole::GESTOR,
            'departamento_id' => $departamento->id,
        ]);

        $colabSemSetor = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => UserRole::COLABORADOR,
            'departamento_id' => null,
        ]);

        $payload = [
            'grau' => '360',
            'titulo' => 'Avaliação Colaborador Sem Setor',
            'descricao' => 'Teste sem setor.',
            'data_inicio' => now()->format('Y-m-d'),
            'data_fim' => now()->addDays(30)->format('Y-m-d'),
            'competencias' => [$this->competencia->id],
            'avaliadores' => [$colabSemSetor->id],
            'avaliados' => [$colabSemSetor->id],
            'mediadores' => [],
            'filtros' => []
        ];

        $response = $this->actingAs($gestor)->post(route('avaliacoes.store'), $payload);

        $response->assertRedirect(route('avaliacoes.index'));
        $this->assertDatabaseHas('avaliacoes', [
            'titulo' => 'Avaliação Colaborador Sem Setor',
            'empresa_id' => $this->empresa->id,
            'created_by' => $gestor->id,
        ]);
    }

    public function test_gestor_nao_pode_criar_avaliacao_para_colaborador_de_outro_setor(): void
    {
        $departamentoA = \App\Models\Departamento::create([
            'nome' => 'Tecnologia',
            'empresa_id' => $this->empresa->id,
        ]);

        $departamentoB = \App\Models\Departamento::create([
            'nome' => 'Financeiro',
            'empresa_id' => $this->empresa->id,
        ]);

        $gestor = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => UserRole::GESTOR,
            'departamento_id' => $departamentoA->id,
        ]);

        $colabOutroSetor = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => UserRole::COLABORADOR,
            'departamento_id' => $departamentoB->id,
        ]);

        $payload = [
            'grau' => '360',
            'titulo' => 'Avaliação Outro Setor',
            'descricao' => 'Deverá falhar na validação.',
            'data_inicio' => now()->format('Y-m-d'),
            'data_fim' => now()->addDays(30)->format('Y-m-d'),
            'competencias' => [$this->competencia->id],
            'avaliadores' => [$colabOutroSetor->id],
            'avaliados' => [$colabOutroSetor->id],
            'mediadores' => [],
            'filtros' => []
        ];

        $response = $this->actingAs($gestor)->post(route('avaliacoes.store'), $payload);

        $response->assertSessionHasErrors(['avaliados.0']);
    }

    public function test_master_pode_criar_avaliacao_para_colaborador_de_qualquer_setor(): void
    {
        $departamento = \App\Models\Departamento::create([
            'nome' => 'Financeiro',
            'empresa_id' => $this->empresa->id,
        ]);

        $colabSetorFinanceiro = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => UserRole::COLABORADOR,
            'departamento_id' => $departamento->id,
        ]);

        $payload = [
            'grau' => '360',
            'titulo' => 'Avaliação Master Sem Restrição',
            'descricao' => 'Master pode tudo na empresa.',
            'data_inicio' => now()->format('Y-m-d'),
            'data_fim' => now()->addDays(30)->format('Y-m-d'),
            'competencias' => [$this->competencia->id],
            'avaliadores' => [$colabSetorFinanceiro->id],
            'avaliados' => [$colabSetorFinanceiro->id],
            'mediadores' => [],
            'filtros' => []
        ];

        $response = $this->actingAs($this->master)->post(route('avaliacoes.store'), $payload);

        $response->assertRedirect(route('avaliacoes.index'));
        $this->assertDatabaseHas('avaliacoes', [
            'titulo' => 'Avaliação Master Sem Restrição',
            'empresa_id' => $this->empresa->id,
            'created_by' => $this->master->id,
        ]);
    }

    public function test_mediador_ao_fechar_atualiza_status_do_participante_para_respondido(): void
    {
        $mediador = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => UserRole::GESTOR,
        ]);

        $avaliacao = Avaliacao::create([
            'empresa_id' => $this->empresa->id,
            'titulo' => 'Avaliação com Mediador',
            'data_inicio' => now(),
            'data_fim' => now()->addDays(10),
            'status' => StatusAvaliacao::ANDAMENTO,
            'created_by' => $this->master->id,
            'mediadores' => [$mediador->id]
        ]);

        // Vincula a competência à avaliação
        $avaliacao->competencias()->sync([$this->competencia->id]);

        $skill = \App\Models\Skill::where('competencia_id', $this->competencia->id)->first();

        // Cria o registro na pivot
        $participante = \App\Models\AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id' => $this->colab1->id,
            'avaliador_id' => $mediador->id,
            'tipo' => 'mediador',
            'status' => 'pendente',
        ]);

        $payload = [
            'grau' => '360',
            'respostas' => [
                $skill->id => 5,
            ],
            'comentario' => 'Concluído pelo mediador',
        ];

        // Executa a rota para salvar o fechamento (mediar)
        $response = $this->actingAs($mediador)->post(
            route('avaliacoes.salvar-fechamento', [$avaliacao->id, $this->colab1->id]), 
            $payload
        );

        $response->assertRedirect(route('avaliacoes.show', $avaliacao->id));

        // Valida que o fechamento foi salvo com nota_final = 5.00
        $this->assertDatabaseHas('avaliacao_fechamentos', [
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id' => $this->colab1->id,
            'responsavel_id' => $mediador->id,
            'nota_final' => 5.00,
        ]);

        // Valida que o participante mediador teve o status atualizado para 'respondido'
        $this->assertDatabaseHas('avaliacao_participantes', [
            'id' => $participante->id,
            'status' => 'respondido',
        ]);
    }

    public function test_mediador_designado_gestor_pode_visualizar_detalhes_da_avaliacao(): void
    {
        $mediador = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => UserRole::GESTOR,
        ]);

        $avaliacao = Avaliacao::create([
            'empresa_id' => $this->empresa->id,
            'titulo' => 'Avaliação com Mediador',
            'data_inicio' => now(),
            'data_fim' => now()->addDays(10),
            'status' => StatusAvaliacao::ANDAMENTO,
            'created_by' => $this->master->id,
            'mediadores' => [$mediador->id]
        ]);

        // O gestor tenta ver a avaliação criada pelo Master onde ele é mediador
        $response = $this->actingAs($mediador)->get(route('avaliacoes.show', $avaliacao->id));

        $response->assertStatus(200);
        $response->assertViewIs('avaliacoes.show');
    }
}
