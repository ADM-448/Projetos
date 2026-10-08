<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$url = 'https://talentipro.com.br';
$apiKey = 'ca6ece4fca2b05087e76dc38d2a21ee4f30e31636d31c2f4931759599f6c280f';

echo "=== TESTANDO API DO WHATSAPP (TALENTIPRO.COM.BR) ===\n";
try {
    // 1. Tenta listar instâncias da Evolution API
    $resp = Http::withHeaders(['apikey' => $apiKey])->timeout(8)->get("{$url}/instance/fetchInstances");
    echo "Endpoint /instance/fetchInstances -> Status: " . $resp->status() . "\n";
    echo "Body: " . substr($resp->body(), 0, 300) . "\n\n";
} catch (\Throwable $e) {
    echo "Erro /instance/fetchInstances: " . $e->getMessage() . "\n";
}

try {
    // 2. Tenta raiz
    $resp = Http::timeout(8)->get($url);
    echo "Endpoint / -> Status: " . $resp->status() . "\n";
} catch (\Throwable $e) {
    echo "Erro /: " . $e->getMessage() . "\n";
}
