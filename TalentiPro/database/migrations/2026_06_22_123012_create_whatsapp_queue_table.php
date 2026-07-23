<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fila de mensagens WhatsApp com suporte a reenvio automático.
     * Mensagens que falharam (conexão caída, API fora do ar) ficam
     * aqui e são reenviadas automaticamente a cada 10 minutos.
     */
    public function up(): void
    {
        Schema::create('whatsapp_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // destinatário
            $table->string('numero', 20);                      // +5511999999999
            $table->text('mensagem');
            $table->string('status', 20)->default('pendente'); // pendente | enviada | falhou
            $table->unsignedTinyInteger('tentativas')->default(0);
            $table->unsignedTinyInteger('max_tentativas')->default(3);
            $table->text('ultimo_erro')->nullable();
            $table->timestamp('enviada_em')->nullable();
            $table->timestamps();

            $table->index(['status', 'tentativas']);
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_queue');
    }
};
