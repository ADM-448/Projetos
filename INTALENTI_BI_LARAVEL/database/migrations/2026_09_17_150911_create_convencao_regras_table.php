<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convencao_regras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convencao_id')->constrained('convencoes')->onDelete('cascade');
            $table->enum('tipo_regra', ['piso_cargo', 'beneficio_fixo', 'adicional_percentual']);
            $table->string('descricao');
            $table->decimal('valor', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convencao_regras');
    }
};
