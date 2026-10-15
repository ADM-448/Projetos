<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório Executivo - Plano de Cargos e Salários</title>
    <style>
        @page {
            margin: 25px 30px 45px 30px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1E293B;
            line-height: 1.35;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0F172A;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .header-logo-italent {
            text-align: left;
            vertical-align: middle;
            width: 35%;
        }
        .header-title-box {
            text-align: center;
            vertical-align: middle;
            width: 30%;
        }
        .header-logo-cliente {
            text-align: right;
            vertical-align: middle;
            width: 35%;
        }
        .title-main {
            font-size: 14px;
            font-weight: bold;
            color: #0F172A;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .subtitle-main {
            font-size: 9px;
            color: #64748B;
            margin-top: 2px;
        }
        .badge-bonus {
            display: inline-block;
            background-color: #EFF6FF;
            color: #1D4ED8;
            border: 1px solid #BFDBFE;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 4px;
        }
        .kpi-table {
            width: 100%;
            margin-bottom: 15px;
            border-spacing: 6px;
        }
        .kpi-card {
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 4px;
            padding: 8px 10px;
            text-align: center;
        }
        .kpi-value {
            font-size: 15px;
            font-weight: bold;
            color: #0F172A;
        }
        .kpi-label {
            font-size: 8px;
            color: #64748B;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #0F172A;
            border-bottom: 1px solid #CBD5E1;
            padding-bottom: 4px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.data-table th {
            background-color: #0F172A;
            color: #FFFFFF;
            font-size: 8px;
            text-transform: uppercase;
            padding: 6px 5px;
            text-align: left;
        }
        table.data-table td {
            padding: 6px 5px;
            border-bottom: 1px solid #E2E8F0;
            font-size: 9px;
        }
        table.data-table tr:nth-child(even) {
            background-color: #F8FAFC;
        }

        .badge-status {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
        }
        .badge-conforme {
            background-color: #ECFDF5;
            color: #047857;
            border: 1px solid #A7F3D0;
        }
        .badge-abaixo {
            background-color: #FEF2F2;
            color: #B91C1C;
            border: 1px solid #FECACA;
        }
        .badge-acima {
            background-color: #FFFBEB;
            color: #B45309;
            border: 1px solid #FDE68A;
        }

        .recommendation-box {
            background-color: #F8FAFC;
            border-left: 3px solid #3B82F6;
            padding: 8px 10px;
            margin-bottom: 8px;
            border-radius: 0 4px 4px 0;
        }
        .rec-cargo {
            font-size: 10px;
            font-weight: bold;
            color: #1E293B;
        }
        .rec-text {
            font-size: 8.5px;
            color: #475569;
            margin-top: 3px;
        }
        .rec-action {
            font-size: 8.5px;
            font-weight: bold;
            color: #0F172A;
            margin-top: 2px;
        }

        .legal-notice {
            background-color: #FEF2F2;
            border: 1px solid #FCA5A5;
            border-radius: 4px;
            padding: 8px 10px;
            margin-top: 15px;
            font-size: 8px;
            color: #991B1B;
            line-height: 1.4;
        }
        .footer-note {
            text-align: center;
            font-size: 8px;
            color: #94A3B8;
            margin-top: 15px;
            border-top: 1px solid #E2E8F0;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    <!-- CABEÇALHO COM AMBOS OS LOGOTIPOS -->
    <table class="header-table">
        <tr>
            <td class="header-logo-italent">
                @if(!empty($logoItTalentBase64))
                    <img src="{{ $logoItTalentBase64 }}" style="max-height: 40px; max-width: 140px;" alt="ItTalent Logo">
                @else
                    <span style="font-size: 16px; font-weight: bold; color: #0F172A;">ItTalent BI</span>
                @endif
            </td>
            <td class="header-title-box">
                <div class="title-main">Plano de Cargos & Salários</div>
                <div class="subtitle-main">Diagnóstico Salarial & Governança CBO</div>
                <div class="badge-bonus">Módulo Bonificado (Fase de Degustação)</div>
            </td>
            <td class="header-logo-cliente">
                @if(!empty($logoClienteBase64))
                    <img src="{{ $logoClienteBase64 }}" style="max-height: 40px; max-width: 140px;" alt="{{ $empresaNome }}">
                @else
                    <div style="font-size: 13px; font-weight: bold; color: {{ $corPrimaria ?? '#1E3A8A' }};">{{ $empresaNome ?? 'Empresa Cliente' }}</div>
                    <div style="font-size: 8px; color: #64748B;">Data: {{ date('d/m/Y') }}</div>
                @endif
            </td>
        </tr>
    </table>

    <!-- KPIS RESUMO -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-value">{{ count($funcionarios) }}</div>
                <div class="kpi-label">Colaboradores Avaliados</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-value" style="color: #047857;">{{ $totalConforme ?? 0 }}</div>
                <div class="kpi-label">Salários Conformes</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-value" style="color: #B91C1C;">{{ $totalAbaixo ?? 0 }}</div>
                <div class="kpi-label">Abaixo da Tabela (Dissídio)</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-value" style="color: #B45309;">{{ $totalAcima ?? 0 }}</div>
                <div class="kpi-label">Acima do Teto (Anomalias)</div>
            </td>
        </tr>
    </table>

    <!-- TABELA COMPARATIVA -->
    <div class="section-title">Quadro Comparativo de Cargos e Remuneração</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Colaborador</th>
                <th>Cargo Informado</th>
                <th>Cargo CBO Oficial</th>
                <th style="text-align: right;">Salário Atual</th>
                <th style="text-align: right;">Faixa Mercado (Piso / Teto)</th>
                <th style="text-align: center;">Situação</th>
            </tr>
        </thead>
        <tbody>
            @foreach($funcionarios as $func)
                <tr>
                    <td><strong>{{ $func['nome'] ?? 'Colaborador' }}</strong></td>
                    <td>{{ $func['cargo_informado'] ?? $func['cargo'] }}</td>
                    <td>
                        {{ $func['cbo_info']['cargo_real'] ?? $func['cargo'] }}
                        @if(!empty($func['cbo_info']['foi_corrigido']))
                            <span style="color: #2563EB; font-size: 7.5px;">(Corrigido)</span>
                        @endif
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        R$ {{ number_format($func['salario_atual'], 2, ',', '.') }}
                    </td>
                    <td style="text-align: right; color: #475569;">
                        @if(isset($func['piso']) && isset($func['teto']))
                            R$ {{ number_format($func['piso'], 2, ',', '.') }} - {{ number_format($func['teto'], 2, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($func['analise']['status'] === 'conforme')
                            <span class="badge-status badge-conforme">Conforme</span>
                        @elseif($func['analise']['status'] === 'abaixo_tabela')
                            <span class="badge-status badge-abaixo">Abaixo Tabela</span>
                        @elseif($func['analise']['status'] === 'acima_teto')
                            <span class="badge-status badge-acima">Acima do Teto</span>
                        @else
                            <span class="badge-status" style="background-color: #F1F5F9; color: #64748B;">Sem Dados</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- RECOMENDAÇÕES EXECUTIVAS -->
    <div class="section-title" style="margin-top: 15px;">Diretrizes Executivas e Recomendações Estratégicas</div>
    @foreach($funcionarios as $func)
        @if(in_array($func['analise']['status'], ['abaixo_tabela', 'acima_teto']) || !empty($func['cbo_info']['foi_corrigido']))
            <div class="recommendation-box" style="border-left-color: {{ $func['analise']['status'] === 'abaixo_tabela' ? '#EF4444' : ($func['analise']['status'] === 'acima_teto' ? '#F59E0B' : '#3B82F6') }};">
                <div class="rec-cargo">
                    {{ $func['nome'] }} &mdash; {{ $func['cbo_info']['cargo_real'] }} ({{ $func['cbo_info']['cbo'] }})
                </div>
                <div class="rec-text">
                    {{ $func['analise']['recomendacao'] }}
                </div>
                <div class="rec-action">
                    &bull; Diretriz da Diretoria: {{ $func['analise']['diretriz_diretoria'] }}
                </div>
                @if(!empty($func['cbo_info']['foi_corrigido']))
                    <div style="font-size: 8px; color: #2563EB; margin-top: 2px;">
                        &bull; {{ $func['cbo_info']['aviso_ajuste'] }}
                    </div>
                @endif
            </div>
        @endif
    @endforeach

    <!-- AVISO LEGAL OBRIGATÓRIO -->
    <div class="legal-notice">
        <strong>AVISO LEGAL E DE CONFORMIDADE JURÍDICA:</strong><br>
        {{ $avisoLegal }}
    </div>

    <!-- NOTA DE PREÇÁRIO / BÔNUS -->
    <div class="footer-note">
        Este relatório faz parte do módulo de Governança Salarial ItTalent. Fornecido como bônus durante a fase de testes. A disponibilidade contínua deste serviço estará sujeita à contratação da licença executiva.
    </div>

</body>
</html>
