<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\CompanyEnrichmentService;

echo "--- TESTE DO COMPANY ENRICHMENT SERVICE ---\n";

$service = new CompanyEnrichmentService();

// CNPJ da Petrobras ou Magazine Luiza ou Nubank
$cnpjTeste = '33.000.167/0001-01'; // Petrobras
echo "Consultando CNPJ: {$cnpjTeste}...\n";

$data = $service->fetchByCnpj($cnpjTeste);

echo "Sucesso: " . ($data['success'] ? 'SIM' : 'NÃO') . "\n";
if ($data['success']) {
    echo "Fonte: {$data['source']}\n";
    echo "Nome: {$data['name']}\n";
    echo "Razão Social: {$data['legal_name']}\n";
    echo "Segmento: {$data['segment']}\n";
    echo "Situação: {$data['status']}\n";
    echo "Capital Social: {$data['capital_social_formatted']}\n";
    echo "Endereço: {$data['full_address']}\n";
    echo "Sócios / Decisores (QSA) encontrados: " . count($data['partners']) . "\n";
    foreach (array_slice($data['partners'], 0, 5) as $p) {
        echo "  - {$p['name']} ({$p['job_title']})\n";
    }
} else {
    echo "Erro: " . ($data['message'] ?? 'Desconhecido') . "\n";
}

echo "\n--- TESTE CONCLUÍDO ---\n";
