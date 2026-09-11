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
        Schema::create('configuracoes_google', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->dateTime('token_expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('reuniao_agendamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gestor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('colaborador_id')->constrained('users')->onDelete('cascade');
            $table->string('titulo');
            $table->dateTime('data_hora');
            $table->string('google_event_id')->nullable();
            $table->string('google_meet_link')->nullable();
            $table->text('transcricao_temporaria')->nullable();
            $table->enum('status', ['agendado', 'em_andamento', 'concluido', 'cancelado'])->default('agendado');
            $table->timestamps();
        });

        Schema::create('reuniao_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gestor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('agendamento_id')->nullable()->constrained('reuniao_agendamentos')->onDelete('set null');
            $table->text('transcricao_completa')->nullable();
            $table->text('resumo_ia')->nullable();
            $table->text('sentimento_ia')->nullable();
            $table->string('audio_path')->nullable();
            $table->timestamps();
        });

        Schema::create('reuniao_feedback_participantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reuniao_feedback_id')->constrained('reuniao_feedbacks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reuniao_feedback_participantes');
        Schema::dropIfExists('reuniao_feedbacks');
        Schema::dropIfExists('reuniao_agendamentos');
        Schema::dropIfExists('configuracoes_google');
    }
};
