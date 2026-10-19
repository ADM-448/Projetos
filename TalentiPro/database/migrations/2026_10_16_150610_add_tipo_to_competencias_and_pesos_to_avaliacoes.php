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
        Schema::table('competencias', function (Blueprint $table) {
            $table->string('tipo', 20)->default('comportamental')->after('descricao'); // 'comportamental' ou 'tecnica'
            $table->foreignId('cargo_id')->nullable()->after('tipo')->constrained('cargos')->nullOnDelete();
        });

        Schema::table('avaliacoes', function (Blueprint $table) {
            $table->unsignedTinyInteger('peso_tecnico')->default(50)->after('grau');
            $table->unsignedTinyInteger('peso_comportamental')->default(50)->after('peso_tecnico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('avaliacoes', function (Blueprint $table) {
            $table->dropColumn(['peso_tecnico', 'peso_comportamental']);
        });

        Schema::table('competencias', function (Blueprint $table) {
            $table->dropForeign(['cargo_id']);
            $table->dropColumn(['tipo', 'cargo_id']);
        });
    }
};
