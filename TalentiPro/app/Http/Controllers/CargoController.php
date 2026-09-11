<?php

namespace App\Http\Controllers;

use App\Contracts\CargoServiceContract;
use App\Http\Requests\StoreCargoRequest;
use App\Http\Requests\UpdateCargoRequest;
use App\Models\Cargo;
use App\Models\Departamento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller de Cargos.
 *
 * Responsabilidade única: receber a request, delegar ao Service e retornar a response.
 * Toda lógica de negócio está em CargoService.
 */
class CargoController extends Controller
{
    public function __construct(
        private readonly CargoServiceContract $cargoService
    ) {}

    /**
     * Lista os cargos da empresa logada.
     * Eager loading para evitar N+1.
     */
    public function index(): View
    {
        $cargos = Cargo::with(['departamento', 'cargoSuperior'])
            ->withCount('colaboradores')
            ->get();

        $departamentos    = Departamento::orderBy('nome')->get();
        $cargosExistentes = Cargo::orderBy('nome')->get();

        return view('cargos.index', compact('cargos', 'departamentos', 'cargosExistentes'));
    }

    public function show(int $id): RedirectResponse
    {
        return redirect()->route('cargos.index');
    }

    public function edit(int $id): RedirectResponse
    {
        return redirect()->route('cargos.index');
    }

    /**
     * Cria um cargo via Form Request + Service.
     */
    public function store(StoreCargoRequest $request): RedirectResponse
    {
        $this->cargoService->criar($request->validated());

        return redirect()->route('cargos.index')
            ->with('success', 'Cargo criado com sucesso!');
    }

    /**
     * Atualiza um cargo via Form Request + Service.
     */
    public function update(UpdateCargoRequest $request, int $id): RedirectResponse
    {
        $cargo = Cargo::findOrFail($id);

        if ($this->cargoTentaReportarASiMesmo($request, $id)) {
            return back()->withErrors(['cargo_superior_id' => 'Um cargo não pode reportar-se a si mesmo.'])->withInput();
        }

        $this->cargoService->atualizar($cargo, $request->validated());

        return redirect()->route('cargos.index')
            ->with('success', 'Cargo atualizado com sucesso!');
    }

    /**
     * Remove um cargo via Service (que valida as regras de negócio).
     */
    public function destroy(int $id): RedirectResponse
    {
        $cargo = Cargo::findOrFail($id);

        try {
            $this->cargoService->excluir($cargo);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()->route('cargos.index')
            ->with('success', 'Cargo excluído com sucesso!');
    }

    // ─────────────────────────── Guard Auxiliar ───────────────────────────────

    private function cargoTentaReportarASiMesmo(Request $request, int $id): bool
    {
        return $request->filled('cargo_superior_id')
            && (int) $request->input('cargo_superior_id') === $id;
    }
}
