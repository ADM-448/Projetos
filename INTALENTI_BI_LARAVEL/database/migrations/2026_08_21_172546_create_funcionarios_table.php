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
        Schema::create('funcionarios', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->date('admissao');
            $table->foreignId('cargo_id')->nullable()->constrained('cargos')->nullOnDelete();
            $table->decimal('salario_atual', 10, 2);
            $table->integer('tempo_de_casa_meses')->default(0);
            $table->decimal('salario_mais_proximo_tabela', 10, 2)->nullable();
            $table->integer('nivel_mais_proximo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funcionarios');
    }
};
