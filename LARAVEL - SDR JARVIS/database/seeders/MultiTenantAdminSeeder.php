<?php

namespace Database\Seeders;

use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MultiTenantAdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Garantir Tenant 1 (Matriz)
        $tenant1 = Tenant::updateOrCreate(
            ['slug' => 'jarvis-matriz'],
            [
                'name' => 'SDR Jarvis - Matriz B2B',
                'description' => 'Operação principal de prospecção corporativa e outbound B2B.',
                'status' => 'active',
                'settings' => [
                    'ai_name' => 'JARVIS Executive Agent',
                    'ai_persona' => 'Consultor Estratégico B2B focado em qualificação rápida e agendamento de reuniões executivas',
                    'ai_model' => 'gpt-4o-mini',
                    'ai_prompt' => 'Você é o SDR da operação Matriz do SDR JARVIS. Mantenha tom executivo, objetivo e focado em apresentar os ganhos de ROI.',
                    'timezone' => 'America/Sao_Paulo',
                    'max_instances' => 5,
                ],
            ]
        );

        $p1 = Pipeline::withoutGlobalScopes()->where('tenant_id', $tenant1->id)->first();
        if (! $p1) {
            $p1 = Pipeline::create([
                'tenant_id' => $tenant1->id,
                'name' => 'Pipeline Matriz B2B',
                'is_default' => true,
            ]);

            $stages = [
                ['name' => 'Base Fria / Descoberta', 'position' => 1, 'color' => '#5f5c6e'],
                ['name' => '1º Toque WhatsApp', 'position' => 2, 'color' => '#3b82f6'],
                ['name' => 'Em Conversa / Qualificação', 'position' => 3, 'color' => '#8b5cf6'],
                ['name' => 'Reunião Agendada', 'position' => 4, 'color' => '#ffe228'],
                ['name' => 'Fechado / Ganho', 'position' => 5, 'color' => '#10b981'],
            ];
            foreach ($stages as $s) {
                PipelineStage::create([
                    'tenant_id' => $tenant1->id,
                    'pipeline_id' => $p1->id,
                    'name' => $s['name'],
                    'position' => $s['position'],
                    'color' => $s['color'],
                ]);
            }
        }

        // 2. Garantir Tenant 2 (Growth Scale)
        $tenant2 = Tenant::updateOrCreate(
            ['slug' => 'growth-scale'],
            [
                'name' => 'Growth Scale - Operação 2',
                'description' => 'CRM dedicado para prospecção no setor de Tecnologia e Serviços.',
                'status' => 'active',
                'settings' => [
                    'ai_name' => 'Growth Bot SDR',
                    'ai_persona' => 'Especialista em soluções de tecnologia, SaaS e aceleração comercial',
                    'ai_model' => 'gpt-4o-mini',
                    'ai_prompt' => 'Você atua na operação de Growth. Aborde os leads com tom consultivo e amigável para entender suas ferramentas atuais.',
                    'timezone' => 'America/Sao_Paulo',
                    'max_instances' => 3,
                ],
            ]
        );

        $p2 = Pipeline::withoutGlobalScopes()->where('tenant_id', $tenant2->id)->first();
        if (! $p2) {
            $p2 = Pipeline::create([
                'tenant_id' => $tenant2->id,
                'name' => 'Pipeline Growth B2B',
                'is_default' => true,
            ]);

            $stages = [
                ['name' => 'Base Fria Tech', 'position' => 1, 'color' => '#5f5c6e'],
                ['name' => '1º Toque WhatsApp', 'position' => 2, 'color' => '#3b82f6'],
                ['name' => 'Em Conversa', 'position' => 3, 'color' => '#8b5cf6'],
                ['name' => 'Reunião Agendada', 'position' => 4, 'color' => '#ffe228'],
                ['name' => 'Ganho / Cliente', 'position' => 5, 'color' => '#10b981'],
            ];
            foreach ($stages as $s) {
                PipelineStage::create([
                    'tenant_id' => $tenant2->id,
                    'pipeline_id' => $p2->id,
                    'name' => $s['name'],
                    'position' => $s['position'],
                    'color' => $s['color'],
                ]);
            }
        }

        // 3. Garantir Super Admin Global (Você)
        User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'admin@jarvis.com'],
            [
                'tenant_id' => $tenant1->id,
                'name' => 'Admin Jarvis Global',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_superadmin' => true,
                'is_active' => true,
                'sip_extension' => '1000',
                'sip_secret' => 'jarvis123',
                'sip_webrtc_enabled' => true,
            ]
        );

        // 4. Garantir Gestora Comercial (Manager da base Matriz)
        User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'mariana.sdr@jarvis.com'],
            [
                'tenant_id' => $tenant1->id,
                'name' => 'Mariana Gestora Comercial',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'is_superadmin' => false,
                'is_active' => true,
                'sip_extension' => '1001',
                'sip_secret' => 'jarvis123',
                'sip_webrtc_enabled' => true,
            ]
        );

        // 5. Garantir SDR Operador (Operador da base Matriz)
        User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'lucas.sdr@jarvis.com'],
            [
                'tenant_id' => $tenant1->id,
                'name' => 'Lucas SDR Operador',
                'password' => Hash::make('password'),
                'role' => 'sdr',
                'is_superadmin' => false,
                'is_active' => true,
                'sip_extension' => '1002',
                'sip_secret' => 'jarvis123',
                'sip_webrtc_enabled' => true,
            ]
        );
    }
}
