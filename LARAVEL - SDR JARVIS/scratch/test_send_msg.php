<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;
use App\Models\WhatsappInstance;
use App\Jobs\SendWhatsappMessageJob;

$lead = Lead::find(4); // Carlos Silva (Diretor)
$instance = WhatsappInstance::withoutGlobalScopes()->where('tenant_id', $lead->tenant_id)->first();

echo "Lead: {$lead->name} (Tenant: {$lead->tenant_id})\n";
echo "Instância: " . ($instance ? "ID: {$instance->id}, Nome: {$instance->instance_name}" : "NENHUMA") . "\n";

try {
    SendWhatsappMessageJob::dispatchSync($lead, $instance, "Olá Carlos, mensagem de teste de persistência no histórico!");
    echo "Job executado com sucesso!\n";
} catch (\Throwable $e) {
    echo "ERRO no Job: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

$msgs = \App\Models\Message::where('lead_id', 4)->get();
echo "Mensagens salvas para o lead 4: " . $msgs->count() . "\n";
foreach ($msgs as $m) {
    echo "- [{$m->direction}] {$m->body} (Enviado em: {$m->sent_at})\n";
}
