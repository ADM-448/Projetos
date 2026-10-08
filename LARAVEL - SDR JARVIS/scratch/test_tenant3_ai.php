<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenant = App\Models\Tenant::find(3);
$service = new App\Services\AiAgentService($tenant);

$tests = [
    'vcs treinam oq?',
    'oi',
    'quanto custa?',
    'nós usamos planilhas aqui no excel',
    'sou gestor de equipe',
    'temos 50 colaboradores na empresa',
    'estou sem tempo para implantar software',
];

echo "TESTANDO COM O TENANT #3 ({$tenant->name})\n";
echo "PRODUTO: " . ($tenant->settings['company_value_prop'] ?? '') . "\n\n";

foreach ($tests as $t) {
    echo "====================================================\n";
    echo "LEAD: {$t}\n";
    $res = $service->simulateConversation([], $t);
    echo "JARVIS: {$res['reply']}\n";
}
