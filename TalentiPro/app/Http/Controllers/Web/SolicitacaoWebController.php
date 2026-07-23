<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Solicitacao;
use App\Models\AvaliacaoParticipante;
use App\Models\Avaliacao;
use App\Models\Notificacao;
use Illuminate\Http\Request;

class SolicitacaoWebController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Eager load relationships including creator
        $query = Solicitacao::with(['user.departamento', 'user.gestor', 'avaliacao.creator']);

        if ($user->perfil_acesso === 'gestor') {
            // Gestor vê apenas solicitações de quem ele gerencia (seus subordinados diretos ou do mesmo departamento)
            // Lógica: User que fez a solicitação tem gestor_id == eu
            $query->whereHas('user', function($q) use ($user) {
                $q->where('gestor_id', $user->id);
            });
        }

        $solicitacoes = $query->orderByRaw("FIELD(status, 'pendente', 'aprovada', 'recusada')")
            ->orderBy('created_at', 'desc')
            ->get();

        return view('avaliacoes.solicitacoes.index', compact('solicitacoes'));
    }

    public function aprovar(Request $request, $id)
    {
        $solicitacao = Solicitacao::with('avaliacao')->findOrFail($id);
        $user = auth()->user();

        // Permission check: only Master or evaluation creator can approve
        if ($user->perfil_acesso !== 'master' && $user->perfil_acesso !== 'admin') {
            if ($solicitacao->avaliacao->created_by !== $user->id) {
                return redirect()->back()->with('error', 'Você não tem permissão para aprovar esta solicitação.');
            }
        }

        $solicitacao->status = 'aprovada';
        $solicitacao->save();

        if ($solicitacao->tipo === 'reabertura') {
            // Reabrir a avaliação para o colaborador
            // Isso significa mudar o status de 'realizada' para 'pendente' na tabela avaliacao_participantes
            // Precisamos encontrar a participação dele (como autoavaliação ou todas as pendências dele?)
            // Geralmente ele pede para refazer a autoavaliação ou então ele pede para refazer a avaliação DE ALGUEM.
            // Vamos assumir que se ele pediu reabertura da "Avaliação X", é a participação dele nela.
            
            // Buscar participação DELE como avaliador ou autoavaliador
            // Se for reabertura de autoavaliação:
            $participacao = AvaliacaoParticipante::where('avaliacao_id', $solicitacao->avaliacao_id)
                ->where('avaliador_id', $solicitacao->user_id) // Ele é quem responde
                // ->where('tipo', 'autoavaliacao') // Nem sempre, pode ser que ele queira refazer uma avaliação de um par
                ->get();

            // Reseta todas as participações dele nesta avaliação para pendente?
            // Ou apenas a auto? Pela simplicidade, vamos reabrir TODAS as que ele fez nessa avaliação ou só a auto.
            // O texto do html diz: "Acredito que houve um erro no cálculo...".
            // Vamos reabrir TUDO que ele preencheu nessa avaliação.
            
            foreach($participacao as $p) {
                $p->status = 'pendente';
                $p->save();
            }
        }

        return redirect()->back()->with('success', 'Solicitação aprovada e avaliação reaberta.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'avaliacao_id' => 'required',
            'tipo' => 'required',
            'justificativa' => 'required'
        ]);

        $data['user_id'] = auth()->id();
        $data['status'] = 'pendente';

        Solicitacao::create($data);

        $avaliacao = Avaliacao::find($request->avaliacao_id);
        if ($avaliacao && $avaliacao->created_by && $avaliacao->created_by != auth()->id()) {
            Notificacao::create([
                'empresa_id' => auth()->user()->empresa_id,
                'destinatario_id' => $avaliacao->created_by,
                'remetente_id' => auth()->id(),
                'tipo' => 'solicitacao',
                'mensagem' => 'Nova solicitação de alteração na avaliação: ' . $avaliacao->titulo,
                'link' => route('solicitacoes.index')
            ]);
        }

        return redirect()->back()->with('success', 'Solicitação enviada ao RH/Gestor.');
    }

    public function recusar(Request $request, $id)
    {
        $solicitacao = Solicitacao::with('avaliacao')->findOrFail($id);
        $user = auth()->user();

        // Permission check: only Master or evaluation creator can deny
        if ($user->perfil_acesso !== 'master' && $user->perfil_acesso !== 'admin') {
            if ($solicitacao->avaliacao->created_by !== $user->id) {
                return redirect()->back()->with('error', 'Você não tem permissão para recusar esta solicitação.');
            }
        }

        $solicitacao->status = 'recusada';
        // $solicitacao->resposta_rh = $request->motivo; // Se quiser salvar motivo
        $solicitacao->save();

        return redirect()->back()->with('success', 'Solicitação recusada.');
    }
}
