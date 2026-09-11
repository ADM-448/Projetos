<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('climate_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('climate_survey_id')->constrained('climate_surveys')->onDelete('cascade');
            $table->foreignId('climate_question_id')->constrained('climate_questions')->onDelete('cascade');
            $table->integer('nota')->nullable(); // 1 a 5 para escala_1_5
            $table->text('comentario')->nullable(); // para texto_livre ou observações
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('climate_responses');
    }
};
