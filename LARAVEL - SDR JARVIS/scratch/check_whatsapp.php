<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\WhatsappInstance;
use App\Models\Message;
use App\Models\Lead;

echo "--- DIAGNÓSTICO DO WHATSAPP E MENSAGENS ---\n";

$instances = WhatsappInstance::all();
echo "Instâncias cadastradas: " . $instances->count() . "\n";
foreach ($instances as $inst) {
    echo "ID: {$inst->id} | Tenant: {$inst->tenant_id} | Nome: {$inst->name} | Status: {$inst->status} | Disparos Hoje: {$inst->sent_today}/{$inst->daily_limit}\n";
}

$leads = Lead::all();
echo "\nTotal de Leads: " . $leads->count() . "\n";
foreach ($leads as $l) {
    echo "Lead ID: {$l->id} | Nome: {$l->name} | Tenant: {$l->tenant_id} | Telefone: {$l->phone}\n";
}

$messages = Message::all();
echo "\nTotal de Mensagens no Banco: " . $messages->count() . "\n";
foreach ($messages as $m) {
    echo "Msg ID: {$m->id} | Lead ID: {$m->lead_id} | Tenant: {$m->tenant_id} | Dir: {$m->direction} | Texto: {$m->body}\n";
}
