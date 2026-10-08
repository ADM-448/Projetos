<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\WhatsappInstance;
use App\Services\WhatsappClientService;
use App\Traits\BelongsToTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WhatsappInstanceController extends Controller
{
    public function __construct(
        protected WhatsappClientService $client
    ) {}

    /**
     * Resolve o tenant atual do usuário.
     */
    protected function getCurrentTenantId(): int
    {
        return BelongsToTenant::resolveCurrentTenantId() ?? Auth::user()?->tenant_id ?? 1;
    }

    /**
     * Lista todas as instâncias de WhatsApp do tenant ativo.
     */
    public function index(): JsonResponse
    {
        $tenantId = $this->getCurrentTenantId();

        $instances = WhatsappInstance::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->latest()
            ->get();

        // Se não houver nenhuma instância, cria uma padrão inicial
        if ($instances->isEmpty()) {
            $tenant = Tenant::find($tenantId);
            $slug = Str::slug($tenant?->name ?? 'sdr-jarvis');
            $defaultInstance = WhatsappInstance::withoutGlobalScopes()->create([
                'tenant_id' => $tenantId,
                'instance_name' => "chip-{$slug}-01",
                'status' => 'disconnected',
                'daily_limit' => 60,
                'sent_today' => 0,
            ]);
            $instances = collect([$defaultInstance]);
        }

        return response()->json([
            'success' => true,
            'instances' => $instances,
            'gateway' => [
                'base_url' => $this->client->getBaseUrl(),
                'is_mock' => $this->client->isMockMode(),
            ],
        ]);
    }

    /**
     * Cadastra e inicializa uma nova instância de WhatsApp.
     */
    public function store(Request $request): JsonResponse
    {
        $tenantId = $this->getCurrentTenantId();

        $validated = $request->validate([
            'instance_name' => 'required|string|max:100',
            'daily_limit' => 'nullable|integer|min:10|max:500',
        ]);

        $sanitizedName = Str::slug($validated['instance_name']);

        // Evita duplicidade no banco
        $instance = WhatsappInstance::withoutGlobalScopes()
            ->where('instance_name', $sanitizedName)
            ->first();

        if (!$instance) {
            $instance = WhatsappInstance::withoutGlobalScopes()->create([
                'tenant_id' => $tenantId,
                'instance_name' => $sanitizedName,
                'status' => 'disconnected',
                'daily_limit' => $validated['daily_limit'] ?? 60,
                'sent_today' => 0,
            ]);
        }

        // Tenta registrar na Evolution API com webhook configurado
        $webhookUrl = url("/api/webhooks/whatsapp/{$instance->instance_name}");
        $webhookUrl = str_replace(['127.0.0.1', 'localhost'], 'host.docker.internal', $webhookUrl);
        
        try {
            $this->client->createInstance($instance->instance_name, $webhookUrl);
        } catch (\Throwable $e) {
            // Continua mesmo se der erro no gateway, para permitir que o usuário configure
        }

        return response()->json([
            'success' => true,
            'message' => 'Instância criada com sucesso!',
            'instance' => $instance,
        ]);
    }

    /**
     * Retorna o QR Code em base64 para leitura no WhatsApp.
     */
    public function qrCode(Request $request, WhatsappInstance $instance): JsonResponse
    {
        try {
            // Garante que a instância exista no gateway
            $webhookUrl = url("/api/webhooks/whatsapp/{$instance->instance_name}");
            
            // Corrige o IP para o Docker acessar o host local do Windows
            $webhookUrl = str_replace(['127.0.0.1', 'localhost'], 'host.docker.internal', $webhookUrl);

            $this->client->createInstance($instance->instance_name, $webhookUrl);

            // Busca o QR code ou Pairing Code (se informado telefone)
            $phone = $request->query('phone') ?? $request->query('number');
            $result = $this->client->connectInstance($instance->instance_name, $phone);

            $instance->update(['status' => 'connecting']);

            return response()->json([
                'success' => true,
                'instance_name' => $instance->instance_name,
                'base64' => $result['base64'] ?? null,
                'code' => $result['code'] ?? null,
                'pairing_code' => $result['pairingCode'] ?? null,
                'is_mock' => !empty($result['mock']),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Falha ao gerar QR Code: ' . $e->getMessage(),
                'gateway_url' => $this->client->getBaseUrl(),
            ], 500);
        }
    }

    /**
     * Checa se o WhatsApp já foi escaneado e conectado.
     */
    public function status(WhatsappInstance $instance): JsonResponse
    {
        $state = $this->client->getConnectionState($instance->instance_name);

        $newStatus = $state['status'] ?? 'disconnected';
        if ($newStatus !== $instance->status) {
            $instance->update([
                'status' => $newStatus,
                'last_active_at' => $newStatus === 'connected' ? now() : $instance->last_active_at,
            ]);
        }

        return response()->json([
            'success' => true,
            'status' => $instance->status,
            'state_raw' => $state['state'] ?? 'unknown',
            'is_connected' => $instance->status === 'connected',
            'instance' => $instance->fresh(),
        ]);
    }

    /**
     * Desconecta a sessão do WhatsApp.
     */
    public function disconnect(WhatsappInstance $instance): JsonResponse
    {
        $this->client->logoutInstance($instance->instance_name);

        $instance->update([
            'status' => 'disconnected',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Instância desconectada com sucesso!',
            'instance' => $instance,
        ]);
    }

    /**
     * Envia uma mensagem de teste para verificar se o envio está operando.
     */
    public function testSend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'instance_id' => 'required|exists:whatsapp_instances,id',
            'phone' => 'required|string',
            'message' => 'nullable|string',
        ]);

        $instance = WhatsappInstance::withoutGlobalScopes()->findOrFail($validated['instance_id']);
        $phone = preg_replace('/\D/', '', $validated['phone']);
        
        // Se o número tiver 10 ou 11 dígitos, provavelmente é do Brasil sem o 55
        if (strlen($phone) === 10 || strlen($phone) === 11) {
            $phone = '55' . $phone;
        }

        $text = $validated['message'] ?: "🚀 *SDR JARVIS Online!*\nSeu WhatsApp foi conectado com sucesso e está pronto para operar cadências e IA autônoma.";

        try {
            $result = $this->client->sendTextMessage($instance, $phone, $text);

            return response()->json([
                'success' => true,
                'message' => "Mensagem de teste enviada com sucesso para {$phone}!",
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Falha no envio de teste: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Testa ping do gateway de WhatsApp.
     */
    public function pingGateway(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => 'nullable|string',
            'api_key' => 'nullable|string',
        ]);

        $result = $this->client->pingGateway(
            $validated['url'] ?? null,
            $validated['api_key'] ?? null
        );

        return response()->json($result);
    }
}
