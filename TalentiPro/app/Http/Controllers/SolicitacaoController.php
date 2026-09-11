<?php

namespace App\Http\Controllers;

use App\Models\Avaliacao;
use App\Models\Atividade;
use App\Models\AvaliacaoParticipante;
use App\Models\Solicitacao;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SolicitacaoController extends Controller
{
    /**
     * Lista solicitações conforme o perfil do usuário.
     */
    public function index()
    {
        $this->authorize('viewAny', Solicitacao::class);

        $user = auth()->user();

        $query = Solicitacao::with(['user.departamento', 'avaliacao']);

        // Gestor vê apenas solicitações dos seus subordinados diretos
        if ($user->perfil_acesso === UserRole::GESTOR) {
            $query->whereHas('user', fn ($q) => $q->where('gestor_id', $user->id));
        }

        // Colaborador vê apenas as próprias
        if ($user->perfil_acesso === UserRole::COLABORADOR) {
            $query->where('user_id', $user->id);
        }

        $solicitacoes = $query
            ->orderByRaw("FIELD(status, 'pendente', 'aprovada', 'recusada')")
            ->orderBy('created_at', 'desc')
            ->get();

        return view('solicitacoes.index', compact('solicitacoes'));
    }

    /**
     * Colaborador cria uma nova solicitação de reabertura/contestação.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Solicitacao::class);

        /** @var \App\Models\User $user */
        $user = auth()->user();

        $data = $request->validate([
            'avaliacao_id'  => 'required|exists:avaliacoes,id',
            'tipo'          => 'required|in:reabertura,contestacao',
            'justificativa' => 'required|string|min:20|max:1000',
        ], [
            'justificativa.min' => 'A justificativa deve ter pelo menos 20 caracteres.',
        ]);

        // Verificar se já existe qualquer solicitação deste tipo
        $existente = Solicitacao::where('user_id', $user->id)
            ->where('avaliacao_id', $data['avaliacao_id'])
            ->where('tipo', $data['tipo'])
            ->first();

        if ($existente) {
            $statusStr = match ($existente->status) {
                'pendente'  => 'pendente',
                'aprovada'  => 'aprovada',
                'recusada'  => 'recusada (não é possível solicitar novamente)',
                'utilizada' => 'já utilizada',
                default     => $existente->status
            };
            return back()->with('error', "Você já possui uma solicitação de {$data['tipo']} para esta avaliação com o status: {$statusStr}.");
        }

        Solicitacao::create([
            ...$data,
            'user_id'    => $user->id,
            'empresa_id' => $user->empresa_id,
            'status'     => 'pendente',
        ]);

        // Notificação in-app para o criador da avaliação
        $avaliacao = Avaliacao::find($data['avaliacao_id']);
        if ($avaliacao && $avaliacao->created_by && $avaliacao->created_by !== $user->id) {
            \App\Models\Atividade::create([
                'user_id'    => $avaliacao->created_by,
                'empresa_id' => $user->empresa_id,
                'icone'      => 'fa-solid fa-paper-plane',
                'cor'        => '#8b5cf6',
                'titulo'     => 'Nova Solicitação',
                'descricao'  => $user->name . ' enviou uma solicitação de reabertura na avaliação: ' . $avaliacao->titulo,
                'url'        => route('solicitacoes.index'),
                'lida'       => false,
            ]);

            app(\App\Services\EmailService::class)->notificar(
                $avaliacao->created_by,
                "Nova Solicitação de {$data['tipo']}",
                "O colaborador {$user->name} enviou uma solicitação de {$data['tipo']} referente à avaliação '{$avaliacao->titulo}'.\n\nJustificativa: {$data['justificativa']}",
                route('solicitacoes.index'),
                'Ver Solicitação'
            );
        }

        return back()->with('success', 'Solicitação enviada com sucesso! O gestor responsável será notificado.');
    }

    /**
     * Master/Gestor aprova uma solicitação.
     */
    public function aprovar(Request $request, Solicitacao $solicitacao)
    {
        $this->authorize('aprovar', $solicitacao);

        DB::transaction(function () use ($solicitacao) {
            $solicitacao->update(['status' => 'aprovada']);

            // Se for reabertura de avaliação 360: voltar as participações para 'pendente'
            if ($solicitacao->tipo === 'reabertura') {
                AvaliacaoParticipante::where('avaliacao_id', $solicitacao->avaliacao_id)
                    ->where('avaliador_id', $solicitacao->user_id)
                    ->update(['status' => 'pendente']);
            }

            // Se for reabertura do DISC: apagar o assessment para o usuário poder refazer
            if ($solicitacao->tipo === 'reabertura_disc' && $solicitacao->disc_assessment_id) {
                \App\Models\DiscAssessment::where('id', $solicitacao->disc_assessment_id)->delete();
            }

            // Montar descrição da notificação dinamicamente
            $descricao = $solicitacao->tipo === 'reabertura_disc'
                ? 'Sua solicitação para refazer o Teste DISC foi APROVADA. Você já pode realizá-lo novamente!'
                : 'Sua solicitação de reabertura foi APROVADA na avaliação: ' . optional($solicitacao->avaliacao)->titulo;

            $url = $solicitacao->tipo === 'reabertura_disc'
                ? route('disc.take')
                : route('avaliacoes.minhas');

            // Notificar o colaborador que a solicitação foi aprovada
            Atividade::create([
                'user_id'    => $solicitacao->user_id,
                'empresa_id' => $solicitacao->empresa_id,
                'icone'      => 'fa-solid fa-check',
                'cor'        => '#10b981',
                'titulo'     => 'Solicitação Aprovada',
                'descricao'  => $descricao,
                'url'        => $url,
                'lida'       => false,
            ]);
        });

        return back()->with('success', 'Solicitação aprovada. O colaborador foi notificado.');
    }

    /**
     * Master/Gestor recusa uma solicitação com motivo obrigatório (RF-79).
     */
    public function recusar(Request $request, Solicitacao $solicitacao)
    {
        $this->authorize('recusar', $solicitacao);

        $data = $request->validate([
            'resposta_rh' => 'required|string|min:10|max:500',
        ], [
            'resposta_rh.required' => 'O motivo da recusa é obrigatório.',
            'resposta_rh.min'      => 'O motivo deve ter pelo menos 10 caracteres.',
        ]);

        DB::transaction(function () use ($solicitacao, $data) {
            $solicitacao->update([
                'status'      => 'recusada',
                'resposta_rh' => $data['resposta_rh'],
            ]);

            // Notificar o colaborador com o motivo da recusa
            Atividade::create([
                'user_id'    => $solicitacao->user_id,
                'empresa_id' => $solicitacao->empresa_id,
                'icone'      => 'fa-solid fa-ban',
                'cor'        => '#ef4444',
                'titulo'     => 'Solicitação Recusada',
                'descricao'  => 'Sua solicitação de reabertura foi RECUSADA. Motivo: ' . $data['resposta_rh'],
                'url'        => route('solicitacoes.index'),
                'lida'       => false,
            ]);
        });

        return back()->with('success', 'Solicitação recusada. O colaborador foi notificado.');
    }
}
