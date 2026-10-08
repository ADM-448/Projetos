<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TasksSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::withoutGlobalScopes()->with('contacts')->take(5)->get();
        $user = User::withoutGlobalScopes()->first();

        if ($companies->isEmpty()) {
            return;
        }

        $sampleTasks = [
            [
                'title' => 'Ligar para confirmar presença na demonstração',
                'type' => 'call',
                'priority' => 'high',
                'due_at' => Carbon::today()->setHour(14)->setMinute(30),
                'status' => 'pending',
                'description' => 'Verificar se o Diretor de TI recebeu o convite do Google Meet e alinhar as expectativas técnicas.',
            ],
            [
                'title' => 'Enviar case de sucesso do setor de tecnologia',
                'type' => 'email',
                'priority' => 'medium',
                'due_at' => Carbon::today()->subDays(1)->setHour(11)->setMinute(0),
                'status' => 'pending',
                'description' => 'Lead solicitou estudo de ROI de implantação em empresa similar.',
            ],
            [
                'title' => 'Follow-up via WhatsApp sobre proposta comercial',
                'type' => 'whatsapp',
                'priority' => 'high',
                'due_at' => Carbon::tomorrow()->setHour(10)->setMinute(0),
                'status' => 'pending',
                'description' => 'Cobrar retorno do CFO sobre aprovação orçamentária do projeto.',
            ],
            [
                'title' => 'Pesquisar decisores adicionais no LinkedIn',
                'type' => 'followup',
                'priority' => 'low',
                'due_at' => Carbon::now()->addDays(3)->setHour(16)->setMinute(0),
                'status' => 'completed',
                'description' => 'Mapear coordenador de compras e gerente de expansão.',
            ],
        ];

        foreach ($companies as $index => $company) {
            $taskData = $sampleTasks[$index % count($sampleTasks)];
            $lead = $company->contacts->first();

            Task::withoutGlobalScopes()->create([
                'tenant_id' => $company->tenant_id,
                'company_id' => $company->id,
                'lead_id' => $lead?->id,
                'user_id' => $company->assigned_user_id ?? $user?->id,
                'title' => $taskData['title'],
                'description' => $taskData['description'],
                'type' => $taskData['type'],
                'priority' => $taskData['priority'],
                'due_at' => $taskData['due_at'],
                'status' => $taskData['status'],
                'completed_at' => $taskData['status'] === 'completed' ? Carbon::now()->subHours(2) : null,
            ]);
        }
    }
}
