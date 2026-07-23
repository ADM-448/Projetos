<?php

namespace Database\Seeders;

use App\Models\Departamento;
use Illuminate\Database\Seeder;

class DepartamentoSeeder extends Seeder
{
    public function run(): void
    {
        $departamentos = [
            ['nome' => 'Recursos Humanos'],
            ['nome' => 'Tecnologia da Informação'],
            ['nome' => 'Financeiro'],
            ['nome' => 'Comercial'],
            ['nome' => 'Marketing'],
            ['nome' => 'Operações'],
        ];

        $empresaId = 1; // TalentiPro Corp

        foreach ($departamentos as $dept) {
            Departamento::create(array_merge($dept, ['empresa_id' => $empresaId]));
        }
    }
}
