<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Lead;
use Illuminate\Support\Facades\Auth;

$user = User::where('tenant_id', 3)->first() ?? User::first();
Auth::login($user);

echo "Usuário: {$user->name} (Tenant: {$user->tenant_id})\n";

$lead = Lead::find(4); // Carlos Silva (Diretor)
echo "Lead: {$lead->name} (Tenant: {$lead->tenant_id})\n";

$controller = new \App\Http\Controllers\KanbanController();
$request = \Illuminate\Http\Request::create('/leads/4/send-message', 'POST', [
    'text' => 'Olá Carlos Silva! Testando o endpoint direto com resposta JSON e histórico instantâneo.',
]);
$request->headers->set('Accept', 'application/json');

$response = $controller->sendMessage($request, 4);
$data = $response->getData(true);

echo "Sucesso: " . ($data['success'] ? 'SIM' : 'NÃO') . "\n";
echo "Mensagem gravada ID: " . $data['message']['id'] . "\n";
echo "Texto gravado: " . $data['message']['body'] . "\n";
echo "Direção: " . $data['message']['direction'] . "\n";

$allMsgs = \App\Models\Message::where('lead_id', 4)->get();
echo "\nTotal de mensagens salvas no histórico do Carlos Silva: " . $allMsgs->count() . "\n";
foreach ($allMsgs as $m) {
    echo "- #{$m->id} [{$m->direction}]: {$m->body}\n";
}
