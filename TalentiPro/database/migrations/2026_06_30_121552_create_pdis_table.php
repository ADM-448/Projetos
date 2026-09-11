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
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('colaborador_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('gestor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pdi_grupo_id')->nullable()->constrained('pdi_grupos')->nullOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->date('data_conclusao')->nullable();
            $table->enum('status', ['nao_iniciado', 'em_andamento', 'concluido', 'atrasado', 'cancelado'])->default('nao_iniciado');
            $table->integer('progresso')->default(0);
            $table->string('comprovante')->nullable();
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
