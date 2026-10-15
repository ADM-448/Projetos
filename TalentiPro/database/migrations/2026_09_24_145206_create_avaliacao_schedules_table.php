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
        Schema::create('avaliacao_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_avaliacao_id')->constrained('avaliacoes')->cascadeOnDelete();
            $table->enum('recorrencia', ['none', 'diario', 'semanal', 'mensal', 'semestral', 'anual'])->default('none');
            $table->date('proxima_execucao')->nullable();
            $table->integer('dias_duracao')->default(15);
            $table->json('config_delegacao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avaliacao_schedules');
    }
};
