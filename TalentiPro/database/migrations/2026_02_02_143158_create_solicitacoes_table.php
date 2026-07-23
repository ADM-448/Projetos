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
        Schema::create('solicitacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Quem solicitou
            $table->foreignId('avaliacao_id')->constrained('avaliacaos')->onDelete('cascade');
            $table->enum('tipo', ['reabertura', 'contestacao'])->default('reabertura');
            $table->text('justificativa');
            $table->enum('status', ['pendente', 'aprovada', 'recusada'])->default('pendente');
            $table->text('resposta_rh')->nullable(); // Opcional, se o RH quiser explicar a recusa
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitacoes');
    }
};
