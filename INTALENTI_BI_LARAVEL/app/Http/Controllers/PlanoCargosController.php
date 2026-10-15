<?php

namespace App\Http\Controllers;

use App\Services\CepMarketSearchService;
use App\Services\JobDescriptionWordExportService;
use App\Services\SalaryRecommendationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PlanoCargosController extends Controller
{
    public function __construct(
        private SalaryRecommendationService $recommendationService,
        private CepMarketSearchService $cepMarketSearchService,
        private JobDescriptionWordExportService $wordExportService
    ) {}

    /**
     * Endpoint para pesquisa de mercado e sugestão de cargo por CEP.
     */
    public function pesquisarMercadoPorCep(Request $request)
    {
        $request->validate([
            'cargo' => 'required|string|max:150',
            'cep' => 'nullable|string|max:10',
        ]);

        $resultado = $this->cepMarketSearchService->pesquisarVagaPorCepECargo(
            $request->input('cargo'),
            $request->input('cep')
        );

        return response()->json($resultado);
    }

    /**
     * Exportação da descrição do cargo em arquivo Word (.docx) com branding do cliente e aviso legal.
     */
    public function exportarDescricaoWord(Request $request)
    {
        $cargo = $request->input('cargo', 'Assistente Comercial');
        $cep = $request->input('cep');

        $dadosCargo = $this->cepMarketSearchService->pesquisarVagaPorCepECargo($cargo, $cep);

        $configEmpresa = [
            'nome' => $request->input('empresa_nome', 'Empresa Cliente'),
            'cor_primaria_hex' => $request->input('cor_primaria', '#1E3A8A'),
            'cor_secundaria_hex' => $request->input('cor_secundaria', '#2563EB'),
            'logo_path' => public_path('img/logoPreta.png'),
        ];

        $filePath = $this->wordExportService->gerarDocx($dadosCargo, $configEmpresa);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    /**
     * Geração do Relatório Executivo em PDF do Plano de Cargos e Salários.
     */
    public function exportarRelatorioPdf(Request $request)
    {
        // Obtém colaboradores carregados na sessão do dashboard salarial ou mock de demonstração
        $dataSession = Session::get('salary_dashboard_data', []);
        $funcionariosRaw = $dataSession['funcionarios'] ?? [];

        if (empty($funcionariosRaw)) {
            // Demonstração estruturada caso não haja folha carregada na sessão
            $funcionariosRaw = [
                ['nome' => 'Carlos Alberto Silva', 'cargo' => 'faz tudo comercial', 'salario' => 1950.00, 'piso' => 2400.00, 'teto' => 3800.00],
                ['nome' => 'Mariana Souza Santos', 'cargo' => 'Analista Financeiro Pleno', 'salario' => 4800.00, 'piso' => 4200.00, 'teto' => 5900.00],
                ['nome' => 'Roberto Mendes Jr.', 'cargo' => 'analista ninja', 'salario' => 12500.00, 'piso' => 5500.00, 'teto' => 8800.00],
                ['nome' => 'Fernanda Lima Duarte', 'cargo' => 'Assistente Administrativo', 'salario' => 1850.00, 'piso' => 2100.00, 'teto' => 3100.00],
            ];
        }

        $funcionariosProcessados = [];
        $totalConforme = 0;
        $totalAbaixo = 0;
        $totalAcima = 0;

        foreach ($funcionariosRaw as $f) {
            $cargoNome = $f['cargo'] ?? $f['funcao'] ?? 'Cargo';
            $salario = (float) ($f['salario'] ?? $f['salario_atual'] ?? 0);
            $piso = isset($f['piso']) ? (float)$f['piso'] : null;
            $teto = isset($f['teto']) ? (float)$f['teto'] : null;

            $cboInfo = $this->recommendationService->normalizarCargoECBO($cargoNome);
            $analise = $this->recommendationService->analisarSituacao($salario, $piso, $teto);

            if ($analise['status'] === SalaryRecommendationService::SITUACAO_CONFORME) {
                $totalConforme++;
            } elseif ($analise['status'] === SalaryRecommendationService::SITUACAO_ABAIXO) {
                $totalAbaixo++;
            } elseif ($analise['status'] === SalaryRecommendationService::SITUACAO_ACIMA) {
                $totalAcima++;
            }

            $funcionariosProcessados[] = [
                'nome' => $f['nome'] ?? 'Colaborador',
                'cargo_informado' => $cargoNome,
                'cbo_info' => $cboInfo,
                'salario_atual' => $salario,
                'piso' => $piso,
                'teto' => $teto,
                'analise' => $analise,
            ];
        }

        // Carrega logo da ItTalent em Base64
        $logoItTalentPath = public_path('img/logoPreta.png');
        $logoItTalentBase64 = null;
        if (file_exists($logoItTalentPath)) {
            $logoItTalentBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoItTalentPath));
        }

        $logoClienteBase64 = null; // Cliente pode configurar dinamicamente

        $pdf = Pdf::loadView('reports.plano_cargos_salarios_pdf', [
            'empresaNome' => $request->input('empresa_nome', 'Empresa Contratante S.A.'),
            'corPrimaria' => $request->input('cor_primaria', '#1E3A8A'),
            'logoItTalentBase64' => $logoItTalentBase64,
            'logoClienteBase64' => $logoClienteBase64,
            'funcionarios' => $funcionariosProcessados,
            'totalConforme' => $totalConforme,
            'totalAbaixo' => $totalAbaixo,
            'totalAcima' => $totalAcima,
            'avisoLegal' => SalaryRecommendationService::getAvisoLegalObrigatorio(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Relatorio_Plano_Cargos_Salarios_' . date('Ymd_His') . '.pdf');
    }
}
