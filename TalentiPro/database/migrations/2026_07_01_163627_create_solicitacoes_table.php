<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('avaliacao_id')->constrained('avaliacoes')->cascadeOnDelete();
            $table->enum('tipo', ['reabertura', 'contestacao']);
            $table->text('justificativa');
            $table->enum('status', ['pendente', 'aprovada', 'recusada', 'utilizada'])->default('pendente');
            $table->text('resposta_rh')->nullable()->comment('Obrigatório ao recusar — RF-79');
            $table->timestamps();

            // Um colaborador só pode ter uma solicitação pendente por avaliação/tipo
            $table->unique(['user_id', 'avaliacao_id', 'tipo'], 'solicitacao_unica_por_tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitacoes');
    }
};

