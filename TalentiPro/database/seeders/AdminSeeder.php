<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Cria o Usuário Admin Global (Sem empresa vinculada!)
        User::create([
            'empresa_id' => null,
            'name' => 'Administrador Plataforma',
            'email' => 'ademarteisen2004@gmail.com', // String limpa sem colchetes
            'perfil_acesso' => 'admin',
            'password' => Hash::make('IntalenTI2018_AdmTI'),
            'email_verified_at' => now(),
        ]);

        // 2. Cria a Empresa de Testes (Para os outros níveis)
        $empresaAlpha = Empresa::create([
            'nome' => 'Minha Empresa',
            'cnpj' => '12345678000190',
            'status' => 'ativa',
        ]);

        // 3. Cria o Master vinculado à Empresa Alpha
        User::create([
            'empresa_id' => $empresaAlpha->id,
            'name' => 'Pamela RH',
            'email' => 'pamela@gmail.com',
            'perfil_acesso' => 'master',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now(),
        ]);

        // 4. Cria o Gestor vinculado à Empresa Alpha
        User::create([
            'empresa_id' => $empresaAlpha->id,
            'name' => 'José Gestor',
            'email' => 'jose@gmail.com',
            'perfil_acesso' => 'gestor',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now(),
        ]);

        // 5. Cria o Colaborador vinculado à Empresa Alpha
        User::create([
            'empresa_id' => $empresaAlpha->id,
            'name' => 'Carlos Colaborador',
            'email' => 'carlos@gmail.com',
            'perfil_acesso' => 'colaborador',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now(),
        ]);
    }
}
