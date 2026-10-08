<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Message;
use App\Models\PipelineStage;
use App\Models\WhatsappInstance;
use Illuminate\Support\Facades\Log;

class WhatsappWebhookService
{
    /**
     * Processa a carga útil (payload) do webhook da Evolution API.
     */
    public function handle(array $payload, ?string $urlInstanceName = null): array
    {
        $event = strtolower((string) ($payload['event'] ?? ''));
        $instanceName = $payload['instance'] ?? $urlInstanceName ?? null;

        Log::info("[Evolution Webhook] Evento recebido: {$event}", [
            'instance' => $instanceName,
            'event' => $event,
        ]);

        if (! $instanceName) {
            return ['status' => 'ignored', 'reason' => 'instance_not_provided'];
        }

        $instance = WhatsappInstance::withoutGlobalScopes()
            ->where('instance_name', $instanceName)
            ->first();

        if (! $instance) {
            Log::warning("[Evolution Webhook] Instância não cadastrada: {$instanceName}");
            return ['status' => 'ignored', 'reason' => 'instance_not_found'];
        }

        return match ($event) {
            'messages.upsert', 'messages_upsert', 'send_message' => $this->handleMessageUpsert($instance, $payload['data'] ?? []),
            'messages.update', 'messages_update' => $this->handleMessageUpdate($instance, $payload['data'] ?? []),
            'connection.update', 'connection_update' => $this->handleConnectionUpdate($instance, $payload['data'] ?? []),
            default => ['status' => 'unhandled_event', 'event' => $event],
        };
    }

    /**
     * Trata o recebimento ou envio de novas mensagens.
     */
    protected function handleMessageUpsert(WhatsappInstance $instance, mixed $data): array
    {
        $items = isset($data[0]) ? $data : [$data];
        $processed = 0;

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $key = $item['key'] ?? [];
            $fromMe = (bool) ($key['fromMe'] ?? false);
            $remoteJid = (string) ($key['remoteJid'] ?? '');
            $externalId = (string) ($key['id'] ?? '');

            // Ignorar mensagens de grupos ou de status do WhatsApp
            if (str_contains($remoteJid, '@g.us') || str_contains($remoteJid, 'status@broadcast')) {
                continue;
            }

            // Extrair telefone limpo (ex: 5544998881122@s.whatsapp.net -> 5544998881122)
            $phone = preg_replace('/[^0-9]/', '', explode('@', $remoteJid)[0] ?? '');
            if (empty($phone)) {
                continue;
            }

            $body = $this->extractMessageText($item['message'] ?? []);
            if (empty($body)) {
                continue;
            }

            // Buscar lead do mesmo tenant pelo telefone
            $lead = $this->findLeadByPhone($instance->tenant_id, $phone);

            $isNewLead = false;
            if (! $lead) {
                // Se o lead ainda não existe e mandou mensagem (inbound), cria um lead novo na etapa inicial
                if (! $fromMe) {
                    $initialStage = PipelineStage::withoutGlobalScopes()
                        ->where('tenant_id', $instance->tenant_id)
                        ->orderBy('position', 'asc')
                        ->first();

                    $lead = Lead::withoutGlobalScopes()->create([
                        'tenant_id' => $instance->tenant_id,
                        'pipeline_stage_id' => $initialStage?->id,
                        'name' => $item['pushName'] ?? 'Lead WhatsApp ' . substr($phone, -4),
                        'phone' => $phone,
                        'status' => 'open',
                    ]);

                    // Pausa a IA para novos contatos passivos desconhecidos (evita responder contatos pessoais/familiares)
                    $lead->pauseAiAutonomous('Contato passivo / Fora do pipeline B2B');
                    $isNewLead = true;
                } else {
                    continue;
                }
            }

            // Evitar duplicidade de mensagem pelo external_id
            $existingMessage = Message::withoutGlobalScopes()
                ->where('external_id', $externalId)
                ->first();

            if ($existingMessage) {
                continue;
            }

            // Gravar a mensagem
            Message::withoutGlobalScopes()->create([
                'tenant_id' => $instance->tenant_id,
                'lead_id' => $lead->id,
                'whatsapp_instance_id' => $instance->id,
                'direction' => $fromMe ? 'outbound' : 'inbound',
                'body' => $body,
                'status' => $fromMe ? 'sent' : 'read',
                'external_id' => $externalId,
                'sent_at' => now(),
            ]);

            // Se for resposta do Lead (inbound), acionar detector de resposta de cadência e motor de IA autônoma
            // TRAVA DE SEGURANÇA: A IA só atende leads que já receberam abordagem outbound prévia da Cadência
            // ou que pertençam a uma Empresa cadastrada no CRM (nunca contatos pessoais ou avulsos)
            if (! $fromMe && ! $isNewLead) {
                $hasPriorOutboundOrCompany = $lead->company_id || Message::withoutGlobalScopes()
                    ->where('lead_id', $lead->id)
                    ->where('direction', 'outbound')
                    ->exists();

                if ($hasPriorOutboundOrCompany && ! $lead->isAiAutonomousPaused()) {
                    app(\App\Services\CadenceExecutionService::class)->handleLeadReply($lead, 'whatsapp', $body);

                    try {
                        // Delega o processamento pesado de IA (e os "sleeps" de digitação) para a Fila em Background
                        \App\Jobs\ProcessAiResponseJob::dispatch($lead, $instance, $body);
                    } catch (\Throwable $aiErr) {
                        Log::error('[Autonomous AI] Erro ao engatilhar job da IA: ' . $aiErr->getMessage(), [
                            'lead_id' => $lead->id,
                            'instance' => $instance->instance_name,
                        ]);
                    }
                }
            }

            $processed++;
        }

        return ['status' => 'success', 'messages_processed' => $processed];
    }

    /**
     * Atualiza o status de leitura/entrega da mensagem (ex: READ, DELIVERY_ACK).
     */
    protected function handleMessageUpdate(WhatsappInstance $instance, mixed $data): array
    {
        $items = isset($data[0]) ? $data : [$data];
        $updatedCount = 0;

        foreach ($items as $item) {
            $externalId = $item['key']['id'] ?? null;
            $rawStatus = strtoupper((string) ($item['update']['status'] ?? ''));

            if (! $externalId || ! $rawStatus) {
                continue;
            }

            $mappedStatus = match ($rawStatus) {
                'READ', 'PLAYED' => 'read',
                'DELIVERY_ACK', 'DELIVERED' => 'delivered',
                'ERROR' => 'failed',
                default => null,
            };

            if ($mappedStatus) {
                $updated = Message::withoutGlobalScopes()
                    ->where('external_id', $externalId)
                    ->update(['status' => $mappedStatus]);

                if ($updated) {
                    $updatedCount++;
                }
            }
        }

        return ['status' => 'success', 'updated_count' => $updatedCount];
    }

    /**
     * Atualiza o status da conexão da instância (open, close, etc).
     */
    protected function handleConnectionUpdate(WhatsappInstance $instance, array $data): array
    {
        $state = strtolower((string) ($data['state'] ?? ''));

        $status = match ($state) {
            'open' => 'connected',
            'connecting' => 'connecting',
            'close', 'closed' => 'disconnected',
            default => $instance->status,
        };

        $instance->update([
            'status' => $status,
            'last_active_at' => now(),
        ]);

        return ['status' => 'success', 'instance_status' => $status];
    }

    /**
     * Localiza o lead pelo telefone no tenant.
     */
    protected function findLeadByPhone(int $tenantId, string $phone): ?Lead
    {
        // Busca exata ou pelos últimos 8 dígitos (para mitigar diferenças de 9º dígito / DDI)
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        $last8 = substr($cleanPhone, -8);

        return Lead::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where(function ($query) use ($cleanPhone, $last8) {
                $query->where('phone', $cleanPhone)
                    ->orWhere('phone', 'like', "%{$last8}");
            })
            ->first();
    }

    /**
     * Avança o Lead para a etapa de 'Respondeu / Conversa' se ele estava em etapa inicial.
     */
    protected function advanceLeadOnReply(Lead $lead): void
    {
        // Buscar a etapa "Respondeu" ou a 3ª etapa do pipeline
        $responseStage = PipelineStage::withoutGlobalScopes()
            ->where('tenant_id', $lead->tenant_id)
            ->where(function ($query) {
                $query->where('name', 'like', '%Respondeu%')
                    ->orWhere('name', 'like', '%Conversa%')
                    ->orWhere('position', 3);
            })
            ->first();

        if ($responseStage) {
            if ($lead->pipeline_stage_id !== $responseStage->id) {
                $lead->update([
                    'pipeline_stage_id' => $responseStage->id,
                ]);
            }

            // Atualiza também a Empresa no Kanban
            if ($lead->company_id) {
                $lead->company()->update([
                    'pipeline_stage_id' => $responseStage->id,
                ]);
            }

            Log::info("[Evolution Webhook] Lead e Empresa avançados de etapa após resposta", [
                'lead_id' => $lead->id,
                'company_id' => $lead->company_id,
                'stage_id' => $responseStage->id,
                'stage_name' => $responseStage->name,
            ]);
        }
    }

    /**
     * Extrai o conteúdo em texto de diferentes formatos de mensagens do WhatsApp.
     */
    protected function extractMessageText(array $message): string
    {
        return $message['conversation']
            ?? $message['extendedTextMessage']['text']
            ?? $message['imageMessage']['caption']
            ?? $message['videoMessage']['caption']
            ?? $message['documentMessage']['caption']
            ?? '';
    }
}
