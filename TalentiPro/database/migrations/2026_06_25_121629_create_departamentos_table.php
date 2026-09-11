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
        Schema::create('departamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('descricao')->nullable();

            // FK Empresa com deleção cascata
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();

            // FK Gestor apontando para users (se deletar o user, define gestor_id como null)
            $table->foreignId('gestor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departamentos');
    }
};
