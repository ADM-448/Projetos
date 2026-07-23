<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Note que o nome da tabela é no singular, ordem alfabética (padrão Laravel)
        // Se no Model você definiu outro nome, mude aqui.
        // No seu model Avaliacao estava: 'avaliacao_competencias' (plural)

        Schema::create('avaliacao_competencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('avaliacao_id')->constrained('avaliacaos')->onDelete('cascade');
            $table->foreignId('competencia_id')->constrained('competencias')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avaliacao_competencia');
    }
};
