<?php

namespace App\Services;

use App\Models\WhatsappInstance;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class WhatsappClientService
{
    protected string $baseUrl;
    protected string $globalApiKey;
    protected bool $isMock;

    public function __construct()
    {
        $this->baseUrl = config('services.whatsapp.api_url', env('WHATSAPP_API_URL', 'http://localhost:8080'));
        $this->globalApiKey = config('services.whatsapp.api_key', env('WHATSAPP_API_KEY', ''));
        $this->isMock = (bool) config('services.whatsapp.mock', env('WHATSAPP_MOCK', true));
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function isMockMode(): bool
    {
        return $this->isMock;
    }

    /**
     * Testa se o gateway da Evolution API está acessível na rede.
     */
    public function pingGateway(?string $customUrl = null, ?string $customKey = null): array
    {
        $url = rtrim($customUrl ?: $this->baseUrl, '/');
        $key = $customKey ?: $this->globalApiKey;

        try {
            // Tenta /instance/fetchInstances
            $response = Http::withHeaders(['apikey' => $key])
                ->timeout(6)
                ->get("{$url}/instance/fetchInstances");

            if ($response->successful()) {
                return [
                    'online' => true,
                    'status_code' => $response->status(),
                    'url' => $url,
                    'message' => 'Evolution API conectada e respondendo normalmente!',
                    'instances_count' => count($response->json() ?? []),
                ];
            }

            // Tenta raiz
            $rootResp = Http::timeout(4)->get($url);
            if ($rootResp->successful()) {
                return [
                    'online' => true,
                    'status_code' => $rootResp->status(),
                    'url' => $url,
                    'message' => 'Servidor acessível, mas a chave de API (apikey) foi rejeitada ou o endpoint retornou status ' . $response->status(),
                ];
            }

            return [
                'online' => false,
                'status_code' => $response->status(),
                'url' => $url,
                'message' => 'Servidor retornou HTTP ' . $response->status() . ': ' . substr($response->body(), 0, 150),
            ];
        } catch (\Throwable $e) {
            return [
                'online' => false,
                'url' => $url,
                'message' => 'Não foi possível conectar ao gateway: ' . $e->getMessage() . '. Verifique se o Docker ou a Evolution API estão rodando nesta porta.',
            ];
        }
    }

    /**
     * Cria uma nova instância na Evolution API.
     */
    public function createInstance(string $instanceName, ?string $webhookUrl = null): array
    {
        if ($this->isMock) {
            return [
                'success' => true,
                'instance' => [
                    'instanceName' => $instanceName,
                    'status' => 'created',
                ],
                'qrcode' => [
                    'base64' => $this->generateMockQrCodeSvg(),
                ],
                'mock' => true,
            ];
        }

        $url = rtrim($this->baseUrl, '/');
        $endpoint = "{$url}/instance/create";

        $payload = [
            'instanceName' => $instanceName,
            'token' => Str::random(32),
            'qrcode' => true,
            'integration' => 'WHATSAPP-BAILEYS',
        ];

        if ($webhookUrl) {
            $payload['webhook'] = [
                'url' => $webhookUrl,
                'byEvents' => false,
                'events' => ['MESSAGES_UPSERT', 'CONNECTION_UPDATE'],
            ];
        }

        $response = Http::withHeaders([
            'apikey' => $this->globalApiKey,
        ])->timeout(15)->post($endpoint, $payload);

        if ($response->successful()) {
            return [
                'success' => true,
                'data' => $response->json(),
            ];
        }

        // Se a instância já existir, prossegue com sucesso
        if ($response->status() === 403 || str_contains($response->body(), 'already exists') || str_contains($response->body(), 'already in use')) {
            return [
                'success' => true,
                'already_exists' => true,
                'message' => 'Instância já existia no gateway.',
            ];
        }

        Log::error("[WhatsappClient] Falha ao criar instância: " . $response->body());
        throw new Exception("Falha ao criar instância na Evolution API: " . $response->body());
    }

    /**
     * Obtém o QR Code ou Código de Pareamento (Pairing Code por número) para conexão do WhatsApp.
     */
    public function connectInstance(string $instanceName, ?string $phoneNumber = null): array
    {
        if ($this->isMock) {
            return [
                'state' => 'connecting',
                'base64' => $this->generateMockQrCodeSvg(),
                'pairingCode' => 'ABCD-1234',
                'mock' => true,
            ];
        }

        $url = rtrim($this->baseUrl, '/');
        $endpoint = "{$url}/instance/connect/{$instanceName}";

        if ($phoneNumber) {
            $clean = preg_replace('/\D/', '', $phoneNumber);
            if (strlen($clean) <= 11) {
                $clean = '55' . $clean;
            }
            $endpoint .= "?number={$clean}";
        }

        $response = Http::withHeaders([
            'apikey' => $this->globalApiKey,
        ])->timeout(15)->get($endpoint);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'state' => 'connecting',
                'base64' => $data['base64'] ?? ($data['qrcode']['base64'] ?? null),
                'code' => $data['code'] ?? ($data['qrcode']['code'] ?? null),
                'pairingCode' => $data['pairingCode'] ?? null,
            ];
        }

        throw new Exception("Erro ao obter QR Code/Código da Evolution API (HTTP {$response->status()}): " . $response->body());
    }

    /**
     * Verifica o estado de conexão atual da instância ('open', 'connecting', 'close').
     */
    public function getConnectionState(string $instanceName): array
    {
        if ($this->isMock) {
            $record = \App\Models\WhatsappInstance::withoutGlobalScopes()->where('instance_name', $instanceName)->first();
            $currentStatus = $record?->status ?? 'disconnected';
            return [
                'state' => $currentStatus === 'connected' ? 'open' : ($currentStatus === 'connecting' ? 'connecting' : 'close'),
                'status' => $currentStatus,
                'mock' => true,
            ];
        }

        $url = rtrim($this->baseUrl, '/');
        $endpoint = "{$url}/instance/connectionState/{$instanceName}";

        try {
            $response = Http::withHeaders([
                'apikey' => $this->globalApiKey,
            ])->timeout(8)->get($endpoint);

            if ($response->successful()) {
                $data = $response->json();
                $state = $data['instance']['state'] ?? ($data['state'] ?? 'close');
                return [
                    'state' => $state,
                    'status' => $state === 'open' ? 'connected' : ($state === 'connecting' ? 'connecting' : 'disconnected'),
                ];
            }

            return ['state' => 'unknown', 'status' => 'disconnected'];
        } catch (\Throwable $e) {
            return ['state' => 'error', 'status' => 'disconnected', 'error' => $e->getMessage()];
        }
    }

    /**
     * Desconecta a sessão do WhatsApp.
     */
    public function logoutInstance(string $instanceName): bool
    {
        if ($this->isMock) {
            return true;
        }

        $url = rtrim($this->baseUrl, '/');
        $endpoint = "{$url}/instance/logout/{$instanceName}";

        try {
            $response = Http::withHeaders(['apikey' => $this->globalApiKey])
                ->timeout(10)
                ->delete($endpoint);
            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning("[WhatsappClient] Falha ao fazer logout da instância {$instanceName}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envia mensagem de texto via WhatsApp.
    /**
     * Formata e sanitiza o número de telefone adicionando DDI 55 para números brasileiros sem prefixo.
     */
    public function formatPhoneNumber(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($clean) >= 10 && strlen($clean) <= 11 && !str_starts_with($clean, '55')) {
            $clean = '55' . $clean;
        }
        return $clean;
    }

    public function sendTextMessage(WhatsappInstance $instance, string $phone, string $text): array
    {
        $phone = $this->formatPhoneNumber($phone);

        if ($this->isMock) {
            $mockId = 'MOCK_' . strtoupper(bin2hex(random_bytes(8)));
            Log::info("[MOCK WHATSAPP] Mensagem enviada com sucesso", [
                'instance' => $instance->instance_name,
                'phone' => $phone,
                'text' => $text,
                'mock_id' => $mockId,
            ]);

            return [
                'key' => [
                    'id' => $mockId,
                    'fromMe' => true,
                    'remoteJid' => "{$phone}@s.whatsapp.net",
                ],
                'status' => 'SERVER_ACK',
                'message' => [
                    'conversation' => $text,
                ],
            ];
        }

        $baseUrl = rtrim($this->baseUrl, '/');
        $endpoint = "{$baseUrl}/message/sendText/{$instance->instance_name}";
        
        $response = Http::withHeaders([
            'apikey' => $this->globalApiKey,
        ])->timeout(15)->post($endpoint, [
            'number' => $phone,
            'text' => $text,
            'delay' => 1200,
            'linkPreview' => true
        ]);

        if ($response->failed()) {
            Log::error("[WhatsappClient] Falha ao enviar WhatsApp", [
                'instance' => $instance->instance_name,
                'phone' => $phone,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            throw new Exception("Falha ao enviar mensagem via WhatsApp API: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Simula a presença "digitando..." ou "gravando áudio...".
     */
    public function sendPresence(WhatsappInstance $instance, string $phone, string $status = 'composing'): bool
    {
        $phone = $this->formatPhoneNumber($phone);

        if ($this->isMock) {
            return true;
        }

        $baseUrl = rtrim($this->baseUrl, '/');
        $endpoint = "{$baseUrl}/chat/sendPresence/{$instance->instance_name}";

        try {
            $response = Http::withHeaders([
                'apikey' => $this->globalApiKey,
            ])->timeout(5)->post($endpoint, [
                'number' => $phone,
                'presence' => $status,
                'delay' => 0,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Gera um SVG de QR code visual demonstrativo em Data URI para o modo simulação/fallback.
     */
    protected function generateMockQrCodeSvg(): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="200" height="200">
            <rect width="200" height="200" fill="#ffffff"/>
            <!-- Quadrado Superior Esquerdo -->
            <rect x="20" y="20" width="50" height="50" fill="#130e30"/>
            <rect x="30" y="30" width="30" height="30" fill="#ffffff"/>
            <rect x="37" y="37" width="16" height="16" fill="#ffe228"/>
            <!-- Quadrado Superior Direito -->
            <rect x="130" y="20" width="50" height="50" fill="#130e30"/>
            <rect x="140" y="30" width="30" height="30" fill="#ffffff"/>
            <rect x="147" y="37" width="16" height="16" fill="#ffe228"/>
            <!-- Quadrado Inferior Esquerdo -->
            <rect x="20" y="130" width="50" height="50" fill="#130e30"/>
            <rect x="30" y="140" width="30" height="30" fill="#ffffff"/>
            <rect x="37" y="147" width="16" height="16" fill="#ffe228"/>
            <!-- Pixels Centrais / Dados Simulados -->
            <rect x="85" y="25" width="10" height="10" fill="#130e30"/>
            <rect x="105" y="25" width="10" height="10" fill="#130e30"/>
            <rect x="85" y="45" width="10" height="10" fill="#130e30"/>
            <rect x="85" y="85" width="30" height="30" fill="#130e30"/>
            <rect x="92" y="92" width="16" height="16" fill="#ffffff"/>
            <rect x="130" y="85" width="10" height="10" fill="#130e30"/>
            <rect x="150" y="105" width="10" height="10" fill="#130e30"/>
            <rect x="130" y="125" width="10" height="10" fill="#130e30"/>
            <rect x="85" y="135" width="10" height="10" fill="#130e30"/>
            <rect x="105" y="155" width="10" height="10" fill="#130e30"/>
            <rect x="145" y="145" width="35" height="35" fill="#130e30"/>
            <text x="100" y="195" font-family="sans-serif" font-size="8" font-weight="bold" text-anchor="middle" fill="#5f5c6e">MODO SIMULAÇÃO</text>
        </svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
