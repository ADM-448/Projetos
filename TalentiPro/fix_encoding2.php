<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

App\Models\Skill::all()->each(function($s) {
    $n = str_replace(['çãoo', 'Competˆncias'], ['ção', 'Competências'], $s->nome);
    $d = str_replace(['çãoo', 'Competˆncias'], ['ção', 'Competências'], $s->descricao);
    
    if ($n !== $s->nome || $d !== $s->descricao) {
        $s->nome = $n;
        $s->descricao = $d;
        $s->save();
        echo "Fixed: " . $s->nome . "\n";
    }
});

App\Models\Competencia::all()->each(function($c) {
    $n = str_replace(['çãoo', 'Competˆncias'], ['ção', 'Competências'], $c->nome);
    $d = str_replace(['çãoo', 'Competˆncias'], ['ção', 'Competências'], $c->descricao);
    
    if ($n !== $c->nome || $d !== $c->descricao) {
        $c->nome = $n;
        $c->descricao = $d;
        $c->save();
        echo "Fixed Competencia: " . $c->nome . "\n";
    }
});
echo "Done\n";
