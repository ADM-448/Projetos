<?php

namespace App\Services;

use App\Contracts\EmpresaServiceContract;
use App\Models\Empresa;
use App\Models\User;
use App\Enums\UserRole;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;

class EmpresaService implements EmpresaServiceContract
{
    public function __construct(
        protected WhatsAppService $whatsappService
    ) {}

    /**
     * Cria uma nova empresa e automaticamente cria seu usuário master.
     * Toda a operação é garantida por uma transação (DB::transaction).
     */
    public function criarComMaster(array $dadosEmpresa, array $dadosMaster): Empresa
    {
        return DB::transaction(function () use ($dadosEmpresa, $dadosMaster) {
            // 1. Cria a Empresa
            $empresa = Empresa::create($dadosEmpresa);

            // 2. Prepara os dados do Master e vincula à empresa
            $dadosMaster['empresa_id'] = $empresa->id;
            $dadosMaster['perfil_acesso'] = UserRole::MASTER;
            $dadosMaster['status'] = 'ativo';
            
            // 3. Cria o Master
            User::create($dadosMaster);

            return $empresa;
        });
    }

    /**
     * Atualiza os dados de uma empresa.
     */
    public function atualizar(Empresa $empresa, array $dados): Empresa
    {
        $whatsappFoiDesativado = isset($dados['whatsapp_ativo']) && $dados['whatsapp_ativo'] == false && $empresa->whatsapp_ativo == true;

        $empresaAtualizada = DB::transaction(function () use ($empresa, $dados) {
            $empresa->update($dados);
            return $empresa->fresh();
        });

        // Se o WhatsApp foi desativado, exclui a instância na VPS
        if ($whatsappFoiDesativado) {
            $this->whatsappService->excluirInstancia($empresa->id);
        }

        return $empresaAtualizada;
    }

    /**
     * Exclui a empresa permanentemente.
     * O model User está configurado com cascadeOnDelete na migration, 
     * então os usuários também serão excluídos pelo banco.
     */
    public function excluir(Empresa $empresa): void
    {
        $empresaId = $empresa->id;

        DB::transaction(function () use ($empresa) {
            $empresa->delete();
        });

        // Exclui a instância da VPS para não deixar lixo
        $this->whatsappService->excluirInstancia($empresaId);
    }
}
