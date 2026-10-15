<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * SQLite (usado nos testes) não suporta ALTER TABLE MODIFY — só executa no MySQL.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return; // SQLite não tem ENUM nem suporta MODIFY; coluna é string por padrão.
        }

        // Adiciona 'quinzenal' ao ENUM de recorrencia e corrige dias_duracao para inteiro sem sinal
        DB::statement("ALTER TABLE avaliacao_schedules MODIFY recorrencia ENUM('none','diario','semanal','quinzenal','mensal','semestral','anual') NOT NULL DEFAULT 'none'");
        DB::statement("ALTER TABLE avaliacao_schedules MODIFY dias_duracao INT UNSIGNED NOT NULL DEFAULT 15");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE avaliacao_schedules MODIFY recorrencia ENUM('none','diario','semanal','mensal','semestral','anual') NOT NULL DEFAULT 'none'");
        DB::statement("ALTER TABLE avaliacao_schedules MODIFY dias_duracao INT NOT NULL DEFAULT 15");
    }
};
