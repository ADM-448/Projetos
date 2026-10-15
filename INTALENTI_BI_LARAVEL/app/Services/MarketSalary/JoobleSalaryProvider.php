<?php

namespace App\Services\MarketSalary;

use App\Models\MarketSalary;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JoobleSalaryProvider
{
    public function fetch(MarketSalary $market): ?SalaryQuote
    {
        $apiKey = config('services.jooble.api_key');
        if (! $apiKey) {
            return null;
        }

        $cargoName = (string) $market->cargo;
        $tags = ! empty($market->competencias_chave) ? ' ' . implode(' ', $market->competencias_chave) : '';
        $query = $cargoName . $tags;
        $location = $market->regiao ?? 'Brasil';

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->timeout(8)
            ->post("https://br.jooble.org/api/{$apiKey}", [
                'keywords' => $query,
                'location' => $location,
                'radius' => '50',
                'salary' => 1000 // Just to get paid jobs
            ]);
            
            if (! $response->successful()) {
                Log::warning('Jooble HTTP ' . $response->status() . ' para ' . $cargoName);
                return null;
            }

            $jobs = $response->json('jobs');
            
            if (empty($jobs) || !is_array($jobs)) {
                return null;
            }

            $monthlyMedians = [];

            foreach ($jobs as $job) {
                if (!empty($job['salary'])) {
                    // Jooble often returns salary strings like "R$ 5.000 - R$ 7.000"
                    if (preg_match_all('/(?:R\$|BRL)?\s*([\d\.]+)/i', $job['salary'], $matches)) {
                        $values = [];
                        foreach ($matches[1] as $match) {
                            $val = (float) str_replace('.', '', $match);
                            if ($val > 500) {
                                $values[] = $val;
                            }
                        }
                        
                        if (count($values) > 0) {
                            $med = array_sum($values) / count($values);
                            $monthlyMedians[] = $med;
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
                key: 'jooble',
                nome: 'Portal Jooble (Vagas Consolidadas)',
                piso: round($piso, 2),
                mediana: round($mediana, 2),
                teto: round($teto, 2),
                origem: 'jooble'
            );

        } catch (\Throwable $e) {
            Log::error('Erro na API Jooble para ' . $cargoName . ': ' . $e->getMessage());
            return null;
        }
    }
}
