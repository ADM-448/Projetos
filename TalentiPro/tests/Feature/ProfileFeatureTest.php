<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function criarUsuario(UserRole $role): User
    {
        $empresa = Empresa::factory()->create();
        return User::factory()->create([
            'empresa_id'    => $empresa->id,
            'perfil_acesso' => $role,
        ]);
    }

    #[Test]
    public function usuario_nao_autenticado_e_redirecionado_para_login(): void
    {
        $response = $this->get(route('profile.edit'));
        $response->assertRedirect(route('login'));

        $response = $this->put(route('profile.update'), []);
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function colaborador_visualiza_proprios_dados_corretamente(): void
    {
        $user = $this->criarUsuario(UserRole::COLABORADOR);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertViewIs('profile.edit');
        $response->assertSee($user->name);
        $response->assertSee($user->email);
    }

    #[Test]
    public function colaborador_atualiza_nome_email_telefone_com_sucesso(): void
    {
        $user = $this->criarUsuario(UserRole::COLABORADOR);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name'     => 'Nome Colaborador Atualizado',
            'email'    => 'novoemail@empresa.com',
            'telefone' => '(11) 9 8888-7777',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id'       => $user->id,
            'name'     => 'Nome Colaborador Atualizado',
            'email'    => 'novoemail@empresa.com',
            'telefone' => '11988887777',
        ]);
    }

    #[Test]
    public function colaborador_nao_consegue_alterar_senha(): void
    {
        $user = $this->criarUsuario(UserRole::COLABORADOR);
        $senhaOriginal = $user->password;

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name'     => $user->name,
            'email'    => $user->email,
            'password' => 'novasenha123',
            'password_confirmation' => 'novasenha123',
        ]);

        $user->refresh();
        $this->assertTrue(Hash::check('password', $user->password) || $user->password === $senhaOriginal);
    }

    #[Test]
    public function master_consegue_alterar_senha(): void
    {
        $user = $this->criarUsuario(UserRole::MASTER);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name'     => $user->name,
            'email'    => $user->email,
            'password' => 'novasenha123',
            'password_confirmation' => 'novasenha123',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('novasenha123', $user->password));
    }
}
