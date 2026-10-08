<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;
use App\Models\Message;
use App\Models\Tenant;
use App\Models\WhatsappInstance;
use App\Services\AutonomousAiResponderService;

$tenant = Tenant::first();
if (!$tenant) {
    echo "Nenhum tenant encontrado.\n";
    exit(1);
}

// 1. Ativa o Piloto Automático no Tenant
$settings = $tenant->settings ?? [];
$settings['ai_autonomous_enabled'] = true;
$settings['autonomous_delay_seconds'] = 1;
$settings['autonomous_auto_advance'] = true;
$settings['autonomous_auto_disqualify'] = true;
$settings['autonomous_handoff_on_stress'] = true;
$settings['autonomous_working_hours_only'] = false;
$tenant->settings = $settings;
$tenant->save();

echo "Tenant #{$tenant->id} ({$tenant->name}) configurado com Piloto Automático ATIVO.\n";

// 2. Busca ou cria lead de teste
$lead = Lead::withoutGlobalScopes()->where('tenant_id', $tenant->id)->first();
if (!$lead) {
    $lead = Lead::withoutGlobalScopes()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Carlos Gestor Teste',
        'phone' => '5511987654321',
        'status' => 'open',
    ]);
}

$lead->resumeAiAutonomous(); // Garante que não está pausado

$instance = WhatsappInstance::withoutGlobalScopes()->where('tenant_id', $tenant->id)->first();
if (!$instance) {
    $instance = WhatsappInstance::withoutGlobalScopes()->create([
        'tenant_id' => $tenant->id,
        'instance_name' => 'Instancia_Teste',
        'phone_number' => '5511999990000',
        'status' => 'connected',
    ]);
}

$service = app(AutonomousAiResponderService::class);

echo "\n--- TESTE 1: Pergunta normal de lead ('Qual o valor da plataforma?') ---\n";
$res1 = $service->processInbound($lead, $instance, 'Qual o valor da plataforma de vocês?');
echo "Status: " . ($res1['status'] ?? 'unknown') . "\n";
echo "Resposta Autônoma da IA: " . ($res1['reply'] ?? 'none') . "\n";

echo "\n--- TESTE 2: Aceite de Call ('Vamos marcar quinta às 14h?') ---\n";
$res2 = $service->processInbound($lead, $instance, 'Perfeito, vamos marcar quinta às 14h para conversarmos!');
echo "Status: " . ($res2['status'] ?? 'unknown') . "\n";
echo "Etapa atual do lead: " . ($lead->fresh()->stage?->name ?? 'Sem etapa') . "\n";
echo "Resposta Autônoma da IA: " . ($res2['reply'] ?? 'none') . "\n";

echo "\n--- TESTE 3: Pedido de Atendente Humano (Transbordo Handoff) ---\n";
$res3 = $service->processInbound($lead, $instance, 'Por favor, quero falar com uma pessoa de verdade, não com robô.');
echo "Status: " . ($res3['status'] ?? 'unknown') . "\n";
echo "Mensagem de Handoff: " . ($res3['message'] ?? 'none') . "\n";
echo "IA Pausada para o lead? " . ($lead->fresh()->isAiAutonomousPaused() ? 'SIM (Correto!)' : 'NAO') . "\n";

echo "\n--- TESTE 4: Mensagem subsequente com IA pausada (deve ignorar/não responder) ---\n";
$res4 = $service->processInbound($lead, $instance, 'Alguém na escuta?');
echo "Status: " . ($res4['status'] ?? 'unknown') . " (Motivo: " . ($res4['reason'] ?? 'none') . ")\n";
echo "✅ Testes concluídos com sucesso!\n";
