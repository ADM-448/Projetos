@extends('layouts.app')

@section('title', 'Criar Novo PDI')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title">Criar Novo PDI</h1>
        <p style="color: #666; font-size: 0.95rem;">Defina metas, prazos e ações de desenvolvimento individual ou em grupo.</p>
    </div>
    <div>
        <a href="{{ route('pdis.index') }}" class="btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<form action="{{ route('pdis.store') }}" method="POST" id="form-pdi">
    @csrf

    <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 20px;">
        
        <!-- CARD PRINCIPAL: DADOS DO PDI -->
        <div class="card" style="grid-column: span 2; display: flex; flex-direction: column; gap: 20px;">
            <h3 style="margin: 0 0 10px 0; color: #2d3748; border-bottom: 1px solid #edf2f7; padding-bottom: 10px;">
                <i class="fa-solid fa-file-invoice" style="color: #6a4c7c;"></i> Informações Gerais
            </h3>

            <!-- Tipo de Criação -->
            <div>
                <label class="form-label" style="font-weight: bold; margin-bottom: 10px; display: block;">Como deseja criar este PDI?</label>
                <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600;">
                        <input type="radio" name="tipo_criacao" value="individual" checked onclick="alternarTipoCriacao('individual')" style="width: 18px; height: 18px; accent-color: #6a4c7c;">
                        Para um colaborador (Individual)
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600;">
                        <input type="radio" name="tipo_criacao" value="grupo" onclick="alternarTipoCriacao('grupo')" style="width: 18px; height: 18px; accent-color: #6a4c7c;">
                        Para múltiplos colaboradores (Em Grupo / Lote)
                    </label>
                </div>
            </div>

            <!-- Seleção de Colaboradores -->
            <div id="wrapper-individual" class="form-group">
                <label class="form-label" for="colaborador_id">Colaborador <span style="color: red;">*</span></label>
                <select name="colaborador_id" id="colaborador_id" class="form-control">
                    <option value="">Selecione o colaborador...</option>
                    @foreach($colaboradores as $colab)
                        <option value="{{ $colab->id }}">{{ $colab->name }} ({{ $colab->cargo->nome ?? 'Sem cargo' }})</option>
                    @endforeach
                </select>
                @error('colaborador_id')
                    <span style="color: #e53e3e; font-size: 0.85rem;">{{ $message }}</span>
                @enderror
            </div>

            <div id="wrapper-grupo" class="form-group" style="display: none;">
                <label class="form-label" for="colaboradores">Colaboradores no Grupo <span style="color: red;">*</span></label>
                <select name="colaboradores[]" id="colaboradores" class="form-control" multiple>
                    @foreach($colaboradores as $colab)
                        <option value="{{ $colab->id }}">{{ $colab->name }} ({{ $colab->cargo->nome ?? 'Sem cargo' }})</option>
                    @endforeach
                </select>
                @error('colaboradores')
                    <span style="color: #e53e3e; font-size: 0.85rem;">{{ $message }}</span>
                @enderror
                <p style="color: #718096; font-size: 0.8rem; margin-top: 5px;">Cada colaborador selecionado receberá um PDI idêntico em sua lista.</p>
            </div>

            <!-- Título e Categoria -->
            <div class="form-row">
                <div class="form-group" style="flex: 2; margin-bottom: 0;">
                    <label class="form-label" for="titulo">Título do PDI <span style="color: red;">*</span></label>
                    <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Ex: Aprimoramento de Liderança e Comunicação" required>
                    @error('titulo')
                        <span style="color: #e53e3e; font-size: 0.85rem;">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group" style="flex: 1; margin-bottom: 0;">
                    <label class="form-label" for="categoria">Categoria</label>
                    <select id="categoria" name="categoria" class="form-control">
                        <option value="Técnico">Técnico (Hard Skills)</option>
                        <option value="Comportamental" selected>Comportamental (Soft Skills)</option>
                        <option value="Liderança">Liderança / Gestão</option>
                        <option value="Outros">Outros</option>
                    </select>
                    @error('categoria')
                        <span style="color: #e53e3e; font-size: 0.85rem;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Prazos -->
            <div class="form-row">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="data_inicio">Data de Início</label>
                    <input type="date" id="data_inicio" name="data_inicio" class="form-control">
                    @error('data_inicio')
                        <span style="color: #e53e3e; font-size: 0.85rem;">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="data_fim">Data Limite de Término</label>
                    <input type="date" id="data_fim" name="data_fim" class="form-control">
                    @error('data_fim')
                        <span style="color: #e53e3e; font-size: 0.85rem;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Descrição Geral -->
            <div class="form-group">
                <label class="form-label" for="descricao">Objetivo Geral do PDI</label>
                <textarea id="descricao" name="descricao" class="form-control" rows="3" placeholder="Descreva brevemente o propósito geral do plano de desenvolvimento..."></textarea>
                @error('descricao')
                    <span style="color: #e53e3e; font-size: 0.85rem;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- CARD DE AÇÕES (METAS) -->
        <div class="card" style="grid-column: span 2; display: flex; flex-direction: column; gap: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #edf2f7; padding-bottom: 10px;">
                <h3 style="margin: 0; color: #2d3748;">
                    <i class="fa-solid fa-list-check" style="color: #6a4c7c;"></i> Ações e Metas Específicas <span style="color: red;">*</span>
                </h3>
                <button type="button" class="btn-secondary" onclick="adicionarAcao()" style="font-size: 0.9rem; padding: 6px 15px;">
                    <i class="fa-solid fa-plus"></i> Adicionar Ação
                </button>
            </div>

            <!-- Container das Ações -->
            <div id="acoes-container" style="display: flex; flex-direction: column; gap: 15px;">
                <!-- Primeira Ação (Default) -->
                <div class="acao-row form-row" data-index="0" style="background: #f7fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; align-items: flex-start; margin-bottom: 0;">
                    <div style="display: flex; flex-direction: column; gap: 10px; flex: 1;">
                        <div>
                            <label class="form-label" style="font-size: 0.85rem; font-weight: bold;">Título da Ação/Meta <span style="color: red;">*</span></label>
                            <input type="text" name="acoes[0][titulo]" class="form-control" placeholder="Ex: Realizar o curso XPTO de Comunicação Assertiva" required>
                        </div>
                        <div>
                            <label class="form-label" style="font-size: 0.85rem;">Como realizar / Critério de sucesso (Opcional)</label>
                            <textarea name="acoes[0][descricao]" class="form-control" rows="2" placeholder="Ex: Assistir todas as aulas e aplicar a técnica de escuta ativa nas reuniões de departamento."></textarea>
                        </div>
                    </div>
                    <div style="padding-top: 25px;">
                        <button type="button" class="btn-danger btn-remove-acao" onclick="removerAcao(this)" style="padding: 10px 14px; background-color: #e74c3c; display: none;">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            @error('acoes')
                <span style="color: #e53e3e; font-size: 0.85rem;">{{ $message }}</span>
            @enderror
        </div>

    </div>

    <!-- SUBMIT -->
    <div style="margin-top: 20px; text-align: right; margin-bottom: 50px;">
        <button type="submit" class="btn-primary" style="font-size: 1.1rem; padding: 12px 30px;">
            <i class="fa-solid fa-floppy-disk"></i> Salvar e Atribuir PDI
        </button>
    </div>

</form>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<style>
    .choices__inner {
        border-radius: 8px;
        background-color: #fff;
        border: 1px solid #e2e8f0;
    }
    .choices__list--multiple .choices__item {
        background-color: #6a4c7c;
        border: 1px solid #6a4c7c;
        border-radius: 4px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
    let choicesColaborador, choicesGrupo;
    let acaoIndex = 1;

    document.addEventListener('DOMContentLoaded', function() {
        // Inicializa Choices.js
        choicesColaborador = new Choices('#colaborador_id', { removeItemButton: true, searchEnabled: true, itemSelectText: '', placeholderValue: 'Pesquise o colaborador...' });
        choicesGrupo = new Choices('#colaboradores', { removeItemButton: true, searchEnabled: true, itemSelectText: '', placeholderValue: 'Selecione um ou mais colaboradores...' });

        // Inicializa o FormDraft para salvar rascunhos locais automaticamente
        inicializarRascunho();
    });

    /** Alternar exibição de campos com base no tipo de criação */
    function alternarTipoCriacao(tipo) {
        const wrapperIndividual = document.getElementById('wrapper-individual');
        const wrapperGrupo = document.getElementById('wrapper-grupo');

        if (tipo === 'individual') {
            wrapperIndividual.style.display = 'block';
            wrapperGrupo.style.display = 'none';
            document.getElementById('colaboradores').removeAttribute('required');
            document.getElementById('colaborador_id').setAttribute('required', 'required');
        } else {
            wrapperIndividual.style.display = 'none';
            wrapperGrupo.style.display = 'block';
            document.getElementById('colaborador_id').removeAttribute('required');
            document.getElementById('colaboradores').setAttribute('required', 'required');
        }
    }

    let isAdding = false;
    /** Adiciona uma nova linha de ação no repetidor */
    function adicionarAcao() {
        if (isAdding) return;
        isAdding = true;

        const container = document.getElementById('acoes-container');

        const acaoHTML = `
            <div class="acao-row form-row" data-index="${acaoIndex}" style="background: #f7fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; align-items: flex-start; margin-bottom: 0; animation: fadeIn 0.3s ease;">
                <div style="display: flex; flex-direction: column; gap: 10px; flex: 1;">
                    <div>
                        <label class="form-label" style="font-size: 0.85rem; font-weight: bold;">Título da Ação/Meta <span style="color: red;">*</span></label>
                        <input type="text" name="acoes[${acaoIndex}][titulo]" class="form-control" placeholder="Ex: Realizar o curso XPTO de Comunicação Assertiva" required>
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.85rem;">Como realizar / Critério de sucesso (Opcional)</label>
                        <textarea name="acoes[${acaoIndex}][descricao]" class="form-control" rows="2" placeholder="Ex: Assistir todas as aulas e aplicar a técnica de escuta ativa nas reuniões de departamento."></textarea>
                    </div>
                </div>
                <div style="padding-top: 25px;">
                    <button type="button" class="btn-danger btn-remove-acao" onclick="removerAcao(this)" style="padding: 10px 14px; background-color: #e74c3c;">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', acaoHTML);
        acaoIndex++;
        verificarBotoesRemocao();

        setTimeout(() => isAdding = false, 300);
    }

    /** Remove a ação selecionada */
    function removerAcao(button) {
        button.closest('.acao-row').remove();
        verificarBotoesRemocao();
    }

    /** Controla exibição dos botões de exclusão das ações */
    function verificarBotoesRemocao() {
        const rows = document.querySelectorAll('.acao-row');
        const buttons = document.querySelectorAll('.btn-remove-acao');

        if (rows.length <= 1) {
            buttons.forEach(btn => btn.style.display = 'none');
        } else {
            buttons.forEach(btn => btn.style.display = 'block');
        }
    }

    // ─────────────────────────── FormDraft Helper ───────────────────────────────
    
    const STORAGE_KEY = 'draft_pdi';

    function inicializarRascunho() {
        const form = document.getElementById('form-pdi');
        if (!form) return;

        // 1. Restaurar campos do localStorage
        restaurarRascunho(form);

        // 2. Ouvir alterações e gravar
        form.addEventListener('input', () => salvarRascunho(form));
        form.addEventListener('change', () => salvarRascunho(form));

        // 3. Limpar ao fazer submit
        form.addEventListener('submit', function() {
            localStorage.removeItem(STORAGE_KEY);
        });
    }

    function salvarRascunho(form) {
        const data = {};
        
        // Dados básicos
        data['tipo_criacao'] = form.querySelector('input[name="tipo_criacao"]:checked')?.value || 'individual';
        data['titulo'] = form.querySelector('[name="titulo"]').value;
        data['categoria'] = form.querySelector('[name="categoria"]').value;
        data['data_inicio'] = form.querySelector('[name="data_inicio"]').value;
        data['data_fim'] = form.querySelector('[name="data_fim"]').value;
        data['descricao'] = form.querySelector('[name="descricao"]').value;

        // Ações/Metas
        const acoesData = [];
        form.querySelectorAll('.acao-row').forEach(row => {
            const index = row.getAttribute('data-index');
            const titulo = row.querySelector(`[name="acoes[${index}][titulo]"]`)?.value || '';
            const descricao = row.querySelector(`[name="acoes[${index}][descricao]"]`)?.value || '';
            
            if (titulo || descricao) {
                acoesData.push({ titulo, descricao });
            }
        });
        data['acoes'] = acoesData;

        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    }

    function restaurarRascunho(form) {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (!saved) return;

        try {
            const data = JSON.parse(saved);

            // Tipo de criação
            if (data.tipo_criacao) {
                const radio = form.querySelector(`input[name="tipo_criacao"][value="${data.tipo_criacao}"]`);
                if (radio) {
                    radio.checked = true;
                    alternarTipoCriacao(data.tipo_criacao);
                }
            }

            // Dados básicos
            if (data.titulo) form.querySelector('[name="titulo"]').value = data.titulo;
            if (data.categoria) form.querySelector('[name="categoria"]').value = data.categoria;
            if (data.data_inicio) form.querySelector('[name="data_inicio"]').value = data.data_inicio;
            if (data.data_fim) form.querySelector('[name="data_fim"]').value = data.data_fim;
            if (data.descricao) form.querySelector('[name="descricao"]').value = data.descricao;

            // Restaurar Ações
            if (data.acoes && data.acoes.length > 0) {
                const container = document.getElementById('acoes-container');
                container.innerHTML = ''; // Limpa o default
                
                data.acoes.forEach((item, idx) => {
                    const acaoHTML = `
                        <div class="acao-row" data-index="${idx}" style="background: #f7fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; display: grid; grid-template-columns: 1fr auto; gap: 15px; align-items: start;">
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <div>
                                    <label class="form-label" style="font-size: 0.85rem; font-weight: bold;">Título da Ação/Meta <span style="color: red;">*</span></label>
                                    <input type="text" name="acoes[${idx}][titulo]" class="form-control" value="${item.titulo}" placeholder="Ex: Realizar o curso XPTO de Comunicação Assertiva" required>
                                </div>
                                <div>
                                    <label class="form-label" style="font-size: 0.85rem;">Como realizar / Critério de sucesso (Opcional)</label>
                                    <textarea name="acoes[${idx}][descricao]" class="form-control" rows="2" placeholder="Ex: Assistir todas as aulas e aplicar a técnica de escuta ativa nas reuniões de departamento.">${item.descricao}</textarea>
                                </div>
                            </div>
                            <div style="padding-top: 25px;">
                                <button type="button" class="btn-danger btn-remove-acao" onclick="removerAcao(this)" style="padding: 10px 14px; background-color: #e74c3c;">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', acaoHTML);
                });
                
                acaoIndex = data.acoes.length;
                verificarBotoesRemocao();
            }

            // Banner informativo
            exibirBannerRascunho(form);

        } catch (e) {
            localStorage.removeItem(STORAGE_KEY);
        }
    }

    function exibirBannerRascunho(form) {
        const banner = document.createElement('div');
        banner.style.background = '#eef2ff';
        banner.style.border = '1px solid #c7d2fe';
        banner.style.color = '#4338ca';
        banner.style.padding = '12px 20px';
        banner.style.borderRadius = '8px';
        banner.style.marginBottom = '20px';
        banner.style.display = 'flex';
        banner.style.justifyContent = 'space-between';
        banner.style.alignItems = 'center';
        banner.style.fontSize = '0.95rem';
        banner.id = 'rascunho-banner';

        banner.innerHTML = `
            <span>📝 Rascunho do PDI carregado automaticamente.</span>
            <button type="button" onclick="descartarRascunho()" style="background: white; border: 1px solid #c7d2fe; color: #4338ca; padding: 6px 12px; border-radius: 6px; font-weight: bold; cursor: pointer;">Descartar rascunho</button>
        `;

        form.insertAdjacentElement('beforebegin', banner);
    }

    function descartarRascunho() {
        localStorage.removeItem(STORAGE_KEY);
        document.getElementById('rascunho-banner')?.remove();
        window.location.reload();
    }
</script>
@endpush
