<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Avaliação - {{ $data['colaborador']['name'] }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #6a4c7c; padding-bottom: 10px; margin-bottom: 20px; }
        h1 { color: #6a4c7c; font-size: 20px; margin: 0; }
        h2 { color: #2d3748; font-size: 16px; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 25px; }
        .info-table, .score-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 5px; }
        .score-table th, .score-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .score-table th { background-color: #f8fafc; font-weight: bold; color: #4a5568; }
        .text-center { text-align: center; }
        .highlight { font-weight: bold; font-size: 14px; }
        .purple { color: #6a4c7c; }
        .blue { color: #3498db; }
        .orange { color: #e67e22; }
        .footer { text-align: center; font-size: 10px; color: #999; margin-top: 50px; border-top: 1px solid #eee; padding-top: 10px; }
        .page-break { page-break-after: always; }
        
        .box-comparativo { width: 24%; display: inline-block; text-align: center; background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 0; margin-right: 1%; }
        .box-comparativo.last { margin-right: 0; }
        .box-comparativo .title { font-size: 10px; color: #718096; margin-bottom: 5px; }
        .box-comparativo .value { font-size: 18px; font-weight: bold; color: #2d3748; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Relatório de Desempenho</h1>
        <p style="margin: 5px 0 0 0;">Campanha: <strong>{{ $data['avaliacao']['titulo'] ?? 'Avaliação' }}</strong></p>
    </div>

    <!-- Informações do Colaborador -->
    <table class="info-table">
        <tr>
            <td width="15%"><strong>Nome:</strong></td>
            <td width="35%">{{ $data['colaborador']['name'] }}</td>
            <td width="15%"><strong>Cargo:</strong></td>
            <td width="35%">{{ $data['colaborador']['cargo']['nome'] ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Setor:</strong></td>
            <td>{{ $data['colaborador']['departamento']['nome'] ?? 'N/A' }}</td>
            <td><strong>Líder Direto:</strong></td>
            <td>{{ $data['colaborador']['gestor']['name'] ?? 'N/A' }}</td>
        </tr>
    </table>

    <!-- Resumo dos Scores -->
    <h2>Resumo das Notas</h2>
    <table class="info-table text-center" style="margin: 20px 0;">
        <tr>
            <td width="33%">
                <div style="font-size: 12px; color: #6a4c7c;">Autoavaliação</div>
                <div style="font-size: 24px; font-weight: bold; color: #6a4c7c;">{{ $data['scores']['colab']['media'] }}</div>
            </td>
            <td width="33%">
                <div style="font-size: 12px; color: #3498db;">Avaliadores (Líder/Pares)</div>
                <div style="font-size: 24px; font-weight: bold; color: #3498db;">{{ $data['scores']['aval']['media'] }}</div>
            </td>
            <td width="33%">
                <div style="font-size: 12px; color: #e67e22;">Consenso / Final</div>
                <div style="font-size: 24px; font-weight: bold; color: #e67e22;">{{ $data['scores']['med']['media'] }}</div>
            </td>
        </tr>
    </table>

    <!-- Médias Comparativas -->
    <h2>Comparativo (Médias)</h2>
    <div style="margin-top: 10px;">
        <div class="box-comparativo">
            <div class="title">Indivíduo</div>
            <div class="value">{{ number_format((float)$data['charts']['individual'], 1) }}</div>
        </div>
        <div class="box-comparativo">
            <div class="title">Cargo</div>
            <div class="value">{{ number_format((float)$data['charts']['cargo'], 1) }}</div>
        </div>
        <div class="box-comparativo">
            <div class="title">Setor</div>
            <div class="value">{{ number_format((float)$data['charts']['setor'], 1) }}</div>
        </div>
        <div class="box-comparativo last">
            <div class="title">Empresa</div>
            <div class="value">{{ number_format((float)$data['charts']['empresa'], 1) }}</div>
        </div>
    </div>
    
    <div style="clear: both;"></div>

    <div class="page-break"></div>

    <!-- Detalhamento por Competência -->
    <h2>Detalhamento por Competência</h2>

    @php
        // Agrupa as notas de colab e aval para gerar a tabela, semelhante ao JS
        $competencias = [];
        
        if (isset($data['scores']['colab']['detalhes']) && is_array($data['scores']['colab']['detalhes'])) {
            foreach ($data['scores']['colab']['detalhes'] as $d) {
                $comp = $d['competencia'] ?: 'Geral';
                if (!isset($competencias[$comp])) $competencias[$comp] = [];
                $competencias[$comp][$d['pergunta']]['colab'] = $d;
            }
        }

        if (isset($data['scores']['aval']['detalhes']) && is_array($data['scores']['aval']['detalhes'])) {
            foreach ($data['scores']['aval']['detalhes'] as $d) {
                $comp = $d['competencia'] ?: 'Geral';
                if (!isset($competencias[$comp])) $competencias[$comp] = [];
                $competencias[$comp][$d['pergunta']]['aval'] = $d;
            }
        }
    @endphp

    @foreach($competencias as $compName => $skills)
        <div style="background-color: #6a4c7c; color: #fff; padding: 5px 10px; font-weight: bold; margin-top: 20px;">
            {{ $compName }}
        </div>
        <table class="score-table">
            <thead>
                <tr>
                    <th width="40%">Pergunta (Skill)</th>
                    <th width="15%" class="text-center">Autoavaliação</th>
                    <th width="15%" class="text-center">Avaliadores</th>
                    <th width="30%">Comentários</th>
                </tr>
            </thead>
            <tbody>
                @foreach($skills as $skillName => $dadosSkill)
                    @php
                        $nColab = isset($dadosSkill['colab']['nota']) ? $dadosSkill['colab']['nota'] : '-';
                        $nAval = isset($dadosSkill['aval']['nota']) ? $dadosSkill['aval']['nota'] : '-';
                        $comentarios = [];
                        if (isset($dadosSkill['aval']['comentarios']) && is_array($dadosSkill['aval']['comentarios'])) {
                            $comentarios = $dadosSkill['aval']['comentarios'];
                        }
                    @endphp
                    <tr>
                        <td>{{ $skillName }}</td>
                        <td class="text-center purple highlight">{{ $nColab }}</td>
                        <td class="text-center blue highlight">{{ $nAval }}</td>
                        <td style="font-size: 11px;">
                            @if(count($comentarios) > 0)
                                <ul style="margin: 0; padding-left: 15px;">
                                    @foreach($comentarios as $c)
                                        <li>{{ $c }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span style="color: #999;">Sem comentários</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <!-- Considerações Finais -->
    @if(!empty($data['scores']['med']['comentarios']) || !empty($data['scores']['observacao_avaliado']))
        <h2>Considerações Finais</h2>
        <div style="background-color: #fffaf0; border: 1px solid #fbd38d; padding: 15px;">
            @if(!empty($data['scores']['med']['comentarios']))
                <h4 style="margin: 0 0 5px 0; color: #dd6b20;">Fechamento (RH/Gestor)</h4>
                <p style="margin: 0 0 15px 0;">{!! nl2br(e($data['scores']['med']['comentarios'])) !!}</p>
            @endif

            @if(!empty($data['scores']['observacao_avaliado']))
                <h4 style="margin: 0 0 5px 0; color: #dd6b20;">Auto-reflexão do Colaborador</h4>
                <p style="margin: 0;">{!! nl2br(e($data['scores']['observacao_avaliado'])) !!}</p>
            @endif
        </div>
    @endif

    <div class="footer">
        Gerado pelo sistema Talenti-Pro em {{ date('d/m/Y H:i') }}
    </div>

</body>
</html>
