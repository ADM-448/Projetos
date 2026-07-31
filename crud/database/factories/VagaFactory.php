<?php

namespace Database\Factories;

use App\Models\Vaga;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vaga>
 */
class VagaFactory extends Factory
{
    protected $model = Vaga::class;

    public function definition(): array
    {
        return [
            'titulo' => $this->faker->jobTitle(),
            'descricao' => $this->faker->paragraph(),
            'salario' => $this->faker->randomFloat(2, 2000, 15000),
            'ativa' => true,
        ];
    }
}
