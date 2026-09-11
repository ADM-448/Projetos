<?php

namespace Tests\Feature;

use App\Enums\StatusEmpresa;
use App\Enums\StatusAvaliacao;
use App\Enums\StatusUsuario;
use App\Enums\UserRole;
use App\Models\Avaliacao;
use App\Models\Competencia;
use App\Models\Empresa;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Suite: CompetenciaSkillTest
 * Cobre: CRUD de Competências e Skills com regras de negócio.
 */
class CompetenciaSkillTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private User $master;
    private User $colaborador;

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
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 1. CRUD de Competências
    // ──────────────────────────────────────────────────────────────────────────

    public function test_master_pode_criar_competencia(): void
    {
        $this->actingAs($this->master)
             ->post(route('competencias.store'), ['nome' => 'Liderança'])
             ->assertRedirect(route('competencias.index'));

        $this->assertDatabaseHas('competencias', ['nome' => 'Liderança']);
    }

    public function test_colaborador_nao_pode_criar_competencia(): void
    {
        $this->actingAs($this->colaborador)
             ->postJson(route('competencias.store'), ['nome' => 'Tentativa'])
             ->assertForbidden();
    }

    public function test_master_pode_excluir_competencia_sem_avaliacao(): void
    {
        $competencia = Competencia::create(['nome' => 'Para Deletar']);

        $this->actingAs($this->master)
             ->delete(route('competencias.destroy', $competencia->id))
             ->assertRedirect(route('competencias.index'));

        $this->assertDatabaseMissing('competencias', ['id' => $competencia->id]);
    }

    public function test_nome_competencia_e_obrigatorio(): void
    {
        $this->actingAs($this->master)
             ->postJson(route('competencias.store'), ['nome' => ''])
             ->assertUnprocessable()
             ->assertJsonValidationErrors('nome');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 2. CRUD de Skills
    // ──────────────────────────────────────────────────────────────────────────

    public function test_master_pode_adicionar_skill_a_competencia(): void
    {
        $competencia = Competencia::create(['nome' => 'Comunicação']);

        $this->actingAs($this->master)
             ->postJson(route('skills.store', $competencia->id), ['nome' => 'Escuta ativa'])
             ->assertCreated();

        $this->assertDatabaseHas('skills', [
            'nome'           => 'Escuta ativa',
            'competencia_id' => $competencia->id,
        ]);
    }

    public function test_master_pode_excluir_skill(): void
    {
        $competencia = Competencia::create(['nome' => 'Comunicação']);
        $skill       = Skill::create(['nome' => 'Skill para deletar', 'competencia_id' => $competencia->id]);

        $this->actingAs($this->master)
             ->deleteJson(route('skills.destroy', [$competencia->id, $skill->id]))
             ->assertOk();

        $this->assertDatabaseMissing('skills', ['id' => $skill->id]);
    }

    public function test_colaborador_nao_pode_adicionar_skill(): void
    {
        $competencia = Competencia::create(['nome' => 'Comunicação']);

        $this->actingAs($this->colaborador)
             ->postJson(route('skills.store', $competencia->id), ['nome' => 'Tentativa'])
             ->assertForbidden();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 3. Validação de nome duplicado
    // ──────────────────────────────────────────────────────────────────────────

    public function test_nome_skill_vazio_retorna_erro(): void
    {
        $competencia = Competencia::create(['nome' => 'Comunicação']);

        $this->actingAs($this->master)
             ->postJson(route('skills.store', $competencia->id), ['nome' => ''])
             ->assertUnprocessable()
             ->assertJsonValidationErrors('nome');
    }
}
