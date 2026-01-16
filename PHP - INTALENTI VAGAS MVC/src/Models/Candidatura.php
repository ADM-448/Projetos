<?php

namespace src\Models;

use src\Config\Database;

class Candidatura
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Busca candidatos de uma vaga específica
     * Ordenado por data (Recentes)
     */
    /**
     * Busca candidatos de uma vaga específica
     * Ordenado por data (Recentes) ou proximidade
     */
    public function obterPorVaga($vagaId, $ordem = 'recentes', $vagaLat = null, $vagaLong = null)
    {
        $camposExtras = "";
        $orderBy = "cand.data_candidatura DESC";
        $params = ['vaga_id' => $vagaId];

        if ($ordem === 'proximos' && !empty($vagaLat) && !empty($vagaLong)) {
            // Fórmula Haversine
            $camposExtras = ", (
                6371 * acos(
                    LEAST(1.0, GREATEST(-1.0, 
                        cos(radians(:vlat)) * cos(radians(c.latitude)) *
                        cos(radians(c.longitude) - radians(:vlong)) +
                        sin(radians(:vlat)) * sin(radians(c.latitude))
                    ))
                )
            ) AS distancia_km";

            $orderBy = "distancia_km ASC";
            $params['vlat'] = $vagaLat;
            $params['vlong'] = $vagaLong;
        } else {
            // Em 'recentes', calcular distância só pra mostrar no badge (opcional, mas bom ter)
            if (!empty($vagaLat) && !empty($vagaLong)) {
                $camposExtras = ", (
                    6371 * acos(
                        LEAST(1.0, GREATEST(-1.0, 
                            cos(radians(:vlat)) * cos(radians(c.latitude)) *
                            cos(radians(c.longitude) - radians(:vlong)) +
                            sin(radians(:vlat)) * sin(radians(c.latitude))
                        ))
                    )
                ) AS distancia_km";
                $params['vlat'] = $vagaLat;
                $params['vlong'] = $vagaLong;
            }
        }

        $sql = "SELECT 
                    u.id as user_id,
                    u.name as nome_candidato,
                    u.email,
                    c.id AS candidato_profile_id,
                    c.curriculo_path,
                    c.cidade, 
                    c.estado,
                    cand.status,
                    cand.data_candidatura as created_at
                    {$camposExtras}
                FROM candidaturas cand
                JOIN candidatos c ON cand.candidato_id = c.id
                JOIN users u ON c.user_id = u.id
                WHERE cand.vaga_id = :vaga_id
                ORDER BY {$orderBy}";

        return $this->db->select($sql, $params);
    }

    /**
     * Busca todas as candidaturas de um candidato especifico
     * Incluindo informa??es da vaga, empresa e chat
     */
    public function obterPorCandidato($candidatoId)
    {
        $sql = "SELECT 
                c.id as candidatura_id, 
                c.status, 
                c.data_candidatura,
                v.id as vaga_id, 
                v.titulo, 
                v.localizacao, 
                v.confidencial,
                e.nome_empresa, 
                e.logo_path,
                ch.id AS chat_id,
                ch.status AS chat_status,
                (SELECT COUNT(id) FROM mensagens_chat WHERE chat_id = ch.id) as msg_count
              FROM candidaturas c
              JOIN vagas v ON c.vaga_id = v.id
              JOIN empresas e ON v.empresa_id = e.id
              LEFT JOIN chats ch ON c.id = ch.candidatura_id
              WHERE c.candidato_id = :cid
              ORDER BY c.data_candidatura DESC";

        return $this->db->select($sql, ['cid' => $candidatoId]);
    }

    /**
     * Atualiza o status de uma candidatura
     */
    public function atualizarStatus($candidaturaId, $novoStatus)
    {
        return $this->db->update(
            'candidaturas',
            ['status' => $novoStatus],
            'id = :id',
            ['id' => $candidaturaId]
        );
    }
}
