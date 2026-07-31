<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vaga;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VagaCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_when_accessing_vagas(): void
    {
        $response = $this->get('/vagas');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_users_can_view_vagas_index(): void
    {
        $user = User::factory()->create();
        Vaga::factory()->create(['titulo' => 'Desenvolvedor Backend PHP']);

        $response = $this->actingAs($user)->get('/vagas');

        $response->assertStatus(200);
        $response->assertSee('Desenvolvedor Backend PHP');
    }

    public function test_authenticated_users_can_create_a_vaga(): void
    {
        $user = User::factory()->create();

        $dadosVaga = [
            'titulo' => 'Desenvolvedor Laravel Pleno',
            'descricao' => 'Responsável pelo desenvolvimento de APIs RESTful e aplicações web.',
            'salario' => 9500.00,
            'ativa' => '1',
        ];

        $response = $this->actingAs($user)->post('/vagas', $dadosVaga);

        $response->assertRedirect('/vagas');
        $response->assertSessionHas('sucess');
        $this->assertDatabaseHas('vagas', [
            'titulo' => 'Desenvolvedor Laravel Pleno',
            'salario' => 9500.00,
            'user_id' => $user->id,
        ]);
    }

    public function test_vaga_creation_requires_valid_title_and_description(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/vagas', [
            'titulo' => '', // Título vazio
            'descricao' => '', // Descrição vazia
        ]);

        $response->assertSessionHasErrors(['titulo', 'descricao']);
    }

    public function test_authenticated_users_can_view_a_single_vaga(): void
    {
        $user = User::factory()->create();
        $vaga = Vaga::factory()->create([
            'titulo' => 'Arquiteto de Software',
            'descricao' => 'Liderança técnica e arquitetura de microsserviços.',
        ]);

        $response = $this->actingAs($user)->get("/vagas/{$vaga->id}");

        $response->assertStatus(200);
        $response->assertSee('Arquiteto de Software');
        $response->assertSee('Liderança técnica e arquitetura de microsserviços.');
    }

    public function test_authenticated_users_can_update_a_vaga(): void
    {
        $user = User::factory()->create();
        $vaga = Vaga::factory()->create([
            'titulo' => 'Vaga Antiga',
            'salario' => 5000.00,
        ]);

        $response = $this->actingAs($user)->put("/vagas/{$vaga->id}", [
            'titulo' => 'Vaga Atualizada Pleno',
            'descricao' => 'Nova descrição detalhada da vaga.',
            'salario' => 8000.00,
            'ativa' => '1',
        ]);

        $response->assertRedirect('/vagas');
        $this->assertDatabaseHas('vagas', [
            'id' => $vaga->id,
            'titulo' => 'Vaga Atualizada Pleno',
            'salario' => 8000.00,
        ]);
    }

    public function test_authenticated_users_can_delete_a_vaga(): void
    {
        $user = User::factory()->create();
        $vaga = Vaga::factory()->create();

        $response = $this->actingAs($user)->delete("/vagas/{$vaga->id}");

        $response->assertRedirect('/vagas');
        $this->assertDatabaseMissing('vagas', [
            'id' => $vaga->id,
        ]);
    }
}
