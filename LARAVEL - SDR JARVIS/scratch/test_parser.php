<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenant = App\Models\Tenant::find(3); // SDR Jarvis - Matriz B2B
$instructions = $tenant->settings['instructions'] ?? '';
$companyProp = $tenant->settings['company_value_prop'] ?? '';
$agentName = $tenant->settings['ai_name'] ?? 'Consultor';

echo "TENANT: {$tenant->name}\n";
echo "AGENT: {$agentName}\n";
echo "PROP: {$companyProp}\n";

// Teste de parsing do prompt do usuário
$lines = explode("\n", $instructions);
echo "Total de linhas no prompt: " . count($lines) . "\n";
