<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Company;
use App\Models\Lead;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

echo "--- TESTE DE REGISTRO DE CHAMADA COM ATA DE IA E TIMELINE ---\n";

$user = User::first();
Auth::login($user);

$company = Company::first();
$lead = Lead::where('company_id', $company->id)->first();

echo "Empresa: {$company->name} | Lead: " . ($lead ? $lead->name : 'Nenhum') . "\n";

$controller = new \App\Http\Controllers\VoipController();
$req = \Illuminate\Http\Request::create('/voip/log-call', 'POST', [
    'phone_dialed' => '11998887766',
    'company_id' => $company->id,
    'lead_id' => $lead?->id,
    'duration' => 75,
    'billable_seconds' => 70,
    'status' => 'completed',
    'outcome' => 'meeting_scheduled',
    'notes' => 'Decisor Carlos confirmou interesse na demonstração.',
    'auto_analyze' => true,
]);

$response = $controller->logCall($req);
$data = $response->getData(true);

echo "Sucesso: " . ($data['success'] ? 'SIM' : 'NÃO') . "\n";
echo "Call Log ID: " . $data['call_log']['id'] . "\n";
echo "Sentimento da Ata: " . ($data['ai_analysis']['sentiment_label'] ?? 'Nenhum') . "\n";
echo "Score de Engajamento: " . ($data['ai_analysis']['engagement_score'] ?? 0) . "/100\n";
echo "Tópicos da Ata:\n";
foreach ($data['ai_analysis']['summary_bullets'] ?? [] as $b) {
    echo "  - {$b}\n";
}
echo "Próximo Passo: " . ($data['ai_analysis']['next_actions'][0] ?? 'Nenhum') . "\n";
echo "Dica de Coaching: " . ($data['ai_analysis']['coaching_tip'] ?? 'Nenhuma') . "\n";

$lastActivity = Activity::where('company_id', $company->id)->latest()->first();
echo "\nAtividade na Timeline da Empresa:\n";
echo "Título: {$lastActivity->title}\n";
echo "Descrição:\n{$lastActivity->description}\n";

echo "\n--- TESTE CONCLUÍDO COM SUCESSO! ---\n";
