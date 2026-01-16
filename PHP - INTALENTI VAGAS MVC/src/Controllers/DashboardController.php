<?php

namespace src\Controllers;

use src\Core\Controller;
use src\Models\Vaga;

class DashboardController extends Controller
{

    public function index()
    {
        // Validação de Sessão
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        $empresasModel = new \src\Models\Empresa();
        $vinculos = $empresasModel->getByUser($_SESSION['user_id']);

        // Se for Candidato (tipo 1), redireciona ou mantém fluxo atual?
        // Assumindo que DashboardController principal é para Empresas/Recrutadores/Admins
        // Se for candidato, deveria ir para Perfil ou Vagas? Por enquanto mantemos lógica de redirecionar ou mostrar erro se não tiver empresa.
        // Mas se for empresa...

        $empresaId = null;
        if (!empty($vinculos)) {
            $empresaId = $vinculos[0]['id'];
        }

        // Se tiver empresa, mostra Dashboard Estilo Prefeitura (Analytics)
        if ($empresaId) {
            $relatorio = new \src\Models\Relatorio();
            $_SESSION['empresa_id_atual'] = $empresaId; // Guarda para AJAX

            // ... (rest of the logic) ...

            // 1. KPIs
            $kpis = [
                'vagas_ativas' => $relatorio->getTotalVagasAtivas($empresaId),
                'total_empresas' => 1, // Fixado para empresa
                'total_candidatos' => $relatorio->getTotalCandidatos($empresaId), // Candidatos inscritos
                'interacoes' => $relatorio->getTotalInteracoes($empresaId),
                'impacto_economico' => $relatorio->getImpactoEconomicoEstimado($empresaId)
            ];

            // 2. Gráficos
            $vagasPorSetor = $relatorio->getVagasPorSetor($empresaId);
            $labelsSetor = [];
            $dataSetor = [];
            foreach ($vagasPorSetor as $v) {
                $labelsSetor[] = $v['ramo_atividade'] ?: 'Não Informado';
                $dataSetor[] = $v['total'];
            }

            // 3. KPI Comparativo
            $kpiEvolucao = $relatorio->getKpiComparativo('semestre', $empresaId);

            // 4. Listas
            // Precisamos de métodos filtrados em Relatorio ou usar VagaModel
            // O Relatorio::getListaVagasRecentes não tem filtro de empresa ainda.
            // Vamos usar o VagaModel padrão para listas ou aceitar que Relatorio precisa de update nos getLista também.
            // Por simplicidade, vou usar VagaModel aqui para a lista de vagas e adaptar.
            $vagaModel = new Vaga();
            $listaVagas = $vagaModel->obterPorEmpresa($empresaId); // Retorna todas, limitaria na View ou aqui com array_slice
            $listaVagas = array_slice($listaVagas, 0, 10);

            // Candidaturas recentes
            $listaCandidatos = $relatorio->getListaCandidatosRecentes(10, $empresaId);

            // 5. Mapa
            $pontosVagas = $relatorio->getDadosMapaCalorVagas($empresaId);
            $pontosCandidatos = $relatorio->getDadosMapaCalorCandidatos($empresaId);

            // 6. Gráfico Evolução
            $graficoEvolucao = $relatorio->getDadosGraficoEvolucao('semestre', $empresaId);

            $this->renderTemplate('Dashboard/company', [
                'title' => 'Painel da Empresa',
                'kpis' => $kpis,
                'grafico_setor' => [
                    'labels' => json_encode($labelsSetor),
                    'data' => json_encode($dataSetor)
                ],
                'kpi_evolucao' => $kpiEvolucao,
                'grafico_evolucao' => [
                    'labels' => json_encode($graficoEvolucao['labels']),
                    'data' => json_encode($graficoEvolucao['data'])
                ],
                'pontos_vagas' => json_encode($pontosVagas),
                'pontos_candidatos' => json_encode($pontosCandidatos),
                'lista_vagas' => $listaVagas,
                'lista_empresas' => [], // Não faz sentido para empresa ver lista de outras empresas
                'lista_candidatos' => $listaCandidatos // Candidatos aplicados
            ]);
            return;
        }

        // Fallback: Dashboard antigão se não tiver empresa (recrutador avulso)
        $this->vagas();
    }

    public function vagas()
    {
        // Método específico para listar vagas (Visão antiga/simplificada)
        // Validação de Sessão
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        $vagaModel = new Vaga();
        $empresasModel = new \src\Models\Empresa();

        // Busca vínculos do usuário
        $vinculos = $empresasModel->getByUser($_SESSION['user_id']);
        $role = 'membro';

        if (!empty($vinculos)) {
            $empresaId = $vinculos[0]['id'];
            $vagas = $vagaModel->obterPorEmpresa($empresaId);
            $role = $vinculos[0]['role'];
        } else {
            $vagas = $vagaModel->obterPorRecrutador($_SESSION['user_id']);
        }

        $this->renderTemplate('Dashboard/index', [
            'nome' => $_SESSION['user_name'],
            'tipo' => $_SESSION['tipo_usuario'] ?? $_SESSION['user_type'] ?? 0,
            'vagas' => $vagas,
            'user_role' => $role, // Simplificado, admin ou membro pode vir do vinculo
            'user_id' => $_SESSION['user_id']
        ]);
    }

    public function dadosEvolucao()
    {
        // AJAX para Empresa
        ob_clean();
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode([]);
            exit;
        }

        $empresaId = $_SESSION['empresa_id_atual'] ?? null;
        if (!$empresaId) {
            // Tenta recuperar de novo
            $empresasModel = new \src\Models\Empresa();
            $vinculos = $empresasModel->getByUser($_SESSION['user_id']);
            if (!empty($vinculos)) $empresaId = $vinculos[0]['id'];
        }

        $periodo = filter_input(INPUT_GET, 'periodo', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'semestre';
        $relatorio = new \src\Models\Relatorio();

        $dadosKpi = $relatorio->getKpiComparativo($periodo, $empresaId);
        $dadosGrafico = $relatorio->getDadosGraficoEvolucao($periodo, $empresaId);

        echo json_encode([
            'kpi' => $dadosKpi,
            'grafico' => $dadosGrafico
        ]);
        exit;
    }
}
