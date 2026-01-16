<?php

namespace src\Controllers;

use src\Core\Controller;
use src\Models\User;
use src\Config\Database;

class PerfilController extends Controller
{
    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
    }

    public function index()
    {
        $userModel = new User();
        $user = $userModel->find($_SESSION['user_id']);

        // Se for candidato, busca dados extras se necessário (legacy faz join)
        // O user já tem 'name' e 'email'. O legado usa 'nome_completo' da tb candidatos se existir.
        // Vamos passar o $user simples por enquanto ou melhorar se precisarmos do 'candidatos'

        // Simulação do LEFT JOIN do legado
        // SELECT u.name, u.email, c.nome_completo ... 
        // Se formos exibir nome_completo do candidato ao invés do user.name:
        $nomeExibicao = $user['name'];

        $tipoUsuario = $_SESSION['tipo_usuario'] ?? $_SESSION['user_type'] ?? 0;

        if ($tipoUsuario == 1) {
            // Busca candidato
            $db = Database::getInstance();
            $candidato = $db->selectOne("SELECT * FROM candidatos WHERE user_id = :uid", ['uid' => $_SESSION['user_id']]);
            if ($candidato && !empty($candidato['nome_completo'])) {
                $nomeExibicao = $candidato['nome_completo'];
            }
        }

        $this->renderTemplate('Perfil/index', [
            'user' => $user,
            'nome_exibicao' => $nomeExibicao
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'perfil');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $nomeCompleto = $_POST['nome_completo'] ?? '';
        $email = $_POST['email'] ?? '';
        $novaSenha = $_POST['nova_senha'] ?? '';
        $confirmaSenha = $_POST['confirma_senha'] ?? '';

        $userModel = new User();

        // 1. Validação de Email Duplicado
        $existing = $userModel->emailExistsForOther($email, $userId);
        if ($existing) {
            $_SESSION['flash_error'] = "Erro: Este e-mail já está em uso por outra conta.";
            header('Location: ' . BASE_URL . 'perfil');
            exit;
        }

        // 2. Senha
        $dataUpdate = [
            'name' => $nomeCompleto,
            'email' => $email
        ];

        if (!empty($novaSenha)) {
            if ($novaSenha !== $confirmaSenha) {
                $_SESSION['flash_error'] = "As novas senhas não conferem!";
                header('Location: ' . BASE_URL . 'perfil');
                exit;
            }
            $dataUpdate['password'] = password_hash($novaSenha, PASSWORD_DEFAULT);
        }

        // 3. Atualiza User
        try {
            $userModel->update($userId, $dataUpdate);

            // 4. Atualiza Candidato se for tipo 1
            $tipoUsuario = $_SESSION['tipo_usuario'] ?? $_SESSION['user_type'] ?? 0;
            if ($tipoUsuario == 1) {
                $db = Database::getInstance();
                // Verifica se existe registro em candidatos
                $cand = $db->selectOne("SELECT id FROM candidatos WHERE user_id = :uid", ['uid' => $userId]);
                if ($cand) {
                    $db->update('candidatos', ['nome_completo' => $nomeCompleto], 'user_id = :uid', ['uid' => $userId]);
                }
            }

            // Atualiza Sessão
            $_SESSION['user_name'] = $nomeCompleto;
            $_SESSION['flash_success'] = "Perfil atualizado com sucesso!";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Erro ao atualizar perfil: " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . 'perfil');
        exit;
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'perfil');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        $userModel = new User();
        $user = $userModel->find($userId);

        if (!$user || !password_verify($passwordConfirm, $user['password'])) {
            $_SESSION['flash_error'] = "Senha de confirmação incorreta. A conta não foi excluída.";
            header('Location: ' . BASE_URL . 'perfil');
            exit;
        }

        try {
            $userModel->deleteAccount($userId);
            session_destroy();
            // Redireciona para login com mensagem (via GET pois sessão morreu)
            header('Location: ' . BASE_URL . 'auth/login?msg=conta_excluida');
            exit;
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Erro ao excluir conta: " . $e->getMessage();
            header('Location: ' . BASE_URL . 'perfil');
            exit;
        }
    }
}
