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
 * Suite: ColaboradorCrudTest
 * Cobre: CRUD completo de colaboradores com regras de negócio e isolamento de tenant.
 */
class ColaboradorCrudTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private Empresa $empresaB;
    private User $master;
    private User $gestor;
    private User $colaborador;
    private User $masterB;
    private Departamento $departamento;
    private Cargo $cargo;

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

        // Cria departamento e cargo para a empresa A
        $this->actingAs($this->master);
        $this->departamento = Departamento::create(['nome' => 'TI', 'descricao' => 'Tecnologia']);
        $this->cargo        = Cargo::create(['nome' => 'Dev', 'departamento_id' => $this->departamento->id]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 1. Criação de colaborador
    // ──────────────────────────────────────────────────────────────────────────

    public function test_master_pode_criar_colaborador(): void
    {
        $payload = [
            'name'          => 'Novo Colaborador',
            'email'         => 'novo@empresa.com',
            'password'      => 'Senha@123',
            'perfil_acesso' => UserRole::COLABORADOR->value,
            'status'        => StatusUsuario::ATIVO->value,
            'departamento_id' => $this->departamento->id,
            'cargo_id'      => $this->cargo->id,
        ];

        $this->actingAs($this->master)
             ->post(route('colaboradores.store'), $payload)
             ->assertRedirect(route('colaboradores.index'))
             ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email'      => 'novo@empresa.com',
            'empresa_id' => $this->empresa->id,
        ]);
    }

    public function test_gestor_nao_pode_criar_colaborador(): void
    {
        $payload = [
            'name'          => 'Colaborador do Gestor',
            'email'         => 'colabgestor@empresa.com',
            'password'      => 'Senha@123',
            'perfil_acesso' => UserRole::COLABORADOR->value,
            'status'        => StatusUsuario::ATIVO->value,
            'departamento_id' => $this->departamento->id,
            'cargo_id'      => $this->cargo->id,
        ];

        $this->actingAs($this->gestor)
             ->post(route('colaboradores.store'), $payload)
             ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'colabgestor@empresa.com']);
    }

    public function test_colaborador_comum_nao_pode_criar_colaborador(): void
    {
        $payload = [
            'name'          => 'Tentativa',
            'email'         => 'tentativa@empresa.com',
            'password'      => 'Senha@123',
            'perfil_acesso' => UserRole::COLABORADOR->value,
            'status'        => StatusUsuario::ATIVO->value,
        ];

        $this->actingAs($this->colaborador)
             ->post(route('colaboradores.store'), $payload)
             ->assertForbidden();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 2. Validações de negócio
    // ──────────────────────────────────────────────────────────────────────────

    public function test_email_duplicado_na_mesma_empresa_e_bloqueado(): void
    {
        $payload = [
            'name'          => 'Duplicado',
            'email'         => $this->colaborador->email, // email já existente
            'password'      => 'Senha@123',
            'perfil_acesso' => UserRole::COLABORADOR->value,
            'status'        => StatusUsuario::ATIVO->value,
            'departamento_id' => $this->departamento->id,
            'cargo_id'      => $this->cargo->id,
        ];

        $this->actingAs($this->master)
             ->post(route('colaboradores.store'), $payload)
             ->assertSessionHasErrors('email');
    }

    public function test_colaborador_nao_pode_ser_gestor_de_si_mesmo(): void
    {
        $payload = [
            'name'          => $this->colaborador->name,
            'email'         => $this->colaborador->email,
            'perfil_acesso' => UserRole::COLABORADOR->value,
            'status'        => StatusUsuario::ATIVO->value,
            'gestor_id'     => $this->colaborador->id, // tenta ser gestor de si mesmo
        ];

        $this->actingAs($this->master)
             ->put(route('colaboradores.update', $this->colaborador->id), $payload)
             ->assertSessionHasErrors('gestor_id');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 3. Isolamento de tenant (empresa_id injetado automaticamente)
    // ──────────────────────────────────────────────────────────────────────────

    public function test_empresa_id_e_injetado_automaticamente_ao_criar(): void
    {
        $payload = [
            'name'          => 'Colab Injetado',
            'email'         => 'injetado@empresa.com',
            'password'      => 'Senha@123',
            'perfil_acesso' => UserRole::COLABORADOR->value,
            'status'        => StatusUsuario::ATIVO->value,
            'departamento_id' => $this->departamento->id,
            'cargo_id'      => $this->cargo->id,
        ];

        $this->actingAs($this->master)
             ->post(route('colaboradores.store'), $payload);

        $this->assertDatabaseHas('users', [
            'email'      => 'injetado@empresa.com',
            'empresa_id' => $this->empresa->id, // deve ser da empresa logada
        ]);
    }

    public function test_master_nao_ve_colaboradores_de_outra_empresa(): void
    {
        User::factory()->create([
            'empresa_id'    => $this->empresaB->id,
            'perfil_acesso' => UserRole::COLABORADOR,
            'name'          => 'Colaborador Empresa B',
        ]);

        $response = $this->actingAs($this->master)
                         ->get(route('colaboradores.index'));

        $response->assertDontSee('Colaborador Empresa B');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 4. Exclusão
    // ──────────────────────────────────────────────────────────────────────────

    public function test_master_pode_excluir_colaborador(): void
    {
        $this->actingAs($this->master)
             ->delete(route('colaboradores.destroy', $this->colaborador->id))
             ->assertRedirect(route('colaboradores.index'))
             ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $this->colaborador->id]);
    }

    public function test_colaborador_nao_pode_excluir_outro_colaborador(): void
    {
        $outro = User::factory()->create([
            'empresa_id'    => $this->empresa->id,
            'perfil_acesso' => UserRole::COLABORADOR,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $this->actingAs($this->colaborador)
             ->delete(route('colaboradores.destroy', $outro->id))
             ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $outro->id]);
    }
}
