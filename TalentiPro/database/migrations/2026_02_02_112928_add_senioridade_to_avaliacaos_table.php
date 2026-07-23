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
        Schema::table('avaliacaos', function (Blueprint $table) {
            $table->string('senioridade')->nullable()->after('forma_avaliacao');
            $table->date('data_admissao')->nullable()->after('senioridade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('avaliacaos', function (Blueprint $table) {
            $table->dropColumn(['senioridade', 'data_admissao']);
        });
    }
};
