<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tenta dropar índices antigos (global users_email_unique)
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['email']); // Tenta dropar constraint 'users_email_unique'
            });
        } catch (\Exception $e) {
            // Se falhar (não existe), ignora
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_email_unique'); // Tenta dropar pelo nome explícito
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['cpf']);
            });
        } catch (\Exception $e) {}

        try {
             Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_cpf_unique');
            });
        } catch (\Exception $e) {}

        // Dropa compostas antigas se existirem (para recriar)
        try {
            Schema::table('users', function (Blueprint $table) {
                // Nome padrão gerado pelo Laravel: table_fields_unique
                $table->dropUnique(['email', 'empresa_id']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['cpf', 'empresa_id']);
            });
        } catch (\Exception $e) {}

        // Tenta adicionar as novas compostas
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->unique(['email', 'empresa_id']);
                $table->unique(['cpf', 'empresa_id']);
            });
        } catch (\Exception $e) {
            // Se já existirem, ignora
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
    }
};
