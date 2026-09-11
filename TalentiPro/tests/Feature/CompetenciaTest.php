<?php

namespace Tests\Feature;

use App\Enums\StatusEmpresa;
use App\Enums\UserRole;
use App\Models\Competencia;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetenciaTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private User $master;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::factory()->create();

        $this->master = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => UserRole::MASTER,
        ]);
    }

    public function test_master_pode_criar_competencia(): void
    {
        $response = $this->actingAs($this->master)->post(route('competencias.store'), [
            'nome' => 'Comunicação',
            'descricao' => 'Capacidade de comunicação clara',
        ]);

        $response->assertRedirect(route('competencias.index'));
        $this->assertDatabaseHas('competencias', [
            'nome' => 'Comunicação',
            'empresa_id' => $this->empresa->id,
        ]);
    }

    public function test_colaborador_nao_pode_criar_competencia(): void
    {
        $colaborador = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => UserRole::COLABORADOR,
        ]);

        $response = $this->actingAs($colaborador)->post(route('competencias.store'), [
            'nome' => 'Comunicação',
            'descricao' => 'Capacidade',
        ]);

        $response->assertForbidden();
    }

    public function test_isolamento_tenant_competencias(): void
    {
        // Empresa B
        $empresaB = Empresa::factory()->create();
        $masterB = User::factory()->create([
            'empresa_id' => $empresaB->id,
            'perfil_acesso' => UserRole::MASTER,
        ]);

        // Master A cria competência
        $this->actingAs($this->master)->post(route('competencias.store'), [
            'nome' => 'Comp A',
        ]);

        // Master B tenta listar e não deve ver a de A
        $response = $this->actingAs($masterB)->get(route('competencias.index'));
        $response->assertDontSee('Comp A');
        
        $competenciasB = Competencia::where('empresa_id', $empresaB->id)->get();
        $this->assertCount(0, $competenciasB);
    }

    public function test_pode_adicionar_skill_via_ajax(): void
    {
        $this->actingAs($this->master);

        $competencia = Competencia::create([
            'nome' => 'Tecnologia',
        ]);

        $response = $this->postJson(route('skills.store', $competencia->id), [
            'nome' => 'PHP',
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['nome' => 'PHP']);
        
        $this->assertDatabaseHas('skills', [
            'competencia_id' => $competencia->id,
            'nome' => 'PHP',
        ]);
    }
}
