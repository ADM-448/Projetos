<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;
use App\Models\Tenant;
use App\Services\AiAgentService;

echo "--- TESTE DO JARVIS AI AGENT SERVICE ---\n";

$tenant = Tenant::first();
echo "Tenant selecionado: " . ($tenant ? $tenant->name : 'Nenhum') . "\n";

$lead = Lead::first();
if (!$lead) {
    echo "Nenhum lead encontrado para teste. Criando lead temporário...\n";
    $lead = Lead::create([
        'tenant_id' => $tenant->id,
        'name' => 'Carlos Eduardo',
        'company_name' => 'Tech Logistics Brasil',
        'phone' => '11999998888',
        'status' => 'open',
    ]);
}

echo "Lead para teste: {$lead->name} ({$lead->company_name})\n";

$service = new AiAgentService($tenant);

echo "\n1. Testando geração de sugestões de resposta...\n";
$suggestions = $service->generateSuggestions($lead, "Lead perguntou se é muito caro");

echo "Sugestões geradas: " . count($suggestions) . "\n";
foreach ($suggestions as $i => $s) {
    echo "\n[" . ($i + 1) . "] {$s['badge']} - {$s['label']}\n";
    echo "Texto: {$s['text']}\n";
    echo "Estratégia: {$s['strategy']}\n";
}

echo "\n2. Testando qualificação de lead...\n";
$qualification = $service->qualifyLead($lead);
echo "Score: {$qualification['score']}/100\n";
echo "Sentimento: {$qualification['sentiment_label']}\n";
echo "Status: {$qualification['qualification_status']}\n";
echo "Próximo Passo Recomendado: {$qualification['next_best_action']}\n";
echo "Etapa Sugerida: {$qualification['suggested_stage']}\n";

echo "\n3. Testando refinamento de tom...\n";
$improved = $service->improveDraft("oi queria ver se vc tem tempo pra gente conversar sobre a empresa", "short", $lead);
echo "Texto Aprimorado (curto): {$improved}\n";

echo "\n--- TESTE CONCLUÍDO COM SUCESSO! ---\n";
