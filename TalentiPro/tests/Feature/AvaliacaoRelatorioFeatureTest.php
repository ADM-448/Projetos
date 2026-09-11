<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Avaliacao;
use App\Models\AvaliacaoParticipante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AvaliacaoRelatorioFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_colaborador_pode_acessar_meus_relatorios()
    {
        $empresa = \App\Models\Empresa::factory()->create();
        $colaborador = User::factory()->create(['perfil_acesso' => 'colaborador', 'empresa_id' => $empresa->id]);
        
        $response = $this->actingAs($colaborador)->get(route('avaliacoes.relatorios.meus'));

        $response->assertStatus(200);
        $response->assertViewIs('avaliacoes.relatorios.meus_relatorios');
    }

    public function test_colaborador_nao_pode_acessar_relatorios_da_equipe()
    {
        $empresa = \App\Models\Empresa::factory()->create();
        $colaborador = User::factory()->create(['perfil_acesso' => 'colaborador', 'empresa_id' => $empresa->id]);
        
        $response = $this->actingAs($colaborador)->get(route('avaliacoes.relatorios'));

        // No Laravel, Gate checks usually throw 403. Let's assume there is a middleware.
        // If not protected by middleware, we might get 200 but the array will be empty.
        // However, standard is to block it via middleware/Gate. 
        // Agora que `avaliacoes.relatorios` está dentro do middleware `role:master,gestor`,
        // um colaborador receberá 403 (Forbidden).
        $response->assertStatus(403);
    }

    public function test_master_pode_acessar_relatorios_da_equipe()
    {
        $empresa = \App\Models\Empresa::factory()->create();
        $master = User::factory()->create(['perfil_acesso' => 'master', 'empresa_id' => $empresa->id]);
        
        $response = $this->actingAs($master)->get(route('avaliacoes.relatorios'));

        $response->assertStatus(200);
        $response->assertViewIs('avaliacoes.relatorios.index');
    }

    public function test_api_retorna_dados_json_do_relatorio()
    {
        $empresa = \App\Models\Empresa::factory()->create();
        $gestor = User::factory()->create(['perfil_acesso' => 'gestor', 'empresa_id' => $empresa->id]);
        $colaborador = User::factory()->create(['perfil_acesso' => 'colaborador', 'gestor_id' => $gestor->id, 'empresa_id' => $empresa->id]);

        $avaliacao = \App\Models\Avaliacao::create([
            'titulo' => 'Avaliação de Desempenho 2026',
            'empresa_id' => $empresa->id,
            'created_by' => $gestor->id,
            'status' => 'concluida',
            'data_inicio' => \Carbon\Carbon::now()->subDays(10),
            'data_fim' => \Carbon\Carbon::now()->addDays(10),
        ]);

        AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliador_id' => $gestor->id,
            'avaliado_id' => $colaborador->id,
            'tipo' => 'gestor',
            'status' => 'respondido'
        ]);

        $response = $this->actingAs($gestor)->getJson('/api/relatorios/dados?avaliacao_id=' . $avaliacao->id . '&colaborador_id=' . $colaborador->id);

        $response->assertStatus(200);
        
        // Verifica a estrutura base do JSON retornado pelo motor de relatórios
        $response->assertJsonStructure([
            'colaborador' => [
                'id', 'name'
            ],
            'charts' => [
                'individual',
                'cargo',
                'setor',
                'empresa',
            ],
            'scores' => [
                'colab',
                'aval',
                'med'
            ]
        ]);
    }

    public function test_api_retorna_medias_corretas_com_enums()
    {
        $empresa = \App\Models\Empresa::factory()->create();
        $master = User::factory()->create(['perfil_acesso' => 'master', 'empresa_id' => $empresa->id]);
        
        // Autenticando cedo para as traits de "BelongsToEmpresa" funcionarem
        $this->actingAs($master);

        $colaborador = User::factory()->create(['perfil_acesso' => 'colaborador', 'empresa_id' => $empresa->id]);
        
        $avaliacao = \App\Models\Avaliacao::create([
            'titulo' => 'Avaliação com Notas',
            'empresa_id' => $empresa->id,
            'created_by' => $master->id,
            'status' => 'concluida',
            'data_inicio' => \Carbon\Carbon::now()->subDays(10),
            'data_fim' => \Carbon\Carbon::now()->addDays(10),
        ]);

        $competencia = \App\Models\Competencia::create(['nome' => 'Liderança', 'empresa_id' => $empresa->id, 'descricao' => 'Teste']);
        $skill = \App\Models\Skill::create(['competencia_id' => $competencia->id, 'nome' => 'Sabe liderar?', 'peso' => 1]);

        // Participação 1: Autoavaliação
        $partAuto = AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliador_id' => $colaborador->id,
            'avaliado_id' => $colaborador->id,
            'tipo' => 'autoavaliacao',
            'status' => 'respondido'
        ]);
        
        \App\Models\AvaliacaoResposta::create([
            'avaliacao_participante_id' => $partAuto->id,
            'skill_id' => $skill->id,
            'nota' => 8
        ]);

        // Participação 2: Gestor
        $partGestor = AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliador_id' => $master->id,
            'avaliado_id' => $colaborador->id,
            'tipo' => 'gestor',
            'status' => 'respondido'
        ]);

        \App\Models\AvaliacaoResposta::create([
            'avaliacao_participante_id' => $partGestor->id,
            'skill_id' => $skill->id,
            'nota' => 10
        ]);

        $response = $this->actingAs($master)->getJson('/api/relatorios/dados?avaliacao_id=' . $avaliacao->id . '&colaborador_id=' . $colaborador->id);

        $response->assertStatus(200);
        
        $json = $response->json();
        
        // As médias devem refletir 8.0 para autoavaliação e 10.0 para gestor
        // Se a busca falhar no banco (pelo Enum), essas notas voltarão como '0.00'.
        $this->assertEquals('8.00', $json['scores']['colab']['media']);
        $this->assertEquals('10.00', $json['scores']['aval']['media']);
    }

    public function test_rota_de_exportar_pdf_retorna_sucesso()
    {
        $empresa = \App\Models\Empresa::factory()->create();
        $master = User::factory()->create(['perfil_acesso' => 'master', 'empresa_id' => $empresa->id]);
        $colaborador = User::factory()->create(['perfil_acesso' => 'colaborador', 'empresa_id' => $empresa->id]);

        $avaliacao = \App\Models\Avaliacao::create([
            'titulo' => 'Avaliação Trimestral',
            'empresa_id' => $empresa->id,
            'created_by' => $master->id,
            'status' => 'concluida',
            'data_inicio' => \Carbon\Carbon::now()->subDays(10),
            'data_fim' => \Carbon\Carbon::now()->addDays(10),
        ]);

        AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliador_id' => $master->id,
            'avaliado_id' => $colaborador->id,
            'tipo' => 'gestor',
            'status' => 'respondido'
        ]);

        $response = $this->actingAs($master)->get('/avaliacoes/relatorios/pdf?avaliacao_id=' . $avaliacao->id . '&colaborador_id=' . $colaborador->id);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
