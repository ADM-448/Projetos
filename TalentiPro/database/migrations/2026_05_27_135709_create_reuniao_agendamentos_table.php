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
        Schema::create('reuniao_agendamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gestor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('colaborador_id')->constrained('users')->onDelete('cascade');
            $table->string('titulo');
            $table->dateTime('data_hora');
            $table->string('link_jitsi')->nullable();
            $table->enum('status', ['agendado', 'em_andamento', 'concluido', 'cancelado'])->default('agendado');
            $table->text('transcricao_temporaria')->nullable(); // Para guardar a transcrição distribuída
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reuniao_agendamentos');
    }
};
