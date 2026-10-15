<?php

namespace App\Services\MarketSalary;

use App\Models\MarketSalary;

class HeuristicSalaryProvider
{
    public function __construct(
        private StackTagExtractor $stacks,
    ) {}

    public function fetch(MarketSalary $market): SalaryQuote
    {
        $mediana = $this->estimateMediana($market);

        return new SalaryQuote(
            key: 'heuristica',
            nome: 'Benchmark interno (fallback)',
            piso: round($mediana * 0.55, 2),
            mediana: round($mediana, 2),
            teto: round($mediana * 1.80, 2),
            origem: 'heuristic',
        );
    }

    public function estimateMediana(MarketSalary $market): float
    {
        $name = mb_strtolower((string) $market->cargo);
        $tags = array_map(fn ($t) => mb_strtolower((string) $t), $market->competencias_chave ?? []);
        $tagsHaystack = implode(', ', $tags);
        $haystack = $name.' '.$tagsHaystack;

        $tagTokens = array_merge($market->competencias_chave ?? [], preg_split('/[\s\-\/]+/u', (string) $market->cargo) ?: []);
        $stacks = $this->stacks->extract($tagTokens = array_values(array_filter($tagTokens)));

        $base = $this->roleBase($name, $haystack);

        if (str_contains($haystack, 'sênior') || str_contains($haystack, 'senior') || str_contains($haystack, 'especialista')) {
            $base += 3500;
        }
        if (str_contains($haystack, 'júnior') || str_contains($haystack, 'junior') || str_contains($haystack, 'estágio') || str_contains($haystack, 'estagio')) {
            $base -= 3000;
        }

        $boosts = [
            'java' => 900,
            'python' => 700,
            'php' => 350,
            'laravel' => 500,
            'javascript' => 400,
            'typescript' => 450,
            'react' => 650,
            'node' => 800,
            'nodejs' => 800,
            'golang' => 800,
            'go' => 800,
            'kotlin' => 600,
            'ruby' => 400,
            'csharp' => 500,
            'dotnet' => 500,
            'vue' => 400,
            'angular' => 450,
            'flutter' => 500,
        ];

        foreach ($stacks as $stack) {
            $base += $boosts[$stack] ?? 250;
        }

        return max(1500, $base);
    }

    private function roleBase(string $name, string $haystack): float
    {
        if (str_contains($name, 'gerente geral') || str_contains($name, 'diretor')) {
            return 12500.00;
        }
        if (str_contains($name, 'gerente')) {
            return 8500.00;
        }

        if (str_contains($haystack, 'devops') || str_contains($haystack, 'sre') || str_contains($haystack, 'platform')) {
            return 11200.00;
        }
        if (str_contains($name, 'engenheiro') || str_contains($name, 'engineer')) {
            return 9800.00;
        }

        if (str_contains($name, 'designer') || str_contains($name, 'ux') || str_contains($name, 'ui')) {
            return 6400.00;
        }

        if (str_contains($name, 'front')) {
            return 7400.00;
        }
        if (str_contains($name, 'back')) {
            return 8600.00;
        }
        if (str_contains($name, 'fullstack') || str_contains($name, 'full stack') || str_contains($name, 'full-stack')) {
            return 8000.00;
        }

        if (str_contains($name, 'sistem') || str_contains($name, 'desenvolvedor') || str_contains($name, 'programador')) {
            return 7500.00;
        }

        if (str_contains($name, 'supervisor') && str_contains($name, 'vend')) {
            return 5500.00;
        }
        if (str_contains($name, 'supervisor') && str_contains($name, 'estoqu')) {
            return 4850.00;
        }
        if (str_contains($name, 'supervisor') || str_contains($name, 'coordenador')) {
            return 5200.00;
        }

        if (str_contains($name, 'financeiro') && (str_contains($name, 'analista') || str_contains($name, 'encarregado'))) {
            return 4850.00;
        }
        if (str_contains($name, 'analista') || str_contains($name, 'comprador') || str_contains($name, 'encarregado')) {
            return 4200.00;
        }

        if (str_contains($name, 'motorista')) {
            return 3200.00;
        }
        if (str_contains($name, 'arte final') || str_contains($name, 'tecnico')) {
            return 2800.00;
        }

        if (str_contains($name, 'faturamento') || str_contains($name, 'assist') || str_contains($name, 'aux') || str_contains($name, 'monitor')) {
            return 2400.00;
        }
        if (str_contains($name, 'televendas') || str_contains($name, 'vendedor') || str_contains($name, 'comercial') || str_contains($name, 'recepcion')) {
            return 2100.00;
        }

        if (str_contains($name, 'estoquista') || str_contains($name, 'repositor') || str_contains($name, 'conferente') || str_contains($name, 'expedicao')) {
            return 2150.00;
        }
        if (str_contains($name, 'servicos') || str_contains($name, 'gerais') || str_contains($name, 'zelador') || str_contains($name, 'limpeza') || str_contains($name, 'jardin') || str_contains($name, 'vigia')) {
            return 1924.00;
        }

        return 2800.00;
    }
}
