<?php

namespace src\Models;

use src\Config\Database;

class Relatorio
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // --- KPIs GERAIS ---

    public function getTotalVagasAtivas($empresaId = null)
    {
        $sql = "SELECT COUNT(*) as total FROM vagas";
        $params = [];
        if ($empresaId) {
            $sql .= " WHERE empresa_id = :eid";
            $params['eid'] = $empresaId;
        }
        return $this->db->selectOne($sql, $params)['total'];
    }

    public function getTotalEmpresas()
    {
        return $this->db->selectOne("SELECT COUNT(*) as total FROM empresas")['total'];
    }

    public function getTotalCandidatos($empresaId = null)
    {
        if ($empresaId) {
            // Contar apenas candidatos que aplicaram para vagas DESTA empresa
            $sql = "SELECT COUNT(DISTINCT c.id) as total 
                    FROM candidatos c
                    JOIN candidaturas cand ON c.id = cand.candidato_id
                    JOIN vagas v ON cand.vaga_id = v.id
                    WHERE v.empresa_id = :eid";
            return $this->db->selectOne($sql, ['eid' => $empresaId])['total'];
        }
        return $this->db->selectOne("SELECT COUNT(*) as total FROM candidatos")['total'];
    }

    public function getTotalInteracoes($empresaId = null)
    {
        $sql = "SELECT COUNT(*) as total FROM candidaturas c";
        $params = [];

        if ($empresaId) {
            $sql .= " JOIN vagas v ON c.vaga_id = v.id WHERE v.empresa_id = :eid";
            $params['eid'] = $empresaId;
        }

        return $this->db->selectOne($sql, $params)['total'];
    }

    // --- KPIs ESTRATÉGICOS (O "Very Good") ---

    public function getImpactoEconomicoEstimado($empresaId = null)
    {
        $sql = "SELECT SUM(COALESCE(faixa_salarial_min, 0)) as total FROM vagas";
        $params = [];
        if ($empresaId) {
            $sql .= " WHERE empresa_id = :eid";
            $params['eid'] = $empresaId;
        }
        $result = $this->db->selectOne($sql, $params);
        return $result['total'] ?? 0;
    }

    public function getVagasPorSetor($empresaId = null)
    {
        $where = "WHERE ramo_atividade IS NOT NULL AND ramo_atividade != ''";
        $params = [];

        if ($empresaId) {
            $where .= " AND empresa_id = :eid";
            $params['eid'] = $empresaId;
        }

        $sql = "SELECT ramo_atividade, COUNT(*) as total 
                FROM vagas 
                {$where}
                GROUP BY ramo_atividade 
                ORDER BY total DESC 
                LIMIT 5";
        return $this->db->select($sql, $params);
    }

    public function getDadosMapaCalorVagas($empresaId = null)
    {
        $sql = "SELECT titulo, latitude, longitude, id FROM vagas 
                WHERE latitude IS NOT NULL AND longitude IS NOT NULL AND latitude != 0";
        $params = [];
        if ($empresaId) {
            $sql .= " AND empresa_id = :eid";
            $params['eid'] = $empresaId;
        }
        return $this->db->select($sql, $params);
    }

    public function getDadosMapaCalorCandidatos($empresaId = null)
    {
        $params = [];
        if ($empresaId) {
            // Join para pegar apenas candidatos aplicados às vagas da empresa
            $sql = "SELECT c.id, c.latitude, c.longitude 
                    FROM candidatos c
                    JOIN candidaturas cand ON c.id = cand.candidato_id
                    JOIN vagas v ON cand.vaga_id = v.id
                    WHERE c.latitude IS NOT NULL AND c.longitude IS NOT NULL AND c.latitude != 0
                    AND v.empresa_id = :eid";
            $params['eid'] = $empresaId;
            // distinct para não repetir bolinhas se aplicou em varias vagas? 
            // no Heatmap weight acumula? Bom, distinct id é melhor.
            $sql = str_replace("SELECT c.id", "SELECT DISTINCT c.id", $sql);
        } else {
            $sql = "SELECT id, latitude, longitude FROM candidatos 
                    WHERE latitude IS NOT NULL AND longitude IS NOT NULL AND latitude != 0";
        }
        return $this->db->select($sql, $params);
    }

    // --- KPI COMPARATIVO (Novo: Substitui gráfico de linha) ---
    public function getKpiComparativo($periodo, $empresaId = null)
    {
        $dias = 0;
        switch ($periodo) {
            case 'semana':
                $dias = 7;
                break;
            case 'mes':
                $dias = 30;
                break;
            case 'semestre': // Semestre
            default:
                $dias = 180;
                break;
        }

        $whereBase = "";
        $params = [];
        if ($empresaId) {
            $whereBase = " AND empresa_id = :eid";
            $params['eid'] = $empresaId;
        }

        // Período Atual
        $sqlAtual = "SELECT COUNT(*) as total FROM vagas 
                     WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :dias DAY) {$whereBase}";
        $pAtual = array_merge(['dias' => $dias], $params);
        $totalAtual = $this->db->selectOne($sqlAtual, $pAtual)['total'];

        // Período Anterior
        $sqlAnterior = "SELECT COUNT(*) as total FROM vagas 
                        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :dias2 DAY)
                        AND created_at < DATE_SUB(CURDATE(), INTERVAL :dias1 DAY) {$whereBase}";

        $pAnterior = array_merge([
            'dias2' => $dias * 2,
            'dias1' => $dias
        ], $params);

        $totalAnterior = $this->db->selectOne($sqlAnterior, $pAnterior)['total'];

        // Cálculos
        $diferenca = $totalAtual - $totalAnterior;
        $percentual = 0;

        if ($totalAnterior > 0) {
            $percentual = ($diferenca / $totalAnterior) * 100;
        } elseif ($totalAtual > 0) {
            $percentual = 100; // Se era 0 e virou algo, subiu 100% (ou infinito, mas 100 fica visualmente ok)
        }

        return [
            'total_atual' => $totalAtual,
            'total_anterior' => $totalAnterior,
            'diferenca' => $diferenca,
            'percentual' => round($percentual, 1)
        ];
    }

    // Dados de evolução (Mantido se precisar para outros fins, mas não usado no principal)
    public function getEvolucaoVagasMensal($meses = 6)
    {
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '%m/%Y') as mes, 
                    COUNT(*) as total 
                FROM vagas 
                WHERE created_at >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL :meses MONTH)
                GROUP BY mes, YEAR(created_at), MONTH(created_at)
                ORDER BY YEAR(created_at) ASC, MONTH(created_at) ASC";
        return $this->db->select($sql, ['meses' => $meses]);
    }

    // --- LISTAGENS PARA MODAIS ---

    public function getListaVagasRecentes($limit = 10)
    {
        $sql = "SELECT v.id, v.titulo, v.created_at, v.salario_a_combinar, v.faixa_salarial, 
                       e.nome_empresa 
                FROM vagas v
                LEFT JOIN empresas e ON v.empresa_id = e.id
                ORDER BY v.created_at DESC 
                LIMIT " . (int)$limit;
        return $this->db->select($sql);
    }

    public function getListaEmpresasRecentes($limit = 10)
    {
        $sql = "SELECT id, nome_empresa, cidade, estado, created_at, tipo 
                FROM empresas 
                ORDER BY created_at DESC 
                LIMIT " . (int)$limit;
        return $this->db->select($sql);
    }

    public function getListaCandidatosRecentes($limit = 10, $empresaId = null)
    {
        if ($empresaId) {
            // Candidatos que aplicaram recentemente para vagas da empresa
            $sql = "SELECT DISTINCT c.id, c.nome_completo as nome, c.cidade, c.estado 
                    FROM candidatos c
                    JOIN candidaturas cand ON c.id = cand.candidato_id
                    JOIN vagas v ON cand.vaga_id = v.id
                    WHERE v.empresa_id = :eid
                    ORDER BY cand.data_candidatura DESC
                    LIMIT " . (int)$limit;
            return $this->db->select($sql, ['eid' => $empresaId]);
        }

        $sql = "SELECT id, nome_completo as nome, cidade, estado, created_at 
                FROM candidatos 
                ORDER BY created_at DESC 
                LIMIT " . (int)$limit;
        return $this->db->select($sql);
    }
    public function getDadosGraficoEvolucao($periodo, $empresaId = null)
    {
        $labels = [];
        $data = [];

        // Configuração Inicial por Período
        $fim = new \DateTime(); // Data Final (Hoje)
        $inicio = clone $fim;   // Será ajustada abaixo

        if ($periodo === 'semana') {
            // Últimos 7 dias (contando hoje) -> Hoje - 6 dias
            $inicio->modify('-6 days');
            $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m-%d') as chave, COUNT(*) as total 
                    FROM vagas 
                    WHERE created_at >= :data_inicio";
            $formatoLabel = 'd/m';
            $formatoChave = 'Y-m-d';
            $modifyStep = '+1 day';
        } elseif ($periodo === 'mes') {
            // Últimos 30 dias (contando hoje) -> Hoje - 29 dias
            $inicio->modify('-29 days');
            $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m-%d') as chave, COUNT(*) as total 
                    FROM vagas 
                    WHERE created_at >= :data_inicio";
            $formatoLabel = 'd/m';
            $formatoChave = 'Y-m-d';
            $modifyStep = '+1 day';
        } else {
            // Semestre: 6 meses (Mês atual + 5 para trás)
            $inicio->modify('first day of this month');
            $inicio->modify('-5 months');

            $sql = "SELECT DATE_FORMAT(created_at, '%m/%Y') as chave, COUNT(*) as total 
                    FROM vagas 
                    WHERE created_at >= :data_inicio";
            $formatoLabel = 'm/Y';
            $formatoChave = 'm/Y';
            $modifyStep = '+1 month';
        }

        $params = ['data_inicio' => $inicio->format('Y-m-d 00:00:00')];
        if ($empresaId) {
            $sql .= " AND empresa_id = :eid";
            $params['eid'] = $empresaId;
        }

        $sql .= " GROUP BY chave";

        // 1. Busca Dados no Banco
        $resultados = $this->db->select($sql, $params);

        // 2. Transforma em Mapa Associativo [ '2023-12-09' => 5, ... ]
        $mapaDados = [];
        foreach ($resultados as $row) {
            $mapaDados[$row['chave']] = $row['total'];
        }

        // 3. Loop de Preenchimento de Gaps (Zeros)
        $atual = clone $inicio;

        // Ajustar fim para comparação correta no loop
        $dataFimComparacao = clone $fim;
        if ($periodo === 'semestre') {
            $dataFimComparacao->modify('last day of this month');
        }
        $dataFimComparacao->setTime(23, 59, 59);

        while ($atual <= $dataFimComparacao) {
            $chave = $atual->format($formatoChave);
            $label = $atual->format($formatoLabel);

            $valor = isset($mapaDados[$chave]) ? (int)$mapaDados[$chave] : 0;

            $labels[] = $label;
            $data[] = $valor;

            $atual->modify($modifyStep);

            // Segurança anti-loop eterno (caso modify falhe)
            if (count($labels) > 366) break;
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
