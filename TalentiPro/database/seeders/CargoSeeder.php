<?php

namespace Database\Seeders;

use App\Models\Cargo;
use Illuminate\Database\Seeder;

class CargoSeeder extends Seeder
{
    public function run(): void
    {
        $cargos = [
            // RH (dept_id: 1)
            ['nome' => 'Gerente de RH', 'departamento_id' => 1, 'descricao' => 'Responsável pela gestão de pessoas'],
            ['nome' => 'Analista de RH', 'departamento_id' => 1, 'cargo_superior_id' => 1],
            ['nome' => 'Assistente de RH', 'departamento_id' => 1, 'cargo_superior_id' => 2],

            // TI (dept_id: 2)
            ['nome' => 'Gerente de TI', 'departamento_id' => 2, 'descricao' => 'Responsável pela infraestrutura tecnológica'],
            ['nome' => 'Tech Lead', 'departamento_id' => 2, 'cargo_superior_id' => 4],
            ['nome' => 'Desenvolvedor Sênior', 'departamento_id' => 2, 'cargo_superior_id' => 5],
            ['nome' => 'Desenvolvedor Pleno', 'departamento_id' => 2, 'cargo_superior_id' => 5],
            ['nome' => 'Desenvolvedor Júnior', 'departamento_id' => 2, 'cargo_superior_id' => 6],

            // Financeiro (dept_id: 3)
            ['nome' => 'Gerente Financeiro', 'departamento_id' => 3],
            ['nome' => 'Analista Financeiro', 'departamento_id' => 3, 'cargo_superior_id' => 9],

            // Comercial (dept_id: 4)
            ['nome' => 'Gerente Comercial', 'departamento_id' => 4],
            ['nome' => 'Executivo de Vendas', 'departamento_id' => 4, 'cargo_superior_id' => 11],

            // Marketing (dept_id: 5)
            ['nome' => 'Gerente de Marketing', 'departamento_id' => 5],
            ['nome' => 'Analista de Marketing', 'departamento_id' => 5, 'cargo_superior_id' => 13],

            // Operações (dept_id: 6)
            ['nome' => 'Gerente de Operações', 'departamento_id' => 6],
            ['nome' => 'Coordenador de Operações', 'departamento_id' => 6, 'cargo_superior_id' => 15],
        ];

        $empresaId = 1; // TalentiPro Corp

        foreach ($cargos as $cargo) {
            Cargo::create(array_merge($cargo, ['empresa_id' => $empresaId]));
        }
    }
}
