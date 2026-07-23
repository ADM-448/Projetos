@extends(in_array(auth()->user()->perfil_acesso, ['admin', 'master']) ? 'layouts.admin' : (auth()->user()->perfil_acesso === 'gestor' ? 'layouts.gestor' : 'layouts.colaborador'))
@section('title', 'Minhas Avaliações')

@push('styles')
<style>
    /* Estilos baseados em avaliacoes_colaborador.html */
    .page-title {
        font-size: 24px; color: #333; font-weight: bold; margin-bottom: 30px; text-align: center;
    }

    .list-card {
        background: white; border-radius: 10px; padding: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: 1px solid #e0e0e0;
        width: 100%; max-width: 900px; margin: 0 auto;
    }

    .custom-table { width: 100%; border-collapse: collapse; }
    .custom-table th {
        text-align: center; padding: 15px; border-bottom: 2px solid #ccc;
        color: #333; font-weight: bold; font-size: 14px;
    }
    .custom-table td {
        padding: 15px; text-align: center; color: #555; font-weight: 500;
        font-size: 14px; border-bottom: 1px solid #eee; border-right: 1px solid #eee;
    }
    .custom-table td:last-child { border-right: none; }

    .link-access {
        color: #00008B; text-decoration: none; font-weight: bold; cursor: pointer;
    }
    .link-access:hover { text-decoration: underline; }

    /* Modal Generico */
    .modal-overlay {
        display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(0, 0, 0, 0.5); z-index: 2000;
        justify-content: center; align-items: center; padding: 20px;
    }
    .modal-content {
        background-color: white; padding: 30px; border-radius: 10px;
        width: 900px; max-width: 95%; position: relative; animation: slideDown 0.3s;
        max-height: 90vh; overflow-y: auto;
    }
    .modal-sm { width: 500px; } /* Modal pequeno para confirmacoes */

    .modal-title { font-size: 20px; font-weight: bold; text-align: center; margin-bottom: 5px; color: #333; }
    .modal-subtitle { font-size: 14px; color: #666; text-align: center; margin-bottom: 25px; }

    .eval-section-title {
        font-size: 16px; font-weight: bold; margin-bottom: 15px; color: #333;
        background: #f8f9fa; padding: 10px; border-left: 4px solid #6a4c7c;
    }

    .questions-table { width: 100%; border-collapse: collapse; border: 1px solid #999; margin-bottom: 30px; }
    .questions-table th {
        background-color: #eef2f7; border: 1px solid #999; padding: 10px;
        font-weight: bold; color: #000; text-align: center;
    }
    .questions-table td { border: 1px solid #999; padding: 10px; vertical-align: middle; }

    .col-pergunta { width: 30%; background-color: #dbe4f1; font-weight: 600; text-align: center; font-size: 13px; }
    .col-explica { width: 55%; background-color: #dbe4f1; text-align: justify; font-size: 13px; padding: 15px; line-height: 1.4; }
    .col-nota { width: 15%; text-align: center; background-color: white; }

    .input-nota {
        width: 60px; padding: 8px; border: 1px solid #333; border-radius: 5px;
        text-align: center; font-weight: bold; outline: none;
    }
    .input-nota:focus { border-color: #3b5998; border-width: 2px; }
    .input-nota:disabled { background-color: #eee; cursor: not-allowed; border-color: #ccc; }

    .input-nota-modern {
        width: 80px; padding: 10px; border: 2px solid #ddd; border-radius: 8px;
        text-align: center; font-weight: bold; font-size: 16px; color: #333;
        transition: all 0.3s;
    }
    .input-nota-modern:focus { 
        border-color: #3b5998; box-shadow: 0 0 8px rgba(59, 89, 152, 0.3); background-color: #f0f7ff; 
    }
    .input-nota-modern:disabled { background-color: #eee; cursor: not-allowed; border-color: #ccc; color: #777; }

    .btn-save {
        background-color: #3b5998; color: white; border: none; padding: 12px 60px;
        border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px;
        display: block; margin: 0 auto;
    }
    .btn-save:hover { background-color: #2d4373; }
    
    .btn-cancel {
        background-color: #ccc; color: #333; border: none; padding: 10px 30px;
        border-radius: 5px; cursor: pointer; font-weight: bold;
    }

    .close-modal {
        position: absolute; top: 20px; right: 20px; font-size: 20px; cursor: pointer; color: #999;
    }
    @keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

    /* DROPDOWN MENU */
    .dropdown { position: relative; display: inline-block; margin-left: 10px; }
    .dropbtn { color: #666; cursor: pointer; font-size: 18px; padding: 5px; }
    .dropbtn:hover { color: #333; }
    .dropdown-content {
        display: none; position: absolute; right: 0; background-color: #f9f9f9;
        min-width: 160px; box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 1; border-radius: 5px; overflow: hidden;
    }
    .dropdown-content a {
        color: black; padding: 12px 16px; text-decoration: none; display: block; font-size: 14px; text-align: left;
    }
    .dropdown-content a:hover { background-color: #f1f1f1; }
    .show { display: block; }

    /* Select Estilizado (Master) */
    .select-colaborador {
        width: 100%; max-width: 400px;
        padding: 10px; border-radius: 5px;
        border: 1px solid #3b5998; background-color: #3b5998;
        color: white; font-weight: bold; outline: none;
        margin: 0 auto 20px auto; display: block;
        text-align: center; cursor: pointer;
        font-size: 16px;
    }
    .select-colaborador option { background-color: white; color: #333; }

    /* ESTRELAS */
    .star-rating { direction: ltr; font-size: 24px; display: inline-flex; cursor: pointer; }
    .star-rating i { color: #ccc; transition: 0.2s; padding: 0 2px; }
    .star-rating i.hover, .star-rating i.active { color: #f39c12; }

    /* Estilo Específico para o SELECT de Notas */
    .select-nota-modern {
        width: 100%; height: 45px;
        padding: 5px 10px; border: 2px solid #ddd; border-radius: 8px;
        font-weight: bold; font-size: 14px; color: #333;
        background-color: white; outline: none; transition: all 0.3s;
        cursor: pointer; appearance: auto; /* Garante seta nativa */
    }
    .select-nota-modern:focus {
        border-color: #3b5998; box-shadow: 0 0 5px rgba(59, 89, 152, 0.2);
    }
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        display: block;
    }

    @media (max-width: 768px) {
        .modal-content {
            padding: 15px !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            border-radius: 0 !important;
        }
        .questions-table, .questions-table thead, .questions-table tbody, .questions-table th, .questions-table td, .questions-table tr {
            display: block !important;
            width: 100% !important;
            min-width: 0 !important;
        }
        .questions-table thead { display: none !important; }
        .questions-table tr {
            margin-bottom: 20px !important;
            border: 1px solid #ccc !important;
            border-radius: 8px !important;
            background: #fff !important;
            padding: 15px !important;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05) !important;
            overflow: hidden !important;
        }
        .questions-table td {
            display: flex !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            text-align: left !important;
            padding: 10px 0 !important;
            border: none !important;
            background-color: transparent !important;
        }
        .questions-table td::before {
            content: attr(data-label);
            font-weight: 700;
            font-size: 13px;
            color: #3b5998;
            margin-bottom: 5px;
            width: 100%;
            display: block;
        }
        .table-responsive { overflow-x: visible !important; }
    }
</style>
@endpush

@section('content')
<div class="page-content">

    <h1 class="page-title">Avaliações</h1>

    @if(isset($contexto) && $contexto === 'manager')
    <div style="text-align: center; margin-bottom: 20px;">
        <a href="{{ route('avaliacoes.create') }}" class="btn-save" style="text-decoration: none; display: inline-block; width: auto;">
            <i class="fa-solid fa-plus-circle"></i> Criar Nova Avaliação
        </a>
    </div>
    @endif

    <div class="list-card">
        @if($participacoes->isEmpty())
            <p style="text-align: center; padding: 20px; color: #666;">Nenhuma avaliação disponível no momento.</p>
        @else
        <table class="custom-table">
            <thead>
                <tr>
                    <th width="50%">Nome</th>
                    <th width="25%">Prazo</th>
                    <th width="25%">Acessar</th>
                </tr>
            </thead>
            <tbody>
                @foreach($participacoes as $part)
                <tr>
                    <td data-label="Nome">{{ $part->avaliacao->titulo }}</td>
                    <td data-label="Prazo">{{ \Carbon\Carbon::parse($part->avaliacao->data_fim)->format('d/m/Y') }}</td>
                    <td data-label="Acessar">
                        <div style="display: flex; align-items: center; justify-content: center;">
                            @if($part->status == 'pendente')
                                <a class="link-access" href="#" onclick="carregarAvaliacao({{ $part->avaliacao->id }}, 'pendente')">Acessar</a>
                            @else
                                <a class="link-access" href="#" onclick="carregarAvaliacao({{ $part->avaliacao->id }}, 'realizada')" style="color: green; display: flex; align-items: center; gap: 5px;">
                                    <i class="fa-solid fa-check"></i> Concluída
                                </a>
                            @endif

                            @if(!in_array(auth()->user()->perfil_acesso, ['master', 'admin']))
                                <div class="dropdown" style="margin-left: 15px;">
                                    <i class="fa-solid fa-ellipsis-vertical dropbtn" onclick="toggleDropdown('drop-{{ $part->id }}')"></i>
                                    <div id="drop-{{ $part->id }}" class="dropdown-content">
                                        <a href="#" onclick="openModalSolicitacao({{ $part->avaliacao->id }})">Solicitar Reavaliação</a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

{{-- MODAL DE RESPOSTAS --}}
<div id="modalResposta" class="modal-overlay">
    <div class="modal-content">
        <i class="fa-solid fa-xmark close-modal" onclick="fecharModal('modalResposta')"></i>

        <h2 id="modalTitle" class="modal-title">Carregando...</h2>
        <div id="modalSubtitle" class="modal-subtitle">Autoavaliação</div>

        {{-- Select de Alvo (Apenas visível para Master ou se houver múltiplos) --}}
        <select id="selectAlvo" class="select-colaborador" style="display:none;" onchange="carregarFormulario(this.value)">
        </select>

        <div id="loadingArea" style="text-align: center; padding: 50px; display: none;">
            <i class="fa-solid fa-spinner fa-spin fa-2x"></i>
        </div>

        <form id="formRespostas" style="display: none;">
             <input type="hidden" id="participanteId" name="participante_id">
             @csrf
            
            <div id="questionsContainer"></div>

            {{-- Campo de Observação do Avaliado --}}
            <div id="secaoObservacao" style="margin: 20px 0 30px 0;">
                <label style="font-weight: bold; display:block; margin-bottom:8px; font-size: 15px; color: #333;">
                    <i class="fa-regular fa-comment-dots" style="margin-right: 6px; color: #6a4c7c;"></i>
                    Observações / Comentários (opcional)
                </label>
                <textarea id="observacaoAvaliado" name="observacao_avaliado" rows="4"
                    style="width:100%; padding:12px; border:1px solid #ccc; border-radius:8px; font-size:14px; resize:vertical;"
                    placeholder="Deixe aqui suas impressões, comentários ou justificativas sobre a avaliação..."></textarea>
            </div>

            <button type="button" id="btnSalvarResposta" class="btn-save" onclick="openModalConfirmacao()">Salvar</button>
            <p id="msgConcluida" style="text-align: center; color: green; font-weight: bold; display: none; margin-top: 20px;">
                Avaliação Concluída.
            </p>
        </form>
    </div>
</div>

{{-- MODAL SOLICITAÇÃO --}}
<div id="modalSolicitacao" class="modal-overlay">
    <div class="modal-content modal-sm">
        <i class="fa-solid fa-xmark close-modal" onclick="fecharModal('modalSolicitacao')"></i>
        <h3 class="modal-title" style="margin-bottom: 20px;">Solicitar Reavaliação</h3>
        <p style="text-align: center; margin-bottom: 20px; font-size: 14px; color: #555;">
            Caso precise corrigir sua avaliação, descreva o motivo abaixo e aguarde a aprovação do RH.
        </p>

        <form method="POST" action="{{ route('solicitacoes.store') }}">
            @csrf
            <input type="hidden" name="avaliacao_id" id="solicitacaoAvaliacaoId">
            <input type="hidden" name="tipo" value="reabertura">
            
            <div style="margin-bottom: 20px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Justificativa:</label>
                <textarea name="justificativa" rows="4" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;" required placeholder="Explique o motivo..."></textarea>
            </div>
            
            <div style="text-align: center;">
                <button type="submit" class="btn-save">Enviar Solicitação</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL CONFIRMAÇÃO ENVIO --}}
<div id="modalConfirmacaoEnvio" class="modal-overlay">
    <div class="modal-content modal-sm" style="text-align: center;">
        <i class="fa-solid fa-circle-exclamation" style="font-size: 50px; color: #f39c12; margin-bottom: 20px;"></i>
        <h3 class="modal-title">Confirmar Envio?</h3>
        <p style="margin: 20px 0; color: #555; line-height: 1.5;">
            Você tem certeza que deseja finalizar a avaliação?<br>
            <strong>Você não poderá editar as respostas depois.</strong><br>
            Será necessário solicitar reabertura ao RH caso precise alterar algo.
        </p>
        <div style="display: flex; justify-content: center; gap: 15px;">
            <button class="btn-cancel" onclick="fecharModal('modalConfirmacaoEnvio')">Cancelar</button>
            <button class="btn-save" onclick="enviarRespostas()" style="margin: 0;">Confirmar e Enviar</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Rotas
    var routeGetDetalhesBase = "{{ url('/api/avaliacao') }}"; 
    var routeGetAlvosBase = "{{ url('/api/avaliacao') }}"; 
    var routeGetRespostasBase = "{{ url('/api/avaliacao-participante') }}";
    var routeResponder = "{{ route('avaliacoes.responder') }}";

    // DROPDOWN
    function toggleDropdown(id) {
        // Close others
        var dropdowns = document.getElementsByClassName("dropdown-content");
        for (var i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.id !== id && openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
        document.getElementById(id).classList.toggle("show");
    }

    // Close dropdown window click
    window.onclick = function(event) {
        if (!event.target.matches('.dropbtn')) {
            var dropdowns = document.getElementsByClassName("dropdown-content");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show')) {
                    openDropdown.classList.remove('show');
                }
            }
        }
        
        // Modais close check
        if (event.target.classList.contains('modal-overlay')) {
            event.target.style.display = 'none';
        }
    }

    function fecharModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    /* --- LOGICA AVALIAÇÃO --- */
    /* --- LOGICA AVALIAÇÃO --- */
    var currentAlvos = [];
    var currentAvaliacao = null; // Cache dos dados da avaliação

    async function carregarAvaliacao(avaliacaoId, status) {
        document.getElementById('modalResposta').style.display = 'flex';
        document.getElementById('loadingArea').style.display = 'block';
        document.getElementById('formRespostas').style.display = 'none';
        document.getElementById('selectAlvo').style.display = 'none'; 
        document.getElementById('modalTitle').innerText = 'Carregando...';
        document.getElementById('modalSubtitle').innerText = '';

        try {
            // 1. Detalhes da Avaliação
            const respDetalhes = await fetch(`${routeGetDetalhesBase}/${avaliacaoId}/detalhes`);
            if(!respDetalhes.ok) throw new Error('Erro ao buscar detalhes');
            currentAvaliacao = await respDetalhes.json();

            document.getElementById('modalTitle').innerText = currentAvaliacao.titulo;
            
            // 2. Alvos (Quem eu vou avaliar)
            const context = "{{ $contexto ?? '' }}";
            const respAlvos = await fetch(`${routeGetAlvosBase}/${avaliacaoId}/alvos?context=${context}`);
            if(!respAlvos.ok) throw new Error('Erro ao buscar alvos');
            const dataAlvos = await respAlvos.json();
            currentAlvos = dataAlvos.alvos;

            if (currentAlvos.length === 0) {
                alert('Erro: Nenhum alvo vinculado.');
                fecharModal('modalResposta');
                return;
            }

            // 3. Monta o Select (caso seja Master ou tenha múltiplos)
            const select = document.getElementById('selectAlvo');
            select.innerHTML = '';
            
            currentAlvos.forEach(a => {
                const opt = document.createElement('option');
                opt.value = a.id; // ID da Participação
                const statusIcon = a.status === 'realizada' ? ' (Concluída)' : '';
                opt.innerText = a.avaliado.name + statusIcon; 
                select.appendChild(opt);
            });

            // MODIFICAÇÃO: Se tiver mais de um alvo, TEM QUE MOSTRAR O SELECT, independente do perfil (Gestor avaliando equipe precisa disso)
            // Mas para "Autoavaliação" (colaborador simples) geralmente é 1.
            // Regra: se length > 1 -> Mostra Select.
            
            // Mostra o select sempre para o usuário ver quem está sendo avaliado (o "bloco azul")
            // A MENOS que seja ele próprio e seja o único alvo (auto-avaliação)
            const currentUserId = {{ auth()->id() }};
            const isAutoAvaliacao = (currentAlvos.length === 1 && currentAlvos[0].avaliado_id == currentUserId);

            if (!isAutoAvaliacao) {
                select.style.display = 'block';
                if(currentAlvos.length > 1) {
                    document.getElementById('modalSubtitle').innerText = 'Selecione abaixo quem deseja avaliar:';
                } else {
                    document.getElementById('modalSubtitle').innerText = currentAvaliacao && currentAvaliacao.forma_avaliacao ? 'Responda abaixo' : 'Avaliação';
                }
            } else {
                select.style.display = 'none';
                document.getElementById('modalSubtitle').innerText = 'Responda abaixo';
            }

            // Carrega o primeiro alvo da lista automaticamente (ou o único)
            if(currentAlvos.length > 0) {
                 select.value = currentAlvos[0].id;
                 carregarFormulario(currentAlvos[0].id);
            } else {
                 alert('Nenhum alvo encontrado.');
                 fecharModal('modalResponder');
            }

        } catch (error) {
            console.error(error);
            alert('Falha ao carregar dados da avaliação.');
            fecharModal('modalResposta');
        }
    }

    async function carregarFormulario(participanteId) {
        // Encontra o objeto alvo selecionado para info extra se precisar
        const alvo = currentAlvos.find(a => a.id == participanteId);
        if(!alvo) return;
        
        const isMaster = ['admin', 'master'].includes("{{ auth()->user()->perfil_acesso }}");
        const isCompleted = (alvo.status === 'realizada');
        
        // Todos que preenchem (mesmo master/gestor) não podem mais editar sem reabertura
        const canEdit = !isCompleted;

        document.getElementById('btnSalvarResposta').style.display = canEdit ? 'block' : 'none';
        document.getElementById('msgConcluida').style.display = (isCompleted && !isMaster) ? 'block' : 'none';
        
        if(isMaster) {
           // Opcional: atualizar subtítulo ou deixar o select falar por si
           // document.getElementById('modalSubtitle').innerText = "Avaliando: " + alvo.avaliado.name;
        }

        document.getElementById('loadingArea').style.display = 'block';
        document.getElementById('formRespostas').style.display = 'none';

        try {
            // Set ID no formulário
            document.getElementById('participanteId').value = participanteId;
            document.getElementById('selectAlvo').value = participanteId; // Sync select

            // Busca Respostas Salvas
            const respRespostas = await fetch(`${routeGetRespostasBase}/${participanteId}/respostas`);
            const dadosRespostas = await respRespostas.json();
            
            const notasMap = {};
            const respostasSalvas = dadosRespostas.respostas || dadosRespostas;
            respostasSalvas.forEach(r => notasMap[r.skill_id] = { nota: r.nota, comentario: r.comentario || '' });

            // Preenche campo de observação (se existir)
            const obsField = document.getElementById('observacaoAvaliado');
            if (obsField) {
                obsField.value = dadosRespostas.observacao_avaliado || '';
                obsField.disabled = !canEdit;
                document.getElementById('secaoObservacao').style.display = canEdit ? 'block' : (dadosRespostas.observacao_avaliado ? 'block' : 'none');
            }

            renderizarPerguntas(currentAvaliacao.competencias, notasMap, !canEdit);

            document.getElementById('loadingArea').style.display = 'none';
            document.getElementById('formRespostas').style.display = 'block';

        } catch(e) {
            console.error(e);
            alert('Erro ao carregar formulário.');
        }
    }

    function renderizarPerguntas(competencias, notasMap, isReadOnly) {
        const container = document.getElementById('questionsContainer');
        container.innerHTML = '';

        if(!competencias || competencias.length === 0) {
            container.innerHTML = '<p>Nenhuma competência vinculada.</p>';
            return;
        }

        const tipoAvaliacao = currentAvaliacao && currentAvaliacao.forma_avaliacao ? currentAvaliacao.forma_avaliacao : 'nota_1_10';

        competencias.forEach(comp => {
            const titleDiv = document.createElement('div');
            titleDiv.className = 'eval-section-title';
            titleDiv.innerText = comp.nome;
            container.appendChild(titleDiv);

            const table = document.createElement('table');
            table.className = 'questions-table';
            table.style.minWidth = '500px';
            table.innerHTML = `
                <thead>
                    <tr>
                        <th width="30%">Perguntas</th>
                        <th width="40%">Explicação</th>
                        <th width="15%">Nota</th>
                        <th width="15%">Obs (Opcional)</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `;
            const tbody = table.querySelector('tbody');

            const tableWrapper = document.createElement('div');
            tableWrapper.className = 'table-responsive';
            tableWrapper.appendChild(table);
            container.appendChild(tableWrapper);

            if(comp.skills && comp.skills.length > 0) {
                comp.skills.forEach(skill => {
                    const tr = document.createElement('tr');
                    const notaObj = notasMap[skill.id] || { nota: 0, comentario: '' };
                    const notaSalva = notaObj.nota;
                    const comentarioSalvo = notaObj.comentario;
                    const disabledAttr = isReadOnly ? 'disabled' : ''; // Para inputs nativos
                    const disabledClass = isReadOnly ? 'disabled' : ''; // Para custom (estrelas)

                    // Logic distinguishing Master vs Colaborador
                    const isMaster = ['admin', 'master'].includes("{{ auth()->user()->perfil_acesso }}");

                    // Se for MASTER, talvez mostre input direto para edição, ou respeite o tipo?
                    // Vamos respeitar o tipo visual, mas o Master pode ver o numero.
                    
                    let inputHtml = '';
                    
                    if (tipoAvaliacao === 'estrelas') {
                        // RENDER ESTRELAS (1 a 5)
                        // Input Hidden guarda o valor
                        // Gera 5 icones
                        let starHtml = '';
                        for(let i=1; i<=5; i++) {
                            const activeClass = i <= notaSalva ? 'active' : '';
                            const onClick = isReadOnly ? '' : `onclick="setStar(this, ${skill.id}, ${i})"`;
                            starHtml += `<i class="fa-solid fa-star ${activeClass}" data-val="${i}" ${onClick}></i>`;
                        }
                        
                        inputHtml = `
                            <div class="star-rating ${disabledClass}" id="stars-${skill.id}">
                                ${starHtml}
                            </div>
                            <input type="hidden" name="notas[${skill.id}]" id="input-star-${skill.id}" value="${notaSalva}">
                        `;
                    } 
                    else if (tipoAvaliacao === 'conceito') {
                        // DROPDOWN (Ruim/Bom/Otimo) - Mapeando para notas?
                        // Vamos assumir: Ruim=3, Bom=7, Otimo=10 (ou usar string se o backend suportar, mas é float. Mapping necessário)
                        // Backend espera float. Vamos mapear visualmente.
                        // 1-4: Ruim, 5-8: Bom, 9-10: Ótimo?
                        // Melhor usar valores fixos no select.
                        
                        inputHtml = `
                            <select name="notas[${skill.id}]" class="select-nota-modern" ${disabledAttr}>
                                <option value="">Selecione</option>
                                <option value="3" ${notaSalva == 3 ? 'selected' : ''}>Abaixo</option>
                                <option value="7" ${notaSalva == 7 ? 'selected' : ''}>Esperado</option>
                                <option value="10" ${notaSalva == 10 ? 'selected' : ''}>Acima</option>
                            </select>
                        `;
                    }
                    else {
                         // PADRAO (Nota 1-10)
                         const cssClass = isMaster ? 'input-nota' : 'input-nota-modern';
                         const maxVal = isMaster ? '100' : '10';
                         const ph = isMaster ? '-' : '0-10';
                         const validation = isMaster ? '' : 'oninput="validarNota(this)"';

                         inputHtml = `
                            <input type="number" 
                                   name="notas[${skill.id}]" 
                                   class="${cssClass}" 
                                   min="0" max="${maxVal}" 
                                   step="0.1"
                                   placeholder="${ph}"
                                   value="${notaSalva > 0 ? notaSalva : ''}"
                                   ${validation}
                                   ${disabledAttr}>
                         `;
                    }

                    const obsHtml = `
                        <input type="text" name="comentarios[${skill.id}]" class="input-comentario-obs" placeholder="Obs..." value="${comentarioSalvo}" ${disabledAttr} style="width: 100%; border: 1px solid #ccc; border-radius: 5px; padding: 10px; font-size: 13px; outline: none; transition: border-color 0.3s;" onfocus="this.style.borderColor='#3b5998'" onblur="this.style.borderColor='#ccc'">
                    `;

                    tr.innerHTML = `
                        <td class="col-pergunta" data-label="Pergunta (Skill)">${skill.nome}</td>
                        <td class="col-explica" data-label="Explicação">${skill.descricao || ''}</td>
                        <td class="col-nota" data-label="Nota">
                            ${inputHtml}
                        </td>
                        <td class="col-obs" data-label="Observação (Opcional)" style="background-color:#fff; padding:10px 0;">
                            ${obsHtml}
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="3">Nenhuma habilidade cadastrada.</td></tr>';
            }
            container.appendChild(table);
        });
    }

    function setStar(starElement, skillId, val) {
        // Atualiza input hidden
        document.getElementById(`input-star-${skillId}`).value = val;
        
        // Atualiza visual
        const container = document.getElementById(`stars-${skillId}`);
        const stars = container.getElementsByTagName('i');
        
        for(let i=0; i<stars.length; i++) {
            const starVal = parseInt(stars[i].getAttribute('data-val'));
            if(starVal <= val) {
                stars[i].classList.add('active');
            } else {
                stars[i].classList.remove('active');
            }
        }
    }

    function validarNota(input) {
        let value = parseFloat(input.value);
        if (value < 0) input.value = 0;
        if (value > 10) input.value = 10;
    }

    function openModalConfirmacao() {
        const isMaster = ['admin', 'master'].includes("{{ auth()->user()->perfil_acesso }}");
        
        // Find current target
        const participanteId = document.getElementById('participanteId').value;
        const alvo = currentAlvos.find(a => a.id == participanteId);
        
        // Check if Self-Evaluation (User is the Avaliado)
        // Note: AvaliacaoParticipante has avaliado_id
        const isSelfEvaluation = alvo && (alvo.avaliado_id == {{ auth()->id() }});

        if(isMaster) {
            enviarRespostas();
        } else if (isSelfEvaluation) {
            // "esse modal sp deve aparescer so para o avaliado"
            document.getElementById('modalConfirmacaoEnvio').style.display = 'flex';
        } else {
            // Manager evaluating team -> Direct submit
            enviarRespostas();
        }
    }

    async function enviarRespostas() {
        // Fechar confirmação
        fecharModal('modalConfirmacaoEnvio');

        const form = document.getElementById('formRespostas');
        const formData = new FormData(form);

        try {
            const resp = await fetch(routeResponder, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });

            if (!resp.ok) {
                if (resp.status === 419) {
                    alert('Sua sessão expirou. A página será recarregada.');
                    location.reload();
                    return;
                }
                
                try {
                    const errorData = await resp.json();
                    if(errorData.message) {
                        alert('Erro: ' + errorData.message);
                        return;
                    }
                } catch(e) {
                    // Não é JSON
                }
                
                throw new Error('Erro HTTP: ' + resp.status);
            }

            const data = await resp.json();

            if (data.success) {
                fecharModal('modalResposta');
                alert('Avaliação salva com sucesso!');
                location.reload();

            } else {
                alert('Erro ao salvar: ' + (data.message || 'Desconhecido'));
            }

        } catch (error) {
            console.error(error);
            alert('Erro ao salvar os dados. Verifique sua conexão ou tente recarregar a página.');
        }
    }
    
    /* --- LOGICA SOLICITAÇÃO --- */
    function openModalSolicitacao(avaliacaoId) {
        document.getElementById('solicitacaoAvaliacaoId').value = avaliacaoId;
        document.getElementById('modalSolicitacao').style.display = 'flex';
    }

    async function reabrirAvaliacao(id) {
        
        try {
            const res = await fetch('{{ url("/avaliacoes/reabrir") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ avaliacao_id: id })
            });

            if(!res.ok) {
                 const data = await res.json();
                 alert('Erro: ' + (data.error || 'Falha ao reabrir.'));
                 return;
            }
            
            // Sucesso -> Abre Modal direto
            carregarAvaliacao(id, 'pendente');

        } catch(e) {
            console.error(e);
            alert('Erro ao processar solicitação.');
        }
    }
</script>
@endpush