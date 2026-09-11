<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Departamento;
use App\Models\Empresa;

class DepartamentoFactory extends Factory
{
    protected $model = Departamento::class;

    public function definition(): array
    {
        return [
            'nome' => $this->faker->word . ' Dept',
            'empresa_id' => Empresa::factory(),
            'descricao' => $this->faker->sentence
        ];
    }
}
