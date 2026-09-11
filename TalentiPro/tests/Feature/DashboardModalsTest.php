<?php

namespace Tests\Feature;

use App\Enums\StatusAvaliacao;
use App\Enums\StatusPdi;
use App\Models\Avaliacao;
use App\Models\AvaliacaoParticipante;
use App\Models\Empresa;
use App\Models\Pdi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardModalsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Garantir que a empresa exista e o usuário tenha o perfil master
        $this->empresa = Empresa::factory()->create();
        $this->masterUser = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => 'master',
        ]);
    }

    public function test_api_avaliacoes_ativas_returns_successful_response()
    {
        $this->actingAs($this->masterUser);

        // Criar uma avaliação ativa
        $avaliacao = Avaliacao::create([
            'empresa_id' => $this->empresa->id,
            'titulo' => 'Avaliação Teste',
            'data_inicio' => now(),
            'data_fim' => now()->addDays(5),
            'status' => StatusAvaliacao::ANDAMENTO,
            'created_by' => $this->masterUser->id,
        ]);

        $avaliado = User::factory()->create(['empresa_id' => $this->empresa->id]);
        $avaliador = User::factory()->create(['empresa_id' => $this->empresa->id]);

        AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id' => $avaliado->id,
            'avaliador_id' => $avaliador->id,
            'status' => 'pendente',
            'tipo' => \App\Enums\TipoAvaliacao::AUTOAVALIACAO,
        ]);

        $response = $this->get(route('dashboard.api.avaliacoes-ativas'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.partials.modal_avaliacoes');
        $response->assertSee('Avaliação Teste');
        $response->assertSee($avaliador->name);
    }

    public function test_api_pdis_pendentes_returns_successful_response()
    {
        $this->actingAs($this->masterUser);

        $colaborador = User::factory()->create(['empresa_id' => $this->empresa->id]);
        
        $pdi = Pdi::create([
            'empresa_id' => $this->empresa->id,
            'colaborador_id' => $colaborador->id,
            'gestor_id' => $this->masterUser->id,
            'status' => StatusPdi::EM_ANDAMENTO,
            'titulo' => 'Melhoria Contínua',
            'descricao' => 'Desc',
            'progresso' => 50,
            'data_inicio' => now(),
            'data_fim' => now()->addDays(10),
        ]);

        $response = $this->get(route('dashboard.api.pdis-pendentes'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.partials.modal_pdis');
        $response->assertSee('Melhoria Contínua');
        $response->assertSee($colaborador->name);
    }

    public function test_api_modals_block_unauthorized_users()
    {
        $colaborador = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => 'colaborador',
        ]);

        $this->actingAs($colaborador);

        // Colaboradores não devem acessar essas APIs de master/gestor
        $response1 = $this->get(route('dashboard.api.avaliacoes-ativas'));
        $response1->assertStatus(403); // assuming the role middleware returns 403

        $response2 = $this->get(route('dashboard.api.pdis-pendentes'));
        $response2->assertStatus(403);
    }
}
