<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// ─── Fila de Jobs (compatível com hospedagem compartilhada) ─────────
// Processa jobs pendentes a cada minuto. Para sozinho quando a fila esvazia.
// withoutOverlapping() impede que duas instâncias rodem ao mesmo tempo.
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3')
    ->everyMinute()
    ->withoutOverlapping();

// Agenda o Job do WhatsApp a cada 10 minutos (reprocessa falhadas)
Schedule::job(new \App\Jobs\ProcessarFilaWhatsApp)->everyTenMinutes();

// Agenda o robô de envio diário de E-mail e WhatsApp (Resumo de Pendências)
Schedule::command('notify:daily')->dailyAt('08:00');

// Limpeza automática de notificações com mais de 3 dias (Diariamente às 03:00)
Schedule::command('notificacoes:limpar --dias=3')->dailyAt('03:00');

// Processa agendamentos de avaliações e pesquisas de clima (Diariamente às 00:05)
Schedule::command('avaliacoes:processar-agendamentos')->dailyAt('00:05');
Schedule::command('lembretes:processar')->dailyAt('07:00'); // Roda de manhã
