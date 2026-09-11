<?php

namespace Tests\Feature;

use App\Enums\StatusEmpresa;
use App\Enums\StatusUsuario;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresaAtiva;

    private Empresa $empresaInativa;

    private Empresa $empresaPendente;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar as empresas com diferentes status
        $this->empresaAtiva = Empresa::create([
            'nome' => 'Empresa Ativa',
            'cnpj' => '11111111000111',
            'status' => StatusEmpresa::ATIVA,
        ]);

        $this->empresaInativa = Empresa::create([
            'nome' => 'Empresa Inativa',
            'cnpj' => '22222222000122',
            'status' => StatusEmpresa::SUSPENSA,
        ]);

        $this->empresaPendente = Empresa::create([
            'nome' => 'Empresa Pendente',
            'cnpj' => '33333333000133',
            'status' => StatusEmpresa::CANCELADA,
        ]);
    }

    /**
     * Teste: Usuário de uma empresa ATIVA consegue acessar o dashboard normalmente.
     */
    public function test_user_of_active_company_can_access_dashboard(): void
    {
        $user = User::create([
            'name' => 'User Ativo',
            'email' => 'ativo@empresa.com',
            'password' => bcrypt('password123'),
            'perfil_acesso' => UserRole::MASTER,
            'status' => StatusUsuario::ATIVO,
            'empresa_id' => $this->empresaAtiva->id,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    /**
     * Teste: Usuário de uma empresa INATIVA é barrado e redirecionado para o login com erro.
     */
    public function test_user_of_inactive_company_is_blocked(): void
    {
        $user = User::create([
            'name' => 'User Inativo',
            'email' => 'inativo@empresa.com',
            'password' => bcrypt('password123'),
            'perfil_acesso' => UserRole::MASTER,
            'status' => StatusUsuario::ATIVO,
            'empresa_id' => $this->empresaInativa->id,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        // Deve redirecionar para a tela de login
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
    }

    /**
     * Teste: Usuário de uma empresa PENDENTE é barrado e redirecionado para o login com erro.
     */
    public function test_user_of_pending_company_is_blocked(): void
    {
        $user = User::create([
            'name' => 'User Pendente',
            'email' => 'pendente@empresa.com',
            'password' => bcrypt('password123'),
            'perfil_acesso' => UserRole::MASTER,
            'status' => StatusUsuario::ATIVO,
            'empresa_id' => $this->empresaPendente->id,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
    }

    /**
     * Teste: Usuário com perfil COLABORADOR é impedido de acessar rotas restritas de RH.
     */
    public function test_colaborador_is_blocked_from_rh_management_routes(): void
    {
        $colab = User::create([
            'name' => 'User Colab',
            'email' => 'colab@empresa.com',
            'password' => bcrypt('password123'),
            'perfil_acesso' => UserRole::COLABORADOR, // perfil comum, sem permissão de RH
            'status' => StatusUsuario::ATIVO,
            'empresa_id' => $this->empresaAtiva->id,
        ]);

        // Tenta acessar as rotas protegidas pelo middleware de role:master,gestor
        $response1 = $this->actingAs($colab)->get('/departamentos');
        $response2 = $this->actingAs($colab)->get('/cargos');
        $response3 = $this->actingAs($colab)->get('/colaboradores');

        $response1->assertStatus(403);
        $response2->assertStatus(403);
        $response3->assertStatus(403);
    }

    /**
     * Teste: Usuários com perfis MASTER ou GESTOR conseguem acessar as rotas de RH normalmente.
     */
    public function test_master_and_gestor_can_access_rh_management_routes(): void
    {
        $master = User::create([
            'name' => 'User Master',
            'email' => 'master@empresa.com',
            'password' => bcrypt('password123'),
            'perfil_acesso' => UserRole::MASTER,
            'status' => StatusUsuario::ATIVO,
            'empresa_id' => $this->empresaAtiva->id,
        ]);

        $gestor = User::create([
            'name' => 'User Gestor',
            'email' => 'gestor@empresa.com',
            'password' => bcrypt('password123'),
            'perfil_acesso' => UserRole::GESTOR,
            'status' => StatusUsuario::ATIVO,
            'empresa_id' => $this->empresaAtiva->id,
        ]);

        // Acessa como Master
        $this->actingAs($master);
        $this->get('/departamentos')->assertStatus(200);
        $this->get('/cargos')->assertStatus(200);
        $this->get('/colaboradores')->assertStatus(200);

        // Acessa como Gestor
        $this->actingAs($gestor);
        $this->get('/departamentos')->assertStatus(200);
        $this->get('/cargos')->assertStatus(200);
        $this->get('/colaboradores')->assertStatus(200);
    }
}
