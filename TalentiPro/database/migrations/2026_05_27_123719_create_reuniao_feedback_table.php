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
        Schema::create('reuniao_feedback', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->foreignId('gestor_id')->constrained('users')->onDelete('cascade');
            $table->longText('transcricao')->nullable();
            $table->longText('resumo')->nullable();
            $table->string('audio_path')->nullable();
            $table->enum('status', ['pendente', 'aprovada', 'cancelada'])->default('pendente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reuniao_feedback');
    }
};
