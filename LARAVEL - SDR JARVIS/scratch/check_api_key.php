<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$t = App\Models\Tenant::find(3);
echo "AI_API_KEY no banco: " . ($t->settings['ai_api_key'] ?? 'VAZIO') . "\n";
echo "AI_MODEL: " . ($t->settings['ai_model'] ?? 'VAZIO') . "\n";
