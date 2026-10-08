<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Company;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class SdrTeamSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        if (! $tenant) {
            return;
        }

        $sdr1 = User::firstOrCreate(
            ['email' => 'mariana.sdr@jarvis.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Mariana Duarte',
                'password' => bcrypt('password123'),
            ]
        );

        $sdr2 = User::firstOrCreate(
            ['email' => 'lucas.sdr@jarvis.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Lucas Mendes',
                'password' => bcrypt('password123'),
            ]
        );

        // Atribui algumas contas e atividades de demonstração
        $companies = Company::withoutGlobalScopes()->where('tenant_id', $tenant->id)->get();
        if ($companies->count() >= 2) {
            $companies[0]->update(['assigned_user_id' => $sdr1->id]);
            $companies[1]->update(['assigned_user_id' => $sdr2->id]);

            // Cria atividades para o ranking ficar dinâmico
            Activity::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'company_id' => $companies[0]->id,
                    'user_id' => $sdr1->id,
                    'type' => 'call',
                    'call_outcome' => 'meeting_scheduled',
                ],
                [
                    'title' => 'Reunião Agendada com Diretoria',
                    'description' => 'Apresentação comercial marcada para terça-feira às 14h com o decisor.',
                ]
            );

            Activity::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'company_id' => $companies[1]->id,
                    'user_id' => $sdr2->id,
                    'type' => 'call',
                    'call_outcome' => 'connected',
                ],
                [
                    'title' => 'Ligação de Qualificação',
                    'description' => 'Conversado com compras, enviada proposta técnica para análise.',
                ]
            );

            Activity::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'company_id' => $companies[0]->id,
                    'user_id' => $sdr1->id,
                    'type' => 'email',
                ],
                [
                    'title' => '✉️ E-mail: Apresentação SDR JARVIS',
                    'description' => 'Enviado e-mail de introdução com cases do setor.',
                ]
            );
        }
    }
}
