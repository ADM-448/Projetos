<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    protected function checkSuperAdmin(): void
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Acesso restrito ao Administrador Geral.');
        }
    }

    /**
     * Lista todas as empresas e prefeituras com filtros e métricas
     */
    public function empresas(Request $request)
    {
        $this->checkSuperAdmin();
        $tipo = $request->query('tipo'); // 'prefeitura', 'empresa', 'pendentes'
        $busca = $request->query('busca');

        $query = Empresa::with(['usuarios' => function ($q) {
            $q->wherePivot('role', 'admin');
        }])->withCount('vagas');

        if ($tipo === 'prefeitura') {
            $query->where('tipo', 'prefeitura');
        } elseif ($tipo === 'empresa') {
            $query->where('tipo', 'empresa');
        } elseif ($tipo === 'pendentes') {
            $query->where('status', false);
        }

        if (!empty($busca)) {
            $query->where(function ($q) use ($busca) {
                $q->where('nome_empresa', 'LIKE', "%{$busca}%")
                  ->orWhere('cnpj', 'LIKE', "%{$busca}%")
                  ->orWhere('cidade', 'LIKE', "%{$busca}%")
                  ->orWhere('dominio', 'LIKE', "%{$busca}%");
            });
        }

        $lista = $query->latest()->paginate(15)->withQueryString();

        $kpis = [
            'total_organizacoes' => Empresa::count(),
            'prefeituras_ativas' => Empresa::where('tipo', 'prefeitura')->where('status', true)->count(),
            'prefeituras_pendentes' => Empresa::where('tipo', 'prefeitura')->where('status', false)->count(),
            'empresas_ativas' => Empresa::where('tipo', 'empresa')->where('status', true)->count(),
        ];

        return view('admin.empresas', compact('lista', 'tipo', 'busca', 'kpis'));
    }

    /**
     * Alterna o status da organização e seus usuários (Aprovar / Bloquear)
     */
    public function toggleStatus($id)
    {
        $this->checkSuperAdmin();
        $empresa = Empresa::findOrFail($id);
        $novoStatus = !$empresa->status;
        $empresa->update(['status' => $novoStatus]);

        // Sincroniza o status dos usuários vinculados
        foreach ($empresa->usuarios as $user) {
            $user->update(['status' => $novoStatus]);
        }

        $acao = $novoStatus ? 'aprovada e ativada' : 'bloqueada e inativada';
        return back()->with('success', "A organização '{$empresa->nome_empresa}' foi {$acao} com sucesso.");
    }

    /**
     * Atualiza o domínio personalizado da prefeitura/portal
     */
    public function salvarDominio(Request $request, $id)
    {
        $this->checkSuperAdmin();
        $empresa = Empresa::findOrFail($id);

        $request->validate([
            'dominio' => ['nullable', 'string', 'max:255', 'unique:empresas,dominio,' . $empresa->id],
        ], [
            'dominio.unique' => 'Este domínio já está associado a outro portal / prefeitura.',
        ]);

        $dominio = $request->input('dominio') ? strtolower(trim($request->input('dominio'))) : null;
        $empresa->update(['dominio' => $dominio]);

        return back()->with('success', "Domínio do portal '{$empresa->nome_empresa}' atualizado para '{$dominio}' com sucesso!");
    }

    /**
     * Remove uma organização e seus vínculos
     */
    public function destroy($id)
    {
        $this->checkSuperAdmin();
        $empresa = Empresa::findOrFail($id);
        $nome = $empresa->nome_empresa;

        // Desassocia usuários
        $empresa->usuarios()->detach();
        $empresa->delete();

        return back()->with('success', "Registro '{$nome}' excluído com sucesso.");
    }
}
