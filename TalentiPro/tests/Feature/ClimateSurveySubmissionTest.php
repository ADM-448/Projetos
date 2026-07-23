<?php

namespace Tests\Feature;

use App\Exceptions\DuplicateParticipationException;
use App\Models\ClimateQuestion;
use App\Models\ClimateResponse;
use App\Models\ClimateSurvey;
use App\Models\ClimateSurveyParticipation;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class ClimateSurveySubmissionTest extends TestCase
{
    use RefreshDatabase;

    private User $master;
    private User $colaborador;
    private Empresa $empresa;
    private ClimateSurvey $survey;
    private ClimateQuestion $questionNota;
    private ClimateQuestion $questionTexto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::factory()->create();
        
        $this->master = User::factory()->create([
            'empresa_id'    => $this->empresa->id,
            'perfil_acesso' => 'master',
        ]);

        $departamento = Departamento::factory()->create(['empresa_id' => $this->empresa->id]);
        
        $this->colaborador = User::factory()->create([
            'empresa_id'      => $this->empresa->id,
            'perfil_acesso'   => 'colaborador',
            'departamento_id' => $departamento->id,
        ]);

        $this->survey = ClimateSurvey::factory()->ativa()->create([
            'empresa_id' => $this->empresa->id,
            'created_by' => $this->master->id,
        ]);

        $this->questionNota = ClimateQuestion::factory()->tipoNota()->create([
            'survey_id' => $this->survey->id,
        ]);

        $this->questionTexto = ClimateQuestion::factory()->tipoTexto()->create([
            'survey_id' => $this->survey->id,
        ]);
    }

    public function test_response_is_stored_without_plain_user_id()
    {
        $this->actingAs($this->colaborador);

        $payload = [
            'respostas' => [
                ['question_id' => $this->questionNota->id, 'nota' => 8],
                ['question_id' => $this->questionTexto->id, 'texto_resposta' => 'Muito bom!'],
            ],
        ];

        $response = $this->post(route('clima.submit', $this->survey->id), $payload);
        $response->assertRedirect(route('clima.index'));

        // Verifica que a tabela não possui a coluna user_id
        $this->assertDatabaseMissing('climate_responses', [
            'survey_id' => $this->survey->id,
            'nota'      => 8,
            'user_id'   => $this->colaborador->id // Este campo não existe e vai falhar no assertDatabaseHas nativamente se não cuidarmos, mas DatabaseMissing ajuda a provar a intenção. Na verdade, é melhor testar a presença da resposta.
        ]);

        $this->assertDatabaseHas('climate_responses', [
            'survey_id' => $this->survey->id,
            'nota'      => 8,
        ]);
        
        $dbResponse = ClimateResponse::withoutGlobalScope('empresa')->where('nota', 8)->first();
        
        // Garante que o campo existe e está preenchido, mas não é legível
        $this->assertNotNull($dbResponse->encrypted_user_id);
        $this->assertNotEquals($this->colaborador->id, $dbResponse->encrypted_user_id);
    }

    public function test_encrypted_user_id_is_stored_and_decryptable()
    {
        $this->actingAs($this->colaborador);

        $this->post(route('clima.submit', $this->survey->id), [
            'respostas' => [
                ['question_id' => $this->questionNota->id, 'nota' => 10],
            ],
        ]);

        $dbResponse = ClimateResponse::withoutGlobalScope('empresa')->where('nota', 10)->first();
        
        $decrypted = Crypt::decryptString($dbResponse->encrypted_user_id);
        
        $this->assertEquals($this->colaborador->id, $decrypted);
    }

    public function test_duplicate_submission_is_rejected()
    {
        $this->actingAs($this->colaborador);

        // Primeira submissão
        $this->post(route('clima.submit', $this->survey->id), [
            'respostas' => [
                ['question_id' => $this->questionNota->id, 'nota' => 5],
            ],
        ]);

        // Segunda submissão tenta interceptar a exception a nível de controller
        $response = $this->post(route('clima.submit', $this->survey->id), [
            'respostas' => [
                ['question_id' => $this->questionNota->id, 'nota' => 9],
            ],
        ]);

        // O controller captura DuplicateParticipationException e redireciona com erro
        $response->assertRedirect(route('clima.index'));
        $response->assertSessionHas('error', 'Você já participou desta pesquisa de clima organizacional.');

        // Garante que só salvou 1 vez
        $this->assertDatabaseCount('climate_responses', 1);
        $this->assertDatabaseHas('climate_responses', ['nota' => 5]);
    }

    public function test_inactive_survey_rejects_submission()
    {
        $rascunho = ClimateSurvey::factory()->rascunho()->create(['empresa_id' => $this->empresa->id]);
        
        $this->actingAs($this->colaborador);

        $response = $this->post(route('clima.submit', $rascunho->id), [
            'respostas' => [
                ['question_id' => $this->questionNota->id, 'nota' => 5], // id misturado para teste
            ],
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('climate_responses', 0);
    }

    public function test_participation_recorded_in_separate_table()
    {
        $this->actingAs($this->colaborador);

        $this->post(route('clima.submit', $this->survey->id), [
            'respostas' => [
                ['question_id' => $this->questionNota->id, 'nota' => 7],
            ],
        ]);

        $this->assertDatabaseHas('climate_survey_participations', [
            'survey_id' => $this->survey->id,
            'user_id'   => $this->colaborador->id,
        ]);
    }

    public function test_non_admin_cannot_access_auditoria()
    {
        $this->actingAs($this->master); // Master não pode ver auditoria, só Admin
        $response = $this->get(route('clima.auditoria', $this->survey->id));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_auditoria()
    {
        $admin = User::factory()->create(['perfil_acesso' => 'admin']);
        $this->actingAs($admin);
        
        $response = $this->get(route('clima.auditoria', $this->survey->id));
        $response->assertStatus(200);
    }

    public function test_colaborador_blocked_when_results_not_released()
    {
        $this->actingAs($this->colaborador);
        $response = $this->get(route('clima.dashboard', $this->survey->id));
        $response->assertStatus(403);
    }

    public function test_colaborador_can_access_when_results_released()
    {
        $this->survey->update(['resultado_liberado_colaboradores' => true]);
        $this->actingAs($this->colaborador);
        
        $response = $this->get(route('clima.dashboard', $this->survey->id));
        $response->assertStatus(200);
    }

    public function test_gestor_sees_general_when_toggle_is_geral()
    {
        $gestor = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => 'gestor',
        ]);
        
        $this->actingAs($gestor);
        $response = $this->get(route('clima.dashboard', $this->survey->id));
        
        $response->assertStatus(200);
        $this->assertNull($response->viewData('deptId')); // Depto Null = Visão Geral
    }

    public function test_gestor_sees_only_dept_when_toggle_is_apenas_departamento()
    {
        $gestor = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => 'gestor',
            'departamento_id' => 99,
        ]);
        
        $this->survey->update(['visibilidade_gestor' => 'apenas_departamento']);
        
        $this->actingAs($gestor);
        $response = $this->get(route('clima.dashboard', $this->survey->id));
        
        $response->assertStatus(200);
        $this->assertEquals(99, $response->viewData('deptId')); // Filtrado pelo depto do gestor
    }
}
