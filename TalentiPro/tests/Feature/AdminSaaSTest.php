<?php

namespace Tests\Feature;

use App\Enums\StatusEmpresa;
use App\Enums\StatusUsuario;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Suite: AdminSaaSTest
 * Cobre: Gerenciamento de Empresas (Tenants) pelo Admin Global.
 */
class AdminSaaSTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $master;
    private Empresa $empresa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'empresa_id'    => null,
            'perfil_acesso' => UserRole::ADMIN,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $this->empresa = Empresa::factory()->create(['status' => StatusEmpresa::ATIVA]);

        $this->master = User::factory()->create([
            'empresa_id'    => $this->empresa->id,
            'perfil_acesso' => UserRole::MASTER,
            'status'        => StatusUsuario::ATIVO,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 1. Acesso ao Painel Admin
    // ──────────────────────────────────────────────────────────────────────────

    public function test_admin_acessa_painel_com_sucesso(): void
    {
        $this->actingAs($this->admin)
             ->get(route('admin.dashboard'))
             ->assertStatus(200);
    }

    public function test_master_nao_acessa_painel_admin(): void
    {
        $this->actingAs($this->master)
             ->get(route('admin.dashboard'))
             ->assertForbidden();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 2. CRUD de Empresas
    // ──────────────────────────────────────────────────────────────────────────

    public function test_admin_pode_criar_empresa(): void
    {
        $this->actingAs($this->admin)
             ->post(route('admin.empresas.store'), [
                 'nome'                     => 'Nova Empresa Teste',
                 'cnpj'                     => '99888777000100',
                 'email'                    => 'empresa@teste.com',
                 'max_usuarios'             => 10,
                 'status'                   => StatusEmpresa::ATIVA->value,
                 'master_name'              => 'Master da Empresa',
                 'master_email'             => 'master@novaempresa.com',
                 'master_password'          => 'Senha@123',
                 'master_password_confirmation' => 'Senha@123',
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('empresas', ['nome' => 'Nova Empresa Teste']);
    }

    public function test_master_nao_pode_criar_empresa(): void
    {
        $this->actingAs($this->master)
             ->post(route('admin.empresas.store'), [
                 'nome'            => 'Empresa Invasão',
                 'cnpj'            => '11222333000100',
                 'email'           => 'invasao@empresa.com',
                 'max_usuarios'    => 10,
                 'status'          => StatusEmpresa::ATIVA->value,
                 'master_name'     => 'Invasor',
                 'master_email'    => 'invasor@empresa.com',
                 'master_password' => 'Senha@123',
             ])
             ->assertForbidden();
    }

    public function test_admin_pode_listar_todas_empresas(): void
    {
        Empresa::factory()->create(['nome' => 'Empresa Alpha']);
        Empresa::factory()->create(['nome' => 'Empresa Beta']);

        $response = $this->actingAs($this->admin)
                         ->get(route('admin.empresas.index'));

        $response->assertStatus(200);
        $response->assertSee('Empresa Alpha');
        $response->assertSee('Empresa Beta');
    }

    public function test_admin_pode_editar_empresa(): void
    {
        // Testa que a edição de empresa pelo Admin funciona corretamente
        $novoNome = 'Nome Atualizado ' . now()->timestamp;
        $this->actingAs($this->admin)
             ->put(route('admin.empresas.update', $this->empresa->id), [
                 'nome'         => $novoNome,
                 'cnpj'         => $this->empresa->cnpj,
                 'email'        => 'novo@email.com',
                 'max_usuarios' => 20,
                 'status'       => StatusEmpresa::ATIVA->value,
             ])
             ->assertRedirect();

        $updatedEmpresa = \App\Models\Empresa::find($this->empresa->id);
        $this->assertEquals($novoNome, $updatedEmpresa->nome);
    }

    public function test_admin_pode_configurar_limites_de_ia_e_whatsapp(): void
    {
        $this->actingAs($this->admin)
             ->put(route('admin.empresas.update', $this->empresa->id), [
                 'nome'         => $this->empresa->nome,
                 'cnpj'         => $this->empresa->cnpj,
                 'email'        => $this->empresa->email,
                 'max_usuarios' => $this->empresa->max_usuarios,
                 'status'       => $this->empresa->status->value,
                 'ia_ativa'     => 1,
                 'ia_limite_transcricoes_mes' => 50,
                 'whatsapp_ativo' => 1,
             ])
             ->assertRedirect();

        $this->empresa->refresh();
        $this->assertTrue($this->empresa->ia_ativa);
        $this->assertEquals(50, $this->empresa->ia_limite_transcricoes_mes);
        $this->assertTrue($this->empresa->whatsapp_ativo);
    }

    public function test_whatsapp_instance_is_deleted_when_disabled(): void
    {
        // Ativa o whatsapp primeiro
        $this->empresa->update(['whatsapp_ativo' => true]);

        // Finge requisições HTTP para a Evolution API
        \Illuminate\Support\Facades\Http::fake([
            '*/instance/delete/*' => \Illuminate\Support\Facades\Http::response(['status' => 'SUCCESS'], 200),
        ]);

        $this->actingAs($this->admin)
             ->put(route('admin.empresas.update', $this->empresa->id), [
                 'nome'         => $this->empresa->nome,
                 'cnpj'         => $this->empresa->cnpj,
                 'email'        => $this->empresa->email,
                 'max_usuarios' => $this->empresa->max_usuarios,
                 'status'       => $this->empresa->status->value,
                 'whatsapp_ativo' => 0, // Desativa
             ])
             ->assertRedirect();

        $this->empresa->refresh();
        $this->assertFalse($this->empresa->whatsapp_ativo);

        // Verifica se a API foi chamada
        \Illuminate\Support\Facades\Http::assertSent(function (\Illuminate\Http\Client\Request $request) {
            return $request->method() === 'DELETE' &&
                   str_contains($request->url(), '/instance/delete/talentipro_' . $this->empresa->id);
        });
    }

    public function test_whatsapp_instance_is_deleted_when_empresa_destroyed(): void
    {
        // Finge requisições HTTP para a Evolution API
        \Illuminate\Support\Facades\Http::fake([
            '*/instance/delete/*' => \Illuminate\Support\Facades\Http::response(['status' => 'SUCCESS'], 200),
        ]);

        $this->actingAs($this->admin)
             ->delete(route('admin.empresas.destroy', $this->empresa->id))
             ->assertRedirect();

        $this->assertDatabaseMissing('empresas', ['id' => $this->empresa->id]);

        // Verifica se a API foi chamada
        \Illuminate\Support\Facades\Http::assertSent(function (\Illuminate\Http\Client\Request $request) {
            return $request->method() === 'DELETE' &&
                   str_contains($request->url(), '/instance/delete/talentipro_' . $this->empresa->id);
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 3. Suspensão de Empresa bloqueia seus usuários
    // ──────────────────────────────────────────────────────────────────────────

    public function test_usuario_de_empresa_suspensa_e_bloqueado_apos_suspensao(): void
    {
        // Suspende a empresa diretamente no banco (sem depender do Controller do Admin)
        $this->empresa->update(['status' => StatusEmpresa::SUSPENSA]);
        $this->empresa->refresh();

        $this->assertEquals(StatusEmpresa::SUSPENSA, $this->empresa->status);

        // Tenta acessar o dashboard como Master da empresa agora suspensa
        // O middleware EnsureEmpresaAtiva deve barrar e redirecionar para login
        $response = $this->actingAs($this->master)->get('/dashboard');
        $response->assertRedirect(route('login'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 4. Validações de criação
    // ──────────────────────────────────────────────────────────────────────────

    public function test_cnpj_duplicado_retorna_erro(): void
    {
        $this->actingAs($this->admin)
             ->post(route('admin.empresas.store'), [
                 'nome'   => 'Duplicada',
                 'cnpj'   => $this->empresa->cnpj, // CNPJ já existe
                 'email'  => 'duplicada@teste.com',
                 'max_usuarios' => 10,
                 'status' => StatusEmpresa::ATIVA->value,
             ])
             ->assertSessionHasErrors('cnpj');
    }

    public function test_nome_empresa_e_obrigatorio(): void
    {
        $this->actingAs($this->admin)
             ->post(route('admin.empresas.store'), [
                 'nome'   => '',
                 'cnpj'   => '12345678000100',
                 'email'  => 'vazia@teste.com',
                 'max_usuarios' => 10,
                 'status' => StatusEmpresa::ATIVA->value,
             ])
             ->assertSessionHasErrors('nome');
    }
}
