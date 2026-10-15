<?php

namespace App\Services\MarketSalary;

use App\Models\MarketSalary;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiSalaryProvider
{
    public function fetch(MarketSalary $market): ?SalaryQuote
    {
        $apiKey = config('services.gemini.api_key');
        if (! $apiKey) {
            return null;
        }

        $cargoName = mb_strtolower((string) $market->cargo);
        $porte = $market->porte_empresa ?? 'N/A';
        $segmento = $market->segmento_empresa ?? 'N/A';
        $regiao = $market->regiao ?? 'Nacional';
        $tags = ! empty($market->competencias_chave) ? implode(', ', $market->competencias_chave) : 'Nenhuma específica';

        $prompt = "Você é um especialista em RH e salários no Brasil. Qual é a faixa salarial CLT atual (em Reais) para o cargo de '{$cargoName}' considerando: Porte [{$porte}], Segmento [{$segmento}], Região [{$regiao}] e competências: [{$tags}]? "
            .'Responda APENAS com um objeto JSON válido no formato {"piso": 1234.50, "mediana": 2345.60, "teto": 3456.70} e NADA MAIS. Não inclua texto, nem blocos de código markdown.';

        try {
            $response = Http::timeout(20)->post(
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key='.$apiKey,
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                ]
            );
        } catch (\Throwable $e) {
            Log::error('Erro na API Gemini para '.$cargoName.': '.$e->getMessage());

            return null;
        }

        if (! $response->successful()) {
            Log::warning('Gemini HTTP '.$response->status().' para '.$cargoName. ' - Body: '.$response->body());

            return null;
        }

        $text = $response->json('candidates.0.content.parts.0.text') ?? '';
        $text = trim(str_replace(['```json', '```'], '', $text));
        $json = json_decode($text, true);

        if (! is_array($json)) {
            return null;
        }

        $mediana = isset($json['mediana']) && is_numeric($json['mediana']) ? (float) $json['mediana'] : null;
        if (! $mediana || $mediana < 500) {
            return null;
        }

        $piso = isset($json['piso']) && is_numeric($json['piso']) ? (float) $json['piso'] : round($mediana * 0.55, 2);
        $teto = isset($json['teto']) && is_numeric($json['teto']) ? (float) $json['teto'] : round($mediana * 1.80, 2);

        Log::info('Salário via IA', [
            'cargo' => $cargoName,
            'tags' => $market->competencias_chave,
            'piso' => $piso,
            'mediana' => $mediana,
            'teto' => $teto,
        ]);

        return new SalaryQuote(
            key: 'gemini',
            nome: 'Estimativa contextual (IA)',
            piso: round($piso, 2),
            mediana: round($mediana, 2),
            teto: round($teto, 2),
            origem: 'gemini',
        );
    }
}
