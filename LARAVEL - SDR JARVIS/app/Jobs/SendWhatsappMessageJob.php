<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Models\Message;
use App\Models\WhatsappInstance;
use App\Services\SpintaxService;
use App\Services\WhatsappClientService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsappMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 30;

    protected Lead $lead;
    protected WhatsappInstance $instance;
    protected string $templateText;

    /**
     * Create a new job instance.
     */
    public function __construct(Lead $lead, WhatsappInstance $instance, string $templateText)
    {
        $this->lead = $lead;
        $this->instance = $instance;
        $this->templateText = $templateText;
    }

    /**
     * Execute the job.
     */
    public function handle(SpintaxService $spintaxService, WhatsappClientService $whatsappClient): void
    {
        try {
            // 1. Rate limiting e trava diária
            if ($this->instance->sent_today >= $this->instance->daily_limit) {
                Log::warning("Limite diário de envio atingido para a instância", [
                    'instance_id' => $this->instance->id,
                    'sent_today' => $this->instance->sent_today,
                    'daily_limit' => $this->instance->daily_limit
                ]);
                throw new Exception("Limite diário de disparos atingido para a instância {$this->instance->id}");
            }

            // 2. Resolver o texto final
            $replacements = [
                'nome' => $this->lead->name ?? '',
                'empresa' => $this->lead->company_name ?? $this->lead->company ?? '',
            ];
            
            $finalText = $spintaxService->process($this->templateText, $replacements);

            // 3. Formatar telefone do lead
            $phone = preg_replace('/[^0-9]/', '', $this->lead->phone ?? '');
            if (strlen($phone) >= 10 && strlen($phone) <= 11 && !str_starts_with($phone, '55')) {
                $phone = '55' . $phone;
            }

            $isMock = (bool) config('services.whatsapp.mock', env('WHATSAPP_MOCK', true));

            // 4. Simulação de presença humana (somente se não for mock)
            if (! $isMock && !empty($phone)) {
                $whatsappClient->sendPresence($this->instance, $phone, 'composing');
                // Reduzido para 1 segundo em disparos de cadência para não atrasar a fila
                sleep(1);
                $whatsappClient->sendPresence($this->instance, $phone, 'paused');
            }

            // 5. Envio HTTP (com tratamento defensivo)
            $externalId = null;
            try {
                $response = $whatsappClient->sendTextMessage($this->instance, $phone, $finalText);
                $externalId = $response['key']['id'] ?? $response['messageId'] ?? null;
            } catch (\Throwable $apiEx) {
                Log::warning("Falha na chamada externa da API de WhatsApp, gravando mensagem localmente.", [
                    'error' => $apiEx->getMessage()
                ]);
            }

            // 5. Persistir a mensagem no histórico do Lead
            Message::create([
                'tenant_id' => $this->lead->tenant_id,
                'lead_id' => $this->lead->id,
                'whatsapp_instance_id' => $this->instance->id,
                'body' => $finalText,
                'direction' => 'outbound',
                'status' => 'sent',
                'external_id' => $externalId ?? ('MSG_' . strtoupper(bin2hex(random_bytes(6)))),
                'sent_at' => now(),
            ]);

            // 6. Incrementar contador e atualizar timestamp
            $this->instance->increment('sent_today');
            $this->instance->touch('last_active_at');

        } catch (Exception $e) {
            Log::error("Erro no SendWhatsappMessageJob", [
                'lead_id' => $this->lead->id,
                'instance_id' => $this->instance->id,
                'error' => $e->getMessage(),
            ]);
            
            // Re-throw para a fila lidar com os retries
            throw $e;
        }
    }
}
