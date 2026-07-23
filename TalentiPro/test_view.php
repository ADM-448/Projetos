<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Simulate login as admin (user ID 1 usually)
auth()->loginUsingId(1);

try {
    echo view('dashboard')->render();
    echo "\n\n=== SUCESSO! ===";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
