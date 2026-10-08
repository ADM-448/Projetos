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
        Schema::table('cadence_steps', function (Blueprint $table) {
            $table->string('delay_unit')->default('days')->after('day_offset'); // seconds, minutes, hours, days
            $table->integer('delay_value')->nullable()->after('delay_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cadence_steps', function (Blueprint $table) {
            $table->dropColumn(['delay_unit', 'delay_value']);
        });
    }
};
