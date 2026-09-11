<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->boolean('ia_ativa')->default(false)->after('max_usuarios');
            $table->integer('ia_limite_transcricoes_mes')->default(10)->after('ia_ativa');
            $table->integer('ia_transcricoes_utilizadas')->default(0)->after('ia_limite_transcricoes_mes');
            $table->date('ia_limite_reset_em')->nullable()->after('ia_transcricoes_utilizadas');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'ia_ativa',
                'ia_limite_transcricoes_mes',
                'ia_transcricoes_utilizadas',
                'ia_limite_reset_em',
            ]);
        });
    }
};

