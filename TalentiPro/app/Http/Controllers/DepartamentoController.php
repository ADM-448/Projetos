<?php

namespace App\Http\Controllers;

use App\Contracts\DepartamentoServiceContract;
use App\Enums\UserRole;
use App\Http\Requests\StoreDepartamentoRequest;
use App\Http\Requests\UpdateDepartamentoRequest;
use App\Models\Departamento;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Controller de Departamentos.
 *
 * Responsabilidade única: receber a request, delegar ao Service e retornar a response.
 * Toda lógica de negócio está em DepartamentoService.
 */
class DepartamentoController extends Controller
{
    public function __construct(
        private readonly DepartamentoServiceContract $departamentoService
    ) {}

    /**
     * Lista os departamentos da empresa logada.
     * Eager loading para evitar N+1.
     */
    public function index(): View
    {
        $departamentos = Departamento::with('gestor')
            ->withCount('colaboradores')
            ->get();

        $gestores = User::where('empresa_id', auth()->user()->empresa_id)
            ->whereIn('perfil_acesso', [UserRole::GESTOR, UserRole::MASTER])
            ->with('cargo')
            ->get();

        return view('departamentos.index', compact('departamentos', 'gestores'));
    }

    public function show(int $id): RedirectResponse
    {
        return redirect()->route('departamentos.index');
    }

    public function edit(int $id): RedirectResponse
    {
        return redirect()->route('departamentos.index');
    }

    /**
     * Cria um departamento via Form Request + Service.
     */
    public function store(StoreDepartamentoRequest $request): RedirectResponse
    {
        $this->departamentoService->criar($request->validated());

        return redirect()->route('departamentos.index')
            ->with('success', 'Departamento criado com sucesso!');
    }

    /**
     * Atualiza um departamento via Form Request + Service.
     */
    public function update(UpdateDepartamentoRequest $request, int $id): RedirectResponse
    {
        $departamento = Departamento::findOrFail($id);

        $this->departamentoService->atualizar($departamento, $request->validated());

        return redirect()->route('departamentos.index')
            ->with('success', 'Departamento atualizado com sucesso!');
    }

    /**
     * Remove um departamento via Service (que valida as regras de negócio).
     */
    public function destroy(int $id): RedirectResponse
    {
        $departamento = Departamento::findOrFail($id);

        try {
            $this->departamentoService->excluir($departamento);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()->route('departamentos.index')
            ->with('success', 'Departamento excluído com sucesso!');
    }
}
