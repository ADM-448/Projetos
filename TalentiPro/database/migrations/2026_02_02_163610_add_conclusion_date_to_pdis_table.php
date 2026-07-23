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
            if (!Schema::hasColumn('pdis', 'data_conclusao')) {
                $table->date('data_conclusao')->nullable()->after('data_fim'); // Data real de conclusão
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pdis', function (Blueprint $table) {
            $table->dropColumn(['data_conclusao']);
        });
    }
};
