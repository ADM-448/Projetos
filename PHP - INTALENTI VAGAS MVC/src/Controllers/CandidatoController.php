<?php

namespace src\Controllers;

use src\Core\Controller;
use src\Models\CandidatoProfile;

class CandidatoController extends Controller
{

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
    }

    public function perfil($userId)
    {
        $vagaId = filter_input(INPUT_GET, 'vaga_id', FILTER_VALIDATE_INT);

        // Se não tiver vagaId, podemos mostrar só o perfil genérico, 
        // mas para esse fluxo precisamos da vaga para mostrar o questionário e ações.
        if (!$vagaId) {
            echo "ID da vaga não fornecido.";
            return;
        }

        $profileModel = new CandidatoProfile();

        // 1. Perfil Completo
        $candidato = $profileModel->getFullProfileByUserId($userId);

        if (!$candidato) {
            echo "Candidato não encontrado (User ID: $userId).";
            return;
        }

        // 2. Detalhes da Candidatura (Status, Chat, Questionário)
        // O Model precisa do ID do CANDIDATO, não do User. Já temos em $candidato['id']
        $candidatura = $profileModel->getCandidaturaDetails($candidato['id'], $vagaId);

        // Prepara link do WhatsApp (Lógica original portada)
        $whatsapp_link = null;
        if (!empty($candidato['whatsapp'])) {
            $num = preg_replace('/\D/', '', $candidato['whatsapp']);
            if (strlen($num) == 10 || strlen($num) == 11) {
                $num = '55' . $num;
            }
            $whatsapp_link = 'https://wa.me/' . $num;
        }

        $this->renderTemplate('Candidato/perfil', [
            'candidato' => $candidato,
            'candidatura' => $candidatura, // Pode ser null se não houver inscrição
            'experiencias' => $candidato['experiencias'],
            'formacoes' => $candidato['formacoes'],
            'cursos' => $candidato['cursos'],
            'questionario_respostas' => $candidatura['respostas'] ?? [],
            'vaga_id' => $vagaId,
            'whatsapp_link' => $whatsapp_link,
            'nome' => $_SESSION['user_name']
        ]);
    }
}
