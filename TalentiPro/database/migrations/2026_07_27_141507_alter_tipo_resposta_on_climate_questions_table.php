<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE climate_questions MODIFY tipo_resposta VARCHAR(255) DEFAULT 'escala_1_10'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE climate_questions MODIFY tipo_resposta ENUM('escala_1_10', 'multipla_escolha', 'texto_livre', 'sim_nao') DEFAULT 'escala_1_10'");
        }
    }
};
