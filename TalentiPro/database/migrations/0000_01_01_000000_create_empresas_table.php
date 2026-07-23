<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('cnpj', 18)->unique();
            $table->string('razao_social');
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();
            $table->string('logo')->nullable();
            $table->enum('status', ['ativa', 'suspensa', 'cancelada'])->default('ativa');
            $table->integer('max_usuarios')->default(50);
            $table->date('data_contratacao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
