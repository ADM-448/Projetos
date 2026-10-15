<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Estado de rascunho da gravação: permite gravar em segundo plano e retomar
     * a revisão (transcrição/análise) mesmo após sair da tela ou recarregar.
     */
    public function up(): void
    {
        Schema::table('reuniao_agendamentos', function (Blueprint $table) {
            // nenhuma | gravando | enviando | transcrita | analisada
            $table->string('gravacao_status', 20)->default('nenhuma')->after('status');
            $table->dateTime('gravacao_iniciada_em')->nullable()->after('gravacao_status');
            $table->dateTime('gravacao_heartbeat_em')->nullable()->after('gravacao_iniciada_em');
            $table->string('audio_path')->nullable()->after('gravacao_heartbeat_em');
            $table->longText('transcricao_rascunho')->nullable()->after('audio_path');
            $table->json('analise_rascunho')->nullable()->after('transcricao_rascunho');
        });
    }

    public function down(): void
    {
        Schema::table('reuniao_agendamentos', function (Blueprint $table) {
            $table->dropColumn([
                'gravacao_status',
                'gravacao_iniciada_em',
                'gravacao_heartbeat_em',
                'audio_path',
                'transcricao_rascunho',
                'analise_rascunho',
            ]);
        });
    }
};
