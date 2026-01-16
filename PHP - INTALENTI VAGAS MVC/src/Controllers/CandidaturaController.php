<?php

namespace src\Controllers;

use src\Core\Controller;
use src\Models\Candidatura;
use src\Config\Database;

class CandidaturaController extends Controller
{

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
    }

    public function status()
    {
        // Apenas POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        $candidaturaId = filter_input(INPUT_POST, 'candidatura_id', FILTER_VALIDATE_INT);
        $novoStatus = filter_input(INPUT_POST, 'novo_status');

        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? BASE_URL . 'dashboard';

        if ($candidaturaId && $novoStatus) {
            $db = Database::getInstance();
            $db = Database::getInstance();
            // Validação de Segurança: Verificar se a vaga pertence ao recrutador logado ou se é admin
            $vagaInfo = $db->selectOne("
                SELECT v.recrutador_id, v.empresa_id 
                FROM candidaturas c 
                JOIN vagas v ON c.vaga_id = v.id 
                WHERE c.id = :cid
            ", ['cid' => $candidaturaId]);

            if (!$vagaInfo) {
                $_SESSION['flash_error'] = "Candidatura não encontrada.";
                header('Location: ' . $redirectUrl);
                exit;
            }

            $empresaModel = new \src\Models\Empresa();
            $isOwner = ($vagaInfo['recrutador_id'] == $_SESSION['user_id']);
            $isAdmin = false;

            if (!empty($vagaInfo['empresa_id'])) {
                $isAdmin = ($empresaModel->getUserRole($_SESSION['user_id'], $vagaInfo['empresa_id']) === 'admin');
            }

            if (!$isOwner && !$isAdmin) {
                $_SESSION['flash_error'] = "Acesso Negado: Vaga não pertence a você.";
                header('Location: ' . $redirectUrl);
                exit;
            }

            $candidaturaModel = new Candidatura();
            $candidaturaModel->atualizarStatus($candidaturaId, $novoStatus);

            // --- NOTIFICAÇÃO PARA O CANDIDATO ---
            // Busca dados do candidato e da vaga
            $info = $db->selectOne("
                SELECT u.id as user_id, u.email, u.name, v.titulo, v.id as vaga_id
                FROM candidaturas c
                JOIN candidatos cand ON c.candidato_id = cand.id
                JOIN users u ON cand.user_id = u.id
                JOIN vagas v ON c.vaga_id = v.id
                WHERE c.id = :cid
            ", ['cid' => $candidaturaId]);

            if ($info) {
                // 1. Notificação Interna
                $msg = "O status da sua candidatura para <strong>{$info['titulo']}</strong> mudou para: <strong>$novoStatus</strong>";
                $link = BASE_URL . "vagas/show/" . $info['vaga_id']; // Link para a vaga ou área do candidato

                $notificacao = new \src\Models\Notificacao();
                $notificacao->criar($info['user_id'], $msg, $link);

                // 2. Enviar E-mail
                $assunto = "Atualização na sua candidatura: " . $info['titulo'];
                $html = "<p>Olá, {$info['name']}.</p>
                        <p>O status da sua candidatura para a vaga <strong>{$info['titulo']}</strong> foi atualizado.</p>
                        <p>Novo Status: <span style='color: #007bff; font-weight: bold;'>$novoStatus</span></p>
                        <p>Acesse a plataforma para ver mais detalhes.</p>";

                \src\Utils\Mailer::enviar($info['email'], $info['name'], $assunto, $html);
            }
            // ------------------------------------

            $_SESSION['flash_success'] = "Status atualizado para: <strong>$novoStatus</strong>";
        } else {
            $_SESSION['flash_error'] = "Erro ao atualizar status.";
        }

        header('Location: ' . $redirectUrl);
        exit;
    }

    public function aplicar()
    {
        if (($_SESSION['tipo_usuario'] ?? $_SESSION['user_type']) != 1) {
            $_SESSION['msg_error'] = "Acesso negado. Apenas candidatos podem se aplicar.";
            header("Location: " . BASE_URL . "vagas");
            exit;
        }

        $vaga_id = filter_input(INPUT_POST, 'vaga_id', FILTER_VALIDATE_INT);
        if (!$vaga_id) {
            $_SESSION['msg_error'] = "Vaga inválida.";
            header("Location: " . BASE_URL . "vagas");
            exit;
        }

        $db = Database::getInstance();
        $userId = $_SESSION['user_id'];

        $candidato = $db->selectOne("SELECT id FROM candidatos WHERE user_id = :uid", ['uid' => $userId]);

        if (!$candidato) {
            $_SESSION['msg_error'] = "Você precisa completar seu currículo antes de se candidatar.";
            header("Location: " . BASE_URL . "curriculo"); // Changed to correct route
            exit;
        }

        $candidatoId = $candidato['id'];

        if (isset($_POST['tem_questionario']) && $_POST['tem_questionario'] == 1) {
            $this->processarQuestionario($db, $vaga_id, $candidatoId);
        } else {
            $this->processarSimples($db, $vaga_id, $candidatoId);
        }
    }

    private function processarSimples($db, $vagaId, $candidatoId)
    {
        try {
            $check = $db->selectOne("SELECT id FROM candidaturas WHERE vaga_id = :vid AND candidato_id = :cid", [
                'vid' => $vagaId,
                'cid' => $candidatoId
            ]);

            if ($check) {
                $_SESSION['msg_error'] = "Você já se candidatou a esta vaga.";
            } else {
                $db->query("INSERT INTO candidaturas (vaga_id, candidato_id, status, data_candidatura) VALUES (:vid, :cid, 'Enviada', NOW())", [
                    'vid' => $vagaId,
                    'cid' => $candidatoId
                ]);

                // NOTIFICAÇÃO RH
                $recrutador = $db->selectOne("SELECT u.id, u.email, u.name, v.titulo FROM vagas v JOIN users u ON v.recrutador_id = u.id WHERE v.id = :vid", ['vid' => $vagaId]);
                if ($recrutador) {
                    $msg = "Novo candidato para a vaga: <strong>{$recrutador['titulo']}</strong>";
                    $link = BASE_URL . "vagas/candidatos/" . $vagaId;

                    $notificacao = new \src\Models\Notificacao();
                    $notificacao->criar($recrutador['id'], $msg, $link);

                    $assunto = "Nova candidatura: " . $recrutador['titulo'];
                    $html = "<p>Olá, {$recrutador['name']}.</p><p>Um novo candidato se aplicou para a vaga <strong>{$recrutador['titulo']}</strong>.</p><p><a href='$link'>Ver Candidatos</a></p>";
                    \src\Utils\Mailer::enviar($recrutador['email'], $recrutador['name'], $assunto, $html);
                }

                $_SESSION['msg'] = "Candidatura enviada com sucesso!";
            }
        } catch (\Exception $e) {
            $_SESSION['msg_error'] = "Erro ao processar candidatura.";
        }

        header("Location: " . BASE_URL . "vagas/show/" . $vagaId);
        exit;
    }

    private function processarQuestionario($db, $vagaId, $candidatoId)
    {
        // Verifica eliminatórias
        $eliminatorias = $db->select("SELECT id, texto_pergunta FROM perguntas_vaga WHERE vaga_id = :vid AND eliminatoria = 1", ['vid' => $vagaId]);
        // Transforma em array [id => texto]
        $mapElim = [];
        foreach ($eliminatorias as $e) $mapElim[$e['id']] = $e['texto_pergunta'];

        $foi_desclassificado = false;

        if (isset($_POST['pergunta_id']) && isset($_POST['respostas'])) {
            foreach ($_POST['pergunta_id'] as $index => $pId) {
                if (isset($mapElim[$pId])) {
                    $resp = trim($_POST['respostas'][$index] ?? '');
                    if (strcasecmp($resp, 'Não') == 0 || strcasecmp($resp, 'Nao') == 0) {
                        $foi_desclassificado = true;
                        break;
                    }
                }
            }
        }

        if ($foi_desclassificado) {
            $_SESSION['msg_error'] = "Você não atende aos requisitos obrigatórios desta vaga.";
            header("Location: " . BASE_URL . "vagas/show/" . $vagaId);
            exit;
        }

        // Transação
        try {
            $db->beginTransaction();

            $db->query("INSERT INTO candidaturas (vaga_id, candidato_id, status, data_candidatura) VALUES (:vid, :cid, 'Enviada', NOW())", [
                'vid' => $vagaId,
                'cid' => $candidatoId
            ]);
            $candId = $db->getConnection()->lastInsertId();

            if (isset($_POST['pergunta_id'])) {
                foreach ($_POST['pergunta_id'] as $index => $pId) {
                    $resp = $_POST['respostas'][$index] ?? '';
                    $db->query("INSERT INTO respostas_candidato (candidatura_id, pergunta_id, resposta) VALUES (:cid, :pid, :resp)", [
                        'cid' => $candId,
                        'pid' => $pId,
                        'resp' => $resp
                    ]);
                }
            }

            // NOTIFICAÇÃO RH
            $recrutador = $db->selectOne("SELECT u.id, u.email, u.name, v.titulo FROM vagas v JOIN users u ON v.recrutador_id = u.id WHERE v.id = :vid", ['vid' => $vagaId]);
            if ($recrutador) {
                $msg = "Novo candidato para a vaga: <strong>{$recrutador['titulo']}</strong>";
                $link = BASE_URL . "vagas/candidatos/" . $vagaId;

                $notificacao = new \src\Models\Notificacao();
                $notificacao->criar($recrutador['id'], $msg, $link);

                $assunto = "Nova candidatura: " . $recrutador['titulo'];
                $html = "<p>Olá, {$recrutador['name']}.</p><p>Um novo candidato se aplicou para a vaga <strong>{$recrutador['titulo']}</strong>.</p><p><a href='$link'>Ver Candidatos</a></p>";
                \src\Utils\Mailer::enviar($recrutador['email'], $recrutador['name'], $assunto, $html);
            }

            $db->commit();
            $_SESSION['msg'] = "Candidatura enviada com sucesso!";
        } catch (\Exception $e) {
            $db->rollBack();
            $_SESSION['msg_error'] = "Erro ao salvar candidatura: " . $e->getMessage();
        }

        header("Location: " . BASE_URL . "vagas/show/" . $vagaId);
        exit;
    }

    public function cancelar()
    {
        $vaga_id = filter_input(INPUT_POST, 'vaga_id', FILTER_VALIDATE_INT);
        if (!$vaga_id) {
            header("Location: " . BASE_URL . "vagas");
            exit;
        }

        $db = Database::getInstance();
        $userId = $_SESSION['user_id'];
        $candidato = $db->selectOne("SELECT id FROM candidatos WHERE user_id = :uid", ['uid' => $userId]);

        if ($candidato) {
            $db->query("DELETE FROM candidaturas WHERE vaga_id = :vid AND candidato_id = :cid", [
                'vid' => $vaga_id,
                'cid' => $candidato['id']
            ]);
            $_SESSION['msg'] = "Candidatura cancelada com sucesso.";
        }

        header("Location: " . BASE_URL . "vagas/show/" . $vaga_id);
        exit;
    }
}
