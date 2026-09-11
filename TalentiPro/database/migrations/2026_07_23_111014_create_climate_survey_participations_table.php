<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('climate_survey_participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('climate_survey_id')->constrained('climate_surveys')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pendente', 'respondido'])->default('pendente');
            $table->timestamp('respondido_em')->nullable();
            $table->timestamps();

            $table->unique(['climate_survey_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('climate_survey_participations');
    }
};
