<?php

namespace src\Models;

use src\Config\Database;

class Vaga
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Busca todas as vagas de um recrutador específico
     * 
     * @param int $recrutadorId O ID do usuário logado (RH)
     * @return array Lista de vagas
     */
    public function obterPorRecrutador($recrutadorId)
    {
        $sql = "SELECT 
                    v.*, 
                    e.nome_empresa,
                    e.logo_path,
                    (SELECT COUNT(*) FROM candidaturas c WHERE c.vaga_id = v.id) as total_candidatos
                FROM vagas v
                JOIN empresas e ON v.empresa_id = e.id
                WHERE v.recrutador_id = :id";

        $params = ['id' => $recrutadorId];
        if (defined('PORTAL_ATUAL')) {
            $sql .= " AND e.portal_id = :portal_id";
            $params['portal_id'] = PORTAL_ATUAL;
        }
        $sql .= " ORDER BY v.created_at DESC";

        return $this->db->select($sql, $params);
    }

    /**
     * Busca todas as vagas de uma empresa específica, incluindo informações do criador
     */
    public function obterPorEmpresa($empresaId)
    {
        $sql = "SELECT 
                    v.*, 
                    e.nome_empresa,
                    e.logo_path,
                    u.name as criador_nome,
                    (SELECT COUNT(*) FROM candidaturas c WHERE c.vaga_id = v.id) as total_candidatos
                FROM vagas v
                JOIN empresas e ON v.empresa_id = e.id
                JOIN users u ON v.recrutador_id = u.id
                WHERE v.empresa_id = :id";

        $params = ['id' => $empresaId];
        if (defined('PORTAL_ATUAL')) {
            $sql .= " AND e.portal_id = :portal_id";
            $params['portal_id'] = PORTAL_ATUAL;
        }
        $sql .= " ORDER BY v.created_at DESC";

        return $this->db->select($sql, $params);
    }

    /**
     * Busca uma vaga pelo ID
     */
    public function find($id)
    {
        $sql = "SELECT v.*, e.nome_fantasia, e.cidade, e.estado 
                FROM vagas v 
                JOIN empresas e ON v.empresa_id = e.id 
                WHERE v.id = :id";
        return $this->db->selectOne($sql, ['id' => $id]);
    }

    public function create($data)
    {
        $sql = "INSERT INTO vagas (
                    empresa_id, recrutador_id, titulo, tipo_contrato, modalidade, localizacao, cep, ramo_atividade, 
                    descricao, requisitos, beneficios, confidencial, tipo_vaga, experiencia, faixa_salarial, 
                    faixa_salarial_min, faixa_salarial_max, salario_a_combinar, created_at, portal_id, latitude, longitude
                ) VALUES (
                    :empresa_id, :recrutador_id, :titulo, :tipo_contrato, :modalidade, :localizacao, :cep, :ramo_atividade,
                    :descricao, :requisitos, :beneficios, :confidencial, :tipo_vaga, :experiencia, :faixa_salarial,
                    :faixa_salarial_min, :faixa_salarial_max, :salario_a_combinar, NOW(), :portal_id, :latitude, :longitude
                )";

        // Inject Portal ID
        if (!isset($data['portal_id'])) {
            $data['portal_id'] = defined('PORTAL_ATUAL') ? PORTAL_ATUAL : 1;
        }

        $this->db->query($sql, $data); // Assuming query() executes INSERT
        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $data['id'] = $id; // Ensure ID is in params
        $sql = "UPDATE vagas SET 
                    empresa_id=:empresa_id, titulo=:titulo, tipo_contrato=:tipo_contrato, modalidade=:modalidade, 
                    localizacao=:localizacao, cep=:cep, latitude=:latitude, longitude=:longitude, ramo_atividade=:ramo_atividade, 
                    descricao=:descricao, requisitos=:requisitos, beneficios=:beneficios, confidencial=:confidencial, 
                    tipo_vaga=:tipo_vaga, experiencia=:experiencia, faixa_salarial_min=:faixa_salarial_min, 
                    faixa_salarial_max=:faixa_salarial_max, salario_a_combinar=:salario_a_combinar, faixa_salarial=:faixa_salarial 
                WHERE id=:id AND portal_id=:portal_id";

        return $this->db->updateRaw($sql, $data); // Need a raw update method or using query
    }

    public function delete($id, $portalId)
    {
        // Transação é ideal ser controlada no Controller, mas aqui executamos as queries
        $this->db->delete('candidaturas', 'vaga_id = :id', ['id' => $id]);
        $this->db->delete('perguntas_vaga', 'vaga_id = :id', ['id' => $id]);
        return $this->db->delete('vagas', 'id = :id AND portal_id = :portal_id', ['id' => $id, 'portal_id' => $portalId]);
    }

    // --- Perguntas ---

    public function syncPerguntas($vagaId, $perguntasForm, $perguntasNovas)
    {
        // 1. Busca IDs atuais no banco
        $atuais = $this->getPerguntas($vagaId);
        $idsAtuais = array_column($atuais, 'id');
        $idsMantidos = [];

        // 2. Atualiza existentes
        if (!empty($perguntasForm)) {
            $sqlUp = "UPDATE perguntas_vaga SET texto_pergunta=:texto, eliminatoria=:eliminatoria WHERE id=:id AND vaga_id=:vaga_id";
            foreach ($perguntasForm as $id => $p) {
                $this->db->query($sqlUp, [
                    'texto' => trim($p['texto']),
                    'eliminatoria' => (int)($p['eliminatoria'] ?? 0),
                    'id' => $id,
                    'vaga_id' => $vagaId
                ]);
                $idsMantidos[] = $id;
            }
        }

        // 3. Insere novas
        if (!empty($perguntasNovas)) {
            $sqlIns = "INSERT INTO perguntas_vaga (vaga_id, texto_pergunta, eliminatoria) VALUES (:vaga_id, :texto, :eliminatoria)";
            foreach ($perguntasNovas as $p) {
                if (!empty(trim($p['texto']))) {
                    $this->db->query($sqlIns, [
                        'vaga_id' => $vagaId,
                        'texto' => trim($p['texto']),
                        'eliminatoria' => (int)($p['eliminatoria'] ?? 0)
                    ]);
                }
            }
        }

        // 4. Deleta removidas (Diff)
        $idsParaDeletar = array_diff($idsAtuais, $idsMantidos);
        if (!empty($idsParaDeletar)) {
            // PDO não aceita array direto, precisamos montar placeholders
            $placeholders = implode(',', array_fill(0, count($idsParaDeletar), '?'));
            $sqlDel = "DELETE FROM perguntas_vaga WHERE id IN ($placeholders) AND vaga_id = ?";

            // Params: IDs... + vaga_id
            $params = array_values($idsParaDeletar);
            $params[] = $vagaId;

            $this->db->query($sqlDel, $params);
        }
    }

    // --- Métodos individuais mantidos para compatibilidade se necessário, ou removidos ---
    // (getPerguntas ainda é necessário para a View)
    public function getPerguntas($vagaId)
    {
        return $this->db->select("SELECT * FROM perguntas_vaga WHERE vaga_id = :vaga_id", ['vaga_id' => $vagaId]);
    }

    public function addPergunta($vagaId, $texto, $eliminatoria)
    {
        return $this->db->insert('perguntas_vaga', [
            'vaga_id' => $vagaId,
            'texto_pergunta' => $texto,
            'eliminatoria' => $eliminatoria
        ]);
    }
}
