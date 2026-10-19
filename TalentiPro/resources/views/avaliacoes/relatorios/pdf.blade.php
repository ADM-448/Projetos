<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Desempenho - {{ $data['colaborador']['name'] }}</title>
    @php
        $corPrimaria = $data['empresa']['cor_primaria'] ?? '#4f46e5';
        $corSecundaria = $data['empresa']['cor_secundaria'] ?? '#7c3aed';
        $corTerciaria = $data['empresa']['cor_terciaria'] ?? '#1e1b4b';
    @endphp
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #1e293b;
            margin: 0;
            padding: 14px 20px;
            line-height: 1.4;
        }

        /* CABEÇALHO 50% / 50% SIMÉTRICO */
        .header-table {
            width: 100%;
            border-bottom: 2px solid {{ $corPrimaria }};
            padding-bottom: 8px;
            margin-bottom: 10px;
            table-layout: fixed;
        }

        /* INFO DO COLABORADOR */
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            margin-bottom: 10px;
            table-layout: fixed;
        }
        .info-grid td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; }
        .info-grid tr:last-child td { border-bottom: none; }
        .info-grid .lbl { font-weight: bold; color: #64748b; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-grid .val { color: #0f172a; font-weight: 700; font-size: 10px; }

        /* TÍTULOS DE SEÇÃO */
        h2 {
            font-size: 9.5px;
            font-weight: 800;
            color: #0f172a;
            margin: 10px 0 5px 0;
            padding-bottom: 3px;
            border-bottom: 1px solid #e2e8f0;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        /* SCORES */
        .scores-wrap { width: 100%; border-collapse: collapse; margin-bottom: 10px; table-layout: fixed; }
        .score-box { text-align: center; padding: 6px 4px; border: 1px solid #e2e8f0; background: #ffffff; }
        .score-box .s-label { font-size: 7.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
        .score-box .s-value { font-size: 17px; font-weight: 800; }
        .score-auto  { color: {{ $corSecundaria }}; border-top: 3px solid {{ $corSecundaria }}; }
        .score-aval  { color: {{ $corPrimaria }}; border-top: 3px solid {{ $corPrimaria }}; }
        .score-med   { color: #ec4899; border-top: 3px solid #ec4899; }
        .score-final { color: #059669; border-top: 3px solid #059669; }

        /* COMPARATIVOS */
        .comp-wrap { width: 100%; border-collapse: collapse; margin-bottom: 10px; table-layout: fixed; }
        .comp-box { text-align: center; padding: 6px 4px; border: 1px solid #e2e8f0; background: #f8fafc; }
        .comp-box .c-label { font-size: 7.5px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold; }
        .comp-box .c-value { font-size: 13.5px; font-weight: 800; color: #0f172a; margin-top: 2px; }

        /* COMPETÊNCIAS */
        .comp-header {
            background: {{ $corTerciaria }};
            color: #ffffff;
            padding: 5px 10px;
            font-weight: 700;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 8px;
        }
        .skill-table { width: 100%; border-collapse: collapse; margin-bottom: 0; font-size: 9px; }
        .skill-table th {
            background: #f1f5f9; padding: 4px 8px; text-align: left;
            font-weight: 700; color: #475569; font-size: 7.5px;
            text-transform: uppercase; letter-spacing: 0.4px;
            border-bottom: 1px solid #e2e8f0;
        }
        .skill-table th.center, .skill-table td.center { text-align: center; }
        .skill-table td { padding: 4px 8px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
        .skill-table tr:last-child td { border-bottom: none; }
        .skill-table tr:nth-child(even) td { background: #fafafa; }

        .badge-auto  { color: {{ $corSecundaria }}; font-weight: 800; font-size: 10.5px; }
        .badge-aval  { color: {{ $corPrimaria }}; font-weight: 800; font-size: 10.5px; }
        .badge-final { color: #059669; font-weight: 800; font-size: 10.5px; }
        .dash        { color: #cbd5e1; }

        /* CONSIDERAÇÕES */
        .feedback-box { border: 1px solid #e2e8f0; border-left: 3px solid {{ $corPrimaria }}; background: #f8fafc; padding: 8px 10px; margin-top: 8px; font-size: 8.5px; page-break-inside: avoid; }
        .feedback-box h4 { margin: 0 0 3px 0; font-size: 8.5px; color: {{ $corPrimaria }}; text-transform: uppercase; letter-spacing: 0.4px; }
        .feedback-box p  { margin: 0 0 6px 0; color: #334155; line-height: 1.4; text-align: justify; }

        /* TERMO LGPD */
        .termo-lgpd-pdf {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 3px solid {{ $corPrimaria }};
            padding: 6px 10px;
            margin-bottom: 10px;
            font-size: 7px;
            line-height: 1.35;
            color: #64748b;
            text-align: justify;
        }
        .termo-lgpd-pdf strong { color: #1e293b; }

        /* RODAPÉ */
        .footer { text-align: center; font-size: 7.5px; color: #94a3b8; margin-top: 14px; border-top: 1px solid #e2e8f0; padding-top: 6px; position: fixed; bottom: -20px; width: 100%; }

        .competencia-section { page-break-inside: avoid; margin-bottom: 8px; }
    </style>
</head>
<body>

    {{-- CABEÇALHO COM LOGO E IDENTIFICAÇÃO DA EMPRESA CLIENTE E TALENTIPRO - 50% / 50% --}}
    <table class="header-table">
        <tr>
            {{-- LADO ESQUERDO: EMPRESA CLIENTE (50%) --}}
            <td style="width: 50%; vertical-align: middle; padding-right: 10px;">
                <div style="font-size: 7px; text-transform: uppercase; letter-spacing: 0.8px; color: #64748b; font-weight: bold; margin-bottom: 2px;">
                    Empresa Avaliadora
                </div>
                @if(!empty($data['empresa']['logo_path']) && file_exists($data['empresa']['logo_path']))
                    <img src="{{ $data['empresa']['logo_path'] }}" style="max-height: 40px; max-width: 150px; margin-bottom: 3px; display: block; object-fit: contain;">
                @elseif(!empty($data['empresa']['logo_url']))
                    <img src="{{ $data['empresa']['logo_url'] }}" style="max-height: 40px; max-width: 150px; margin-bottom: 3px; display: block; object-fit: contain;">
                @endif
                <div style="font-size: 12.5px; font-weight: 800; color: #0f172a; letter-spacing: -0.3px;">{{ $data['empresa']['nome'] ?? 'Empresa Avaliadora' }}</div>
                @if(!empty($data['empresa']['cnpj']))
                    <div style="font-size: 7.5px; color: #64748b; font-weight: 600; margin-top: 1px;">CNPJ: {{ $data['empresa']['cnpj'] }}</div>
                @endif
            </td>

            {{-- LADO DIREITO: LOGO TALENTIPRO E DADOS DA AVALIAÇÃO (50%) --}}
            <td style="width: 50%; text-align: right; vertical-align: middle; border-left: 1px solid #f1f5f9; padding-left: 10px;">
                <div style="margin-bottom: 2px;">
                    @if(file_exists(public_path('images/logo-talentipro.png')))
                        <img src="{{ public_path('images/logo-talentipro.png') }}" style="height: 24px; width: auto; display: inline-block;">
                    @elseif(file_exists(public_path('images/logo-talentipro.jpg')))
                        <img src="{{ public_path('images/logo-talentipro.jpg') }}" style="height: 24px; width: auto; display: inline-block;">
                    @else
                        <div style="font-size: 15px; font-weight: 800; color: #0f172a; letter-spacing: -0.3px;">Talenti<span style="color: {{ $corPrimaria }};">Pro</span></div>
                    @endif
                </div>
                <div style="font-size: 10px; font-weight: 800; color: {{ $corPrimaria }}; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1px;">
                    Relatório de Desempenho
                </div>
                <div style="font-size: 8px; color: #475569;">Campanha: <strong>{{ $data['avaliacao']['titulo'] ?? 'Avaliação' }}</strong></div>
                <div style="font-size: 7.5px; color: #64748b;">Data de Emissão: <strong>{{ date('d/m/Y H:i') }}</strong></div>
            </td>
        </tr>
    </table>

    {{-- TERMO DE CONFIDENCIALIDADE E PROTEÇÃO DE DADOS (LGPD) --}}
    <div class="termo-lgpd-pdf">
        <strong>DOCUMENTO CONFIDENCIAL • PROTEÇÃO DE DADOS (LGPD - LEI Nº 13.709/2018):</strong>
        Este relatório contém avaliações funcionais e dados de desempenho de caráter sigiloso e intransferível, de uso exclusivo do colaborador avaliado e da liderança corporativa/RH autorizados. É proibido qualquer compartilhamento, reprodução ou divulgação a terceiros sem autorização prévia por escrito, sujeitando o infrator às sanções legais cabíveis (LGPD - Lei 13.709/2018, Art. 5º, X da CF/88, Arts. 153/154 do Código Penal e Art. 482, 'g' da CLT).
    </div>

    {{-- INFO DO COLABORADOR --}}
    <table class="info-grid">
        <tr>
            <td width="10%"><span class="lbl">Nome</span></td>
            <td width="35%"><span class="val">{{ $data['colaborador']['name'] }}</span></td>
            <td width="10%"><span class="lbl">Cargo</span></td>
            <td width="45%"><span class="val">{{ $data['colaborador']['cargo']['nome'] ?? 'N/A' }}</span></td>
        </tr>
        <tr>
            <td><span class="lbl">Setor</span></td>
            <td><span class="val">{{ $data['colaborador']['departamento']['nome'] ?? 'N/A' }}</span></td>
            <td><span class="lbl">Líder</span></td>
            <td><span class="val">{{ $data['colaborador']['gestor']['name'] ?? 'N/A' }}</span></td>
        </tr>
    </table>

    @php
        $sAuto  = (float)($data['scores']['colab']['media'] ?? 0);
        $sAval  = (float)($data['scores']['aval']['media'] ?? 0);
        $sMed   = isset($data['scores']['med']['media']) && $data['scores']['med']['media'] !== null
                  ? (float)$data['scores']['med']['media'] : null;
        $parts  = array_filter([$sAuto > 0 ? $sAuto : null, $sAval > 0 ? $sAval : null, $sMed]);
        $mFinal = count($parts) > 0 ? round(array_sum($parts) / count($parts), 2) : 0;
        $grau   = $data['avaliacao']['grau'] ?? null;
        $is90   = $grau == 90;
        $is360  = $grau == 360;
    @endphp

    {{-- RESUMO DAS NOTAS --}}
    <h2>Resumo das Notas</h2>
    <table class="scores-wrap">
        <tr>
            @if(!$is90)
            <td style="width:24%; padding-right:5px;">
                <div class="score-box score-auto">
                    <div class="s-label">Autoavaliação</div>
                    <div class="s-value">{{ number_format($sAuto, 1) }}</div>
                </div>
            </td>
            @endif
            <td style="width:{{ $is90 ? '33' : '24' }}%; padding-right:5px;">
                <div class="score-box score-aval">
                    <div class="s-label">{{ $is360 ? 'Média Pares' : 'Avaliadores' }}</div>
                    <div class="s-value">{{ number_format($sAval, 1) }}</div>
                </div>
            </td>
            @if($sMed !== null)
            <td style="width:{{ $is90 ? '33' : '24' }}%; padding-right:5px;">
                <div class="score-box score-med">
                    <div class="s-label">Mediação</div>
                    <div class="s-value">{{ number_format($sMed, 1) }}</div>
                </div>
            </td>
            @endif
            <td style="width:{{ $is90 ? '34' : '24' }}%;">
                <div class="score-box score-final">
                    <div class="s-label">Média Final</div>
                    <div class="s-value">{{ number_format($mFinal, 1) }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- COMPARATIVOS --}}
    <h2>Comparativo de Médias</h2>
    <table class="comp-wrap">
        <tr>
            <td style="width:25%; padding-right:5px;"><div class="comp-box"><div class="c-label">Indivíduo</div><div class="c-value">{{ number_format((float)$data['charts']['individual'], 1) }}</div></div></td>
            <td style="width:25%; padding-right:5px;"><div class="comp-box"><div class="c-label">Cargo</div><div class="c-value">{{ number_format((float)$data['charts']['cargo'], 1) }}</div></div></td>
            <td style="width:25%; padding-right:5px;"><div class="comp-box"><div class="c-label">Setor</div><div class="c-value">{{ number_format((float)$data['charts']['setor'], 1) }}</div></div></td>
            <td style="width:25%;"><div class="comp-box"><div class="c-label">Empresa</div><div class="c-value">{{ number_format((float)$data['charts']['empresa'], 1) }}</div></div></td>
        </tr>
    </table>

    {{-- DETALHAMENTO POR COMPETÊNCIA --}}
    @php
        $competencias = [];
        if (isset($data['scores']['colab']['detalhes']) && is_array($data['scores']['colab']['detalhes'])) {
            foreach ($data['scores']['colab']['detalhes'] as $d) {
                $comp = $d['competencia'] ?: 'Geral';
                $competencias[$comp][$d['pergunta']]['colab'] = $d;
            }
        }
        if (isset($data['scores']['aval']['detalhes']) && is_array($data['scores']['aval']['detalhes'])) {
            foreach ($data['scores']['aval']['detalhes'] as $d) {
                $comp = $d['competencia'] ?: 'Geral';
                $competencias[$comp][$d['pergunta']]['aval'] = $d;
            }
        }
    @endphp

    <h2>Detalhamento por Competência</h2>

    @foreach($competencias as $compName => $skills)
        @php
            $cNotas = array_filter(array_map(fn($s) => isset($s['colab']['nota']) ? (float)$s['colab']['nota'] : null, $skills));
            $aNotas = array_filter(array_map(fn($s) => isset($s['aval']['nota'])  ? (float)$s['aval']['nota']  : null, $skills));
            $avgCC = count($cNotas) ? number_format(array_sum($cNotas)/count($cNotas),1) : '-';
            $avgCA = count($aNotas) ? number_format(array_sum($aNotas)/count($aNotas),1) : '-';
            $fp    = array_filter([$avgCC !== '-' ? (float)$avgCC : null, $avgCA !== '-' ? (float)$avgCA : null]);
            $avgCF = count($fp) ? number_format(array_sum($fp)/count($fp),1) : '-';
        @endphp

        @php
            $compPeso = 1;
            foreach ($skills as $s) {
                if (isset($s['colab']['peso'])) { $compPeso = $s['colab']['peso']; break; }
                if (isset($s['aval']['peso'])) { $compPeso = $s['aval']['peso']; break; }
            }
        @endphp

        <div class="competencia-section">
        <div class="comp-header">
            ★ {{ strtoupper($compName) }} <span style="background: rgba(255,255,255,0.15); padding: 1px 6px; border-radius: 3px; font-size: 7.5px;">Peso: {{ $compPeso }}%</span>
            &nbsp;&nbsp;
            @if(!$is90)Média Auto: {{ $avgCC }} &nbsp;|&nbsp;@endif
            Média Avaliadores: {{ $avgCA }} &nbsp;|&nbsp;
            <span style="background:rgba(255,255,255,0.2);padding:1px 5px;border-radius:3px;">Média Geral: {{ $avgCF }}</span>
        </div>

        <table class="skill-table">
            <thead>
                <tr>
                    <th style="width:{{ $is90 ? '58%' : '50%' }};">Critério Avaliado</th>
                    @if(!$is90)<th class="center" style="width:17%;">Autoavaliação</th>@endif
                    <th class="center" style="width:{{ $is90 ? '21%' : '17%' }};">Avaliadores</th>
                    <th class="center" style="width:21%; color:#059669;">Média Final</th>
                </tr>
            </thead>
            <tbody>
                @foreach($skills as $skillName => $dadosSkill)
                    @php
                        $nC = isset($dadosSkill['colab']['nota']) ? $dadosSkill['colab']['nota'] : null;
                        $nA = isset($dadosSkill['aval']['nota'])  ? $dadosSkill['aval']['nota']  : null;
                        $fp2 = array_filter([$nC !== null ? (float)$nC : null, $nA !== null ? (float)$nA : null]);
                        $sF  = count($fp2) ? number_format(array_sum($fp2)/count($fp2),2) : null;
                    @endphp
                    <tr>
                        <td>
                            <strong style="color: #0f172a;">{{ $skillName }}</strong>
                            @if(!empty($dadosSkill['colab']['evidencia']))
                                <div style="font-size: 8px; color: #6b21a8; background: #faf5ff; padding: 2px 5px; border-radius: 3px; border-left: 2px solid #a855f7; margin-top: 3px;">
                                    <strong>Auto:</strong> {{ $dadosSkill['colab']['evidencia'] }}
                                </div>
                            @endif
                            @if(!empty($dadosSkill['aval']['evidencias']))
                                @foreach($dadosSkill['aval']['evidencias'] as $ev)
                                    <div style="font-size: 8px; color: #064e3b; background: #f0fdf4; padding: 2px 5px; border-radius: 3px; border-left: 2px solid #10b981; margin-top: 3px;">
                                        <strong>Evidência{{ $ev['avaliador'] && $ev['avaliador'] !== 'Avaliador' ? ' (' . $ev['avaliador'] . ')' : '' }}:</strong> {{ $ev['texto'] }}
                                    </div>
                                @endforeach
                            @endif
                        </td>
                        @if(!$is90)
                        <td class="center">
                            @if($nC !== null)<span class="badge-auto">{{ $nC }}</span>@else<span class="dash">—</span>@endif
                        </td>
                        @endif
                        <td class="center">
                            @if($nA !== null)<span class="badge-aval">{{ $nA }}</span>@else<span class="dash">—</span>@endif
                        </td>
                        <td class="center">
                            @if($sF !== null)<span class="badge-final">{{ $sF }}</span>@else<span class="dash">—</span>@endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @endforeach

    {{-- CONSIDERAÇÕES FINAIS --}}
    @if(!empty($data['scores']['med']['comentarios']) || !empty($data['scores']['observacao_avaliado']))
        <div class="feedback-box">
            @if(!empty($data['scores']['med']['comentarios']))
                <h4>Fechamento — RH / Gestor</h4>
                <p>{!! nl2br(e($data['scores']['med']['comentarios'])) !!}</p>
            @endif
            @if(!empty($data['scores']['observacao_avaliado']))
                <h4 style="color:#1d4ed8;border-top:1px dashed #bfdbfe;padding-top:5px;margin-top:2px;">Auto-reflexão do Colaborador</h4>
                <p style="margin:0;">{!! nl2br(e($data['scores']['observacao_avaliado'])) !!}</p>
            @endif
        </div>
    @endif

    <div class="footer">
        Relatório gerado automaticamente pelo sistema <strong>TalentiPro</strong> em {{ date('d/m/Y \à\s H:i') }}
    </div>

</body>
</html>
