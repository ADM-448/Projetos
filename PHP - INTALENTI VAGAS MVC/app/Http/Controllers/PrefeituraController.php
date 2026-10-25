<?php

namespace App\Http\Controllers;

use App\Models\Vaga;
use App\Models\Empresa;
use App\Models\Candidato;
use App\Models\Candidatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PrefeituraController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = Auth::user();

        // Apenas Admin (3) ou Gestor Municipal de Prefeitura
        if (!$user->isAdmin()) {
            $isPrefeitura = $user->empresas()->where('tipo', 'prefeitura')->exists();
            if (!$isPrefeitura) {
                abort(403, 'Acesso restrito ao Observatório do Trabalho.');
            }
        }

        $cidade = $user->getCidadePrefeitura();

        // 1. Queries com escopo de cidade
        $queryVagas = Vaga::withoutGlobalScopes()->ativas();
        $queryEmpresas = Empresa::where('tipo', 'empresa');
        $queryCandidatos = Candidato::query();

        if (!empty($cidade)) {
            $queryVagas->where(function ($q) use ($cidade) {
                $q->where('localizacao', 'LIKE', "%{$cidade}%")
                  ->orWhereHas('empresa', fn($e) => $e->where('cidade', 'LIKE', "%{$cidade}%"));
            });
            $queryEmpresas->where('cidade', 'LIKE', "%{$cidade}%");
            $queryCandidatos->where('cidade', 'LIKE', "%{$cidade}%");
        }

        $totalVagasAtivas = (clone $queryVagas)->count();
        $totalEmpresas = (clone $queryEmpresas)->count();
        $totalCandidatos = (clone $queryCandidatos)->count();

        $vagasIdsCidade = (clone $queryVagas)->pluck('id');
        $totalCandidaturas = Candidatura::whereIn('vaga_id', $vagasIdsCidade)->count();

        // Impacto econômico estimado (soma da média salarial das vagas ativas da cidade)
        $impactoEconomico = (clone $queryVagas)
            ->whereNotNull('faixa_salarial_min')
            ->sum('faixa_salarial_min');

        $kpis = [
            'vagas_ativas' => $totalVagasAtivas,
            'total_empresas' => $totalEmpresas,
            'total_candidatos' => $totalCandidatos,
            'total_candidaturas' => $totalCandidaturas,
            'impacto_economico' => $impactoEconomico,
        ];

        // 2. Gráfico: Vagas por Ramo de Atividade (da cidade)
        $vagasPorSetor = (clone $queryVagas)
            ->select('ramo_atividade', DB::raw('count(*) as total'))
            ->groupBy('ramo_atividade')
            ->orderByDesc('total')
            ->get();

        $labelsSetor = $vagasPorSetor->pluck('ramo_atividade')->toArray();
        $dataSetor = $vagasPorSetor->pluck('total')->toArray();

        // 3. Mapa de Calor (Pontos de Vagas e Pontos de Candidatos da cidade)
        $pontosVagas = (clone $queryVagas)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('empresa')
            ->get(['id', 'titulo', 'empresa_id', 'latitude', 'longitude'])
            ->map(function ($v) {
                return [
                    'lat' => $v->latitude,
                    'lng' => $v->longitude,
                    'titulo' => $v->titulo,
                    'empresa' => $v->empresa->nome_empresa ?? 'Confidencial',
                    'tipo' => 'vaga',
                ];
            });

        $pontosCandidatos = (clone $queryCandidatos)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'nome_completo', 'cargo_pretendido', 'latitude', 'longitude'])
            ->map(function ($c) {
                return [
                    'lat' => $c->latitude,
                    'lng' => $c->longitude,
                    'nome' => $c->nome_completo,
                    'cargo' => $c->cargo_pretendido ?: 'Não informado',
                    'tipo' => 'candidato',
                ];
            });

        // 4. Lista de Candidatos Recentes (da cidade)
        $candidatosRecentes = (clone $queryCandidatos)
            ->with('user')
            ->latest('created_at')
            ->take(10)
            ->get();

        // 5. Lista de Empresas Recentes (da cidade)
        $empresasRecentes = (clone $queryEmpresas)
            ->latest()
            ->take(10)
            ->get();

        return view('prefeitura.dashboard', compact(
            'kpis',
            'cidade',
            'labelsSetor',
            'dataSetor',
            'pontosVagas',
            'pontosCandidatos',
            'candidatosRecentes',
            'empresasRecentes'
        ));
    }
}
