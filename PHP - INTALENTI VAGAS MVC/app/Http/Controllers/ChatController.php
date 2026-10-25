<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\MensagemChat;
use App\Models\Notificacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function show($id)
    {
        $chat = Chat::with(['vaga.empresa', 'candidato', 'recrutador', 'mensagens.remetente'])->findOrFail($id);
        $this->authorizeChatAccess($chat);

        $user = Auth::user();

        // Marca mensagens recebidas como lidas
        MensagemChat::where('chat_id', $chat->id)
            ->where('remetente_id', '!=', $user->id)
            ->where('lida', false)
            ->update(['lida' => true]);

        // Marca notificações de chat para esta conversa como lidas
        Notificacao::where('user_id', $user->id)
            ->where('link', route('chat.show', $chat->id))
            ->where('lida', false)
            ->update(['lida' => true]);

        // Determina quem é o interlocutor
        $isCompany = ($user->id === $chat->recrutador_id || $user->isEmpresa());
        $nomeInterlocutor = $isCompany ? $chat->candidato->name : ($chat->vaga->empresa->nome_empresa ?? 'Empresa');

        // Regra de Negócio: O chat só é liberado para o candidato após a empresa enviar a primeira mensagem
        $mensagensEmpresaCount = $chat->mensagens->where('remetente_id', '!=', $chat->candidato_user_id)->count();
        $chatLiberado = $isCompany || ($mensagensEmpresaCount > 0);

        return view('chat.show', compact('chat', 'nomeInterlocutor', 'isCompany', 'chatLiberado'));
    }

    public function enviarMensagem(Request $request, $id)
    {
        $chat = Chat::findOrFail($id);
        $this->authorizeChatAccess($chat);

        $user = Auth::user();
        $isCompany = ($user->id === $chat->recrutador_id || $user->isEmpresa());

        // Regra: Candidato não pode iniciar a conversa se a empresa ainda não mandou mensagem
        if (!$isCompany) {
            $mensagensEmpresaCount = MensagemChat::where('chat_id', $chat->id)
                ->where('remetente_id', '!=', $chat->candidato_user_id)
                ->count();

            if ($mensagensEmpresaCount === 0) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Aguarde o recrutador iniciar a conversa.'
                    ], 403);
                }
                return back()->with('error', 'Aguarde o recrutador iniciar a conversa.');
            }
        }

        $request->validate([
            'mensagem' => ['required', 'string', 'max:2000'],
        ]);

        $mensagem = MensagemChat::create([
            'chat_id' => $chat->id,
            'remetente_id' => Auth::id(),
            'mensagem' => trim($request->input('mensagem')),
            'lida' => false,
        ]);

        // Dispara notificação para o destinatário da mensagem
        $destinatarioId = ($user->id === $chat->candidato_user_id) ? $chat->recrutador_id : $chat->candidato_user_id;
        $chat->loadMissing('vaga');
        $nomeRemetente = $user->primeiro_nome ?: $user->name;
        $vagaTitulo = $chat->vaga->titulo ?? 'Vaga';

        Notificacao::create([
            'user_id' => $destinatarioId,
            'titulo' => 'Nova Mensagem no Chat',
            'mensagem' => "{$nomeRemetente} enviou uma nova mensagem sobre a vaga '{$vagaTitulo}'.",
            'link' => route('chat.show', $chat->id),
            'lida' => false,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'mensagem' => [
                    'id' => $mensagem->id,
                    'texto' => $mensagem->mensagem,
                    'remetente' => Auth::user()->name,
                    'e_meu' => true,
                    'hora' => $mensagem->created_at->format('H:i'),
                ],
            ]);
        }

        return back();
    }

    public function mensagensJson($id, Request $request)
    {
        $chat = Chat::findOrFail($id);
        $this->authorizeChatAccess($chat);

        $ultimoId = (int) ($request->query('ultimo_id') ?? $request->query('last_id') ?? 0);

        $mensagens = MensagemChat::with('remetente')
            ->where('chat_id', $chat->id)
            ->where('id', '>', $ultimoId)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'texto' => $m->mensagem,
                    'remetente' => $m->remetente->name,
                    'e_meu' => $m->remetente_id === Auth::id(),
                    'hora' => $m->created_at->format('H:i'),
                ];
            });

        $mensagensEmpresaCount = MensagemChat::where('chat_id', $chat->id)
            ->where('remetente_id', '!=', $chat->candidato_user_id)
            ->count();
        $isCompany = (Auth::id() === $chat->recrutador_id || Auth::user()->isEmpresa());
        $chatLiberado = $isCompany || ($mensagensEmpresaCount > 0);

        return response()->json([
            'mensagens' => $mensagens,
            'chat_liberado' => $chatLiberado,
        ]);
    }

    protected function authorizeChatAccess(Chat $chat)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->id === $chat->candidato_user_id || $user->id === $chat->recrutador_id) {
            return true;
        }

        // Se for admin da empresa que postou a vaga
        if ($user->isEmpresa()) {
            $empresaIds = $user->empresas()->pluck('empresas.id')->toArray();
            if (in_array($chat->vaga->empresa_id, $empresaIds)) {
                return true;
            }
        }

        abort(403, 'Acesso não autorizado a este chat.');
    }
}
