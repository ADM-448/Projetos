<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardGestorTarefasTest extends TestCase
{
    use RefreshDatabase;

    public function test_gestor_consegue_ver_grid_de_conteudo_com_tarefas_na_dashboard()
    {
        $empresa = Empresa::factory()->create();

        $gestor = User::factory()->create([
            'empresa_id' => $empresa->id,
            'perfil_acesso' => UserRole::GESTOR
        ]);

        $response = $this->actingAs($gestor)->get(route('dashboard'));

        $response->assertStatus(200);
        // O Gestor deve ver a div class="content-grid" que antes estava escondida
        $response->assertSee('class="content-grid"', false);
        $response->assertSee('Minhas Tarefas Pendentes');
    }

    public function test_master_nao_ve_grid_de_conteudo_na_dashboard()
    {
        $empresa = Empresa::factory()->create();

        $master = User::factory()->create([
            'empresa_id' => $empresa->id,
            'perfil_acesso' => UserRole::MASTER
        ]);

        $response = $this->actingAs($master)->get(route('dashboard'));

        $response->assertStatus(200);
        // Master ainda não deve ver a content grid, pois seu menu é mais analítico
        $response->assertDontSee('class="content-grid"', false);
    }
}
