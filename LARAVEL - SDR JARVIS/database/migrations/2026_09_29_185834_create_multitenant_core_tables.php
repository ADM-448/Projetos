<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela de Tenants (Organizações / Clientes)
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('document')->nullable()->index(); // CNPJ ou NIF
            $table->enum('status', ['active', 'suspended', 'cancelled'])->default('active');
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        // 2. Extensão / Adaptação da Tabela de Usuários
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->after('id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->enum('role', ['admin', 'manager', 'sdr'])->default('sdr')->after('email');
            
            $table->index(['tenant_id', 'role']);
        });

        // 3. Pipelines de Vendas
        Schema::create('pipelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'is_default']);
        });

        // 4. Etapas do Kanban
        Schema::create('pipeline_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('pipeline_id')->constrained('pipelines')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('position')->default(0);
            $table->string('color', 20)->default('#64748b');
            $table->timestamps();

            $table->index(['pipeline_id', 'position']);
            $table->index(['tenant_id', 'pipeline_id']);
        });

        // 5. Instâncias de Disparo do WhatsApp
        Schema::create('whatsapp_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('instance_name')->unique();
            $table->string('phone_number')->nullable();
            $table->enum('status', ['disconnected', 'connecting', 'connected', 'banned'])->default('disconnected');
            $table->unsignedSmallInteger('daily_limit')->default(50);
            $table->unsignedSmallInteger('sent_today')->default(0);
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        // 6. Tabela de Leads
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('pipeline_stage_id')->nullable()->constrained('pipeline_stages')->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->enum('status', ['open', 'won', 'lost', 'unqualified'])->default('open');
            $table->json('custom_attributes')->nullable();
            $table->timestamps();

            // Índices para buscas rápidas pelo Kanban e webhook do WhatsApp
            $table->index(['tenant_id', 'phone']);
            $table->index(['tenant_id', 'pipeline_stage_id', 'status']);
        });

        // 7. Histórico de Mensagens
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('whatsapp_instance_id')->nullable()->constrained('whatsapp_instances')->nullOnDelete();
            $table->enum('direction', ['inbound', 'outbound']);
            $table->text('body');
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed'])->default('pending');
            $table->string('external_id')->nullable()->index(); // ID fornecido pelo Baileys/Evolution API
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'lead_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('whatsapp_instances');
        Schema::dropIfExists('pipeline_stages');
        Schema::dropIfExists('pipelines');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn(['tenant_id', 'role']);
        });

        Schema::dropIfExists('tenants');
    }
};