<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenants = App\Models\Tenant::all();
foreach ($tenants as $t) {
    echo "ID: {$t->id} | NAME: {$t->name}\n";
    echo "AI_NAME: " . ($t->settings['ai_name'] ?? 'none') . "\n";
    echo "COMPANY_VALUE_PROP: " . ($t->settings['company_value_prop'] ?? 'none') . "\n";
    echo "INSTRUCTIONS: " . ($t->settings['instructions'] ?? 'none') . "\n";
    echo "API_KEY: " . (!empty($t->settings['ai_api_key']) ? 'YES' : 'NO') . "\n";
    echo "----------------------------------------\n";
}
