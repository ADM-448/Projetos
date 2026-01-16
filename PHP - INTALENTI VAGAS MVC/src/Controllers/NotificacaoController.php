<?php

namespace src\Controllers;

use src\Core\Controller;
use src\Models\Notificacao;

class NotificacaoController extends Controller
{
    public function check()
    {
        // Retorna JSON para o polling do JS
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['nao_lidas' => 0, 'lista' => []]);
            exit;
        }

        // Validação de Segurança: Verificar se RH foi removido da empresa
        if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] == 2) {
            $empresaModel = new \src\Models\Empresa();
            $vinculos = $empresaModel->getByUser($_SESSION['user_id']);
            if (empty($vinculos)) {
                // Usuário foi removido de todas as empresas
                // Destruir sessão e retornar flag
                session_destroy();
                echo json_encode(['force_logout' => true]);
                exit;
            }
        }

        $notificacaoModel = new Notificacao();
        $dados = $notificacaoModel->obterNotificacoesHeader($_SESSION['user_id']);

        echo json_encode($dados);
        exit;
    }

    public function markRead()
    {
        // Marca como lida via AJAX
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error']);
            exit;
        }

        $notificacaoModel = new Notificacao();
        $notificacaoModel->marcarTodasComoLidas($_SESSION['user_id']);

        echo json_encode(['status' => 'success']);
        exit;
    }
}
