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
            $table->foreignId('avaliacao_id')->constrained('avaliacoes')->cascadeOnDelete();
            $table->foreignId('avaliado_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('responsavel_id')->constrained('users')->cascadeOnDelete(); // master ou gestor que fechou
            $table->decimal('nota_final', 4, 2)->nullable();
            $table->text('comentario')->nullable();
            $table->timestamps();
            
            $table->unique(['avaliacao_id', 'avaliado_id'], 'aval_fechamentos_unique');
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
