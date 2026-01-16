<?php

namespace src\Controllers;

use src\Core\Controller;
use src\Models\Candidatura;
use src\Config\Database;

class CandidaturasController extends Controller
{
    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        // Verifica se é candidato (tipo 1)
        if (($_SESSION['tipo_usuario'] ?? $_SESSION['user_type']) != 1) {
            $_SESSION['msg_error'] = "Acesso restrito a candidatos.";
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }
    }

    public function index()
    {
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'];

        // 1. Pega ID do Candidato
        $candidato = $db->selectOne("SELECT id FROM candidatos WHERE user_id = :uid", ['uid' => $userId]);

        if (!$candidato) {
            $_SESSION['msg_error'] = "Complete seu perfil antes de ver suas candidaturas.";
            header('Location: ' . BASE_URL . 'curriculo');
            exit;
        }

        // 2. Busca Candidaturas
        $candidaturaModel = new Candidatura();
        $minhas_vagas = $candidaturaModel->obterPorCandidato($candidato['id']);

        $this->renderTemplate('Candidatura/index', [
            'minhas_vagas' => $minhas_vagas,
            'title' => 'Minhas Candidaturas'
        ]);
    }
}
