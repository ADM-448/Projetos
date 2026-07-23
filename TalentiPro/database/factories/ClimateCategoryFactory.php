<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ClimateCategory;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClimateCategory>
 */
class ClimateCategoryFactory extends Factory
{
    protected $model = ClimateCategory::class;

    public function definition(): array
    {
        $categorias = [
            'Liderança', 'Ambiente de Trabalho', 'Comunicação',
            'Reconhecimento', 'Desenvolvimento', 'eNPS', 'Bem-estar',
            'Diversidade e Inclusão', 'Processos Internos',
        ];

        return [
            'empresa_id' => Empresa::factory(),
            'nome'       => fake()->randomElement($categorias),
            'descricao'  => fake()->sentence(),
            'cor'        => fake()->hexColor(),
        ];
    }
}
