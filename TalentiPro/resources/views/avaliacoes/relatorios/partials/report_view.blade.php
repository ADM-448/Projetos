<style>
    .chart-circle {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: 800;
        color: #1e293b;
        background: conic-gradient(#f1f5f9 0%, #f1f5f9 100%);
        position: relative;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03), inset 0 2px 10px rgba(0, 0, 0, 0.03);
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .chart-circle:hover {
        transform: scale(1.08) rotate(5deg);
    }

    .chart-circle::before {
        content: "";
        position: absolute;
        inset: 14px;
        background: #ffffff;
        border-radius: 50%;
        box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.04);
        z-index: 0;
    }

    .chart-value {
        position: relative;
        z-index: 1;
        background: linear-gradient(135deg, #1e293b, #475569);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-family: 'Inter', sans-serif;
    }

    .score-card {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 20px;
        padding: 30px 20px;
        text-align: center;
        flex: 1;
        min-width: 200px;
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.4s ease;
        position: relative;
        overflow: hidden;
    }

    .score-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 25px 35px -5px rgba(0, 0, 0, 0.1), 0 15px 15px -5px rgba(0, 0, 0, 0.04);
    }

    .score-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: var(--card-color, #cbd5e1);
        opacity: 0.8;
    }

    .scores-grid {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
        margin-bottom: 40px;
    }

    .comparative-box {
        flex: 1;
        min-width: 150px;
        text-align: center;
        padding: 24px 20px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .comparative-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.5) 0%, rgba(255, 255, 255, 0) 100%);
        pointer-events: none;
    }

    .comparative-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.05);
        border-color: #cbd5e1;
    }

    .table-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        margin-bottom: 30px;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.03);
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        padding: 20px 30px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .table-custom {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    @media screen and (max-width: 768px) {

        .table-custom,
        .table-custom tbody,
        .table-custom tr,
        .table-custom td {
            display: block;
            width: 100%;
        }

        .table-custom thead {
            display: none;
        }

        .table-custom tr {
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
            background: #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
        }

        .table-custom td {
            border: none;
            padding: 10px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: right !important;
            gap: 8px;
        }

        .table-custom td::before {
            content: attr(data-label);
            font-weight: 700;
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
            text-align: left;
            flex-shrink: 0;
        }

        /* Campanha: empilha label em cima, valor embaixo (nome longo) */
        .table-custom td[data-label="Campanha"] {
            flex-direction: column;
            align-items: flex-start;
            text-align: left !important;
            gap: 4px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 12px;
            margin-bottom: 4px;
        }

        .table-custom td[data-label="Campanha"] > span,
        .table-custom td[data-label="Campanha"] {
            word-break: break-word;
            overflow-wrap: anywhere;
            white-space: normal;
        }

        .table-custom td:first-child {
            display: block;
            font-size: 1.05rem;
            color: #0f172a;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 12px;
            margin-bottom: 8px;
            text-align: left !important;
            line-height: 1.5;
        }

        .table-custom td:first-child::before {
            display: none;
        }

        .table-custom td:last-child {
            border-top: 1px dashed #e2e8f0;
            margin-top: 8px;
            padding-top: 12px;
        }

        /* Score cards: uma coluna só no mobile */
        .scores-grid {
            flex-direction: column;
            gap: 16px;
        }

        .score-card {
            min-width: 0 !important;
            width: 100%;
        }

        /* Header do card de competência: empilha no mobile */
        .table-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 10px;
            padding: 16px !important;
        }

        /* Remove padding-left fixo das médias no mobile */
        .table-header > div[style*="padding-left: 42px"] {
            padding-left: 0 !important;
        }

        .table-card {
            background: transparent;
            border: none;
            box-shadow: none;
            margin-bottom: 15px;
        }

        .table-header {
            border-radius: 16px;
            margin-bottom: 15px;
            border: 1px solid #e2e8f0;
            background: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        }
    }

    .table-custom th {
        padding: 16px 30px;
        font-weight: 700;
        color: #64748b;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        background: #ffffff;
        border-bottom: 2px solid #e2e8f0;
    }

    .table-custom td {
        padding: 20px 30px;
        color: #334155;
        font-size: 0.95rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .table-custom tr:last-child td {
        border-bottom: none;
    }

    .table-custom tr:hover td {
        background-color: #f8fafc;
    }

    .badge-score {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 44px;
        height: 44px;
        border-radius: 12px;
        font-weight: 800;
        font-size: 1.1rem;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .section-title {
        color: #0f172a;
        margin-bottom: 25px;
        font-size: 1.5rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 12px;
        position: relative;
    }

    .icon-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: #f1f5f9;
    }

    @page {
        size: A4 portrait;
        margin: 10mm 10mm 12mm 10mm;
    }

    /* Print Styles - Para deixar o PDF executivo, proporcional e sem vazios */
    @media print {
        /* Esconde menus, cabeçalhos, botões, sidebar e filtros */
        aside, header, nav, .navbar, .sidebar, #btnExportPdf, #aiActionContainer, footer,
        .header-actions, .filter-panel, button, .no-print {
            display: none !important;
        }

        .only-print {
            display: inline-block !important;
        }

        /* Ocupa a página inteira e remove scrolls que cortam o PDF */
        body, html, .main-content, .content-wrapper, main, .page-content {
            background: white !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            height: auto !important;
            min-height: auto !important;
            overflow: visible !important;
            position: relative !important;
        }

        #reportContent {
            margin-top: 0 !important;
            padding-top: 0 !important;
            display: block !important;
        }

        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            box-shadow: none !important;
            text-shadow: none !important;
        }

        /* Reduz espaçamentos gerais gigantescos para caber harmonicamente no A4 */
        .report-header-card {
            border: 1px solid #e2e8f0 !important;
            padding: 14px 18px !important;
            margin-bottom: 12px !important;
            box-shadow: none !important;
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        .header-main-grid {
            display: table !important;
            width: 100% !important;
            table-layout: fixed !important;
        }

        .header-col-empresa {
            display: table-cell !important;
            width: 50% !important;
            vertical-align: middle !important;
            padding-right: 15px !important;
        }

        .header-col-colab {
            display: table-cell !important;
            width: 50% !important;
            vertical-align: middle !important;
            border-left: 2px solid #f1f5f9 !important;
            padding-left: 18px !important;
        }

        .termo-lgpd-box {
            background: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            border-left: 4px solid var(--empresa-cor-primaria, #4f46e5) !important;
            padding: 9px 14px !important;
            margin-bottom: 14px !important;
            font-size: 0.72rem !important;
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        .termo-lgpd-box p {
            font-size: 0.7rem !important;
            line-height: 1.4 !important;
            color: #475569 !important;
            margin: 0 !important;
            text-align: justify !important;
        }

        /* Bloco comparativos gerais enxuto e perfeitamente simétrico */
        .comparative-grid-container {
            display: table !important;
            width: 100% !important;
            table-layout: fixed !important;
            gap: 0 !important;
        }

        .comparative-box {
            display: table-cell !important;
            width: 25% !important;
            min-width: 0 !important;
            max-width: none !important;
            padding: 10px 8px !important;
            margin: 0 4px !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 8px !important;
            text-align: center !important;
            vertical-align: middle !important;
        }

        .comparative-box p {
            font-size: 0.68rem !important;
            margin-bottom: 4px !important;
        }

        .comparative-box .comp-value {
            font-size: 1.55rem !important;
        }

        /* Grid de notas circulares enxuta */
        .scores-grid {
            display: flex !important;
            gap: 12px !important;
            margin-bottom: 14px !important;
        }

        .score-card {
            border: 1px solid #e2e8f0 !important;
            padding: 12px 10px !important;
            margin-bottom: 0 !important;
            border-radius: 10px !important;
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        .score-card h3 {
            font-size: 0.92rem !important;
            margin-bottom: 2px !important;
        }

        .score-card p {
            font-size: 0.7rem !important;
            margin: 2px 0 6px 0 !important;
        }

        .chart-circle {
            width: 78px !important;
            height: 78px !important;
            margin: 6px auto !important;
            font-size: 1.2rem !important;
        }

        .chart-circle::before {
            inset: 7px !important;
        }

        .chart-value {
            font-size: 1.2rem !important;
        }

        /* Títulos de seção nunca ficam órfãos no fim da página */
        .section-title {
            break-after: avoid !important;
            page-break-after: avoid !important;
            font-size: 1.05rem !important;
            margin-top: 14px !important;
            margin-bottom: 8px !important;
        }

        /* Cards de competência com tabela */
        .table-card {
            border: 1px solid #e2e8f0 !important;
            padding: 10px 14px !important;
            margin-bottom: 10px !important;
            border-radius: 8px !important;
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        .table-custom th, .table-custom td {
            padding: 6px 8px !important;
            font-size: 0.8rem !important;
        }

        /* Considerações Finais */
        #feedbackContainer {
            border: 1px solid #e2e8f0 !important;
            border-left: 4px solid #8b5cf6 !important;
            padding: 14px 18px !important;
            margin-bottom: 14px !important;
            border-radius: 8px !important;
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        .obs-card-item {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
            margin-bottom: 8px !important;
            padding: 10px 12px !important;
            border-radius: 6px !important;
        }

        /* Se tiver PDI injetado depois */
        .pdi-print-section {
            page-break-before: always;
        }
    }

    .only-print {
        display: none;
    }
</style>

<div id="reportContent" style="display: none; margin-top: 10px; animation: fadeIn 0.6s cubic-bezier(0.4, 0, 0.2, 1);">

    <!-- Cabeçalho Executivo: Identificação da Empresa Cliente e Colaborador -->
    <div class="report-header-card"
        style="background: white; padding: 22px 28px; border-radius: 18px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.04); border: 1px solid #e2e8f0; margin-bottom: 20px;">
        
        <!-- Barra Superior: Identificação da Plataforma TalentiPro e Ação -->
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 18px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                @if(file_exists(public_path('images/logo-talentipro.png')))
                    <img src="{{ asset('images/logo-talentipro.png') }}" alt="TalentiPro" style="height: 24px; width: auto; display: inline-block;">
                @elseif(file_exists(public_path('images/logo-talentipro.jpg')))
                    <img src="{{ asset('images/logo-talentipro.jpg') }}" alt="TalentiPro" style="height: 24px; width: auto; display: inline-block;">
                @else
                    <span style="font-size: 1.1rem; font-weight: 800; color: #0f172a; display: inline-flex; align-items: center; gap: 7px; letter-spacing: -0.3px;">
                        <i class="fa-solid fa-brain" style="color: var(--empresa-cor-primaria, var(--primary-color, #4f46e5)); font-size: 1.15rem;"></i>
                        Talenti<span style="color: var(--empresa-cor-primaria, var(--primary-color, #4f46e5));">Pro</span>
                    </span>
                @endif
                <span style="color: #cbd5e1; font-size: 0.85rem; margin: 0 4px;">|</span>
                <span style="font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                    Gestão Inteligente de Talentos
                </span>
            </div>

            <div>
                @if(auth()->user()->perfil_acesso->value !== 'colaborador')
                    <a href="#" id="btnExportPdf" onclick="imprimirRelatorioPdf(); return false;" class="btn no-print"
                        style="background: linear-gradient(135deg, var(--empresa-cor-primaria, #4f46e5), var(--empresa-cor-secundaria, #4338ca)); color: white; border: none; padding: 8px 18px; border-radius: 10px; font-weight: 700; font-size: 0.85rem; text-decoration: none; box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.2); transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-print"></i> Salvar Relatório
                    </a>
                @endif
                <span class="only-print" style="font-size: 0.75rem; color: #64748b; font-weight: 600;">
                    Emitido em: {{ date('d/m/Y H:i') }}
                </span>
            </div>
        </div>

        <!-- Linha Principal: Empresa Cliente (Esquerda) e Colaborador (Direita) - 50% / 50% Perfeitamente Simétrico -->
        <div class="header-main-grid" style="display: flex; gap: 20px; align-items: center; justify-content: space-between;">
            
            <!-- Coluna 1: Empresa Cliente & Logotipo (50%) -->
            <div class="header-col-empresa" style="flex: 1; width: 50%; display: flex; align-items: center; gap: 16px;">
                <div id="reportEmpresaLogoWrapper" style="display: flex; align-items: center; justify-content: center; min-width: 64px; max-width: 140px; height: 56px;">
                    <img id="reportEmpresaLogo" src="" alt="Logotipo da Empresa" style="display: none; max-height: 52px; max-width: 135px; width: auto; height: auto; object-fit: contain; border-radius: 6px;">
                    <div id="reportEmpresaIcon" style="display: flex; align-items: center; justify-content: center; width: 52px; height: 52px; border-radius: 12px; background: linear-gradient(135deg, var(--empresa-cor-primaria, #1e293b), var(--empresa-cor-secundaria, #334155)); color: white; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);">
                        <i class="fa-solid fa-building" style="font-size: 1.3rem;"></i>
                    </div>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <span style="display: inline-block; text-transform: uppercase; letter-spacing: 0.8px; color: #64748b; font-size: 0.68rem; font-weight: 800; margin-bottom: 2px;">
                        Empresa Avaliadora
                    </span>
                    <h2 id="reportEmpresaNome" style="color: #0f172a; margin: 0; font-size: 1.22rem; font-weight: 800; line-height: 1.25; letter-spacing: -0.3px; word-break: break-word;">
                        Empresa Cliente
                    </h2>
                    <span id="reportEmpresaCnpj" style="display: inline-block; margin-top: 4px; font-size: 0.73rem; color: #475569; background: #f1f5f9; padding: 2px 8px; border-radius: 6px; font-weight: 600;"></span>
                </div>
            </div>

            <!-- Coluna 2: Colaborador e Metadados (50%) -->
            <div class="header-col-colab" style="flex: 1; width: 50%; border-left: 2px solid #f1f5f9; padding-left: 20px;">
                <span id="lblColabHeader" style="display: inline-block; text-transform: uppercase; letter-spacing: 0.8px; color: var(--empresa-cor-primaria, #4f46e5); font-size: 0.68rem; font-weight: 800; margin-bottom: 2px;">
                    Colaborador Avaliado
                </span>
                <h3 style="color: #0f172a; margin: 0 0 6px 0; font-size: 1.35rem; font-weight: 800; line-height: 1.25; letter-spacing: -0.4px; word-break: break-word;">
                    <span id="reportTitle">Resultados da Avaliação</span>
                </h3>
                <div style="display: flex; flex-wrap: wrap; gap: 6px; font-size: 0.76rem;">
                    <span style="background: #f8fafc; border: 1px solid #e2e8f0; color: #334155; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 5px;">
                        <i class="fa-solid fa-briefcase" style="color: var(--empresa-cor-primaria, #6366f1);"></i>
                        <span style="color: #64748b;">Cargo:</span> <strong id="reportColabCargo" style="color: #0f172a;">—</strong>
                    </span>
                    <span style="background: #f8fafc; border: 1px solid #e2e8f0; color: #334155; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 5px;">
                        <i class="fa-solid fa-sitemap" style="color: var(--empresa-cor-secundaria, #0ea5e9);"></i>
                        <span style="color: #64748b;">Setor:</span> <strong id="reportColabSetor" style="color: #0f172a;">—</strong>
                    </span>
                    <span style="background: #f8fafc; border: 1px solid #e2e8f0; color: #334155; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 5px;">
                        <i class="fa-solid fa-clipboard-list" style="color: var(--empresa-cor-primaria, #8b5cf6);"></i>
                        <span style="color: #64748b;">Campanha:</span> <strong id="reportCampanhaTitulo" style="color: #0f172a;">—</strong>
                    </span>
                </div>
            </div>

        </div>
    </div>

    <!-- Caixa de Conformidade LGPD & Confidencialidade (Visual Executivo & Discreto) -->
    <div class="termo-lgpd-box"
        style="background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%); border: 1px solid #e2e8f0; border-left: 4px solid var(--empresa-cor-primaria, #4f46e5); border-radius: 12px; padding: 12px 18px; margin-bottom: 24px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 5px;">
            <div style="display: flex; align-items: center; gap: 8px; color: #1e293b; font-weight: 800; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.6px;">
                <i class="fa-solid fa-shield-halved" style="color: var(--empresa-cor-primaria, #4f46e5); font-size: 0.95rem;"></i>
                <span>Documento Confidencial • Proteção de Dados (LGPD)</span>
            </div>
            <span style="font-size: 0.7rem; font-weight: 700; color: var(--empresa-cor-primaria, #4338ca); background: #f1f5f9; padding: 2px 8px; border-radius: 10px; border: 1px solid #e2e8f0;">
                Lei Federal nº 13.709/2018
            </span>
        </div>
        <p style="margin: 0; font-size: 0.77rem; line-height: 1.5; color: #64748b; text-align: justify;">
            Este relatório contém dados pessoais e avaliações de desempenho de caráter estritamente confidencial, de propriedade e interesse exclusivo do colaborador avaliado e da liderança/RH autorizados. São vedados a divulgação, compartilhamento ou envio a terceiros sem prévia autorização formal, sob pena das sanções civis, penais e trabalhistas cabíveis (LGPD - Lei 13.709/2018, Art. 5º, X da CF/88, Arts. 153 e 154 do Código Penal e Art. 482, &ldquo;g&rdquo; da CLT).
        </p>
    </div>

    <!-- Seção: Comparativos Gerais -->
    @if(auth()->user()->perfil_acesso->value !== 'colaborador')
        <div
            style="background: white; border-radius: 20px; padding: 35px; margin-bottom: 45px; box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
            <h3 class="section-title" style="justify-content: center;">
                <div class="icon-wrapper" style="color: #f59e0b; background: #fef3c7;"><i
                        class="fa-solid fa-ranking-star"></i></div>
                Médias Comparativas Globais
            </h3>
            <div class="comparative-grid-container" style="display: flex; gap: 20px; flex-wrap: wrap; justify-content: center;">
                <div class="comparative-box" style="flex: 1; min-width: 140px; max-width: 250px;">
                    <p
                        style="margin: 0 0 10px 0; color: #64748b; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
                        Indivíduo</p>
                    <div style="font-size: 2.5rem; font-weight: 800; background: linear-gradient(135deg, #8b5cf6, #d946ef); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"
                        id="txtMediaInd">0.0</div>
                </div>

                @if(in_array(auth()->user()->perfil_acesso->value, ['master', 'admin', 'consultor']))
                    <div class="comparative-box" style="flex: 1; min-width: 140px; max-width: 250px;">
                        <p
                            style="margin: 0 0 10px 0; color: #64748b; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
                            Cargo</p>
                        <div style="font-size: 2.5rem; font-weight: 800; color: #1e293b;" id="txtMediaCargo">0.0</div>
                    </div>
                @endif

                @if(in_array(auth()->user()->perfil_acesso->value, ['master', 'admin', 'gestor', 'consultor']))
                    <div class="comparative-box" style="flex: 1; min-width: 140px; max-width: 250px;">
                        <p
                            style="margin: 0 0 10px 0; color: #64748b; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
                            Setor</p>
                        <div style="font-size: 2.5rem; font-weight: 800; color: #1e293b;" id="txtMediaSetor">0.0</div>
                    </div>
                @endif

                @if(in_array(auth()->user()->perfil_acesso->value, ['master', 'admin', 'consultor']))
                    <div class="comparative-box" style="flex: 1; min-width: 140px; max-width: 250px;">
                        <p
                            style="margin: 0 0 10px 0; color: #64748b; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
                            Empresa</p>
                        <div style="font-size: 2.5rem; font-weight: 800; color: #1e293b;" id="txtMediaEmpresa">0.0</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Seção: Histórico de Avaliações do Colaborador (Apenas para Gestor/Master/Admin) -->
        <div id="historicoContainer" style="display: none; background: white; border: 1px solid #e2e8f0; border-radius: 20px; padding: 30px; margin-bottom: 45px; box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                <h3 class="section-title" style="margin-bottom: 0;">
                    <div class="icon-wrapper" style="color: #6366f1; background: #e0e7ff;"><i class="fa-solid fa-timeline"></i></div>
                    Histórico & Evolução do Colaborador
                </h3>
                <span style="font-size: 0.85rem; color: #64748b; background: #f1f5f9; padding: 4px 12px; border-radius: 20px; font-weight: 600;">
                    <i class="fa-solid fa-lock" style="margin-right: 4px; font-size: 0.75rem;"></i> Acesso exclusivo Gestão / RH
                </span>
            </div>
            <div id="historicoTableWrapper" style="overflow-x: auto;">
                <!-- Tabela de histórico renderizada via JS -->
            </div>
        </div>
    @endif

    <!-- Seção: Gráficos de Círculo (Notas Principais) -->
    <div class="scores-grid">
        <div class="score-card" id="cardAuto" style="--card-color: #8b5cf6;">
            <h3 style="font-size: 1.25rem; color: #0f172a; margin-top: 0; font-weight: 800;">Autoavaliação</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin: 5px 0 25px 0;">Visão do próprio colaborador</p>
            <div style="display: flex; justify-content: center; margin: 20px 0;">
                <div class="chart-circle" id="circleAuto">
                    <span class="chart-value" id="scoreAuto"
                        style="background: linear-gradient(135deg, #8b5cf6, #6d28d9); -webkit-background-clip: text;">0.0</span>
                </div>
            </div>
        </div>

        <div class="score-card" style="--card-color: #3b82f6;">
            <h3 id="lblAvaliadoresCard" style="font-size: 1.25rem; color: #0f172a; margin-top: 0; font-weight: 800;">
                Avaliadores</h3>
            <p id="subAvaliadoresCard" style="color: #64748b; font-size: 0.9rem; margin: 5px 0 25px 0;">Média das
                avaliações recebidas</p>
            <div style="display: flex; justify-content: center; margin: 20px 0;">
                <div class="chart-circle" id="circleGestor">
                    <span class="chart-value" id="scoreGestor"
                        style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); -webkit-background-clip: text;">0.0</span>
                </div>
            </div>
        </div>

        <div class="score-card" id="cardMediacao" style="--card-color: #ec4899; display: none;">
            <h3 style="font-size: 1.25rem; color: #0f172a; margin-top: 0; font-weight: 800;">Nota de Mediação</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin: 5px 0 25px 0;">Avaliação do mediador responsável</p>
            <div style="display: flex; justify-content: center; margin: 20px 0;">
                <div class="chart-circle" id="circleFinal">
                    <span class="chart-value" id="scoreFinal"
                        style="background: linear-gradient(135deg, #ec4899, #be185d); -webkit-background-clip: text;">0.0</span>
                </div>
            </div>
        </div>

        <div class="score-card" id="cardMediaFinal" style="--card-color: #10b981;">
            <h3 style="font-size: 1.25rem; color: #0f172a; margin-top: 0; font-weight: 800;">Média Final</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin: 5px 0 25px 0;">Média consolidada</p>
            <div style="display: flex; justify-content: center; margin: 20px 0;">
                <div class="chart-circle" id="circleMediaConsolidada">
                    <span class="chart-value" id="scoreMediaConsolidada"
                        style="background: linear-gradient(135deg, #10b981, #047857); -webkit-background-clip: text;">0.0</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Seção: Detalhamento por Competência -->
    <h3 class="section-title">
        <div class="icon-wrapper" style="color: #3b82f6; background: #eff6ff;"><i class="fa-solid fa-layer-group"></i>
        </div>
        Detalhamento por Competência
    </h3>
    <div id="detailsContainer">
        <!-- Tabelas injetadas via JS -->
    </div>

    <!-- Seção: Feedback Geral / Considerações Finais -->
    <div id="feedbackContainer"
        style="display: none; background: white; border: 1px solid #e2e8f0; border-left: 4px solid #8b5cf6; padding: 25px 28px; border-radius: 16px; margin-top: 25px; margin-bottom: 35px; box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.04);">
        <h3 class="section-title" style="margin-top: 0; margin-bottom: 16px;">
            <div class="icon-wrapper" style="color: #8b5cf6; background: #f3e8ff;"><i
                    class="fa-solid fa-quote-left"></i></div>
            Considerações Finais & Observações
        </h3>
        <div id="feedbackText" style="color: #334155; line-height: 1.6; font-size: 0.95rem;">
            <!-- Injetado via JS -->
        </div>
    </div>

    <!-- Seção IA PDI Automático -->
    @if(auth()->user()->perfil_acesso->value !== 'colaborador')
        <div id="aiActionContainer"
            style="display: none; background: linear-gradient(135deg, #fdf4ff, #faf5ff); border: 1px solid #f0abfc; padding: 35px; border-radius: 20px; margin-bottom: 45px; box-shadow: 0 10px 40px -10px rgba(217, 70, 239, 0.15); text-align: center; margin-top: 30px;">
            <h3
                style="color: #86198f; font-weight: 800; font-size: 1.5rem; margin-top: 0; display: flex; align-items: center; justify-content: center; gap: 10px;">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Gerar Plano de Desenvolvimento (IA)
            </h3>
            <p style="color: #a21caf; font-size: 1rem; margin-bottom: 25px;">Notamos que algumas competências estão com nota
                3.0 ou inferior. Você pode gerar um PDI focado nestes gaps automaticamente.</p>
            <button id="btnGerarPdiIa" onclick="gerarPdiIa()" class="btn-generate"
                style="background: linear-gradient(135deg, #d946ef, #c026d3); max-width: 300px; margin: 0 auto; border: none;">
                <i class="fa-solid fa-bolt"></i> Gerar PDI com IA
            </button>
        </div>
    @endif
    
    <!-- Seção: PDI Anexado ao Relatório (Fica visível na tela e no print se houver) -->
    <div id="pdiReportContainer" style="display: none;" class="pdi-print-section">
        <h3 class="section-title" style="margin-top: 40px; border-top: 2px dashed #cbd5e1; padding-top: 40px;">
            <div class="icon-wrapper" style="color: #8b5cf6; background: #f3e8ff;"><i class="fa-solid fa-bullseye"></i></div>
            Plano de Desenvolvimento Individual (PDI)
        </h3>
        <div id="pdiReportContent">
            <!-- Injetado via JS -->
        </div>
    </div>
</div>

<script>
    window.isColaboradorProfile = {{ auth()->user()->perfil_acesso->value === 'colaborador' ? 'true' : 'false' }};

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function updateCircle(elemId, textId, score, colorHex) {
        const elem = document.getElementById(elemId);
        const text = document.getElementById(textId);

        // A nota máxima é 10. Se não tiver nota (NaN), mostra 0 ou traço.
        if (isNaN(score)) {
            text.innerText = '-';
            elem.style.background = `conic-gradient(#f1f5f9 100%, #f1f5f9 0)`;
            return;
        }

        const percent = (score / 10) * 100;

        // Animate the counter
        let start = 0;
        const duration = 1000;
        const startTime = performance.now();

        function animateCounter(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            // Ease out quad
            const easeProgress = progress * (2 - progress);

            const currentScore = (score * easeProgress);
            const currentPercent = (percent * easeProgress);

            text.innerText = currentScore.toFixed(1);
            elem.style.background = `conic-gradient(${colorHex} ${currentPercent}%, #f1f5f9 0)`;

            if (progress < 1) {
                requestAnimationFrame(animateCounter);
            }
        }

        requestAnimationFrame(animateCounter);
    }

    function renderReport(data) {
        document.getElementById('reportContent').style.display = 'block';

        // 0. Atualizar Título e Link PDF
        document.getElementById('reportTitle').innerText = data.colaborador.name;
        const btnPdf = document.getElementById('btnExportPdf');
        if (btnPdf) {
            btnPdf.href = `{{ route('relatorios.exportar.pdf') }}?avaliacao_id=${data.avaliacao.id}&colaborador_id=${data.colaborador.id}`;
        }

        // Dados da Empresa Cliente
        const empresaNomeElem = document.getElementById('reportEmpresaNome');
        const empresaCnpjElem = document.getElementById('reportEmpresaCnpj');
        const empresaLogoElem = document.getElementById('reportEmpresaLogo');
        const empresaIconElem = document.getElementById('reportEmpresaIcon');

        if (empresaNomeElem) empresaNomeElem.innerText = data.empresa ? data.empresa.nome : 'Empresa Avaliadora';
        if (empresaCnpjElem) {
            empresaCnpjElem.innerText = (data.empresa && data.empresa.cnpj) ? `CNPJ: ${data.empresa.cnpj}` : '';
        }
        if (empresaLogoElem && empresaIconElem) {
            if (data.empresa && data.empresa.logo_url) {
                empresaLogoElem.src = data.empresa.logo_url;
                empresaLogoElem.style.display = 'block';
                empresaIconElem.style.display = 'none';
            } else {
                empresaLogoElem.style.display = 'none';
                empresaIconElem.style.display = 'flex';
                const inicial = (data.empresa && data.empresa.nome) ? data.empresa.nome.charAt(0).toUpperCase() : 'E';
                empresaIconElem.innerHTML = `<span style="font-weight: 800; font-size: 1.3rem;">${inicial}</span>`;
            }
        }

        // Campanha, Cargo e Setor do Colaborador
        const campanhaTituloElem = document.getElementById('reportCampanhaTitulo');
        if (campanhaTituloElem) campanhaTituloElem.innerText = data.avaliacao ? data.avaliacao.titulo : '—';

        const colabCargoElem = document.getElementById('reportColabCargo');
        if (colabCargoElem) colabCargoElem.innerText = (data.colaborador.cargo && data.colaborador.cargo.nome) ? data.colaborador.cargo.nome : 'Não informado';

        const colabSetorElem = document.getElementById('reportColabSetor');
        if (colabSetorElem) colabSetorElem.innerText = (data.colaborador.departamento && data.colaborador.departamento.nome) ? data.colaborador.departamento.nome : 'Geral';

        // 1. Médias Comparativas
        if (document.getElementById('txtMediaInd')) document.getElementById('txtMediaInd').innerText = data.charts.individual.toFixed(1);
        if (document.getElementById('txtMediaCargo')) document.getElementById('txtMediaCargo').innerText = data.charts.cargo.toFixed(1);
        if (document.getElementById('txtMediaSetor')) document.getElementById('txtMediaSetor').innerText = data.charts.setor.toFixed(1);
        if (document.getElementById('txtMediaEmpresa')) document.getElementById('txtMediaEmpresa').innerText = data.charts.empresa.toFixed(1);

        // 2. Scores (Circles)
        const sAuto = parseFloat(data.scores.colab.media);
        const sGestor = parseFloat(data.scores.aval.media);
        const sFinal = parseFloat(data.scores.med.media);

        const is90 = data.avaliacao.grau == 90;
        const is360 = data.avaliacao.grau == 360;

        if (is90 && window.isColaboradorProfile) {
            document.getElementById('cardAuto').style.display = 'none';
        } else {
            document.getElementById('cardAuto').style.display = 'block';
        }

        if (is360) {
            document.getElementById('lblAvaliadoresCard').innerText = 'Média Pares';
            document.getElementById('subAvaliadoresCard').innerText = 'Visão 360° (Líderes, pares e/ou subordinados)';
        } else {
            document.getElementById('lblAvaliadoresCard').innerText = 'Avaliadores';
            document.getElementById('subAvaliadoresCard').innerText = 'Média das avaliações recebidas';
        }

        // Injeta Variáveis de Cores da Empresa no Relatório
        const corPrimaria = (data.empresa && data.empresa.cor_primaria) ? data.empresa.cor_primaria : '#4f46e5';
        const corSecundaria = (data.empresa && data.empresa.cor_secundaria) ? data.empresa.cor_secundaria : '#7c3aed';
        const corTerciaria = (data.empresa && data.empresa.cor_terciaria) ? data.empresa.cor_terciaria : '#1e1b4b';

        const reportElem = document.getElementById('reportContent');
        reportElem.style.setProperty('--empresa-cor-primaria', corPrimaria);
        reportElem.style.setProperty('--empresa-cor-secundaria', corSecundaria);
        reportElem.style.setProperty('--empresa-cor-terciaria', corTerciaria);

        // Atualiza gradiente da Média do Indivíduo para refletir as cores da empresa
        const txtMediaInd = document.getElementById('txtMediaInd');
        if (txtMediaInd) {
            txtMediaInd.style.background = `linear-gradient(135deg, ${corPrimaria}, ${corSecundaria})`;
            txtMediaInd.style.webkitBackgroundClip = 'text';
            txtMediaInd.style.webkitTextFillColor = 'transparent';
        }

        // Cores do novo sistema com base na identidade visual da empresa
        const cardAuto = document.getElementById('cardAuto');
        if (cardAuto) cardAuto.style.setProperty('--card-color', corSecundaria);
        updateCircle('circleAuto', 'scoreAuto', sAuto, corSecundaria);

        const cardAvaliadores = document.querySelector('#lblAvaliadoresCard')?.closest('.score-card');
        if (cardAvaliadores) cardAvaliadores.style.setProperty('--card-color', corPrimaria);
        updateCircle('circleGestor', 'scoreGestor', sGestor, corPrimaria);

        if (isNaN(sFinal)) {
            document.getElementById('cardMediacao').style.display = 'none';
        } else {
            document.getElementById('cardMediacao').style.display = 'block';
            updateCircle('circleFinal', 'scoreFinal', sFinal, '#ec4899');
        }

        // Cálculo da Média Final Consolidada
        let sumAverages = 0;
        let countAverages = 0;
        if (!isNaN(sAuto) && sAuto > 0) { sumAverages += sAuto; countAverages++; }
        if (!isNaN(sGestor) && sGestor > 0) { sumAverages += sGestor; countAverages++; }
        if (!isNaN(sFinal) && sFinal > 0) { sumAverages += sFinal; countAverages++; }
        
        const mFinalCalculada = countAverages > 0 ? (sumAverages / countAverages) : 0;

        // Card Média Final: só para gestores/masters, não para colaborador
        const cardMediaFinal = document.getElementById('cardMediaFinal');
        if (window.isColaboradorProfile) {
            cardMediaFinal.style.display = 'none';
        } else {
            cardMediaFinal.style.display = 'block';
            updateCircle('circleMediaConsolidada', 'scoreMediaConsolidada', mFinalCalculada, '#10b981');
        }

        // 3. Detalhamento (Tabelas)
        const container = document.getElementById('detailsContainer');
        container.innerHTML = '';

        // Box do RH (Breakdown de Avaliadores) - Mostrado para RH/Gestor se não for o próprio relatório
        if (!data.is_own_report && data.avaliadores_breakdown && data.avaliadores_breakdown.length > 0) {
            const jsonStr = JSON.stringify(data.avaliadores_breakdown).replace(/"/g, '&quot;').replace(/'/g, "\\'");
            let rhHtml = `
                <div style="background: #ffffff; border-radius: 16px; padding: 20px; margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); border: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #4f46e5, #3b82f6); color: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);">
                            <i class="fa-solid fa-users-viewfinder"></i>
                        </div> 
                        <h3 style="margin: 0; font-size: 1.1rem; color: #1e293b; font-weight: 800; letter-spacing: -0.3px;">Média Final por Avaliador <span style="font-size: 0.75rem; font-weight: 600; color: #64748b; background: #f1f5f9; padding: 3px 8px; border-radius: 20px; margin-left: 8px;">Visão RH/Gestor</span></h3>
                    </div>
                    <div>
                        <button onclick="window.abrirModalNotas('Média Geral dos Avaliadores', '${jsonStr}')" style="background: #eff6ff; color: #1d4ed8; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(29, 78, 216, 0.1);">
                            <i class="fa-solid fa-list-ul"></i> Ver Lista (${data.avaliadores_breakdown.length})
                        </button>
                    </div>
                </div>
            `;
            container.innerHTML += rhHtml;
        }

        let competencias = {};

        if (data.scores.colab && data.scores.colab.detalhes) {
            data.scores.colab.detalhes.forEach(d => {
                let comp = d.competencia || 'Geral';
                if (!competencias[comp]) competencias[comp] = { peso: d.peso || 1, skills: {}, notasColab: [], notasAval: [] };
                if (!competencias[comp].skills[d.pergunta]) competencias[comp].skills[d.pergunta] = { colab: null, aval: null };

                competencias[comp].skills[d.pergunta].colab = d;
                if (!isNaN(parseFloat(d.nota))) {
                    competencias[comp].notasColab.push(parseFloat(d.nota));
                }
            });
        }

        if (data.scores.aval && data.scores.aval.detalhes) {
            data.scores.aval.detalhes.forEach(d => {
                let comp = d.competencia || 'Geral';
                if (!competencias[comp]) competencias[comp] = { tipo: d.tipo_competencia || 'comportamental', peso: d.peso || 1, skills: {}, notasColab: [], notasAval: [] };
                if (!competencias[comp].skills[d.pergunta]) competencias[comp].skills[d.pergunta] = { colab: null, aval: null };

                competencias[comp].skills[d.pergunta].aval = d;
                if (!isNaN(parseFloat(d.nota))) {
                    competencias[comp].notasAval.push(parseFloat(d.nota));
                }
            });
        }

        for (const [compName, dados] of Object.entries(competencias)) {
            let avgColab = dados.notasColab.length > 0 ? (dados.notasColab.reduce((a, b) => a + b, 0) / dados.notasColab.length).toFixed(1) : '-';
            let avgAval = dados.notasAval.length > 0 ? (dados.notasAval.reduce((a, b) => a + b, 0) / dados.notasAval.length).toFixed(1) : '-';

            // Média Final da competência (Auto + Avaliadores)
            let partsForFinal = [];
            if (avgColab !== '-') partsForFinal.push(parseFloat(avgColab));
            if (avgAval !== '-') partsForFinal.push(parseFloat(avgAval));
            let avgFinalComp = partsForFinal.length > 0 ? (partsForFinal.reduce((a, b) => a + b, 0) / partsForFinal.length).toFixed(1) : '-';

            let isTecnica = (dados.tipo === 'tecnica');
            let iconComp = isTecnica ? 'fa-solid fa-code' : 'fa-solid fa-star';
            let iconBg = isTecnica ? '#e0e7ff' : '#fef3c7';
            let iconColor = isTecnica ? '#4338ca' : '#f59e0b';
            let badgeTipo = isTecnica 
                ? '<span style="background: #e0e7ff; color: #4338ca; font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase; margin-left: 8px;">Técnica</span>'
                : '<span style="background: #fef3c7; color: #b45309; font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase; margin-left: 8px;">Comportamental</span>';

            let badgePeso = `<span style="background: #f1f5f9; color: #475569; font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 9999px; border: 1px solid #e2e8f0; margin-left: 6px;"><i class="fa-solid fa-weight-hanging" style="margin-right: 3px; font-size: 0.65rem;"></i>Peso: ${dados.peso || 1}%</span>`;

            let card = document.createElement('div');
            card.className = "table-card";

            let html = `
                <div class="table-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a; font-weight: 800; display: flex; align-items: center; gap: 6px; min-width: 0; flex: 1 1 auto; flex-wrap: wrap;">
                        <div class="icon-wrapper" style="width: 32px; height: 32px; min-width: 32px; background: ${iconBg}; color: ${iconColor}; font-size: 0.8rem;"><i class="${iconComp}"></i></div> 
                        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${compName}</span>
                        ${badgeTipo}
                        ${badgePeso}
                    </h3>
            `;
            if (is90) {
                html += `
                    <div style="display: flex; flex-wrap: wrap; justify-content: flex-end; align-items: center; gap: 8px; font-size: 0.9rem; flex-shrink: 0; max-width: 100%;">
                        <span style="color: #64748b; font-weight: 600; white-space: nowrap;">Média Avaliador:</span> 
                        <span style="background: #eff6ff; color: #1d4ed8; padding: 4px 10px; border-radius: 8px; font-weight: 800;">${avgAval}</span>
                        ${!window.isColaboradorProfile ? `
                        <span style="width: 1px; height: 18px; background: #e2e8f0; display: inline-block; margin: 0 4px;"></span>
                        <span style="color: #64748b; font-weight: 600; white-space: nowrap;">Média Geral:</span>
                        <span style="background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46; padding: 4px 10px; border-radius: 8px; font-weight: 800;">${avgFinalComp}</span>
                        ` : ''}
                    </div>
                </div>
            `;
            } else {
                html += `
                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px; font-size: 0.85rem; flex-shrink: 0; padding-left: 42px; max-width: 100%;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="color: #64748b; font-weight: 600; white-space: nowrap;">Média Auto:</span> 
                            <span style="background: #f3e8ff; color: #7e22ce; padding: 4px 10px; border-radius: 8px; font-weight: 800;">${avgColab}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="color: #64748b; font-weight: 600; white-space: nowrap;">${is360 ? 'Média Pares:' : 'Média Avaliadores:'}</span> 
                            <span style="background: #eff6ff; color: #1d4ed8; padding: 4px 10px; border-radius: 8px; font-weight: 800;">${avgAval}</span>
                        </div>
                        ${!window.isColaboradorProfile ? `
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="width: 1px; height: 18px; background: #e2e8f0; display: inline-block;"></span>
                            <span style="color: #64748b; font-weight: 600; white-space: nowrap;">Média Geral:</span>
                            <span style="background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46; padding: 4px 10px; border-radius: 8px; font-weight: 800;">${avgFinalComp}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
            }

            // O colaborador agora pode ver o detalhamento por competência
            if (true) {
                if (is90) {
                    html += `
                    <div style="padding: 0; overflow-x: auto;">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th style="width: 60%;">Critério Avaliado</th>
                                    <th style="text-align: center; width: 20%;">${is360 ? 'Média Pares' : 'Avaliador'}</th>
                                    <th style="text-align: center; width: 20%; color: #065f46; background: #f0fdf4;">Média Final</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                } else {
                    html += `
                    <div style="padding: 0; overflow-x: auto;">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th style="width: 55%;">Critério Avaliado</th>
                                    <th style="text-align: center; width: 15%;">Autoavaliação</th>
                                    <th style="text-align: center; width: 15%;">${is360 ? 'Média Pares' : 'Avaliadores'}</th>
                                    <th style="text-align: center; width: 15%; color: #065f46; background: #f0fdf4;">Média Final</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                }

                for (const [skillName, skillData] of Object.entries(dados.skills)) {
                    let nColab = skillData.colab && skillData.colab.nota !== null ? skillData.colab.nota : '-';
                    let nAval = skillData.aval && skillData.aval.nota !== null ? skillData.aval.nota : '-';

                    // Média final por skill
                    let skillFinalParts = [];
                    if (nColab !== '-') skillFinalParts.push(parseFloat(nColab));
                    if (nAval !== '-') skillFinalParts.push(parseFloat(nAval));
                    let skillFinal = skillFinalParts.length > 0
                        ? (skillFinalParts.reduce((a, b) => a + b, 0) / skillFinalParts.length).toFixed(2)
                        : '-';

                    let badgeColab = nColab !== '-' ? `<span class="badge-score" style="background: #f3e8ff; color: #7e22ce;">${nColab}</span>` : `<span style="color: #cbd5e1; font-weight:600;">-</span>`;
                    let badgeAval = nAval !== '-' ? `<span class="badge-score" style="background: #eff6ff; color: #1d4ed8;">${nAval}</span>` : `<span style="color: #cbd5e1; font-weight:600;">-</span>`;
                    let badgeFinal = skillFinal !== '-' ? `<span class="badge-score" style="background: #d1fae5; color: #065f46;">${skillFinal}</span>` : `<span style="color: #cbd5e1; font-weight:600;">-</span>`;

                    let breakdownHtml = '';
                    if (!data.is_own_report && skillData.aval && skillData.aval.breakdown && skillData.aval.breakdown.length > 0) {
                        const jsonStr = JSON.stringify(skillData.aval.breakdown).replace(/"/g, '&quot;');
                        const sName = skillName.replace(/"/g, '&quot;').replace(/'/g, "\\'");
                        breakdownHtml = `
                            <button onclick="window.abrirModalNotas('${sName}', '${jsonStr}')" title="Ver Notas" style="background: transparent; border: none; color: #94a3b8; font-size: 1rem; cursor: pointer; transition: color 0.2s; padding: 0; margin-left: 6px; vertical-align: middle; display: inline;" onmouseover="this.style.color='#3b82f6'" onmouseout="this.style.color='#94a3b8'">
                                <i class="fa-solid fa-list-ul"></i>
                            </button>
                        `;
                    }

                    // Evidências registradas para este critério (auto e avaliadores)
                    let evidenciasBloco = '';
                    let evidenciasItens = [];

                    if (skillData.colab && skillData.colab.evidencia) {
                        evidenciasItens.push(`
                            <div style="font-size: 0.78rem; background: #faf5ff; border: 1px solid #e9d5ff; border-left: 3px solid #a855f7; border-radius: 6px; padding: 4px 8px; margin-top: 4px; color: #581c87; line-height: 1.35;">
                                <strong>Autoavaliação:</strong> ${escapeHtml(skillData.colab.evidencia)}
                            </div>
                        `);
                    }

                    if (skillData.aval && skillData.aval.evidencias && skillData.aval.evidencias.length > 0) {
                        skillData.aval.evidencias.forEach(ev => {
                            evidenciasItens.push(`
                                <div style="font-size: 0.78rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-left: 3px solid #10b981; border-radius: 6px; padding: 4px 8px; margin-top: 4px; color: #064e3b; line-height: 1.35;">
                                    <strong>Evidência do Avaliador${ev.avaliador && ev.avaliador !== 'Avaliador' ? ' (' + escapeHtml(ev.avaliador) + ')' : ''}:</strong> ${escapeHtml(ev.texto)}
                                </div>
                            `);
                        });
                    }

                    if (evidenciasItens.length > 0) {
                        evidenciasBloco = `<div style="margin-top: 6px;">${evidenciasItens.join('')}</div>`;
                    }

                    if (is90) {
                        html += `
                            <tr>
                                <td data-label="Critério Avaliado" style="font-weight: 600; color: #0f172a;">
                                    <div>${skillName}</div>
                                    ${evidenciasBloco}
                                </td>
                                <td data-label="${is360 ? 'Média Pares' : 'Avaliador'}" style="text-align: center;">
                                    <span>${badgeAval}${breakdownHtml}</span>
                                </td>
                                <td data-label="Média Final" style="text-align: center; background: #f0fdf4;">${badgeFinal}</td>
                            </tr>
                        `;
                    } else {
                        html += `
                            <tr>
                                <td data-label="Critério Avaliado" style="font-weight: 600; color: #0f172a;">
                                    <div>${skillName}</div>
                                    ${evidenciasBloco}
                                </td>
                                <td data-label="Autoavaliação" style="text-align: center;">${badgeColab}</td>
                                <td data-label="${is360 ? 'Média Pares' : 'Avaliadores'}" style="text-align: center;">
                                    <span>${badgeAval}${breakdownHtml}</span>
                                </td>
                                <td data-label="Média Final" style="text-align: center; background: #f0fdf4;">${badgeFinal}</td>
                            </tr>
                        `;
                    }
                }

                html += `
                            </tbody>
                        </table>
                    </div>
                `;
            }

            card.innerHTML = html;
            container.appendChild(card);
        }

        // 4. Feedback Geral / Considerações Finais
        const fbContainer = document.getElementById('feedbackContainer');
        const fbText = document.getElementById('feedbackText');

        const obsMaster = data.scores.med && data.scores.med.comentarios ? data.scores.med.comentarios : null;
        const obsAvaliado = data.scores.observacao_avaliado;

        let htmlContent = '';

        if (obsMaster) {
            htmlContent += `
            <div class="obs-card-item" style="border: 1px solid #fbcfe8; border-left: 4px solid #db2777; border-radius: 8px; padding: 12px 16px; margin-bottom: 12px; background: #fdf2f8; break-inside: avoid; page-break-inside: avoid;">
                <div style="font-weight: 800; color: #9d174d; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 6px; display: flex; align-items: center; gap: 7px;">
                    <i class="fa-solid fa-check-double" style="color: #db2777;"></i> Feedback do Gestor / RH
                </div>
                <div style="color: #4a044e; font-size: 0.9rem; line-height: 1.5; white-space: pre-wrap;">${obsMaster}</div>
            </div>`;
        }

        if (obsAvaliado) {
            htmlContent += `
            <div class="obs-card-item" style="border: 1px solid #bfdbfe; border-left: 4px solid #2563eb; border-radius: 8px; padding: 12px 16px; margin-bottom: 12px; background: #eff6ff; break-inside: avoid; page-break-inside: avoid;">
                <div style="font-weight: 800; color: #1e40af; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 6px; display: flex; align-items: center; gap: 7px;">
                    <i class="fa-solid fa-comment-dots" style="color: #2563eb;"></i> Observação do Avaliado
                </div>
                <div style="color: #1e3a8a; font-size: 0.9rem; line-height: 1.5; white-space: pre-wrap;">${obsAvaliado}</div>
            </div>`;
        }

        if (data.scores.observacoes_avaliadores && data.scores.observacoes_avaliadores.length > 0) {
            data.scores.observacoes_avaliadores.forEach(obs => {
                htmlContent += `
                <div class="obs-card-item" style="border: 1px solid #a7f3d0; border-left: 4px solid #059669; border-radius: 8px; padding: 12px 16px; margin-bottom: 12px; background: #ecfdf5; break-inside: avoid; page-break-inside: avoid;">
                    <div style="font-weight: 800; color: #065f46; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 6px; display: flex; align-items: center; gap: 7px;">
                        <i class="fa-solid fa-user-tie" style="color: #059669;"></i> Observação do Avaliador (${obs.nome})
                    </div>
                    <div style="color: #064e3b; font-size: 0.9rem; line-height: 1.5; white-space: pre-wrap;">${obs.texto}</div>
                </div>`;
            });
        }

        if (htmlContent) {
            fbContainer.style.display = 'block';
            fbText.innerHTML = htmlContent;
        } else {
            fbContainer.style.display = 'none';
        }

        // 5. Analisar se exibe o botão da IA (Existem notas <= 3.0?)
        const aiContainer = document.getElementById('aiActionContainer');
        if (aiContainer) {
            let hasLowScores = false;
            let totalNotas = 0;
            for (const [compName, dados] of Object.entries(competencias)) {
                for (const [skillName, skillData] of Object.entries(dados.skills)) {
                    let nAval = skillData.aval && skillData.aval.nota !== null ? parseFloat(skillData.aval.nota) : null;
                    let nColab = skillData.colab && skillData.colab.nota !== null ? parseFloat(skillData.colab.nota) : null;

                    if (nAval !== null || nColab !== null) {
                        totalNotas++;
                    }

                    if ((nAval !== null && nAval <= 3.0) || (nColab !== null && nColab <= 3.0)) {
                        hasLowScores = true;
                    }
                }
            }

            let isComplete = totalNotas > 0;

            const aiContainerDesc = document.getElementById('aiActionDesc');
            
            // Sempre exibe o container de IA
            aiContainer.style.display = 'block';

            if (aiContainerDesc) {
                if (hasLowScores) {
                    aiContainerDesc.innerText = 'Notamos que algumas competências estão com nota 3.0 ou inferior. Você pode gerar um PDI focado nestes gaps automaticamente.';
                } else {
                    aiContainerDesc.innerText = 'Você pode gerar um Plano de Desenvolvimento com a IA para potencializar e aprimorar ainda mais as competências avaliadas.';
                }
            }

            const btnAi = document.getElementById('btnGerarPdiIa');
            if (!isComplete) {
                btnAi.disabled = true;
                btnAi.style.opacity = '0.5';
                btnAi.style.cursor = 'not-allowed';
                btnAi.title = 'Aguarde a finalização de todas as respostas (Autoavaliação e Avaliadores) para gerar o PDI com Inteligência Artificial.';
            } else {
                btnAi.disabled = false;
                btnAi.style.opacity = '1';
                btnAi.style.cursor = 'pointer';
                btnAi.title = '';
            }

            // Salvar dados globais para envio à IA
            window.dadosParaIa = {
                avaliacao_id: data.avaliacao.id,
                colaborador_id: data.colaborador.id,
                colaborador_nome: data.colaborador.name,
                competencias: competencias
            };
        }

        // 6. Renderizar Histórico de Avaliações (apenas Gestor/Master/Admin)
        const histContainer = document.getElementById('historicoContainer');
        const histTableWrapper = document.getElementById('historicoTableWrapper');
        if (histContainer && histTableWrapper) {
            if (data.historico && data.historico.length > 0) {
                histContainer.style.display = 'block';
                let histHtml = `
                    <table class="table-custom" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                <th style="padding: 12px 16px; color: #475569; font-weight: 700; font-size: 0.85rem; text-transform: uppercase;">Data / Início</th>
                                <th style="padding: 12px 16px; color: #475569; font-weight: 700; font-size: 0.85rem; text-transform: uppercase;">Campanha</th>
                                <th style="text-align: center; padding: 12px 16px; color: #7c3aed; font-weight: 700; font-size: 0.85rem; text-transform: uppercase;">Autoavaliação</th>
                                <th style="text-align: center; padding: 12px 16px; color: #2563eb; font-weight: 700; font-size: 0.85rem; text-transform: uppercase;">Avaliadores</th>
                                <th style="text-align: center; padding: 12px 16px; color: #059669; font-weight: 700; font-size: 0.85rem; text-transform: uppercase;">Média Geral</th>
                                <th style="text-align: center; padding: 12px 16px; color: #475569; font-weight: 700; font-size: 0.85rem; text-transform: uppercase;">Status</th>
                                <th style="text-align: center; padding: 12px 16px; color: #475569; font-weight: 700; font-size: 0.85rem; text-transform: uppercase;">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                data.historico.forEach(h => {
                    const isAtual = h.is_atual;
                    const rowBg = isAtual ? 'background: #f0fdf4;' : '';
                    const tagAtual = isAtual ? '<span style="background: #dcfce7; color: #15803d; font-size: 0.72rem; padding: 2px 8px; border-radius: 6px; font-weight: 800; margin-left: 6px;">Em Exibição</span>' : '';
                    
                    histHtml += `
                        <tr style="border-bottom: 1px solid #f1f5f9; ${rowBg}">
                            <td data-label="Data" style="font-weight: 600; color: #334155; padding: 14px 16px;">${h.data_inicio}</td>
                            <td data-label="Campanha" style="font-weight: 700; color: #0f172a; padding: 14px 16px;">
                                ${h.titulo} ${tagAtual}
                            </td>
                            <td data-label="Auto" style="text-align: center; padding: 14px 16px;">
                                ${h.media_auto !== null ? `<span class="badge-score" style="background: #f3e8ff; color: #7e22ce;">${h.media_auto}</span>` : '<span style="color:#cbd5e1; font-weight:600;">—</span>'}
                            </td>
                            <td data-label="Avaliadores" style="text-align: center; padding: 14px 16px;">
                                ${h.media_avaliadores !== null ? `<span class="badge-score" style="background: #eff6ff; color: #1d4ed8;">${h.media_avaliadores}</span>` : '<span style="color:#cbd5e1; font-weight:600;">—</span>'}
                            </td>
                            <td data-label="Média Geral" style="text-align: center; padding: 14px 16px;">
                                <span class="badge-score" style="background: #d1fae5; color: #065f46; font-weight: 800;">${h.media_geral}</span>
                            </td>
                            <td data-label="Status" style="text-align: center; padding: 14px 16px;">
                                <span style="font-size: 0.8rem; font-weight: 600; color: #475569; background: #f1f5f9; padding: 3px 10px; border-radius: 12px;">${h.status}</span>
                            </td>
                            <td data-label="Ação" style="text-align: center; padding: 14px 16px;">
                                ${!isAtual ? `
                                    <button onclick="carregarHistoricoDireto(${h.avaliacao_id}, ${data.colaborador.id})" class="btn" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 6px 14px; font-size: 0.82rem; border-radius: 8px; cursor: pointer; font-weight: 700; transition: all 0.2s;">
                                        <i class="fa-solid fa-eye"></i> Abrir Relatório
                                    </button>
                                ` : '<span style="font-size: 0.82rem; font-weight: 700; color: #16a34a;"><i class="fa-solid fa-circle-check"></i> Aberto</span>'}
                            </td>
                        </tr>
                    `;
                });
                histHtml += `</tbody></table>`;
                histTableWrapper.innerHTML = histHtml;
            } else {
                histContainer.style.display = 'none';
            }
        }

        // 7. Renderizar PDI Recente, se existir
        const pdiContainer = document.getElementById('pdiReportContainer');
        const pdiContent = document.getElementById('pdiReportContent');
        if (data.pdi_recente) {
            pdiContainer.style.display = 'block';
            let acoesHtml = '';
            
            if (data.pdi_recente.acoes && data.pdi_recente.acoes.length > 0) {
                acoesHtml = '<ul style="list-style: none; padding: 0; margin-top: 20px;">';
                data.pdi_recente.acoes.forEach(a => {
                    acoesHtml += `
                        <li style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; margin-bottom: 10px; display: flex; align-items: flex-start; gap: 15px;">
                            <div style="background: ${a.concluida ? '#dcfce7' : '#f1f5f9'}; color: ${a.concluida ? '#16a34a' : '#64748b'}; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px;">
                                <i class="fa-solid ${a.concluida ? 'fa-check' : 'fa-spinner'}"></i>
                            </div>
                            <div>
                                <h4 style="margin: 0 0 5px 0; color: #0f172a; font-size: 1.05rem;">${a.titulo}</h4>
                                ${a.descricao ? `<p style="margin: 0; color: #475569; font-size: 0.95rem;">${a.descricao}</p>` : ''}
                            </div>
                        </li>
                    `;
                });
                acoesHtml += '</ul>';
            } else {
                acoesHtml = '<p style="color: #64748b; font-style: italic;">Nenhuma ação cadastrada neste PDI ainda.</p>';
            }

            pdiContent.innerHTML = `
                <div style="background: #f8fafc; border-radius: 16px; padding: 25px; border: 1px solid #e2e8f0;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                        <h4 style="margin: 0; color: #0f172a; font-size: 1.3rem; font-weight: 800;">${data.pdi_recente.titulo}</h4>
                        <span style="background: #e0e7ff; color: #4338ca; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700;">Status: ${data.pdi_recente.status}</span>
                    </div>
                    ${data.pdi_recente.descricao ? `<p style="margin: 0 0 20px 0; color: #334155; line-height: 1.6;">${data.pdi_recente.descricao}</p>` : ''}
                    
                    <h5 style="margin: 0; color: #0f172a; font-size: 1.1rem; font-weight: 700; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">Ações Propostas</h5>
                    ${acoesHtml}
                </div>
            `;
        } else {
            pdiContainer.style.display = 'none';
        }
    }

    window.carregarHistoricoDireto = async function(avaliacaoId, colaboradorId) {
        const selectAv = document.getElementById('avaliacao_id');
        if (selectAv) selectAv.value = avaliacaoId;

        document.getElementById('reportContent').style.display = 'none';
        const loadingElem = document.getElementById('loading');
        if (loadingElem) loadingElem.style.display = 'block';

        try {
            const url = `{{ route('relatorios.dados') }}?avaliacao_id=${avaliacaoId}&colaborador_id=${colaboradorId}`;
            const res = await fetch(url);
            if (!res.ok) throw new Error('Erro ao buscar avaliação do histórico');
            const data = await res.json();
            renderReport(data);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (e) {
            console.error(e);
            alert(e.message);
        } finally {
            if (loadingElem) loadingElem.style.display = 'none';
        }
    };

    function gerarPdiIa() {
        if (!window.dadosParaIa || !window.dadosParaIa.colaborador_id) return;

        const btnElement = document.getElementById('btnGerarPdiIa');
        const colaboradorId = window.dadosParaIa.colaborador_id;
        const colaboradorNome = window.dadosParaIa.colaborador_nome;

        window.showConfirm(
            'Deseja gerar um PDI com Inteligência Artificial? Isso descontará 1 crédito do seu plano.',
            async function () {
                const originalText = btnElement.innerHTML;
                btnElement.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> A Inteligência Artificial está analisando...';
                btnElement.disabled = true;

                const compData = window.dadosParaIa.competencias;

                try {
                    const res = await fetch(`{{ route('avaliacoes.gerar_pdi_ia') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            avaliacao_id: window.dadosParaIa.avaliacao_id,
                            colaborador_id: colaboradorId,
                            colaborador_nome: colaboradorNome,
                            competencias: compData
                        })
                    });

                    const data = await res.json();
                    if (data.success && data.dados) {
                        abrirModalPdiIa(data.dados, colaboradorId, `Plano de Desenvolvimento: ${colaboradorNome}`);
                    } else if (data.success && data.queued) {
                        window.showAlert(data.message, 'Processando...', 'success');
                    } else {
                        throw new Error(data.error || 'Erro desconhecido');
                    }
                } catch (e) {
                    console.error(e);
                    window.showAlert('Falha ao gerar PDI com IA: ' + e.message, 'Erro', 'danger');
                } finally {
                    btnElement.innerHTML = originalText;
                    btnElement.disabled = false;
                }
            },
            'Gerar PDI com IA',
            'info'
        );
    }
</script>

<!-- Modal Detalhe Notas Avaliadores -->
<div id="modalNotasAvaliadores" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(3px);">
    <div style="background: white; border-radius: 12px; width: 90%; max-width: 400px; padding: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); max-height: 80vh; display: flex; flex-direction: column;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
            <h4 style="margin: 0; color: #0f172a; font-weight: 800; font-size: 1.1rem;"><i class="fa-solid fa-list-ul" style="color: #4338ca; margin-right: 5px;"></i> Notas da Competência</h4>
            <button onclick="document.getElementById('modalNotasAvaliadores').style.display = 'none'" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #64748b; padding: 0;">&times;</button>
        </div>
        <div style="margin-bottom: 15px;">
            <div id="modalNotasCompetenciaNome" style="font-size: 0.95rem; color: #334155; font-weight: 600; text-align: center;"></div>
        </div>
        <div id="modalNotasLista" style="overflow-y: auto; flex: 1; padding-right: 5px;">
            <!-- Lista renderizada via JS -->
        </div>
        <div style="margin-top: 15px; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 15px;">
            <button onclick="document.getElementById('modalNotasAvaliadores').style.display = 'none'" style="background: #e2e8f0; color: #334155; border: none; padding: 8px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s;">Fechar</button>
        </div>
    </div>
</div>

<script>
    window.abrirModalNotas = function(skillName, breakdownJson) {
        const listContainer = document.getElementById('modalNotasLista');
        const compTitle = document.getElementById('modalNotasCompetenciaNome');
        
        compTitle.innerText = skillName;
        listContainer.innerHTML = '';
        
        try {
            const breakdown = JSON.parse(breakdownJson);
            if (breakdown && breakdown.length > 0) {
                breakdown.forEach(b => {
                    let notaVal = b.nota !== undefined ? b.nota : b.media;
                    let notaNum = parseFloat(notaVal).toFixed(1);
                    listContainer.innerHTML += `
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #f1f5f9;">
                            <div style="display: flex; align-items: center; gap: 10px; color: #334155; font-size: 0.9rem;">
                                <div style="width: 32px; height: 32px; background: #e0e7ff; color: #4338ca; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <span style="font-weight: 600;">${b.nome}</span>
                            </div>
                            <span style="background: #eff6ff; color: #1d4ed8; padding: 4px 10px; border-radius: 8px; font-weight: 800;">${notaNum}</span>
                        </div>
                    `;
                });
            } else {
                listContainer.innerHTML = '<div style="text-align: center; color: #64748b; padding: 20px;">Nenhuma nota encontrada.</div>';
            }
        } catch (e) {
            console.error("Erro ao ler as notas", e);
        }
        
        document.getElementById('modalNotasAvaliadores').style.display = 'flex';
    };

    window.imprimirRelatorioPdf = function() {
        const originalTitle = document.title;
        // Pega o nome do colaborador a partir do card principal
        const colabNameElement = document.querySelector('.report-colab-name') || document.querySelector('h2.mb-1');
        let colabName = colabNameElement ? colabNameElement.innerText.trim() : 'Relatorio';
        
        // Remove caracteres estranhos
        colabName = colabName.replace(/[^a-zA-Z0-9 -]/g, '').replace(/ /g, '_');
        
        document.title = `Relatorio_Avaliacao_${colabName}`;
        window.print();
        
        // Retorna o titulo original depois da janela de print abrir
        setTimeout(() => {
            document.title = originalTitle;
        }, 1000);
    };
</script>

{{-- Inclusão do Modal de IA --}}
@include('pdis.partials.modal_ai_create')