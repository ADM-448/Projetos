<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ClimateSurvey;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClimateSurvey>
 */
class ClimateSurveyFactory extends Factory
{
    protected $model = ClimateSurvey::class;

    public function definition(): array
    {
        return [
            'empresa_id'                       => Empresa::factory(),
            'titulo'                           => fake()->sentence(4),
            'descricao'                        => fake()->paragraph(),
            'data_inicio'                      => now()->subDays(7),
            'data_fim'                         => now()->addDays(7),
            'status'                           => 'rascunho',
            'visibilidade_gestor'              => 'geral',
            'resultado_liberado_colaboradores' => false,
            'created_by'                       => null,
        ];
    }

    public function rascunho(): static
    {
        return $this->state(['status' => 'rascunho']);
    }

    public function ativa(): static
    {
        return $this->state(['status' => 'ativa']);
    }

    public function encerrada(): static
    {
        return $this->state(['status' => 'encerrada']);
    }

    public function gestorVerApenasDepto(): static
    {
        return $this->state(['visibilidade_gestor' => 'apenas_departamento']);
    }

    public function resultadoLiberado(): static
    {
        return $this->state(['resultado_liberado_colaboradores' => true]);
    }
}
