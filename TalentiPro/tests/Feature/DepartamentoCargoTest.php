<?php

namespace Tests\Feature;

use App\Enums\StatusEmpresa;
use App\Enums\StatusUsuario;
use App\Enums\UserRole;
use App\Models\Cargo;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Suite: DepartamentoCargoTest
 * Cobre: CRUD de Departamentos e Cargos com integridade referencial e isolamento tenant.
 */
class DepartamentoCargoTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private Empresa $empresaB;
    private User $master;
    private User $masterB;
    private User $colaborador;

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

        $this->masterB = User::factory()->create([
            'empresa_id'    => $this->empresaB->id,
            'perfil_acesso' => UserRole::MASTER,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $this->colaborador = User::factory()->create([
            'empresa_id'    => $this->empresa->id,
            'perfil_acesso' => UserRole::COLABORADOR,
            'status'        => StatusUsuario::ATIVO,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 1. CRUD de Departamentos
    // ──────────────────────────────────────────────────────────────────────────

    public function test_master_pode_criar_departamento(): void
    {
        $this->actingAs($this->master)
             ->post(route('departamentos.store'), ['nome' => 'Marketing', 'descricao' => 'Setor de marketing'])
             ->assertRedirect(route('departamentos.index'))
             ->assertSessionHas('success');

        $this->assertDatabaseHas('departamentos', [
            'nome'       => 'Marketing',
            'empresa_id' => $this->empresa->id,
        ]);
    }

    public function test_master_pode_editar_departamento(): void
    {
        $this->actingAs($this->master);
        $dept = Departamento::create(['nome' => 'TI Original']);

        $this->put(route('departamentos.update', $dept->id), ['nome' => 'TI Renomeado'])
             ->assertRedirect(route('departamentos.index'))
             ->assertSessionHas('success');

        $this->assertDatabaseHas('departamentos', ['id' => $dept->id, 'nome' => 'TI Renomeado']);
    }

    public function test_master_pode_excluir_departamento_vazio(): void
    {
        $this->actingAs($this->master);
        $dept = Departamento::create(['nome' => 'Para Deletar']);

        $this->delete(route('departamentos.destroy', $dept->id))
             ->assertRedirect(route('departamentos.index'))
             ->assertSessionHas('success');

        $this->assertDatabaseMissing('departamentos', ['id' => $dept->id]);
    }

    public function test_departamento_com_colaboradores_vinculados_nao_pode_ser_deletado(): void
    {
        $this->actingAs($this->master);
        $dept  = Departamento::create(['nome' => 'Departamento Vinculado']);

        // Vincula um colaborador ao departamento
        $this->colaborador->update(['departamento_id' => $dept->id]);

        $this->delete(route('departamentos.destroy', $dept->id))
             ->assertRedirect()
             ->assertSessionHas('error');

        $this->assertDatabaseHas('departamentos', ['id' => $dept->id]);
    }

    public function test_colaborador_nao_pode_criar_departamento(): void
    {
        $this->actingAs($this->colaborador)
             ->post(route('departamentos.store'), ['nome' => 'Tentativa Invasão'])
             ->assertForbidden();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 2. Isolamento Cross-Tenant em Departamentos
    // ──────────────────────────────────────────────────────────────────────────

    public function test_master_nao_ve_departamentos_de_outra_empresa(): void
    {
        $this->actingAs($this->masterB);
        $deptB = Departamento::create(['nome' => 'Departamento Empresa B']);

        $response = $this->actingAs($this->master)
                         ->get(route('departamentos.index'));

        $response->assertDontSee('Departamento Empresa B');
    }

    public function test_master_nao_pode_editar_departamento_de_outra_empresa(): void
    {
        $this->actingAs($this->masterB);
        $deptB = Departamento::create(['nome' => 'Dept B']);

        $this->actingAs($this->master)
             ->put(route('departamentos.update', $deptB->id), ['nome' => 'Invasão'])
             ->assertStatus(404);

        $this->assertDatabaseHas('departamentos', ['id' => $deptB->id, 'nome' => 'Dept B']);
    }

    public function test_master_nao_pode_deletar_departamento_de_outra_empresa(): void
    {
        $this->actingAs($this->masterB);
        $deptB = Departamento::create(['nome' => 'Dept B para Deletar']);

        $this->actingAs($this->master)
             ->delete(route('departamentos.destroy', $deptB->id))
             ->assertStatus(404);

        $this->assertDatabaseHas('departamentos', ['id' => $deptB->id]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 3. CRUD de Cargos
    // ──────────────────────────────────────────────────────────────────────────

    public function test_master_pode_criar_cargo(): void
    {
        $this->actingAs($this->master);
        $dept = Departamento::create(['nome' => 'TI']);

        $this->post(route('cargos.store'), [
                 'nome'            => 'Desenvolvedor',
                 'departamento_id' => $dept->id,
             ])
             ->assertRedirect(route('cargos.index'))
             ->assertSessionHas('success');

        $this->assertDatabaseHas('cargos', [
            'nome'       => 'Desenvolvedor',
            'empresa_id' => $this->empresa->id,
        ]);
    }

    public function test_cargo_com_colaboradores_vinculados_nao_pode_ser_deletado(): void
    {
        $this->actingAs($this->master);
        $dept  = Departamento::create(['nome' => 'TI']);
        $cargo = Cargo::create(['nome' => 'Dev', 'departamento_id' => $dept->id]);

        // Vincula o colaborador ao cargo
        $this->colaborador->update(['cargo_id' => $cargo->id]);

        $this->delete(route('cargos.destroy', $cargo->id))
             ->assertRedirect()
             ->assertSessionHas('error');

        $this->assertDatabaseHas('cargos', ['id' => $cargo->id]);
    }

    public function test_colaborador_nao_pode_criar_cargo(): void
    {
        $this->actingAs($this->master);
        $dept = Departamento::create(['nome' => 'TI']);

        $this->actingAs($this->colaborador)
             ->post(route('cargos.store'), ['nome' => 'Cargo Invadido', 'departamento_id' => $dept->id])
             ->assertForbidden();
    }
}
