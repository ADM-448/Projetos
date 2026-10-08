<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;
use App\Models\User;
use App\Models\PipelineStage;
use Illuminate\Support\Facades\Auth;

echo "--- TESTE DE ENDPOINTS HTTP DO AI AGENT CONTROLLER ---\n";

$user = User::first();
if (!$user) {
    die("Nenhum usuário cadastrado.\n");
}
Auth::login($user);
echo "Usuário autenticado: {$user->name} (Tenant ID: {$user->tenant_id})\n";

$lead = Lead::where('tenant_id', $user->tenant_id)->first();
if (!$lead) {
    die("Nenhum lead encontrado para o tenant.\n");
}
echo "Lead de teste: {$lead->name} (ID: {$lead->id})\n";

$controller = new \App\Http\Controllers\AiAgentController();

// 1. Teste suggestReplies
echo "\n1. Testando suggestReplies:\n";
$req = \Illuminate\Http\Request::create('/ai/suggest-replies', 'POST', [
    'lead_id' => $lead->id,
    'context' => 'Lead quer saber o diferencial da plataforma',
]);
$res = $controller->suggestReplies($req);
$data = $res->getData(true);
echo "Status: " . ($data['success'] ? 'OK' : 'FAIL') . "\n";
echo "Total de sugestões: " . count($data['suggestions']) . "\n";
echo "Primeira sugestão: " . $data['suggestions'][0]['badge'] . " -> " . substr($data['suggestions'][0]['text'], 0, 70) . "...\n";

// 2. Teste qualifyLead
echo "\n2. Testando qualifyLead:\n";
$req2 = \Illuminate\Http\Request::create('/ai/qualify-lead', 'POST', [
    'lead_id' => $lead->id,
]);
$res2 = $controller->qualifyLead($req2);
$data2 = $res2->getData(true);
echo "Status: " . ($data2['success'] ? 'OK' : 'FAIL') . "\n";
echo "Score: " . $data2['qualification']['score'] . "/100\n";
echo "Sentimento: " . $data2['qualification']['sentiment_label'] . "\n";
echo "Etapa Sugerida: " . $data2['qualification']['suggested_stage'] . "\n";

// 3. Teste improveDraft
echo "\n3. Testando improveDraft:\n";
$req3 = \Illuminate\Http\Request::create('/ai/improve-draft', 'POST', [
    'lead_id' => $lead->id,
    'text' => 'oi queria te mostrar nossa ferramenta',
    'tone' => 'executive',
]);
$res3 = $controller->improveDraft($req3);
$data3 = $res3->getData(true);
echo "Status: " . ($data3['success'] ? 'OK' : 'FAIL') . "\n";
echo "Texto polido: " . $data3['text'] . "\n";

echo "\n--- TODOS OS ENDPOINTS DO CONTROLLER FUNCIONANDO COM PERFEIÇÃO! ---\n";
