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
                Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')
                ->nullable()
                ->constrained('empresas')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            
            // Unicidade de email combinada por empresa (Multitenant!)
            $table->unique(['email', 'empresa_id']);

            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('perfil_acesso', ['admin', 'master', 'gestor', 'colaborador'])->default('colaborador');
            
            // --- NOVAS COLUNAS DA SPRINT 2 ---
            $table->string('cpf', 14)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->date('data_nascimento')->nullable();
            $table->enum('genero', ['M', 'F', 'O'])->nullable();
            $table->string('escolaridade', 50)->nullable();
            $table->string('foto_perfil')->nullable();
            $table->date('data_admissao')->nullable();
            $table->date('data_rescisao')->nullable();
            $table->enum('status', ['ativo', 'inativo', 'ferias'])->default('ativo');
            $table->enum('vinculo', ['clt', 'pj', 'estagio'])->default('clt');

            // ID dos relacionamentos (apenas colunas, a chave física faremos na tabela correspondente)
            $table->unsignedBigInteger('departamento_id')->nullable();
            $table->unsignedBigInteger('cargo_id')->nullable();
            $table->unsignedBigInteger('gestor_id')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
