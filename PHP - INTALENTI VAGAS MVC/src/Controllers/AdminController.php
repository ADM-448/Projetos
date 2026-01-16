<?php

namespace src\Controllers;

use src\Core\Controller;
use src\Models\Empresa;

class AdminController extends Controller
{
    private $empresaModel;

    public function __construct()
    {
        // Verifica se é Super Admin (tipo 3)
        // Se sua lógica de login for diferente, ajuste aqui.
        if (!isset($_SESSION['user_id']) || ($_SESSION['tipo_usuario'] ?? 0) != 3) {
            $_SESSION['flash_error'] = "Acesso negado. Área exclusiva para administradores.";
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        $this->empresaModel = new Empresa();
    }

    public function dashboard()
    {
        // Redireciona para o índice (que lista as empresas)
        // Ou poderíamos ter uma tela com estatísticas gerais aqui.
        $this->index();
    }

    public function index()
    {
        // Dashboard do Admin
        // Pode mostrar contagens rápidas
        // Por enquanto, redireciona para a lista
        $this->empresas();
    }

    public function empresas()
    {
        $tipo = filter_input(INPUT_GET, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS);

        // Vamos precisar de um método no Model para listar todas (com filtro opcional)
        // Como o getByUser é restrito, vamos criar um getAll no model ou usar query direta aqui se for rápido
        // Melhor adicionar getAll no Model.

        // POG temporário se o model não tiver getAll:
        // Vamos supor que vou adicionar getAll($tipo) no Model Empresa.
        $lista = $this->empresaModel->getAll($tipo);

        $this->renderTemplate('Admin/empresas', [
            'lista' => $lista,
            'filtro_tipo' => $tipo
        ]);
    }

    public function nova()
    {
        $this->renderTemplate('Admin/form_empresa', ['action' => 'create']);
    }

    public function editar($id)
    {
        $empresa = $this->empresaModel->find($id);
        if (!$empresa) {
            $_SESSION['flash_error'] = "Empresa não encontrada.";
            header('Location: ' . BASE_URL . 'admin/empresas');
            exit;
        }

        $this->renderTemplate('Admin/form_empresa', [
            'action' => 'edit',
            'dado' => $empresa
        ]);
    }

    public function salvar()
    {
        $id = filter_input(INPUT_POST, 'id');
        $nome = filter_input(INPUT_POST, 'nome_empresa');
        $tipo = filter_input(INPUT_POST, 'tipo'); // empresa, prefeitura, rh
        $dominio = filter_input(INPUT_POST, 'dominio'); // Agora ativo
        $cnpj = filter_input(INPUT_POST, 'cnpj'); // Novo campo

        if (!$nome || !$tipo) {
            $_SESSION['flash_error'] = "Nome e Tipo são obrigatórios.";
            header('Location: ' . BASE_URL . 'admin/nova');
            exit;
        }

        $data = [
            'nome_empresa' => $nome,
            'tipo' => $tipo,
            'dominio' => $dominio,
            'cnpj' => $cnpj ?? '' // Garante string vazia se null, embora banco possa reclamar se não aceitar ''
        ];

        // Se vier status do form (edição)
        if (isset($_POST['status'])) {
            $data['status'] = filter_input(INPUT_POST, 'status', FILTER_VALIDATE_INT);
        }

        if ($id) {
            // Update
            $this->empresaModel->update($id, $data);
            $_SESSION['flash_success'] = "Registro atualizado com sucesso!";
        } else {
            // Create
            // Precisa de um método create no model
            $this->empresaModel->create($data);
            $_SESSION['flash_success'] = "Registro criado com sucesso!";
        }

        header('Location: ' . BASE_URL . 'admin/empresas');
        exit;
    }

    public function toggleStatus($id)
    {
        // Soft Delete / Desativar
        // Precisamos saber o status atual ou passar via GET
        // Vamos assumir toggle simples

        $empresa = $this->empresaModel->find($id);
        if ($empresa) {
            $novoStatus = ($empresa['status'] ?? 1) == 1 ? 0 : 1;
            // Assumindo que tem coluna 'status' na tabela. Se não tiver, precisa criar.
            $this->empresaModel->update($id, ['status' => $novoStatus]);

            $msg = $novoStatus ? 'ativada' : 'desativada';
            $_SESSION['flash_success'] = "Empresa $msg com sucesso.";
        }

        header('Location: ' . BASE_URL . 'admin/empresas');
        exit;
    }

    public function excluir($id)
    {
        // Hard Delete
        // Perigo: Apaga vagas, vinculos, convites.
        // O Model precisa cuidar da limpeza em cascata ou o banco (FKs).

        if ($this->empresaModel->deleteFull($id)) {
            $_SESSION['flash_success'] = "Empresa e todos os dados vinculados foram removidos permanentemente.";
        } else {
            $_SESSION['flash_error'] = "Erro ao excluir.";
        }

        header('Location: ' . BASE_URL . 'admin/empresas');
        exit;
    }
}
