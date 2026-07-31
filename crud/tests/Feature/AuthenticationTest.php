<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Acessar Conta');
    }

    public function test_register_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Criar Nova Conta');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'dev@exemplo.com',
            'password' => bcrypt('senha123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'dev@exemplo.com',
            'password' => 'senha123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/vagas');
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'dev@exemplo.com',
            'password' => bcrypt('senha123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'dev@exemplo.com',
            'password' => 'senha-errada',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Desenvolvedor Pleno',
            'email' => 'pleno@exemplo.com',
            'password' => 'senha123',
            'password_confirmation' => 'senha123',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'pleno@exemplo.com',
            'name' => 'Desenvolvedor Pleno',
        ]);
        $response->assertRedirect('/vagas');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
