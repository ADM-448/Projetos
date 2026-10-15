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
            $table->dropUnique('market_salaries_cargo_regiao_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_salaries', function (Blueprint $table) {
            $table->unique(['cargo', 'regiao'], 'market_salaries_cargo_regiao_unique');
        });
    }
};
