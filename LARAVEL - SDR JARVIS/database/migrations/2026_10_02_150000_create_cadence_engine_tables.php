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
        // 1. Cadences (Sequências / Réguas de Prospecção)
        Schema::create('cadences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('target_segment')->nullable();
            $table->string('color')->default('#130e30');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // 2. Cadence Steps (Passos da régua)
        Schema::create('cadence_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cadence_id')->constrained('cadences')->cascadeOnDelete();
            $table->integer('step_number')->default(1);
            $table->integer('day_offset')->default(0); // Dias de intervalo
            $table->string('task_type')->default('whatsapp'); // whatsapp, email, call, meeting, followup, research
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->text('template_content')->nullable();
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->timestamps();
        });

        // 3. Company Cadences (Matrícula da Conta na Cadência)
        Schema::create('company_cadences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('cadence_id')->constrained('cadences')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('current_step_number')->default(1);
            $table->string('status')->default('active')->index(); // active, paused, completed, stopped
            $table->dateTime('started_at');
            $table->dateTime('next_due_at')->nullable()->index();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // 4. Vincular tarefas à cadência
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('company_cadence_id')->nullable()->after('lead_id')->constrained('company_cadences')->nullOnDelete();
            $table->foreignId('cadence_id')->nullable()->after('company_cadence_id')->constrained('cadences')->nullOnDelete();
            $table->foreignId('cadence_step_id')->nullable()->after('cadence_id')->constrained('cadence_steps')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['cadence_step_id']);
            $table->dropForeign(['cadence_id']);
            $table->dropForeign(['company_cadence_id']);
            $table->dropColumn(['company_cadence_id', 'cadence_id', 'cadence_step_id']);
        });

        Schema::dropIfExists('company_cadences');
        Schema::dropIfExists('cadence_steps');
        Schema::dropIfExists('cadences');
    }
};
