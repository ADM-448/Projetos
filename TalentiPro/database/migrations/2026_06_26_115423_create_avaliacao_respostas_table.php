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
        Schema::create('avaliacao_respostas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('avaliacao_participante_id')->constrained('avaliacao_participantes')->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained('skills')->cascadeOnDelete();
            $table->integer('nota'); // 1 a 5
            $table->timestamps();
            
            $table->unique(['avaliacao_participante_id', 'skill_id'], 'aval_respostas_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avaliacao_respostas');
    }
};
