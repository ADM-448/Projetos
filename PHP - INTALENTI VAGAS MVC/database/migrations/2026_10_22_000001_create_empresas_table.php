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
            $table->unsignedBigInteger('portal_id')->nullable()->index();
            $table->string('nome_empresa');
            $table->string('nome_fantasia')->nullable();
            $table->string('cnpj', 18)->nullable()->index();
            $table->enum('tipo', ['empresa', 'prefeitura', 'rh'])->default('empresa')->index();
            $table->string('dominio')->nullable()->index();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('logo_path')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('empresa_usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['admin', 'recrutador'])->default('recrutador');
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['empresa_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_usuarios');
        Schema::dropIfExists('empresas');
    }
};
