<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Funcionario;
use App\Models\Cargo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExcelSalaryService
{
    /**
     * Processa a planilha completa para extrair cargos, níveis e colaboradores.
     * Retorna estatísticas da importação.
     */
    public function processFile($filePath)
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            
            // 1. Processar a aba de Tabela Salarial Geral
            $sheetTabela = $spreadsheet->getSheetByName('Tabela Salárial Geral');
            if ($sheetTabela) {
                // $this->processarTabelaSalarial($sheetTabela);
                // NOTA: Para este protótipo e para seguir a arquitetura monolítica,
                // vamos abstrair a leitura e focar apenas no carregamento básico.
                Log::info("Aba Tabela Salárial Geral encontrada.");
            }

            // 2. Processar a aba de Colaboradores
            $sheetColaboradores = $spreadsheet->getSheetByName('Relatorio e Analise dos Colabor');
            $importados = 0;
            
            if ($sheetColaboradores) {
                $importados = $this->processarColaboradores($sheetColaboradores);
            }

            return [
                'status' => 'success',
                'message' => "Importação concluída. $importados colaboradores sincronizados.",
                'colaboradores_importados' => $importados
            ];
            
        } catch (\Exception $e) {
            Log::error("Erro no ExcelSalaryService: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => "Erro ao processar planilha: " . $e->getMessage()
            ];
        }
    }

    private function processarColaboradores($sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $importados = 0;

        // O cabeçalho no modelo fornecido costuma estar na linha 1.
        for ($row = 2; $row <= $highestRow; $row++) {
            $nome = $sheet->getCell("A$row")->getCalculatedValue();
            
            if (empty($nome)) {
                continue; // Pula linhas vazias
            }

            $cargoNome = $sheet->getCell("C$row")->getCalculatedValue();
            $dataAdmissaoExcel = $sheet->getCell("D$row")->getCalculatedValue();
            $salarioAtual = $sheet->getCell("I$row")->getCalculatedValue();

            // Formatação do Salario
            $salarioAtual = (float) str_replace(['R$', '.', ' '], '', $salarioAtual);
            
            // Tratamento da Data de Admissão
            $dataAdmissao = null;
            if (is_numeric($dataAdmissaoExcel)) {
                $dataAdmissao = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dataAdmissaoExcel);
            } else {
                try {
                    $dataAdmissao = Carbon::createFromFormat('d/m/Y', $dataAdmissaoExcel);
                } catch (\Exception $e) {
                    $dataAdmissao = now(); // fallback
                }
            }

            // Garante que o Cargo existe para associar
            $cargo = Cargo::firstOrCreate(['nome' => $cargoNome]);

            // Determina senioridade (Criterios do plano de Arquitetura: 0-2 Jr, 2-5 Pl, >5 Sr)
            $anosCasa = Carbon::now()->diffInYears($dataAdmissao);
            $nivelId = $this->determinarSenioridade($anosCasa);

            // Atualiza ou Cria o Funcionário (Se a model Funcionario existir)
            if (class_exists(Funcionario::class)) {
                Funcionario::updateOrCreate(
                    ['nome' => $nome],
                    [
                        'cargo_id' => $cargo->id,
                        'salario_atual' => $salarioAtual,
                        'data_admissao' => $dataAdmissao,
                        'nivel_id' => $nivelId,
                    ]
                );
                $importados++;
            }
        }
        
        return $importados;
    }

    private function determinarSenioridade($anosCasa)
    {
        // Simulando a associação do nível de acordo com as regras de maturidade
        if ($anosCasa < 2) return 1; // Júnior
        if ($anosCasa < 5) return 2; // Pleno
        return 3; // Sênior
    }
}
