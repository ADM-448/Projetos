<?php

namespace App\Services;

use App\Models\MarketSalary;
use App\Services\MarketSalary\MarketSalaryAggregator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CepMarketSearchService
{
    public function __construct(
        private MarketSalaryAggregator $marketSalaryAggregator,
        private SalaryRecommendationService $recommendationService
    ) {}

    /**
     * Consulta localidade por CEP via ViaCEP.
     */
    public function consultarCep(string $cep): ?array
    {
        $cepLimpo = preg_replace('/\D/', '', $cep);
        if (strlen($cepLimpo) !== 8) {
            return null;
        }

        try {
            $response = Http::timeout(5)->get("https://viacep.com.br/ws/{$cepLimpo}/json/");
            if ($response->successful()) {
                $data = $response->json();
                if (!isset($data['erro'])) {
                    return [
                        'cep' => $cepLimpo,
                        'logradouro' => $data['logradouro'] ?? '',
                        'bairro' => $data['bairro'] ?? '',
                        'cidade' => $data['localidade'] ?? '',
                        'uf' => $data['uf'] ?? '',
                        'regiao' => $data['uf'] ?? 'BR',
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Erro ao consultar ViaCEP para {$cep}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Realiza a pesquisa automática de mercado com base no cargo e CEP.
     */
    public function pesquisarVagaPorCepECargo(string $cargo, ?string $cep = null): array
    {
        $localidade = $cep ? $this->consultarCep($cep) : null;
        $regiao = $localidade['uf'] ?? 'BR';
        $cidade = $localidade['cidade'] ?? 'Nacional';

        // Normalização CBO
        $cboInfo = $this->recommendationService->normalizarCargoECBO($cargo);
        $cargoParaPesquisa = $cboInfo['cargo_real'];

        // Instancia ou busca modelo MarketSalary
        $marketSalary = MarketSalary::firstOrNew([
            'cargo' => $cargoParaPesquisa,
            'regiao' => $regiao,
        ]);

        if (!$marketSalary->exists || !$marketSalary->mediana || $marketSalary->last_scraped_at < now()->subDays(30)) {
            try {
                $resolvido = $this->marketSalaryAggregator->resolve($marketSalary);
                $marketSalary->piso = $resolvido['piso'];
                $marketSalary->mediana = $resolvido['mediana'];
                $marketSalary->teto = $resolvido['teto'];
                $marketSalary->raw_data = $resolvido['raw_data'];
                $marketSalary->last_scraped_at = now();
                $marketSalary->status = 'completed';
                $marketSalary->save();
            } catch (\Throwable $e) {
                Log::warning("Falha ao resolver salário de mercado para {$cargoParaPesquisa}: " . $e->getMessage());
            }
        }

        $piso = $marketSalary->piso ? (float) $marketSalary->piso : 2200.00;
        $mediana = $marketSalary->mediana ? (float) $marketSalary->mediana : 3400.00;
        $teto = $marketSalary->teto ? (float) $marketSalary->teto : 5200.00;

        return [
            'cargo_original' => $cargo,
            'cargo_real' => $cboInfo['cargo_real'],
            'cbo' => $cboInfo['cbo'],
            'descricao_cargo' => $cboInfo['descricao'],
            'foi_corrigido' => $cboInfo['foi_corrigido'],
            'aviso_ajuste' => $cboInfo['aviso_ajuste'],
            'localidade' => [
                'cep' => $localidade['cep'] ?? $cep,
                'cidade' => $cidade,
                'uf' => $regiao,
            ],
            'faixa_salarial' => [
                'piso' => $piso,
                'mediana' => $mediana,
                'teto' => $teto,
            ],
            'aviso_legal' => SalaryRecommendationService::getAvisoLegalObrigatorio(),
        ];
    }
}
