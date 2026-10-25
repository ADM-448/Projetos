<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidatos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nome_completo');
            $table->date('data_nascimento')->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('celular', 20)->nullable();
            $table->string('cep', 10)->nullable()->index();
            $table->string('logradouro')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable()->index();
            $table->string('estado', 2)->nullable();
            $table->decimal('latitude', 10, 7)->nullable()->index();
            $table->decimal('longitude', 10, 7)->nullable()->index();
            $table->text('sobre_mim')->nullable();
            $table->text('resumo_profissional')->nullable();
            $table->string('situacao_profissional')->nullable();
            $table->string('cargo_pretendido')->nullable();
            $table->string('faixa_salarial')->nullable();
            $table->string('escolaridade')->nullable();
            $table->string('sexo', 50)->nullable();
            $table->string('estado_civil', 50)->nullable();
            $table->string('nacionalidade')->default('Brasileiro(a)');
            $table->string('rne', 50)->nullable();
            $table->string('curriculo_path')->nullable();
            $table->json('idiomas')->nullable();
            $table->string('carteira_habilitacao', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('experiencias_profissionais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidato_id')->constrained('candidatos')->cascadeOnDelete();
            $table->string('empresa');
            $table->string('cargo');
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->boolean('atual')->default(false);
            $table->text('descricao')->nullable();
            $table->timestamps();
        });

        Schema::create('formacoes_academicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidato_id')->constrained('candidatos')->cascadeOnDelete();
            $table->string('instituicao');
            $table->string('curso');
            $table->string('nivel')->nullable();
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->boolean('concluido')->default(true);
            $table->timestamps();
        });

        Schema::create('cursos_complementares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidato_id')->constrained('candidatos')->cascadeOnDelete();
            $table->string('nome_curso');
            $table->string('instituicao')->nullable();
            $table->string('carga_horaria', 50)->nullable();
            $table->string('ano_conclusao', 4)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cursos_complementares');
        Schema::dropIfExists('formacoes_academicas');
        Schema::dropIfExists('experiencias_profissionais');
        Schema::dropIfExists('candidatos');
    }
};
