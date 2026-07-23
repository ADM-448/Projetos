<?php

namespace App\Services;

use App\Models\WhatsappQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $baseUrl;
    private string $apiKey;
    private string $instance;
    private bool   $enabled;

    public function __construct()
    {
        $this->baseUrl  = config('whatsapp.base_url') ?? '';
        $this->apiKey   = config('whatsapp.api_key') ?? '';
        $this->enabled  = (bool) config('whatsapp.enabled', false);

        // Define a empresa padrão com base no usuário autenticado ou ID 1
        $empresaId = 1;
        if (auth()->check() && auth()->user()->empresa_id) {
            $empresaId = auth()->user()->empresa_id;
        }
        $this->setEmpresaId($empresaId);
    }

    /**
     * Define a empresa ativa e atualiza o nome da instância de forma dinâmica.
     */
    public function setEmpresaId(int $empresaId): self
    {
        $prefix = config('whatsapp.instance', 'talentipro');
        $this->instance = "{$prefix}_{$empresaId}";
        return $this;
    }

    /**
     * Garante que a instância exista na Evolution API.
     * Tenta criar se ainda não existir.
     */
    private function garantirInstanciaCriada(): void
    {
        if (!$this->enabled) return;

        try {
            // Tenta verificar se ela existe primeiro
            $response = Http::timeout(10)
                ->withHeaders(['apikey' => $this->apiKey])
                ->get("{$this->baseUrl}/instance/connectionState/{$this->instance}");

            // Se retornar erro ou não encontrar, cria
            if ($response->failed() || empty($response->json('instance.state') ?? $response->json('state'))) {
                Http::timeout(30)
                    ->withHeaders([
                        'apikey'       => $this->apiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->post("{$this->baseUrl}/instance/create", [
                        'instanceName' => $this->instance,
                        'qrcode'       => true,
                    ]);
            }
        } catch (\Exception $e) {
            Log::warning('WhatsApp: erro ao garantir criação de instância', [
                'instancia' => $this->instance,
                'erro'      => $e->getMessage()
            ]);
        }
    }

    /**
     * Envia mensagem WhatsApp.
     * Se falhar, salva na fila para reenvio automático em até 3 tentativas.
     *
     * @param  string      $telefone  Número com ou sem DDI/máscara
     * @param  string      $mensagem  Texto da mensagem
     * @param  int|null    $userId    ID do destinatário (para rastreamento na fila)
     */
    public function enviar(string $telefone, string $mensagem, ?int $userId = null): bool
    {
        if (!$this->enabled) {
            return false;
        }

        // Se passar o userId, define a empresa dele para enviar da instância correta
        if ($userId) {
            $user = \App\Models\User::withoutGlobalScope('empresa')->find($userId);
            if ($user && $user->empresa_id) {
                $this->setEmpresaId($user->empresa_id);
            }
        }

        $numero = $this->normalizarNumero($telefone);

        // 1. Tenta enviar imediatamente
        [$sucesso, $erro] = $this->tentarEnvio($numero, $mensagem);

        if ($sucesso) {
            return true;
        }

        // 2. Falhou → salva na fila para reenvio automático
        WhatsappQueue::create([
            'user_id'        => $userId,
            'numero'         => $numero,
            'mensagem'       => $mensagem,
            'status'         => 'pendente',
            'tentativas'     => 1,
            'max_tentativas' => 3,
            'ultimo_erro'    => $erro,
        ]);

        Log::warning('WhatsApp: mensagem na fila para reenvio', [
            'numero' => $numero,
            'erro'   => $erro,
        ]);

        return false;
    }

    /**
     * Processa a fila de mensagens pendentes.
     * Chamado pelo Job agendado a cada 10 minutos.
     * Retorna [enviadas, falhadas] para logging.
     */
    public function processarFila(): array
    {
        if (!$this->enabled) {
            return [0, 0];
        }

        // Carrega os pendentes com o relacionamento 'user' para sabermos a empresa
        $pendentes = WhatsappQueue::pendentes()->with('user')->get();

        if ($pendentes->isEmpty()) {
            return [0, 0];
        }

        $enviadas = 0;
        $falhadas = 0;

        foreach ($pendentes as $item) {
            // Define o empresaId dinamicamente para o envio
            $empresaId = $item->user ? $item->user->empresa_id : 1;
            $this->setEmpresaId($empresaId);

            [$sucesso, $erro] = $this->tentarEnvio($item->numero, $item->mensagem);

            if ($sucesso) {
                $item->marcarEnviada();
                $enviadas++;
            } else {
                $item->registrarFalha($erro);
                if ($item->fresh()->status === 'falhou') {
                    $falhadas++;
                }
            }

            // Pausa de 1s entre envios para não sobrecarregar a API
            usleep(1_000_000);
        }

        Log::info("WhatsApp Fila: {$enviadas} enviadas, {$falhadas} falharam definitivamente.");

        return [$enviadas, $falhadas];
    }

    /**
     * Verifica se a instância está conectada.
     * Retorna true = online, false = offline/erro.
     */
    public function estaConectado(): bool
    {
        if (!$this->enabled) return false;

        $this->garantirInstanciaCriada();

        try {
            $response = Http::timeout(15)
                ->withHeaders(['apikey' => $this->apiKey])
                ->get("{$this->baseUrl}/instance/connectionState/{$this->instance}");

            $state = $response->json('instance.state') ?? $response->json('state') ?? '';
            return strtolower($state) === 'open';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Retorna dados completos de status para o painel admin.
     */
    public function status(): array
    {
        if (!$this->enabled) {
            return ['status' => 'disabled', 'message' => 'WhatsApp desabilitado no .env'];
        }

        $this->garantirInstanciaCriada();

        try {
            $response = Http::timeout(15)
                ->withHeaders(['apikey' => $this->apiKey])
                ->get("{$this->baseUrl}/instance/connectionState/{$this->instance}");

            return $response->json() ?? ['error' => 'Resposta vazia'];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * QR Code em Base64 para conectar a instância.
     */
    public function qrCode(): ?string
    {
        if (!$this->enabled) return null;

        $this->garantirInstanciaCriada();

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'apikey' => $this->apiKey,
                    'Accept' => 'application/json', // Solicita JSON prioritariamente
                ])
                ->get("{$this->baseUrl}/instance/connect/{$this->instance}");

            // Se a API retornar uma imagem binária diretamente
            $contentType = $response->header('Content-Type') ?? '';
            if (str_contains($contentType, 'image/')) {
                return base64_encode($response->body());
            }

            // Trata como JSON (v1 usa 'base64', v2 usa 'qrcode.base64')
            $code = $response->json('base64') ?? $response->json('qrcode.base64') ?? $response->json('code') ?? null;

            if ($code) {
                // Remove o prefixo se ele estiver presente, pois a view já adiciona "data:image/png;base64,"
                return preg_replace('/^data:image\/\w+;base64,/', '', $code);
            }

            // Fallback: se o body começar com assinatura de PNG (\x89PNG)
            $body = $response->body();
            if (!empty($body) && str_starts_with($body, "\x89PNG")) {
                return base64_encode($body);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('WhatsApp: erro ao obter QR Code', ['exception' => $e->getMessage()]);
            return null;
        }
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    // ─── Internos ──────────────────────────────────────────────────────────────

    /**
     * Executa a chamada HTTP para a Evolution API.
     * Retorna [bool sucesso, string|null erro].
     */
    private function tentarEnvio(string $numero, string $mensagem): array
    {
        $this->garantirInstanciaCriada();

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'apikey'       => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->baseUrl}/message/sendText/{$this->instance}", [
                    'number' => $numero,
                    'textMessage' => [
                        'text' => $mensagem
                    ],
                ]);

            if ($response->successful()) {
                Log::info('WhatsApp: enviado', ['numero' => $numero]);
                return [true, null];
            }

            $erro = "HTTP {$response->status()}: {$response->body()}";
            Log::warning('WhatsApp: falha no envio', ['numero' => $numero, 'erro' => $erro]);
            return [false, $erro];

        } catch (\Exception $e) {
            Log::error('WhatsApp: exceção', ['numero' => $numero, 'exception' => $e->getMessage()]);
            return [false, $e->getMessage()];
        }
    }

    private function normalizarNumero(string $telefone): string
    {
        $numero = preg_replace('/\D/', '', $telefone);
        if (strlen($numero) <= 11) {
            $numero = '55' . $numero;
        }
        return $numero;
    }
}
