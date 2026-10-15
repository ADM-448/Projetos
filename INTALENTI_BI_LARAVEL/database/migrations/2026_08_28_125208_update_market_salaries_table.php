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
        Schema::table('market_salaries', function (Blueprint $table) {
            $table->string('porte_empresa')->nullable()->after('regiao');
            $table->string('segmento_empresa')->nullable()->after('porte_empresa');
            $table->json('competencias_chave')->nullable()->after('segmento_empresa');
            $table->string('context_hash')->nullable()->unique()->after('competencias_chave');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_salaries', function (Blueprint $table) {
            $table->dropColumn(['porte_empresa', 'segmento_empresa', 'competencias_chave', 'context_hash']);
        });
    }
};
