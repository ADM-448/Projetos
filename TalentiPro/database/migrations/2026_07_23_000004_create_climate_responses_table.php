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
            $table->foreignId('survey_id')
                  ->constrained('climate_surveys')
                  ->onDelete('cascade');
            $table->foreignId('question_id')
                  ->constrained('climate_questions')
                  ->onDelete('cascade');
            $table->foreignId('department_id')
                  ->nullable()
                  ->constrained('departamentos')
                  ->onDelete('set null');

            // empresa_id sem FK intencional: isolamento do anonimato.
            // Não queremos que um JOIN acidental exponha dados via Eloquent eager loading.
            $table->unsignedBigInteger('empresa_id');

            $table->unsignedTinyInteger('nota')->nullable();     // 1-10
            $table->text('texto_resposta')->nullable();

            // Campo de auditoria: user_id criptografado com Crypt::encryptString().
            // NUNCA exposto em views ou queries de dashboard.
            // Acesso exclusivo via endpoint /clima/{id}/auditoria (perfil_acesso = 'admin').
            $table->text('encrypted_user_id')->nullable();

            // Respostas são imutáveis após o envio.
            $table->timestamp('created_at')->useCurrent();
            // Sem updated_at — const UPDATED_AT = null no Model.

            // Índices compostos para as queries de agregação do CalculateClimateMetricsService
            $table->index(['survey_id', 'question_id']);
            $table->index(['survey_id', 'department_id']);
            $table->index(['survey_id', 'empresa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('climate_responses');
    }
};
