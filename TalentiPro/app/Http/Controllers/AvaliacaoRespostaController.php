<?php

namespace App\Http\Controllers;

use App\Contracts\AvaliacaoServiceContract;
use App\Http\Requests\StoreRespostaAvaliacaoRequest;
use App\Models\Atividade;
use App\Models\AvaliacaoParticipante;
use App\Models\Solicitacao;
use App\Services\AtividadeService;
use Carbon\Carbon;

class AvaliacaoRespostaController extends Controller
{
    public function __construct(private AvaliacaoServiceContract $avaliacaoService) {}

    public function index()
    {
        $participacoes = AvaliacaoParticipante::with(['avaliacao', 'avaliado'])
            ->where('avaliador_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('avaliacoes.minhas', compact('participacoes'));
    }

    public function create(AvaliacaoParticipante $participante)
    {
        // Garante que o usuario logado é o avaliador que foi designado
        if ($participante->avaliador_id !== auth()->id()) {
            abort(403, 'Acesso não autorizado.');
        }

        $isPoderoso = auth()->user()->perfil_acesso->value === 'master' || $participante->avaliacao->created_by === auth()->id();

        if (!$isPoderoso) {
            // Verifica se o prazo encerrou
            if ($participante->avaliacao->data_fim && Carbon::parse($participante->avaliacao->data_fim)->endOfDay()->isPast()) {
                return redirect()->route('avaliacoes.minhas')->with('error', 'O prazo para responder esta avaliação já foi encerrado.');
            }

            // Bloqueia reedição se já respondido e não há solicitação de reabertura aprovada
            if ($participante->status === 'respondido') {
                $reaberturaAprovada = Solicitacao::where('user_id', auth()->id())
                    ->where('avaliacao_id', $participante->avaliacao_id)
                    ->where('tipo', 'reabertura')
                    ->where('status', 'aprovada')
                    ->exists();

                if (! $reaberturaAprovada) {
                    return redirect()->route('avaliacoes.minhas')
                        ->with('error', 'Esta avaliação já foi respondida. Solicite uma reabertura para editá-la.');
                }
            }
        }

        // Carregar a avaliação com suas competências e skills e as respostas já dadas
        $participante->load('avaliacao.competencias.skills', 'respostas');

        $respostasSalvas = $participante->respostas->pluck('nota', 'skill_id')->toArray();

        return view('avaliacoes.responder', compact('participante', 'respostasSalvas'));
    }

    public function store(StoreRespostaAvaliacaoRequest $request, AvaliacaoParticipante $participante)
    {
        if ($participante->avaliador_id !== auth()->id()) {
            abort(403, 'Acesso não autorizado.');
        }

        $isPoderoso = auth()->user()->perfil_acesso->value === 'master' || $participante->avaliacao->created_by === auth()->id();

        if (!$isPoderoso) {
            // Verifica se o prazo encerrou
            if ($participante->avaliacao->data_fim && Carbon::parse($participante->avaliacao->data_fim)->endOfDay()->isPast()) {
                return redirect()->route('avaliacoes.minhas')->with('error', 'O prazo para responder esta avaliação já foi encerrado.');
            }

            // Bloqueia reedição se já respondido e não há solicitação de reabertura aprovada
            if ($participante->status === 'respondido') {
                $solicitacaoAprovada = Solicitacao::where('user_id', auth()->id())
                    ->where('avaliacao_id', $participante->avaliacao_id)
                    ->where('tipo', 'reabertura')
                    ->where('status', 'aprovada')
                    ->first();

                if (! $solicitacaoAprovada) {
                    return redirect()->route('avaliacoes.minhas')
                        ->with('error', 'Esta avaliação já foi respondida. Solicite uma reabertura para editá-la.');
                }

                // Invalida a solicitação após uso para evitar reedições ilimitadas
                $solicitacaoAprovada->update(['status' => 'utilizada']);
            }
        }

        $this->avaliacaoService->salvarRespostas($participante, $request->validated('respostas'));

        $participante->update([
            'status' => 'respondido',
            'observacao' => $request->validated('observacao'),
        ]);

        // Verifica se ainda restam participações pendentes deste avaliador nesta mesma campanha
        $pendenciasRestantes = AvaliacaoParticipante::where('avaliacao_id', $participante->avaliacao_id)
            ->where('avaliador_id', $participante->avaliador_id)
            ->where('status', 'pendente')
            ->count();

        if ($pendenciasRestantes === 0) {
            // Se o usuário concluiu todas as suas avaliações nesta campanha, marca as atividades correspondentes como lidas
            Atividade::where('user_id', $participante->avaliador_id)
                ->where('lida', false)
                ->where(function ($q) use ($participante) {
                    $q->where('url', 'like', "%avaliacoes/{$participante->avaliacao_id}%")
                      ->orWhere('titulo', $participante->avaliacao->titulo)
                      ->orWhere('descricao', 'like', "%{$participante->avaliacao->titulo}%");
                })
                ->update([
                    'lida' => true,
                    'read_at' => Carbon::now(),
                ]);

            // Se não restar nenhuma avaliação pendente em nenhuma campanha para este usuário,
            // marca também como lida qualquer atividade com URL 'minhas-avaliacoes'
            $temOutraCampanhaPendente = AvaliacaoParticipante::where('avaliador_id', $participante->avaliador_id)
                ->where('status', 'pendente')
                ->exists();

            if (! $temOutraCampanhaPendente) {
                Atividade::where('user_id', $participante->avaliador_id)
                    ->where('lida', false)
                    ->where('url', 'like', '%minhas-avaliacoes%')
                    ->update([
                        'lida' => true,
                        'read_at' => Carbon::now(),
                    ]);
            }
        }

        // Notifica o criador da avaliação que alguém respondeu
        app(AtividadeService::class)->registrar(
            $participante->avaliacao->empresa_id,
            $participante->avaliacao->created_by,
            'Avaliação Concluída',
            "{$participante->avaliador->name} finalizou a avaliação de {$participante->avaliado->name} na campanha: {$participante->avaliacao->titulo}.",
            'fa-solid fa-check-double',
            '#38a169',
            route('avaliacoes.show', $participante->avaliacao->id)
        );

        return redirect()->route('avaliacoes.minhas')->with('success', 'Avaliação respondida com sucesso!');
    }
}
