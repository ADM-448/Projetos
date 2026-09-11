<?php

namespace App\Http\Controllers;

use App\Contracts\CompetenciaServiceContract;
use App\Http\Requests\StoreCompetenciaRequest;
use App\Http\Requests\UpdateCompetenciaRequest;
use App\Models\Competencia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CompetenciaController extends Controller
{
    public function __construct(
        private readonly CompetenciaServiceContract $competenciaService
    ) {}

    public function index(): View
    {
        Gate::authorize('viewAny', Competencia::class);

        $competencias = Competencia::with('skills')->orderBy('nome')->get();

        return view('competencias.index', compact('competencias'));
    }

    public function store(StoreCompetenciaRequest $request): RedirectResponse
    {
        Gate::authorize('create', Competencia::class);

        $this->competenciaService->criar($request->validated());

        return redirect()->route('competencias.index')
            ->with('success', 'Competência criada com sucesso!');
    }

    public function update(UpdateCompetenciaRequest $request, Competencia $competencia): RedirectResponse
    {
        Gate::authorize('update', $competencia);

        $this->competenciaService->atualizar($competencia, $request->validated());

        return redirect()->route('competencias.index')
            ->with('success', 'Competência atualizada com sucesso!');
    }

    public function importar(\Illuminate\Http\Request $request): RedirectResponse
    {
        Gate::authorize('create', Competencia::class);

        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120'
        ]);

        $file = $request->file('arquivo');

        try {
            $reader = \Spatie\SimpleExcel\SimpleExcelReader::create($file->getRealPath(), $file->getClientOriginalExtension());
            
            if (strtolower($file->getClientOriginalExtension()) === 'csv') {
                $content = file_get_contents($file->getRealPath(), false, null, 0, 1024);
                if (strpos($content, "\t") !== false) {
                    $reader->useDelimiter("\t");
                } elseif (strpos($content, ";") !== false) {
                    $reader->useDelimiter(";");
                }
            }

            $importedCount = 0;

            \Illuminate\Support\Facades\DB::transaction(function () use ($reader, &$importedCount) {
                $reader->getRows()->each(function (array $row) use (&$importedCount) {
                    $nome = $row['nome'] ?? $row['Nome'] ?? null;
                    $descricao = $row['descricao'] ?? $row['Descricao'] ?? $row['Descrição'] ?? null;
                    $skillsRaw = $row['skills'] ?? $row['Skills'] ?? $row['Habilidades'] ?? null;

                    if (empty($nome)) return;

                    $competencia = Competencia::updateOrCreate(
                        ['nome' => $nome],
                        ['descricao' => $descricao]
                    );

                    if ($skillsRaw) {
                        $skillsArray = array_map('trim', explode('|', $skillsRaw));
                        foreach ($skillsArray as $skillNome) {
                            if (!empty($skillNome)) {
                                $competencia->skills()->updateOrCreate(
                                    ['nome' => $skillNome],
                                    ['descricao' => null] // Default empty description for skills created via import
                                );
                            }
                        }
                    }
                    $importedCount++;
                });
            });

            return redirect()->route('competencias.index')
                ->with('success', "Importação concluída! {$importedCount} competências processadas.");
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao importar planilha: Verifique o formato do arquivo.');
        }
    }

    public function destroy(Competencia $competencia): RedirectResponse
    {
        Gate::authorize('delete', $competencia);

        $this->competenciaService->excluir($competencia);

        return redirect()->route('competencias.index')
            ->with('success', 'Competência excluída com sucesso!');
    }
}
