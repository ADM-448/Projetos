<?php

namespace src\Models;

use src\Config\Database;

class Empresa
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function isUserPrefeitura($userId)
    {
        // Verifica se o usuário tem vínculo com alguma empresa do tipo 'prefeitura'
        // Se a coluna 'tipo' não existir no banco atual, isso pode dar erro. 
        // Nesse caso, o usuário deve criar a coluna ou ajustaremos a query.
        $sql = "SELECT COUNT(*) as total 
                FROM empresa_user eu 
                JOIN empresas e ON eu.empresa_id = e.id 
                WHERE eu.user_id = :uid AND e.tipo = 'prefeitura'";

        try {
            $res = $this->db->selectOne($sql, ['uid' => $userId]);
            return $res['total'] > 0;
        } catch (\Exception $e) {
            // Fallback se a coluna não existir: retorna false ou verifica por nome
            return false;
        }
    }

    public function getByUser($userId)
    {
        $sql = "SELECT e.*, eu.role 
                FROM empresas e 
                JOIN empresa_user eu ON e.id = eu.empresa_id 
                WHERE eu.user_id = :user_id 
                ORDER BY e.nome_empresa ASC";
        return $this->db->select($sql, ['user_id' => $userId]);
    }

    public function find($id)
    {
        return $this->db->selectOne("SELECT * FROM empresas WHERE id = :id", ['id' => $id]);
    }

    public function update($id, $data)
    {
        // $data keys must match column names
        $where = "id = :id";
        $whereParams = ['id' => $id];
        return $this->db->update('empresas', $data, $where, $whereParams);
    }
    public function getEquipe($empresaId)
    {
        $sql = "SELECT u.id, u.name, u.email, eu.role 
                FROM users u
                JOIN empresa_user eu ON u.id = eu.user_id
                WHERE eu.empresa_id = :empresa_id
                ORDER BY u.name ASC";
        return $this->db->select($sql, ['empresa_id' => $empresaId]);
    }

    public function getUserRole($userId, $empresaId)
    {
        $sql = "SELECT role FROM empresa_user WHERE user_id = :uid AND empresa_id = :eid";
        $res = $this->db->selectOne($sql, ['uid' => $userId, 'eid' => $empresaId]);
        return $res ? $res['role'] : null;
    }

    public function updateRole($userId, $empresaId, $role)
    {
        return $this->db->update('empresa_user', ['role' => $role], "user_id = :uid AND empresa_id = :eid", ['uid' => $userId, 'eid' => $empresaId]);
    }

    public function transferVagas($fromUserId, $toUserId, $empresaId)
    {
        $sql = "UPDATE vagas SET recrutador_id = :to_id WHERE recrutador_id = :from_id AND empresa_id = :eid";
        return $this->db->updateRaw($sql, ['to_id' => $toUserId, 'from_id' => $fromUserId, 'eid' => $empresaId]);
    }

    public function removeMember($userId, $empresaId)
    {
        // Remove vínculo
        $this->db->delete('empresa_user', "user_id = :uid AND empresa_id = :eid", ['uid' => $userId, 'eid' => $empresaId]);

        // Remove usuário completo (conforme lógica original de exclusão total)
        return $this->db->delete('users', "id = :id", ['id' => $userId]);
    }

    public function deleteVagasMember($userId, $empresaId)
    {
        return $this->db->delete('vagas', "recrutador_id = :uid AND empresa_id = :eid", ['uid' => $userId, 'eid' => $empresaId]);
    }

    // --- Convites ---

    public function createInvite($empresaId, $creatorId, $type = 'unico')
    {
        $codigo = ($type === 'unico')
            ? 'CONV-' . uniqid() . '-' . rand(1000, 9999)
            : 'CONV-REU-' . $empresaId . '-' . uniqid();

        $expiration = ($type === 'unico') ? date('Y-m-d H:i:s', strtotime('+24 hours')) : null;

        $this->db->insert('convites_empresa', [
            'empresa_id' => $empresaId,
            'codigo' => $codigo,
            'criado_por_user_id' => $creatorId,
            'tipo' => $type,
            'data_expiracao' => $expiration
        ]);

        return $codigo;
    }

    public function getValidInvite($codigo)
    {
        $sql = "SELECT * FROM convites_empresa 
                WHERE codigo = :code 
                AND (
                    (tipo = 'unico' AND usado = 0 AND data_expiracao > NOW()) 
                    OR 
                    (tipo = 'reutilizavel' AND (data_expiracao IS NULL OR data_expiracao > NOW()))
                )";
        return $this->db->selectOne($sql, ['code' => $codigo]);
    }

    public function markInviteUsed($inviteId)
    {
        // Se for único, deleta ou marca usado. O legado deletava.
        return $this->db->delete('convites_empresa', "id = :id AND tipo='unico'", ['id' => $inviteId]);
    }

    public function getReusableInvite($empresaId)
    {
        $sql = "SELECT codigo FROM convites_empresa 
                WHERE empresa_id = :eid AND tipo = 'reutilizavel' 
                AND (data_expiracao IS NULL OR data_expiracao > NOW()) 
                LIMIT 1";
        return $this->db->selectOne($sql, ['eid' => $empresaId]);
    }
    // --- Métodos Administrativos (AdminController) ---

    public function getAll($tipo = null)
    {
        // Join para pegar o responsável (primeiro admin encontrado)
        $sql = "SELECT e.*, 
                (SELECT COUNT(*) FROM vagas v WHERE v.empresa_id = e.id) as total_vagas,
                u.name as responsavel_nome,
                u.email as responsavel_email,
                u.cpf as responsavel_cpf
                FROM empresas e
                LEFT JOIN users u ON u.id = (
                    SELECT user_id FROM empresa_user 
                    WHERE empresa_id = e.id AND role = 'admin' 
                    ORDER BY id ASC LIMIT 1
                )
                WHERE 1=1";

        $params = [];
        if ($tipo) {
            $sql .= " AND e.tipo = :tipo";
            $params['tipo'] = $tipo;
        }

        // Multi-tenancy
        // SE FOR ADMIN GERAL (tipo 3), ele quer ver TUDO, não só do portal atual.
        // A sessão tem user_id, precisamos checar o tipo.
        $userType = $_SESSION['tipo_usuario'] ?? $_SESSION['user_type'] ?? 0;

        if (defined('PORTAL_ATUAL') && $userType != 3) {
            $sql .= " AND (e.portal_id = :portal_id OR e.id = :portal_id_check)";
            $params['portal_id'] = PORTAL_ATUAL;
            $params['portal_id_check'] = PORTAL_ATUAL;
        }

        $sql .= " ORDER BY e.created_at DESC, e.nome_empresa ASC";

        return $this->db->select($sql, $params);
    }

    public function create($data)
    {
        // $data deve conter ['nome_empresa', 'tipo', ...]
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('empresas', $data);
    }

    public function deleteFull($id)
    {
        // Exclusão completa (Hard Delete)
        try {
            // 1. Apagar convites
            $this->db->delete('convites_empresa', "empresa_id = :id", ['id' => $id]);

            // 2. Desvincular usuários (ou apagar se for regra de negócio)
            $this->db->delete('empresa_user', "empresa_id = :id", ['id' => $id]);

            // 3. Apagar Vagas
            $this->db->delete('vagas', "empresa_id = :id", ['id' => $id]);

            // 4. Apagar a Empresa
            return $this->db->delete('empresas', "id = :id", ['id' => $id]);
        } catch (\Exception $e) {
            return false;
        }
    }
}
