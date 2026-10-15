<?php

namespace App\Services\MarketSalary;

use App\Models\MarketSalary;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SerpApiSalaryProvider
{
    public function fetch(MarketSalary $market): ?SalaryQuote
    {
        $apiKey = config('services.serpapi.api_key');
        if (! $apiKey) {
            return null;
        }

        $cargoName = (string) $market->cargo;
        $tags = ! empty($market->competencias_chave) ? ' ' . implode(' ', $market->competencias_chave) : '';
        $query = $cargoName . $tags;
        
        $location = $market->regiao ?? 'Brazil';
        if (strtolower($location) === 'nacional') {
            $location = 'Brazil';
        }

        try {
            // SerpApi Google Jobs
            $response = Http::timeout(8)->get('https://serpapi.com/search.json', [
                'engine' => 'google_jobs',
                'q' => $query,
                'location' => $location,
                'api_key' => $apiKey,
                'hl' => 'pt',
            ]);
            
            if (! $response->successful()) {
                Log::warning('SerpApi HTTP ' . $response->status() . ' para ' . $cargoName . ' - ' . $response->body());
                return null;
            }

            $jobs = $response->json('jobs_results');
            
            if (empty($jobs) || !is_array($jobs)) {
                return null;
            }

            $monthlyMedians = [];

            foreach ($jobs as $job) {
                // Not all jobs have salary
                if (isset($job['job_highlights']) && is_array($job['job_highlights'])) {
                    foreach ($job['job_highlights'] as $highlight) {
                        if (isset($highlight['items']) && is_array($highlight['items'])) {
                            foreach ($highlight['items'] as $item) {
                                // e.g. "R$ 5.000 – R$ 7.000 por mês"
                                if (preg_match('/R\$\s*([\d\.]+).*R\$\s*([\d\.]+)/', $item, $matches)) {
                                    $min = (float) str_replace('.', '', $matches[1]);
                                    $max = (float) str_replace('.', '', $matches[2]);
                                    $med = ($min + $max) / 2;
                                    
                                    if (stripos($item, 'ano') !== false) {
                                        $med = $med / 12;
                                    }
                                    
                                    if ($med > 500) {
                                        $monthlyMedians[] = $med;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (empty($monthlyMedians)) {
                return null;
            }

            $mediana = array_sum($monthlyMedians) / count($monthlyMedians);
            $piso = $mediana * 0.7;
            $teto = $mediana * 1.5;

            return new SalaryQuote(
                key: 'serpapi',
                nome: 'Google Jobs & Trabalha Brasil',
                piso: round($piso, 2),
                mediana: round($mediana, 2),
                teto: round($teto, 2),
                origem: 'serpapi'
            );

        } catch (\Throwable $e) {
            Log::error('Erro na API SerpApi para ' . $cargoName . ': ' . $e->getMessage());
            return null;
        }
    }
}
