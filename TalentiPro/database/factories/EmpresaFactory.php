<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Empresa>
 */
class EmpresaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->company(),
            'cnpj' => fake()->numerify('##.###.###/####-##'),
            'razao_social' => fake()->companySuffix(),
            'email' => fake()->unique()->companyEmail(),
            'telefone' => fake()->phoneNumber(),
            'max_usuarios' => 50,
            'status' => 'ativa',
        ];
    }
}
