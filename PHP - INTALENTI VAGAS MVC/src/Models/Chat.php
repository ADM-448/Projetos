<?php

namespace src\Models;

use src\Config\Database;

class Chat
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getChatInfo($chatId)
    {
        $sql = "SELECT 
                    ch.id,
                    v.titulo AS vaga_titulo,
                    e.id AS empresa_id,
                    e.nome_empresa,
                    cand.nome_completo AS nome_candidato,
                    cand.user_id AS candidato_user_id,
                    v.recrutador_id,
                    c.candidato_id, 
                    c.vaga_id
                FROM chats ch
                JOIN candidaturas c ON ch.candidatura_id = c.id
                JOIN vagas v ON c.vaga_id = v.id
                JOIN empresas e ON v.empresa_id = e.id
                JOIN candidatos cand ON c.candidato_id = cand.id
                WHERE ch.id = :chat_id";

        return $this->db->selectOne($sql, ['chat_id' => $chatId]);
    }

    public function getMessages($chatId, $lastMsgId = 0)
    {
        $sql = "SELECT * FROM mensagens_chat 
                WHERE chat_id = :chat_id AND id > :last_id 
                ORDER BY enviada_em ASC";
        return $this->db->select($sql, ['chat_id' => $chatId, 'last_id' => $lastMsgId]);
    }

    public function sendMessage($chatId, $senderId, $message)
    {
        return $this->db->insert('mensagens_chat', [
            'chat_id' => $chatId,
            'remetente_id' => $senderId,
            'mensagem' => $message
            // 'created_at' geralmente é automático na tabela
        ]);
    }

    public function deleteMessage($msgId, $userId)
    {
        return $this->db->delete('mensagens_chat', 'id = :id AND remetente_id = :uid', [
            'id' => $msgId,
            'uid' => $userId
        ]);
    }

    public function checkExistingChat($candidaturaId)
    {
        return $this->db->selectOne("SELECT id FROM chats WHERE candidatura_id = :id", ['id' => $candidaturaId]);
    }

    public function createChat($candidaturaId)
    {
        return $this->db->insert('chats', [
            'candidatura_id' => $candidaturaId,
            'status' => 'aberto'
        ]);
    }
}
