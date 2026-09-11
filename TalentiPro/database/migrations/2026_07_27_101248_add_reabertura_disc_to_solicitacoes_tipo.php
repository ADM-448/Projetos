<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE solicitacoes MODIFY COLUMN tipo ENUM('reabertura', 'contestacao', 'reabertura_disc') NOT NULL");
        }
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            DB::statement("UPDATE solicitacoes SET tipo = 'reabertura' WHERE tipo = 'reabertura_disc'");
            DB::statement("ALTER TABLE solicitacoes MODIFY COLUMN tipo ENUM('reabertura', 'contestacao') NOT NULL");
        }
    }
};
