<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmpresaWebController extends Controller
{
    /**
     * Display a listing of empresas (Admin only)
     */
    public function index()
    {
        // Verifica se é Admin
        if (Auth::user()->perfil_acesso !== 'admin') {
            abort(403, 'Acesso negado. Apenas Admin pode gerenciar empresas.');
        }

        $empresas = Empresa::withCount('users')
            ->with(['users' => function($query) {
                $query->whereIn('perfil_acesso', ['rh', 'master'])->orderBy('created_at', 'asc')->limit(1);
            }])
            ->get();
        
        return view('empresas.index', compact('empresas'));
    }

    /**
     * Store a newly created empresa
     */
    public function store(Request $request)
    {
        if (Auth::user()->perfil_acesso !== 'admin') {
            abort(403);
        }

        $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => 'nullable|string|regex:/^\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}$/',
            'master_name' => 'required|string|max:255',
            'master_email' => 'required|email|unique:users,email',
            'master_password' => 'required|string|min:8',
        ], [
            'cnpj.regex' => 'O CNPJ deve estar no formato: 00.000.000/0000-00',
        ]);

        // Cria a empresa
        $empresa = Empresa::create([
            'nome' => $request->nome,
            'razao_social' => $request->nome, // Usa o nome como razão social por padrão
            'cnpj' => $request->cnpj,
            'status' => 'ativa',
        ]);

        // Cria o usuário Master vinculado à empresa
        User::create([
            'name' => $request->master_name,
            'email' => $request->master_email,
            'password' => bcrypt($request->master_password),
            'perfil_acesso' => 'master',
            'empresa_id' => $empresa->id,
        ]);

        return redirect()->route('empresas.index')
            ->with('success', 'Empresa e usuário Master criados com sucesso!');
    }

    /**
     * Update the specified empresa
     */
    public function update(Request $request, Empresa $empresa)
    {
        if (Auth::user()->perfil_acesso !== 'admin') {
            abort(403);
        }

        $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:18',
        ]);

        $empresa->update([
            'nome' => $request->nome,
            'cnpj' => $request->cnpj,
        ]);

        return redirect()->route('empresas.index')
            ->with('success', 'Empresa atualizada com sucesso!');
    }

    /**
     * Toggle empresa status (ativo/inativo)
     */
    public function toggleStatus(Empresa $empresa)
    {
        if (Auth::user()->perfil_acesso !== 'admin') {
            abort(403);
        }

        $newStatus = $empresa->status === 'ativa' ? 'suspensa' : 'ativa';
        
        $empresa->update([
            'status' => $newStatus
        ]);

        $statusText = $newStatus === 'ativa' ? 'ativada' : 'suspensa';
        
        return redirect()->route('empresas.index')
            ->with('success', "Empresa {$statusText} com sucesso!");
    }

    /**
     * Remove the specified empresa
     */
    public function destroy(Empresa $empresa)
    {
        if (Auth::user()->perfil_acesso !== 'admin') {
            abort(403);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $empresaId = $empresa->id;
            $userIds = $empresa->users()->pluck('id')->toArray();

            // Desabilita checagem de chave estrangeira temporariamente
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            if (!empty($userIds)) {
                // 1. Reuniões Feedback
                $reunioesIds = \Illuminate\Support\Facades\DB::table('reuniao_feedback')->whereIn('gestor_id', $userIds)->pluck('id')->toArray();
                if (!empty($reunioesIds)) {
                    \Illuminate\Support\Facades\DB::table('reuniao_feedback_participantes')->whereIn('reuniao_feedback_id', $reunioesIds)->delete();
                    \Illuminate\Support\Facades\DB::table('reuniao_feedback')->whereIn('id', $reunioesIds)->delete();
                }
                \Illuminate\Support\Facades\DB::table('reuniao_agendamentos')
                    ->whereIn('gestor_id', $userIds)
                    ->orWhereIn('colaborador_id', $userIds)
                    ->delete();

                // 2. Solicitações e Históricos
                \Illuminate\Support\Facades\DB::table('historico_perfils')->whereIn('user_id', $userIds)->delete();
                \Illuminate\Support\Facades\DB::table('solicitacao_avaliacaos')->whereIn('colaborador_id', $userIds)->delete();
            }

            // 3. Avaliações
            $avaliacoesIds = \Illuminate\Support\Facades\DB::table('avaliacaos')->where('empresa_id', $empresaId)->pluck('id')->toArray();
            if (!empty($avaliacoesIds)) {
                \Illuminate\Support\Facades\DB::table('avaliacao_fechamentos')->whereIn('avaliacao_id', $avaliacoesIds)->delete();
                \Illuminate\Support\Facades\DB::table('avaliacao_respostas')->whereIn('avaliacao_id', $avaliacoesIds)->delete();
                \Illuminate\Support\Facades\DB::table('avaliacao_participantes')->whereIn('avaliacao_id', $avaliacoesIds)->delete();
                \Illuminate\Support\Facades\DB::table('avaliacao_competencia')->whereIn('avaliacao_id', $avaliacoesIds)->delete();
                \Illuminate\Support\Facades\DB::table('avaliacaos')->whereIn('id', $avaliacoesIds)->delete();
            }

            // 4. PDIs
            $pdiIds = \Illuminate\Support\Facades\DB::table('pdis')->where('empresa_id', $empresaId)->pluck('id')->toArray();
            if (!empty($pdiIds)) {
                \Illuminate\Support\Facades\DB::table('pdi_acaos')->whereIn('pdi_id', $pdiIds)->delete();
                \Illuminate\Support\Facades\DB::table('pdi_comentarios')->whereIn('pdi_id', $pdiIds)->delete();
                \Illuminate\Support\Facades\DB::table('pdis')->whereIn('id', $pdiIds)->delete();
            }

            // 5. Configurações, Notificações e Outros
            \Illuminate\Support\Facades\DB::table('configuracoes_google')->where('empresa_id', $empresaId)->delete();
            \Illuminate\Support\Facades\DB::table('notificacaos')->where('empresa_id', $empresaId)->delete();
            
            if (!empty($userIds)) {
                \Illuminate\Support\Facades\DB::table('solicitacoes')->whereIn('user_id', $userIds)->delete();
            }

            // 6. Estrutura Organizacional
            $cargoIds = \Illuminate\Support\Facades\DB::table('cargos')->where('empresa_id', $empresaId)->pluck('id')->toArray();
            if (!empty($cargoIds)) {
                \Illuminate\Support\Facades\DB::table('cargo_hardskills')->whereIn('cargo_id', $cargoIds)->delete();
                \Illuminate\Support\Facades\DB::table('cargo_competencia')->whereIn('cargo_id', $cargoIds)->delete();
                \Illuminate\Support\Facades\DB::table('cargos')->whereIn('id', $cargoIds)->delete();
            }
            \Illuminate\Support\Facades\DB::table('departamentos')->where('empresa_id', $empresaId)->delete();
            \Illuminate\Support\Facades\DB::table('competencias')->where('empresa_id', $empresaId)->delete();

            // 7. Usuários e Empresa
            \Illuminate\Support\Facades\DB::table('users')->where('empresa_id', $empresaId)->update(['gestor_id' => null]);
            \Illuminate\Support\Facades\DB::table('users')->where('empresa_id', $empresaId)->delete();
            \Illuminate\Support\Facades\DB::table('empresas')->where('id', $empresaId)->delete();

            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('empresas.index')
                ->with('success', 'A Empresa e todos os seus dados foram excluídos com sucesso!');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return redirect()->route('empresas.index')
                ->with('error', 'Erro ao excluir empresa: ' . $e->getMessage());
        }
    }
}
