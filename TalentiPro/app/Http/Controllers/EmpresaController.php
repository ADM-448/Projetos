<?php

namespace App\Http\Controllers;

use App\Contracts\EmpresaServiceContract;
use App\Http\Requests\StoreEmpresaRequest;
use App\Http\Requests\UpdateEmpresaRequest;
use App\Models\Empresa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EmpresaController extends Controller
{
    protected EmpresaServiceContract $empresaService;

    public function __construct(EmpresaServiceContract $empresaService)
    {
        $this->empresaService = $empresaService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', Empresa::class);
        $empresas = Empresa::withCount('usuarios')->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.empresas.index', compact('empresas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Gate::authorize('create', Empresa::class);
        return view('admin.empresas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmpresaRequest $request): RedirectResponse
    {
        Gate::authorize('create', Empresa::class);

        // Separa dados da Empresa dos dados do Usuário Master
        $dadosEmpresa = $request->safe()->only([
            'nome', 'cnpj', 'razao_social', 'email', 'telefone', 'max_usuarios', 'status'
        ]);
        
        $dadosMaster = [
            'name' => $request->validated('master_name'),
            'email' => $request->validated('master_email'),
            'password' => $request->validated('master_password'),
        ];

        $this->empresaService->criarComMaster($dadosEmpresa, $dadosMaster);

        return redirect()->route('admin.empresas.index')
                         ->with('success', 'Empresa e usuário Master criados com sucesso.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Empresa $empresa): View
    {
        Gate::authorize('update', $empresa);
        return view('admin.empresas.edit', compact('empresa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmpresaRequest $request, Empresa $empresa): RedirectResponse
    {
        Gate::authorize('update', $empresa);

        $dados = $request->validated();

        if ($request->hasFile('logo')) {
            if ($empresa->logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($empresa->logo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($empresa->logo);
            }
            $dados['logo'] = $request->file('logo')->store('empresas/logos', 'public');
        } elseif ($request->boolean('remover_logo')) {
            if ($empresa->logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($empresa->logo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($empresa->logo);
            }
            $dados['logo'] = null;
        }

        if ($request->hasFile('marca_dagua')) {
            if ($empresa->marca_dagua && \Illuminate\Support\Facades\Storage::disk('public')->exists($empresa->marca_dagua)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($empresa->marca_dagua);
            }
            $dados['marca_dagua'] = $request->file('marca_dagua')->store('empresas/watermarks', 'public');
        } elseif ($request->boolean('remover_marca_dagua')) {
            if ($empresa->marca_dagua && \Illuminate\Support\Facades\Storage::disk('public')->exists($empresa->marca_dagua)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($empresa->marca_dagua);
            }
            $dados['marca_dagua'] = null;
        }

        $this->empresaService->atualizar($empresa, $dados);

        return redirect()->route('admin.empresas.index')
                         ->with('success', 'Dados da empresa e personalização visual atualizados com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Empresa $empresa): RedirectResponse
    {
        Gate::authorize('delete', $empresa);

        $this->empresaService->excluir($empresa);

        return redirect()->route('admin.empresas.index')
                         ->with('success', 'Empresa excluída com sucesso.');
    }
}
