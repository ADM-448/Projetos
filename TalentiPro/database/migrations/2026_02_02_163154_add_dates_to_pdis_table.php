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
        Schema::table('pdis', function (Blueprint $table) {
            if (!Schema::hasColumn('pdis', 'data_inicio')) {
                $table->date('data_inicio')->nullable()->after('colaborador_id');
            }
            if (!Schema::hasColumn('pdis', 'data_fim')) {
                $table->date('data_fim')->nullable()->after('data_inicio');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pdis', function (Blueprint $table) {
            $table->dropColumn(['data_inicio', 'data_fim']);
        });
    }
};
