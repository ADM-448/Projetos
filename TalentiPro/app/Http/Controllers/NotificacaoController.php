<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Atividade;
use Carbon\Carbon;

class NotificacaoController extends Controller
{
    public function ler($id)
    {
        $atividade = Atividade::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (!$atividade->lida) {
            $atividade->update([
                'lida' => true,
                'read_at' => Carbon::now()
            ]);
        }

        if ($atividade->url) {
            return redirect($atividade->url);
        }

        return redirect()->back();
    }

    public function lerTodas()
    {
        auth()->user()->atividades()
            ->where('lida', false)
            ->update([
                'lida' => true,
                'read_at' => Carbon::now()
            ]);

        return redirect()->back()->with('success', 'Todas as notificações foram marcadas como lidas.');
    }
}
