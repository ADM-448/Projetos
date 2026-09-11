<?php

namespace Tests\Feature;

use App\Enums\StatusAvaliacao;
use App\Enums\StatusEmpresa;
use App\Enums\StatusUsuario;
use App\Enums\TipoAvaliacao;
use App\Enums\UserRole;
use App\Models\Atividade;
use App\Models\Avaliacao;
use App\Models\AvaliacaoParticipante;
use App\Models\Competencia;
use App\Models\Empresa;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Suite: AvaliacaoFluxoTest
 * Cobre: Fluxo completo de criação, resposta, notificação e controle de acesso de avaliações.
 */
class AvaliacaoFluxoTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private Empresa $empresaB;
    private User $master;
    private User $gestor;
    private User $colaborador;
    private User $masterB;
    private Competencia $competencia;
    private Skill $skill;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa  = Empresa::factory()->create(['status' => StatusEmpresa::ATIVA]);
        $this->empresaB = Empresa::factory()->create(['status' => StatusEmpresa::ATIVA]);

        $this->master = User::factory()->create([
            'empresa_id'    => $this->empresa->id,
            'perfil_acesso' => UserRole::MASTER,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $this->gestor = User::factory()->create([
            'empresa_id'    => $this->empresa->id,
            'perfil_acesso' => UserRole::GESTOR,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $this->colaborador = User::factory()->create([
            'empresa_id'    => $this->empresa->id,
            'perfil_acesso' => UserRole::COLABORADOR,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $this->masterB = User::factory()->create([
            'empresa_id'    => $this->empresaB->id,
            'perfil_acesso' => UserRole::MASTER,
            'status'        => StatusUsuario::ATIVO,
        ]);

        // Competências são globais (sem BelongsToEmpresa), logo sem escopo de tenant
        $this->actingAs($this->master);
        $this->competencia = Competencia::create(['nome' => 'Liderança']);
        $this->skill       = Skill::create(['competencia_id' => $this->competencia->id, 'nome' => 'Delega bem']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 1. Visibilidade na Listagem por Perfil
    // ──────────────────────────────────────────────────────────────────────────

    public function test_gestor_ve_apenas_proprias_avaliacoes(): void
    {
        // Gestor A cria uma avaliação
        $avaliacaoGestor = Avaliacao::create([
            'empresa_id'  => $this->empresa->id,
            'titulo'      => 'Avaliação do Gestor',
            'data_inicio' => now(),
            'data_fim'    => now()->addDays(10),
            'status'      => StatusAvaliacao::ANDAMENTO,
            'created_by'  => $this->gestor->id,
        ]);

        // Master cria outra avaliação
        Avaliacao::create([
            'empresa_id'  => $this->empresa->id,
            'titulo'      => 'Avaliação do Master',
            'data_inicio' => now(),
            'data_fim'    => now()->addDays(10),
            'status'      => StatusAvaliacao::ANDAMENTO,
            'created_by'  => $this->master->id,
        ]);

        $response = $this->actingAs($this->gestor)
                         ->get(route('avaliacoes.index'));

        $response->assertStatus(200);
        $response->assertSee('Avaliação do Gestor');
        $response->assertDontSee('Avaliação do Master');
    }

    public function test_master_ve_todas_avaliacoes_da_empresa(): void
    {
        Avaliacao::create([
            'empresa_id'  => $this->empresa->id,
            'titulo'      => 'Avaliação do Gestor',
            'data_inicio' => now(),
            'data_fim'    => now()->addDays(10),
            'status'      => StatusAvaliacao::ANDAMENTO,
            'created_by'  => $this->gestor->id,
        ]);

        Avaliacao::create([
            'empresa_id'  => $this->empresa->id,
            'titulo'      => 'Avaliação do Master',
            'data_inicio' => now(),
            'data_fim'    => now()->addDays(10),
            'status'      => StatusAvaliacao::ANDAMENTO,
            'created_by'  => $this->master->id,
        ]);

        $response = $this->actingAs($this->master)
                         ->get(route('avaliacoes.index'));

        $response->assertStatus(200);
        $response->assertSee('Avaliação do Gestor');
        $response->assertSee('Avaliação do Master');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 2. Controle de Acesso ao Responder
    // ──────────────────────────────────────────────────────────────────────────

    public function test_participante_pode_acessar_sua_propria_avaliacao(): void
    {
        $avaliacao = Avaliacao::create([
            'empresa_id'  => $this->empresa->id,
            'titulo'      => 'Avaliação X',
            'data_inicio' => now(),
            'data_fim'    => now()->addDays(10),
            'status'      => StatusAvaliacao::ANDAMENTO,
            'created_by'  => $this->master->id,
        ]);

        $participante = AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id'  => $this->colaborador->id,
            'avaliador_id' => $this->colaborador->id,
            'tipo'         => TipoAvaliacao::AUTOAVALIACAO,
            'status'       => 'pendente',
        ]);

        $this->actingAs($this->colaborador)
             ->get(route('avaliacoes.responder', $participante))
             ->assertStatus(200);
    }

    public function test_participante_nao_pode_responder_avaliacao_de_outro(): void
    {
        $avaliacao = Avaliacao::create([
            'empresa_id'  => $this->empresa->id,
            'titulo'      => 'Avaliação Y',
            'data_inicio' => now(),
            'data_fim'    => now()->addDays(10),
            'status'      => StatusAvaliacao::ANDAMENTO,
            'created_by'  => $this->master->id,
        ]);

        // Participante é o gestor, mas quem tenta acessar é o colaborador
        $participante = AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id'  => $this->gestor->id,
            'avaliador_id' => $this->gestor->id,
            'tipo'         => TipoAvaliacao::AUTOAVALIACAO,
            'status'       => 'pendente',
        ]);

        $this->actingAs($this->colaborador)
             ->get(route('avaliacoes.responder', $participante))
             ->assertForbidden();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 3. Notificação ao responder avaliação
    // ──────────────────────────────────────────────────────────────────────────

    public function test_atividade_e_criada_para_o_criador_ao_responder(): void
    {
        $avaliacao = Avaliacao::create([
            'empresa_id'  => $this->empresa->id,
            'titulo'      => 'Avaliação de Notificação',
            'data_inicio' => now(),
            'data_fim'    => now()->addDays(10),
            'status'      => StatusAvaliacao::ANDAMENTO,
            'created_by'  => $this->master->id,
        ]);

        $avaliacao->competencias()->attach($this->competencia->id);

        $participante = AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id'  => $this->colaborador->id,
            'avaliador_id' => $this->colaborador->id,
            'tipo'         => TipoAvaliacao::AUTOAVALIACAO,
            'status'       => 'pendente',
        ]);

        // Monta payload de respostas (uma skill)
        $payload = [
            'respostas' => [
                $this->skill->id => 4,
            ],
        ];

        $this->actingAs($this->colaborador)
             ->post(route('avaliacoes.store-resposta', $participante), $payload);

        // Verifica que a Atividade foi criada para o Master (criador da avaliação)
        $this->assertDatabaseHas('atividades', [
            'user_id'    => $this->master->id,
            'empresa_id' => $this->empresa->id,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 4. Isolamento Cross-Tenant nas Avaliações
    // ──────────────────────────────────────────────────────────────────────────

    public function test_avaliacao_cross_tenant_retorna_404(): void
    {
        // Cria avaliação e participante na Empresa B
        $avaliacaoB = Avaliacao::create([
            'empresa_id'  => $this->empresaB->id,
            'titulo'      => 'Avaliação Empresa B',
            'data_inicio' => now(),
            'data_fim'    => now()->addDays(10),
            'status'      => StatusAvaliacao::ANDAMENTO,
            'created_by'  => $this->masterB->id,
        ]);

        $colabB = User::factory()->create([
            'empresa_id'    => $this->empresaB->id,
            'perfil_acesso' => UserRole::COLABORADOR,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $participanteB = AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacaoB->id,
            'avaliado_id'  => $colabB->id,
            'avaliador_id' => $colabB->id,
            'tipo'         => TipoAvaliacao::AUTOAVALIACAO,
            'status'       => 'pendente',
        ]);

        // Colaborador da Empresa A tenta acessar participante da Empresa B
        $this->actingAs($this->colaborador)
             ->get(route('avaliacoes.responder', $participanteB))
             ->assertForbidden();
    }

    public function test_master_nao_ve_avaliacoes_de_outra_empresa(): void
    {
        Avaliacao::create([
            'empresa_id'  => $this->empresaB->id,
            'titulo'      => 'Só da Empresa B',
            'data_inicio' => now(),
            'data_fim'    => now()->addDays(10),
            'status'      => StatusAvaliacao::ANDAMENTO,
            'created_by'  => $this->masterB->id,
        ]);

        $response = $this->actingAs($this->master)
                         ->get(route('avaliacoes.index'));

        $response->assertDontSee('Só da Empresa B');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 5. Validação de dados ao criar
    // ──────────────────────────────────────────────────────────────────────────

    public function test_criacao_avaliacao_sem_titulo_retorna_erro_validacao(): void
    {
        $payload = [
            'titulo'       => '',
            'data_inicio'  => now()->format('Y-m-d'),
            'data_fim'     => now()->addDays(10)->format('Y-m-d'),
            'competencias' => [$this->competencia->id],
            'avaliadores'  => [$this->gestor->id],
            'avaliados'    => [$this->colaborador->id],
            'mediadores'   => [],
            'filtros'      => [],
        ];

        $this->actingAs($this->master)
             ->post(route('avaliacoes.store'), $payload)
             ->assertSessionHasErrors('titulo');
    }

    public function test_criacao_avaliacao_sem_avaliados_retorna_erro_validacao(): void
    {
        $payload = [
            'titulo'       => 'Título Válido',
            'data_inicio'  => now()->format('Y-m-d'),
            'data_fim'     => now()->addDays(10)->format('Y-m-d'),
            'competencias' => [$this->competencia->id],
            'avaliadores'  => [$this->gestor->id],
            'avaliados'    => [],  // sem avaliados
            'mediadores'   => [],
            'filtros'      => [],
        ];

        $this->actingAs($this->master)
             ->post(route('avaliacoes.store'), $payload)
             ->assertSessionHasErrors('avaliados');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 6. Exclusão de avaliação
    // ──────────────────────────────────────────────────────────────────────────

    public function test_master_pode_excluir_avaliacao_propria_empresa(): void
    {
        $avaliacao = Avaliacao::create([
            'empresa_id'  => $this->empresa->id,
            'titulo'      => 'Para Deletar',
            'data_inicio' => now(),
            'data_fim'    => now()->addDays(10),
            'status'      => StatusAvaliacao::ANDAMENTO,
            'created_by'  => $this->master->id,
        ]);

        $this->actingAs($this->master)
             ->delete(route('avaliacoes.destroy', $avaliacao))
             ->assertRedirect(route('avaliacoes.index'));

        $this->assertDatabaseMissing('avaliacoes', ['id' => $avaliacao->id]);
    }

    public function test_colaborador_nao_pode_excluir_avaliacao(): void
    {
        $avaliacao = Avaliacao::create([
            'empresa_id'  => $this->empresa->id,
            'titulo'      => 'Avaliação Protegida',
            'data_inicio' => now(),
            'data_fim'    => now()->addDays(10),
            'status'      => StatusAvaliacao::ANDAMENTO,
            'created_by'  => $this->master->id,
        ]);

        $this->actingAs($this->colaborador)
             ->delete(route('avaliacoes.destroy', $avaliacao))
             ->assertForbidden();

        $this->assertDatabaseHas('avaliacoes', ['id' => $avaliacao->id]);
    }
}
