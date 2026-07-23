<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\ClimateSurveyOpenedMail;
use App\Models\ClimateSurvey;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Job individual de notificação por pesquisa de clima.
 * Disparado em lote pelo OpenClimateSurveyAction via Bus::batch().
 * Cada job processa 1 usuário: e-mail + WhatsApp (se disponível).
 */
class SendClimateSurveyNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    /** Tentativas antes de desistir */
    public int $tries = 3;

    /** Timeout em segundos */
    public int $timeout = 30;

    public function __construct(
        public readonly ClimateSurvey $survey,
        public readonly User $user,
    ) {}

    public function handle(WhatsAppService $whatsAppService): void
    {
        // 1. E-mail para todos (obrigatório)
        Mail::to($this->user->email)
            ->send(new ClimateSurveyOpenedMail($this->survey, $this->user));

        // 2. WhatsApp apenas para quem tem telefone e o serviço está habilitado
        if ($this->user->telefone && config('services.whatsapp.enabled', false)) {
            $mensagem = "📊 *Pesquisa de Clima:* {$this->survey->titulo}\n\n"
                      . "Olá, {$this->user->name}! Sua opinião é muito importante.\n"
                      . "Acesse o TalentiPro e responda anonimamente:\n"
                      . url("/clima/{$this->survey->id}");

            $whatsAppService->enviar($this->user->telefone, $mensagem, $this->user->id);
        }
    }
}
