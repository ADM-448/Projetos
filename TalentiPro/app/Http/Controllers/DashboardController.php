<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard do Tenant (Master, Gestor, Colaborador)
     */
    public function index(): View
    {
        $empresaId = auth()->user()->empresa_id;

        // Total de colaboradores na empresa
        $totalColaboradores = User::where('empresa_id', $empresaId)->count();

        // Tarefas Pendentes (Avaliações que o usuário precisa responder)
        $tarefasPendentes = \App\Models\AvaliacaoParticipante::with(['avaliacao', 'avaliado'])
            ->where('avaliador_id', auth()->id())
            ->where('status', 'pendente')
            ->get();

        // Avaliações Ativas — conta avaliações com status 'andamento' na empresa.
        // Usa count distinct de avaliado_id para representar quantos avaliados estão no processo.
        $avaliacoesAtivas = \App\Models\AvaliacaoParticipante::where('status', 'pendente')
            ->whereHas('avaliacao', function($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId)
                  ->where('status', \App\Enums\StatusAvaliacao::ANDAMENTO);
            })->distinct('avaliado_id')->count('avaliado_id');
        
        $perfil = auth()->user()->perfil_acesso->value ?? auth()->user()->perfil_acesso;
        $userId = auth()->id();

        // PDIs Pendentes count
        $pdisPendentesQuery = \App\Models\Pdi::where('status', '!=', \App\Enums\StatusPdi::CONCLUIDO)
            ->where('data_fim', '>=', now()->startOfDay());
        if ($perfil === 'colaborador') {
            $pdisPendentesQuery->where('colaborador_id', $userId);
        } elseif ($perfil === 'gestor') {
            $pdisPendentesQuery->where('gestor_id', $userId);
        } else {
            $pdisPendentesQuery->where('empresa_id', $empresaId);
        }
        $pdisPendentes = $pdisPendentesQuery->count();

        // Lista de PDIs Pendentes para a aba de tarefas
        $meusPdisPendentes = \App\Models\Pdi::with('colaborador')
            ->where('status', '!=', \App\Enums\StatusPdi::CONCLUIDO)
            ->where('data_fim', '>=', now()->startOfDay())
            ->where(function($q) use ($userId) {
                $q->where('colaborador_id', $userId)
                  ->orWhere('gestor_id', $userId);
            })
            ->orderBy('data_fim', 'asc')
            ->take(5)
            ->get();

        // Avaliações em Andamento (para o card de Acompanhamento)
        $avaliacoesAtivasList = \App\Models\Avaliacao::where('empresa_id', $empresaId)
            ->where('status', \App\Enums\StatusAvaliacao::ANDAMENTO)
            ->where('is_template', false)
            ->with(['participantes' => function($q) {
                $q->select('id', 'avaliacao_id', 'avaliado_id', 'status');
            }])
            ->get();

        // Pesquisas de Clima Ativas
        $climasAtivosList = \App\Models\ClimateSurvey::where('empresa_id', $empresaId)
            ->where('status', 'ativa')
            ->with('participacoes')
            ->get();

        // PDIs globais para o summary
        $pdisConcluidos = \App\Models\Pdi::where('empresa_id', $empresaId)
            ->where('status', \App\Enums\StatusPdi::CONCLUIDO)
            ->count();
        
        $pdisEmAndamentoTotal = \App\Models\Pdi::where('empresa_id', $empresaId)
            ->where('status', '!=', \App\Enums\StatusPdi::CONCLUIDO)
            ->where('data_fim', '>=', now()->startOfDay())
            ->count();

        return view('dashboard', compact(
            'totalColaboradores',
            'avaliacoesAtivas',
            'pdisPendentes',
            'tarefasPendentes',
            'meusPdisPendentes',
            'avaliacoesAtivasList',
            'climasAtivosList',
            'pdisConcluidos',
            'pdisEmAndamentoTotal'
        ));
    }

    /**
     * API: Modais de Avaliações Ativas
     */
    public function apiAvaliacoesAtivas()
    {
        $empresaId = auth()->user()->empresa_id;

        // Mesma lógica do contador: busca avaliações com status 'andamento'
        // que ainda possuem participantes pendentes e não estão expiradas
        $avaliacoes = \App\Models\Avaliacao::where('empresa_id', $empresaId)
            ->where('status', \App\Enums\StatusAvaliacao::ANDAMENTO)
            ->where('is_template', false)
            ->where(function ($q) {
                $q->whereNull('data_fim')
                  ->orWhere('data_fim', '>=', now()->startOfDay());
            })
            ->whereHas('participantes', function ($q) {
                $q->where('status', 'pendente');
            })
            ->with(['participantes.avaliado', 'participantes.avaliador'])
            ->get();

        return view('dashboard.partials.modal_avaliacoes', compact('avaliacoes'));
    }

    /**
     * API: Modais de PDIs Pendentes
     */
    public function apiPdisPendentes()
    {
        $empresaId = auth()->user()->empresa_id;

        $pdis = \App\Models\Pdi::with('colaborador')
            ->where('empresa_id', $empresaId)
            ->where('status', '!=', \App\Enums\StatusPdi::CONCLUIDO)
            ->where('data_fim', '>=', now()->startOfDay())
            ->orderBy('data_fim', 'asc')
            ->get();

        return view('dashboard.partials.modal_pdis', compact('pdis'));
    }

    /**
     * Dashboard do SaaS (Admin)
     */
    public function adminIndex(): View
    {
        $totalEmpresas = Empresa::count();
        $totalUsuarios = User::count();
        $totalConsultores = User::where('perfil_acesso', \App\Enums\UserRole::CONSULTOR->value)->count();
        $empresas = Empresa::orderBy('created_at', 'desc')->take(10)->get();

        return view('admin.dashboard', compact(
            'totalEmpresas',
            'totalUsuarios',
            'totalConsultores',
            'empresas'
        ));
    }
}
