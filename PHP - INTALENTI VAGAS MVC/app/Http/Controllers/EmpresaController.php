<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Notificacao;
use App\Models\User;
use App\Models\Vaga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmpresaController extends Controller
{
    /**
     * Obtém a empresa ativa do usuário autenticado e valida permissão administrativa
     */
    protected function getEmpresaComPermissao(bool $exigirAdmin = false): Empresa
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $empresa = $user->getEmpresa();

        // Se for Super Admin e não tiver empresa direta vinculada, pega a primeira empresa privada
        if (!$empresa && $user->isSuperAdmin()) {
            $empresa = Empresa::where('tipo', 'empresa')->first() ?? Empresa::first();
        }

        if (!$empresa) {
            abort(403, 'Nenhuma empresa vinculada ao seu usuário.');
        }

        if ($exigirAdmin && !$user->isEmpresaAdmin()) {
            abort(403, 'Acesso restrito a administradores da empresa.');
        }

        return $empresa;
    }

    /**
     * Tela de Gestão de Equipe e Membros da Empresa
     */
    public function equipe(Request $request)
    {
        $user = Auth::user();
        $empresa = $this->getEmpresaComPermissao(false);

        $isEmpresaAdmin = $user->isEmpresaAdmin();

        $membros = $empresa->usuarios()
            ->withPivot('role', 'status', 'created_at')
            ->orderByPivot('created_at', 'asc')
            ->get();

        // Contagem de vagas criadas por cada recrutador na empresa
        $vagasPorUsuario = Vaga::withoutGlobalScopes()
            ->where('empresa_id', $empresa->id)
            ->selectRaw('recrutador_id, count(*) as total')
            ->groupBy('recrutador_id')
            ->pluck('total', 'recrutador_id');

        $kpis = [
            'total_membros' => $membros->count(),
            'total_admins' => $membros->where('pivot.role', 'admin')->count(),
            'total_recrutadores' => $membros->where('pivot.role', 'recrutador')->count(),
            'membros_ativos' => $membros->where('pivot.status', true)->count(),
        ];

        return view('empresa.equipe', compact('empresa', 'membros', 'kpis', 'vagasPorUsuario', 'isEmpresaAdmin'));
    }

    /**
     * Adiciona um novo usuário (ou vincula existente) à equipe da empresa
     */
    public function adicionarUsuario(Request $request)
    {
        $empresa = $this->getEmpresaComPermissao(true);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'cpf' => ['nullable', 'string', 'max:18'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['admin', 'recrutador'])],
        ], [
            'name.required' => 'O nome do colaborador é obrigatório.',
            'email.required' => 'O e-mail corporativo é obrigatório.',
            'email.email' => 'Informe um formato de e-mail válido.',
            'password.required' => 'A senha inicial é obrigatória.',
            'password.min' => 'A senha deve conter pelo menos 6 caracteres.',
            'role.in' => 'Selecione uma função válida (Administrador ou Recrutador).',
        ]);

        $cpfLimpo = $request->input('cpf') ? preg_replace('/\D/', '', $request->input('cpf')) : null;
        $emailNormalizado = strtolower(trim($request->input('email')));

        $usuarioExistente = User::where('email', $emailNormalizado)->first();

        if ($usuarioExistente) {
            // Verifica se já está na equipe
            if ($empresa->usuarios()->where('user_id', $usuarioExistente->id)->exists()) {
                return back()->withErrors(['email' => 'Este usuário já faz parte da equipe desta empresa.'])->withInput();
            }

            // Associa usuário existente à empresa
            $empresa->usuarios()->attach($usuarioExistente->id, [
                'role' => $request->input('role'),
                'status' => true,
            ]);

            $targetUser = $usuarioExistente;
        } else {
            // Se informou CPF, valida se já está em uso
            if ($cpfLimpo && User::where('cpf', $cpfLimpo)->exists()) {
                return back()->withErrors(['cpf' => 'Este CPF já está cadastrado em outra conta.'])->withInput();
            }

            $targetUser = User::create([
                'name' => trim($request->input('name')),
                'email' => $emailNormalizado,
                'cpf' => $cpfLimpo,
                'password' => Hash::make($request->input('password')),
                'tipo_usuario' => 2, // Empresa / RH
                'status' => true,
                'portal_id' => $empresa->portal_id,
            ]);

            $empresa->usuarios()->attach($targetUser->id, [
                'role' => $request->input('role'),
                'status' => true,
            ]);
        }

        // Notificação interna para o usuário convidado/adicionado
        Notificacao::create([
            'user_id' => $targetUser->id,
            'titulo' => 'Boas-vindas à equipe ' . $empresa->nome_empresa,
            'mensagem' => 'Você foi adicionado à equipe com a função de ' . ($request->input('role') === 'admin' ? 'Administrador' : 'Recrutador') . '. Acesse seu painel para gerenciar vagas e candidatos.',
            'link' => route('dashboard'),
            'lida' => false,
        ]);

        return back()->with('success', "Colaborador '{$targetUser->name}' adicionado à equipe com sucesso!");
    }

    /**
     * Alterna a função de um membro entre 'admin' e 'recrutador'
     */
    public function atualizarFuncao(Request $request, int $userId)
    {
        $empresa = $this->getEmpresaComPermissao(true);
        $userAutenticado = Auth::user();

        if ($userAutenticado->id == $userId) {
            return back()->withErrors(['error' => 'Você não pode alterar sua própria função de acesso.']);
        }

        $request->validate([
            'role' => ['required', Rule::in(['admin', 'recrutador'])],
        ]);

        $membro = $empresa->usuarios()->where('user_id', $userId)->firstOrFail();
        $empresa->usuarios()->updateExistingPivot($userId, [
            'role' => $request->input('role'),
        ]);

        $roleNome = $request->input('role') === 'admin' ? 'Administrador' : 'Recrutador';
        return back()->with('success', "Função de '{$membro->name}' atualizada para {$roleNome} com sucesso!");
    }

    /**
     * Ativa ou suspende o acesso de um membro da equipe
     */
    public function toggleStatusMembro(int $userId)
    {
        $empresa = $this->getEmpresaComPermissao(true);
        $userAutenticado = Auth::user();

        if ($userAutenticado->id == $userId) {
            return back()->withErrors(['error' => 'Você não pode desativar seu próprio acesso.']);
        }

        $membro = $empresa->usuarios()->where('user_id', $userId)->firstOrFail();
        $novoStatus = !$membro->pivot->status;

        $empresa->usuarios()->updateExistingPivot($userId, [
            'status' => $novoStatus,
        ]);

        $acao = $novoStatus ? 'ativado' : 'suspenso';
        return back()->with('success', "Acesso de '{$membro->name}' foi {$acao} com sucesso.");
    }

    /**
     * Remove o vínculo de um membro da equipe com opção de transferir vagas
     */
    public function removerMembro(Request $request, int $userId)
    {
        $empresa = $this->getEmpresaComPermissao(true);
        $userAutenticado = Auth::user();

        if ($userAutenticado->id == $userId) {
            return back()->withErrors(['error' => 'Você não pode remover a si mesmo da equipe.']);
        }

        $membro = $empresa->usuarios()->where('user_id', $userId)->firstOrFail();
        $metodo = $request->input('metodo', 'transferir'); // 'transferir' ou 'manter'

        if ($metodo === 'transferir') {
            Vaga::withoutGlobalScopes()
                ->where('empresa_id', $empresa->id)
                ->where('recrutador_id', $userId)
                ->update(['recrutador_id' => $userAutenticado->id]);
        }

        $empresa->usuarios()->detach($userId);

        return back()->with('success', "Membro '{$membro->name}' removido da equipe com sucesso!");
    }
}
