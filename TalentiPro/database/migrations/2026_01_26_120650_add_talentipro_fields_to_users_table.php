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
        Schema::table('users', function (Blueprint $table) {
            // Dados Pessoais
            $table->string('cpf', 14)->unique()->nullable()->after('email');
            $table->date('data_nascimento')->nullable();
            $table->enum('genero', ['M', 'F', 'O'])->nullable();
            $table->string('escolaridade', 50)->nullable();
            $table->string('foto_perfil')->nullable();

            // Dados Contratuais
            $table->date('data_admissao')->nullable();
            $table->enum('status', ['ativo', 'inativo', 'ferias'])->default('ativo');
            $table->enum('vinculo', ['clt', 'pj', 'estagio'])->default('clt');

            // Relacionamentos Organizacionais
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->onDelete('set null');
            $table->foreignId('cargo_id')->nullable()->constrained('cargos')->onDelete('set null');
            $table->foreignId('gestor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('perfil_acesso', ['master', 'admin', 'gestor', 'mediador', 'colaborador'])->default('colaborador');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['departamento_id']);
            $table->dropForeign(['cargo_id']);
            $table->dropForeign(['gestor_id']);

            $table->dropColumn([
                'cpf',
                'data_nascimento',
                'genero',
                'escolaridade',
                'foto_perfil',
                'data_admissao',
                'status',
                'vinculo',
                'departamento_id',
                'cargo_id',
                'gestor_id',
                'perfil_acesso'
            ]);
        });
    }
};
