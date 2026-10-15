<?php

namespace App\Services\MarketSalary;

use App\Models\MarketSalary;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JSearchSalaryProvider
{
    public function fetch(MarketSalary $market): ?SalaryQuote
    {
        $apiKey = config('services.jsearch.api_key');
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
            $response = Http::withHeaders([
                'X-RapidAPI-Host' => 'jsearch.p.rapidapi.com',
                'X-RapidAPI-Key' => $apiKey,
            ])
            ->timeout(8)
            ->get('https://jsearch.p.rapidapi.com/estimated-salary', [
                'job_title' => $query,
                'location' => $location,
                'radius' => '100',
            ]);
            
            if (! $response->successful()) {
                Log::warning('JSearch HTTP ' . $response->status() . ' para ' . $cargoName . ' - ' . $response->body());
                return null;
            }

            $data = $response->json('data');
            
            if (empty($data) || !is_array($data)) {
                return null;
            }

            $monthlyMedians = [];
            $monthlyMins = [];
            $monthlyMaxs = [];

            foreach ($data as $estimate) {
                $med = (float) ($estimate['median_salary'] ?? 0);
                $min = (float) ($estimate['min_salary'] ?? 0);
                $max = (float) ($estimate['max_salary'] ?? 0);
                $period = strtoupper($estimate['salary_period'] ?? '');
                
                if ($med <= 0) continue;

                if ($period === 'YEAR') {
                    $med = $med / 12;
                    $min = $min / 12;
                    $max = $max / 12;
                } elseif ($period === 'HOUR') {
                    $med = $med * 160;
                    $min = $min * 160;
                    $max = $max * 160;
                }

                $monthlyMedians[] = $med;
                $monthlyMins[] = $min;
                $monthlyMaxs[] = $max;
            }

            if (empty($monthlyMedians)) {
                return null;
            }

            $mediana = array_sum($monthlyMedians) / count($monthlyMedians);
            $piso = array_sum($monthlyMins) / count($monthlyMins);
            $teto = array_sum($monthlyMaxs) / count($monthlyMaxs);

            if ($mediana < 1000) {
                $mediana *= 5;
                $piso *= 5;
                $teto *= 5;
            }

            return new SalaryQuote(
                key: 'jsearch',
                nome: 'Indeed & Glassdoor (JSearch)',
                piso: round($piso, 2),
                mediana: round($mediana, 2),
                teto: round($teto, 2),
                origem: 'jsearch'
            );

        } catch (\Throwable $e) {
            Log::error('Erro na API JSearch para ' . $cargoName . ': ' . $e->getMessage());
            return null;
        }
    }
}
