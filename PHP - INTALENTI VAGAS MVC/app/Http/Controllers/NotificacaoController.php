<?php

namespace App\Http\Controllers;

use App\Models\Notificacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacaoController extends Controller
{
    /**
     * Marca uma notificação específica como lida e redireciona para o link de destino.
     */
    public function abrir($id)
    {
        $notificacao = Notificacao::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $notificacao->update(['lida' => true]);

        if (!empty($notificacao->link)) {
            return redirect($notificacao->link);
        }

        return back();
    }

    /**
     * Marca todas as notificações do usuário logado como lidas.
     */
    public function marcarTodasLidas(Request $request)
    {
        Notificacao::where('user_id', Auth::id())
            ->where('lida', false)
            ->update(['lida' => true]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Todas as notificações foram marcadas como lidas.'
            ]);
        }

        return back()->with('success', 'Todas as notificações foram marcadas como lidas.');
    }
}
