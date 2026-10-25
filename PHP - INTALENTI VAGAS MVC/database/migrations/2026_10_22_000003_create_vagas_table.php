<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vagas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('recrutador_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('portal_id')->default(1)->index();
            $table->string('titulo');
            $table->string('tipo_contrato')->nullable(); // CLT, PJ, Estágio, Jovem Aprendiz, 1° Emprego, etc.
            $table->string('modalidade')->nullable(); // Presencial, Híbrido, Remoto
            $table->string('localizacao')->nullable()->index();
            $table->string('cep', 10)->nullable();
            $table->string('ramo_atividade')->nullable()->index();
            $table->text('descricao');
            $table->text('requisitos')->nullable();
            $table->text('beneficios')->nullable();
            $table->boolean('confidencial')->default(false);
            $table->string('tipo_vaga')->nullable();
            $table->string('experiencia')->nullable();
            $table->string('faixa_salarial')->nullable();
            $table->decimal('faixa_salarial_min', 10, 2)->nullable();
            $table->decimal('faixa_salarial_max', 10, 2)->nullable();
            $table->boolean('salario_a_combinar')->default(false);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status', 20)->default('ativa')->index();
            $table->timestamps();

            $table->index(['portal_id', 'localizacao'], 'idx_portal_local');
            $table->index(['portal_id', 'created_at'], 'idx_portal_created');
            if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
                $table->fullText(['titulo', 'descricao'], 'idx_fulltext_vagas');
            }
        });

        Schema::create('perguntas_vaga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vaga_id')->constrained('vagas')->cascadeOnDelete();
            $table->text('texto_pergunta');
            $table->boolean('eliminatoria')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perguntas_vaga');
        Schema::dropIfExists('vagas');
    }
};
