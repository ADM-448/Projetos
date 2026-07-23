<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$empresa = App\Models\Empresa::find(1);
$controller = app()->make(App\Http\Controllers\Web\EmpresaWebController::class);

try {
    Auth::loginUsingId(1); // Admin
    $controller->destroy($empresa);
    echo "DELETED";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
