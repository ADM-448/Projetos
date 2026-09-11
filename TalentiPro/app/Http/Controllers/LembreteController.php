<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lembrete;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LembreteController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'lembreteable_type' => 'required|string',
            'lembreteable_id'   => 'required|integer',
            'dias'              => 'required|integer|min:1',
            'mensagem'          => 'required|string|max:255',
        ]);

        $dataAgendada = Carbon::now()->addDays($request->dias)->startOfDay();

        Lembrete::create([
            'user_id'           => Auth::id(),
            'lembreteable_type' => $request->lembreteable_type,
            'lembreteable_id'   => $request->lembreteable_id,
            'data_agendada'     => $dataAgendada,
            'mensagem'          => $request->mensagem,
            'status'            => 'pendente',
        ]);

        return back()->with('success', 'Lembrete agendado com sucesso!');
    }
}
