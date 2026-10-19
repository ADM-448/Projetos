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
        Schema::create('cargo_questao_ias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('cargo_id')->constrained('cargos')->cascadeOnDelete();
            $table->string('hash_descricao', 64)->index(); // MD5/SHA256 da descrição e CBO para cache
            $table->json('questoes_sugeridas'); // Array com 3 a 5 perguntas técnicas recomendadas
            $table->string('modelo_ia', 50)->default('gemini-2.5-flash');
            $table->timestamps();

            $table->unique(['empresa_id', 'cargo_id', 'hash_descricao'], 'unique_cargo_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargo_questao_ias');
    }
};
