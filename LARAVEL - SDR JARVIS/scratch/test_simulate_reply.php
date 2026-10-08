<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;
use App\Models\Company;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

echo "=== TESTE: SIMULADOR DE RESPOSTA DO LEAD (SANDBOX) ===\n";

$user = User::first();
if ($user) {
    Auth::login($user);
    echo "Usuário logado: {$user->name} (Tenant: {$user->tenant_id})\n";
}

$lead = Lead::first();
if (! $lead) {
    echo "Nenhum lead encontrado para o teste.\n";
    exit(1);
}

echo "Lead selecionado: ID {$lead->id} - {$lead->name} (Telefone: {$lead->phone})\n";

// Criar simulação
$controller = app(\App\Http\Controllers\KanbanController::class);
$request = new \Illuminate\Http\Request();
$request->merge([
    'text' => 'Olá! Vi a sua mensagem e tenho interesse em agendar uma apresentação!',
]);

$response = $controller->simulateReply($request, $lead->id);
$data = $response->getData(true);

echo "Status de retorno: " . ($data['success'] ? 'SUCESSO' : 'FALHA') . "\n";
echo "Mensagem gravada ID: " . ($data['message']['id'] ?? 'N/A') . "\n";
echo "Direção da mensagem: " . ($data['message']['direction'] ?? 'N/A') . "\n";
echo "Texto gravado: " . ($data['message']['body'] ?? 'N/A') . "\n";
echo "Novo estágio do lead: " . ($data['lead']['pipeline_stage_id'] ?? 'N/A') . "\n";

echo "=== TESTE FINALIZADO COM SUCESSO ===\n";
