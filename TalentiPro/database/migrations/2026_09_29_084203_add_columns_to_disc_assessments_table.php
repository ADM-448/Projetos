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
        Schema::table('disc_assessments', function (Blueprint $table) {
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->change();
            $table->string('respondent_name')->nullable();
            $table->string('respondent_email')->nullable();
            $table->string('origin')->default('internal'); // internal, external_form
            $table->uuid('public_token')->nullable()->unique();
            $table->json('metadata')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('disc_assessments', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
            $table->dropColumn([
                'empresa_id', 'respondent_name', 'respondent_email', 
                'origin', 'public_token', 'metadata'
            ]);
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
