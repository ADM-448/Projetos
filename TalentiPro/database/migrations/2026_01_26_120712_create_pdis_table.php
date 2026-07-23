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
        Schema::create('pdis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->onDelete('cascade');
            $table->foreignId('colaborador_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('gestor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->date('prazo')->nullable();
            $table->enum('status', ['nao_iniciado', 'em_andamento', 'concluido', 'atrasado', 'cancelado'])->default('nao_iniciado');
            $table->integer('progresso')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdis');
    }
};
