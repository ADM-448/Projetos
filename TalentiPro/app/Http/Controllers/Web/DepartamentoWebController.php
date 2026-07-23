<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\User;
use Illuminate\Http\Request;

class DepartamentoWebController extends Controller
{
    public function index()
    {
        // Traz departamentos com o gestor (se houver) e contagem de users per departamento
        $departamentos = Departamento::with('gestor')->withCount('users')->get();

        // Traz usuários elegíveis para serem gestores (para o Modal de Criar/Editar)
        // User pediu SÓ GESTOR.
        $gestores = User::where('perfil_acesso', 'gestor')->with('cargo')->get();

        return view('departamentos.index', compact('departamentos', 'gestores'));
    }

    public function show($id)
    {
        return redirect()->route('departamentos.index');
    }

    public function edit($id)
    {
        return redirect()->route('departamentos.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'gestor_id' => 'nullable|exists:users,id',
        ]);

        // Se você não tiver empresa_id no User table ainda, remova essa linha ou deixe nullable no banco
        // $validated['empresa_id'] = auth()->user()->empresa_id ?? 1;

        Departamento::create($validated);

        return redirect()->route('departamentos.index')->with('success', 'Departamento criado!');
    }

    public function update(Request $request, $id)
    {
        // Tenta buscar com o escopo (empresa do usuário)
        $departamento = Departamento::find($id);

        if (!$departamento) {
            // Se não achou com o escopo, verifica se ele existe sem o escopo (outra empresa)
            $existeEmOutraEmpresa = Departamento::withoutGlobalScope('empresa')->find($id);
            if ($existeEmOutraEmpresa) {
                return back()->withErrors(['erro' => 'Acesso negado: Este departamento pertence a outra empresa.']);
            }
            abort(404, 'Departamento não encontrado.');
        }

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'gestor_id' => 'nullable|exists:users,id',
        ]);

        $departamento->update($validated);

        return redirect()->route('departamentos.index')->with('success', 'Departamento atualizado!');
    }

    public function destroy($id)
    {
        $departamento = Departamento::find($id);

        if (!$departamento) {
            abort(404);
        }

        // Opcional: Verificar se tem usuários antes de apagar para não deixar órfãos
        if($departamento->users()->count() > 0) {
            return back()->withErrors(['erro' => 'Não é possível excluir: Existem colaboradores neste departamento.']);
        }

        $departamento->delete();

        return redirect()->route('departamentos.index')->with('success', 'Departamento excluído.');
    }
}