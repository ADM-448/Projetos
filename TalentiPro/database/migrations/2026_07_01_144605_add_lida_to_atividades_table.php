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
        Schema::table('atividades', function (Blueprint $table) {
            $table->boolean('lida')->default(false)->after('url');
            $table->timestamp('read_at')->nullable()->after('lida');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atividades', function (Blueprint $table) {
            $table->dropColumn(['lida', 'read_at']);
        });
    }
};
