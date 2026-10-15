<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ConsultorController extends Controller
{
    /**
     * Lista todos os consultores cadastrados.
     */
    public function index(Request $request): View
    {
        $query = User::where('perfil_acesso', UserRole::CONSULTOR->value)
            ->with(['empresasConsultoria'])
            ->orderBy('name');

        if ($request->filled('busca')) {
            $busca = $request->get('busca');
            $query->where(function ($q) use ($busca) {
                $q->where('name', 'like', "%{$busca}%")
                  ->orWhere('email', 'like', "%{$busca}%")
                  ->orWhere('cpf', 'like', "%{$busca}%");
            });
        }

        $consultores = $query->paginate(15)->withQueryString();

        return view('admin.consultores.index', compact('consultores'));
    }

    /**
     * Exibe o formulário de cadastro de novo consultor.
     */
    public function create(): View
    {
        $empresas = Empresa::where('status', 'ativa')
            ->orderBy('nome')
            ->get();

        return view('admin.consultores.create', compact('empresas'));
    }

    /**
     * Armazena um novo consultor e vincula às empresas selecionadas.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'   => ['required', 'string', 'min:6'],
            'cpf'        => ['nullable', 'string', 'max:20'],
            'telefone'   => ['nullable', 'string', 'max:20'],
            'status'     => ['required', 'in:ativo,inativo'],
            'empresas'   => ['required', 'array', 'min:1'],
            'empresas.*' => ['exists:empresas,id'],
        ], [
            'empresas.required' => 'Selecione ao menos uma empresa para este consultor.',
            'empresas.min'      => 'Selecione ao menos uma empresa para este consultor.',
            'email.unique'      => 'Este e-mail já está em uso por outro usuário.',
        ]);

        $primeiraEmpresaId = $validated['empresas'][0];

        $consultor = User::create([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'password'      => Hash::make($validated['password']),
            'perfil_acesso' => UserRole::CONSULTOR->value,
            'empresa_id'    => $primeiraEmpresaId,
            'cpf'           => $validated['cpf'] ?? null,
            'telefone'      => $validated['telefone'] ?? null,
            'status'        => $validated['status'],
        ]);

        // Vincula as empresas na tabela pivô
        $consultor->empresasConsultoria()->sync($validated['empresas']);

        return redirect()
            ->route('admin.consultores.index')
            ->with('success', "Consultor {$consultor->name} cadastrado com sucesso com vínculo em " . count($validated['empresas']) . " empresa(s).");
    }

    /**
     * Exibe o formulário de edição do consultor.
     */
    public function edit(User $consultore): View
    {
        // O resource route usa 'consultore' ou 'consultor' como parâmetro de rota
        $consultor = $consultore;

        $empresas = Empresa::orderBy('nome')->get();
        $empresasVinculadasIds = $consultor->empresasConsultoria()->pluck('empresas.id')->toArray();

        return view('admin.consultores.edit', compact('consultor', 'empresas', 'empresasVinculadasIds'));
    }

    /**
     * Atualiza os dados do consultor e suas empresas vinculadas.
     */
    public function update(Request $request, User $consultore): RedirectResponse
    {
        $consultor = $consultore;

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $consultor->id],
            'password'   => ['nullable', 'string', 'min:6'],
            'cpf'        => ['nullable', 'string', 'max:20'],
            'telefone'   => ['nullable', 'string', 'max:20'],
            'status'     => ['required', 'in:ativo,inativo'],
            'empresas'   => ['required', 'array', 'min:1'],
            'empresas.*' => ['exists:empresas,id'],
        ], [
            'empresas.required' => 'Selecione ao menos uma empresa para este consultor.',
            'empresas.min'      => 'Selecione ao menos uma empresa para este consultor.',
            'email.unique'      => 'Este e-mail já está em uso por outro usuário.',
        ]);

        $dadosAtualizacao = [
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'cpf'      => $validated['cpf'] ?? null,
            'telefone' => $validated['telefone'] ?? null,
            'status'   => $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $dadosAtualizacao['password'] = Hash::make($validated['password']);
        }

        // Se a empresa ativa atual não estiver mais na lista de empresas selecionadas, atualiza para a primeira
        if (!in_array($consultor->empresa_id, $validated['empresas'])) {
            $dadosAtualizacao['empresa_id'] = $validated['empresas'][0];
        }

        $consultor->update($dadosAtualizacao);

        // Sincroniza vínculos
        $consultor->empresasConsultoria()->sync($validated['empresas']);

        return redirect()
            ->route('admin.consultores.index')
            ->with('success', "Consultor {$consultor->name} atualizado com sucesso.");
    }

    /**
     * Remove o consultor e seus vínculos.
     */
    public function destroy(User $consultore): RedirectResponse
    {
        $consultor = $consultore;

        $nome = $consultor->name;
        $consultor->empresasConsultoria()->detach();
        $consultor->delete();

        return redirect()
            ->route('admin.consultores.index')
            ->with('success', "Consultor {$nome} removido com sucesso.");
    }
}
