<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitacoes', function (Blueprint $table) {
            // Torna avaliacao_id nullable (era NOT NULL antes)
            $table->unsignedBigInteger('avaliacao_id')->nullable()->change();

            // Nova coluna para vincular a um DiscAssessment
            $table->unsignedBigInteger('disc_assessment_id')->nullable()->after('avaliacao_id');
            $table->foreign('disc_assessment_id')->references('id')->on('disc_assessments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('solicitacoes', function (Blueprint $table) {
            $table->dropForeign(['disc_assessment_id']);
            $table->dropColumn('disc_assessment_id');
            $table->unsignedBigInteger('avaliacao_id')->nullable(false)->change();
        });
    }
};
