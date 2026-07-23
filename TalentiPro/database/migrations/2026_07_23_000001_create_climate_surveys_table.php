<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('climate_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')
                  ->constrained('empresas')
                  ->onDelete('cascade');
            $table->string('titulo', 255);
            $table->text('descricao')->nullable();
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->enum('status', ['rascunho', 'ativa', 'encerrada'])
                  ->default('rascunho');
            $table->enum('visibilidade_gestor', ['geral', 'apenas_departamento'])
                  ->default('geral');
            $table->boolean('resultado_liberado_colaboradores')->default(false);
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamps();

            // Índice para filtragem por empresa (usado pelo GlobalScope BelongsToEmpresa)
            $table->index('empresa_id');
            $table->index(['empresa_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('climate_surveys');
    }
};
