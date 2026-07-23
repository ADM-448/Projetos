@extends(in_array(auth()->user()->perfil_acesso, ['admin', 'master']) ? 'layouts.admin' : (auth()->user()->perfil_acesso === 'gestor' ? 'layouts.gestor' : 'layouts.colaborador'))
@section('title', 'PDI - Metas Individuais')
@section('content')

@push('styles')
<style>
    /* Estilos Gerais */


    /* Card da Lista */
    .list-card {
        background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: 1px solid #e0e0e0;
    }

    .search-area {
        display: flex; align-items: center; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 20px;
    }
    .search-input {
        padding: 10px; border: 1px solid #ccc; border-radius: 5px; width: 300px; background: #f0f2f5; outline:none;
    }
    .btn-search {
        background: #3b5998; color: white; width: 35px; height: 35px; border-radius: 50%; border: none; cursor: pointer; display:flex; align-items:center; justify-content:center;
    }

    /* Tabela */
    .pdi-table { width: 100%; border-collapse: collapse; }
    .pdi-table th { text-align: left; padding: 15px; font-weight: bold; color: #333; border-bottom: 2px solid #eee; }
    .pdi-table td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
    
    .progress-bar-bg {
        width: 100px; height: 10px; background: #eee; border-radius: 5px; overflow: hidden;
        display: inline-block; vertical-align: middle; margin-right: 10px;
    }
    .progress-fill { height: 100%; background: #27ae60; width: 0%; transition: width 0.5s; }
    
    .icon-action { cursor: pointer; color: #555; margin-right: 10px; font-size: 14px; }
    .icon-action:hover { color: #3b5998; }
    .icon-eye { color: #3b5998; cursor: pointer; font-size: 1.2em; }

    /* MODALS */
    .modal-overlay {
        display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(0, 0, 0, 0.5); z-index: 2000; justify-content: center; align-items: center;
        overflow-y: auto; padding: 20px;
    }
    .modal-content {
        background-color: white; width: 900px; max-width: 95%; border-radius: 10px; padding: 30px;
        position: relative; animation: slideDown 0.3s;
    }

    /* Modal Criar */
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #444; }
    .form-control { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; background: #fff; }
    
    .steps-container { margin-top: 10px; }
    .step-row { display: flex; gap: 10px; margin-bottom: 10px; }
    .btn-add-step { background: #3b5998; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-size: 12px; }

    /* Modal Ver (Layout Img 3) */
    .colab-header {
        background: white; border: 1px solid #eee; padding: 20px; border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); margin-bottom: 20px; text-align: center;
    }
    .colab-tags { display: flex; justify-content: center; gap: 20px; margin-top: 15px; }
    .tag-blue { background: #3b5998; color: white; padding: 5px 20px; border-radius: 5px; font-size: 13px; font-weight: bold; }
    
    .meta-container {
        background: white; border: 1px solid #eee; padding: 30px; border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); display: flex; justify-content: space-between; align-items: flex-start;
    }
    .meta-info { width: 65%; }
    .meta-title { font-size: 18px; font-weight: bold; color: #333; margin-bottom: 20px; }
    .date-row { display: flex; gap: 40px; margin-bottom: 20px; font-size: 14px; color: #555; }
    
    .meta-buttons { display: flex; gap: 10px; margin-top: 20px; }
    .btn-modal-action {
        background: #4a6fa5; color: white; padding: 8px 15px; border-radius: 5px; border: none; cursor: pointer; font-size: 13px;
    }
    
    .checklist-area { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
    .check-item { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; font-size: 14px; }
    .check-item input { width: 16px; height: 16px; cursor: pointer; accent-color: #27ae60; }

    .progress-circle-container {
        width: 30%; display: flex; justify-content: center; align-items: center; flex-direction: column;
    }
    .circle-outer {
        width: 120px; height: 120px; border-radius: 50%;
        background: conic-gradient(#27ae60 0deg, #eee 0deg);
        display: flex; align-items: center; justify-content: center; margin-bottom: 10px; transition: background 0.5s;
    }
    .circle-inner {
        width: 90px; height: 90px; background: white; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold;
    }

    /* Small Modal */
    .small-modal { width: 500px; }
    .obs-list { max-height: 250px; overflow-y: auto; text-align: left; margin-bottom: 15px; }
    .obs-item { background: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 10px; }
    .obs-header { font-size: 11px; font-weight: bold; color: #666; display: flex; justify-content: space-between; margin-bottom: 5px; }

    /* Custom Checkbox List */
    .custom-select-container {
        border: 1px solid #ccc;
        border-radius: 5px;
        max-height: 200px;
        overflow-y: auto;
        background: #fff;
    }
    .custom-checkbox-item {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: background 0.2s;
        margin: 0; /* Override default label margin */
    }
    .custom-checkbox-item:last-child { border-bottom: none; }
    .custom-checkbox-item:hover { background: #f5f7fa; }
    
    .custom-checkbox-item input { margin-right: 12px; accent-color: #3b5998; width: 16px; height: 16px; cursor: pointer; }
    .colab-name { font-weight: 600; color: #444; font-size: 14px; }
    .colab-role { color: #888; font-size: 12px; margin-left: auto; background: #eee; padding: 2px 8px; border-radius: 10px; }

    @keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

    /* Responsividade Modal Ver */
    @media (max-width: 768px) {
        .meta-container {
            flex-direction: column;
            align-items: center;
            padding: 15px;
        }
        .meta-info {
            width: 100%;
            margin-bottom: 20px;
            text-align: center;
        }
        .date-row {
            flex-direction: column;
            gap: 10px;
        }
        .meta-buttons {
            flex-direction: column;
            width: 100%;
        }
        .btn-modal-action {
            width: 100%;
        }
        .progress-circle-container {
            width: 100%;
        }
        .colab-tags {
            flex-wrap: wrap;
            gap: 10px;
        }
        .tag-blue {
            flex: 1;
            min-width: 120px;
            box-sizing: border-box;
        }

        .desktop-colab-count { display: none; }
        .mobile-colab-count { display: block; }
        .mobile-ver-mais { display: inline-block; }
        .td-last-pdi { justify-content: space-between !important; }
    }

    /* Ocultar elementos mobile no desktop */
    .mobile-colab-count, .mobile-ver-mais { display: none; }
</style>
@endpush


    <div class="header-actions">
        <h1 class="page-title">PDI - Metas Individuais</h1>
        <button class="btn-primary" onclick="abrirModalCriar()">
            <i class="fa-solid fa-plus"></i> Criar PDI
        </button>
    </div>

    {{-- Feedback Messages --}}
    @if(session('success'))
    <div style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 5px;">
        {{ session('success') }}
    </div>
    @endif
    
    @if($errors->any())
    <div style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 5px;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="list-card">
        <div class="search-area">
            <input type="text" id="searchInput" class="search-input" placeholder="Pesquisar..." onkeyup="filtrarTable()">
            <button class="btn-search"><i class="fa-solid fa-magnifying-glass"></i></button>
        </div>

        <table class="pdi-table" id="pdiTable">
            <thead>
                <tr>
                    <th width="40%">Título</th>
                    <th width="30%">Porcentagem</th>
                    <th width="15%">Ações</th>
                    <th width="15%">Ver Mais</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $uniquePdis = $pdis->unique('titulo'); 
                @endphp
                @foreach($uniquePdis as $pdi)
                <tr>
                    <td data-label="Título">
                        <div class="mobile-td-content">
                            <strong>{{ $pdi->titulo }}</strong><br>
                            <span class="desktop-colab-count" style="font-size: 12px; color: #666;">
                                {{ $pdis->where('titulo', $pdi->titulo)->count() }} Colaboradores
                            </span>
                        </div>
                    </td>
                    <td data-label="Porcentagem">
                        <div class="mobile-td-flex">
                            @php 
                                $avg = $pdis->where('titulo', $pdi->titulo)->avg('progresso'); 
                            @endphp
                            <div class="progress-bar-bg" style="margin: 0; width: 80px;">
                                <div class="progress-fill" style="width: {{ $avg }}%;"></div>
                            </div>
                            <span>{{ round($avg) }}%</span>
                        </div>
                    </td>
                    <td data-label="Ações">
                        <div class="mobile-td-flex">
                            <i class="fa-solid fa-pen icon-action" title="Editar Grupo" onclick="editarGrupo('{{ addslashes($pdi->titulo) }}')"></i>
                            <i class="fa-solid fa-trash icon-action" title="Excluir Grupo" onclick="excluirGrupo('{{ addslashes($pdi->titulo) }}')" style="color: #c0392b; margin-right: 0;"></i>
                        </div>
                    </td>
                    <td data-label="" class="td-last-pdi">
                        <div class="mobile-colab-count" style="font-size: 12px; color: #666; font-weight: bold; text-align: left;">
                            {{ $pdis->where('titulo', $pdi->titulo)->count() }} Colaboradores
                        </div>
                        <div class="mobile-td-flex">
                            <span class="mobile-ver-mais" style="font-weight: 700; text-transform: uppercase; font-size: 11px; color: #777; margin-right: 5px;">VER MAIS</span>
                            <i class="fa-solid fa-eye icon-eye" onclick="verPdiGroup('{{ addslashes($pdi->titulo) }}')"></i>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>


{{-- MODAL CRIAR (Img 0) --}}
<div id="modalCriar" class="modal-overlay">
    <div class="modal-content">
        <h2 style="margin-bottom: 20px;">Criar PDI</h2>
        <form action="{{ route('pdis.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label>Selecionar Colaborador(es)</label>
                <div class="custom-select-container">
                    @foreach($colaboradores as $colab)
                    <label class="custom-checkbox-item">
                        <input type="checkbox" name="colaboradores[]" value="{{ $colab->id }}">
                        <span class="colab-name">{{ $colab->name }}</span>
                        <span class="colab-role">{{ $colab->cargo->nome ?? 'Sem Cargo' }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label>Meta (Nome do PDI)</label>
                <input type="text" name="titulo" class="form-control" placeholder="Ex: Curso de Liderança" required>
            </div>

            <div class="form-group" style="display: flex; gap: 20px;">
                <div style="flex:1;">
                    <label>Data Início</label>
                    <input type="date" name="data_inicio" id="createInicio" class="form-control">
                </div>
                <div style="flex:1;">
                    <label>Data Término</label>
                    <input type="date" name="data_fim" id="createFim" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label>Descrição da Meta</label>
                <textarea name="descricao" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label>Etapas para Conclusão</label>
                <div class="steps-container" id="createStepsContainer">
                    <div class="step-row">
                        <input type="text" name="etapas[]" class="form-control" placeholder="Ex: Assistir Módulo 1" required>
                    </div>
                </div>
                <button type="button" class="btn-add-step" onclick="adicionarEtapaCriacao()">+ Adicionar Etapa</button>
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn-primary" style="flex: 1; min-width: 120px; background: #ccc; color: #333;" onclick="fecharModal('modalCriar')">Cancelar</button>
                <button type="submit" class="btn-primary" style="flex: 1; min-width: 150px;">Salvar</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDITAR GRUPO --}}
<div id="modalEditar" class="modal-overlay">
    <div class="modal-content">
        <h2 style="margin-bottom: 20px;">Editar Grupo de PDI</h2>
        <form action="{{ route('pdis.update.group') }}" method="POST">
            @csrf
            <input type="hidden" name="old_titulo" id="editOldTitulo">
            
            <div class="form-group">
                <label>Meta (Nome do PDI)</label>
                <input type="text" name="titulo" id="editTitulo" class="form-control" required>
            </div>

            <div class="form-group" style="display: flex; gap: 20px;">
                <div style="flex:1;">
                    <label>Data Início</label>
                    <input type="date" name="data_inicio" id="editInicio" class="form-control">
                </div>
                <div style="flex:1;">
                    <label>Data Término</label>
                    <input type="date" name="data_fim" id="editFim" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label>Descrição da Meta</label>
                <textarea name="descricao" id="editDescricao" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label>Selecionar Colaborador(es)</label>
                <div class="custom-select-container">
                    @foreach($colaboradores as $colab)
                    <label class="custom-checkbox-item">
                        <input type="checkbox" name="colaboradores[]" value="{{ $colab->id }}" class="edit-colab-check">
                        <span class="colab-name">{{ $colab->name }}</span>
                        <span class="colab-role">{{ $colab->cargo->nome ?? 'Sem Cargo' }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label>Etapas</label>
                <div class="steps-container" id="editStepsContainer">
                   <!-- Populated by JS -->
                </div>
                <button type="button" class="btn-add-step" onclick="adicionarEtapaEdicao()">+ Adicionar Etapa</button>
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn-primary" style="flex: 1; min-width: 120px; background: #ccc; color: #333;" onclick="document.getElementById('modalEditar').style.display='none'">Cancelar</button>
                <button type="submit" class="btn-primary" style="flex: 1; min-width: 150px;">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL VER DETALHES (Img 2) --}}
<div id="modalVer" class="modal-overlay">
    <div class="modal-content">
        
        <div class="colab-header">
            {{-- Name updated by JS --}}
            <h2 id="viewName">Nome Colaborador</h2> 
            
            {{-- Select for Grouping --}}
            <div style="width: 300px; max-width: 100%; margin: 10px auto;">
                <select id="viewColabSelect" class="form-control">
                    {{-- JS populates --}}
                </select>
            </div>

            <div class="colab-tags">
                <div class="tag-blue" id="viewDept">Dept</div>
                <div class="tag-blue" id="viewCargo">Cargo</div>
                <div class="tag-blue" id="viewGestor">Gestor</div>
            </div>
        </div>

        <div class="meta-container">
            <div class="meta-info">
                <div class="meta-title" id="viewTitulo">Titulo PDI</div>

                <div class="date-row">
                    <span id="viewInicio">Início: -</span>
                    <span id="viewFim">Final: -</span>
                    <span id="viewConclusaoData" style="display:none;"></span>
                </div>

                <div class="meta-buttons">
                    <button class="btn-modal-action" onclick="toggleDesc()">Descrição do PDI</button>
                    <button class="btn-modal-action" onclick="toggleObs()">Observações do Colaborador</button>
                    <a id="btnComprovante" href="#" target="_blank" class="btn-modal-action" style="display:none; text-decoration:none; background:#e67e22;">
                        <i class="fa-solid fa-file-pdf"></i> Ver Comprovante
                    </a>
                </div>

                <div class="checklist-area">
                    <p style="font-weight: bold; margin-bottom: 10px;">Etapas:</p>
                    <div id="viewChecklist">
                        {{-- JS populates --}}
                    </div>
                </div>
            </div>

            <div class="progress-circle-container">
                <div class="circle-outer" id="viewCircleOuter">
                    <div class="circle-inner" id="viewCircleText">0%</div>
                </div>
                <span style="font-size: 12px; color: #666; margin-top: 5px;">Conclusão</span>
                
                <button class="btn-primary" style="margin-top:20px; background:#333;" onclick="fecharModal('modalVer')">Voltar</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DESCRIÇÃO --}}
<div id="modalDesc" class="modal-overlay" style="z-index: 2001;">
    <div class="modal-content small-modal">
        <h3>Descrição do PDI</h3>
        <p id="descText" style="margin: 20px 0; color: #555; text-align: justify; white-space: pre-wrap;"></p>
        <div style="text-align:center;">
            <button class="btn-primary" style="display:inline-block;" onclick="document.getElementById('modalDesc').style.display='none'">Fechar</button>
        </div>
    </div>
</div>

{{-- MODAL OBSERVAÇÕES --}}
<div id="modalObs" class="modal-overlay" style="z-index: 2001;">
    <div class="modal-content small-modal">
        <h3>Observações do Colaborador</h3>
        <div class="obs-list" id="obsListContainer">
            {{-- Lista --}}
        </div>
        {{-- Master pode comentar? O html diz "obs do colaborador". Vou deixar leitura e escrita pra ser flexível --}}
        <textarea id="newObsMaster" placeholder="Adicionar observação..." class="form-control" rows="2"></textarea>
        <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-top: 10px;">
            <button class="btn-primary" style="flex: 1; min-width: 100px; background: #ccc; color: #333;" onclick="document.getElementById('modalObs').style.display='none'">Fechar</button>
            <button class="btn-primary" style="flex: 1; min-width: 100px;" onclick="salvarObsMaster()">Enviar</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const allPdis = @json($pdis);
    // Grouping helper
    const pdiGroups = {};
    allPdis.forEach(p => {
        if(!pdiGroups[p.titulo]) pdiGroups[p.titulo] = [];
        pdiGroups[p.titulo].push(p);
    });

    let currentPdiId = null;

    // --- MODAL CRIAR ---
    function abrirModalCriar() {
        document.getElementById('modalCriar').style.display = 'flex';
    }
    
    function adicionarEtapaCriacao() {
        const container = document.getElementById('createStepsContainer');
        const div = document.createElement('div');
        div.className = 'step-row';
        div.innerHTML = `
            <input type="text" name="etapas[]" class="form-control" placeholder="Nova etapa..." required> 
            <button type="button" class="btn-add-step" style="background:#c0392b;" onclick="this.parentElement.remove()">X</button>
        `;
        container.appendChild(div);
    }

    // --- MODAL EDITAR ---
    function editarGrupo(titulo) {
        const group = pdiGroups[titulo];
        if(!group || group.length === 0) return;
        
        const pdi = group[0]; // Take first one as template

        document.getElementById('editOldTitulo').value = titulo;
        document.getElementById('editTitulo').value = pdi.titulo;
        
        // Format YYYY-MM-DD for input type=date
        const start = pdi.data_inicio ? (() => {
            const dateObj = new Date(pdi.data_inicio);
            return !isNaN(dateObj) ? dateObj.toISOString().split('T')[0] : '';
        })() : '';
        
        const end = pdi.data_fim ? (() => {
            const dateObj = new Date(pdi.data_fim);
            return !isNaN(dateObj) ? dateObj.toISOString().split('T')[0] : '';
        })() : '';
        
        document.getElementById('editInicio').value = start;
        document.getElementById('editFim').value = end;
        document.getElementById('editDescricao').value = pdi.descricao || '';

        // 1. Colaboradores
        const colabsNoGrupo = group.map(p => p.colaborador_id);
        const checkboxes = document.querySelectorAll('.edit-colab-check');
        checkboxes.forEach(chk => {
            chk.checked = colabsNoGrupo.includes(parseInt(chk.value));
        });

        // 2. Etapas (Load from first PDI)
        const container = document.getElementById('editStepsContainer');
        container.innerHTML = '';
        if(pdi.acoes && pdi.acoes.length > 0) {
            pdi.acoes.forEach(a => {
                adicionarEtapaEdicao(a.titulo);
            });
        } else {
             // Optional: add one empty input if needed, or leave empty to add manually
        }

        document.getElementById('modalEditar').style.display = 'flex';
    }

    function adicionarEtapaEdicao(valor = '') {
        const container = document.getElementById('editStepsContainer');
        const div = document.createElement('div');
        div.className = 'step-row';
        div.innerHTML = `
            <input type="text" name="etapas[]" class="form-control" placeholder="Nome da etapa..." value="${valor}" required> 
            <button type="button" class="btn-add-step" style="background:#c0392b;" onclick="this.parentElement.remove()">X</button>
        `;
        container.appendChild(div);
    }

    // --- ACAO EXCLUIR ---
    function excluirGrupo(titulo) {
        showConfirmModal('Excluir Grupo PDI', 'Tem certeza que deseja excluir o grupo "' + titulo + '"? Isso removerá o PDI de TODOS os colaboradores.', function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("pdis.destroy.group") }}';
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';
            form.appendChild(method);

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'titulo';
            input.value = titulo;
            form.appendChild(input);

            document.body.appendChild(form);
            form.submit();
        });
    }

    // --- MODAL VER GROUP ---
    function verPdiGroup(titulo) {
        const group = pdiGroups[titulo];
        if(!group) return;

        // Populate Select
        const select = document.getElementById('viewColabSelect');
        select.innerHTML = '';
        group.forEach(p => {
             const colabName = p.colaborador ? p.colaborador.name : 'Desconhecido';
             const opt = document.createElement('option');
             opt.value = p.id;
             opt.innerText = colabName;
             select.appendChild(opt);
        });

        // Open Modal
        document.getElementById('modalVer').style.display = 'flex';
        
        // Load first
        if(group.length > 0) {
            verPdi(group[0].id);
        }
        
        // Listener
        select.onchange = (e) => verPdi(parseInt(e.target.value));
    }

    // --- VIEW SINGLE PDI DETAILS ---
    function verPdi(id) {
        currentPdiId = id;
        const pdi = allPdis.find(p => p.id === id);
        if(!pdi) return;

        // Sync Select if manual call (optional safety)
        document.getElementById('viewColabSelect').value = id;

        // Header Info
        const colab = pdi.colaborador || {};
        document.getElementById('viewName').innerText = colab.name || 'Desconhecido';
        document.getElementById('viewDept').innerText = colab.departamento ? colab.departamento.nome : 'Sem Dept';
        document.getElementById('viewCargo').innerText = colab.cargo ? colab.cargo.nome : 'Sem Cargo';
        document.getElementById('viewGestor').innerText = colab.gestor ? 'Gestor: ' + colab.gestor.name : 'Sem Gestor';

        // Meta Info
        document.getElementById('viewTitulo').innerText = pdi.titulo;
        document.getElementById('viewInicio').innerText = 'Início: ' + (pdi.data_inicio ? formatDate(pdi.data_inicio) : '-');
        document.getElementById('viewFim').innerText = 'Final: ' + (pdi.data_fim ? formatDate(pdi.data_fim) : '-');

        const conclusionSpan = document.getElementById('viewConclusaoData');
        if(pdi.data_conclusao) {
            conclusionSpan.innerText = 'Conclusão Real: ' + formatDate(pdi.data_conclusao);
            conclusionSpan.style.display = 'inline-block';
            conclusionSpan.style.color = '#27ae60';
            conclusionSpan.style.marginLeft = '20px';
            conclusionSpan.style.fontWeight = 'bold';
        } else {
            conclusionSpan.style.display = 'none';
        }

        // Circle
        updateCircle(pdi.progresso);

        // Checklist
        renderChecklist(pdi);

        // Descrição e Obs (Prepara dados)
        document.getElementById('descText').innerText = pdi.descricao || 'Sem descrição.';
        renderObsList(pdi);

        // Comprovante - usa rota de download
        const btnComp = document.getElementById('btnComprovante');
        if(pdi.comprovante) {
            btnComp.style.display = 'inline-flex';
            btnComp.style.alignItems = 'center';
            btnComp.style.gap = '5px';
            let downloadUrl = "{{ route('pdis.download', ':id') }}";
            btnComp.href = downloadUrl.replace(':id', pdi.id);
        } else {
            btnComp.style.display = 'none';
        }
    }

    function renderChecklist(pdi) {
        const container = document.getElementById('viewChecklist');
        container.innerHTML = '';
        if(!pdi.acoes || pdi.acoes.length === 0) {
            container.innerHTML = '<p>Nenhuma etapa.</p>';
            return;
        }

        pdi.acoes.forEach(a => {
            const checked = a.status === 'concluido' ? 'checked' : '';
            // Disabled for Gestor/Admin - Only the collaborator can mark as done in "Meu PDI"
            container.innerHTML += `
                <div class="check-item">
                    <input type="checkbox" ${checked} disabled>
                    <span>${a.titulo}</span>
                </div>
            `;
        });
    }
    
    function renderObsList(pdi) {
        const container = document.getElementById('obsListContainer');
        container.innerHTML = '';
        if(!pdi.comentarios || pdi.comentarios.length === 0) {
            container.innerHTML = '<p style="color:#777; font-size:12px;">Nenhuma observação.</p>';
        } else {
            pdi.comentarios.forEach(c => {
                const author = c.user ? c.user.name : 'User';
                const date = new Date(c.created_at).toLocaleString();
                container.innerHTML += `
                    <div class="obs-item">
                        <div class="obs-header"><span>${author}</span> <span>${date}</span></div>
                        <div>${c.comentario}</div>
                    </div>
                `;
            });
        }
    }

    // --- ACTIONS ---
    async function toggleAcao(id) {
        try {
            const res = await fetch('{{ url("/pdis/acao") }}/' + id + '/toggle', {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
            });
            const data = await res.json();
            if(data.success) {
                updateCircle(data.progresso);
                // Update in allPdis local cache to persist if toggling back and forth
                const pdi = allPdis.find(p => p.id === currentPdiId);
                if(pdi) pdi.progresso = data.progresso;
            }
        } catch(e) { console.error(e); }
    }

    async function salvarObsMaster() {
        const txt = document.getElementById('newObsMaster').value;
        if(!txt) return;
        
        try {
            const res = await fetch('{{ url("/pdis") }}/' + currentPdiId + '/comentar', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({ comentario: txt })
            });
            const data = await res.json();
            if(data.success) {
                alert('Observação enviada!');
                location.reload(); 
            }
        } catch(e) { console.error(e); }
    }

    // --- HELPER UI ---
    function updateCircle(percent) {
        document.getElementById('viewCircleText').innerText = percent + "%";
        document.getElementById('viewCircleOuter').style.background = `conic-gradient(#27ae60 ${percent * 3.6}deg, #eee 0deg)`;
    }

    function toggleDesc() {
        document.getElementById('modalDesc').style.display = 'flex';
    }

    function toggleObs() {
        document.getElementById('modalObs').style.display = 'block';
    }

    function fecharModal(id) {
        document.getElementById(id).style.display = 'none';
        if(id === 'modalVer') location.reload(); // Refresh para atualizar % na tabela
    }
    
    function formatDate(dateStr) {
        if(!dateStr) return '-';
        const [year, month, day] = dateStr.split('-');
        return `${day}/${month}/${year}`;
    }

    function filtrarTable() {
        const val = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#pdiTable tbody tr');
        rows.forEach(r => {
            const txt = r.innerText.toLowerCase();
            r.style.display = txt.includes(val) ? '' : 'none';
        });
    }

    window.onclick = function (event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.style.display = 'none';
            if(event.target.id === 'modalVer') location.reload();
        }
    }

    function validarDatas(startId, endId) {
        const startInput = document.getElementById(startId);
        const endInput = document.getElementById(endId);

        // Create error message element
        let errorMsg = endInput.parentElement.querySelector('.date-error-msg');
        if (!errorMsg) {
            errorMsg = document.createElement('small');
            errorMsg.className = 'date-error-msg';
            errorMsg.style.color = '#e74c3c';
            errorMsg.style.fontSize = '12px';
            errorMsg.style.marginTop = '5px';
            errorMsg.style.display = 'none';
            endInput.parentElement.appendChild(errorMsg);
        }

        function checkDates() {
            const startDate = startInput.value;
            const endDate = endInput.value;

            // Set min attribute
            if (startDate) {
                endInput.min = startDate;
            }

            // Only validate if both dates are complete (10 chars: YYYY-MM-DD)
            if (startDate && endDate && startDate.length === 10 && endDate.length === 10) {
                if (endDate < startDate) {
                    errorMsg.textContent = '⚠️ A Data de Término não pode ser anterior à Data de Início.';
                    errorMsg.style.display = 'block';
                    endInput.style.borderColor = '#e74c3c';
                } else {
                    errorMsg.style.display = 'none';
                    endInput.style.borderColor = '#ccc';
                }
            } else {
                // Hide error if dates are incomplete
                errorMsg.style.display = 'none';
                endInput.style.borderColor = '#ccc';
            }
        }

        startInput.addEventListener('change', checkDates);
        endInput.addEventListener('input', checkDates);
        endInput.addEventListener('change', checkDates);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        if(urlParams.get('create')) {
            abrirModalCriar();
        }

        // Apply Validation
        validarDatas('createInicio', 'createFim');
        validarDatas('editInicio', 'editFim');
    });
</script>
@endpush
@endsection