<?php

namespace Tests\Feature;

use App\Contracts\EmpresaServiceContract;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EmpresaTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pode_criar_empresa_com_master(): void
    {
        $admin = User::factory()->create([
            'perfil_acesso' => UserRole::ADMIN,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.empresas.store'), [
            'nome' => 'Empresa Nova',
            'cnpj' => '11.111.111/0001-11',
            'razao_social' => 'Empresa Nova LTDA',
            'email' => 'contato@empresanova.com',
            'telefone' => '11999999999',
            'max_usuarios' => 50,
            'status' => 'ativa',
            'master_name' => 'João Master',
            'master_email' => 'master@empresanova.com',
            'master_password' => 'password123',
            'master_password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('admin.empresas.index'));
        $this->assertDatabaseHas('empresas', ['cnpj' => '11.111.111/0001-11']);
        $this->assertDatabaseHas('users', [
            'email' => 'master@empresanova.com',
            'perfil_acesso' => UserRole::MASTER->value,
        ]);
    }

    public function test_master_nao_pode_acessar_painel_admin_empresas(): void
    {
        $empresa = Empresa::factory()->create();
        $master = User::factory()->create([
            'empresa_id' => $empresa->id,
            'perfil_acesso' => UserRole::MASTER,
        ]);

        $response = $this->actingAs($master)->get(route('admin.empresas.index'));

        // O middleware de rota role:admin barra antes mesmo da policy
        $response->assertForbidden();
    }

    public function test_empresa_service_falha_se_master_invalido(): void
    {
        $service = app(EmpresaServiceContract::class);

        $dadosEmpresa = [
            'nome' => 'Empresa Teste',
            'cnpj' => '22.222.222/0001-22',
            'email' => 'teste@empresa.com',
            'max_usuarios' => 10,
            'status' => 'ativa',
        ];

        // Falta o password e name para o master de propósito para dar erro no BD (Not Null)
        $dadosMaster = [
            'email' => 'master@empresa.com',
        ];

        try {
            $service->criarComMaster($dadosEmpresa, $dadosMaster);
            $this->fail('Deveria ter lançado exception por dados incompletos do master.');
        } catch (\Exception $e) {
            // Se caiu aqui, deu rollback
            $this->assertDatabaseMissing('empresas', ['cnpj' => '22.222.222/0001-22']);
            $this->assertDatabaseMissing('users', ['email' => 'master@empresa.com']);
        }
    }
}
