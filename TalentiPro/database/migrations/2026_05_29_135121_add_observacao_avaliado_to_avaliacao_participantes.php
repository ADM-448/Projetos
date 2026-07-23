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
        Schema::table('avaliacao_participantes', function (Blueprint $table) {
            $table->text('observacao_avaliado')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('avaliacao_participantes', function (Blueprint $table) {
            $table->dropColumn('observacao_avaliado');
        });
    }
};
