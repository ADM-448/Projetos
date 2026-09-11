@extends('layouts.app')

@section('title', 'Resumo da Reunião - ' . $reuniao->titulo)
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-file-lines" style="color: var(--primary-color);"></i> Relatório da Reunião {{ $reuniao->feedback->audio_path ? '(IA)' : '(Manual)' }}</h1>
        <p style="color: #666; font-size: 0.95rem;">Reunião com: {{ auth()->id() === $reuniao->gestor_id ? $reuniao->participantes->pluck('name')->join(', ') : $reuniao->gestor->name }}</p>
    </div>
    <div class="reuniao-show-actions" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        @if(auth()->id() === $reuniao->gestor_id || in_array(auth()->user()->perfil_acesso->value, ['master', 'admin']))
            <form id="form-excluir-reuniao" action="{{ route('reunioes.destroy', $reuniao) }}" method="POST" style="display: inline-block; margin: 0;">
                @csrf
                @method('DELETE')
                <button type="button" onclick="confirmarExclusaoReuniao()" class="btn-danger" style="padding: 10px 18px; font-size: 0.9rem; font-weight: bold; border-radius: 6px; cursor: pointer; width: 100%; justify-content: center; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-trash-can"></i> Excluir Registro
                </button>
            </form>
        @endif
        @if(auth()->id() === $reuniao->gestor_id)
            <button id="btn-iniciar-edicao" onclick="iniciarEdicao()" class="btn-primary" style="padding: 10px 18px; font-size: 0.9rem; font-weight: bold; background: var(--primary-color); border: none; border-radius: 6px; cursor: pointer; display: inline-flex; justify-content: center; align-items: center; gap: 6px;">
                <i class="fa-solid fa-pen-to-square"></i> Editar Registro
            </button>
            <button id="btn-cancelar-edicao" onclick="cancelarEdicao()" class="btn-secondary" style="display: none; padding: 10px 18px; font-size: 0.9rem; font-weight: bold; border-radius: 6px; cursor: pointer; justify-content: center; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-xmark"></i> Cancelar
            </button>
            <button id="btn-salvar-edicao" onclick="salvarEdicao()" class="btn-primary" style="display: none; padding: 10px 18px; font-size: 0.9rem; font-weight: bold; background: #059669; border: none; border-radius: 6px; cursor: pointer; justify-content: center; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-check"></i> Salvar Alterações
            </button>
        @endif
        <a href="{{ route('reunioes.index') }}" class="btn-secondary" style="text-decoration: none; padding: 10px 18px; border-radius: 6px; font-size: 0.9rem; justify-content: center; display: inline-flex;">Voltar para Reuniões</a>
    </div>
</div>

<div class="reuniao-show-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
    <!-- Resumo e Transcrição -->
    <div class="card" style="padding: 30px;">
        <h2 style="color: var(--primary-color); border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; margin-bottom: 20px;">
            <i class="fa-solid fa-wand-magic-sparkles"></i> Resumo Executivo
        </h2>
        
        {{-- Resumo Mode Visualização --}}
        <div id="resumo-visualizacao" style="background-color: #f8fafc; padding: 20px; border-radius: 8px; border-left: 5px solid var(--primary-color); line-height: 1.6; color: #334155;">
            {!! nl2br(e($reuniao->feedback->resumo_ia ?? 'Nenhum resumo gerado.')) !!}
        </div>

        {{-- Resumo Mode Edição (oculto) --}}
        <div id="resumo-edicao" style="display: none;">
            <textarea id="edit-resumo-texto" rows="12" style="width: 100%; padding: 15px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem; resize: vertical; box-sizing: border-box; line-height: 1.6; font-family: inherit; color: #334155;">{{ $reuniao->feedback->resumo_ia }}</textarea>
        </div>

        @if($reuniao->feedback->resumo_ia_original)
        <div style="margin-top: 20px; background-color: #f1f5f9; padding: 20px; border-radius: 8px; border-left: 5px solid #64748b;">
            <h4 style="margin-top: 0; color: #475569; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;"><i class="fa-solid fa-robot"></i> Resumo Original da IA (Apenas Leitura)</h4>
            <div style="line-height: 1.6; color: #64748b; font-size: 0.9rem; margin-top: 10px;">
                {!! nl2br(e($reuniao->feedback->resumo_ia_original)) !!}
            </div>
        </div>
        @endif

        <h3 style="color: var(--primary-color); margin-top: 40px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; margin-bottom: 20px; font-weight: bold; font-size: 1.25rem;">
            <i class="fa-solid fa-list-check"></i> Planos de Desenvolvimento (PDI) Estabelecidos
        </h3>
        
        <div id="pdis-container" style="display: flex; flex-direction: column; gap: 15px;">
            {{-- Renderizado via JavaScript --}}
        </div>
        <p id="sem-pdis" style="color: #94a3b8; text-align: center; font-style: italic; display: none;">Nenhum PDI estabelecido nesta reunião.</p>
        
        <div id="edicao-pdi-acoes" style="display: none; margin-top: 15px;">
            <button type="button" onclick="adicionarPdi()" style="background: #f1f5f9; border: 1px solid #cbd5e1; padding: 10px 18px; border-radius: 6px; cursor: pointer; font-size: 0.9rem; font-weight: 600; color: #475569; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-plus"></i> Adicionar PDI
            </button>
        </div>

        @if($reuniao->feedback->transcricao_completa !== 'Registro Manual')
        <h3 style="color: #475569; margin-top: 40px; margin-bottom: 15px;">
            <i class="fa-solid fa-quote-left"></i> Transcrição (Final)
        </h3>
        
        <div style="background-color: #fff; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; max-height: 400px; overflow-y: auto; font-family: monospace; font-size: 0.9rem; line-height: 1.5; color: #64748b;">
            {!! nl2br(e($reuniao->feedback->transcricao_completa ?? 'Nenhuma transcrição disponível.')) !!}
        </div>
        @endif

        @if($reuniao->feedback->transcricao_original && $reuniao->feedback->transcricao_original !== 'Registro Manual')
        <h3 style="color: #475569; margin-top: 30px; margin-bottom: 15px;">
            <i class="fa-solid fa-file-audio"></i> Transcrição Original (Bruta da IA)
        </h3>
        
        <div style="background-color: #f1f5f9; padding: 20px; border-radius: 8px; border-left: 5px solid #94a3b8; max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 0.85rem; line-height: 1.5; color: #94a3b8;">
            {!! nl2br(e($reuniao->feedback->transcricao_original)) !!}
        </div>
        @endif
    </div>

    <!-- Metadados -->
    <div class="card" style="padding: 30px; height: fit-content;">
        <h3 style="color: #334155; margin-bottom: 20px;"><i class="fa-solid fa-circle-info"></i> Detalhes</h3>
        
        <div style="margin-bottom: 20px;">
            <strong>Data da Reunião:</strong><br>
            <span style="color: #64748b;">{{ $reuniao->data_hora->format('d/m/Y às H:i') }}</span>
        </div>

        {{-- Exibe a data de modificação se o relatório foi editado --}}
        <div id="bloco-data-modificacao" style="margin-bottom: 20px; display: {{ $reuniao->feedback->updated_at->diffInSeconds($reuniao->feedback->created_at) > 5 ? 'block' : 'none' }}; background: #fffbeb; border: 1px solid #fef3c7; padding: 10px; border-radius: 6px; border-left: 3px solid #f59e0b;">
            <strong style="color: #b45309; font-size: 0.85rem;"><i class="fa-solid fa-clock-rotate-left"></i> Última Modificação:</strong><br>
            <span id="data-modificacao-texto" style="color: #b45309; font-size: 0.85rem;">{{ $reuniao->feedback->updated_at->format('d/m/Y \à\s H:i') }}</span>
        </div>

        <div style="margin-bottom: 20px;">
            @if($reuniao->feedback->sentimento_ia)
                <strong>Sentimento Geral:</strong><br>
                @php
                    $sentimento = strtolower($reuniao->feedback->sentimento_ia ?? 'neutro');
                    $cor = '#64748b'; // default neutro
                    if(str_contains($sentimento, 'positivo')) $cor = '#10b981';
                    if(str_contains($sentimento, 'negativo')) $cor = '#ef4444';
                @endphp
                <span style="color: {{ $cor }}; font-weight: bold; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                    @if($cor === '#10b981') <i class="fa-regular fa-face-smile"></i>
                    @elseif($cor === '#ef4444') <i class="fa-regular fa-face-frown"></i>
                    @else <i class="fa-regular fa-face-meh"></i>
                    @endif
                    {{ ucfirst($reuniao->feedback->sentimento_ia) }}
                </span>
            @else
                <span style="color: #64748b; font-style: italic;">Sem análise de IA</span>
            @endif
        </div>



        @if($reuniao->google_meet_link)
            <div style="margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                <strong>Link do Google Meet:</strong><br>
                <a href="{{ $reuniao->google_meet_link }}" target="_blank" style="color: #4285F4; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 6px; margin-top: 5px; font-weight: 600;">
                    <i class="fa-solid fa-video"></i> Acessar Sala do Google Meet
                </a>
            </div>
        @endif
</div>
@endsection

@if(auth()->id() === $reuniao->gestor_id)
@push('scripts')
<script>
    const URL_UPDATE_REGISTRO = '{{ route('reunioes.update_registro', $reuniao->id) }}';
    const CSRF_TOKEN = '{{ csrf_token() }}';
    
    // Lista de participantes da reunião para o select de novos/editados PDIs
    const PARTICIPANTES_LISTA = <?php echo json_encode($reuniao->feedback->participantes->map(function($p) {
        return ['id' => $p->id, 'name' => $p->name];
    })); ?>;

    // Inicializa a lista de PDIs a partir do banco
    let pdisData = <?php echo json_encode($reuniao->feedback->pdis->map(function($p) {
        return [
            'id' => $p->id,
            'colaborador_id' => $p->colaborador_id,
            'titulo' => $p->titulo,
            'descricao' => $p->descricao,
            'prazo' => $p->data_fim ? $p->data_fim->format('Y-m-d') : '',
            'status' => $p->status->value
        ];
    })); ?>;

    // Clonamos os dados iniciais para restaurar se o usuário cancelar
    const INITIAL_PDIS = JSON.parse(JSON.stringify(pdisData));
    const INITIAL_RESUMO = <?php echo json_encode($reuniao->feedback->resumo_ia); ?>;

    let deletarPdis = [];
    let isEditMode = false;

    function confirmarExclusaoReuniao() {
        showGlobalModal({
            title: 'Excluir Reunião',
            message: 'Tem certeza que deseja excluir esta reunião e todos os registros associados? Esta ação não pode ser desfeita.',
            type: 'danger',
            showCancel: true,
            confirmText: 'Sim, Excluir',
            cancelText: 'Cancelar',
            onConfirm: () => {
                document.getElementById('form-excluir-reuniao').submit();
            }
        });
    }

    // Inicializa a renderização em modo leitura
    document.addEventListener('DOMContentLoaded', () => {
        renderPdis();
    });

    function iniciarEdicao() {
        isEditMode = true;
        
        // Controla visibilidade de botões do cabeçalho
        document.getElementById('btn-iniciar-edicao').style.display = 'none';
        document.getElementById('btn-cancelar-edicao').style.display = 'inline-block';
        document.getElementById('btn-salvar-edicao').style.display = 'inline-block';

        // Alterna entre visualização e textarea do Resumo
        document.getElementById('resumo-visualizacao').style.display = 'none';
        document.getElementById('resumo-edicao').style.display = 'block';

        // Exibe o botão de Adicionar PDI
        document.getElementById('edicao-pdi-acoes').style.display = 'block';

        deletarPdis = [];
        renderPdis();
    }

    function cancelarEdicao() {
        isEditMode = false;

        // Controla visibilidade de botões do cabeçalho
        document.getElementById('btn-iniciar-edicao').style.display = 'inline-block';
        document.getElementById('btn-cancelar-edicao').style.display = 'none';
        document.getElementById('btn-salvar-edicao').style.display = 'none';

        // Alterna entre visualização e textarea do Resumo
        document.getElementById('resumo-visualizacao').style.display = 'block';
        document.getElementById('resumo-edicao').style.display = 'none';

        // Oculta o botão de Adicionar PDI
        document.getElementById('edicao-pdi-acoes').style.display = 'none';

        // Restaura valores originais
        document.getElementById('edit-resumo-texto').value = INITIAL_RESUMO;
        pdisData = JSON.parse(JSON.stringify(INITIAL_PDIS));
        deletarPdis = [];
        
        renderPdis();
    }

    function renderPdis() {
        const container = document.getElementById('pdis-container');
        const semPdis = document.getElementById('sem-pdis');
        container.innerHTML = '';

        if (!pdisData.length) {
            semPdis.style.display = 'block';
            return;
        }
        semPdis.style.display = 'none';

        pdisData.forEach((pdi, idx) => {
            if (isEditMode) {
                // MODO EDIÇÃO
                container.insertAdjacentHTML('beforeend', `
                    <div id="pdi-card-${idx}" style="border: 1px solid #e2e8f0; border-left: 4px solid var(--primary-color); border-radius: 8px; padding: 20px; background: #fafafa; position: relative;">
                        <button type="button" onclick="removerPdi(${idx})" style="position: absolute; top: 12px; right: 12px; background: #fef2f2; border: none; color: #ef4444; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; font-size: 0.85rem;" title="Excluir PDI">✕</button>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                            <div>
                                <label style="font-size: 0.8rem; color: #94a3b8; font-weight: 600;">COLABORADOR</label>
                                <select id="pdi-colab-${idx}" onchange="updatePdiField(${idx}, 'colaborador_id', this.value)" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.9rem; font-weight: 600; color: #334155;">
                                    ${PARTICIPANTES_LISTA.map(p => `<option value="${p.id}" ${p.id == pdi.colaborador_id ? 'selected' : ''}>${escapeHtml(p.name)}</option>`).join('')}
                                </select>
                            </div>
                            <div>
                                <label style="font-size: 0.8rem; color: #94a3b8; font-weight: 600;">PRAZO</label>
                                <input type="date" id="pdi-prazo-${idx}" value="${pdi.prazo || ''}" onchange="updatePdiField(${idx}, 'prazo', this.value)" style="width: 100%; padding: 6px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.9rem;">
                            </div>
                        </div>
                        <div style="margin-bottom: 10px;">
                            <label style="font-size: 0.8rem; color: #94a3b8; font-weight: 600;">TÍTULO</label>
                            <input type="text" id="pdi-titulo-${idx}" value="${escapeHtml(pdi.titulo || '')}" oninput="updatePdiField(${idx}, 'titulo', this.value)" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.95rem; box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="font-size: 0.8rem; color: #94a3b8; font-weight: 600;">DESCRIÇÃO</label>
                            <textarea id="pdi-desc-${idx}" rows="3" oninput="updatePdiField(${idx}, 'descricao', this.value)" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.9rem; resize: vertical; box-sizing: border-box;">${escapeHtml(pdi.descricao || '')}</textarea>
                        </div>
                        <div style="margin-top: 15px; border-top: 1px dashed #e2e8f0; padding-top: 15px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <label style="font-size: 0.85rem; color: #475569; font-weight: 600;"><i class="fa-solid fa-list-check"></i> Metas / Etapas (Ações)</label>
                                <button onclick="adicionarAcaoPdi(${idx})" type="button" style="background: #f1f5f9; border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 0.75rem;"><i class="fa-solid fa-plus"></i> Adicionar Etapa</button>
                            </div>
                            <div id="pdi-acoes-container-${idx}" style="display: flex; flex-direction: column; gap: 10px;">
                                ${(pdi.acoes || []).map((acao, aIdx) => `
                                    <div style="display: grid; grid-template-columns: 1fr auto; gap: 10px; background: #fff; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; align-items: start;">
                                        <div style="display: flex; flex-direction: column; gap: 8px;">
                                            <input type="text" id="pdi-${idx}-acao-titulo-${aIdx}" value="${escapeHtml(acao.titulo || '')}" oninput="updateAcaoField(${idx}, ${aIdx}, 'titulo', this.value)" placeholder="Título da etapa" style="width: 100%; padding: 6px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.85rem;">
                                            <input type="text" id="pdi-${idx}-acao-desc-${aIdx}" value="${escapeHtml(acao.descricao || '')}" oninput="updateAcaoField(${idx}, ${aIdx}, 'descricao', this.value)" placeholder="Descrição (Opcional)" style="width: 100%; padding: 6px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.85rem;">
                                        </div>
                                        <button onclick="removerAcaoPdi(${idx}, ${aIdx})" type="button" style="background: #fef2f2; border: none; color: #ef4444; width: 26px; height: 26px; border-radius: 4px; cursor: pointer; font-size: 0.8rem;" title="Remover etapa"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                `).join('')}
                                ${!(pdi.acoes && pdi.acoes.length) ? '<p style="font-size: 0.8rem; color: #94a3b8; font-style: italic; margin: 0;">Nenhuma etapa cadastrada. O PDI ficará com 0% se não tiver etapas.</p>' : ''}
                            </div>
                        </div>
                    </div>
                `);
            } else {
                // MODO LEITURA
                const colab = PARTICIPANTES_LISTA.find(p => p.id == pdi.colaborador_id) || { name: 'Desconhecido' };
                const dataFormatada = pdi.prazo ? formatarDataBr(pdi.prazo) : 'Não definido';
                
                const acoesHtml = (pdi.acoes && pdi.acoes.length > 0) ? `
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
                        <h5 style="margin: 0 0 10px 0; font-size: 0.85rem; color: #475569;"><i class="fa-solid fa-list-check"></i> Etapas (${pdi.acoes.length})</h5>
                        <ul style="margin: 0; padding-left: 20px; color: #64748b; font-size: 0.85rem;">
                            ${pdi.acoes.map(a => `<li><strong>${escapeHtml(a.titulo)}</strong>${a.descricao ? ' - ' + escapeHtml(a.descricao) : ''}</li>`).join('')}
                        </ul>
                    </div>
                ` : '';

                container.insertAdjacentHTML('beforeend', `
                    <div style="background: #fafafa; border: 1px solid #e2e8f0; border-left: 4px solid var(--primary-color); padding: 18px; border-radius: 8px; position: relative;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                            <h4 style="margin: 0; color: #334155; font-size: 1.05rem; font-weight: bold;">${escapeHtml(pdi.titulo || 'Sem título')}</h4>
                            <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.75rem; padding: 4px 8px; border-radius: 4px;">
                                ${escapeHtml(pdi.status ? pdi.status.replace('_', ' ').toUpperCase() : 'NÃO INICIADO')}
                            </span>
                        </div>
                        <p style="color: #64748b; font-size: 0.9rem; margin: 0 0 10px 0;">${escapeHtml(pdi.descricao || 'Sem descrição.').replace(/\n/g, '<br>')}</p>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 8px;">
                            <span><strong>Colaborador:</strong> ${escapeHtml(colab.name)}</span>
                            <span><strong>Prazo:</strong> ${dataFormatada}</span>
                        </div>
                        ${acoesHtml}
                    </div>
                `);
            }
        });
    }

    function adicionarPdi() {
        const primeiro = PARTICIPANTES_LISTA[0] || {};
        pdisData.push({
            id: '',
            colaborador_id: primeiro.id || '',
            titulo: '',
            descricao: '',
            prazo: '',
            status: 'nao_iniciado',
            acoes: []
        });
        renderPdis();
    }

    function removerPdi(idx) {
        showGlobalModal({
            title: 'Excluir PDI',
            message: 'Tem certeza que deseja excluir este Plano de Desenvolvimento Individual (PDI)? O item será deletado definitivamente ao salvar as alterações.',
            type: 'danger',
            showCancel: true,
            confirmText: 'Sim, Excluir',
            cancelText: 'Cancelar',
            onConfirm: () => {
                const pdi = pdisData[idx];
                if (pdi && pdi.id) {
                    deletarPdis.push(pdi.id);
                }
                pdisData.splice(idx, 1);
                renderPdis();
            }
        });
    }

    function updatePdiField(idx, field, value) {
        if (pdisData[idx]) {
            pdisData[idx][field] = value;
        }
    }

    function updateAcaoField(pdiIdx, acaoIdx, field, value) {
        if (pdisData[pdiIdx] && pdisData[pdiIdx].acoes && pdisData[pdiIdx].acoes[acaoIdx]) {
            pdisData[pdiIdx].acoes[acaoIdx][field] = value;
        }
    }

    function adicionarAcaoPdi(idx) {
        if (pdisData[idx]) {
            if (!pdisData[idx].acoes) pdisData[idx].acoes = [];
            pdisData[idx].acoes.push({ id: '', titulo: '', descricao: '' });
            renderPdis();
        }
    }

    function removerAcaoPdi(pdiIdx, acaoIdx) {
        if (pdisData[pdiIdx] && pdisData[pdiIdx].acoes) {
            // Note: deletar acao might not be supported individually on backend yet,
            // but the backend uses whereNotIn so it deletes those not passed!
            pdisData[pdiIdx].acoes.splice(acaoIdx, 1);
            renderPdis();
        }
    }

    async function salvarEdicao() {
        const btnSalvar = document.getElementById('btn-salvar-edicao');
        const btnCancelar = document.getElementById('btn-cancelar-edicao');
        
        btnSalvar.disabled = true;
        btnSalvar.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Salvando...';

        const resumoText = document.getElementById('edit-resumo-texto').value.trim();
        if (!resumoText) {
            alert('O resumo não pode ficar em branco.');
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = '<i class="fa-solid fa-check"></i> Salvar Alterações';
            return;
        }

        // Valida se todos os PDIs possuem título
        for (let i = 0; i < pdisData.length; i++) {
            if (!pdisData[i].titulo.trim()) {
                alert(`Por favor, preencha o título do PDI #${i+1}`);
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = '<i class="fa-solid fa-check"></i> Salvar Alterações';
                return;
            }
        }

        const body = new FormData();
        body.append('_token', CSRF_TOKEN);
        body.append('resumo', resumoText);
        
        pdisData.forEach((pdi, i) => {
            if (pdi.id) body.append(`pdis[${i}][id]`, pdi.id);
            body.append(`pdis[${i}][colaborador_id]`, pdi.colaborador_id);
            body.append(`pdis[${i}][titulo]`, pdi.titulo);
            body.append(`pdis[${i}][descricao]`, pdi.descricao || '');
            if (pdi.prazo) body.append(`pdis[${i}][prazo]`, pdi.prazo);
            
            if (pdi.acoes) {
                pdi.acoes.filter(a => a.titulo && a.titulo.trim() !== '').forEach((acao, a) => {
                    if (acao.id) body.append(`pdis[${i}][acoes][${a}][id]`, acao.id);
                    body.append(`pdis[${i}][acoes][${a}][titulo]`, acao.titulo);
                    body.append(`pdis[${i}][acoes][${a}][descricao]`, acao.descricao || '');
                });
            }
        });

        deletarPdis.forEach(id => body.append('deletar_pdis[]', id));

        try {
            const resp = await fetch(URL_UPDATE_REGISTRO, { method: 'POST', body });
            const data = await resp.json();

            if (!resp.ok || !data.success) {
                alert(data.error || 'Ocorreu um erro ao atualizar o registro.');
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = '<i class="fa-solid fa-check"></i> Salvar Alterações';
                return;
            }

            // Sucesso! Recarrega a página para refletir as alterações no backend
            window.location.reload();

        } catch (e) {
            alert('Erro de conexão ao tentar salvar as alterações.');
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = '<i class="fa-solid fa-check"></i> Salvar Alterações';
        }
    }

    function formatarDataBr(dataIso) {
        if (!dataIso) return '';
        const parts = dataIso.split('-');
        if (parts.length !== 3) return dataIso;
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
</script>
@endpush
@endif
