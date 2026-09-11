<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disc_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('score_d')->default(0);
            $table->integer('score_i')->default(0);
            $table->integer('score_s')->default(0);
            $table->integer('score_c')->default(0);
            $table->enum('primary_profile', ['DOMINANCE', 'INFLUENCE', 'STEADINESS', 'COMPLIANCE'])->nullable();
            $table->enum('secondary_profile', ['DOMINANCE', 'INFLUENCE', 'STEADINESS', 'COMPLIANCE'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disc_assessments');
    }
};