<?php

namespace App\Services\MarketSalary;

use App\Models\MarketSalary;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdzunaSalaryProvider
{
    public function fetch(MarketSalary $market): ?SalaryQuote
    {
        $appId = config('services.adzuna.app_id');
        $appKey = config('services.adzuna.app_key');
        
        if (! $appId || ! $appKey) {
            return null;
        }

        $cargoName = (string) $market->cargo;
        $tags = ! empty($market->competencias_chave) ? ' ' . implode(' ', $market->competencias_chave) : '';
        $query = urlencode($cargoName . $tags);
        
        // Adzuna uses country codes, defaulting to br
        $country = 'br';

        try {
            $response = Http::timeout(8)->get("https://api.adzuna.com/v1/api/jobs/{$country}/history?app_id={$appId}&app_key={$appKey}&what={$query}");
            
            if (! $response->successful()) {
                Log::warning('Adzuna HTTP ' . $response->status() . ' para ' . $cargoName . ' - ' . $response->body());
                return null;
            }

            $data = $response->json('month');
            
            if (empty($data) || !is_array($data)) {
                if (! empty($tags)) {
                    $queryCargo = urlencode($cargoName);
                    $resp2 = Http::timeout(8)->get("https://api.adzuna.com/v1/api/jobs/{$country}/history?app_id={$appId}&app_key={$appKey}&what={$queryCargo}");
                    if ($resp2->successful()) {
                        $data = $resp2->json('month');
                    }
                }
            }

            if (empty($data) || !is_array($data)) {
                return null;
            }

            // Adzuna returns average salaries for the last months
            $lastMonthData = end($data);
            
            if (!$lastMonthData) {
                return null;
            }

            $mediana = (float) $lastMonthData;

            // A Adzuna retorna salário ANUAL (por ano). Convertemos para mensal dividindo por 12.
            $mediana = $mediana / 12;

            if ($mediana < 500) {
                return null;
            }

            $piso = $mediana * 0.7;
            $teto = $mediana * 1.5;

            return new SalaryQuote(
                key: 'adzuna',
                nome: 'Adzuna (Rede de Empregos BR)',
                piso: round($piso, 2),
                mediana: round($mediana, 2),
                teto: round($teto, 2),
                origem: 'adzuna'
            );

        } catch (\Throwable $e) {
            Log::error('Erro na API Adzuna para ' . $cargoName . ': ' . $e->getMessage());
            return null;
        }
    }
}
