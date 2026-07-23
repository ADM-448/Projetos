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
        Schema::create('avaliacao_fechamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('avaliacao_id')->constrained('avaliacaos')->onDelete('cascade');
            $table->foreignId('avaliado_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('responsavel_id')->constrained('users'); // Quem fechou (RH/Master)
            
            // Notas Finais (Mediação)
            $table->decimal('nota_final', 5, 2)->nullable();
            
            // Opcional: Notas de cada pilar/competência se quisermos salvar detalhado, 
            // mas por enquanto só a nota final foi pedida explicitamente no form?
            // "media mediação" implica uma nota geral ou por competência?
            // O layout mostra "Média Mediação 0.00/10". Um único valor.
            
            $table->text('comentarios')->nullable();
            $table->text('plano_acao')->nullable(); // Futuro?
            
            $table->timestamps();
            
            $table->unique(['avaliacao_id', 'avaliado_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avaliacao_fechamentos');
    }
};
