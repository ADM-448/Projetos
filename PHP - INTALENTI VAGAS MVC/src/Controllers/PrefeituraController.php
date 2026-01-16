<?php

namespace src\Controllers;

use src\Core\Controller;
use src\Models\Relatorio;
use src\Models\Empresa;

class PrefeituraController extends Controller
{
    public function __construct()
    {
        // Proteção Básica: Logado
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        // Proteção Avançada: Apenas Admin do Sistema (tipo 3) ou Gestor Municipal vinculada a empresa tipo 'prefeitura'
        // Por simplificação, vamos assumir que tipo 3 é o super admin que vê tudo.
        // Se houver uma flag específica na tabela empresas para "prefeitura", validaríamos aqui.
        $tipo = $_SESSION['tipo_usuario'] ?? $_SESSION['user_type'] ?? 0;

        $acessoPermitido = false;
        if ($tipo == 3) {
            $acessoPermitido = true;
        } else {
            // Validação Extra para Gestor Municipal
            $empresaModel = new Empresa();
            if ($empresaModel->isUserPrefeitura($_SESSION['user_id'])) {
                $acessoPermitido = true;
            }
        }

        if (!$acessoPermitido) {
            $_SESSION['flash_error'] = "Acesso restrito ao Observatório do Trabalho.";
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }
    }

    public function dashboard()
    {
        $relatorio = new Relatorio();

        // 1. KPIs Principais
        $kpis = [
            'vagas_ativas' => $relatorio->getTotalVagasAtivas(),
            'total_empresas' => $relatorio->getTotalEmpresas(),
            'total_candidatos' => $relatorio->getTotalCandidatos(),
            'interacoes' => $relatorio->getTotalInteracoes(),
            'impacto_economico' => $relatorio->getImpactoEconomicoEstimado()
        ];

        // 2. Gráficos
        $vagasPorSetor = $relatorio->getVagasPorSetor();

        $labelsSetor = [];
        $dataSetor = [];
        foreach ($vagasPorSetor as $v) {
            $labelsSetor[] = $v['ramo_atividade'] ?: 'Não Informado';
            $dataSetor[] = $v['total'];
        }

        // 3. KPI Comparativo Inicial (Padrão: Semestre)
        $kpiEvolucao = $relatorio->getKpiComparativo('semestre');

        // 4. Listas para Modais
        $listaVagas = $relatorio->getListaVagasRecentes(10);
        $listaEmpresas = $relatorio->getListaEmpresasRecentes(10);
        $listaCandidatos = $relatorio->getListaCandidatosRecentes(10);

        // 5. Mapa
        $pontosMapa = $relatorio->getDadosMapaCalorVagas();
        $pontosCandidatos = $relatorio->getDadosMapaCalorCandidatos();

        // 6. Dados do Gráfico de Evolução (Inicial: Semestre)
        $graficoEvolucao = $relatorio->getDadosGraficoEvolucao('semestre');

        $this->renderTemplate('Prefeitura/dashboard', [
            'title' => 'Observatório do Trabalho',
            'kpis' => $kpis,
            'grafico_setor' => [
                'labels' => json_encode($labelsSetor),
                'data' => json_encode($dataSetor)
            ],
            'kpi_evolucao' => $kpiEvolucao,
            'grafico_evolucao' => [ // Passando dados do gráfico
                'labels' => json_encode($graficoEvolucao['labels']),
                'data' => json_encode($graficoEvolucao['data'])
            ],
            'pontos_vagas' => json_encode($pontosMapa),
            'pontos_candidatos' => json_encode($pontosCandidatos),
            'lista_vagas' => $listaVagas,
            'lista_empresas' => $listaEmpresas,
            'lista_candidatos' => $listaCandidatos
        ]);
    }

    public function dadosEvolucao()
    {
        // Endpoint AJAX para o KPI Comparativo
        ob_clean();
        header('Content-Type: application/json');

        $periodo = filter_input(INPUT_GET, 'periodo', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'semestre';
        $relatorio = new Relatorio();

        $dadosKpi = $relatorio->getKpiComparativo($periodo);
        $dadosGrafico = $relatorio->getDadosGraficoEvolucao($periodo); // Busca dados do gráfico

        echo json_encode([
            'kpi' => $dadosKpi,     // Dados numéricos
            'grafico' => $dadosGrafico // Dados visuais
        ]);
        exit;
    }
}
