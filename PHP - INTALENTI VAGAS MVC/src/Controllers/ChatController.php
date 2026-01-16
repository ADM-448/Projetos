<?php

namespace src\Controllers;

use src\Core\Controller;
use src\Models\Chat;

class ChatController extends Controller
{

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            // Se for chamada AJAX, retorna JSON de erro
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo json_encode(['status' => 'error', 'message' => 'Não autenticado']);
                exit;
            }
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
    }

    public function index($chatId)
    {
        $chatModel = new Chat();
        $chatInfo = $chatModel->getChatInfo($chatId);

        if (!$chatInfo) {
            echo "Chat não encontrado.";
            return;
        }

        // --- Lógica de Permissão (Copiada e adaptada) ---
        $userId = $_SESSION['user_id'];
        $acessoPermitido = false;

        // Se eu sou o candidato ou recrutador dono da vaga
        if ($userId == $chatInfo['candidato_user_id'] || $userId == $chatInfo['recrutador_id']) {
            $acessoPermitido = true;
        } elseif (($_SESSION['tipo_usuario'] ?? $_SESSION['user_type'] ?? 0) == 3) { // Admin Sistema
            $acessoPermitido = true;
        } else {
            // Admin da Empresa?
            $empresaModel = new \src\Models\Empresa();
            if ($empresaModel->getUserRole($userId, $chatInfo['empresa_id']) === 'admin') {
                $acessoPermitido = true;
            }
        }

        if (!$acessoPermitido) {
            $_SESSION['msg_error'] = "Acesso negado ao chat.";
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'dashboard'));
            exit;
        }

        // Definição de nomes para a View
        // Lógica de nomes:
        // Se eu sou o candidato, vejo o nome da empresa.
        // Se eu sou recrutador ou admin da empresa, vejo o nome do candidato.

        $isCompanySide = ($userId == $chatInfo['recrutador_id']);
        if (!$isCompanySide) {
            $empresaModel = new \src\Models\Empresa();
            if ($empresaModel->getUserRole($userId, $chatInfo['empresa_id']) === 'admin') {
                $isCompanySide = true;
            }
        }

        $nomeOutro = $isCompanySide ? $chatInfo['nome_candidato'] : $chatInfo['nome_empresa'];

        $tituloTopo = $chatInfo['vaga_titulo'];

        // URL de voltar
        $backUrl = BASE_URL . "vagas"; // Ajustado conforme solicitado (era minhas-candidaturas)

        $tipoUsuario = $_SESSION['tipo_usuario'] ?? $_SESSION['user_type'] ?? 0;
        if ($tipoUsuario >= 2) {
            $backUrl = BASE_URL . "candidato/perfil/" . $chatInfo['candidato_user_id'] . "?vaga_id=" . $chatInfo['vaga_id'];
        }

        $this->renderTemplate('Chat/index', [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'candidato_user_id' => $chatInfo['candidato_user_id'],
            'nome_outro' => $nomeOutro,
            'titulo_topo' => $tituloTopo,
            'back_url' => $backUrl
        ]);
    }

    // API JSON
    public function getMessages()
    {
        $chatId = filter_input(INPUT_GET, 'chat_id', FILTER_VALIDATE_INT);
        $lastId = filter_input(INPUT_GET, 'last_id', FILTER_VALIDATE_INT) ?? 0;

        $chatModel = new Chat();
        $msgs = $chatModel->getMessages($chatId, $lastId);

        // Formata a data para exibir bonitinho
        foreach ($msgs as &$msg) {
            // Coluna correta do banco é enviada_em
            $data = $msg['enviada_em'] ?? Date('Y-m-d H:i:s');
            $msg['hora_formatada'] = date('d/m H:i', strtotime($data));
        }

        echo json_encode([
            'status' => 'success',
            'mensagens' => $msgs
        ]);
        exit;
    }

    public function send()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $chatId = filter_input(INPUT_POST, 'chat_id', FILTER_VALIDATE_INT);
            $msg = filter_input(INPUT_POST, 'mensagem');
            $userId = $_SESSION['user_id'];

            if ($chatId && $msg) {
                // --- NOVA REGRA: CANDIDATO NÃO INICIA CONVERSA ---
                // Para validar isso, precisamos saber quem é o candidato deste chat
                // Como não temos a info fácil aqui, vamos fazer uma query rápida ou instanciar o model.
                // A query de notificação abaixo já traz tudo, vou puxá-la para cima.

                $db = \src\Config\Database::getInstance();
                $info = $db->selectOne("
                        SELECT 
                            v.recrutador_id, 
                            cand.user_id as candidato_user_id,
                            v.titulo,
                            u_rec.email as rec_email, u_rec.name as rec_name,
                            u_cand.email as cand_email, u_cand.name as cand_name,
                            e.nome_empresa,
                            v.empresa_id
                        FROM chats ch
                        JOIN candidaturas c ON ch.candidatura_id = c.id
                        JOIN vagas v ON c.vaga_id = v.id
                        JOIN empresas e ON v.empresa_id = e.id
                        JOIN candidatos cand ON c.candidato_id = cand.id
                        JOIN users u_rec ON v.recrutador_id = u_rec.id
                        JOIN users u_cand ON cand.user_id = u_cand.id
                        WHERE ch.id = :cid
                    ", ['cid' => $chatId]);

                if (!$info) {
                    echo json_encode(['status' => 'error', 'message' => 'Chat não encontrado']);
                    exit;
                }

                // Verifica se sou o candidato
                if ($userId == $info['candidato_user_id']) {
                    // Contar mensagens anteriores
                    // Se for 0, bloqueia
                    $countMsg = $db->selectOne("SELECT COUNT(id) as total FROM mensagens_chat WHERE chat_id = :cid", ['cid' => $chatId]);
                    if ($countMsg['total'] == 0) {
                        echo json_encode(['status' => 'error', 'message' => 'Aguarde o recrutador iniciar a conversa.']);
                        exit;
                    }
                }

                $chatModel = new Chat();
                // Se passou, envia
                $chatModel->sendMessage($chatId, $userId, $msg);

                // --- NOTIFICAÇÃO CHAT ---
                try {
                    // $info já foi carregado acima, usamos direto
                    if ($info) {
                        $destUserId = null;
                        $destEmail = null;
                        $destName = null;
                        $remetenteName = null;

                        if ($userId != $info['candidato_user_id']) {
                            // Enviado pela Empresa (Recrutador ou Admin) -> Destino: Candidato
                            $destUserId = $info['candidato_user_id'];
                            $destEmail = $info['cand_email'];
                            $destName = $info['cand_name'];
                            $remetenteName = $info['nome_empresa']; // Usa nome da empresa pra ficar mais profissional
                        } else {
                            // Enviado pelo Candidato -> Destino: Recrutador
                            $destUserId = $info['recrutador_id'];
                            $destEmail = $info['rec_email'];
                            $destName = $info['rec_name'];
                            $remetenteName = $info['cand_name'];
                        }

                        if ($destUserId) {
                            $notifMsg = "Nova mensagem de <strong>{$remetenteName}</strong> sobre a vaga <strong>{$info['titulo']}</strong>";
                            $link = BASE_URL . "chat/index/" . $chatId;

                            // Notificação Interna
                            $notificacao = new \src\Models\Notificacao();
                            $notificacao->criar($destUserId, $notifMsg, $link);

                            // E-mail
                            $assunto = "Nova mensagem sobre a vaga: " . $info['titulo'];
                            $html = "<p>Olá, {$destName}.</p>
                                      <p>Você recebeu uma nova mensagem de <strong>{$remetenteName}</strong>.</p>
                                      <p><i>\"" . substr(htmlspecialchars($msg), 0, 50) . "...\"</i></p>
                                      <p><a href='$link'>Responder agora</a></p>";
                            \src\Utils\Mailer::enviar($destEmail, $destName, $assunto, $html);
                        }
                    }
                } catch (\Exception $e) {
                    // Ignora erro de notificação para não travar o chat
                    error_log("Erro notif chat: " . $e->getMessage());
                }
                // ------------------------

                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Dados inválidos']);
            }
            exit;
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $msgId = filter_input(INPUT_POST, 'mensagem_id', FILTER_VALIDATE_INT);
            if ($msgId && isset($_SESSION['user_id'])) {
                $chatModel = new Chat();
                // deleteMessage agora valida o dono
                $chatModel->deleteMessage($msgId, $_SESSION['user_id']);
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Ação inválida']);
            }
            exit;
        }
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $candidaturaId = filter_input(INPUT_POST, 'candidatura_id', FILTER_VALIDATE_INT);
            if ($candidaturaId) {
                $chatModel = new Chat();

                // Verifica se já existe
                $existing = $chatModel->checkExistingChat($candidaturaId);
                if ($existing) {
                    header('Location: ' . BASE_URL . 'chat/index/' . $existing['id']);
                    exit;
                }

                // Cria novo
                $newId = $chatModel->createChat($candidaturaId);
                if ($newId) {
                    header('Location: ' . BASE_URL . 'chat/index/' . $newId);
                    exit;
                }
            }
        }
        // Se falhar ou não for POST, volta pro dashboard
        // Se falhar ou não for POST, volta pro dashboard (ou referer)
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'dashboard'));
        exit;
    }
}
