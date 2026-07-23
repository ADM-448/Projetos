<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin - Não vinculado a empresa
        User::create([
            'name' => 'Admin Master',
            'email' => 'admin@talentipro.com',
            'password' => Hash::make('password'),
            'cpf' => '111.111.111-11',
            'data_nascimento' => '1985-05-15',
            'genero' => 'M',
            'escolaridade' => 'Superior Completo',
            'data_admissao' => '2020-01-10',
            'status' => 'ativo',
            'vinculo' => 'clt',
            'perfil_acesso' => 'admin',
        ]);
    }
}
