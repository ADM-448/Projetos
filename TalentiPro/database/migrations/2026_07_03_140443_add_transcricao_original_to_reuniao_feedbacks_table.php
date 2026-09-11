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
            $table->longText('transcricao_original')->nullable()->after('transcricao_completa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reuniao_feedbacks', function (Blueprint $table) {
            $table->dropColumn('transcricao_original');
        });
    }
};
