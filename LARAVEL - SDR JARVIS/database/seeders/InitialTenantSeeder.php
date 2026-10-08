<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WhatsappInstance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialTenantSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Criação da Empresa / Tenant de Demonstração
        $tenant = Tenant::firstOrCreate(
            ['name' => 'Empresa Alfa SDR'],
            [
                'document' => '12.345.678/0001-90',
                'status' => 'active',
                'settings' => [
                    'timezone' => 'America/Sao_Paulo',
                    'max_instances' => 3,
                ],
            ]
        );

        // 2. Criação do SDR / Utilizador com vínculo ao Tenant
        $user = User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'sdr@empresaalfa.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Operador SDR',
                'password' => Hash::make('password123'),
                'role' => 'sdr',
                'is_active' => true,
            ]
        );

        // 3. Pipeline Padrão de Prospeção Outbound
        $pipeline = Pipeline::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Outbound B2B Ativo'],
            [
                'is_default' => true,
            ]
        );

        // 4. Etapas do Kanban
        $stagesData = [
            ['name' => '1. Base Fria (Importados)', 'position' => 1, 'color' => '#64748b'],
            ['name' => '2. Disparo Agendado',       'position' => 2, 'color' => '#0284c7'],
            ['name' => '3. Respondeu / Conversa',   'position' => 3, 'color' => '#eab308'],
            ['name' => '4. Qualificado / Agendamento', 'position' => 4, 'color' => '#8b5cf6'],
            ['name' => '5. Reunião Marcada',        'position' => 5, 'color' => '#22c55e'],
            ['name' => '6. Desqualificado',         'position' => 6, 'color' => '#ef4444'],
        ];

        $stages = collect();
        foreach ($stagesData as $stage) {
            $stages->push(
                PipelineStage::withoutGlobalScopes()->firstOrCreate([
                    'tenant_id' => $tenant->id,
                    'pipeline_id' => $pipeline->id,
                    'name' => $stage['name'],
                ], [
                    'position' => $stage['position'],
                    'color' => $stage['color'],
                ])
            );
        }

        // 5. Instância demonstrativa do WhatsApp
        WhatsappInstance::withoutGlobalScopes()->updateOrCreate(
            ['instance_name' => 'chip-sdr-01'],
            [
                'tenant_id' => $tenant->id,
                'phone_number' => '5544999990001',
                'status' => 'connected',
                'daily_limit' => 45,
                'sent_today' => 12,
                'last_active_at' => now(),
            ]
        );

        // 6. Leads de teste
        Lead::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'carlos@logisticaexpress.com.br'],
            [
                'tenant_id' => $tenant->id,
                'pipeline_stage_id' => $stages[0]->id ?? 1,
                'assigned_user_id' => $user->id,
                'name' => 'Carlos Silveira',
                'company_name' => 'Logística Express',
                'phone' => '5544998881122',
                'status' => 'open',
                'custom_attributes' => ['setor' => 'Transportes', 'frota' => 35],
            ]
        );

        Lead::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'mariana@duarte.com.br'],
            [
                'tenant_id' => $tenant->id,
                'pipeline_stage_id' => $stages[2]->id ?? ($stages[0]->id ?? 1),
                'assigned_user_id' => $user->id,
                'name' => 'Mariana Duarte',
                'company_name' => 'Duarte Distribuidora',
                'phone' => '5544997773344',
                'status' => 'open',
                'custom_attributes' => ['setor' => 'Atacado', 'faturamento_estimado' => '5M'],
            ]
        );
    }
}
