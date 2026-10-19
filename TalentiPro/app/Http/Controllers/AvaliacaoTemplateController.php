<?php

namespace App\Http\Controllers;

use App\Models\Avaliacao;
use App\Models\Competencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AvaliacaoTemplateController extends Controller
{
    public function index()
    {
        return redirect()->route('avaliacoes.index', ['tab' => 'templates']);
    }

    public function create()
    {
        Gate::authorize('create', Avaliacao::class);

        $competencias = Competencia::where('empresa_id', auth()->user()->empresa_id)->get();
        $avaliacao = new Avaliacao();

        return view('avaliacoes.templates.create', compact('competencias', 'avaliacao'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Avaliacao::class);

        $request->validate([
            'titulo' => 'required|string|max:255',
            'grau' => 'required|string|in:90,180,360',
            'descricao' => 'nullable|string',
            'competencias' => 'required|array',
            'competencias.*' => 'exists:competencias,id',
            'pesos' => 'nullable|array',
        ]);

        $template = Avaliacao::create([
            'empresa_id' => auth()->user()->empresa_id,
            'titulo' => $request->titulo,
            'grau' => $request->grau,
            'descricao' => $request->descricao,
            'is_template' => true,
            'status' => 'rascunho', // Um template não é uma campanha ativa
            'created_by' => auth()->id(),
        ]);

        $competenciasSync = [];
        $pesosInput = $request->input('pesos', []);
        foreach ($request->competencias as $compId) {
            $peso = isset($pesosInput[$compId]) && is_numeric($pesosInput[$compId]) ? max(1, (int)$pesosInput[$compId]) : 1;
            $competenciasSync[$compId] = ['peso' => $peso];
        }

        $template->competencias()->sync($competenciasSync);

        return redirect()->route('avaliacoes.index', ['tab' => 'templates'])->with('success', 'Modelo de Avaliação criado com sucesso! Agora você pode delegar os participantes.');
    }

    public function show(Avaliacao $template)
    {
        Gate::authorize('view', $template);

        if (!$template->is_template) {
            abort(404, 'Modelo não encontrado.');
        }

        $template->load(['competencias.skills']);
        return view('avaliacoes.templates.show', compact('template'));
    }

    public function edit(Avaliacao $template)
    {
        Gate::authorize('update', $template);

        if (!$template->is_template) {
            abort(404, 'Modelo não encontrado.');
        }

        $template->load('competencias');
        $competencias = Competencia::where('empresa_id', auth()->user()->empresa_id)->get();
        return view('avaliacoes.templates.edit', ['avaliacao' => $template, 'competencias' => $competencias]);
    }

    public function update(Request $request, Avaliacao $template)
    {
        Gate::authorize('update', $template);

        if (!$template->is_template) {
            abort(404, 'Modelo não encontrado.');
        }

        $request->validate([
            'titulo' => 'required|string|max:255',
            'grau' => 'required|string|in:90,180,360',
            'descricao' => 'nullable|string',
            'competencias' => 'required|array',
            'competencias.*' => 'exists:competencias,id',
            'pesos' => 'nullable|array',
        ]);

        $template->update([
            'titulo' => $request->titulo,
            'grau' => $request->grau,
            'descricao' => $request->descricao,
        ]);

        $competenciasSync = [];
        $pesosInput = $request->input('pesos', []);
        foreach ($request->competencias as $compId) {
            $peso = isset($pesosInput[$compId]) && is_numeric($pesosInput[$compId]) ? max(1, (int)$pesosInput[$compId]) : 1;
            $competenciasSync[$compId] = ['peso' => $peso];
        }

        $template->competencias()->sync($competenciasSync);

        return redirect()->route('avaliacoes.index', ['tab' => 'templates'])->with('success', 'Modelo de Avaliação atualizado com sucesso!');
    }

    public function destroy(Avaliacao $template)
    {
        Gate::authorize('delete', $template);

        if (!$template->is_template) {
            abort(403, 'Apenas modelos podem ser excluídos por esta rota.');
        }

        $template->competencias()->detach();
        $template->delete();

        return redirect()->route('avaliacoes.index', ['tab' => 'templates'])->with('success', 'Modelo excluído com sucesso!');
    }
}
