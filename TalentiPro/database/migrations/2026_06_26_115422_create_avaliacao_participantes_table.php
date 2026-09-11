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
        Schema::create('avaliacao_participantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('avaliacao_id')->constrained('avaliacoes')->cascadeOnDelete();
            $table->foreignId('avaliado_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('avaliador_id')->constrained('users')->cascadeOnDelete();
            $table->enum('tipo', ['autoavaliacao', 'gestor', 'pares', 'subordinado', 'mediador']);
            $table->enum('status', ['pendente', 'respondido'])->default('pendente');
            $table->timestamps();
            
            $table->unique(['avaliacao_id', 'avaliado_id', 'avaliador_id', 'tipo'], 'aval_participantes_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avaliacao_participantes');
    }
};
