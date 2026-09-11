<?php

namespace App\Http\Controllers;

use App\Contracts\ColaboradorServiceContract;
use App\Enums\UserRole;
use App\Http\Requests\StoreColaboradorRequest;
use App\Http\Requests\UpdateColaboradorRequest;
use App\Models\Cargo;
use App\Models\Departamento;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller de Colaboradores.
 *
 * Responsabilidade única: receber a request, delegar ao Service e retornar a response.
 * Toda lógica de negócio está em ColaboradorService.
 * Toda validação está nos Form Requests (StoreColaboradorRequest / UpdateColaboradorRequest).
 */
class ColaboradorController extends Controller
{
    public function __construct(
        private readonly ColaboradorServiceContract $colaboradorService
    ) {}

    /**
     * Lista os colaboradores da empresa logada.
     * Eager loading para evitar N+1.
     */
    public function index(): View
    {
        $empresaId = auth()->user()->empresa_id;

        $colaboradores = User::where('empresa_id', $empresaId)
            ->where('perfil_acesso', '!=', UserRole::ADMIN)
            ->with(['departamento', 'cargo', 'gestor'])
            ->withCount('subordinados')
            ->get();

        $departamentos = Departamento::orderBy('nome')->get();
        $cargos        = Cargo::orderBy('nome')->get();

        $gestores = User::where('empresa_id', $empresaId)
            ->whereIn('perfil_acesso', [UserRole::MASTER, UserRole::GESTOR])
            ->orderBy('name')
            ->get();

        return view('colaboradores.index', compact('colaboradores', 'departamentos', 'cargos', 'gestores'));
    }

    public function show(int $id): RedirectResponse
    {
        return redirect()->route('colaboradores.index');
    }

    public function edit(int $id): RedirectResponse
    {
        return redirect()->route('colaboradores.index');
    }

    /**
     * Cria um colaborador via Form Request (validação automática) + Service (negócio).
     */
    public function store(StoreColaboradorRequest $request): RedirectResponse
    {
        if ($this->tentaSerSeuProprioGestor($request)) {
            return back()->withErrors(['gestor_id' => 'Um colaborador não pode ser gestor de si mesmo.'])->withInput();
        }

        $this->colaboradorService->criar(
            $request->validated(),
            auth()->user()->empresa_id
        );

        return redirect()->route('colaboradores.index')
            ->with('success', 'Colaborador criado com sucesso!');
    }

    /**
     * Atualiza um colaborador via Form Request + Service.
     */
    public function update(UpdateColaboradorRequest $request, int $id): RedirectResponse
    {
        $colaborador = User::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        if ($this->tentaSerSeuProprioGestor($request, $id)) {
            return back()->withErrors(['gestor_id' => 'Um colaborador não pode ser gestor de si mesmo.'])->withInput();
        }

        $this->colaboradorService->atualizar(
            $colaborador,
            $request->validated(),
            auth()->id()
        );

        return redirect()->route('colaboradores.index')
            ->with('success', 'Colaborador atualizado com sucesso!');
    }

    /**
     * Remove um colaborador via Service (que valida as regras de negócio).
     */
    public function destroy(int $id): RedirectResponse
    {
        $colaborador = User::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        try {
            $this->colaboradorService->excluir($colaborador, auth()->id());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()->route('colaboradores.index')
            ->with('success', 'Colaborador excluído com sucesso!');
    }

    /**
     * Remove vários colaboradores em lote.
     */
    public function destroyLote(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer'
        ]);

        $colaboradores = User::where('empresa_id', auth()->user()->empresa_id)
            ->whereIn('id', $request->ids)
            ->get();

        $count = 0;
        $erros = [];

        foreach ($colaboradores as $colaborador) {
            try {
                $this->colaboradorService->excluir($colaborador, auth()->id());
                $count++;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $erros[] = "{$colaborador->name}: " . collect($e->errors())->flatten()->first();
            }
        }

        if (count($erros) > 0) {
            return redirect()->route('colaboradores.index')
                ->with('warning', "$count excluídos. Algumas falhas: " . implode(" | ", $erros));
        }

        return redirect()->route('colaboradores.index')
            ->with('success', "$count colaboradores excluídos com sucesso!");
    }

    /**
     * Importa colaboradores em lote via planilha.
     */
    public function importar(Request $request): RedirectResponse
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120'
        ]);

        $file = $request->file('arquivo');
        $empresaId = auth()->user()->empresa_id;

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

            $empresa = \App\Models\Empresa::find($empresaId);
            $maxUsuarios = $empresa->max_usuarios;
            $totalUsuariosAtuais = \App\Models\User::where('empresa_id', $empresaId)->count();

            $importedCount = 0;
            $skipped = [];

            $reader->getRows()->each(function (array $row) use ($empresaId, $maxUsuarios, &$totalUsuariosAtuais, &$importedCount, &$skipped) {
                $nome     = isset($row['nome']) ? trim($row['nome']) : (isset($row['Nome']) ? trim($row['Nome']) : null);
                $email    = isset($row['email']) ? trim($row['email']) : (isset($row['Email']) ? trim($row['Email']) : null);
                $cpf      = isset($row['cpf']) ? trim($row['cpf']) : (isset($row['CPF']) ? trim($row['CPF']) : null);
                $perfil   = strtolower(trim($row['perfil']  ?? $row['Perfil']  ?? 'colaborador'));
                $senha    = isset($row['senha']) ? trim($row['senha']) : (isset($row['Senha']) ? trim($row['Senha']) : 'mudar123');
                $telefone = $row['telefone']   ?? $row['Telefone']  ?? $row['whatsapp'] ?? $row['WhatsApp'] ?? null;
                $cargoRaw = $row['cargo']      ?? $row['Cargo']     ?? null;
                $deptoRaw = $row['departamento'] ?? $row['Departamento'] ?? null;
                
                if (empty($nome) || (empty($email) && empty($cpf))) {
                    $skipped[] = "Linha ignorada: Nome ou (Email/CPF) vazio.";
                    return;
                }

                if ($cpf) {
                    $cpf = preg_replace('/\D/', '', $cpf);
                }
                
                // Admin não pode ser importado, vira colaborador
                if ($perfil === UserRole::ADMIN->value) {
                    $perfil = 'colaborador';
                }

                $query = User::where('empresa_id', $empresaId);
                if ($cpf) {
                    $query->where('cpf', $cpf);
                } else {
                    $query->where('email', $email);
                }

                $user = $query->first();

                $dados = [
                    'name'          => $nome,
                    'perfil_acesso' => in_array($perfil, ['colaborador', 'gestor', 'master']) ? $perfil : 'colaborador',
                    'empresa_id'    => $empresaId,
                ];

                if ($email)    $dados['email']    = $email;
                if ($cpf)      $dados['cpf']       = $cpf;
                // Sanitiza telefone: remove tudo que não for dígito
                if ($telefone) $dados['telefone']  = preg_replace('/\D/', '', $telefone);

                // Tratamento de Departamento e Cargo (Cria se não existir, formato Title Case)
                if (!empty($deptoRaw)) {
                    $deptoFormatado = mb_convert_case(mb_strtolower(trim($deptoRaw), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                    $departamento = \App\Models\Departamento::firstOrCreate(
                        ['empresa_id' => $empresaId, 'nome' => $deptoFormatado]
                    );
                    $dados['departamento_id'] = $departamento->id;
                }

                if (!empty($cargoRaw)) {
                    $cargoFormatado = mb_convert_case(mb_strtolower(trim($cargoRaw), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                    $cargo = \App\Models\Cargo::firstOrCreate(
                        ['empresa_id' => $empresaId, 'nome' => $cargoFormatado]
                    );
                    $dados['cargo_id'] = $cargo->id;
                    
                    if (isset($dados['departamento_id'])) {
                        // Associa o cargo ao departamento caso ainda não esteja associado
                        $cargo->update(['departamento_id' => $dados['departamento_id']]);
                    }
                }

                if (!$user) {
                    if ($totalUsuariosAtuais >= $maxUsuarios) {
                        $skipped[] = "Linha ignorada ({$nome}): Limite de usuários atingido.";
                        return;
                    }

                    $dados['status'] = 'ativo';
                    $dados['password'] = \Illuminate\Support\Facades\Hash::make($senha);
                    User::create($dados);
                    $totalUsuariosAtuais++;
                } else {
                    if (isset($row['senha']) || isset($row['Senha'])) {
                        $dados['password'] = \Illuminate\Support\Facades\Hash::make($senha);
                    }
                    $user->update($dados);
                }

                $importedCount++;
            });

            if (count($skipped) > 0) {
                return redirect()->route('colaboradores.index')
                    ->with('success', "{$importedCount} colaboradores importados.")
                    ->with('error', count($skipped) . " registros ignorados (faltou nome ou cpf/email).");
            }

            return redirect()->route('colaboradores.index')
                ->with('success', "Importação concluída! {$importedCount} colaboradores processados.");
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao importar planilha: Verifique o formato do arquivo.');
        }
    }

    // ─────────────────────────── Guard Auxiliar ───────────────────────────────

    private function tentaSerSeuProprioGestor(Request $request, ?int $id = null): bool
    {
        return $request->filled('gestor_id')
            && $id !== null
            && (int) $request->input('gestor_id') === $id;
    }
}
