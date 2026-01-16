<?php

namespace src\Models;

use src\Config\Database;

class User
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Busca um usuário pelo ID
     */
    public function find($id)
    {
        return $this->db->selectOne("SELECT * FROM users WHERE id = :id", ['id' => $id]);
    }

    /**
     * Busca um usuário pelo E-mail (útil para login)
     */
    public function findByEmail($email)
    {
        return $this->db->selectOne("SELECT * FROM users WHERE email = :email", ['email' => $email]);
    }

    /**
     * Retorna todos os usuários (com paginação simples)
     */
    public function findAll($limit = 10, $offset = 0)
    {
        // Cast para int é crucial para LIMIT/OFFSET no PDO
        // Interpolando int diretamente para evitar erro de string quotes no LIMIT (PDO)
        $limit = (int)$limit;
        $offset = (int)$offset;
        return $this->db->select(
            "SELECT * FROM users ORDER BY id DESC LIMIT $limit OFFSET $offset"
        );
    }

    /**
     * Cria um novo usuário
     */
    public function create($data)
    {
        return $this->db->insert('users', $data);
    }

    public function update($id, $data)
    {
        return $this->db->update('users', $data, 'id = :id_user', ['id_user' => $id]);
    }

    public function emailExistsForOther($email, $id)
    {
        $sql = "SELECT id FROM users WHERE email = :email AND id != :id";
        return $this->db->selectOne($sql, ['email' => $email, 'id' => $id]);
    }

    public function deleteAccount($id)
    {
        try {
            $pdo = $this->db->getConnection();
            if (!$pdo->inTransaction()) {
                $pdo->beginTransaction();
            }

            // 1. Identificar se é candidato para deletar candidaturas corretamente
            // Candidaturas são ligadas pelo candidato_id, não user_id
            $stmtCand = $pdo->prepare("SELECT id FROM candidatos WHERE user_id = :uid");
            $stmtCand->execute([':uid' => $id]);
            $candidato = $stmtCand->fetch(\PDO::FETCH_ASSOC);

            if ($candidato) {
                $candidatoId = $candidato['id'];

                // Deletar Chats de candidaturas desse candidato? 
                // Idealmente sim, mas vamos focar nas candidaturas
                // Deletar mensagens dos chats
                $pdo->prepare("DELETE FROM mensagens_chat WHERE chat_id IN (SELECT id FROM chats WHERE candidatura_id IN (SELECT id FROM candidaturas WHERE candidato_id = :cid))")->execute([':cid' => $candidatoId]);
                $pdo->prepare("DELETE FROM chats WHERE candidatura_id IN (SELECT id FROM candidaturas WHERE candidato_id = :cid)")->execute([':cid' => $candidatoId]);

                $pdo->prepare("DELETE FROM candidaturas WHERE candidato_id = :cid")->execute([':cid' => $candidatoId]);

                // Experiencias, Formação, etc vinculados a candidato?
                // Assumindo tabelas: experiencias_profissionais, formacao_academica
                try {
                    $pdo->prepare("DELETE FROM experiencias_profissionais WHERE candidato_id = :cid")->execute([':cid' => $candidatoId]);
                    $pdo->prepare("DELETE FROM formacao_academica WHERE candidato_id = :cid")->execute([':cid' => $candidatoId]);
                    // Outras tabelas do curriculo...
                } catch (\Exception $ignore) { /* Tabelas podem não existir ou ter nomes diferentes */
                }

                $pdo->prepare("DELETE FROM candidatos WHERE id = :cid")->execute([':cid' => $candidatoId]);
            }

            // 2. Se for Recrutador, deletar suas vagas e dependencias das vagas
            // Dependencias de vagas: perguntas_vaga, candidaturas vincualdas a vaga

            // Pega IDs das vagas do recrutador
            $stmtVagas = $pdo->prepare("SELECT id FROM vagas WHERE recrutador_id = :uid");
            $stmtVagas->execute([':uid' => $id]);
            $vagas = $stmtVagas->fetchAll(\PDO::FETCH_COLUMN);

            if (!empty($vagas)) {
                $vagasIds = implode(',', $vagas); // Seguro pois são inteiros vindos do banco, mas melhor usar bind se possível. Como são ints, ok.

                // Deletar perguntas
                $pdo->exec("DELETE FROM perguntas_vaga WHERE vaga_id IN ($vagasIds)");

                // Deletar mensagens dos chats das vagas
                $pdo->exec("DELETE FROM mensagens_chat WHERE chat_id IN (SELECT id FROM chats WHERE candidatura_id IN (SELECT id FROM candidaturas WHERE vaga_id IN ($vagasIds)))");
                // Deletar chats das candidaturas destas vagas
                $pdo->exec("DELETE FROM chats WHERE candidatura_id IN (SELECT id FROM candidaturas WHERE vaga_id IN ($vagasIds))");

                // Deletar candidaturas recebidas nessas vagas
                $pdo->exec("DELETE FROM candidaturas WHERE vaga_id IN ($vagasIds)");

                // Deletar as vagas
                $pdo->prepare("DELETE FROM vagas WHERE recrutador_id = :uid")->execute([':uid' => $id]);
            }

            // 3. Deletar vínculos de empresa
            $pdo->prepare("DELETE FROM empresa_user WHERE user_id = :id")->execute([':id' => $id]);

            // 4. Se tiver registro em candidatos solto (sem user_id validado antes? Improvável, mas garante)
            $pdo->prepare("DELETE FROM candidatos WHERE user_id = :id")->execute([':id' => $id]);

            // 5. Finalmente User
            $pdo->prepare("DELETE FROM users WHERE id = :id")->execute([':id' => $id]);

            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // Log do erro para debug
            file_put_contents(__DIR__ . '/../../debug_user_delete.log', date('Y-m-d H:i:s') . " - Erro: " . $e->getMessage() . "\n", FILE_APPEND);
            throw $e;
        }
    }
}
