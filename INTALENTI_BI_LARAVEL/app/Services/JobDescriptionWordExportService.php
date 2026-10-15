<?php

namespace App\Services;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Font;

class JobDescriptionWordExportService
{
    /**
     * Gera um documento Word (.docx) estruturado e editável com branding do cliente e aviso legal.
     *
     * @param array $dadosCargo
     * @param array $configEmpresa [ 'nome', 'logo_path', 'cor_primaria_hex', 'cor_secundaria_hex' ]
     * @return string Caminho do arquivo temporário gerado
     */
    public function gerarDocx(array $dadosCargo, array $configEmpresa = []): string
    {
        $phpWord = new PhpWord();

        // Configuração de fonte padrão
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(11);

        $corPrimaria = ltrim($configEmpresa['cor_primaria_hex'] ?? '1E3A8A', '#');
        $corDestaque = ltrim($configEmpresa['cor_secundaria_hex'] ?? '2563EB', '#');
        $nomeEmpresa = $configEmpresa['nome'] ?? 'Empresa Cliente';
        $logoPath = $configEmpresa['logo_path'] ?? null;

        // Seção do Documento
        $section = $phpWord->addSection([
            'marginTop' => 1200,
            'marginBottom' => 1200,
            'marginLeft' => 1400,
            'marginRight' => 1400,
        ]);

        // Cabeçalho com Logo (se existir) e Nome da Empresa
        $headerTable = $section->addTable([
            'borderBottomSize' => 12,
            'borderBottomColor' => $corPrimaria,
            'width' => 100 * 50,
            'unit' => 'pct',
        ]);
        $headerRow = $headerTable->addRow(800);
        
        $logoCell = $headerRow->addCell(5000);
        if ($logoPath && file_exists($logoPath)) {
            $logoCell->addImage($logoPath, [
                'width' => 140,
                'height' => 50,
                'alignment' => Jc::START,
            ]);
        } else {
            $logoCell->addText(mb_strtoupper($nomeEmpresa), [
                'bold' => true,
                'size' => 14,
                'color' => $corPrimaria,
            ]);
        }

        $infoCell = $headerRow->addCell(5000);
        $infoCell->addText('PLANO DE CARGOS E SALÁRIOS', [
            'bold' => true,
            'size' => 9,
            'color' => '64748B',
        ], ['alignment' => Jc::END]);
        $infoCell->addText('Descrição de Cargo & Perfil Profissional', [
            'size' => 9,
            'color' => '94A3B8',
        ], ['alignment' => Jc::END]);

        $section->addTextBreak(1);

        // Título do Cargo
        $cargoReal = $dadosCargo['cargo_real'] ?? $dadosCargo['cargo_original'] ?? 'Cargo Não Especificado';
        $section->addText(mb_strtoupper($cargoReal), [
            'bold' => true,
            'size' => 20,
            'color' => $corPrimaria,
        ]);

        // Subtítulo / Informações de CBO e Região
        $cbo = $dadosCargo['cbo'] ?? 'CBO Sob Análise';
        $localidade = $dadosCargo['localidade']['cidade'] ?? 'Nacional';
        $uf = $dadosCargo['localidade']['uf'] ?? '';
        $regiaoStr = $uf ? "{$localidade} - {$uf}" : $localidade;

        $metaTable = $section->addTable(['cellMargin' => 60]);
        $metaRow = $metaTable->addRow();
        $metaRow->addCell(3000)->addText("CBO Oficial: {$cbo}", ['bold' => true, 'color' => '475569', 'size' => 10]);
        $metaRow->addCell(4000)->addText("Região / Praça: {$regiaoStr}", ['color' => '475569', 'size' => 10]);
        if (!empty($dadosCargo['cargo_original']) && $dadosCargo['cargo_original'] !== $cargoReal) {
            $metaRow->addCell(3000)->addText("Título Interno: {$dadosCargo['cargo_original']}", ['italic' => true, 'color' => '64748B', 'size' => 9]);
        }

        $section->addTextBreak(1);

        // Faixa Salarial de Referência (se houver)
        if (!empty($dadosCargo['faixa_salarial'])) {
            $faixa = $dadosCargo['faixa_salarial'];
            $pisoFormatado = 'R$ ' . number_format($faixa['piso'] ?? 0, 2, ',', '.');
            $medianaFormatada = 'R$ ' . number_format($faixa['mediana'] ?? 0, 2, ',', '.');
            $tetoFormatado = 'R$ ' . number_format($faixa['teto'] ?? 0, 2, ',', '.');

            $section->addText('FAIXA SALARIAL PRATICADA NO MERCADO', [
                'bold' => true,
                'size' => 12,
                'color' => $corDestaque,
            ]);

            $salTable = $section->addTable([
                'borderSize' => 6,
                'borderColor' => 'CBD5E1',
                'cellMargin' => 100,
            ]);
            $salHeader = $salTable->addRow();
            $salHeader->addCell(3300, ['bgColor' => 'F1F5F9'])->addText('Piso Regional', ['bold' => true, 'size' => 10]);
            $salHeader->addCell(3300, ['bgColor' => 'F1F5F9'])->addText('Mediana de Mercado', ['bold' => true, 'size' => 10]);
            $salHeader->addCell(3300, ['bgColor' => 'F1F5F9'])->addText('Teto Salarial', ['bold' => true, 'size' => 10]);

            $salData = $salTable->addRow();
            $salData->addCell(3300)->addText($pisoFormatado, ['color' => '1E293B', 'bold' => true]);
            $salData->addCell(3300)->addText($medianaFormatada, ['color' => $corPrimaria, 'bold' => true]);
            $salData->addCell(3300)->addText($tetoFormatado, ['color' => '1E293B', 'bold' => true]);

            $section->addTextBreak(1);
        }

        // Descrição e Atividades Principais
        $section->addText('MISSÃO E DESCRIÇÃO DAS ATIVIDADES', [
            'bold' => true,
            'size' => 12,
            'color' => $corDestaque,
        ]);

        $descricao = $dadosCargo['descricao_cargo'] ?? 'Executar atividades correlatas e operacionais com zelo técnico e qualidade.';
        $section->addText($descricao, ['size' => 11, 'color' => '334155'], ['lineHeight' => 1.25]);

        $section->addTextBreak(1);

        // Competências e Requisitos Sugeridos
        $section->addText('REQUISITOS E COMPETÊNCIAS PRINCIPAIS', [
            'bold' => true,
            'size' => 12,
            'color' => $corDestaque,
        ]);
        $section->addListItem('Formação e conhecimentos técnicos compatíveis com a complexidade do cargo.', 0, null, ['listType' => \PhpOffice\PhpWord\Style\ListItem::TYPE_BULLET_FILLED]);
        $section->addListItem('Capacidade de organização, comunicação interpessoal e foco em resultados.', 0, null, ['listType' => \PhpOffice\PhpWord\Style\ListItem::TYPE_BULLET_FILLED]);
        $section->addListItem('Alinhamento com as normas de conformidade trabalhista e segurança da empresa.', 0, null, ['listType' => \PhpOffice\PhpWord\Style\ListItem::TYPE_BULLET_FILLED]);

        $section->addTextBreak(2);

        // AVISO LEGAL OBRIGATÓRIO (Em destaque com borda)
        $avisoTable = $section->addTable([
            'borderSize' => 12,
            'borderColor' => 'DC2626', // Vermelho alerta
            'bgColor' => 'FEF2F2',
            'cellMargin' => 150,
            'width' => 100 * 50,
            'unit' => 'pct',
        ]);
        $avisoRow = $avisoTable->addRow();
        $avisoCell = $avisoRow->addCell(10000);
        $avisoCell->addText('AVISO LEGAL & COMPLIANCE TRABALHISTA', [
            'bold' => true,
            'size' => 10,
            'color' => '991B1B',
        ]);
        $avisoCell->addText(
            SalaryRecommendationService::getAvisoLegalObrigatorio(),
            ['italic' => true, 'bold' => true, 'size' => 10, 'color' => 'B91C1C'],
            ['alignment' => Jc::BOTH]
        );

        // Salvar em arquivo temporário
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $filename = 'Descricao_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $cargoReal) . '_' . date('Ymd_His') . '.docx';
        $fullPath = $tempDir . DIRECTORY_SEPARATOR . $filename;

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($fullPath);

        return $fullPath;
    }
}
