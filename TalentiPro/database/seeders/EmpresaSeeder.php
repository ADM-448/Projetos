<?php

namespace Database\Seeders;

use App\Models\Empresa;
use Illuminate\Database\Seeder;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        Empresa::create([
            'nome' => 'TalentiPro Corp',
            'cnpj' => '12.345.678/0001-90',
            'razao_social' => 'TalentiPro Sistemas S.A.',
            'email' => 'contato@talentipro.com',
            'status' => 'ativa'
        ]);

        Empresa::create([
            'nome' => 'Empresa Exemplo Ltda',
            'cnpj' => '98.765.432/0001-00',
            'razao_social' => 'Exemplo Ltda',
            'email' => 'contato@exemplo.com',
            'status' => 'ativa'
        ]);
    }
}
