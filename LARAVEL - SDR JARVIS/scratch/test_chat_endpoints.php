<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$baseUrl = 'http://localhost:8080';
$apiKey = 'B6D711FCDE4D4FD5936544120E713976'; // globalapikey from docker-compose.evolution.yml
$instanceName = 'SDR-JARVIS-01'; // Default instance

echo "Testing POST /chat/findMessages...\n";
$response = Http::withHeaders(['apikey' => $apiKey])->post("$baseUrl/chat/findMessages/$instanceName", ['where' => []]);
echo "Status: " . $response->status() . "\n";
echo "Body: " . substr($response->body(), 0, 500) . "\n\n";

echo "Testing GET /chat/findMessages...\n";
$response = Http::withHeaders(['apikey' => $apiKey])->get("$baseUrl/chat/findMessages/$instanceName");
echo "Status: " . $response->status() . "\n";
echo "Body: " . substr($response->body(), 0, 500) . "\n\n";

echo "Testing POST /chat/findChats...\n";
$response = Http::withHeaders(['apikey' => $apiKey])->post("$baseUrl/chat/findChats/$instanceName");
echo "Status: " . $response->status() . "\n";
echo "Body: " . substr($response->body(), 0, 500) . "\n\n";

echo "Testing GET /chat/find...\n";
$response = Http::withHeaders(['apikey' => $apiKey])->get("$baseUrl/chat/find/$instanceName");
echo "Status: " . $response->status() . "\n";
echo "Body: " . substr($response->body(), 0, 500) . "\n\n";
