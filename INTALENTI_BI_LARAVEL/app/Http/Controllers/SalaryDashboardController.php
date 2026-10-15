<?php

namespace App\Http\Controllers;

use App\Helpers\StringNormalizer;
use App\Jobs\ScrapeMarketSalaryJob;
use App\Models\Convencao;
use App\Models\MarketSalary;
use App\Services\MarketSalary\MarketContextHasher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SalaryDashboardController extends Controller
{
    public function index()
    {
        $cargos = session('cargos', []);
        $marketData = $this->buildMarketRows($cargos);
        $funcionarios = session('funcionarios', []);

        $convencoes = Convencao::with('regras')->orderByDesc('id')->get();
        $convencaoAtiva = $convencoes->first();

        if ($convencaoAtiva && ! empty($funcionarios)) {
            // Pega TODAS as regras da convenção, não apenas 'piso_cargo'
            $regras = $convencaoAtiva->regras;

            foreach ($funcionarios as &$funcionario) {
                $cargoRaw = is_array($funcionario['cargo'] ?? null)
                    ? ($funcionario['cargo']['nome'] ?? '')
                    : ($funcionario['cargo'] ?? '');
                $cargoNormalizadoFuncionario = StringNormalizer::normalizeCargo($cargoRaw);

                $funcionario['abaixo_piso_cct'] = false;
                $funcionario['valor_piso_cct'] = null;
                $funcionario['tipo_regra_cct'] = null; // Novo: para saber qual regra foi aplicada

                foreach ($regras as $regra) {
                    $cargoNormalizadoRegra = StringNormalizer::normalizeCargo($regra->descricao);

                    if ($cargoNormalizadoFuncionario === $cargoNormalizadoRegra) {
                        $salarioAtual = (float) ($funcionario['salario_atual'] ?? $funcionario['salario_base'] ?? 0);

                        // --- AQUI ENTRA A LÓGICA QUE VOCÊ PERGUNTOU ---
                        if ($regra->tipo_regra === 'adicional_percentual') {
                            // Calcula a porcentagem sobre o salário atual
                            $valorCalculado = $salarioAtual * ((float) $regra->valor / 100);
                        } else {
                            // Usa o valor fixo (para 'piso_cargo' ou 'beneficio_fixo')
                            $valorCalculado = (float) $regra->valor;
                        }
                        // ------------------------------------------------

                        $funcionario['valor_piso_cct'] = $valorCalculado;
                        $funcionario['tipo_regra_cct'] = $regra->tipo_regra;

                        // A lógica de "Abaixo do Piso" só faz sentido para valores fixos (piso ou benefício)
                        if (in_array($regra->tipo_regra, ['piso_cargo', 'beneficio_fixo'])) {
                            if ($salarioAtual > 0 && $salarioAtual < $valorCalculado) {
                                $funcionario['abaixo_piso_cct'] = true;
                            }
                        }

                        break; // Encontrou a regra para este cargo, pode parar de procurar
                    }
                }

                // Fallback: se nenhuma regra específica de cargo foi encontrada,
                // aplica o reajuste geral da convenção (se houver)
                if ($funcionario['valor_piso_cct'] === null) {
                    $reajusteGeral = (float) ($convencaoAtiva->reajuste_geral_percentual ?? 0);
                    if ($reajusteGeral > 0) {
                        $salarioAtual = (float) ($funcionario['salario_atual'] ?? $funcionario['salario_base'] ?? 0);
                        // O salário projetado após o reajuste geral
                        $funcionario['valor_piso_cct'] = $salarioAtual * (1 + $reajusteGeral / 100);
                        $funcionario['tipo_regra_cct'] = 'reajuste_geral';
                        // Reajuste geral sempre resulta em salário maior — nunca "abaixo do piso"
                        $funcionario['abaixo_piso_cct'] = false;
                    }
                }
            }
            unset($funcionario); // Quebra a referência
        }

        return view('dashboard', [
            'marketData' => $marketData,
            'cargos' => $cargos,
            'funcionarios' => $funcionarios,
            'matrizSalarial' => session('matriz_salarial', []),
            'convencoes' => $convencoes,
            'convencaoAtiva' => $convencaoAtiva,
        ]);
    }

    public function syncMarketData(Request $request)
    {
        $validated = $request->validate([
            'cargo' => 'required|string',
            'regiao' => 'nullable|string',
        ]);

        $market = MarketSalary::firstOrCreate(
            ['cargo' => $validated['cargo'], 'regiao' => $validated['regiao']],
            ['status' => 'pending']
        );

        $market->update(['status' => 'pending']);

        ScrapeMarketSalaryJob::dispatchSync($market->id);

        return back()->with('message', 'A pesquisa de mercado foi processada.');
    }

    public function syncAllMarketData(Request $request, MarketContextHasher $hasher)
    {
        @set_time_limit(0);
        $cargos = session('cargos', []);

        $porte = $request->input('porte_empresa', 'N/A');
        $segmento = $request->input('segmento_empresa', 'N/A');
        $regiao = $request->input('regiao_empresa', 'Nacional');
        Log::info('SUBMITTED TAGS:', $request->all());
        $todasTags = $request->input('tags', []);

        if (empty($cargos)) {
            $cargos = MarketSalary::query()
                ->orderByDesc('id')
                ->get()
                ->unique(fn ($row) => $row->context_hash ?: mb_strtolower($row->cargo))
                ->map(fn ($row) => $row->cargo)
                ->filter()
                ->values()
                ->all();
        }

        foreach ($cargos as $cargoItem) {
            $cargoNome = is_array($cargoItem) ? ($cargoItem['nome'] ?? '') : $cargoItem;
            $cargoId = is_array($cargoItem) ? ($cargoItem['id'] ?? null) : null;
            if (! $cargoNome) {
                continue;
            }

            $tagsCargo = $this->tagsForCargo($todasTags, $cargoId, $cargoNome);

            $contextHash = $hasher->make($cargoNome, $regiao, $porte, $segmento, $tagsCargo);

            $market = MarketSalary::firstOrCreate(
                ['context_hash' => $contextHash],
                [
                    'cargo' => $cargoNome,
                    'regiao' => $regiao,
                    'porte_empresa' => $porte,
                    'segmento_empresa' => $segmento,
                    'competencias_chave' => $tagsCargo,
                    'status' => 'pending',
                ]
            );

            $market->update([
                'competencias_chave' => $tagsCargo,
                'status' => 'pending',
            ]);

            ScrapeMarketSalaryJob::dispatchSync($market->id);
        }

        return back()->with('message', 'As pesquisas de mercado foram processadas.');
    }

    /**
     * @param  array<int|string, mixed>  $cargos
     * @return list<array<string, mixed>>
     */
    private function buildMarketRows(array $cargos): array
    {
        if ($cargos === []) {
            return MarketSalary::query()
                ->orderByDesc('id')
                ->get()
                ->unique(fn (MarketSalary $row) => $row->context_hash ?: mb_strtolower($row->cargo))
                ->values()
                ->map(fn (MarketSalary $row) => $this->serializeMarket($row, null))
                ->all();
        }

        $rows = [];
        foreach ($cargos as $cargoItem) {
            $cargoNome = is_array($cargoItem) ? ($cargoItem['nome'] ?? '') : $cargoItem;
            $cargoId = is_array($cargoItem) ? ($cargoItem['id'] ?? null) : null;
            if (! $cargoNome) {
                continue;
            }

            $latest = MarketSalary::query()
                ->where('cargo', $cargoNome)
                ->orderByDesc('id')
                ->first();

            $rows[] = $this->serializeMarket($latest, $cargoId, $cargoNome);
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $todasTags
     * @return list<string>
     */
    private function tagsForCargo(array $todasTags, mixed $cargoId, string $cargoNome): array
    {
        $raw = [];
        if ($cargoId !== null && isset($todasTags[(string) $cargoId])) {
            $raw = $todasTags[(string) $cargoId];
        } elseif ($cargoId !== null && isset($todasTags[$cargoId])) {
            $raw = $todasTags[$cargoId];
        } elseif (isset($todasTags[$cargoNome])) {
            $raw = $todasTags[$cargoNome];
        }

        if (! is_array($raw)) {
            return [];
        }

        return array_values(array_filter(array_map('strval', $raw)));
    }

    private function serializeMarket(?MarketSalary $row, mixed $cargoId, ?string $fallbackCargo = null): array
    {
        $competencias = $row?->competencias_chave ?? [];

        return [
            'id' => $row?->id ?? ('cargo-'.$cargoId),
            'cargo_id' => $cargoId,
            'cargo' => $row?->cargo ?? $fallbackCargo,
            'regiao' => $row?->regiao,
            'competencias_chave' => $competencias,
            'tags' => $competencias,
            'status' => $row?->status ?? 'pending',
            'piso' => $row?->piso,
            'mediana' => $row?->mediana,
            'teto' => $row?->teto,
            'raw_data' => $row?->raw_data,
            'last_scraped_at' => $row?->last_scraped_at,
        ];
    }
}
