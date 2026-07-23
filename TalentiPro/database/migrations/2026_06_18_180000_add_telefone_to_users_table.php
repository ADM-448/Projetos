<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona o campo 'telefone' (WhatsApp) à tabela users.
     * Campo opcional, usado para notificações automáticas via WhatsApp.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Adicionado após 'cpf' para manter organização lógica
            $table->string('telefone', 20)->nullable()->after('cpf');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('telefone');
        });
    }
};
