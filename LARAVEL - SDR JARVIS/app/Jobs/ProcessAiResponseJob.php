<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Models\WhatsappInstance;
use App\Services\AutonomousAiResponderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAiResponseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 30; // Aguarda 30 segundos em caso de falha antes de tentar novamente

    protected Lead $lead;
    protected WhatsappInstance $instance;
    protected string $inboundText;

    /**
     * Create a new job instance.
     */
    public function __construct(Lead $lead, WhatsappInstance $instance, string $inboundText)
    {
        $this->lead = $lead;
        $this->instance = $instance;
        $this->inboundText = $inboundText;
    }

    /**
     * Execute the job.
     */
    public function handle(AutonomousAiResponderService $aiResponderService): void
    {
        try {
            Log::info("[ProcessAiResponseJob] Iniciando processamento de IA para Lead #{$this->lead->id}");
            
            $aiResponderService->processInbound($this->lead, $this->instance, $this->inboundText);
            
            Log::info("[ProcessAiResponseJob] Processamento finalizado para Lead #{$this->lead->id}");
        } catch (\Throwable $e) {
            Log::error("[ProcessAiResponseJob] Falha ao processar resposta via IA", [
                'lead_id' => $this->lead->id,
                'error' => $e->getMessage()
            ]);
            
            // Lança novamente para que o Laravel retente o Job, se configurado
            throw $e;
        }
    }
}

