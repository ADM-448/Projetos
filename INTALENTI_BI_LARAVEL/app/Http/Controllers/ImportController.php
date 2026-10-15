<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Services\SpreadsheetParserService;
use App\Services\BrazilianCurrencyParser;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    protected $parserService;
    protected $currencyParser;

    public function __construct(SpreadsheetParserService $parserService, BrazilianCurrencyParser $currencyParser)
    {
        $this->parserService = $parserService;
        $this->currencyParser = $currencyParser;
    }

    public function store(Request $request)
    {
        Log::info('Iniciando importacao', $request->all());

        $request->validate([
            'arquivo' => 'required|file|mimes:xlsx,xls,csv|max:5120' // 5MB max
        ]);

        try {
            $filePath = $request->file('arquivo')->getRealPath();
            $spreadsheet = IOFactory::load($filePath);

            $funcionariosSession = [];
            $cargosUnicos = [];
            $idCounter = 1;
            $cargoIdCounter = 1;

            Log::info('Sheets: ' . implode(', ', $spreadsheet->getSheetNames()));

            // 1. Extrair Matriz Salarial
            $sheetTab = null;
            foreach ($spreadsheet->getSheetNames() as $sName) {
                if (stripos($sName, 'Tabela') !== false || stripos($sName, 'Salarial') !== false || stripos($sName, 'Salárial') !== false) {
                    $sheetTab = $spreadsheet->getSheetByName($sName);
                    break;
                }
            }

            $salaryMatrix = $this->parserService->parseMatrizSalarial($sheetTab);
            Log::info('Matriz salarial: ' . count(array_keys($salaryMatrix)) . ' cargos');

            // 2. Localizar aba de colaboradores
            $sheetColab = $spreadsheet->getSheet(0);
            foreach ($spreadsheet->getSheetNames() as $sName) {
                if (stripos($sName, 'Colabor') !== false || stripos($sName, 'Relatorio') !== false || stripos($sName, 'Modelo') !== false) {
                    $sheetColab = $spreadsheet->getSheetByName($sName);
                    break;
                }
            }

            Log::info('Aba colaboradores: ' . $sheetColab->getTitle());

            // 3. Ler colaboradores
            $colaboradoresRaw = $this->parserService->detectarELerColaboradores($sheetColab);
            Log::info('Colaboradores extraídos raw: ' . count($colaboradoresRaw));

            foreach ($colaboradoresRaw as $c) {
                $nome  = trim((string)($c['nome'] ?? ''));
                $cargo = trim((string)($c['cargo'] ?? 'Indefinido')) ?: 'Indefinido';
                if (!$nome) continue;

                if (!isset($cargosUnicos[$cargo])) {
                    $cargosUnicos[$cargo] = [
                        'id'              => $cargoIdCounter++,
                        'nome'            => $cargo,
                    ];
                }
                $cargoInfo = $cargosUnicos[$cargo];

                $admissao = $this->parserService->parseDate($c['admissao']);

                $nivelInt = 0;
                $nivelRaw = $c['nivel'];
                if (is_numeric($nivelRaw)) {
                    $nivelInt = intval($nivelRaw);
                } elseif (is_string($nivelRaw)) {
                    preg_match('/\d+/', $nivelRaw, $m);
                    $nivelInt = isset($m[0]) ? intval($m[0]) : 0;
                }

                $tempoCasaText = $this->parserService->parseTempoCasaMeses($c['tempo_casa'] ?? 0);
                $tempoCasaMeses = 0;
                if ($tempoCasaText > 0) {
                    $tempoCasaMeses = $tempoCasaText;
                } elseif ($admissao) {
                    $tempoCasaMeses = max(0, intval($admissao->diffInMonths(now())));
                }

                $funcionariosSession[] = [
                    'id'                          => $idCounter++,
                    'nome'                        => $nome,
                    'admissao'                    => $admissao ? $admissao->toDateString() : now()->toDateString(),
                    'cargo_id'                    => $cargoInfo['id'],
                    'cargo'                       => $cargoInfo,
                    'salario_atual'               => $this->currencyParser->parse($c['salario'] ?? 0),
                    'salario_mais_proximo_tabela' => $this->currencyParser->parse($c['salario_tabela'] ?? 0),
                    'tempo_de_casa_meses'         => $tempoCasaMeses,
                    'nivel_mais_proximo'          => $nivelInt,
                ];
            }

            Log::info('Funcionarios processados: ' . count($funcionariosSession));
            Log::info('Cargos: ' . count($cargosUnicos));

            session()->forget(['funcionarios', 'cargos', 'matriz_salarial']);

            session()->put('funcionarios', $funcionariosSession);
            session()->put('cargos', array_values($cargosUnicos));
            session()->put('matriz_salarial', $salaryMatrix);
            session()->save();

            return redirect()->route('dashboard')->with('success', count($funcionariosSession) . ' colaboradores analisados com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao processar planilha: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Erro ao processar planilha: ' . $e->getMessage());
        }
    }
}
