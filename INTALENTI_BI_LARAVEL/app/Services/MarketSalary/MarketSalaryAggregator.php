<?php

namespace App\Services\MarketSalary;

use App\Models\MarketSalary;
use Illuminate\Support\Facades\Log;

class MarketSalaryAggregator
{
    public function __construct(
        private SalarioComBrProvider $salarioComBr,
        private GeminiSalaryProvider $gemini,
        private HeuristicSalaryProvider $heuristic,
        private StackTagExtractor $stacks,
        private JSearchSalaryProvider $jsearch,
        private SerpApiSalaryProvider $serpapi,
        private AdzunaSalaryProvider $adzuna,
        private ScraperApiSalaryProvider $scraperapi,
        private JoobleSalaryProvider $jooble
    ) {}

    public function resolve(MarketSalary $market): array
    {
        $hasStack = $this->stacks->hasStack($market->competencias_chave);
        $quotes = [];

        // 1. Tentar todas as APIs ativas para coletar o maior número de fontes possíveis
        $providers = [
            $this->adzuna,
            $this->jooble,
            $this->jsearch,
            $this->serpapi,
            $this->scraperapi,
        ];

        foreach ($providers as $provider) {
            $quote = $provider->fetch($market);
            if ($quote) {
                $quotes[] = $quote;
            }
        }

        // 2. Se temos menos de 3 fontes, usamos a IA Gemini para complementar
        $usedGemini = false;
        if (count($quotes) < 3) {
            $geminiQuote = $this->gemini->fetch($market);
            if ($geminiQuote) {
                $quotes[] = $geminiQuote;
                $usedGemini = true;
            }
        }

        // 3. Se AINDA temos menos de 3 fontes, usamos a Heurística (Benchmark Interno) para fechar o número
        $usedHeuristic = false;
        if (count($quotes) < 3) {
            $quotes[] = $this->heuristic->fetch($market);
            $usedHeuristic = true;
        }

        $fontes = [];
        $somaPiso = 0;
        $somaMediana = 0;
        $somaTeto = 0;
        
        foreach ($quotes as $quote) {
            $fontes[$quote->key] = $quote->toFonteArray();
            $somaPiso += $quote->piso;
            $somaMediana += $quote->mediana;
            $somaTeto += $quote->teto;
        }

        $qtd = count($quotes);

        $result = [
            'piso' => round($somaPiso / $qtd, 2),
            'mediana' => round($somaMediana / $qtd, 2),
            'teto' => round($somaTeto / $qtd, 2),
            'raw_data' => [
                'url' => null,
                'cargo_normalizado' => $market->cargo,
                'success' => true,
                'fontes' => $fontes,
                'is_estimated' => $usedHeuristic || isset($fontes['gemini']),
                'used_gemini' => isset($fontes['gemini']),
                'used_heuristic' => $usedHeuristic,
                'tags' => $market->competencias_chave ?? [],
                'has_stack' => $hasStack,
                'scrape_is_specific' => true,
            ],
        ];

        Log::info('Market salary resolved', [
            'cargo' => $market->cargo,
            'tags' => $market->competencias_chave,
            'used_gemini' => isset($fontes['gemini']),
            'used_heuristic' => $usedHeuristic,
            'fontes' => array_keys($fontes),
            'mediana' => $result['mediana'],
        ]);

        return $result;
    }
}
