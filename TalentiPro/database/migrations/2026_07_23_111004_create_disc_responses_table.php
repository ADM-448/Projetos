<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disc_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disc_assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('disc_option_id')->constrained()->cascadeOnDelete();
            $table->integer('score');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disc_responses');
    }
};