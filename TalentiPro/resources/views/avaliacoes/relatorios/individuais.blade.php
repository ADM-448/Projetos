@extends(in_array(auth()->user()->perfil_acesso, ['admin', 'master']) ? 'layouts.admin' : (auth()->user()->perfil_acesso === 'gestor' ? 'layouts.gestor' : 'layouts.colaborador'))
@section('title', 'Relatórios Individuais')
@section('content')

@push('styles')
<style>
    /* Estilos copiados e adaptados do HTML original */
    .search-eval-box {
        background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        width: 100%; max-width: 900px; margin-bottom: 30px; display: flex; gap: 10px; align-items: center; border: 1px solid #ccc;
    }
    .input-search-eval { flex: 1; padding: 10px; border: 1px solid #999; border-radius: 5px; outline: none; }
    .btn-filter { background-color: #3b5998; color: white; padding: 10px 20px; border-radius: 5px; border: none; cursor: pointer; font-weight: bold; }
    
    /* Lista de Colaboradores (Intermediária) */
    .colab-list-container { max-width: 900px; width: 100%; background: white; border-radius: 10px; padding: 20px; display: none; margin-bottom: 30px; }
    .colab-list-table { width: 100%; border-collapse: collapse; }
    .colab-list-table th, .colab-list-table td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
    .colab-list-table th { background: #f8f9fa; font-weight: bold; color: #333; }
    
    /* Faixa Azul do Colaborador */
    .colab-info-bar { width: 100%; max-width: 900px; background-color: #4a6fa5; color: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); position: relative; overflow: hidden; display: none;}
    .colab-info-bar::before { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.1); pointer-events: none; }
    .colab-name-title { font-size: 18px; font-weight: bold; margin-bottom: 15px; border-bottom: 1px solid rgba(255, 255, 255, 0.3); padding-bottom: 5px; }
    .colab-details-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; text-align: center; }
    .colab-detail-item { background: rgba(0, 0, 0, 0.2); padding: 10px; border-radius: 5px; font-size: 14px; font-weight: 500; }

    /* Gráficos */
    .charts-container { width: 100%; max-width: 900px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px; display: none; }
    .chart-card { background: white; padding: 20px; border-radius: 10px; text-align: center; border: 1px solid #ddd; }
    .chart-title { font-weight: bold; font-size: 14px; margin-bottom: 15px; color: #333; }
    .chart-circle { width: 100px; height: 100px; background-color: #e0e0e0; border-radius: 50%; margin: 0 auto 10px auto; display: flex; align-items: center; justify-content: center; position: relative; background: conic-gradient(#3b5998 0% 0%, #e0e0e0 0% 100%); transition: background 1s; }
    .chart-inner { width: 60px; height: 60px; background-color: white; border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    .chart-number { font-size: 18px; font-weight: bold; color: #333; }
    .chart-percent { font-size: 10px; color: #666; }

    /* Seção de Competência/Scores */
    .competence-section { width: 100%; max-width: 900px; background: white; border: 1px solid #ccc; border-radius: 10px; padding: 30px; text-align: center; margin-bottom: 30px; display: none;}
    .comp-title { font-size: 18px; font-weight: bold; color: #000; margin-bottom: 20px; }
    .scores-grid { display: flex; justify-content: space-around; gap: 20px; align-items:flex-end; }
    .score-box { background-color: #dbe6f0; border-radius: 10px; padding: 20px; width: 30%; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    .score-label { font-size: 13px; font-weight: bold; color: #333; margin-bottom: 10px; }
    .score-value { font-size: 20px; font-weight: bold; color: #000; margin-bottom: 15px; }
    .btn-more-info { background-color: #3b5998; color: white; border: none; padding: 8px 15px; border-radius: 15px; font-size: 12px; cursor: pointer; font-weight: bold; }
    .btn-more-info:hover { background-color: #2d4373; }
    
    .btn-back-page { background-color: #777; color: white; border: none; padding: 10px 40px; border-radius: 5px; font-size: 14px; cursor: pointer; font-weight: bold; display:none; margin-top:20px; }
    .btn-save-final { background-color: #28a745; color: white; border: none; padding: 10px 40px; border-radius: 5px; font-size: 14px; cursor: pointer; font-weight: bold; margin-top: 20px; }
    
    /* Modals */
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 2000; justify-content: center; align-items: center; overflow-y: auto; padding: 20px; }
    .modal-content { background: white; padding: 30px; border-radius: 10px; width: 800px; max-width: 95%; animation: fadeIn 0.3s; position: relative; }
    .modal-header { font-size: 18px; font-weight: bold; margin-bottom: 20px; text-align: center; color: #3b5998; }
    .modal-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .modal-table th { background-color: #eee; padding: 10px; border: 1px solid #ccc; font-weight: bold; }
    .modal-table td { padding: 10px; border: 1px solid #ccc; font-size: 14px; }
    .close-modal-btn { background-color: #555; color: white; border: none; padding: 8px 20px; border-radius: 5px; cursor: pointer; float: right; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    
    .input-final-score { font-size: 20px; font-weight: bold; width: 80px; text-align: center; padding: 5px; border: 1px solid #999; border-radius: 5px; color: #000; }

    @media (max-width: 768px) {
        .charts-container {
            grid-template-columns: 1fr !important;
        }
        .colab-details-grid {
            grid-template-columns: 1fr !important;
            gap: 10px !important;
        }
        .scores-grid {
            flex-direction: column !important;
            align-items: center !important;
        }
        .score-box {
            width: 100% !important;
            margin-bottom: 10px;
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
@endpush

<div class="page-content" style="padding: 40px; display: flex; flex-direction: column; align-items: center;">
    <h1 class="page-title">Relatórios Individuais</h1>

    {{-- Filtro de Avaliação --}}
    <div class="search-eval-box" style="display: flex; flex-wrap: wrap; gap: 10px;">
        <label style="font-weight: bold; width: 100%;">Selecione a Avaliação:</label>
        <select id="selectAvaliacao" class="input-search-eval" onchange="listarParticipantes()" style="flex: 1; min-width: 200px;">
            <option value="">Selecione...</option>
            @foreach($avaliacoes as $av)
                <option value="{{ $av->id }}">{{ $av->titulo }}</option>
            @endforeach
        </select>
        <button class="btn-filter" onclick="listarParticipantes()">Filtrar</button>
    </div>

    {{-- Tabela de Colaboradores (Passo 2) --}}
    <div id="colabListContainer" class="colab-list-container">
        <h3>Colaboradores na Avaliação</h3>
        <table class="colab-list-table table-relatorios">
            <thead>
                <tr>
                    <th>Colaborador</th>
                    <th style="width: 100px;">Ação</th>
                </tr>
            </thead>
            <tbody id="tbodyColabs">
                <!-- Preenchido via JS -->
            </tbody>
        </table>
    </div>

    {{-- Container do Relatório (Passo 3 - Detalhes) --}}
    <div id="reportContainer" style="width:100%; max-width:900px; display:none; flex-direction:column; align-items:center;">
        
        <div class="sub-title" id="reportAvalTitle" style="font-size: 20px; color: #555; margin-bottom: 30px; border: 1px solid #ccc; padding: 10px 40px; border-radius: 5px; background: #fff;"></div>

        <div class="colab-info-bar" style="display:block;">
            <div class="colab-name-title" id="detailName"></div>
            <div class="colab-details-grid">
                <div class="colab-detail-item" id="detailDept"></div>
                <div class="colab-detail-item" id="detailCargo"></div>
                <div class="colab-detail-item" id="detailGestor"></div>
            </div>
        </div>

        <div class="charts-container" style="display:grid;">
            <div class="chart-card">
                <div class="chart-title">Média Individual</div>
                <div class="chart-circle" id="chartInd">
                    <div class="chart-inner"><span class="chart-number" id="valInd">0</span></div>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-title">Média do Cargo</div>
                <div class="chart-circle" id="chartCargo">
                    <div class="chart-inner"><span class="chart-number" id="valCargo">0</span></div>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-title">Média Setor</div>
                <div class="chart-circle" id="chartSetor">
                    <div class="chart-inner"><span class="chart-number" id="valSetor">0</span></div>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-title">Média Empresa</div>
                <div class="chart-circle" id="chartEmpresa">
                    <div class="chart-inner"><span class="chart-number" id="valEmpresa">0</span></div>
                </div>
            </div>
        </div>

        <div class="competence-section" style="display:block;">
            <div class="comp-title">Resumo da Avaliação</div>

            <div class="scores-grid">
                {{-- Média Avaliadores (Pares/Gestores) --}}
                <div class="score-box">
                    <span class="score-label">Média Avaliadores</span>
                    <span class="score-value" id="scoreAval">0.00</span>
                </div>

                {{-- Média Mediação (Atualizado para ser somente leitura) --}}
                <div class="score-box" style="border: 2px solid #3b5998;">
                    <span class="score-label">Média Mediação</span>
                    <span class="score-value" id="scoreMed">0.00</span>
                </div>

                {{-- Minha Nota (Se for Mediador) --}}
                <div class="score-box" id="boxMinhaMediacao" style="display:none; border: 2px solid #e67e22;">
                    <span class="score-label">Minha Nota</span>
                    <span class="score-value" id="scoreMinhaMed">0.00</span>
                </div>
            </div>
            
            <div id="competenceCardsContainer" style="margin-top: 30px;"></div>
            
            <textarea id="inputComentarios" placeholder="Comentários Finais / Plano de Ação..." style="width:100%; margin-top:20px; padding:10px; border:1px solid #ccc; border-radius:5px; height:100px;"></textarea>

            {{-- Observação do Avaliado (leitura) --}}
            <div id="secaoObsAvaliado" style="display:none; margin-top:20px; text-align:left; background:#f9f9f9; border:1px solid #e0e0e0; border-radius:8px; padding:15px;">
                <h4 style="font-size:14px; font-weight:bold; color:#3b5998; margin-bottom:8px;">
                    <i class="fa-regular fa-comment-dots" style="margin-right:6px;"></i>
                    Observação do Avaliado
                </h4>
                <p id="textoObsAvaliado" style="color:#444; font-size:14px; white-space:pre-wrap; margin:0;"></p>
            </div>

            <button class="btn-save-final" onclick="salvarFechamento()">Salvar Fechamento</button>
        </div>
        
        <div style="display: flex; gap: 15px; margin-top: 20px;">
            <button class="btn-back-page" style="display:inline-block; margin-top: 0;" onclick="voltarParaLista()">Voltar para Lista</button>
            @if(in_array(auth()->user()->perfil_acesso, ['master', 'gestor', 'rh']))
            <button class="btn-export-pdf" style="background-color: #d32f2f; color: white; border: none; padding: 10px 40px; border-radius: 5px; font-size: 14px; cursor: pointer; font-weight: bold; display: inline-block;" onclick="exportarPdf()">
                <i class="fa-solid fa-file-pdf"></i> Exportar PDF
            </button>
            @endif
        </div>
    </div>
</div>

{{-- Modal Mensagem Genérica --}}
<div id="modalMessage" class="modal-overlay">
    <div class="modal-content" style="width: 400px; text-align: center;">
        <div id="msgIcon" style="font-size: 50px; margin-bottom: 20px;"></div>
        <div id="msgTitle" class="modal-header" style="margin-bottom: 10px;"></div>
        <p id="msgBody" style="font-size: 16px; color: #555; margin-bottom: 25px;"></p>
        <button class="btn-more-info" style="padding: 10px 30px; font-size: 14px;" onclick="fecharModal('modalMessage')">OK</button>
    </div>
</div>

@push('scripts')
<script>
    let currentAvaliacaoId = null;
    let currentAvaliadoId = null;
    let modalData = {}; // Cache para dados dos modais

    function showModalMessage(title, message, isError = false) {
        const modal = document.getElementById('modalMessage');
        const icon = document.getElementById('msgIcon');
        const titleElem = document.getElementById('msgTitle');
        const bodyElem = document.getElementById('msgBody');

        if (isError) {
            icon.innerHTML = '<i class="fa-solid fa-circle-xmark" style="color: #ea4335;"></i>';
            titleElem.style.color = '#ea4335';
        } else {
            icon.innerHTML = '<i class="fa-solid fa-circle-check" style="color: #34a853;"></i>';
            titleElem.style.color = '#34a853';
        }

        titleElem.innerText = title;
        bodyElem.innerText = message;
        modal.style.display = 'flex';
    }

    async function listarParticipantes() {
        const id = document.getElementById('selectAvaliacao').value;
        if(!id) return;
        currentAvaliacaoId = id;
        
        document.getElementById('reportContainer').style.display = 'none';
        document.getElementById('colabListContainer').style.display = 'block';
        
        const tbody = document.getElementById('tbodyColabs');
        tbody.innerHTML = '<tr><td colspan="2">Carregando...</td></tr>';
        
        try {
            const res = await fetch('{{ url("/api/avaliacao") }}/' + id + '/participantes')
            const users = await res.json();
            
            tbody.innerHTML = '';
            if(users.length === 0) {
                tbody.innerHTML = '<tr><td colspan="2">Nenhum participante encontrado.</td></tr>';
                return;
            }
            
            users.forEach(u => {
                tbody.innerHTML += `
                    <tr>
                        <td data-label="Colaborador">${u.name}</td>
                        <td data-label="Ação"><button class="btn-more-info" onclick="verRelatorio(${u.id})">Ver +</button></td>
                    </tr>
                `;
            });
            
        } catch(e) { console.error(e); showModalMessage('Erro!', 'Erro ao buscar participantes.', true); }
    }
    
    async function verRelatorio(avaliadoId) {
        currentAvaliadoId = avaliadoId;
        document.getElementById('colabListContainer').style.display = 'none';
        
        // Atualiza Título
        const select = document.getElementById('selectAvaliacao');
        document.getElementById('reportAvalTitle').innerText = select.options[select.selectedIndex].text;
        
        // Carrega Dados
        try {
            const res = await fetch('{{ url("/api/relatorio/dados") }}?avaliacao_id=' + currentAvaliacaoId + '&colaborador_id=' + avaliadoId);
            const data = await res.json();
            
            if(data.error) throw new Error(data.error);
            
            // Popula Info
            const c = data.colaborador;
            document.getElementById('detailName').innerText = c.name;
            document.getElementById('detailDept').innerText = c.departamento ? c.departamento.nome : '-';
            document.getElementById('detailCargo').innerText = c.cargo ? c.cargo.nome : '-';
            document.getElementById('detailGestor').innerText = c.gestor ? `Gestor: ${c.gestor.name}` : 'Sem Gestor';
            
            // Popula Charts
            updateChart('Ind', data.charts.individual);
            updateChart('Cargo', data.charts.cargo);
            updateChart('Setor', data.charts.setor);
            updateChart('Empresa', data.charts.empresa);
            
            // Popula Scores Box
            document.getElementById('scoreAval').innerText = data.scores.aval.media;
            document.getElementById('scoreMed').innerText = data.scores.med.media > 0 ? data.scores.med.media : '0.00';
            
            // Minha Nota (Mediador)
            if(data.scores.minha_mediacao) {
                document.getElementById('scoreMinhaMed').innerText = data.scores.minha_mediacao;
                document.getElementById('boxMinhaMediacao').style.display = 'flex';
            } else {
                document.getElementById('boxMinhaMediacao').style.display = 'none';
            }

            document.getElementById('inputComentarios').value = data.scores.med.comentarios || '';

            // Observação do Avaliado
            const obsAvaliado = data.scores.observacao_avaliado;
            const secaoObs = document.getElementById('secaoObsAvaliado');
            if (obsAvaliado) {
                document.getElementById('textoObsAvaliado').innerText = obsAvaliado;
                secaoObs.style.display = 'block';
            } else {
                secaoObs.style.display = 'none';
            }

            // Render Competence Cards
            renderCompetenceCards(data.scores);
            
            document.getElementById('reportContainer').style.display = 'flex';
            
        } catch(e) { console.error(e); showModalMessage('Erro!', 'Erro ao carregar relatório.', true); }
    }
    
    function updateChart(suffix, val) {
        // Simples update visual
        document.getElementById('val'+suffix).innerText = val;
        // Atualiza cor/slice do gráfico conic
        const perc = (val / 10) * 100; // Assumindo nota 10
        // conic-gradient(#3b5998 PERC%, #e0e0e0 PERC%)
        document.getElementById('chart'+suffix).style.background = `conic-gradient(#3b5998 ${perc}%, #e0e0e0 ${perc}%)`;
    }
    
    function voltarParaLista() {
        document.getElementById('reportContainer').style.display = 'none';
        document.getElementById('colabListContainer').style.display = 'block';
    }

    function exportarPdf() {
        if(!currentAvaliacaoId || !currentAvaliadoId) return;
        const url = `{{ route('relatorios.exportar.pdf') }}?avaliacao_id=${currentAvaliacaoId}&colaborador_id=${currentAvaliadoId}`;
        window.open(url, '_blank');
    }
    
    function renderCompetenceCards(scores) {
        const container = document.getElementById('competenceCardsContainer');
        container.innerHTML = '';
        
        let competencias = {};

        if (scores.colab && scores.colab.detalhes) {
            scores.colab.detalhes.forEach(d => {
                let comp = d.competencia || 'Geral';
                if(!competencias[comp]) competencias[comp] = { skills: {}, notasColab: [], notasAval: [] };
                if(!competencias[comp].skills[d.pergunta]) competencias[comp].skills[d.pergunta] = { colab: null, aval: null };
                
                competencias[comp].skills[d.pergunta].colab = d;
                if (!isNaN(parseFloat(d.nota))) {
                    competencias[comp].notasColab.push(parseFloat(d.nota));
                }
            });
        }

        if (scores.aval && scores.aval.detalhes) {
            scores.aval.detalhes.forEach(d => {
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
    }
    
    function fecharModal(id) {
        document.getElementById(id).style.display = 'none';
    }
    
    async function salvarFechamento() {
        if(!currentAvaliacaoId || !currentAvaliadoId) return;
        
        // A nota mediação virá de outro processo, então aqui salvamos apenas observações.
        const obs = document.getElementById('inputComentarios').value;
        
        try {
            const res = await fetch('{{ url("/api/relatorio/salvar-fechamento") }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({
                    avaliacao_id: currentAvaliacaoId,
                    avaliado_id: currentAvaliadoId,
                    // nota_final não é enviada aqui pois é somente leitura neste momento
                    comentarios: obs
                })
            });
            const data = await res.json();
            if(data.success) {
                showModalMessage('Sucesso!', 'Fechamento Salvo com Sucesso!');
            } else {
                showModalMessage('Erro!', 'Erro ao salvar.', true);
            }
        } catch(e) { console.error(e); showModalMessage('Erro!', 'Erro ao salvar.', true); }
    }
    
    window.onclick = function (event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.style.display = 'none';
        }
    }
</script>
@endpush
@endsection
