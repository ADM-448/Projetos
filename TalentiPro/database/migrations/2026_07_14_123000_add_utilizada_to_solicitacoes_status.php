<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Adiciona 'utilizada' ao status de solicitações.
 *
 * Contexto: Quando uma Solicitação de Reabertura de avaliação é aprovada e o
 * colaborador reedita sua resposta, o status é marcado como 'utilizada' para
 * evitar que a mesma aprovação permita reedições ilimitadas.
 *
 * Esta migration altera o ENUM no MySQL em produção.
 * No SQLite (testes), o ENUM é tratado como TEXT, portanto sem constraint.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitacoes', function (Blueprint $table) {
            if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE solicitacoes MODIFY COLUMN status ENUM('pendente', 'aprovada', 'recusada', 'utilizada') NOT NULL DEFAULT 'pendente'");
            }
        });
    }

    public function down(): void
    {
        Schema::table('solicitacoes', function (Blueprint $table) {
            if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE solicitacoes MODIFY COLUMN status ENUM('pendente', 'aprovada', 'recusada') NOT NULL DEFAULT 'pendente'");
            }
        });
    }
};
