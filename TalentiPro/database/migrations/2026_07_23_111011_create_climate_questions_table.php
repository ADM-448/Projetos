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
            $table->foreignId('climate_survey_id')->constrained('climate_surveys')->onDelete('cascade');
            $table->string('dimensao');
            $table->text('texto_pergunta');
            $table->string('tipo_resposta')->default('escala_1_10');
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('climate_questions');
    }
};
