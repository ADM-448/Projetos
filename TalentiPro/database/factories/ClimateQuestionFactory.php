<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ClimateQuestion;
use App\Models\ClimateSurvey;

class ClimateQuestionFactory extends Factory
{
    protected $model = ClimateQuestion::class;

    public function definition(): array
    {
        return [
            'climate_survey_id' => ClimateSurvey::factory(),
            'texto_pergunta' => $this->faker->sentence . '?',
            'tipo_resposta' => 'escala_1_10',
            'dimensao' => 'Geral'
        ];
    }
}
