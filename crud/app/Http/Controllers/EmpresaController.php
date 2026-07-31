<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmpresaRequest;
use App\Models\Empresa;
use Illuminate\Http\Request;

class EmpresaController
{
    public function index(){
        $empresas = Empresa::with('empresa')->latest()->paginate(10);
        return view('empresas.index', compact('empresas'));
    }

    public function create(){
        return view('vagas.create');
    }

    public function store(EmpresaRequest $request){
        //Validação
        $ddEmpresa = $request->validated();

        //salvar
        Empresa::create($ddEmpresa);
        return redirect()->route('empresa.index')->with('sucess','Empresa Cadastrada');
    }

    public function edit(Empresa $empresa){
        return view('Empresa.edit', compact('empresa'));
    }

    public function update(EmpresaRequest $request, Empresa $empresa){
        
        $ddEmpresa = $request->validated();
        $empresa->update($ddEmpresa);

        return redirect()->route('empresa.index')->with('sucess','Empresa Atualizada');
    }

    public function destroy(Empresa $empresa){
        $empresa->delete();
        return redirect()->back()->with('sucess','Empresa Deletada');
    }
}
