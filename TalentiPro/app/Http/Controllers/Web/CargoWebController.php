<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\Departamento;
use App\Models\Competencia;
use Illuminate\Http\Request;

class CargoWebController extends Controller
{
    public function index()
    {
        $cargos = Cargo::with('departamento')->withCount('users')->get();
        return view('cargos.index', compact('cargos'));
    }

    public function create()
    {
        $departamentos = Departamento::all();
        $competencias = Competencia::all();
        // Precisamos listar os cargos para escolher o "Cargo Superior" (Hierarquia)
        $cargosSuperiores = Cargo::all();

        return view('cargos.form', compact('departamentos', 'competencias', 'cargosSuperiores'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'departamento_id' => 'required|exists:departamentos,id',
            'cbo' => 'nullable|string|max:20',             // <-- Novo
            'senioridade' => 'nullable|string',             // <-- Novo
            'cargo_superior_id' => 'nullable|exists:cargos,id', // <-- Novo
            'descricao' => 'nullable|string',
            'competencias' => 'nullable|array',
        ]);

        // Se você não tiver empresa_id no User, remova ou comente esta linha:
        // $validated['empresa_id'] = auth()->user()->empresa_id ?? 1;

        $cargo = Cargo::create($validated);

        if (!empty($request->competencias)) {
            $cargo->competencias()->sync($request->competencias);
        }

        return redirect()->route('cargos.index')->with('success', 'Cargo criado com sucesso!');
    }

    public function edit($id)
    {
        $cargo = Cargo::with('competencias')->findOrFail($id);
        $departamentos = Departamento::all();
        $competencias = Competencia::all();
        // Lista todos exceto ele mesmo (não pode ser chefe de si mesmo)
        $cargosSuperiores = Cargo::where('id', '!=', $id)->get();

        return view('cargos.form', compact('cargo', 'departamentos', 'competencias', 'cargosSuperiores'));
    }

    public function update(Request $request, $id)
    {
        $cargo = Cargo::findOrFail($id);

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'departamento_id' => 'required|exists:departamentos,id',
            'cbo' => 'nullable|string|max:20',
            'senioridade' => 'nullable|string',
            'cargo_superior_id' => 'nullable|exists:cargos,id',
            'descricao' => 'nullable|string',
            'competencias' => 'nullable|array',
        ]);

        $cargo->update($validated);

        if (isset($request->competencias)) {
            $cargo->competencias()->sync($request->competencias);
        }

        return redirect()->route('cargos.index')->with('success', 'Cargo atualizado!');
    }

    public function destroy($id)
    {
        $cargo = Cargo::findOrFail($id);

        if($cargo->users()->count() > 0) {
            return back()->withErrors(['erro' => 'Não é possível excluir: Existem colaboradores neste cargo.']);
        }

        // Opcional: Verificar se tem subordinados antes de excluir
        if($cargo->subordinados()->count() > 0) {
             return back()->withErrors(['erro' => 'Não é possível excluir: Este cargo possui cargos subordinados a ele.']);
        }

        $cargo->delete();
        return redirect()->route('cargos.index')->with('success', 'Cargo excluído.');
    }
}