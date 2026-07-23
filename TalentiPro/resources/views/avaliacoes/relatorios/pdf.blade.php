<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório - {{ $colaborador->name }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; color: #333; margin: 0; padding: 0; }
        .header { text-align: center; border-bottom: 2px solid #3b5998; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #6a4c7c; margin-bottom: 10px; }
        .report-title { font-size: 20px; color: #555; }
        .info-box { background-color: #f8f9fa; border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-bottom: 30px; }
        .info-box table { width: 100%; }
        .info-box td { padding: 5px; }
        .info-box strong { color: #3b5998; }
        .charts-container { width: 100%; margin-bottom: 30px; text-align: center; }
        .chart-card { display: inline-block; width: 22%; margin: 1%; background: white; padding: 15px; border-radius: 8px; border: 1px solid #ddd; text-align: center; box-sizing: border-box; }
        .chart-title { font-size: 12px; font-weight: bold; color: #555; margin-bottom: 10px; text-transform: uppercase; }
        .chart-number { font-size: 24px; font-weight: bold; color: #3b5998; }
        .scores-box { text-align: center; margin-bottom: 30px; }
        .score-item { display: inline-block; background-color: #dbe6f0; border-radius: 8px; padding: 15px 30px; margin: 0 10px; }
        .score-label { font-size: 12px; font-weight: bold; color: #333; display: block; margin-bottom: 5px; }
        .score-value { font-size: 20px; font-weight: bold; color: #000; }
        .competence-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; page-break-inside: avoid; }
        .competence-table th { background-color: #3b5998; color: white; padding: 10px; text-align: left; }
        .competence-table td { padding: 10px; border: 1px solid #ddd; vertical-align: top; }
        .competence-name { font-weight: bold; background-color: #eef2f5; font-size: 16px; padding: 10px; border: 1px solid #ddd; border-bottom: none; margin-top: 20px; }
        .note-leader { color: #2e7d32; font-weight: bold; text-align: center; }
        .note-colab { color: #1565c0; font-weight: bold; text-align: center; }
        .footer { position: fixed; bottom: -30px; left: 0px; right: 0px; height: 50px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="footer">
        Gerado em {{ now()->format('d/m/Y H:i') }} - TalentiPro
    </div>

    <div class="header">
        <div class="logo">TALENTIPRO</div>
        <div class="report-title">Relatório de Avaliação - {{ $avaliacao->titulo }}</div>
    </div>

    <div class="info-box">
        <table>
            <tr>
                <td><strong>Colaborador:</strong> {{ $colaborador->name }}</td>
                <td><strong>Cargo:</strong> {{ $colaborador->cargo ? $colaborador->cargo->nome : '-' }}</td>
            </tr>
            <tr>
                <td><strong>Departamento:</strong> {{ $colaborador->departamento ? $colaborador->departamento->nome : '-' }}</td>
                <td><strong>Gestor:</strong> {{ $colaborador->gestor ? $colaborador->gestor->name : 'Sem Gestor' }}</td>
            </tr>
        </table>
    </div>

    <div class="charts-container">
        <div class="chart-card">
            <div class="chart-title">Média Individual</div>
            <div class="chart-number">{{ $charts['individual'] }}</div>
        </div>
        <div class="chart-card">
            <div class="chart-title">Média Cargo</div>
            <div class="chart-number">{{ $charts['cargo'] }}</div>
        </div>
        <div class="chart-card">
            <div class="chart-title">Média Setor</div>
            <div class="chart-number">{{ $charts['setor'] }}</div>
        </div>
        <div class="chart-card">
            <div class="chart-title">Média Empresa</div>
            <div class="chart-number">{{ $charts['empresa'] }}</div>
        </div>
    </div>

    <div class="scores-box">
        <div class="score-item">
            <span class="score-label">Média Avaliadores</span>
            <span class="score-value">{{ $scores['aval']['media'] }}</span>
        </div>
        <div class="score-item" style="border: 2px solid #3b5998;">
            <span class="score-label">Média Mediação</span>
            <span class="score-value">{{ $scores['med']['media'] > 0 ? $scores['med']['media'] : '0.00' }}</span>
        </div>
    </div>

    @if(!empty($scores['observacao_avaliado']))
    <div class="info-box" style="margin-bottom: 20px;">
        <strong>Observação do Avaliado:</strong><br>
        {{ $scores['observacao_avaliado'] }}
    </div>
    @endif

    @if(!empty($scores['med']['comentarios']))
    <div class="info-box" style="margin-bottom: 30px;">
        <strong>Comentários de Fechamento / Plano de Ação:</strong><br>
        {{ $scores['med']['comentarios'] }}
    </div>
    @endif

    @php
        // Processar competências
        $competencias = [];
        
        if (isset($scores['colab']) && isset($scores['colab']['detalhes'])) {
            foreach ($scores['colab']['detalhes'] as $d) {
                $comp = $d['competencia'] ?? 'Geral';
                if (!isset($competencias[$comp])) $competencias[$comp] = ['skills' => []];
                if (!isset($competencias[$comp]['skills'][$d['pergunta']])) $competencias[$comp]['skills'][$d['pergunta']] = ['colab' => null, 'aval' => null];
                $competencias[$comp]['skills'][$d['pergunta']]['colab'] = $d;
            }
        }

        if (isset($scores['aval']) && isset($scores['aval']['detalhes'])) {
            foreach ($scores['aval']['detalhes'] as $d) {
                $comp = $d['competencia'] ?? 'Geral';
                if (!isset($competencias[$comp])) $competencias[$comp] = ['skills' => []];
                if (!isset($competencias[$comp]['skills'][$d['pergunta']])) $competencias[$comp]['skills'][$d['pergunta']] = ['colab' => null, 'aval' => null];
                $competencias[$comp]['skills'][$d['pergunta']]['aval'] = $d;
            }
        }
    @endphp

    @foreach($competencias as $compName => $dados)
        <div class="competence-name">{{ $compName }}</div>
        <table class="competence-table">
            <thead>
                <tr>
                    <th style="width: 30%;">Pergunta (Skill)</th>
                    <th style="width: 10%; text-align: center;">Nota Líder</th>
                    <th style="width: 10%; text-align: center;">Nota Colab</th>
                    <th style="width: 25%;">Comentário Líder</th>
                    <th style="width: 25%;">Comentário Colab</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dados['skills'] as $skillName => $skillData)
                    @php
                        $nColab = isset($skillData['colab']) && $skillData['colab']['nota'] !== null ? $skillData['colab']['nota'] : '-';
                        $nAval = isset($skillData['aval']) && $skillData['aval']['nota'] !== null ? $skillData['aval']['nota'] : '-';
                        
                        $cColab = isset($skillData['colab']) && isset($skillData['colab']['comentarios']) && count($skillData['colab']['comentarios']) ? implode('<br>', $skillData['colab']['comentarios']) : '-';
                        $cAval = isset($skillData['aval']) && isset($skillData['aval']['comentarios']) && count($skillData['aval']['comentarios']) ? implode('<br>', $skillData['aval']['comentarios']) : '-';
                    @endphp
                    <tr>
                        <td>{{ $skillName }}</td>
                        <td class="note-leader">{{ $nAval }}</td>
                        <td class="note-colab">{{ $nColab }}</td>
                        <td>{!! $cAval !!}</td>
                        <td>{!! $cColab !!}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

</body>
</html>
