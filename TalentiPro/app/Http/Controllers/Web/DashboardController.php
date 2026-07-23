<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Avaliacao;
use App\Models\Pdi;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // 1. ADMIN / MASTER
        if (in_array($user->perfil_acesso, ['admin', 'master'])) {
            return view('dashboard');
        }

        // 2. GESTOR
        if ($user->perfil_acesso == 'gestor') {
            
            // A. DADOS PESSOAIS (Como Colaborador)
            // Minhas Avaliações Pendentes (Onde EU sou o avaliador e status = pendente)
            // Minhas Avaliações Pendentes (Onde EU sou o avaliador e status = pendente)
            $myPendingCount = \App\Models\AvaliacaoParticipante::where('avaliador_id', $user->id)
                ->where('status', 'pendente')
                ->whereNotIn('tipo', ['gestor', 'mediador'])
                ->whereHas('avaliacao', function($q) {
                    $q->where('status', '!=', 'concluida'); // Só avaliações ativas
                })
                ->count();

            // Meu PDI
            $myPdis = Pdi::where('colaborador_id', $user->id)->get();
            $myPdiProgress = $myPdis->count() > 0 ? round($myPdis->avg('progresso')) : 0;

            // B. DADOS DA EQUIPE (Como Gestor)
            // Pega IDs dos colaboradores do mesmo departamento
            $teamUserIds = \App\Models\User::where('departamento_id', $user->departamento_id)
                ->where('id', '!=', $user->id) // Exclui o próprio gestor da contagem da equipe
                ->pluck('id');

            // PDIs da Equipe
            $teamActivePdis = Pdi::whereIn('colaborador_id', $teamUserIds)
                ->where('status', '!=', 'concluido') // Assumindo status
                ->count();

            // Avaliações Ativas no Setor (Aquelas que envolvem meu time)
            // Lógica Aproximada: Avaliações onde membros do time são PARTICIPANTES (Avaliados)
            $teamActiveEvaluations = Avaliacao::where('status', 'em_andamento')
                ->whereHas('participantes', function($q) use ($teamUserIds) {
                    $q->whereIn('avaliado_id', $teamUserIds);
                })
                ->count();

            // C. MEDIAÇÃO (Se houver)
            $mediationCount = \App\Models\AvaliacaoParticipante::where('avaliador_id', $user->id)
                ->where('status', 'pendente')
                ->where('tipo', 'mediador')
                ->count();

            return view('dashboard_gestor', compact('myPendingCount', 'myPdiProgress', 'teamActivePdis', 'teamActiveEvaluations', 'mediationCount'));
        }

        // 3. COLABORADOR / MEDIADOR
        
        // Minhas Pendências (Melhorada a lógica anterior)
        // EXCLUINDO TAREFAS DE GESTOR/MEDIADOR (Elas têm painéis próprios)
        $pendenciasCount = \App\Models\AvaliacaoParticipante::where('avaliador_id', $user->id)
            ->where('status', 'pendente')
            ->whereNotIn('tipo', ['gestor', 'mediador']) 
            ->count();

        // Contagem de Mediação
        $mediationCount = \App\Models\AvaliacaoParticipante::where('avaliador_id', $user->id)
            ->where('status', 'pendente')
            ->where('tipo', 'mediador')
            ->count();

        // Progresso do Meu PDI
        $pdis = Pdi::where('colaborador_id', $user->id)->get();
        $pdiProgress = $pdis->count() > 0 ? round($pdis->avg('progresso')) : 0;

        return view('dashboard_colaborador', compact('pendenciasCount', 'pdiProgress', 'mediationCount'));
    }
}
