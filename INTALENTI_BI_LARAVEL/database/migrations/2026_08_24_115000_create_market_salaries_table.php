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
        Schema::create('market_salaries', function (Blueprint $table) {
            $table->id();
            $table->string('cargo');
            $table->string('regiao')->nullable();
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->decimal('mediana', 10, 2)->nullable();
            $table->decimal('piso', 10, 2)->nullable();
            $table->decimal('teto', 10, 2)->nullable();
            $table->json('raw_data')->nullable(); // Guardar dados adicionais de scrape
            $table->timestamp('last_scraped_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_salaries');
    }
};
