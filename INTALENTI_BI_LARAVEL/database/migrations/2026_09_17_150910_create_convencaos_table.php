<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convencoes', function (Blueprint $table) {
            $table->id();
            $table->string('sindicato_nome');
            $table->string('ano_vigencia');
            $table->string('data_base');
            $table->decimal('reajuste_geral_percentual', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convencoes');
    }
};
