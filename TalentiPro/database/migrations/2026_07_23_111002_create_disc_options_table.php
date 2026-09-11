<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disc_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disc_question_id')->constrained()->cascadeOnDelete();
            $table->string('text');
            $table->enum('profile', ['DOMINANCE', 'INFLUENCE', 'STEADINESS', 'COMPLIANCE']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disc_options');
    }
};