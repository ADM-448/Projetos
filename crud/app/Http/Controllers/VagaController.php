<?php

namespace App\Http\Controllers;

use App\Http\Requests\VagaRequest;
use App\Models\Vaga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VagaController extends Controller
{
    /**
     * Exibe a listagem de vagas.
     */
    public function index()
    {
        $vagas = Vaga::with(['empresa', 'user'])->latest()->paginate(10);
        return view('vagas.index', compact('vagas'));
    }

    /**
     * Exibe o formulário de criação de vaga.
     */
    public function create()
    {
        return view('vagas.create');
    }

    /**
     * Armazena uma nova vaga.
     */
    public function store(VagaRequest $request)
    {
        $dados = $request->validated();
        $dados['ativa'] = $request->has('ativa');
        $dados['user_id'] = Auth::id();

        Vaga::create($dados);

        return redirect()->route('vagas.index')->with('sucess', 'Vaga criada com sucesso!');
    }

    /**
     * Exibe os detalhes de uma vaga específica.
     */
    public function show(Vaga $vaga)
    {
        return view('vagas.show', compact('vaga'));
    }

    /**
     * Exibe o formulário de edição de vaga.
     */
    public function edit(Vaga $vaga)
    {
        return view('vagas.edit', compact('vaga'));
    }

    /**
     * Atualiza uma vaga existente.
     */
    public function update(VagaRequest $request, Vaga $vaga)
    {
        $dados = $request->validated();
        $dados['ativa'] = $request->has('ativa');

        $vaga->update($dados);

        return redirect()->route('vagas.index')->with('sucess', 'Vaga atualizada com sucesso!');
    }

    /**
     * Remove uma vaga.
     */
    public function destroy(Vaga $vaga)
    {
        $vaga->delete();

        return redirect()->route('vagas.index')->with('sucess', 'Vaga removida com sucesso!');
    }
}
