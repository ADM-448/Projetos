<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ClimateSurvey;
use App\Models\Empresa;

class ClimateSurveyFactory extends Factory
{
    protected $model = ClimateSurvey::class;

    public function definition(): array
    {
        return [
            'titulo' => $this->faker->sentence(3),
            'descricao' => $this->faker->sentence,
            'empresa_id' => Empresa::factory(),
            'data_inicio' => now(),
            'status' => 'rascunho'
        ];
    }
}
