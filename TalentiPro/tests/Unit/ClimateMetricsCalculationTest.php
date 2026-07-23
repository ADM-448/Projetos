<?php

namespace Tests\Unit;

use App\Models\ClimateCategory;
use App\Models\ClimateQuestion;
use App\Models\ClimateResponse;
use App\Models\ClimateSurvey;
use App\Models\Empresa;
use App\Services\CalculateClimateMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class ClimateMetricsCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_enps_calculation_is_accurate()
    {
        $empresa = Empresa::factory()->create();
        $survey = ClimateSurvey::factory()->ativa()->create(['empresa_id' => $empresa->id]);
        $question = ClimateQuestion::factory()->tipoNota()->create(['survey_id' => $survey->id]);

        $this->createResponse($survey->id, $question->id, $empresa->id, 9); // Promotor
        $this->createResponse($survey->id, $question->id, $empresa->id, 10); // Promotor
        $this->createResponse($survey->id, $question->id, $empresa->id, 8); // Neutro
        $this->createResponse($survey->id, $question->id, $empresa->id, 5); // Detrator
        $this->createResponse($survey->id, $question->id, $empresa->id, 1); // Detrator

        $service = new CalculateClimateMetricsService();
        $metrics = $service->getMetricsBySurvey($survey->id, $empresa->id);

        // 5 respostas: 2 promotores (40%), 1 neutro (20%), 2 detratores (40%)
        // eNPS = 40 - 40 = 0
        $this->assertEquals(0.0, $metrics['enps']);

        // Teste de variação
        $this->createResponse($survey->id, $question->id, $empresa->id, 10); // +1 Promotor
        
        $metricsV2 = $service->getMetricsBySurvey($survey->id, $empresa->id);
        // Agora: 6 respostas: 3 promotores (50%), 1 neutro (16.6%), 2 detratores (33.3%)
        // eNPS = 50 - 33.3 = 16.7
        $this->assertEquals(16.7, $metricsV2['enps']);
    }

    public function test_zero_division_is_prevented_in_enps_and_response_rate()
    {
        $empresa = Empresa::factory()->create();
        $survey = ClimateSurvey::factory()->ativa()->create(['empresa_id' => $empresa->id]);
        
        $service = new CalculateClimateMetricsService();
        $metrics = $service->getMetricsBySurvey($survey->id, $empresa->id);

        $this->assertEquals(0.0, $metrics['enps']);
        $this->assertEquals(0, $metrics['response_rate']['rate']);
    }

    public function test_metrics_are_isolated_by_empresa()
    {
        $empresa1 = Empresa::factory()->create();
        $empresa2 = Empresa::factory()->create();
        
        $survey = ClimateSurvey::factory()->ativa()->create(['empresa_id' => $empresa1->id]);
        $question = ClimateQuestion::factory()->tipoNota()->create(['survey_id' => $survey->id]);

        // Respostas da Empresa 1
        $this->createResponse($survey->id, $question->id, $empresa1->id, 10);
        $this->createResponse($survey->id, $question->id, $empresa1->id, 10);

        // Respostas acidentalmente cadastradas pra Empresa 2 (falha de injection)
        $this->createResponse($survey->id, $question->id, $empresa2->id, 1);

        $service = new CalculateClimateMetricsService();
        $metrics = $service->getMetricsBySurvey($survey->id, $empresa1->id);

        // A empresa 1 só vê as próprias respostas, ignorando o detrator injetado
        // 2 respostas promotoras -> 100% eNPS
        $this->assertEquals(100.0, $metrics['enps']);
    }

    public function test_average_by_category_calculation()
    {
        $empresa = Empresa::factory()->create();
        $survey = ClimateSurvey::factory()->ativa()->create(['empresa_id' => $empresa->id]);
        
        $cat1 = ClimateCategory::factory()->create(['nome' => 'Liderança', 'empresa_id' => $empresa->id]);
        $cat2 = ClimateCategory::factory()->create(['nome' => 'Ambiente', 'empresa_id' => $empresa->id]);

        $q1 = ClimateQuestion::factory()->tipoNota()->create(['survey_id' => $survey->id, 'category_id' => $cat1->id]);
        $q2 = ClimateQuestion::factory()->tipoNota()->create(['survey_id' => $survey->id, 'category_id' => $cat2->id]);

        $this->createResponse($survey->id, $q1->id, $empresa->id, 8);
        $this->createResponse($survey->id, $q1->id, $empresa->id, 10); // Média Q1: 9.0

        $this->createResponse($survey->id, $q2->id, $empresa->id, 5);
        $this->createResponse($survey->id, $q2->id, $empresa->id, 6); // Média Q2: 5.5

        $service = new CalculateClimateMetricsService();
        $metrics = $service->getMetricsBySurvey($survey->id, $empresa->id);

        $averages = collect($metrics['averages_by_category']);
        
        // Verifica ordenação decrescente e valores
        $this->assertEquals('Liderança', $averages->first()->categoria);
        $this->assertEquals(9.0, $averages->first()->media);
        
        $this->assertEquals('Ambiente', $averages->last()->categoria);
        $this->assertEquals(5.5, $averages->last()->media);
    }

    private function createResponse(int $surveyId, int $questionId, int $empresaId, int $nota): void
    {
        // Usa insert direto pra bypassar model constraints no teste
        \DB::table('climate_responses')->insert([
            'survey_id'         => $surveyId,
            'question_id'       => $questionId,
            'empresa_id'        => $empresaId,
            'nota'              => $nota,
            'encrypted_user_id' => Crypt::encryptString('1'),
            'created_at'        => now(),
        ]);
    }
}
