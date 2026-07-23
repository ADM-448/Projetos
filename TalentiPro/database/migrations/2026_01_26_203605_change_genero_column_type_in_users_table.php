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
        Schema::table('users', function (Blueprint $table) {
            $table->string('genero', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Reverter para enum pode ser complicado se já existirem valores diferentes,
            // mas aqui é apenas para constar.
            // $table->enum('genero', ['M','F','O'])->nullable()->change(); 
            // Melhor deixar como string ou text para evitar perda de dados no rollback se houver dados incompatíveis.
            $table->string('genero', 20)->nullable()->change();
        });
    }
};
