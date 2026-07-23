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
        Schema::create('solicitacao_avaliacaos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->onDelete('cascade');
            $table->foreignId('colaborador_id')->constrained('users')->onDelete('cascade');
            $table->text('motivo')->nullable();
            $table->enum('status', ['pendente', 'aprovada', 'recusada'])->default('pendente');
            $table->foreignId('aprovador_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('observacao_aprovador')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitacao_avaliacaos');
    }
};
