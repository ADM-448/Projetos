<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('climate_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')
                  ->constrained('climate_surveys')
                  ->onDelete('cascade');
            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained('climate_categories')
                  ->onDelete('set null');
            $table->text('texto');
            $table->enum('tipo', ['nota', 'texto']); // nota=1-10, texto=aberto
            $table->boolean('obrigatoria')->default(true);
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->timestamps();

            // Índice composto para busca ordenada das perguntas de uma survey
            $table->index(['survey_id', 'ordem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('climate_questions');
    }
};
