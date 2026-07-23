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
        // Alter table to modify enum column is tricky in some DBs, but raw SQL usually works for MySQL
        DB::statement("ALTER TABLE avaliacao_participantes MODIFY COLUMN tipo ENUM('autoavaliacao', 'gestor', 'par', 'subordinado', 'mediador')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE avaliacao_participantes MODIFY COLUMN tipo ENUM('autoavaliacao', 'gestor', 'par', 'subordinado')");
    }
};
