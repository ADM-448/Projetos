<?php

namespace src\Models;

use src\Config\Database;

class Notificacao
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Cria uma nova notificação
     */
    public function criar($userId, $mensagem, $link = null)
    {
        return $this->db->insert('notificacoes', [
            'user_id' => $userId,
            'mensagem' => $mensagem,
            'link' => $link,
            'lida' => 0,
            'criada_em' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Busca notificações não lidas e recentes
     */
    public function obterNotificacoesHeader($userId)
    {
        // 1. Contagem não lidas
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) FROM notificacoes WHERE user_id = :uid AND lida = 0");
        $stmt->execute([':uid' => $userId]);
        $nao_lidas = $stmt->fetchColumn();

        // 2. Lista Recente (5)
        $notificacoes = $this->db->select("
            SELECT * FROM notificacoes 
            WHERE user_id = :uid 
            ORDER BY criada_em DESC 
            LIMIT 5
        ", ['uid' => $userId]);

        return [
            'nao_lidas' => $nao_lidas,
            'lista' => $notificacoes
        ];
    }

    /**
     * Marca todas como lidas
     */
    public function marcarTodasComoLidas($userId)
    {
        return $this->db->update('notificacoes', ['lida' => 1], 'user_id = :uid AND lida = 0', ['uid' => $userId]);
    }
}
