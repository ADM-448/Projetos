<?php

namespace App\Http\Controllers;

use App\Models\Atividade;
use App\Models\DiscAssessment;
use App\Models\Solicitacao;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiscSolicitacaoController extends Controller
{
    /**
     * Colaborador/Gestor solicita refazer o Teste DISC.
     * Master/Admin refazem diretamente via disc.retake — esta rota é apenas para os demais.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Somente master/admin não devem usar esta rota
        if ($user->perfil_acesso === UserRole::MASTER || $user->perfil_acesso === UserRole::ADMIN) {
            return redirect()->route('disc.retake')->withInput();
        }

        $request->validate([
            'justificativa' => 'required|string|min:20|max:1000',
        ], [
            'justificativa.required' => 'A justificativa é obrigatória.',
            'justificativa.min'      => 'A justificativa deve ter pelo menos 20 caracteres.',
        ]);

        // Busca o assessment do usuário logado
        $assessment = DiscAssessment::where('user_id', $user->id)->first();
        if (!$assessment) {
            return back()->with('error', 'Você ainda não possui um Teste DISC para solicitar refazer.');
        }

        // Bloqueia duplicata pendente ou aprovada
        $existente = Solicitacao::where('user_id', $user->id)
            ->where('disc_assessment_id', $assessment->id)
            ->where('tipo', 'reabertura_disc')
            ->whereIn('status', ['pendente', 'aprovada'])
            ->first();

        if ($existente) {
            $statusStr = $existente->status === 'pendente' ? 'pendente de aprovação' : 'já aprovada';
            return back()->with('error', "Você já possui uma solicitação de refazer o DISC com status: {$statusStr}.");
        }

        $solicitacao = Solicitacao::create([
            'empresa_id'         => $user->empresa_id,
            'user_id'            => $user->id,
            'avaliacao_id'       => null,
            'disc_assessment_id' => $assessment->id,
            'tipo'               => 'reabertura_disc',
            'justificativa'      => $request->justificativa,
            'status'             => 'pendente',
        ]);

        // Notifica todos os usuários Master/Admin da empresa
        $masters = User::where('empresa_id', $user->empresa_id)
            ->whereIn('perfil_acesso', [UserRole::MASTER->value, UserRole::ADMIN->value])
            ->where('id', '!=', $user->id)
            ->get();

        foreach ($masters as $master) {
            \App\Models\Atividade::create([
                'user_id'    => $master->id,
                'empresa_id' => $user->empresa_id,
                'icone'      => 'fa-solid fa-puzzle-piece',
                'cor'        => '#8b5cf6',
                'titulo'     => 'Solicitação: Refazer DISC',
                'descricao'  => $user->name . ' solicitou permissão para refazer o Teste DISC.',
                'url'        => route('solicitacoes.index'),
                'lida'       => false,
            ]);

            app(\App\Services\EmailService::class)->notificar(
                $master->id,
                "Solicitação de Reabertura de DISC",
                "O colaborador {$user->name} solicitou permissão para refazer o Teste DISC.\n\nJustificativa: {$request->justificativa}",
                route('solicitacoes.index'),
                'Ver Solicitação'
            );
        }

        return back()->with('success', 'Solicitação enviada! O RH será notificado e aprovará em breve.');
    }
}
