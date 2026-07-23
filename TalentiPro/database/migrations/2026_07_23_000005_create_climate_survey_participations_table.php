<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('climate_survey_participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')
                  ->constrained('climate_surveys')
                  ->onDelete('cascade');
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Respostas são imutáveis — sem updated_at
            $table->timestamp('created_at')->useCurrent();

            // Garantia em nível de banco: um usuário não pode participar duas vezes.
            // Complementa a verificação da SubmitClimateSurveyAction.
            $table->unique(['survey_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('climate_survey_participations');
    }
};
