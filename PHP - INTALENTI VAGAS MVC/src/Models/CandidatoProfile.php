<?php

namespace src\Models;

use src\Config\Database;
use PDO;

class CandidatoProfile
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Busca perfil completo pelo USER ID
     */
    public function getFullProfileByUserId($userId)
    {
        // Garante que o CPF venha da tabela users se não existir ou estiver vazio em candidatos
        // COALESCE(c.cpf, u.cpf) seria ideal se os dois existissem, mas como não sabemos o schema exato,
        // vamos trazer u.cpf explicitamente aliasado para garantir.
        // Se c.* trouxer 'cpf', ele sobrescreve chaves numéricas ou associativas dependendo do driver.
        // Vamos selecionar u.cpf AS user_cpf para ter certeza.
        $sql = "SELECT u.name, u.email, u.cpf AS user_cpf, c.* 
                FROM candidatos c
                JOIN users u ON c.user_id = u.id
                WHERE u.id = :user_id";

        $candidato = $this->db->selectOne($sql, ['user_id' => $userId]);

        if (!$candidato) return null;

        $candidatoId = $candidato['id']; // ID da tabela candidatos

        // Busca tabelas relacionadas
        $candidato['experiencias'] = $this->db->select("SELECT * FROM experiencias_profissionais WHERE candidato_id = :id ORDER BY data_inicio DESC", ['id' => $candidatoId]);
        $candidato['formacoes'] = $this->db->select("SELECT * FROM formacoes_academicas WHERE candidato_id = :id ORDER BY data_inicio DESC", ['id' => $candidatoId]);
        $candidato['cursos'] = $this->db->select("SELECT * FROM cursos_complementares WHERE candidato_id = :id ORDER BY ano_conclusao DESC", ['id' => $candidatoId]);

        return $candidato;
    }

    /**
     * Busca dados da candidatura (Status, Chat, Questionário)
     */
    public function getCandidaturaDetails($candidatoId, $vagaId)
    {
        // Busca candidatura e chat
        $sql = "SELECT ca.id, ca.status, ch.id AS chat_id
                FROM candidaturas ca
                LEFT JOIN chats ch ON ca.id = ch.candidatura_id
                WHERE ca.candidato_id = :candidato_id AND ca.vaga_id = :vaga_id";

        $candidatura = $this->db->selectOne($sql, ['candidato_id' => $candidatoId, 'vaga_id' => $vagaId]);

        if ($candidatura) {
            // Busca respostas do questionário
            $sqlResp = "SELECT p.texto_pergunta, r.resposta
                        FROM respostas_candidato r
                        JOIN perguntas_vaga p ON r.pergunta_id = p.id
                        WHERE r.candidatura_id = :id";
            $candidatura['respostas'] = $this->db->select($sqlResp, ['id' => $candidatura['id']]);
        }

        return $candidatura;
    }

    /**
     * Salva/Atualiza o currículo completo
     */
    public function saveProfile($userId, $data, $files = null)
    {
        $pdo = $this->db->getConnection();

        try {
            $pdo->beginTransaction();

            // 1. Upload de Arquivo
            $curriculoPath = null;
            if (isset($files['curriculo_file']) && $files['curriculo_file']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($files['curriculo_file']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['pdf', 'doc', 'docx'])) {
                    throw new \Exception("Formato inválido. Apenas PDF, DOC ou DOCX.");
                }

                $uploadDir = 'uploads/curriculos/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $filename = $userId . '_' . time() . '.' . $ext;
                if (move_uploaded_file($files['curriculo_file']['tmp_name'], $uploadDir . $filename)) {
                    $curriculoPath = $filename;
                }
            }

            // Verifica dados atuais
            $currentData = $this->db->selectOne("SELECT id, curriculo_path FROM candidatos WHERE user_id = :uid", ['uid' => $userId]);
            $candidatoId = $currentData['id'] ?? null;

            // Se enviou arquivo novo e já tinha um antigo, apaga o antigo
            if ($curriculoPath && $currentData && !empty($currentData['curriculo_path'])) {
                $oldFile = 'uploads/curriculos/' . $currentData['curriculo_path'];
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }

            // Mantém arquivo antigo se não enviou novo
            if (!$curriculoPath && $currentData) {
                $curriculoPath = $currentData['curriculo_path'];
            }

            // --- GEOCODING AUTOMÁTICO ---
            // Tenta obter Lat/Long pelo CEP
            $cep = $data['cep_candidato'] ?? '';
            // Remove máscara
            $cepLimpo = preg_replace('/[^0-9]/', '', $cep);

            if (!empty($cepLimpo)) {
                $geoData = \src\Utils\GeoUtils::validateCEP($cepLimpo);
                if ($geoData && !empty($geoData['latitude'])) {
                    $data['latitude'] = $geoData['latitude'];
                    $data['longitude'] = $geoData['longitude'];

                    // Atualiza cidade/estado se estiverem vazios ou para garantir consistência
                    if (empty($data['cidade']) && !empty($geoData['city'])) $data['cidade'] = $geoData['city'];
                    if (empty($data['estado']) && !empty($geoData['state'])) $data['estado'] = $geoData['state'];
                }
            }

            // 3. Atualiza ou Cria Candidato
            $fields = [
                'nome_completo' => $data['nome_completo'],
                'data_nascimento' => $data['data_nascimento'],
                'telefone' => $data['telefone'] ?? '',
                'whatsapp' => $data['whatsapp'] ?? '',
                'cep' => $data['cep_candidato'] ?? '',
                'cidade' => $data['cidade'] ?? '',
                'estado' => $data['estado'] ?? '',
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'resumo_profissional' => $data['resumo_profissional'] ?? '',
                'sobre_mim' => $data['sobre_mim'] ?? '',
                'situacao_profissional' => $data['situacao_profissional'] ?? '',
                'cargo_pretendido' => $data['cargo_pretendido'] ?? '',
                'faixa_salarial' => $data['faixa_salarial'] ?? '',
                'estado_civil' => $data['estado_civil'] ?? '',
                'escolaridade' => $data['escolaridade'] ?? '',
                'sexo' => $data['sexo'] ?? '',
                'nacionalidade' => $data['nacionalidade'] ?? 'Brasileiro(a)',
                'rne' => $data['rne'] ?? '',
                'curriculo_path' => $curriculoPath,
                'email' => $data['email'] ?? ''
            ];

            // Tratamento de Arrays (Implode)
            $fields['idiomas'] = isset($data['idiomas']) ? implode(', ', $data['idiomas']) : '';
            $fields['carteira_habilitacao'] = isset($data['carteira_habilitacao']) ? implode(', ', $data['carteira_habilitacao']) : '';

            if ($candidatoId) {
                $this->db->update('candidatos', $fields, 'id = :id', ['id' => $candidatoId]);
            } else {
                $fields['user_id'] = $userId;
                $candidatoId = $this->db->insert('candidatos', $fields);
            }

            // 4. Experiências (Delete + Insert)
            // Using query() - ensure Database class has this method
            // 4. Experiências (Delete + Insert)
            $this->db->delete('experiencias_profissionais', 'candidato_id = :cid', ['cid' => $candidatoId]);

            if (!empty($data['exp_cargo']) && is_array($data['exp_cargo'])) {
                foreach ($data['exp_cargo'] as $i => $cargo) {
                    if (!empty($cargo)) {
                        $this->db->insert('experiencias_profissionais', [
                            'candidato_id' => $candidatoId,
                            'cargo' => $cargo,
                            'empresa' => $data['exp_empresa'][$i] ?? '',
                            'descricao' => $data['exp_descricao'][$i] ?? '',
                            'data_inicio' => !empty($data['exp_data_inicio'][$i]) ? $data['exp_data_inicio'][$i] : null,
                            'data_fim' => !empty($data['exp_data_fim'][$i]) ? $data['exp_data_fim'][$i] : null,
                            'emprego_atual' => $data['exp_emprego_atual'][$i] ?? 0
                        ]);
                    }
                }
            }

            // 5. Formações (Delete + Insert)
            // 5. Formações (Delete + Insert)
            $this->db->delete('formacoes_academicas', 'candidato_id = :cid', ['cid' => $candidatoId]);

            if (!empty($data['form_grau']) && is_array($data['form_grau'])) {
                foreach ($data['form_grau'] as $i => $grau) {
                    if (!empty($grau)) {
                        $this->db->insert('formacoes_academicas', [
                            'candidato_id' => $candidatoId,
                            'grau' => $grau,
                            'curso' => $data['form_curso'][$i] ?? '',
                            'instituicao' => $data['form_instituicao'][$i] ?? '',
                            'localidade' => $data['form_localidade'][$i] ?? '',
                            'data_inicio' => !empty($data['form_data_inicio'][$i]) ? $data['form_data_inicio'][$i] : null,
                            'data_fim' => !empty($data['form_data_fim'][$i]) ? $data['form_data_fim'][$i] : null,
                            'status' => $data['form_status'][$i] ?? ''
                        ]);
                    }
                }
            }

            // 6. Cursos (Delete + Insert)
            // 6. Cursos (Delete + Insert)
            $this->db->delete('cursos_complementares', 'candidato_id = :cid', ['cid' => $candidatoId]);

            if (!empty($data['curso_nome']) && is_array($data['curso_nome'])) {
                foreach ($data['curso_nome'] as $i => $nome) {
                    if (!empty($nome)) {
                        $this->db->insert('cursos_complementares', [
                            'candidato_id' => $candidatoId,
                            'nome_curso' => $nome,
                            'instituicao' => $data['curso_instituicao'][$i] ?? '',
                            'localidade' => $data['curso_localidade'][$i] ?? '',
                            'ano_conclusao' => !empty($data['curso_ano_conclusao'][$i]) ? $data['curso_ano_conclusao'][$i] : null
                        ]);
                    }
                }
            }

            // 7. Atualiza Nome User
            $this->db->update('users', ['name' => $data['nome_completo']], 'id = :uid', ['uid' => $userId]);

            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
