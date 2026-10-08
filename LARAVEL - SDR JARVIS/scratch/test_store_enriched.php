<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Company;
use App\Models\Lead;
use Illuminate\Support\Facades\Auth;

echo "--- TESTE DE ENRIQUECIMENTO DE CONTA E CADASTRO COMPLETO ---\n";

$user = User::first();
Auth::login($user);

$controller = new \App\Http\Controllers\KanbanController();
$req = \Illuminate\Http\Request::create('/leads', 'POST', [
    'company_name' => 'Empresa Teste Enriquecida S.A.',
    'name' => 'Carlos Alberto Sócio',
    'job_title' => 'Diretor Presidente',
    'phone' => '11999887766',
    'email' => 'carlos@empresa.com',
    'pipeline_stage_id' => 1,
    'document' => '33.000.167/0001-01',
    'segment' => 'Extração e Refino de Óleo e Gás',
    'custom_attributes' => [
        'capital_social' => 'R$ 205.000.000.000,00',
        'cidade' => 'Rio de Janeiro',
    ],
]);

$controller->storeLead($req);

$company = Company::where('name', 'Empresa Teste Enriquecida S.A.')->with('contacts')->first();
echo "Empresa criada: {$company->name} (ID: {$company->id})\n";
echo "CNPJ / Documento: {$company->document}\n";
echo "Segmento: {$company->segment}\n";
echo "Contatos vinculados: " . $company->contacts->count() . "\n";
foreach ($company->contacts as $c) {
    echo "  - Contato: {$c->name} ({$c->job_title}) | Tel: {$c->phone}\n";
}

echo "\n--- TESTE CONCLUÍDO COM SUCESSO! ---\n";
