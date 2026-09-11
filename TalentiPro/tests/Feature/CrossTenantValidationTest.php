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
 * Suite: CrossTenantValidationTest
 *
 * Cobre a correção do BUG #1 — Cross-Tenant Data Leak.
 * Garante que o campo gestor_id (Departamento) e departamento_id / cargo_superior_id (Cargo)
 * sejam validados EXCLUSIVAMENTE dentro do tenant do usuário autenticado,
 * impedindo que IDs de outras empresas sejam injetados via POST forçado.
 *
 * Cenário de ataque simulado:
 *   - Empresa A possui MasterA e GestorA.
 *   - Empresa B possui MasterB, GestorB, DeptB e CargoB.
 *   - MasterA tenta forçar via POST os IDs pertencentes à Empresa B.
 *   - O sistema deve rejeitar com erro de validação (422 ou redirect com erros).
 */
class CrossTenantValidationTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresaA;
    private Empresa $empresaB;
    private User $masterA;
    private User $masterB;
    private User $gestorB;
    private Departamento $deptB;
    private Cargo $cargoB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresaA = Empresa::factory()->create(['status' => StatusEmpresa::ATIVA]);
        $this->empresaB = Empresa::factory()->create(['status' => StatusEmpresa::ATIVA]);

        $this->masterA = User::factory()->create([
            'empresa_id'    => $this->empresaA->id,
            'perfil_acesso' => UserRole::MASTER,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $this->masterB = User::factory()->create([
            'empresa_id'    => $this->empresaB->id,
            'perfil_acesso' => UserRole::MASTER,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $this->gestorB = User::factory()->create([
            'empresa_id'    => $this->empresaB->id,
            'perfil_acesso' => UserRole::GESTOR,
            'status'        => StatusUsuario::ATIVO,
        ]);

        // Cria recursos na Empresa B para servir como IDs "estrangeiros"
        $this->actingAs($this->masterB);
        $this->deptB  = Departamento::create(['nome' => 'Dept Empresa B']);
        $this->cargoB = Cargo::create(['nome' => 'Cargo Empresa B']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. Departamento — gestor_id cross-tenant ao CRIAR
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_nao_aceita_gestor_de_outra_empresa_ao_criar_departamento(): void
    {
        $this->actingAs($this->masterA)
             ->post(route('departamentos.store'), [
                 'nome'      => 'Dept Empresa A',
                 'gestor_id' => $this->gestorB->id, // ID da Empresa B — ataque!
             ])
             ->assertSessionHasErrors('gestor_id');

        $this->assertDatabaseMissing('departamentos', [
            'nome'       => 'Dept Empresa A',
            'empresa_id' => $this->empresaA->id,
        ]);
    }

    /** @test */
    public function test_aceita_gestor_da_propria_empresa_ao_criar_departamento(): void
    {
        $gestorA = User::factory()->create([
            'empresa_id'    => $this->empresaA->id,
            'perfil_acesso' => UserRole::GESTOR,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $this->actingAs($this->masterA)
             ->post(route('departamentos.store'), [
                 'nome'      => 'Dept Empresa A',
                 'gestor_id' => $gestorA->id, // ID legítimo da Empresa A
             ])
             ->assertRedirect(route('departamentos.index'))
             ->assertSessionHas('success');

        $this->assertDatabaseHas('departamentos', [
            'nome'       => 'Dept Empresa A',
            'empresa_id' => $this->empresaA->id,
            'gestor_id'  => $gestorA->id,
        ]);
    }

    /** @test */
    public function test_aceita_gestor_nulo_ao_criar_departamento(): void
    {
        $this->actingAs($this->masterA)
             ->post(route('departamentos.store'), [
                 'nome'      => 'Dept Sem Gestor',
                 'gestor_id' => null,
             ])
             ->assertRedirect(route('departamentos.index'))
             ->assertSessionHas('success');

        $this->assertDatabaseHas('departamentos', [
            'nome'       => 'Dept Sem Gestor',
            'empresa_id' => $this->empresaA->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. Departamento — gestor_id cross-tenant ao ATUALIZAR
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_nao_aceita_gestor_de_outra_empresa_ao_atualizar_departamento(): void
    {
        $this->actingAs($this->masterA);
        $deptA = Departamento::create(['nome' => 'Dept Original A']);

        $this->put(route('departamentos.update', $deptA->id), [
                 'nome'      => 'Dept Atualizado A',
                 'gestor_id' => $this->gestorB->id, // ID da Empresa B — ataque!
             ])
             ->assertSessionHasErrors('gestor_id');

        // O departamento não deve ter sido alterado
        $this->assertDatabaseHas('departamentos', [
            'id'   => $deptA->id,
            'nome' => 'Dept Original A',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. Cargo — departamento_id cross-tenant ao CRIAR
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_nao_aceita_departamento_de_outra_empresa_ao_criar_cargo(): void
    {
        $this->actingAs($this->masterA)
             ->post(route('cargos.store'), [
                 'nome'            => 'Cargo Empresa A',
                 'departamento_id' => $this->deptB->id, // ID da Empresa B — ataque!
             ])
             ->assertSessionHasErrors('departamento_id');

        $this->assertDatabaseMissing('cargos', [
            'nome'       => 'Cargo Empresa A',
            'empresa_id' => $this->empresaA->id,
        ]);
    }

    /** @test */
    public function test_aceita_departamento_da_propria_empresa_ao_criar_cargo(): void
    {
        $this->actingAs($this->masterA);
        $deptA = Departamento::create(['nome' => 'Dept Legitimo A']);

        $this->post(route('cargos.store'), [
                 'nome'            => 'Cargo Empresa A',
                 'departamento_id' => $deptA->id, // ID legítimo da Empresa A
             ])
             ->assertRedirect(route('cargos.index'))
             ->assertSessionHas('success');

        $this->assertDatabaseHas('cargos', [
            'nome'            => 'Cargo Empresa A',
            'empresa_id'      => $this->empresaA->id,
            'departamento_id' => $deptA->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. Cargo — cargo_superior_id cross-tenant ao CRIAR
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_nao_aceita_cargo_superior_de_outra_empresa_ao_criar_cargo(): void
    {
        $this->actingAs($this->masterA)
             ->post(route('cargos.store'), [
                 'nome'              => 'Cargo Empresa A',
                 'cargo_superior_id' => $this->cargoB->id, // ID da Empresa B — ataque!
             ])
             ->assertSessionHasErrors('cargo_superior_id');

        $this->assertDatabaseMissing('cargos', [
            'nome'       => 'Cargo Empresa A',
            'empresa_id' => $this->empresaA->id,
        ]);
    }

    /** @test */
    public function test_aceita_cargo_superior_da_propria_empresa_ao_criar_cargo(): void
    {
        $this->actingAs($this->masterA);
        $cargoSuperiorA = Cargo::create(['nome' => 'Cargo Superior A']);

        $this->post(route('cargos.store'), [
                 'nome'              => 'Cargo Subordinado A',
                 'cargo_superior_id' => $cargoSuperiorA->id,
             ])
             ->assertRedirect(route('cargos.index'))
             ->assertSessionHas('success');

        $this->assertDatabaseHas('cargos', [
            'nome'              => 'Cargo Subordinado A',
            'empresa_id'        => $this->empresaA->id,
            'cargo_superior_id' => $cargoSuperiorA->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. Cargo — cross-tenant ao ATUALIZAR (departamento_id e cargo_superior_id)
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_nao_aceita_departamento_de_outra_empresa_ao_atualizar_cargo(): void
    {
        $this->actingAs($this->masterA);
        $cargoA = Cargo::create(['nome' => 'Cargo A']);

        $this->put(route('cargos.update', $cargoA->id), [
                 'nome'            => 'Cargo A Renomeado',
                 'departamento_id' => $this->deptB->id, // Ataque cross-tenant
             ])
             ->assertSessionHasErrors('departamento_id');

        $this->assertDatabaseHas('cargos', ['id' => $cargoA->id, 'nome' => 'Cargo A']);
    }

    /** @test */
    public function test_nao_aceita_cargo_superior_de_outra_empresa_ao_atualizar_cargo(): void
    {
        $this->actingAs($this->masterA);
        $cargoA = Cargo::create(['nome' => 'Cargo A']);

        $this->put(route('cargos.update', $cargoA->id), [
                 'nome'              => 'Cargo A Renomeado',
                 'cargo_superior_id' => $this->cargoB->id, // Ataque cross-tenant
             ])
             ->assertSessionHasErrors('cargo_superior_id');

        $this->assertDatabaseHas('cargos', ['id' => $cargoA->id, 'nome' => 'Cargo A']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6. Mensagens de erro customizadas
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_mensagem_de_erro_customizada_para_gestor_de_outra_empresa(): void
    {
        $response = $this->actingAs($this->masterA)
             ->post(route('departamentos.store'), [
                 'nome'      => 'Dept A',
                 'gestor_id' => $this->gestorB->id,
             ]);

        $response->assertSessionHasErrors(['gestor_id']);

        $errors = session('errors');
        $this->assertStringContainsString(
            'não pertence a esta empresa',
            $errors->first('gestor_id')
        );
    }
}
