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
        Schema::create('empresas', function (Blueprint $table) {
        $table->id();
        $table->string('nome');              // Ex: TechSolution Ltda
        $table->string('cnpj')->unique();    // Ex: 12.345.678/0001-90
        $table->string('razao_social')->nullable();
        $table->string('email')->nullable();
        $table->string('telefone')->nullable();
        $table->string('logo')->nullable();    // path/to/logo.png

        // status tenant
        $table->enum('status', ['ativa', 'suspensa', 'cancelada'])->default('ativa');

        // Limitação de usuários (SaaS)
        $table->integer('max_usuarios')->default(50);

        $table->date('data_contratacao')->nullable();
        $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
