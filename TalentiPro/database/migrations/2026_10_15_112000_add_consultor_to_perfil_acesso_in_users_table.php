<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN perfil_acesso ENUM('admin', 'master', 'gestor', 'colaborador', 'consultor') NOT NULL DEFAULT 'colaborador'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN perfil_acesso ENUM('admin', 'master', 'gestor', 'colaborador') NOT NULL DEFAULT 'colaborador'");
        }
    }
};
