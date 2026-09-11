<?php

namespace App\Jobs;

use App\Models\WhatsappQueue;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $whatsappQueueId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $whatsappQueueId)
    {
        $this->whatsappQueueId = $whatsappQueueId;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $wpp): void
    {
        $item = WhatsappQueue::with('user')->find($this->whatsappQueueId);

        // Se não encontrar ou já tiver sido tratada, encerra o job
        if (!$item || $item->status !== 'pendente') {
            return;
        }

        // Define a empresa ativa para este disparo
        $empresaId = $item->user ? $item->user->empresa_id : 1;
        $wpp->setEmpresaId($empresaId);

        // Manda o serviço processar e disparar
        $wpp->processarItem($item);
    }
}
