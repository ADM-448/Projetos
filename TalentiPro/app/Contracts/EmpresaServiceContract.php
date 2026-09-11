<?php

namespace App\Contracts;

use App\Models\Empresa;

interface EmpresaServiceContract
{
    /**
     * Cria uma nova empresa junto com seu usuário master.
     *
     * @param array<string, mixed> $dadosEmpresa
     * @param array<string, mixed> $dadosMaster
     * @return Empresa
     */
    public function criarComMaster(array $dadosEmpresa, array $dadosMaster): Empresa;

    /**
     * Atualiza os dados de uma empresa.
     *
     * @param Empresa $empresa
     * @param array<string, mixed> $dados
     * @return Empresa
     */
    public function atualizar(Empresa $empresa, array $dados): Empresa;

    /**
     * Exclui a empresa permanentemente.
     *
     * @param Empresa $empresa
     * @return void
     */
    public function excluir(Empresa $empresa): void;
}
