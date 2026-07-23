<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ClimateCategory;
use App\Models\ClimateQuestion;
use App\Models\ClimateSurvey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClimateQuestion>
 */
class ClimateQuestionFactory extends Factory
{
    protected $model = ClimateQuestion::class;

    public function definition(): array
    {
        return [
            'survey_id'   => ClimateSurvey::factory(),
            'category_id' => null,
            'texto'       => fake()->sentence() . '?',
            'tipo'        => 'nota',
            'obrigatoria' => true,
            'ordem'       => fake()->numberBetween(0, 20),
        ];
    }

    public function tipoNota(): static
    {
        return $this->state(['tipo' => 'nota']);
    }

    public function tipoTexto(): static
    {
        return $this->state(['tipo' => 'texto']);
    }

    public function opcional(): static
    {
        return $this->state(['obrigatoria' => false]);
    }
}
