@extends(in_array(auth()->user()->perfil_acesso, ['admin', 'master']) ? 'layouts.admin' : (auth()->user()->perfil_acesso === 'gestor' ? 'layouts.gestor' : 'layouts.colaborador'))

@section('title', 'Meus Relatórios')



@section('content')
<div class="page-content">
    
    <div class="report-header">
        <h1 class="page-title">Meus Resultados</h1>
        

    </div>

    <div class="report-filter" style="display: flex; flex-wrap: wrap; gap: 10px;">
        <select class="report-select" id="selAvaliacao" style="flex: 1; min-width: 200px;">
            @forelse($avaliacoes as $av)
                <option value="{{ $av->id }}">{{ $av->titulo }} ({{ \Carbon\Carbon::parse($av->data_inicio)->format('m/Y') }})</option>
            @empty
                <option value="">Nenhuma avaliação disponível</option>
            @endforelse
        </select>
        <button class="btn-view" onclick="carregarRelatorio()" style="padding: 10px 20px;">Filtrar</button>
    </div>

    {{-- Content Area --}}
    <div id="reportContent" style="display:none;">
        
        <div class="charts-grid">
            <div class="chart-card">
                <h4>Minha Autoavaliação</h4>
                <div class="chart-circle" id="circleAuto">
                    <div class="chart-inner" id="scoreAuto">-</div>
                </div>
            </div>
            <div class="chart-card">
                <h4>Avaliação (Gestor/Pares)</h4>
                <div class="chart-circle" id="circleGestor">
                    <div class="chart-inner" id="scoreGestor">-</div>
                </div>
            </div>
            <div class="chart-card">
                <h4>Consenso Final</h4>
                <div class="chart-circle" id="circleFinal">
                    <div class="chart-inner" id="scoreFinal">-</div>
                </div>
            </div>
        </div>

        <div class="details-card">
            <h3 class="details-title">Detalhamento por Competência</h3>
            <div id="detailsContainer">
                <!-- JS Insert Rows Here -->
            </div>

            <div style="margin-top: 20px; background: #f9f9f9; padding: 15px; border-radius: 5px; display:none;" id="feedbackContainer">
                <strong>Feedback / Comentários Gerais:</strong>
                <p id="feedbackText" style="color: #666; font-size: 14px; margin-top: 5px;">-</p>
            </div>
        </div>

    </div>
    
    <div id="noDataMsg" style="text-align: center; color: #777; margin-top: 50px;">
        Selecione uma avaliação e clique em Filtrar.
    </div>



</div>

<div class="loading-overlay" id="loading">
    <i class="fa-solid fa-spinner fa-spin fa-2x" style="color: #3b5998;"></i>
</div>
@endsection

@push('scripts')
<style>
    @media (max-width: 768px) {
        .charts-grid {
            grid-template-columns: 1fr !important;
            gap: 15px !important;
        }
        .mobile-cards-table, .mobile-cards-table thead, .mobile-cards-table tbody, .mobile-cards-table th, .mobile-cards-table td, .mobile-cards-table tr {
            display: block !important;
            width: 100% !important;
            min-width: 0 !important;
        }
        .mobile-cards-table thead { display: none !important; }
        .mobile-cards-table tr {
            margin-bottom: 20px !important;
            border: 1px solid #ccc !important;
            border-radius: 8px !important;
            background: #fff !important;
            padding: 15px !important;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05) !important;
            overflow: hidden !important;
        }
        .mobile-cards-table td {
            display: flex !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            text-align: left !important;
            padding: 10px 0 !important;
            border: none !important;
            background-color: transparent !important;
        }
        .mobile-cards-table td::before {
            content: attr(data-label);
            font-weight: 700;
            font-size: 13px;
            color: #3b5998;
            margin-bottom: 5px;
            width: 100%;
            display: block;
        }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Auto load first if exists
        const sel = document.getElementById('selAvaliacao');
        if(sel.options.length > 0 && sel.value) {
            carregarRelatorio();
        }
    });

    async function carregarRelatorio() {
        const id = document.getElementById('selAvaliacao').value;
        if(!id) return;

        document.getElementById('loading').style.display = 'flex';
        document.getElementById('reportContent').style.display = 'none';
        document.getElementById('noDataMsg').style.display = 'none';

        try {
            // Rota: GET /api/relatorios/dados?avaliacao_id=X&colaborador_id=Y
            // Pega o ID logado via Blade ou deixa o backend pegar sozinho? 
            // O endpoint getDadosRelatorio pede colaborador_id.
            const userId = {{ auth()->id() }};
            
            const url = `{{ route('relatorios.dados') }}?avaliacao_id=${id}&colaborador_id=${userId}`;
            const res = await fetch(url);
            const data = await res.json();

            if(data.error) throw new Error(data.error);

            renderReport(data);

        }catch (error) {
            console.error(error);
            alert('Erro ao carregar dados: ' + error.message);
        } finally {
            document.getElementById('loading').style.display = 'none';
        }
    }

    function renderReport(data) {
        document.getElementById('reportContent').style.display = 'block';

        // 1. Scores (Circles)
        const sAuto = parseFloat(data.scores.colab.media);
        const sGestor = parseFloat(data.scores.aval.media);
        const sFinal = parseFloat(data.scores.med.media); // Nota de Fechamento ou Final

        updateCircle('circleAuto', 'scoreAuto', sAuto, '#1565c0');
        updateCircle('circleGestor', 'scoreGestor', sGestor, '#2e7d32');
        updateCircle('circleFinal', 'scoreFinal', sFinal, '#7b1fa2');

        // 2. Detalhamento
        const container = document.getElementById('detailsContainer');
        container.innerHTML = '';

        let competencias = {};

        if (data.scores.colab && data.scores.colab.detalhes) {
            data.scores.colab.detalhes.forEach(d => {
                let comp = d.competencia || 'Geral';
                if(!competencias[comp]) competencias[comp] = { skills: {}, notasColab: [], notasAval: [] };
                if(!competencias[comp].skills[d.pergunta]) competencias[comp].skills[d.pergunta] = { colab: null, aval: null };
                
                competencias[comp].skills[d.pergunta].colab = d;
                if (!isNaN(parseFloat(d.nota))) {
                    competencias[comp].notasColab.push(parseFloat(d.nota));
                }
            });
        }

        if (data.scores.aval && data.scores.aval.detalhes) {
            data.scores.aval.detalhes.forEach(d => {
                let comp = d.competencia || 'Geral';
                if(!competencias[comp]) competencias[comp] = { skills: {}, notasColab: [], notasAval: [] };
                if(!competencias[comp].skills[d.pergunta]) competencias[comp].skills[d.pergunta] = { colab: null, aval: null };
                
                competencias[comp].skills[d.pergunta].aval = d;
                if (!isNaN(parseFloat(d.nota))) {
                    competencias[comp].notasAval.push(parseFloat(d.nota));
                }
            });
        }

        for (const [compName, dados] of Object.entries(competencias)) {
            let avgColab = dados.notasColab.length > 0 ? (dados.notasColab.reduce((a,b)=>a+b,0) / dados.notasColab.length).toFixed(2) : '-';
            let avgAval = dados.notasAval.length > 0 ? (dados.notasAval.reduce((a,b)=>a+b,0) / dados.notasAval.length).toFixed(2) : '-';

            let card = document.createElement('div');
            card.style.cssText = "border: 1px solid #ccc; border-radius: 8px; margin-bottom: 20px; overflow: hidden;";
            
            let html = `
                <div style="display: flex; justify-content: space-between; background: #3b5998; color: white; padding: 10px 15px; align-items: center; flex-wrap: wrap;">
                    <h3 style="margin: 0; font-size: 16px;">${compName}</h3>
                    <div style="display: flex; gap: 20px; font-size: 14px; margin-top: 5px;">
                        <span>Média Líder: <strong>${avgAval}</strong></span>
                        <span>Média Colaborador: <strong>${avgColab}</strong></span>
                    </div>
                </div>
                <div style="padding: 15px; background: #fff; overflow-x: auto;">
                    <table class="mobile-cards-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid #eee; text-align: left;">
                                <th style="padding: 8px;">Pergunta (Skill)</th>
                                <th style="padding: 8px; text-align: center;">Nota Líder</th>
                                <th style="padding: 8px; text-align: center;">Nota Colab</th>
                                <th style="padding: 8px;">Comentário Líder</th>
                                <th style="padding: 8px;">Comentário Colab</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            for (const [skillName, skillData] of Object.entries(dados.skills)) {
                let nColab = skillData.colab && skillData.colab.nota !== null ? skillData.colab.nota : '-';
                let nAval = skillData.aval && skillData.aval.nota !== null ? skillData.aval.nota : '-';
                
                let cColab = skillData.colab && skillData.colab.comentarios && skillData.colab.comentarios.length ? skillData.colab.comentarios.join('<br>') : '-';
                let cAval = skillData.aval && skillData.aval.comentarios && skillData.aval.comentarios.length ? skillData.aval.comentarios.join('<br>') : '-';

                html += `
                    <tr style="border-bottom: 1px solid #eee;">
                        <td data-label="Pergunta (Skill)" style="padding: 8px; vertical-align: top;"><strong>${skillName}</strong></td>
                        <td data-label="Nota Líder" style="padding: 8px; text-align: center; vertical-align: top; font-weight: bold; color: #2e7d32;">${nAval}</td>
                        <td data-label="Nota Colab" style="padding: 8px; text-align: center; vertical-align: top; font-weight: bold; color: #1565c0;">${nColab}</td>
                        <td data-label="Comentário Líder" style="padding: 8px; font-size: 13px; color: #555; vertical-align: top;">${cAval}</td>
                        <td data-label="Comentário Colab" style="padding: 8px; font-size: 13px; color: #555; vertical-align: top;">${cColab}</td>
                    </tr>
                `;
            }

            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            card.innerHTML = html;
            container.appendChild(card);
        }

        // 3. Feedback
        // TODO: Backend does not explicitly return "General Feedback" in simple field.
        // It returns comments array in aval details.
        // 3. Feedback / Comentários Gerais (Fechamento do Master)
        const fbContainer = document.getElementById('feedbackContainer');
        const fbText = document.getElementById('feedbackText');
        
        // Comentário do Fechamento (Master)
        const obsMaster = data.scores.med.comentarios;

        // Comentários dos Avaliadores (Agregado)
        const comments = [];
        if(data.scores.aval.detalhes) {
            data.scores.aval.detalhes.forEach(d => {
                if(d.comentarios && Array.isArray(d.comentarios)) {
                    comments.push(...d.comentarios);
                }
            });
        }

        let htmlContent = '';
        
        if(obsMaster) {
            htmlContent += `<strong>Observações do Gestor/RH:</strong><br>${obsMaster}<br><br>`;
        }

        if(comments.length > 0) {
            htmlContent += `<strong>Comentários nas Competências:</strong><br>${comments.map(c => `• ${c}`).join('<br>')}`;
        }

        if(htmlContent) {
            fbContainer.style.display = 'block';
            fbText.innerHTML = htmlContent;
        } else {
            fbContainer.style.display = 'none';
        }
        

    }

    function updateCircle(elemId, textId, score, colorHex) {
        const elem = document.getElementById(elemId);
        const text = document.getElementById(textId);
        
        // Normalize score 0-10 or 0-100? Assuming 0-10.
        // Conic gradient percent: (score / 10) * 100
        const percent = (score / 10) * 100;

        text.innerText = score.toFixed(1);
        elem.style.background = `conic-gradient(${colorHex} ${percent}%, #eee 0)`;
    }


</script>
@endpush
