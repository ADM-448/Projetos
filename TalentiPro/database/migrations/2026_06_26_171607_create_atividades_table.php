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
        Schema::create('atividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Usuário alvo da notificação
            $table->string('icone')->default('fa-solid fa-bell'); // Ícone FontAwesome
            $table->string('cor')->default('#6a4c7c'); // Cor de destaque
            $table->string('titulo');
            $table->text('descricao');
            $table->string('url')->nullable(); // Link se existir ação
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atividades');
    }
};
