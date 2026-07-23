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
        Schema::table('reuniao_feedback', function (Blueprint $table) {
            $table->foreignId('agendamento_id')->nullable()->after('id')->constrained('reuniao_agendamentos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reuniao_feedback', function (Blueprint $table) {
            $table->dropForeign(['agendamento_id']);
            $table->dropColumn('agendamento_id');
        });
    }
};
