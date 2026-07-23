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
            // Drop existing global unique constraints
            // We need to use array syntax for dropUnique when key name is standard
            $table->dropUnique(['email']);
            $table->dropUnique(['cpf']);

            // Create new composite unique constraints (scoped by empresa_id)
            // Allows same email/cpf in DIFFERENT companies
            $table->unique(['email', 'empresa_id']); 
            $table->unique(['cpf', 'empresa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop composite keys
            $table->dropUnique(['email', 'empresa_id']);
            $table->dropUnique(['cpf', 'empresa_id']);

            // Restore global unique constraints
            // WARNING: This might fail if duplicates exist across companies
            $table->unique('email');
            $table->unique('cpf');
        });
    }
};
