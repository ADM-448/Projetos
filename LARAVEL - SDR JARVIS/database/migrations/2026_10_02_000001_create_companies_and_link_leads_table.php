<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Criação da Tabela de Empresas (Accounts / Contas)
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('pipeline_stage_id')->nullable()->constrained('pipeline_stages')->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('document', 30)->nullable(); // CNPJ
            $table->string('segment', 100)->nullable(); // Setor / Ramo
            $table->enum('status', ['open', 'won', 'lost', 'unqualified'])->default('open');
            $table->json('custom_attributes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'pipeline_stage_id', 'status']);
        });

        // 2. Adicionar vínculo na tabela de leads (agora atuando como Contatos/Decisores da Empresa)
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('tenant_id')->constrained('companies')->nullOnDelete();
            $table->string('job_title', 100)->nullable()->after('name'); // Cargo do decisor (ex: CEO, Diretor, Gerente)
            $table->boolean('is_primary')->default(false)->after('status');
        });

        // 3. Migração de dados existentes: Agrupa leads existentes por empresa
        $existingLeads = DB::table('leads')->get();
        $companiesCache = [];

        foreach ($existingLeads as $lead) {
            $companyName = ! empty($lead->company_name) ? trim($lead->company_name) : 'Empresa de ' . ($lead->name ?: 'Lead #' . $lead->id);
            $cacheKey = $lead->tenant_id . '_' . mb_strtolower($companyName);

            if (! isset($companiesCache[$cacheKey])) {
                $companyId = DB::table('companies')->insertGetId([
                    'tenant_id' => $lead->tenant_id,
                    'pipeline_stage_id' => $lead->pipeline_stage_id,
                    'assigned_user_id' => $lead->assigned_user_id,
                    'name' => $companyName,
                    'status' => $lead->status ?? 'open',
                    'custom_attributes' => $lead->custom_attributes,
                    'created_at' => $lead->created_at ?? now(),
                    'updated_at' => $lead->updated_at ?? now(),
                ]);

                $companiesCache[$cacheKey] = $companyId;
            }

            DB::table('leads')->where('id', $lead->id)->update([
                'company_id' => $companiesCache[$cacheKey],
                'is_primary' => true,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id', 'job_title', 'is_primary']);
        });

        Schema::dropIfExists('companies');
    }
};
