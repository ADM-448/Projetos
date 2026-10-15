<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConsultorContextController extends Controller
{
    /**
     * Exibe o hub de seleção de empresas do Consultor.
     */
    public function index(): View
    {
        $user = auth()->user();

        if (! $user || ! $user->isConsultor()) {
            abort(403, 'Acesso exclusivo para Consultores.');
        }

        $empresas = $user->empresasConsultoria()
            ->withCount([
                'usuarios as total_colaboradores' => function ($q) {
                    $q->where('status', 'ativo');
                }
            ])
            ->orderBy('nome')
            ->get();

        return view('consultor.empresas', compact('empresas', 'user'));
    }

    /**
     * Alterna a empresa ativa para o usuário Consultor.
     */
    public function selecionarEmpresa(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if (! $user || ! $user->isConsultor()) {
            abort(403, 'Apenas usuários com perfil Consultor podem alternar empresas.');
        }

        $request->validate([
            'empresa_id' => 'required|integer|exists:empresas,id',
        ], [
            'empresa_id.required' => 'Selecione uma empresa válida.',
            'empresa_id.exists'   => 'A empresa selecionada não foi encontrada.',
        ]);

        $empresaId = (int) $request->input('empresa_id');

        // Valida se o consultor realmente possui permissão para esta empresa
        $temPermissao = $user->empresasConsultoria()
            ->where('empresas.id', $empresaId)
            ->exists();

        if (! $temPermissao) {
            abort(403, 'Você não possui autorização para acessar esta empresa.');
        }

        $empresa = Empresa::findOrFail($empresaId);

        // Atualiza a empresa_id ativa do usuário
        $user->update(['empresa_id' => $empresaId]);
        session(['empresa_ativa_id' => $empresaId]);

        return redirect()->route('dashboard')->with('success', "Contexto alterado com sucesso para a empresa: {$empresa->nome}");
    }
}
