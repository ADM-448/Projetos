<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            EmpresaSeeder::class,
            DepartamentoSeeder::class,
            CargoSeeder::class,
            UserSeeder::class,
            CompetenciaSeeder::class,
        ]);

        $this->command->info('✅ Banco de dados populado com sucesso!');
        $this->command->info('📧 Login: admin@talentipro.com');
        $this->command->info('🔑 Senha: password');
    }
}
