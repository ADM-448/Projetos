<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$users = User::all();
echo "--- LISTA DE USUÁRIOS PARA LOGIN NO NAVEGADOR ---\n";
foreach ($users as $u) {
    $sa = $u->is_superadmin ? 'SIM' : 'NÃO';
    echo "ID: {$u->id} | Nome: {$u->name} | E-mail: {$u->email} | Nível: {$u->role} | SuperAdmin: {$sa}\n";
}
