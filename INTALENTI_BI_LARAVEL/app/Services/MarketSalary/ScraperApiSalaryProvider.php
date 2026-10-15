<?php

namespace App\Services\MarketSalary;

use App\Models\MarketSalary;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ScraperApiSalaryProvider
{
    public function fetch(MarketSalary $market): ?SalaryQuote
    {
        $apiKey = config('services.scraperapi.api_key');
        if (! $apiKey) {
            return null;
        }

        $cargoName = (string) $market->cargo;
        $slug = Str::slug($cargoName);
        $regiao = Str::slug($market->regiao ?? 'sp');
        
        $urlToScrape = "https://www.salario.com.br/profissao/{$slug}-{$regiao}";

        try {
            // Tenta scrape.do se o formato bater ou como primeira opção
            $response = Http::timeout(8)->get("https://api.scrape.do", [
                'token' => $apiKey,
                'url' => $urlToScrape,
            ]);

            // Se der 401/403, tenta scraperapi.com
            if (! $response->successful() && in_array($response->status(), [401, 403])) {
                $response = Http::timeout(8)->get("http://api.scraperapi.com", [
                    'api_key' => $apiKey,
                    'url' => $urlToScrape,
                    'country_code' => 'br'
                ]);
            }
            
            if (! $response->successful()) {
                Log::warning('Scraper Proxy HTTP ' . $response->status() . ' para ' . $urlToScrape);
                return null;
            }

            $html = $response->body();
            
            preg_match('/Média salarial.*?R\$\s*([\d\.,]+)/i', $html, $matchesMedia);
            preg_match('/Teto salarial.*?R\$\s*([\d\.,]+)/i', $html, $matchesTeto);
            preg_match('/Piso salarial.*?R\$\s*([\d\.,]+)/i', $html, $matchesPiso);

            $parseMoney = function($str) {
                if (!$str) return null;
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
                return (float) $str;
            };

            $mediana = $parseMoney($matchesMedia[1] ?? null);
            $piso = $parseMoney($matchesPiso[1] ?? null);
            $teto = $parseMoney($matchesTeto[1] ?? null);

            if (!$mediana || $mediana < 500) {
                return null;
            }

            return new SalaryQuote(
                key: 'scraperapi',
                nome: 'Salario.com.br (ScraperAPI)',
                piso: round($piso ?? $mediana * 0.7, 2),
                mediana: round($mediana, 2),
                teto: round($teto ?? $mediana * 1.5, 2),
                origem: 'scraperapi'
            );

        } catch (\Throwable $e) {
            Log::error('Erro na API ScraperAPI para ' . $cargoName . ': ' . $e->getMessage());
            return null;
        }
    }
}
