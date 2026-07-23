<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Base Path: " . base_path() . PHP_EOL;
echo "View Paths: " . implode(', ', view()->getFinder()->getPaths()) . PHP_EOL;
echo "Auth Login Exists: " . (view()->exists('auth.login') ? 'Yes' : 'No') . PHP_EOL;
