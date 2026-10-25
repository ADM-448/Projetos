<?php

namespace App\Http\Controllers;

use App\Models\Vaga;
use App\Models\Candidatura;
use App\Services\GeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(protected GeoService $geoService)
    {
    }

    /**
     * Dashboard Exclusivo para Empresa / RH (Item 335 & 356)
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect()->route('prefeitura.dashboard');
        }

        if ($user->isCandidato()) {
            return redirect()->route('minhas.candidaturas');
        }

        $empresa = $user->empresas()->first();
        if (!$empresa) {
            abort(403, 'Nenhuma empresa vinculada ao seu usuário.');
        }

        $vagasIds = Vaga::withoutGlobalScopes()
            ->where('empresa_id', $empresa->id)
            ->pluck('id');

        $kpis = [
            'total_vagas' => $vagasIds->count(),
            'vagas_ativas' => Vaga::withoutGlobalScopes()->where('empresa_id', $empresa->id)->ativas()->count(),
            'total_candidatos' => Candidatura::whereIn('vaga_id', $vagasIds)->count(),
            'candidatos_pendentes' => Candidatura::whereIn('vaga_id', $vagasIds)->where('status', 'pendente')->count(),
            'candidatos_analisando' => Candidatura::whereIn('vaga_id', $vagasIds)->where('status', 'analisando')->count(),
        ];

        // Vagas com contagem de inscritos
        $minhasVagas = Vaga::withoutGlobalScopes()
            ->where('empresa_id', $empresa->id)
            ->withCount('candidaturas')
            ->latest()
            ->paginate(10);

        // Candidaturas recentes
        $candidaturasRecentes = Candidatura::with(['candidato.user', 'vaga'])
            ->whereIn('vaga_id', $vagasIds)
            ->latest('data_candidatura')
            ->take(10)
            ->get();

        // 4. Mapeamento de Candidatos por CEP Aproximado para o Mapa da Empresa
        $candidaturasParaMapa = Candidatura::with(['candidato', 'vaga'])
            ->whereIn('vaga_id', $vagasIds)
            ->get();

        $empresaCoords = $this->geoService->getCoordenadasAproximadas(null, $empresa->cidade ?: 'Maringá');

        $pontosCandidatos = $candidaturasParaMapa->map(function ($c) use ($empresaCoords) {
            $cand = $c->candidato;
            if (!$cand) return null;

            $lat = $cand->latitude;
            $lng = $cand->longitude;
            if (!$lat || !$lng) {
                $coords = $this->geoService->getCoordenadasAproximadas($cand->cep, $cand->cidade);
                $lat = $coords['lat'];
                $lng = $coords['lng'];
            }

            $distancia = $this->geoService->calcularDistancia(
                $empresaCoords['lat'],
                $empresaCoords['lng'],
                $lat,
                $lng
            );

            return [
                'id' => $cand->id,
                'userId' => $cand->user_id,
                'nome' => $cand->nome_completo,
                'cargo' => $cand->cargo_pretendido ?: 'Candidato Inscrito',
                'cidade' => $cand->cidade ?: 'Região',
                'bairro' => $cand->bairro ?: '',
                'cep_aprox' => $cand->cep ? substr(preg_replace('/\D/', '', $cand->cep), 0, 5) . '-***' : 'Aproximado',
                'vaga' => $c->vaga->titulo,
                'vagaId' => $c->vaga->id,
                'status' => $c->status,
                'distancia_km' => $distancia,
                'lat' => (float) $lat,
                'lng' => (float) $lng,
                'perfilUrl' => route('candidatos.perfil', ['userId' => $cand->user_id, 'vaga_id' => $c->vaga->id]),
            ];
        })->filter()->values();

        return view('dashboard.empresa', compact('empresa', 'kpis', 'minhasVagas', 'candidaturasRecentes', 'pontosCandidatos', 'empresaCoords'));
    }

    /**
     * Painel de Minhas Candidaturas para o Candidato
     */
    public function minhasCandidaturas()
    {
        $user = Auth::user();

        if (!$user->isCandidato() || !$user->candidato) {
            abort(403);
        }

        $candidaturas = Candidatura::with(['vaga.empresa', 'chat'])
            ->where('candidato_id', $user->candidato->id)
            ->latest('data_candidatura')
            ->paginate(15);

        return view('candidato.minhas_candidaturas', compact('candidaturas'));
    }
}
