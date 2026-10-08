<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

foreach (User::all() as $u) {
    $u->password = Hash::make('password');
    $u->saveQuietly();
}

echo "Senhas de todos os usuários padronizadas para: password\n";
