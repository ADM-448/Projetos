<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Adaptações na tabela tenants
        Schema::table('tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('tenants', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
            if (! Schema::hasColumn('tenants', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
        });

        // 2. Adaptação na tabela users para aceitar superadmin e is_active
        try {
            DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(50) NOT NULL DEFAULT 'sdr'");
        } catch (\Throwable $e) {
            // Caso já seja varchar ou sqlite em teste
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'slug')) {
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('tenants', 'description')) {
                $table->dropColumn('description');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
