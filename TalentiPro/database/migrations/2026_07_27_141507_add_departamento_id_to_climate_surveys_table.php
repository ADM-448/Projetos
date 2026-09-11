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
        Schema::table('climate_surveys', function (Blueprint $table) {
            $table->foreignId('departamento_id')->nullable()->after('empresa_id')->constrained('departamentos')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('climate_surveys', function (Blueprint $table) {
            $table->dropForeign(['departamento_id']);
            $table->dropColumn('departamento_id');
        });
    }
};
