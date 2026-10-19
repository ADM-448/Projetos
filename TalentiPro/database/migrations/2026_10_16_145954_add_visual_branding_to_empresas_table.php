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
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('cor_primaria', 20)->nullable()->default('#6a4c7c')->after('logo');
            $table->string('cor_secundaria', 20)->nullable()->default('#8b5cf6')->after('cor_primaria');
            $table->string('marca_dagua')->nullable()->after('cor_secundaria');
            $table->string('texto_marca_dagua', 100)->nullable()->after('marca_dagua');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['cor_primaria', 'cor_secundaria', 'marca_dagua', 'texto_marca_dagua']);
        });
    }
};
