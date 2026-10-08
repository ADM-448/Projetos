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
        Schema::create('email_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->index();
            $table->string('name')->nullable();
            $table->string('from_name')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('provider')->default('google'); // google, smtp, etc.
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->enum('status', ['connected', 'disconnected', 'error'])->default('connected');
            $table->unsignedInteger('daily_limit')->default(100);
            $table->unsignedInteger('sent_today')->default(0);
            $table->timestamp('last_active_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_accounts');
    }
};
