<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;

echo "=== TESTE: LABORATÓRIO & TREINAMENTO DA IA (SETTINGS & ESTRESSE) ===\n";

$user = User::first();
if ($user) {
    Auth::login($user);
    echo "Usuário autenticado: {$user->name} (Tenant: {$user->tenant_id})\n";
}

$controller = app(\App\Http\Controllers\AiAgentController::class);

// 1. Testar GET /ai/settings
echo "\n--- 1. Testando GET /ai/settings ---\n";
$reqGet = new \Illuminate\Http\Request();
$resGet = $controller->getSettings($reqGet);
$dataGet = $resGet->getData(true);

echo "Nome do Agente: " . ($dataGet['settings']['ai_name'] ?? 'N/A') . "\n";
echo "Tom: " . ($dataGet['settings']['tone'] ?? 'N/A') . "\n";
echo "Proposta de valor: " . substr($dataGet['settings']['company_value_prop'] ?? '', 0, 50) . "...\n";

// 2. Testar POST /ai/settings (Salvar nova diretriz)
echo "\n--- 2. Testando POST /ai/settings (Salvar Diretrizes) ---\n";
$reqPost = new \Illuminate\Http\Request();
$reqPost->merge([
    'ai_name' => 'JARVIS Closer Pro',
    'ai_persona' => 'Head de Vendas Enterprise e Especialista em SPIN Selling',
    'company_value_prop' => 'Consultoria e software que reduz o Custo de Aquisição de Clientes (CAC) em 40%.',
    'instructions' => "1. Mantenha tom executivo.\n2. Conduza sempre para reunião de 15 min.\n3. Defenda nosso ticket com ROI.",
    'guardrails' => "• Nunca dê desconto no primeiro contato.\n• Nunca critique ferramentas concorrentes.",
    'tone' => 'direct',
    'faq' => [
        ['q' => 'Vocês atendem empresas de qual porte?', 'a' => 'Atendemos desde PMEs de tecnologia até indústrias e empresas de serviços B2B.']
    ]
]);

$resPost = $controller->updateSettings($reqPost);
$dataPost = $resPost->getData(true);
echo "Status: " . ($dataPost['success'] ? 'SUCESSO' : 'FALHA') . " - " . ($dataPost['message'] ?? '') . "\n";
echo "Novo Nome salvo: " . ($dataPost['settings']['ai_name'] ?? 'N/A') . "\n";

// 3. Testar POST /ai/simulate-conversation (Ataque de Estresse)
echo "\n--- 3. Testando POST /ai/simulate-conversation (Ataque de Preço e Estresse) ---\n";
$reqSim = new \Illuminate\Http\Request();
$reqSim->merge([
    'message' => 'Vocês cobram um preço absurdo e o concorrente X faz pela metade! Por que eu deveria pagar isso?',
    'history' => [],
]);

$resSim = $controller->simulateConversation($reqSim);
$dataSim = $resSim->getData(true);

echo "Status Simulação: " . ($dataSim['success'] ? 'SUCESSO' : 'FALHA') . "\n";
echo "Resposta da IA: " . ($dataSim['reply'] ?? 'N/A') . "\n";
echo "Intenção Detectada: " . ($dataSim['xray']['user_intent'] ?? 'N/A') . "\n";
echo "Sentimento do Lead: " . ($dataSim['xray']['user_sentiment'] ?? 'N/A') . "\n";
echo "Pontuação de Estresse: " . ($dataSim['xray']['stress_score'] ?? 'N/A') . "%\n";
echo "Estratégia Aplicada: " . ($dataSim['xray']['strategy_applied'] ?? 'N/A') . "\n";

echo "\n=== TESTE CONCLUÍDO COM ÊXITO ===\n";
