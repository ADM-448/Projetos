<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Company;
use App\Models\EmailAccount;
use App\Models\Lead;
use App\Models\Message;
use App\Models\PipelineStage;
use App\Models\User;
use App\Models\WhatsappInstance;
use Carbon\Carbon;

class DashboardMetricsService
{
    /**
     * Calcula as métricas consolidadas e individuais do Dashboard de Produtividade.
     */
    public function getMetrics(int $tenantId, string $period = 'all'): array
    {
        $startDate = match ($period) {
            'today' => Carbon::today(),
            '7days' => Carbon::now()->subDays(7),
            '30days' => Carbon::now()->subDays(30),
            default => null,
        };

        // SDRs do tenant
        $users = User::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->get(['id', 'name', 'email']);

        // Estágios do pipeline
        $stages = PipelineStage::withoutGlobalScopes()
            ->orderBy('position', 'asc')
            ->get();

        // Query base de Atividades com filtro de período
        $activitiesQuery = Activity::withoutGlobalScopes()
            ->where('tenant_id', $tenantId);

        if ($startDate) {
            $activitiesQuery->where('created_at', '>=', $startDate);
        }

        $activities = $activitiesQuery->get();

        // Contas do tenant
        $companies = Company::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->with(['stage', 'contacts'])
            ->get();

        // Mensagens de WhatsApp
        $messagesQuery = Message::withoutGlobalScopes()
            ->where('tenant_id', $tenantId);

        if ($startDate) {
            $messagesQuery->where('created_at', '>=', $startDate);
        }

        $messages = $messagesQuery->get();

        // Contas de e-mail do tenant
        $emailAccounts = EmailAccount::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->get();

        // Contagem de Ligações e Desfechos
        $calls = $activities->where('type', 'call');
        $totalCalls = $calls->count();
        $connectedCalls = $calls->where('call_outcome', 'connected')->count();
        $meetingsFromCalls = $calls->where('call_outcome', 'meeting_scheduled')->count();
        $noAnswerCalls = $calls->where('call_outcome', 'no_answer')->count();
        $voicemailCalls = $calls->where('call_outcome', 'voicemail')->count();
        $gatekeeperCalls = $calls->where('call_outcome', 'gatekeeper')->count();
        $wrongNumberCalls = $calls->where('call_outcome', 'wrong_number')->count();

        // Reuniões Marcadas Totais (via atividade ou campo da empresa)
        $meetingsActivities = $activities->filter(fn ($a) => $a->type === 'meeting' || $a->call_outcome === 'meeting_scheduled')->count();
        $scheduledCompanies = $companies->filter(fn ($c) => $c->meeting_at !== null)->count();
        $totalMeetings = max($meetingsActivities, $scheduledCompanies);

        // Disparos e respostas de WhatsApp
        $outboundMessages = $messages->where('direction', 'outbound')->count();
        $inboundMessages = $messages->where('direction', 'inbound')->count();
        $whatsappResponseRate = $outboundMessages > 0
            ? round(($inboundMessages / $outboundMessages) * 100, 1)
            : 0;

        // E-mails enviados
        $emailsSent = $activities->where('type', 'email')->count();
        if ($emailsSent === 0) {
            $emailsSent = (int) $emailAccounts->sum('sent_today');
        }

        // Total de Contas Abordadas e Respostas Globais
        $totalCompanies = $companies->count();
        $respondedCompanies = $companies->filter(function ($company) {
            $stageName = mb_strtolower((string) $company->stage?->name);
            return str_contains($stageName, 'respondeu') || str_contains($stageName, 'qualificado') || str_contains($stageName, 'reunião');
        })->count();

        $overallResponseRate = $totalCompanies > 0
            ? round(($respondedCompanies / $totalCompanies) * 100, 1)
            : 0;

        $meetingConversionRate = $totalCompanies > 0
            ? round(($totalMeetings / $totalCompanies) * 100, 1)
            : 0;

        // Distribuição de Contas por Estágio
        $stageDistribution = $stages->map(function ($stage) use ($companies) {
            $count = $companies->where('pipeline_stage_id', $stage->id)->count();
            return [
                'id' => $stage->id,
                'name' => $stage->name,
                'color' => $stage->color ?? '#130e30',
                'count' => $count,
            ];
        });

        // Leaderboard / Desempenho Individual por SDR
        $sdrPerformance = $users->map(function ($user) use ($companies, $activities, $totalMeetings) {
            $userCompanies = $companies->where('assigned_user_id', $user->id);
            $userActivities = $activities->where('user_id', $user->id);

            $userCalls = $userActivities->where('type', 'call')->count();
            $userConnected = $userActivities->where('type', 'call')->where('call_outcome', 'connected')->count();
            $userEmails = $userActivities->where('type', 'email')->count();
            $userNotes = $userActivities->where('type', 'note')->count();
            $userMeetings = $userActivities->filter(fn ($a) => $a->type === 'meeting' || $a->call_outcome === 'meeting_scheduled')->count();

            // Reuniões nas empresas atribuídas ao SDR
            $companiesWithMeeting = $userCompanies->filter(fn ($c) => $c->meeting_at !== null)->count();
            $effectiveMeetings = max($userMeetings, $companiesWithMeeting);

            $assignedCount = $userCompanies->count();
            $conversionRate = $assignedCount > 0
                ? round(($effectiveMeetings / $assignedCount) * 100, 1)
                : 0;

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'assigned_companies' => $assignedCount,
                'calls_count' => $userCalls,
                'calls_connected' => $userConnected,
                'emails_sent' => $userEmails,
                'notes_count' => $userNotes,
                'meetings_scheduled' => $effectiveMeetings,
                'conversion_rate' => $conversionRate,
                'score' => ($effectiveMeetings * 50) + ($userConnected * 10) + ($userCalls * 2) + ($userEmails * 3),
            ];
        })->sortByDesc('score')->values();

        // Se houver contas sem SDR atribuído, cria registro de 'Não atribuído' se tiver atividades
        $unassignedActivities = $activities->whereNull('user_id');
        if ($unassignedActivities->isNotEmpty()) {
            $unassignedCalls = $unassignedActivities->where('type', 'call')->count();
            $unassignedMeetings = $unassignedActivities->filter(fn ($a) => $a->type === 'meeting' || $a->call_outcome === 'meeting_scheduled')->count();
            if ($unassignedCalls > 0 || $unassignedMeetings > 0) {
                $sdrPerformance->push([
                    'id' => 0,
                    'name' => 'Geral / Não atribuído',
                    'email' => 'geral@empresa.com',
                    'assigned_companies' => $companies->whereNull('assigned_user_id')->count(),
                    'calls_count' => $unassignedCalls,
                    'calls_connected' => $unassignedActivities->where('call_outcome', 'connected')->count(),
                    'emails_sent' => $unassignedActivities->where('type', 'email')->count(),
                    'notes_count' => $unassignedActivities->where('type', 'note')->count(),
                    'meetings_scheduled' => $unassignedMeetings,
                    'conversion_rate' => 0,
                    'score' => 0,
                ]);
            }
        }

        return [
            'period' => $period,
            'kpis' => [
                'total_meetings' => $totalMeetings,
                'meeting_conversion_rate' => $meetingConversionRate,
                'total_companies' => $totalCompanies,
                'responded_companies' => $respondedCompanies,
                'response_rate' => $overallResponseRate,
                'total_calls' => $totalCalls,
                'connected_calls' => $connectedCalls,
                'call_connection_rate' => $totalCalls > 0 ? round(($connectedCalls / $totalCalls) * 100, 1) : 0,
                'whatsapp_outbound' => $outboundMessages,
                'whatsapp_inbound' => $inboundMessages,
                'whatsapp_response_rate' => $whatsappResponseRate,
                'emails_sent' => $emailsSent,
                'total_activities' => $activities->count(),
            ],
            'call_outcomes' => [
                'connected' => $connectedCalls,
                'meeting_scheduled' => $meetingsFromCalls,
                'no_answer' => $noAnswerCalls,
                'voicemail' => $voicemailCalls,
                'gatekeeper' => $gatekeeperCalls,
                'wrong_number' => $wrongNumberCalls,
            ],
            'stage_distribution' => $stageDistribution,
            'sdr_leaderboard' => $sdrPerformance,
        ];
    }
}
