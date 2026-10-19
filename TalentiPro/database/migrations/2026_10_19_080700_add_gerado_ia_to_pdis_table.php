<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pdis', function (Blueprint $table) {
            $table->boolean('gerado_ia')->default(false)->after('comprovante')->index();
        });

        // Backfill conservador: só marca como IA os PDIs identificados pelo prefixo do Job.
        DB::table('pdis')->where('titulo', 'like', '[Rascunho IA]%')->update(['gerado_ia' => true]);
    }

    public function down(): void
    {
        Schema::table('pdis', function (Blueprint $table) {
            $table->dropIndex(['gerado_ia']);
            $table->dropColumn('gerado_ia');
        });
    }
};
