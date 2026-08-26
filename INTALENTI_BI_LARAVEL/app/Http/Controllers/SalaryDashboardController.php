<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\MarketSalary;
use App\Models\Funcionario;
use App\Models\Cargo;
use App\Jobs\ScrapeMarketSalaryJob;

class SalaryDashboardController extends Controller
{
    public function index()
    {
        // Recupera cargos ativos (Sessão)
        $cargos = session('cargos', []);

        // Recupera dados do mercado atuais (Cache do DB)
        $marketData = MarketSalary::orderBy('id', 'desc')->get();

        // Dados base de funcionários para Enquadramento (Sessão Volátil)
        $funcionarios = session('funcionarios', []);

        return view('dashboard', [
            'marketData' => $marketData,
            'cargos' => $cargos,
            'funcionarios' => $funcionarios,
            'matrizSalarial' => session('matriz_salarial', [])
        ]);
    }

    public function syncMarketData(Request $request)
    {
        $validated = $request->validate([
            'cargo' => 'required|string',
            'regiao' => 'nullable|string'
        ]);

        // Verifica se já existe uma pesquisa para esse cargo/região (atualiza ou cria pendente)
        $market = MarketSalary::firstOrCreate(
            ['cargo' => $validated['cargo'], 'regiao' => $validated['regiao']],
            ['status' => 'pending']
        );

        // Evitar consulta desnecessária se já foi atualizado nos últimos 30 dias
        if ($market->status === 'completed' && $market->last_scraped_at && $market->last_scraped_at->diffInDays(now()) < 30) {
            return back()->with('message', 'Os dados de mercado para este cargo já estão atualizados (pesquisado nos últimos 30 dias).');
        }

        // Se já existia e falhou ou é antigo, reseta pra pendente
        if ($market->status !== 'pending') {
            $market->update(['status' => 'pending']);
        }

        // Despacha o Job para a Fila (roda em background)
        ScrapeMarketSalaryJob::dispatch($market->id);

        return back()->with('message', 'A pesquisa de mercado foi enviada para processamento em background.');
    }

    public function syncAllMarketData()
    {
        $cargos = session('cargos', []);

        foreach ($cargos as $cargoItem) {
            $cargoNome = is_array($cargoItem) ? ($cargoItem['nome'] ?? '') : $cargoItem;
            if (!$cargoNome) continue;

            $market = MarketSalary::firstOrCreate(
                ['cargo' => $cargoNome, 'regiao' => 'Nacional'],
                ['status' => 'pending']
            );

            // Evitar consulta desnecessária se já foi atualizado nos últimos 30 dias
            if ($market->status === 'completed' && $market->last_scraped_at && $market->last_scraped_at->diffInDays(now()) < 30) {
                continue; // Pula este cargo e vai pro próximo
            }

            if ($market->status !== 'pending') {
                $market->update(['status' => 'pending']);
            }

            ScrapeMarketSalaryJob::dispatch($market->id);
        }

        return back()->with('message', 'Todas as pesquisas de mercado foram enviadas para processamento em fila.');
    }
}
