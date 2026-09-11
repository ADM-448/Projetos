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
        Schema::create('whatsapp_queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('numero');
            $table->text('mensagem');
            $table->string('status')->default('pendente'); // pendente, enviada, falhou
            $table->integer('tentativas')->default(0);
            $table->integer('max_tentativas')->default(3);
            $table->text('ultimo_erro')->nullable();
            $table->timestamp('enviada_em')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_queues');
    }
};
