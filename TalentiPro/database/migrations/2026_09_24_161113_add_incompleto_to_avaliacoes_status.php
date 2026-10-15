<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Altera o ENUM para incluir os novos valores 'agendada' e 'incompleto'
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE avaliacoes MODIFY status ENUM('rascunho','andamento','agendada','concluida','incompleto') NOT NULL DEFAULT 'rascunho'");
        }
    }

    public function down(): void
    {
        // Reverter: remove os novos valores (cuidado — dados com esses valores serão afetados)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE avaliacoes MODIFY status ENUM('rascunho','andamento','agendada','concluida') NOT NULL DEFAULT 'rascunho'");
        }
    }
};
