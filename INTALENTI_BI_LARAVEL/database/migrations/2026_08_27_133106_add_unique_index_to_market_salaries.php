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
            $table->unique(['cargo', 'regiao']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_salaries', function (Blueprint $table) {
            $table->dropUnique(['cargo', 'regiao']);
        });
    }
};
