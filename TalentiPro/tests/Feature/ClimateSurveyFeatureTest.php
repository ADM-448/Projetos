<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Departamento;
use App\Models\ClimateSurvey;
use App\Models\ClimateQuestion;
use App\Enums\UserRole;
use Carbon\Carbon;

class ClimateSurveyFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_colaborador_cannot_create_climate_survey()
    {
        $user = User::factory()->create(['perfil_acesso' => UserRole::COLABORADOR]);

        $response = $this->actingAs($user)->get(route('gestao.clima.create'));
        $response->assertStatus(403);
    }

    public function test_master_can_create_survey_and_distribute_to_everyone()
    {
        $empresa = Empresa::factory()->create();
        $master = User::factory()->create(['perfil_acesso' => UserRole::MASTER, 'empresa_id' => $empresa->id]);
        
        $dep = Departamento::factory()->create(['empresa_id' => $empresa->id]);
        $colab1 = User::factory()->create(['perfil_acesso' => UserRole::COLABORADOR, 'empresa_id' => $empresa->id, 'departamento_id' => $dep->id]);
        $colab2 = User::factory()->create(['perfil_acesso' => UserRole::COLABORADOR, 'empresa_id' => $empresa->id, 'departamento_id' => $dep->id]);

        $data = [
            'titulo' => 'Pesquisa Teste',
            'descricao' => 'Descrição',
            'data_inicio' => Carbon::now()->format('Y-m-d'),
            'departamento_id' => null, // Toda a empresa
            'perguntas' => [
                [
                    'texto_pergunta' => 'Pergunta 1',
                    'tipo_resposta' => 'escala_1_10',
                    'dimensao' => 'Geral'
                ]
            ],
            'salvar_rascunho' => 0,
            'publicar_agora' => 1
        ];

        $response = $this->actingAs($master)->post(route('gestao.clima.store'), $data);
        $response->assertRedirect(route('gestao.clima.index'));

        $this->assertDatabaseHas('climate_surveys', [
            'titulo' => 'Pesquisa Teste',
            'status' => 'ativa'
        ]);

        $survey = ClimateSurvey::where('titulo', 'Pesquisa Teste')->first();
        $this->assertDatabaseCount('climate_survey_participations', 3); // Master + 2 colabs
    }

    public function test_survey_can_be_restricted_by_department()
    {
        $empresa = Empresa::factory()->create();
        $master = User::factory()->create(['perfil_acesso' => UserRole::MASTER, 'empresa_id' => $empresa->id]);
        
        $depTI = Departamento::factory()->create(['empresa_id' => $empresa->id]);
        $depRH = Departamento::factory()->create(['empresa_id' => $empresa->id]);
        
        $colabTI = User::factory()->create(['perfil_acesso' => UserRole::COLABORADOR, 'empresa_id' => $empresa->id, 'departamento_id' => $depTI->id]);
        $colabRH = User::factory()->create(['perfil_acesso' => UserRole::COLABORADOR, 'empresa_id' => $empresa->id, 'departamento_id' => $depRH->id]);

        $data = [
            'titulo' => 'Pesquisa TI',
            'data_inicio' => Carbon::now()->format('Y-m-d'),
            'departamento_id' => $depTI->id,
            'perguntas' => [
                [
                    'texto_pergunta' => 'Pergunta TI',
                    'tipo_resposta' => 'escala_1_10'
                ]
            ],
            'publicar_agora' => 1
        ];

        $this->actingAs($master)->post(route('gestao.clima.store'), $data);

        $survey = ClimateSurvey::first();

        // Check participations - should only be 1 (colabTI)
        $this->assertDatabaseHas('climate_survey_participations', [
            'user_id' => $colabTI->id,
            'climate_survey_id' => $survey->id
        ]);

        $this->assertDatabaseMissing('climate_survey_participations', [
            'user_id' => $colabRH->id,
            'climate_survey_id' => $survey->id
        ]);
        
        $this->assertDatabaseMissing('climate_survey_participations', [
            'user_id' => $master->id,
            'climate_survey_id' => $survey->id
        ]);
    }

    public function test_user_can_submit_responses_anonymously()
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['perfil_acesso' => UserRole::COLABORADOR, 'empresa_id' => $empresa->id]);
        
        $survey = ClimateSurvey::factory()->create(['empresa_id' => $empresa->id, 'status' => 'ativa']);
        $question = ClimateQuestion::factory()->create(['climate_survey_id' => $survey->id, 'tipo_resposta' => 'escala_1_10']);
        
        \App\Models\ClimateSurveyParticipation::create([
            'climate_survey_id' => $survey->id,
            'user_id' => $user->id,
            'status' => 'pendente'
        ]);

        $data = [
            'respostas' => [
                $question->id => 8
            ]
        ];

        $response = $this->actingAs($user)->post(route('clima.salvar-resposta', $survey->id), $data);
        $response->assertRedirect(route('clima.minhas'));

        // Certificar que a participacao foi pra concluida
        $this->assertDatabaseHas('climate_survey_participations', [
            'climate_survey_id' => $survey->id,
            'user_id' => $user->id,
            'status' => 'respondido'
        ]);

        $this->assertDatabaseHas('climate_responses', [
            'climate_survey_id' => $survey->id,
            'climate_question_id' => $question->id,
            'nota' => 8,
            'departamento_id' => $user->departamento_id
        ]);
    }

    public function test_survey_can_be_deleted()
    {
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(['perfil_acesso' => UserRole::ADMIN, 'empresa_id' => $empresa->id]);
        $survey = ClimateSurvey::factory()->create(['empresa_id' => $empresa->id, 'status' => 'ativa']);
        $question = ClimateQuestion::factory()->create(['climate_survey_id' => $survey->id]);

        $response = $this->actingAs($admin)->delete(route('gestao.clima.destroy', $survey->id));
        
        $response->assertRedirect(route('gestao.clima.index'));
        $this->assertDatabaseMissing('climate_surveys', ['id' => $survey->id]);
        $this->assertDatabaseMissing('climate_questions', ['id' => $question->id]);
    }
}
