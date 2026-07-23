<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

App\Models\Skill::all()->each(function($s) {
    $n = str_replace(['‡Æ', ',g', 'Visãoo', 'Comunica‡Æo', 'An lise', 'Resolu‡Æo', 'Prospec‡Æo'], ['ção', 'ég', 'Visão', 'Comunicação', 'Análise', 'Resolução', 'Prospecção'], $s->nome);
    $d = str_replace(['‡Æ', ',g', 'Visãoo', 'Comunica‡Æo', 'An lise', 'Resolu‡Æo', 'Prospec‡Æo'], ['ção', 'ég', 'Visão', 'Comunicação', 'Análise', 'Resolução', 'Prospecção'], $s->descricao);
    
    if ($n !== $s->nome || $d !== $s->descricao) {
        $s->nome = $n;
        $s->descricao = $d;
        $s->save();
        echo "Fixed: " . $s->nome . "\n";
    }
});

App\Models\Competencia::all()->each(function($c) {
    $n = str_replace(['‡Æ', ',g', 'Visãoo', 'Comunica‡Æo', 'An lise', 'Resolu‡Æo', 'Prospec‡Æo'], ['ção', 'ég', 'Visão', 'Comunicação', 'Análise', 'Resolução', 'Prospecção'], $c->nome);
    $d = str_replace(['‡Æ', ',g', 'Visãoo', 'Comunica‡Æo', 'An lise', 'Resolu‡Æo', 'Prospec‡Æo'], ['ção', 'ég', 'Visão', 'Comunicação', 'Análise', 'Resolução', 'Prospecção'], $c->descricao);
    
    if ($n !== $c->nome || $d !== $c->descricao) {
        $c->nome = $n;
        $c->descricao = $d;
        $c->save();
        echo "Fixed Competencia: " . $c->nome . "\n";
    }
});
echo "Done\n";
