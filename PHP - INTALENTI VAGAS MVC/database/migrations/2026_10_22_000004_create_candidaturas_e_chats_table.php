<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidaturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vaga_id')->constrained('vagas')->cascadeOnDelete();
            $table->foreignId('candidato_id')->constrained('candidatos')->cascadeOnDelete();
            $table->string('status', 50)->default('pendente')->index();
            $table->timestamp('data_candidatura')->useCurrent();
            $table->timestamps();

            $table->unique(['vaga_id', 'candidato_id'], 'idx_vaga_candidato');
        });

        Schema::create('respostas_candidato', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidatura_id')->constrained('candidaturas')->cascadeOnDelete();
            $table->foreignId('pergunta_id')->constrained('perguntas_vaga')->cascadeOnDelete();
            $table->text('resposta');
            $table->timestamps();
        });

        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidatura_id')->unique()->constrained('candidaturas')->cascadeOnDelete();
            $table->foreignId('vaga_id')->constrained('vagas')->cascadeOnDelete();
            $table->foreignId('candidato_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recrutador_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('mensagens_chat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('chats')->cascadeOnDelete();
            $table->foreignId('remetente_id')->constrained('users')->cascadeOnDelete();
            $table->text('mensagem');
            $table->boolean('lida')->default(false);
            $table->timestamps();
        });

        Schema::create('notificacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('mensagem');
            $table->string('link')->nullable();
            $table->boolean('lida')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacoes');
        Schema::dropIfExists('mensagens_chat');
        Schema::dropIfExists('chats');
        Schema::dropIfExists('respostas_candidato');
        Schema::dropIfExists('candidaturas');
    }
};
