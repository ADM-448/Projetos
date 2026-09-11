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
            $table->foreignId('reuniao_feedback_id')
                ->nullable()
                ->after('pdi_grupo_id')
                ->constrained('reuniao_feedbacks')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pdis', function (Blueprint $table) {
            $table->dropForeign(['reuniao_feedback_id']);
            $table->dropColumn('reuniao_feedback_id');
        });
    }
};
