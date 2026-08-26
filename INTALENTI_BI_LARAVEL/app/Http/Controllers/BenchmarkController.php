<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cargo;
use Illuminate\Support\Facades\Http;

class BenchmarkController extends Controller
{
    public function analyze(Request $request, $id)
    {
        // Recebe o ID do Cargo
        $cargo = Cargo::findOrFail($id);
        
        try {
            $response = Http::timeout(30)->post('http://127.0.0.1:8001/market-benchmark', [
                'cargo' => $cargo->nome
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['salario_ideal'])) {
                    // Remove qualquer "R$" ou formatação antes de salvar
                    $salario = floatval(preg_replace('/[^0-9.]/', '', str_replace(',', '.', str_replace('.', '', $data['salario_ideal']))));
                    
                    if ($salario > 0) {
                        $cargo->update([
                            'salario_mercado' => $salario
                        ]);
                        return redirect()->back()->with('success', 'Análise de mercado do cargo concluída!');
                    }
                }
            }
            
            return redirect()->back()->withErrors(['error' => 'A IA não conseguiu retornar um valor válido.']);
            
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Erro ao conectar com a IA: ' . $e->getMessage()]);
        }
    }
}
