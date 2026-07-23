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
            $table->foreignId('avaliacao_id')->constrained('avaliacaos')->onDelete('cascade');
            $table->foreignId('avaliado_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('avaliador_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('mediador_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('tipo', ['autoavaliacao', 'gestor', 'par', 'subordinado']);
            $table->enum('status', ['pendente', 'realizada', 'mediada'])->default('pendente');
            $table->timestamps();
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
