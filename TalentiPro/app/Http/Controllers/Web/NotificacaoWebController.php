<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Notificacao;
use Illuminate\Http\Request;

class NotificacaoWebController extends Controller
{
    public function index()
    {
        $notificacoes = Notificacao::where('destinatario_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();
            
        return response()->json($notificacoes);
    }

    public function ler($id)
    {
        $notificacao = Notificacao::where('id', $id)->where('destinatario_id', auth()->id())->firstOrFail();
        $notificacao->update(['lida' => true]);
        return response()->json(['success' => true]);
    }
    
    public function count()
    {
        $count = Notificacao::where('destinatario_id', auth()->id())->where('lida', false)->count();
        return response()->json(['count' => $count]);
    }

    public function lerTodas()
    {
        Notificacao::where('destinatario_id', auth()->id())->update(['lida' => true]);
        return response()->json(['success' => true]);
    }
}
