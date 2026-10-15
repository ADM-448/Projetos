<?php

namespace Tests\Feature;

use App\Enums\StatusEmpresa;
use App\Enums\StatusUsuario;
use App\Enums\UserRole;
use App\Jobs\GerarPdiAvaliacaoJob;
use App\Jobs\GerarPdiClimaJob;
use App\Models\Atividade;
use App\Models\Avaliacao;
use App\Models\ClimateSurvey;
use App\Models\Empresa;
use App\Models\Pdi;
use App\Models\PdiAcao;
use App\Models\User;
use App\Services\GeminiTextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Mockery\MockInterface;
use Tests\TestCase;

class GerarPdiIaJobsTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private User $master;
    private User $colaborador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::factory()->create([
            'status' => StatusEmpresa::ATIVA,
            'ia_transcricoes_utilizadas' => 0
        ]);

        $this->master = User::factory()->create([
            'empresa_id'    => $this->empresa->id,
            'perfil_acesso' => UserRole::MASTER,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $this->colaborador = User::factory()->create([
            'empresa_id'    => $this->empresa->id,
            'perfil_acesso' => UserRole::COLABORADOR,
            'status'        => StatusUsuario::ATIVO,
            'gestor_id'     => $this->master->id,
        ]);
    }

    public function test_gerar_pdi_avaliacao_job_processa_corretamente(): void
    {
        // Mock do AiManager
        $this->mock(\App\Services\AI\AiManager::class, function (MockInterface $mock) {
            $mock->shouldReceive('gerarPdiAvaliacao')
                ->once()
                ->andReturn([
                    'success' => true,
                    'dados' => [
                        'titulo' => 'Melhoria em Comunicação',
                        'descricao' => 'Focar na escuta ativa',
                        'prazo' => '2026-12-31',
                        'acoes' => [
                            ['titulo' => 'Fazer curso de oratória', 'descricao' => 'Carga horária de 20h'],
                            ['titulo' => 'Ler livro sobre feedbacks', 'descricao' => 'Até o fim do mês']
                        ]
                    ]
                ]);
        });

        $dadosCompilados = "Notas do colaborador em comunicação: 3.5/5.0";

        // Instanciar e executar o Job manualmente
        $job = new GerarPdiAvaliacaoJob(
            $dadosCompilados, 
            $this->colaborador->name, 
            $this->colaborador->id, 
            $this->master->id, 
            $this->empresa->id
        );
        
        // Laravel container will inject the mocked service
        app()->call([$job, 'handle']);

        // Assertions
        $this->assertDatabaseHas('empresas', [
            'id' => $this->empresa->id,
            'ia_transcricoes_utilizadas' => 1
        ]);

        $this->assertDatabaseHas('pdis', [
            'empresa_id' => $this->empresa->id,
            'colaborador_id' => $this->colaborador->id,
            'gestor_id' => $this->master->id,
            'titulo' => '[Rascunho IA] Melhoria em Comunicação'
        ]);

        $pdi = Pdi::where('colaborador_id', $this->colaborador->id)->first();

        $this->assertDatabaseHas('pdi_acoes', [
            'pdi_id' => $pdi->id,
            'titulo' => 'Fazer curso de oratória'
        ]);

        $this->assertDatabaseHas('atividades', [
            'empresa_id' => $this->empresa->id,
            'user_id' => $this->master->id,
            'titulo' => 'PDI Gerado com IA'
        ]);
    }

    public function test_gerar_pdi_clima_job_processa_corretamente(): void
    {
        // Mock do AiManager
        $this->mock(\App\Services\AI\AiManager::class, function (MockInterface $mock) {
            $mock->shouldReceive('gerarPdiClima')
                ->once()
                ->andReturn([
                    'success' => true,
                    'dados' => [
                        'titulo' => 'Ação Engajamento',
                        'descricao' => 'Aumentar a favorabilidade da empresa',
                        'prazo' => '2026-10-10',
                        'acoes' => [
                            ['titulo' => 'Sexta-feira casual', 'descricao' => 'Liberar dress code']
                        ]
                    ]
                ]);
        });

        $dadosCompilados = "Médias por Dimensão: Engajamento 60%";

        // Instanciar e executar o Job manualmente
        $job = new GerarPdiClimaJob($dadosCompilados, $this->master->id, $this->empresa->id);
        
        app()->call([$job, 'handle']);

        // Assertions
        $this->assertDatabaseHas('empresas', [
            'id' => $this->empresa->id,
            'ia_transcricoes_utilizadas' => 1
        ]);

        $this->assertDatabaseHas('pdis', [
            'empresa_id' => $this->empresa->id,
            'colaborador_id' => $this->master->id, // Organizacional vai pro gestor
            'gestor_id' => $this->master->id,
            'titulo' => '[PDI Organizacional - IA] Ação Engajamento'
        ]);

        $pdi = Pdi::where('titulo', '[PDI Organizacional - IA] Ação Engajamento')->first();

        $this->assertDatabaseHas('pdi_acoes', [
            'pdi_id' => $pdi->id,
            'titulo' => 'Sexta-feira casual'
        ]);

        $this->assertDatabaseHas('atividades', [
            'empresa_id' => $this->empresa->id,
            'user_id' => $this->master->id,
            'titulo' => 'PDI Organizacional Gerado'
        ]);
    }

    public function test_controller_avaliacao_gera_pdi_ia_com_sucesso(): void
    {
        $this->mock(\App\Services\AI\AiManager::class, function (MockInterface $mock) {
            $mock->shouldReceive('gerarPdiAvaliacao')
                ->once()
                ->andReturn([
                    'success' => true,
                    'dados' => [
                        'titulo' => 'PDI de Comunicação',
                        'descricao' => 'Descrição do plano',
                        'prazo' => '2026-12-31',
                        'acoes' => [
                            ['titulo' => 'Ação 1', 'descricao' => 'Descrição 1']
                        ]
                    ]
                ]);
        });

        $response = $this->actingAs($this->master)->postJson(route('avaliacoes.gerar_pdi_ia'), [
            'avaliacao_id' => 1, // Controller não valida DB
            'colaborador_id' => $this->colaborador->id,
            'colaborador_nome' => $this->colaborador->name,
            'competencias' => [
                'Comunicação' => [
                    'skills' => [
                        'Clareza' => [
                            'aval' => ['nota' => 2.5]
                        ]
                    ]
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure([
            'success',
            'dados' => [
                'titulo',
                'descricao',
                'prazo',
                'acoes' => [
                    '*' => ['titulo', 'descricao']
                ]
            ],
            'message'
        ]);
    }

    public function test_controller_clima_despacha_job_para_fila(): void
    {
        Bus::fake();

        $survey = ClimateSurvey::factory()->create([
            'empresa_id' => $this->empresa->id
        ]);

        $response = $this->actingAs($this->master)->postJson(route('gestao.clima.gerar_pdi_ia', $survey->id), [
            'dimensoesStats' => [],
            'statsDepartamentos' => [],
            'comentarios' => []
        ]);

        $response->assertStatus(200);
        $response->assertJson(['queued' => true]);

        Bus::assertDispatched(GerarPdiClimaJob::class);
    }
}
