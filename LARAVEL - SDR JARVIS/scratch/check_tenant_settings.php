<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenant = App\Models\Tenant::first();
echo "TENANT: " . $tenant->name . "\n";
echo "SETTINGS:\n";
print_r($tenant->settings);
