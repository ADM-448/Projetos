<?php

namespace Tests\Feature;

use App\Enums\StatusEmpresa;
use App\Enums\StatusUsuario;
use App\Enums\UserRole;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase; // Reseta o banco sqlite em memória a cada teste!

    private Empresa $empresaA;

    private Empresa $empresaB;

    private User $userA;

    private User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Criar as empresas de teste
        $this->empresaA = Empresa::create([
            'nome' => 'Empresa Alpha',
            'cnpj' => '11111111000111',
            'status' => StatusEmpresa::ATIVA,
        ]);

        $this->empresaB = Empresa::create([
            'nome' => 'Empresa Beta',
            'cnpj' => '22222222000122',
            'status' => StatusEmpresa::ATIVA,
        ]);

        // 2. Criar os usuários (Master) de cada empresa
        $this->userA = User::create([
            'name' => 'Usuario Alpha',
            'email' => 'alpha@empresa.com',
            'password' => bcrypt('password123'),
            'perfil_acesso' => UserRole::MASTER,
            'status' => StatusUsuario::ATIVO,
            'empresa_id' => $this->empresaA->id,
        ]);

        $this->userB = User::create([
            'name' => 'Usuario Beta',
            'email' => 'beta@empresa.com',
            'password' => bcrypt('password123'),
            'perfil_acesso' => UserRole::MASTER,
            'status' => StatusUsuario::ATIVO,
            'empresa_id' => $this->empresaB->id,
        ]);
    }

    /**
     * Teste: Ao criar um Departamento como Empresa A, a trait BelongsToEmpresa
     * deve injetar automaticamente o empresa_id correspondente.
     */
    public function test_departamento_gets_empresa_id_automatically(): void
    {
        $this->actingAs($this->userA); // Simula login do Usuário A

        // Cria o departamento sem passar o empresa_id explicitamente
        $dept = Departamento::create([
            'nome' => 'TI Alpha',
            'descricao' => 'Setor de TI',
        ]);

        $this->assertDatabaseHas('departamentos', [
            'id' => $dept->id,
            'nome' => 'TI Alpha',
            'empresa_id' => $this->empresaA->id, // Injetado automaticamente!
        ]);
    }

    /**
     * Teste: O Global Scope da trait BelongsToEmpresa deve garantir que
     * a Empresa A liste apenas os seus próprios Departamentos.
     */
    public function test_user_can_only_see_their_own_tenant_departments(): void
    {
        // Cria departamento na Empresa A
        $this->actingAs($this->userA);
        $deptA = Departamento::create(['nome' => 'Setor A']);

        // Cria departamento na Empresa B
        $this->actingAs($this->userB);
        $deptB = Departamento::create(['nome' => 'Setor B']);

        // Loga de volta como Usuário A
        $this->actingAs($this->userA);

        // Consulta todos os departamentos
        $departamentos = Departamento::all();

        // O total de departamentos retornados deve ser 1 (apenas o da Empresa A)
        $this->assertCount(1, $departamentos);
        $this->assertTrue($departamentos->contains($deptA));
        $this->assertFalse($departamentos->contains($deptB));
    }

    /**
     * Teste: Um usuário logado na Empresa A deve receber 404 (Not Found) ao tentar
     * alterar um Departamento pertencente à Empresa B.
     */
    public function test_user_cannot_update_other_tenant_department(): void
    {
        // Cria departamento na Empresa B
        $this->actingAs($this->userB);
        $deptB = Departamento::create(['nome' => 'Setor B original']);

        // Loga como Usuário A
        $this->actingAs($this->userA);

        // Tenta enviar requisição PUT para atualizar o departamento da Empresa B
        $response = $this->put("/departamentos/{$deptB->id}", [
            'nome' => 'Nome alterado por invasor',
            'descricao' => 'Tentativa de alteração cruzada',
        ]);

        // Como o departamento de B não existe sob o escopo de A, o FindOrFail deve retornar 404
        $response->assertStatus(404);

        // Confirma no banco que o nome de B não foi alterado
        $this->assertDatabaseHas('departamentos', [
            'id' => $deptB->id,
            'nome' => 'Setor B original',
        ]);
    }

    /**
     * Teste: Um usuário logado na Empresa A deve receber 404 (Not Found) ao tentar
     * excluir um Departamento pertencente à Empresa B.
     */
    public function test_user_cannot_delete_other_tenant_department(): void
    {
        // Cria departamento na Empresa B
        $this->actingAs($this->userB);
        $deptB = Departamento::create(['nome' => 'Setor B']);

        // Loga como Usuário A
        $this->actingAs($this->userA);

        // Tenta enviar requisição DELETE para excluir o departamento de B
        $response = $this->delete("/departamentos/{$deptB->id}");

        $response->assertStatus(404);

        // Confirma no banco que o departamento de B ainda existe
        $this->assertDatabaseHas('departamentos', [
            'id' => $deptB->id,
        ]);
    }
}
