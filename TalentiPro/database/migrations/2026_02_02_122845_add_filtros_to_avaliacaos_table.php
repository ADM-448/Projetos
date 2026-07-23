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
        Schema::table('avaliacaos', function (Blueprint $table) {
            $table->text('filtros')->nullable()->after('status'); // Armazena JSON com depts e cargos selecionados
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('avaliacaos', function (Blueprint $table) {
            $table->dropColumn('filtros');
        });
    }
};
