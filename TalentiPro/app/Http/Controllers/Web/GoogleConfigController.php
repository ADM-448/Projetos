<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfiguracaoGoogle;

class GoogleConfigController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id ?? 1;
        $config = ConfiguracaoGoogle::where('empresa_id', $empresaId)->first();
        return view('configuracoes.google', compact('config'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_account_json' => 'nullable|string',
            'drive_folder_id' => 'nullable|string'
        ]);

        $empresaId = auth()->user()->empresa_id ?? 1;
        $config = ConfiguracaoGoogle::firstOrNew(['empresa_id' => $empresaId]);

        // Validate JSON
        if ($request->filled('service_account_json')) {
            $json = json_decode($request->service_account_json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()->back()->with('error', 'O JSON da Service Account é inválido.');
            }
        }

        $config->service_account_json = $request->service_account_json;
        $config->drive_folder_id = $request->drive_folder_id;
        $config->save();

        return redirect()->route('configuracoes.google')->with('success', 'Configurações do Google salvas com sucesso!');
    }
}
