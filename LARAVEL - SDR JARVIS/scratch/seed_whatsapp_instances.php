<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use App\Models\WhatsappInstance;

echo "--- CRIANDO/GARANTINDO INSTÂNCIAS DE WHATSAPP PARA TODOS OS TENANTS ---\n";

foreach (Tenant::all() as $tenant) {
    $instance = WhatsappInstance::withoutGlobalScopes()->where('tenant_id', $tenant->id)->first();
    if (!$instance) {
        $newInstance = WhatsappInstance::create([
            'tenant_id' => $tenant->id,
            'instance_name' => 'Instância WhatsApp ' . $tenant->name,
            'phone_number' => '5511999998888',
            'status' => 'connected',
            'daily_limit' => 100,
            'sent_today' => 0,
            'last_active_at' => now(),
        ]);
        echo "Criada instância ID: {$newInstance->id} para Tenant ID: {$tenant->id} ({$tenant->name})\n";
    } else {
        echo "Tenant ID: {$tenant->id} ({$tenant->name}) já possui instância ID: {$instance->id} (Status: {$instance->status})\n";
    }
}
