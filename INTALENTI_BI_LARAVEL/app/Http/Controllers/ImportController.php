<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Funcionario;
use App\Models\Cargo;
use Carbon\Carbon;
use Exception;

class ImportController extends Controller
{
    private function parseBrazilianFloat($val) {
        if ($val === null || $val === '') return 0.0;
        
        if (is_float($val) || is_int($val)) {
            $parsed = floatval($val);
            if ($parsed > 0 && $parsed < 100) {
                $parsed = $parsed * 1000;
            }
            return $parsed;
        }
        
        $val = trim(str_replace(['R$', ' ', "\xc2\xa0"], '', (string)$val));
        
        $dotCount = substr_count($val, '.');
        $commaCount = substr_count($val, ',');

        if ($commaCount > 0 && $dotCount > 0) {
            $val = str_replace('.', '', $val);
            $val = str_replace(',', '.', $val);
        } elseif ($commaCount > 0) {
            $val = str_replace(',', '.', $val);
        } elseif ($dotCount > 0) {
            if (preg_match('/^\d+\.\d{3}$/', $val) || preg_match('/^\d+\.\d{3},\d{2}$/', $val)) {
                $val = str_replace('.', '', $val);
            }
        }
        
        $parsed = floatval($val);
        if ($parsed > 0 && $parsed < 100) {
            $parsed = $parsed * 1000;
        }
        return $parsed;
    }

    private function normalizeCargoName($name) {
        $unwanted = ['Š'=>'S','š'=>'s','Ž'=>'Z','ž'=>'z','À'=>'A','Á'=>'A','Â'=>'A','Ã'=>'A','Ä'=>'A','Å'=>'A','Æ'=>'A','Ç'=>'C','È'=>'E','É'=>'E',
                     'Ê'=>'E','Ë'=>'E','Ì'=>'I','Í'=>'I','Î'=>'I','Ï'=>'I','Ñ'=>'N','Ò'=>'O','Ó'=>'O','Ô'=>'O','Õ'=>'O','Ö'=>'O','Ø'=>'O','Ù'=>'U',
                     'Ú'=>'U','Û'=>'U','Ü'=>'U','Ý'=>'Y','Þ'=>'B','ß'=>'Ss','à'=>'a','á'=>'a','â'=>'a','ã'=>'a','ä'=>'a','å'=>'a','æ'=>'a','ç'=>'c',
                     'è'=>'e','é'=>'e','ê'=>'e','ë'=>'e','ì'=>'i','í'=>'i','î'=>'i','ï'=>'i','ð'=>'o','ñ'=>'n','ò'=>'o','ó'=>'o','ô'=>'o','õ'=>'o',
                     'ö'=>'o','ø'=>'o','ù'=>'u','ú'=>'u','û'=>'u','ý'=>'y','þ'=>'b','ÿ'=>'y'];
        $name = strtr($name, $unwanted);
        $name = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        if (strpos($name, 'servgerais') !== false) return 'servicosgerais';
        return $name;
    }

    private function parseTempoCasaMeses($val) {
        if (is_numeric($val)) return intval($val);
        if (!is_string($val)) return 0;

        $anos = 0;
        $meses = 0;
        if (preg_match('/(\d+)\s*anos?/i', $val, $mA)) $anos = intval($mA[1]);
        if (preg_match('/(\d+)\s*meses?/i', $val, $mM)) $meses = intval($mM[1]);

        return ($anos * 12) + $meses;
    }

    private function parseDate($admissaoRaw) {
        if (empty($admissaoRaw)) return null;
        try {
            if (is_numeric($admissaoRaw)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($admissaoRaw));
            }
            $strDate = trim((string)$admissaoRaw);
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $strDate)) return Carbon::createFromFormat('d/m/Y', $strDate);
            if (preg_match('/^\d{2}\/\d{2}\/\d{2}$/', $strDate))  return Carbon::createFromFormat('d/m/y', $strDate);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $strDate))    return Carbon::createFromFormat('Y-m-d', $strDate);
            return Carbon::parse($strDate);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Detecta o tipo de layout da planilha e retorna os dados no formato padrão.
     * Suporta:
     *   - Layout LINEAR (1 linha por colaborador, colunas mapeadas por cabeçalho)
     *   - Layout INTERCALADO (2 linhas por colaborador, como no Modelo_Importacao_BI.xlsx)
     */
    private function detectarELerColaboradores($sheet) {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $rows = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, false);

        // Encontra a linha do cabeçalho (até a linha 5)
        $headerRowIndex = -1;
        $header = [];
        for ($i = 0; $i < min(5, count($rows)); $i++) {
            foreach ($rows[$i] as $cell) {
                $cell = trim((string)($cell ?? ''));
                if (stripos($cell, 'COLABORADOR') !== false || stripos($cell, 'FUNÇÃO') !== false || stripos($cell, 'FUNCAO') !== false) {
                    $headerRowIndex = $i;
                    $header = $rows[$i];
                    break 2;
                }
            }
        }

        if ($headerRowIndex === -1) return [];

        // Mapeia índice de colunas pelo cabeçalho
        $colMap = [];
        $colMap['admissao'] = [];
        foreach ($header as $idx => $h) {
            $h = mb_strtoupper(trim((string)($h ?? '')), 'UTF-8');
            if (stripos($h, 'COLABORADOR') !== false) $colMap['nome'] = $idx;
            elseif (stripos($h, 'FUNÇÃO') !== false || stripos($h, 'FUNCAO') !== false || stripos($h, 'CARGO') !== false) $colMap['cargo'] = $idx;
            elseif (stripos($h, 'SALÁRIO ATUAL') !== false || stripos($h, 'SALARIO ATUAL') !== false) $colMap['salario'] = $idx;
            elseif (stripos($h, 'TABELA') !== false || stripos($h, 'PROXIMO') !== false || stripos($h, 'PRÓXIMO') !== false) $colMap['salario_tabela'] = $idx;
            elseif (stripos($h, 'SALÁRIO') !== false || stripos($h, 'SALARIO') !== false) { if (!isset($colMap['salario'])) $colMap['salario'] = $idx; }
            elseif (stripos($h, 'TEMPO') !== false || stripos($h, 'CASA') !== false) $colMap['tempo_casa'] = $idx;
            elseif (stripos($h, 'NÍVEL') !== false || stripos($h, 'NIVEL') !== false) $colMap['nivel'] = $idx;
            elseif (stripos($h, 'ADMISS') !== false) { $colMap['admissao'][] = $idx; }
        }

        \Log::info('Mapeamento de colunas: ' . json_encode($colMap));

        $dataRows = array_slice($rows, $headerRowIndex + 1);
        $colaboradores = [];

        // === DETECÇÃO AUTOMÁTICA DO LAYOUT ===
        // Verifica se a planilha usa layout intercalado (par de linhas) ou linear (1 linha)
        // Heurística: no layout intercalado, o Nível/Salário tabela aparecem em linhas com nome vazio

        $temNomeVazio = false;
        for ($i = 0; $i < min(10, count($dataRows)); $i++) {
            $nomeval = isset($colMap['nome']) ? trim((string)($dataRows[$i][$colMap['nome']] ?? '')) : '';
            $cargoVal = isset($colMap['cargo']) ? trim((string)($dataRows[$i][$colMap['cargo']] ?? '')) : '';
            $nivelVal = isset($colMap['nivel']) ? trim((string)($dataRows[$i][$colMap['nivel']] ?? '')) : '';

            if ($nomeval === '' && $cargoVal === '' && $nivelVal !== '') {
                $temNomeVazio = true;
                break;
            }
        }

        if ($temNomeVazio) {
            // LAYOUT INTERCALADO: lê de 2 em 2 linhas
            \Log::info('Layout detectado: INTERCALADO (2 linhas por colaborador)');

            $i = 0;
            while ($i < count($dataRows)) {
                $linha1 = $dataRows[$i];

                // Nome pode estar em A ou B
                $nome = trim((string)($linha1[0] ?? ''));
                if ($nome === '') $nome = trim((string)($linha1[1] ?? ''));
                
                // Se a linha não tiver nome, provavelmente é uma linha em branco ou o par quebrou. 
                // Avança 1 por 1 até achar uma linha válida (o próximo funcionário).
                if ($nome === '') {
                    $i++;
                    continue;
                }

                // Achou o nome, então consideramos esta linha a linha1. A próxima linha deve ser a linha2.
                $linha2 = $dataRows[$i + 1] ?? [];

                // Tentativa de pegar por índice fixo (colunas conhecidas do modelo)
                $cargo       = isset($colMap['cargo'])        ? trim((string)($linha1[$colMap['cargo']] ?? '')) : trim((string)($linha1[2] ?? ''));
                $salario     = isset($colMap['salario'])      ? ($linha1[$colMap['salario']] ?? null)           : ($linha1[3] ?? null);
                $tempoCasa   = isset($colMap['tempo_casa'])   ? ($linha1[$colMap['tempo_casa']] ?? null)        : ($linha1[4] ?? null);

                // Admissão: iterar sobre as colunas detectadas de admissão
                $admissaoRaw = null;
                $idxAdmissao = !empty($colMap['admissao']) ? $colMap['admissao'] : [7, 1];
                // Invert array to prefer the LAST admission column in intercalado layout
                $idxAdmissao = array_reverse($idxAdmissao);
                foreach ($idxAdmissao as $idx) {
                    if (!empty(trim((string)($linha1[$idx] ?? '')))) {
                        $admissaoRaw = $linha1[$idx];
                        break;
                    }
                }

                // Dados da segunda linha
                $salarioTabela = isset($colMap['salario_tabela']) ? ($linha2[$colMap['salario_tabela']] ?? null) : ($linha2[5] ?? null);
                $nivel         = isset($colMap['nivel'])          ? ($linha2[$colMap['nivel']] ?? null)          : ($linha2[6] ?? null);

                $colaboradores[] = [
                    'nome'           => $nome,
                    'cargo'          => $cargo ?: 'Indefinido',
                    'salario'        => $salario,
                    'tempo_casa'     => $tempoCasa,
                    'admissao'       => $admissaoRaw,
                    'salario_tabela' => $salarioTabela,
                    'nivel'          => $nivel,
                ];

                // Consome a linha atual e a próxima (pois a próxima era os dados do salário)
                $i += 2;
            }
        } else {
            // LAYOUT LINEAR: 1 linha por colaborador
            \Log::info('Layout detectado: LINEAR (1 linha por colaborador)');

            foreach ($dataRows as $row) {
                $nome = isset($colMap['nome']) ? trim((string)($row[$colMap['nome']] ?? '')) : '';
                
                // Fallback: se não achou na coluna nomeada, tenta as duas primeiras colunas
                if ($nome === '') $nome = trim((string)($row[0] ?? ''));
                if ($nome === '') $nome = trim((string)($row[1] ?? ''));
                
                if ($nome === '') continue;
                
                // Admissão
                $admissaoRaw = null;
                $idxAdmissao = !empty($colMap['admissao']) ? $colMap['admissao'] : [];
                // Reverse to try the last column first (often the correct one if there are duplicates)
                foreach (array_reverse($idxAdmissao) as $idx) {
                    if (!empty(trim((string)($row[$idx] ?? '')))) {
                        $admissaoRaw = $row[$idx];
                        break;
                    }
                }

                $colaboradores[] = [
                    'nome'           => $nome,
                    'cargo'          => isset($colMap['cargo'])         ? trim((string)($row[$colMap['cargo']] ?? ''))         : 'Indefinido',
                    'salario'        => isset($colMap['salario'])       ? ($row[$colMap['salario']] ?? null)                   : null,
                    'tempo_casa'     => isset($colMap['tempo_casa'])    ? ($row[$colMap['tempo_casa']] ?? null)                : null,
                    'admissao'       => $admissaoRaw,
                    'salario_tabela' => isset($colMap['salario_tabela']) ? ($row[$colMap['salario_tabela']] ?? null)            : null,
                    'nivel'          => isset($colMap['nivel'])         ? ($row[$colMap['nivel']] ?? null)                     : null,
                ];
            }
        }

        return $colaboradores;
    }

    public function store(Request $request)
    {
        ini_set('memory_limit', '-1');
        \Log::info('Iniciando importacao', $request->all());

        $request->validate([
            'arquivo' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            $filePath = $request->file('arquivo')->getRealPath();
            $spreadsheet = IOFactory::load($filePath);

            $funcionariosSession = [];
            $cargosUnicos = [];
            $salaryMatrix = [];
            $idCounter = 1;
            $cargoIdCounter = 1;

            \Log::info('Sheets: ' . implode(', ', $spreadsheet->getSheetNames()));

            // 1. Extrair Matriz Salarial
            $sheetTab = null;
            foreach ($spreadsheet->getSheetNames() as $sName) {
                if (stripos($sName, 'Tabela') !== false || stripos($sName, 'Salarial') !== false || stripos($sName, 'Salárial') !== false) {
                    $sheetTab = $spreadsheet->getSheetByName($sName);
                    break;
                }
            }

            if ($sheetTab) {
                $highestRow = $sheetTab->getHighestRow();
                for ($r = 1; $r <= $highestRow; $r++) {
                    $cargoNome = trim((string)$sheetTab->getCell('A' . $r)->getCalculatedValue());
                    if (!$cargoNome) $cargoNome = trim((string)$sheetTab->getCell('B' . $r)->getCalculatedValue());

                    if ($cargoNome && !in_array(mb_strtoupper($cargoNome, 'UTF-8'), ['CARGO','MÉDIA LIDERANÇA','SUPERVISÃO','TOTAL','NÍVEL','NIVEL',''])) {
                        $levels = [];
                        $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheetTab->getHighestColumn());
                        for ($c = 2; $c <= min($highestColIndex, 20); $c++) {
                            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
                            $val = $sheetTab->getCell($colLetter . $r)->getCalculatedValue();
                            $parsedVal = $this->parseBrazilianFloat($val);
                            if ($parsedVal > 100) $levels[] = $parsedVal;
                        }
                        if (count($levels) > 0) {
                            $normName = $this->normalizeCargoName($cargoNome);
                            $salaryMatrix[$normName] = $levels;
                            $salaryMatrix[$cargoNome] = $levels;
                        }
                    }
                }
            }
            \Log::info('Matriz salarial: ' . count(array_keys($salaryMatrix)) . ' cargos');

            // 2. Localizar aba de colaboradores (primeira aba como fallback)
            $sheetColab = $spreadsheet->getSheet(0);
            foreach ($spreadsheet->getSheetNames() as $sName) {
                if (stripos($sName, 'Colabor') !== false || stripos($sName, 'Relatorio') !== false || stripos($sName, 'Modelo') !== false) {
                    $sheetColab = $spreadsheet->getSheetByName($sName);
                    break;
                }
            }

            \Log::info('Aba colaboradores: ' . $sheetColab->getTitle());

            // 3. Ler colaboradores com detecção automática de layout
            $colaboradoresRaw = $this->detectarELerColaboradores($sheetColab);
            \Log::info('Colaboradores extraídos raw: ' . count($colaboradoresRaw));

            foreach ($colaboradoresRaw as $c) {
                $nome  = trim((string)($c['nome'] ?? ''));
                $cargo = trim((string)($c['cargo'] ?? 'Indefinido')) ?: 'Indefinido';
                if (!$nome) continue;

                $normFuncao = $this->normalizeCargoName($cargo);
                if (!isset($cargosUnicos[$cargo])) {
                    $cargosUnicos[$cargo] = [
                        'id'              => $cargoIdCounter++,
                        'nome'            => $cargo,
                        'normalized_nome' => $normFuncao,
                    ];
                }
                $cargoInfo = $cargosUnicos[$cargo];

                $admissao = $this->parseDate($c['admissao']);

                // Nível: extrai o número de "Nível 12", "Nivel 2", ou número puro
                $nivelInt = 0;
                $nivelRaw = $c['nivel'];
                if (is_numeric($nivelRaw)) {
                    $nivelInt = intval($nivelRaw);
                } elseif (is_string($nivelRaw)) {
                    preg_match('/\d+/', $nivelRaw, $m);
                    $nivelInt = isset($m[0]) ? intval($m[0]) : 0;
                }

                // Tempo de casa: Prioriza a coluna de texto da planilha caso a data de admissão esteja zoada (e.g., 2025 ao inves de 2005)
                $tempoCasaText = $this->parseTempoCasaMeses($c['tempo_casa'] ?? 0);
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
                    'salario_atual'               => $this->parseBrazilianFloat($c['salario'] ?? 0),
                    'salario_mais_proximo_tabela' => $this->parseBrazilianFloat($c['salario_tabela'] ?? 0),
                    'tempo_de_casa_meses'         => $tempoCasaMeses,
                    'nivel_mais_proximo'          => $nivelInt,
                ];
            }

            \Log::info('Funcionarios processados: ' . count($funcionariosSession));
            \Log::info('Cargos: ' . count($cargosUnicos));

            // Limpa dados antigos antes de gravar os novos
            session()->forget(['funcionarios', 'cargos', 'matriz_salarial']);

            session()->put('funcionarios', $funcionariosSession);
            session()->put('cargos', array_values($cargosUnicos));
            session()->put('matriz_salarial', $salaryMatrix);
            session()->save(); // Garante que a sessão é gravada imediatamente

            return redirect()->route('dashboard')->with('success', count($funcionariosSession) . ' colaboradores importados com sucesso!');

        } catch (\Exception $e) {
            \Log::error('Erro ao importar planilha: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Erro ao importar planilha: ' . $e->getMessage());
        }
    }
}

