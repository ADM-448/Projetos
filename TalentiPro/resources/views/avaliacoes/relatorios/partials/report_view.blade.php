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
        min-width: 250px;
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

    @media (max-width: 768px) {

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
        }

        .table-custom td::before {
            content: attr(data-label);
            font-weight: 700;
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
            text-align: left;
        }

        .table-custom td:first-child {
            font-size: 1.05rem;
            color: #0f172a;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 12px;
            margin-bottom: 8px;
            justify-content: flex-start;
        }

        .table-custom td:first-child::before {
            display: none;
        }

        .table-custom td:last-child {
            border-top: 1px dashed #e2e8f0;
            margin-top: 8px;
            padding-top: 12px;
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
</style>

<div id="reportContent" style="display: none; margin-top: 10px; animation: fadeIn 0.6s cubic-bezier(0.4, 0, 0.2, 1);">

    <div
        style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: space-between; align-items: center; margin-bottom: 35px; background: white; padding: 25px 20px; border-radius: 20px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); border: 1px solid #f1f5f9;">
        <div>
            <p
                style="text-transform: uppercase; letter-spacing: 1px; color: #64748b; font-size: 0.8rem; font-weight: 700; margin: 0 0 5px 0;">
                Relatório Consolidado</p>
            <h2
                style="color: #0f172a; margin: 0; font-size: 2rem; font-weight: 800; display: flex; align-items: center; gap: 12px;">
                <span id="reportTitle">Resultados da Avaliação</span>
            </h2>
        </div>
        <a href="#" id="btnExportPdf" target="_blank" class="btn"
            style="background: linear-gradient(135deg, #ef4444, #b91c1c); color: white; border: none; padding: 14px 24px; border-radius: 12px; font-weight: 700; text-decoration: none; box-shadow: 0 10px 20px -5px rgba(239, 68, 68, 0.4); transition: all 0.3s; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-file-pdf"></i> Exportar PDF
        </a>
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
            <div style="display: flex; gap: 24px; flex-wrap: wrap; justify-content: center;">
                <div class="comparative-box" style="max-width: 250px;">
                    <p
                        style="margin: 0 0 10px 0; color: #64748b; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
                        Indivíduo</p>
                    <div style="font-size: 2.5rem; font-weight: 800; background: linear-gradient(135deg, #8b5cf6, #d946ef); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"
                        id="txtMediaInd">0.0</div>
                </div>

                @if(in_array(auth()->user()->perfil_acesso->value, ['master', 'admin']))
                    <div class="comparative-box" style="max-width: 250px;">
                        <p
                            style="margin: 0 0 10px 0; color: #64748b; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
                            Cargo</p>
                        <div style="font-size: 2.5rem; font-weight: 800; color: #1e293b;" id="txtMediaCargo">0.0</div>
                    </div>
                @endif

                @if(in_array(auth()->user()->perfil_acesso->value, ['master', 'admin', 'gestor']))
                    <div class="comparative-box" style="max-width: 250px;">
                        <p
                            style="margin: 0 0 10px 0; color: #64748b; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
                            Setor</p>
                        <div style="font-size: 2.5rem; font-weight: 800; color: #1e293b;" id="txtMediaSetor">0.0</div>
                    </div>
                @endif

                @if(in_array(auth()->user()->perfil_acesso->value, ['master', 'admin']))
                    <div class="comparative-box" style="max-width: 250px;">
                        <p
                            style="margin: 0 0 10px 0; color: #64748b; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
                            Empresa</p>
                        <div style="font-size: 2.5rem; font-weight: 800; color: #1e293b;" id="txtMediaEmpresa">0.0</div>
                    </div>
                @endif
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
    </div>

    <!-- Seção: Feedback Geral / Fechamento -->
    <div id="feedbackContainer"
        style="display: none; background: white; border: 1px solid #e2e8f0; padding: 35px; border-radius: 20px; margin-bottom: 45px; box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.05); position: relative; overflow: hidden;">
        <div
            style="position: absolute; top: 0; left: 0; bottom: 0; width: 6px; background: linear-gradient(180deg, #ec4899, #8b5cf6);">
        </div>
        <h3 class="section-title">
            <div class="icon-wrapper" style="color: #ec4899; background: #fdf2f8;"><i
                    class="fa-solid fa-quote-left"></i></div>
            Considerações Finais
        </h3>
        <div id="feedbackText" style="color: #334155; line-height: 1.8; font-size: 1.05rem; padding-left: 10px;">
            <!-- Injetado via JS -->
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
</div>

<script>
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
        document.getElementById('btnExportPdf').href = `{{ route('relatorios.exportar.pdf') }}?avaliacao_id=${data.avaliacao.id}&colaborador_id=${data.colaborador.id}`;

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

        if (is90) {
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

        // Cores do novo sistema
        updateCircle('circleAuto', 'scoreAuto', sAuto, '#8b5cf6');
        updateCircle('circleGestor', 'scoreGestor', sGestor, '#3b82f6');

        if (isNaN(sFinal)) {
            document.getElementById('cardMediacao').style.display = 'none';
        } else {
            document.getElementById('cardMediacao').style.display = 'block';
            updateCircle('circleFinal', 'scoreFinal', sFinal, '#ec4899');
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
                if (!competencias[comp]) competencias[comp] = { skills: {}, notasColab: [], notasAval: [] };
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
                if (!competencias[comp]) competencias[comp] = { skills: {}, notasColab: [], notasAval: [] };
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

            let card = document.createElement('div');
            card.className = "table-card";

            let html = `
                <div class="table-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a; font-weight: 800; display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1 1 auto;">
                        <div class="icon-wrapper" style="width: 32px; height: 32px; min-width: 32px; background: #fef3c7; color: #f59e0b; font-size: 0.8rem;"><i class="fa-solid fa-star"></i></div> 
                        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${compName}</span>
                    </h3>
            `;
            if (is90) {
                html += `
                    <div style="display: flex; flex-wrap: wrap; justify-content: flex-end; align-items: center; gap: 8px; font-size: 0.9rem; flex-shrink: 0; max-width: 100%;">
                        <span style="color: #64748b; font-weight: 600; white-space: nowrap;">Média Avaliador:</span> 
                        <span style="background: #eff6ff; color: #1d4ed8; padding: 4px 10px; border-radius: 8px; font-weight: 800;">${avgAval}</span>
                    </div>
                </div>
                <div style="padding: 0; overflow-x: auto;">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th style="width: 70%;">Critério Avaliado</th>
                                <th style="text-align: center; width: 30%;">${is360 ? 'Média Pares' : 'Avaliador'}</th>
                            </tr>
                        </thead>
                        <tbody>
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
                    </div>
                </div>
                <div style="padding: 0; overflow-x: auto;">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th style="width: 70%;">Critério Avaliado</th>
                                <th style="text-align: center; width: 15%;">Autoavaliação</th>
                                <th style="text-align: center; width: 15%;">${is360 ? 'Média Pares' : 'Avaliadores'}</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
            }

            for (const [skillName, skillData] of Object.entries(dados.skills)) {
                let nColab = skillData.colab && skillData.colab.nota !== null ? skillData.colab.nota : '-';
                let nAval = skillData.aval && skillData.aval.nota !== null ? skillData.aval.nota : '-';

                let badgeColab = nColab !== '-' ? `<span class="badge-score" style="background: #f3e8ff; color: #7e22ce;">${nColab}</span>` : `<span style="color: #cbd5e1; font-weight:600;">-</span>`;
                let badgeAval = nAval !== '-' ? `<span class="badge-score" style="background: #eff6ff; color: #1d4ed8;">${nAval}</span>` : `<span style="color: #cbd5e1; font-weight:600;">-</span>`;



                if (is90) {
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

                    html += `
                        <tr>
                            <td data-label="Critério Avaliado" style="font-weight: 600; color: #0f172a;">${skillName}</td>
                            <td data-label="${is360 ? 'Média Pares' : 'Avaliador'}" style="text-align: center;">
                                <span>${badgeAval}${breakdownHtml}</span>
                            </td>
                        </tr>
                    `;
                } else {
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

                    html += `
                        <tr>
                            <td data-label="Critério Avaliado" style="font-weight: 600; color: #0f172a;">${skillName}</td>
                            <td data-label="Autoavaliação" style="text-align: center;">${badgeColab}</td>
                            <td data-label="${is360 ? 'Média Pares' : 'Avaliadores'}" style="text-align: center;">
                                <span>${badgeAval}${breakdownHtml}</span>
                            </td>
                        </tr>
                    `;
                }
            }

            html += `
                        </tbody>
                    </table>
                </div>
            `;

            card.innerHTML = html;
            container.appendChild(card);
        }

        // 4. Feedback Geral
        const fbContainer = document.getElementById('feedbackContainer');
        const fbText = document.getElementById('feedbackText');

        const obsMaster = data.scores.med.comentarios;
        const obsAvaliado = data.scores.observacao_avaliado;

        let htmlContent = '';

        if (obsMaster) {
            htmlContent += `<div style="margin-bottom: 25px;">
                <span style="font-weight: 800; color: #be185d; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; display: flex; align-items: center; gap: 8px;"><i class="fa-solid fa-check-double"></i> Feedback do Gestor / RH</span>
                <p style="margin: 10px 0 0 0; background: #fdf2f8; padding: 15px 20px; border-radius: 12px; border: 1px solid #fbcfe8;">${obsMaster}</p>
            </div>`;
        }

        if (obsAvaliado) {
            htmlContent += `<div>
                <span style="font-weight: 800; color: #1d4ed8; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; display: flex; align-items: center; gap: 8px;"><i class="fa-solid fa-comment-dots"></i> Auto-reflexão Geral</span>
                <p style="margin: 10px 0 0 0; background: #eff6ff; padding: 15px 20px; border-radius: 12px; border: 1px solid #bfdbfe;">${obsAvaliado}</p>
            </div>`;
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
            let isComplete = true; // Flag para verificar se todos responderam

            for (const [compName, dados] of Object.entries(competencias)) {
                for (const [skillName, skillData] of Object.entries(dados.skills)) {
                    let nAval = skillData.aval && skillData.aval.nota !== null ? parseFloat(skillData.aval.nota) : null;
                    let nColab = skillData.colab && skillData.colab.nota !== null ? parseFloat(skillData.colab.nota) : null;

                    if (nAval !== null && nAval <= 3.0) {
                        hasLowScores = true;
                    }

                    // Se não tem nota do avaliador ou do avaliado (colab), não está completo
                    if (nAval === null || nColab === null) {
                        isComplete = false;
                    }
                }
            }
            if (hasLowScores) {
                aiContainer.style.display = 'block';

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
            } else {
                aiContainer.style.display = 'none';
            }
        }
    }

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
                    if (data.success && data.queued) {
                        window.showAlert(data.message, 'Processando...', 'success');
                    } else if (data.success) {
                        // Fallback caso a API respondesse direto (não deveria mais acontecer)
                        abrirModalPdiIa(data.dados, colaboradorId, `Plano de Desenvolvimento: ${colaboradorNome}`);
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
</script>

{{-- Inclusão do Modal de IA --}}
@include('pdis.partials.modal_ai_create')