<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SpreadsheetParserService
{
    protected $currencyParser;
    protected $cargoNormalizer;

    public function __construct(BrazilianCurrencyParser $currencyParser, CargoNormalizerService $cargoNormalizer)
    {
        $this->currencyParser = $currencyParser;
        $this->cargoNormalizer = $cargoNormalizer;
    }

    public function parseTempoCasaMeses($val): int
    {
        if (is_numeric($val)) return intval($val);
        if (!is_string($val)) return 0;

        $anos = 0;
        $meses = 0;
        if (preg_match('/(\d+)\s*anos?/i', $val, $mA)) $anos = intval($mA[1]);
        if (preg_match('/(\d+)\s*meses?/i', $val, $mM)) $meses = intval($mM[1]);

        return ($anos * 12) + $meses;
    }

    public function parseDate($admissaoRaw): ?Carbon
    {
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

    public function parseMatrizSalarial($sheetTab): array
    {
        $salaryMatrix = [];
        if (!$sheetTab) return $salaryMatrix;

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
                    $parsedVal = $this->currencyParser->parse($val);
                    if ($parsedVal > 100) $levels[] = $parsedVal;
                }
                if (count($levels) > 0) {
                    $normName = $this->cargoNormalizer->normalize($cargoNome);
                    $salaryMatrix[$normName] = $levels;
                    $salaryMatrix[$cargoNome] = $levels;
                }
            }
        }
        return $salaryMatrix;
    }

    public function detectarELerColaboradores($sheet): array
    {
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

        Log::info('Mapeamento de colunas: ' . json_encode($colMap));

        $dataRows = array_slice($rows, $headerRowIndex + 1);
        $colaboradores = [];

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
            Log::info('Layout detectado: INTERCALADO (2 linhas por colaborador)');

            $i = 0;
            while ($i < count($dataRows)) {
                $linha1 = $dataRows[$i];

                $nome = trim((string)($linha1[0] ?? ''));
                if ($nome === '') $nome = trim((string)($linha1[1] ?? ''));
                
                if ($nome === '') {
                    $i++;
                    continue;
                }

                $linha2 = $dataRows[$i + 1] ?? [];

                $cargo       = isset($colMap['cargo'])        ? trim((string)($linha1[$colMap['cargo']] ?? '')) : trim((string)($linha1[2] ?? ''));
                $salario     = isset($colMap['salario'])      ? ($linha1[$colMap['salario']] ?? null)           : ($linha1[3] ?? null);
                $tempoCasa   = isset($colMap['tempo_casa'])   ? ($linha1[$colMap['tempo_casa']] ?? null)        : ($linha1[4] ?? null);

                $admissaoRaw = null;
                $idxAdmissao = !empty($colMap['admissao']) ? $colMap['admissao'] : [7, 1];
                $idxAdmissao = array_reverse($idxAdmissao);
                foreach ($idxAdmissao as $idx) {
                    if (!empty(trim((string)($linha1[$idx] ?? '')))) {
                        $admissaoRaw = $linha1[$idx];
                        break;
                    }
                }

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

                $i += 2;
            }
        } else {
            Log::info('Layout detectado: LINEAR (1 linha por colaborador)');

            foreach ($dataRows as $row) {
                $nome = isset($colMap['nome']) ? trim((string)($row[$colMap['nome']] ?? '')) : '';
                
                if ($nome === '') $nome = trim((string)($row[0] ?? ''));
                if ($nome === '') $nome = trim((string)($row[1] ?? ''));
                
                if ($nome === '') continue;
                
                $admissaoRaw = null;
                $idxAdmissao = !empty($colMap['admissao']) ? $colMap['admissao'] : [];
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
}
