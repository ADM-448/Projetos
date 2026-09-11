<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Pdi;
use App\Models\PdiAcao;
use App\Enums\StatusPdi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class PdiFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_criacao_de_pdi_individual_por_gestor()
    {
        $empresa = \App\Models\Empresa::factory()->create();
        $gestor = User::factory()->create(['perfil_acesso' => 'gestor', 'empresa_id' => $empresa->id]);
        $colaborador = User::factory()->create([
            'perfil_acesso' => 'colaborador', 
            'empresa_id' => $gestor->empresa_id,
            'departamento_id' => $gestor->departamento_id
        ]);

        $dadosPdi = [
            'tipo_criacao' => 'individual',
            'colaborador_id' => $colaborador->id,
            'titulo' => 'Melhorar Habilidades de Comunicação',
            'descricao' => 'PDI focado em comunicação',
            'data_inicio' => now()->format('Y-m-d'),
            'data_fim' => now()->addDays(30)->format('Y-m-d'),
            'acoes' => [
                [
                    'titulo' => 'Ler livro XPTO',
                    'descricao' => 'Leitura obrigatória'
                ]
            ]
        ];

        $response = $this->actingAs($gestor)->post(route('pdis.store'), $dadosPdi);
        
        $response->assertRedirect(route('pdis.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pdis', [
            'titulo' => 'Melhorar Habilidades de Comunicação',
            'colaborador_id' => $colaborador->id,
            'gestor_id' => $gestor->id,
            'status' => 'nao_iniciado',
            'progresso' => 0
        ]);

        $this->assertDatabaseHas('pdi_acoes', [
            'titulo' => 'Ler livro XPTO',
            'status' => 'pendente'
        ]);

        // Verifica se a atividade foi gerada com o título real do PDI
        $this->assertDatabaseHas('atividades', [
            'user_id' => $colaborador->id,
            'titulo' => 'Melhorar Habilidades de Comunicação',
        ]);
    }

    public function test_progresso_do_pdi_com_tarefas()
    {
        $empresa = \App\Models\Empresa::factory()->create();
        $gestor = User::factory()->create(['perfil_acesso' => 'gestor', 'empresa_id' => $empresa->id]);
        
        $pdi = Pdi::create([
            'empresa_id' => $gestor->empresa_id,
            'colaborador_id' => $gestor->id,
            'gestor_id' => $gestor->id,
            'titulo' => 'Curso de Liderança',
            'status' => StatusPdi::NAO_INICIADO,
            'progresso' => 0
        ]);

        $acao1 = PdiAcao::create(['pdi_id' => $pdi->id, 'titulo' => 'Módulo 1', 'status' => 'pendente']);
        $acao2 = PdiAcao::create(['pdi_id' => $pdi->id, 'titulo' => 'Módulo 2', 'status' => 'pendente']);

        // Chamada para alterar o status da Ação 1 via controller
        // Not using controller in test, the actual route is pdi-acoes.toggle POST /pdi-acoes/{acao}/toggle
        $response = $this->actingAs($gestor)->post(route('pdi-acoes.toggle', $acao1->id), [
            'status' => 'concluido'
        ]);

        $response->assertRedirect();

        // Recarrega o PDI para checar o progresso
        $pdi->refresh();
        
        // 1 de 2 = 50%
        $this->assertEquals(50, $pdi->progresso);
        $this->assertEquals(StatusPdi::EM_ANDAMENTO, $pdi->status);
        
        // Concluindo a segunda ação
        $this->actingAs($gestor)->post(route('pdi-acoes.toggle', $acao2->id), [
            'status' => 'concluido'
        ]);

        $pdi->refresh();

        // 2 de 2 = 100%
        $this->assertEquals(100, $pdi->progresso);
        $this->assertEquals(StatusPdi::CONCLUIDO, $pdi->status);
    }

    public function test_status_de_atraso_do_pdi()
    {
        $empresa = \App\Models\Empresa::factory()->create();
        $gestor = User::factory()->create(['perfil_acesso' => 'gestor', 'empresa_id' => $empresa->id]);
        
        $pdiAtrasado = Pdi::create([
            'empresa_id' => $gestor->empresa_id,
            'colaborador_id' => $gestor->id,
            'gestor_id' => $gestor->id,
            'titulo' => 'Entregar Relatório X',
            'data_fim' => Carbon::yesterday()->format('Y-m-d'),
            'status' => StatusPdi::EM_ANDAMENTO,
            'progresso' => 50
        ]);

        $pdiNoPrazo = Pdi::create([
            'empresa_id' => $gestor->empresa_id,
            'colaborador_id' => $gestor->id,
            'gestor_id' => $gestor->id,
            'titulo' => 'Entregar Relatório Y',
            'data_fim' => Carbon::tomorrow()->format('Y-m-d'),
            'status' => StatusPdi::EM_ANDAMENTO,
            'progresso' => 50
        ]);

        // Na View, ele verifica if ($pdi->data_fim && $pdi->data_fim->isPast())
        $this->assertTrue($pdiAtrasado->data_fim->isPast());
        $this->assertFalse($pdiNoPrazo->data_fim->isPast());
    }
}
