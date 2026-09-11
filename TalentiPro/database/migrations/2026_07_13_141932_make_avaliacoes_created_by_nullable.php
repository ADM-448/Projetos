<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Torna avaliacoes.created_by nullable para permitir a exclusão
     * de usuários que criaram avaliações, sem violar a FK constraint.
     *
     * As avaliações em si são preservadas — apenas o vínculo com o
     * criador é removido (created_by = null) quando o usuário é deletado.
     */
    public function up(): void
    {
        // Dividimos as operações em blocos separados para garantir a compatibilidade com SQLite (usado em memória nos testes),
        // pois alterar a nulidade e as constraints ao mesmo tempo pode falhar silenciosamente no SQLite.
        Schema::table('avaliacoes', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        Schema::table('avaliacoes', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });

        Schema::table('avaliacoes', function (Blueprint $table) {
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('avaliacoes', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->unsignedBigInteger('created_by')->nullable(false)->change();
            $table->foreign('created_by')->references('id')->on('users');
        });
    }
};
