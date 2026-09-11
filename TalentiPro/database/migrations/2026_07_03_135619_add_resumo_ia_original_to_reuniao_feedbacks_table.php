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
        Schema::table('reuniao_feedbacks', function (Blueprint $table) {
            $table->longText('resumo_ia_original')->nullable()->after('resumo_ia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reuniao_feedbacks', function (Blueprint $table) {
            $table->dropColumn('resumo_ia_original');
        });
    }
};
