<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alterando coluna enum para suportar 'mediador' e outras opções
        DB::statement("ALTER TABLE avaliacao_participantes MODIFY COLUMN tipo ENUM('autoavaliacao', 'gestor', 'par', 'subordinado', 'mediador') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         DB::statement("ALTER TABLE avaliacao_participantes MODIFY COLUMN tipo ENUM('autoavaliacao', 'gestor', 'par', 'subordinado') NOT NULL");
    }
};
