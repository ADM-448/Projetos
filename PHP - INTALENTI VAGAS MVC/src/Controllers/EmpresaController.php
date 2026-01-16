<?php

namespace src\Controllers;

use src\Core\Controller;
use src\Models\Empresa;

class EmpresaController extends Controller
{
    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
    }

    public function dados()
    {
        $empresaModel = new Empresa();
        $empresas = $empresaModel->getByUser($_SESSION['user_id']);

        if (empty($empresas)) {
            $_SESSION['msg_error'] = "Nenhuma empresa vinculada ao seu usuário.";
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        // Por enquanto, assume a primeira empresa vinculada
        $empresaId = $empresas[0]['id'];
        $empresa = $empresaModel->find($empresaId);
        $role = $empresas[0]['role'];

        // Restrição: Apenas Admin pode ver/editar dados
        if ($role !== 'admin' && $_SESSION['tipo_usuario'] != 3) {
            $_SESSION['msg_error'] = "Você não tem permissão para acessar o perfil da empresa.";
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'dashboard'));
            exit;
        }

        if (!$empresa) {
            $_SESSION['msg_error'] = "Empresa não encontrada.";
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        $this->renderTemplate('Empresas/dados', [
            'empresa' => $empresa,
            'role' => $role
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'empresa/dados');
            exit;
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            $_SESSION['msg_error'] = "ID inválido.";
            header('Location: ' . BASE_URL . 'empresa/dados');
            exit;
        }

        // Validação de Segurança: Leitura completa do vinculo para checar role e cnpj
        $empresaModel = new Empresa();
        $meusVinculos = $empresaModel->getByUser($_SESSION['user_id']);
        $vinculoAtual = null;

        foreach ($meusVinculos as $v) {
            if ($v['id'] == $id) {
                $vinculoAtual = $v;
                break;
            }
        }

        // Se não achou vínculo ou não é admin (e não é superadmin do sistema), bloqueia
        $isCompanyAdmin = ($vinculoAtual && isset($vinculoAtual['role']) && $vinculoAtual['role'] === 'admin');
        $isSystemAdmin = ($_SESSION['tipo_usuario'] == 3);

        if (!$isCompanyAdmin && !$isSystemAdmin) {
            $_SESSION['msg_error'] = "Acesso negado. Apenas administradores da empresa podem editar.";
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'dashboard'));
            exit;
        }

        // Upload de Logo
        $logoPath = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $uploadDir = 'uploads/logos/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                // Tenta usar CNPJ limpo como nome, senão uniqid
                $cnpjLimpo = preg_replace('/[^0-9]/', '', $vinculoAtual['cnpj'] ?? uniqid());
                $fileName = $cnpjLimpo . '.' . $ext;

                if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $fileName)) {
                    $logoPath = $fileName;
                }
            }
        }

        $data = [
            'nome_empresa' => $_POST['nome_empresa'] ?? ($_POST['razao_social'] ?? null),
            'nome_fantasia' => $_POST['nome_fantasia'],
            'cep' => preg_replace('/\D/', '', $_POST['cep'] ?? ''),
            'logradouro' => $_POST['logradouro'],
            'numero' => $_POST['numero'],
            'complemento' => $_POST['complemento'],
            'bairro' => $_POST['bairro'],
            'cidade' => $_POST['cidade'],
            'estado' => $_POST['estado']
        ];

        if (empty($data['nome_empresa'])) {
            unset($data['nome_empresa']);
        }

        if ($logoPath) {
            $data['logo_path'] = $logoPath;
        }

        try {
            $empresaModel->update($id, $data);
            $_SESSION['msg'] = "Dados atualizados com sucesso!";
        } catch (\Exception $e) {
            $_SESSION['msg_error'] = "Erro ao atualizar: " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . 'empresa/dados');
        exit;
    }

    public function equipe()
    {
        $empresaModel = new Empresa();
        $meusVinculos = $empresaModel->getByUser($_SESSION['user_id']);

        if (empty($meusVinculos)) {
            $_SESSION['msg_error'] = "Nenhuma empresa vinculada.";
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        // Assume primeira empresa
        $empresa = $meusVinculos[0];
        $role = $empresa['role'];

        if ($role !== 'admin') {
            $_SESSION['msg_error'] = "Acesso negado. Apenas administradores podem gerenciar a equipe.";
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'dashboard'));
            exit;
        }

        $equipe = $empresaModel->getEquipe($empresa['id']);

        $this->renderTemplate('Empresas/equipe', [
            'empresa' => $empresa,
            'equipe' => $equipe,
            'msg_convite' => $_SESSION['msg_convite'] ?? null,
            'msg_convite_reutilizavel' => $_SESSION['msg_convite_reutilizavel'] ?? null,
            'msg_convite_error' => $_SESSION['msg_convite_error'] ?? null
        ]);

        // Limpa flashes específicos desta view
        if (isset($_SESSION['msg_convite'])) unset($_SESSION['msg_convite']);
        if (isset($_SESSION['msg_convite_reutilizavel'])) unset($_SESSION['msg_convite_reutilizavel']);
        if (isset($_SESSION['msg_convite_error'])) unset($_SESSION['msg_convite_error']);
    }

    public function gerenciarMembro()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
            header('Location: ' . BASE_URL . 'empresa/equipe');
            exit;
        }

        $empresaModel = new Empresa();
        $meusVinculos = $empresaModel->getByUser($_SESSION['user_id']);
        if (empty($meusVinculos)) exit;
        $empresa = $meusVinculos[0]; // Simplificação de um vínculo

        if ($empresa['role'] !== 'admin') {
            $_SESSION['msg_convite_error'] = "Permissão negada.";
            header('Location: ' . BASE_URL . 'empresa/equipe');
            exit;
        }

        $membroId = filter_input(INPUT_POST, 'membro_user_id', FILTER_VALIDATE_INT);
        $action = $_POST['action'];

        // Não alterar a si mesmo
        if ($membroId == $_SESSION['user_id']) {
            $_SESSION['msg_convite_error'] = "Você não pode alterar sua própria função.";
            header('Location: ' . BASE_URL . 'empresa/equipe');
            exit;
        }

        // Verifica se membro pertence à empresa
        $membroRole = $empresaModel->getUserRole($membroId, $empresa['id']);
        if (!$membroRole) {
            $_SESSION['msg_convite_error'] = "Membro não encontrado na empresa.";
            header('Location: ' . BASE_URL . 'empresa/equipe');
            exit;
        }

        try {
            switch ($action) {
                case 'promover':
                    $empresaModel->updateRole($membroId, $empresa['id'], 'admin');
                    $_SESSION['msg_convite'] = "Usuário promovido a Admin.";
                    break;
                case 'rebaixar':
                    $empresaModel->updateRole($membroId, $empresa['id'], 'membro');
                    $_SESSION['msg_convite'] = "Usuário rebaixado a Membro.";
                    break;
                case 'remover_membro':
                    $metodo = $_POST['metodo_exclusao'] ?? 'transferir';

                    if ($metodo === 'transferir') {
                        $empresaModel->transferVagas($membroId, $_SESSION['user_id'], $empresa['id']);
                        $empresaModel->removeMember($membroId, $empresa['id']);
                        $_SESSION['msg_convite'] = "Membro removido e vagas transferidas.";
                    } elseif ($metodo === 'excluir') {
                        $empresaModel->deleteVagasMember($membroId, $empresa['id']);
                        $empresaModel->removeMember($membroId, $empresa['id']);
                        $_SESSION['msg_convite'] = "Membro e suas vagas excluídos.";
                    }
                    break;
            }
        } catch (\Exception $e) {
            $_SESSION['msg_convite_error'] = "Erro: " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . 'empresa/equipe');
        exit;
    }

    public function gerarConvite()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['type'])) {
            header('Location: ' . BASE_URL . 'empresa/equipe');
            exit;
        }

        $type = $_POST['type']; // 'gerar_convite_unico' ou 'gerar_convite_reutilizavel'

        $empresaModel = new Empresa();
        $meusVinculos = $empresaModel->getByUser($_SESSION['user_id']);
        if (empty($meusVinculos)) exit;
        $empresa = $meusVinculos[0];

        if ($empresa['role'] !== 'admin') exit;

        if ($type === 'gerar_convite_unico') {
            $codigo = $empresaModel->createInvite($empresa['id'], $_SESSION['user_id'], 'unico');
            $_SESSION['msg_convite'] = "Novo código único: <strong>$codigo</strong> (24h)";
        } elseif ($type === 'gerar_convite_reutilizavel') {
            // Tenta buscar existente
            $existente = $empresaModel->getReusableInvite($empresa['id']);
            if ($existente) {
                $codigo = $existente['codigo'];
            } else {
                $codigo = $empresaModel->createInvite($empresa['id'], $_SESSION['user_id'], 'reutilizavel');
            }
            $_SESSION['msg_convite_reutilizavel'] = $codigo;
        }

        header('Location: ' . BASE_URL . 'empresa/equipe');
        exit;
    }
}
