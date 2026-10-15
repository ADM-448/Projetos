@extends('layouts.app')

@section('title', 'Criar Novo PDI')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<style>
    :root {
        --color-primary: #4f46e5;
        --color-primary-hover: #3730a3;
        --color-accent: #6d28d9;
        --color-slate-50: #f8fafc;
        --color-slate-100: #f1f5f9;
        --color-slate-500: #64748b;
        --color-slate-900: #0f172a;
        --radius-card: 16px;
        --radius-element: 10px;
        --radius-pill: 99px;
        --shadow-card: 0 4px 16px rgba(0,0,0,.08);
        --border-soft: 1px solid #e9eaf0;
        --font-main: 'Inter', system-ui, sans-serif;
    }

    .pdi-page {
        font-family: var(--font-main);
        background: var(--color-slate-50);
        min-height: 100vh;
        padding: 24px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 32px;
    }

    .form-card {
        background: #fff;
        border-radius: var(--radius-card);
        padding: 28px 32px;
        box-shadow: var(--shadow-card);
        border: var(--border-soft);
        margin-bottom: 24px;
    }

    .form-card h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--color-slate-900);
        margin: 0 0 20px 0;
        padding-bottom: 14px;
        border-bottom: var(--border-soft);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-label {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--color-slate-900);
        margin-bottom: 8px;
        display: block;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border-radius: var(--radius-element);
        border: var(--border-soft);
        background: #fff;
        font-size: 0.95rem;
        color: var(--color-slate-900);
        font-family: var(--font-main);
        transition: all 0.2s;
        box-sizing: border-box;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .acao-row {
        background: var(--color-slate-50);
        padding: 18px 20px;
        border-radius: var(--radius-element);
        border: var(--border-soft);
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 16px;
        align-items: start;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: var(--radius-pill);
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        border: none;
        font-family: var(--font-main);
    }

    .btn-primary {
        background: var(--color-primary);
        color: #fff;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
    }

    .btn-primary:hover {
        background: var(--color-primary-hover);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
    }

    .btn-secondary {
        background: var(--color-slate-50);
        color: var(--color-slate-900);
        border: var(--border-soft);
    }

    .btn-secondary:hover {
        background: var(--color-slate-100);
        transform: translateY(-1px);
    }

    .btn-danger-icon {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.2);
        border-radius: var(--radius-element);
        padding: 10px 14px;
        cursor: pointer;
        transition: all 0.2s;
        font-family: var(--font-main);
    }

    .btn-danger-icon:hover {
        background: rgba(239, 68, 68, 0.18);
    }

    .tipo-radio-label {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-weight: 600;
        padding: 12px 20px;
        border-radius: var(--radius-element);
        border: var(--border-soft);
        transition: all 0.2s;
        font-size: 0.95rem;
        color: var(--color-slate-900);
    }

    .tipo-radio-label:has(input:checked) {
        border-color: var(--color-primary);
        background: rgba(79, 70, 229, 0.05);
        color: var(--color-primary);
    }

    .tipo-radio-label input {
        accent-color: var(--color-primary);
        width: 18px;
        height: 18px;
    }

    .banner-rascunho {
        background: rgba(79, 70, 229, 0.06);
        border: 1px solid rgba(79, 70, 229, 0.2);
        color: var(--color-primary);
        padding: 14px 20px;
        border-radius: var(--radius-element);
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.95rem;
        font-family: var(--font-main);
    }

    .error-msg {
        color: #dc2626;
        font-size: 0.85rem;
        margin-top: 5px;
        display: block;
    }
</style>

<div class="pdi-page">
    <div class="page-header">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--color-slate-900); margin: 0 0 4px 0; letter-spacing: -0.02em; font-family: var(--font-main);">Criar Novo PDI</h1>
            <p style="color: var(--color-slate-500); font-size: 0.95rem; margin: 0; font-family: var(--font-main);">Defina metas, prazos e ações de desenvolvimento individual ou em grupo.</p>
        </div>
        <a href="{{ route('pdis.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Voltar para PDIs
        </a>
    </div>

    <form action="{{ route('pdis.store') }}" method="POST" id="form-pdi">
        @csrf

        <div style="display: flex; flex-direction: column; gap: 0;">

            <!-- CARD PRINCIPAL -->
            <div class="form-card">
                <h3><i class="fa-solid fa-file-invoice" style="color: var(--color-primary);"></i> Informações Gerais</h3>

                <!-- Tipo de Criação -->
                <div style="margin-bottom: 24px;">
                    <label class="form-label">Como deseja criar este PDI?</label>
                    <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                        <label class="tipo-radio-label">
                            <input type="radio" name="tipo_criacao" value="individual" checked onclick="alternarTipoCriacao('individual')">
                            <i class="fa-solid fa-user"></i> Para um colaborador (Individual)
                        </label>
                        <label class="tipo-radio-label">
                            <input type="radio" name="tipo_criacao" value="grupo" onclick="alternarTipoCriacao('grupo')">
                            <i class="fa-solid fa-people-group"></i> Para múltiplos colaboradores (Em Grupo / Lote)
                        </label>
                    </div>
                </div>

                <!-- Seleção de Colaboradores -->
                <div id="wrapper-individual" class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" for="colaborador_id">Colaborador <span style="color: #dc2626;">*</span></label>
                    <select name="colaborador_id" id="colaborador_id" class="form-control">
                        <option value="">Selecione o colaborador...</option>
                        @foreach($colaboradores as $colab)
                            <option value="{{ $colab->id }}">{{ $colab->name }} ({{ $colab->cargo->nome ?? 'Sem cargo' }})</option>
                        @endforeach
                    </select>
                    @error('colaborador_id') <span class="error-msg">{{ $message }}</span> @enderror
                </div>

                <div id="wrapper-grupo" class="form-group" style="display: none; margin-bottom: 20px;">
                    <label class="form-label" for="colaboradores">Colaboradores no Grupo <span style="color: #dc2626;">*</span></label>
                    <select name="colaboradores[]" id="colaboradores" class="form-control" multiple>
                        @foreach($colaboradores as $colab)
                            <option value="{{ $colab->id }}">{{ $colab->name }} ({{ $colab->cargo->nome ?? 'Sem cargo' }})</option>
                        @endforeach
                    </select>
                    @error('colaboradores') <span class="error-msg">{{ $message }}</span> @enderror
                    <p style="color: var(--color-slate-500); font-size: 0.85rem; margin-top: 8px;">Cada colaborador selecionado receberá um PDI idêntico em sua lista.</p>
                </div>

                <!-- Título e Categoria -->
                <div class="form-row" style="gap: 20px; margin-bottom: 20px;">
                    <div class="form-group" style="flex: 2; margin-bottom: 0;">
                        <label class="form-label" for="titulo">Título do PDI <span style="color: #dc2626;">*</span></label>
                        <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Ex: Aprimoramento de Liderança e Comunicação" required>
                        @error('titulo') <span class="error-msg">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <label class="form-label" for="categoria">Categoria</label>
                        <select id="categoria" name="categoria" class="form-control">
                            <option value="Técnico">Técnico (Hard Skills)</option>
                            <option value="Comportamental" selected>Comportamental (Soft Skills)</option>
                            <option value="Liderança">Liderança / Gestão</option>
                            <option value="Outros">Outros</option>
                        </select>
                        @error('categoria') <span class="error-msg">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Prazos -->
                <div class="form-row" style="gap: 20px; margin-bottom: 20px;">
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <label class="form-label" for="data_inicio">Data de Início</label>
                        <input type="date" id="data_inicio" name="data_inicio" class="form-control">
                        @error('data_inicio') <span class="error-msg">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <label class="form-label" for="data_fim">Data Limite de Término</label>
                        <input type="date" id="data_fim" name="data_fim" class="form-control">
                        @error('data_fim') <span class="error-msg">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Descrição Geral -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="descricao">Objetivo Geral do PDI</label>
                    <textarea id="descricao" name="descricao" class="form-control" rows="3" placeholder="Descreva brevemente o propósito geral do plano de desenvolvimento..."></textarea>
                    @error('descricao') <span class="error-msg">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- CARD DE AÇÕES (METAS) -->
            <div class="form-card">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: var(--border-soft); padding-bottom: 14px; margin-bottom: 20px;">
                    <h3 style="border: none; padding: 0; margin: 0;">
                        <i class="fa-solid fa-list-check" style="color: var(--color-primary);"></i> Ações e Metas Específicas <span style="color: #dc2626;">*</span>
                    </h3>
                    <button type="button" class="btn btn-secondary" onclick="adicionarAcao()" style="padding: 8px 18px; font-size: 0.88rem;">
                        <i class="fa-solid fa-plus"></i> Adicionar Ação
                    </button>
                </div>

                <div id="acoes-container" style="display: flex; flex-direction: column; gap: 14px;">
                    <!-- Primeira Ação (Default) -->
                    <div class="acao-row" data-index="0">
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <div>
                                <label class="form-label" style="font-size: 0.85rem;">Título da Ação/Meta <span style="color: #dc2626;">*</span></label>
                                <input type="text" name="acoes[0][titulo]" class="form-control" placeholder="Ex: Realizar o curso XPTO de Comunicação Assertiva" required>
                            </div>
                            <div>
                                <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Como realizar / Critério de sucesso (Opcional)</label>
                                <textarea name="acoes[0][descricao]" class="form-control" rows="2" placeholder="Ex: Assistir todas as aulas e aplicar a técnica de escuta ativa nas reuniões."></textarea>
                            </div>
                        </div>
                        <div style="padding-top: 26px;">
                            <button type="button" class="btn-danger-icon btn-remove-acao" onclick="removerAcao(this)" style="display: none;">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>

                @error('acoes') <span class="error-msg" style="margin-top: 10px;">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- SUBMIT -->
        <div style="margin-top: 8px; text-align: right; margin-bottom: 50px;">
            <button type="submit" class="btn btn-primary" style="font-size: 1rem; padding: 14px 32px;">
                <i class="fa-solid fa-floppy-disk"></i> Salvar e Atribuir PDI
            </button>
        </div>

    </form>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<style>
    .choices__inner { border-radius: var(--radius-element); background-color: #fff; border: var(--border-soft); }
    .choices__list--multiple .choices__item { background-color: var(--color-primary); border: 1px solid var(--color-primary); border-radius: 6px; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
    let choicesColaborador, choicesGrupo;
    let acaoIndex = 1;

    document.addEventListener('DOMContentLoaded', function() {
        choicesColaborador = new Choices('#colaborador_id', { removeItemButton: true, searchEnabled: true, itemSelectText: '', placeholderValue: 'Pesquise o colaborador...' });
        choicesGrupo = new Choices('#colaboradores', { removeItemButton: true, searchEnabled: true, itemSelectText: '', placeholderValue: 'Selecione um ou mais colaboradores...' });
        inicializarRascunho();
    });

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
    function adicionarAcao() {
        if (isAdding) return;
        isAdding = true;

        const container = document.getElementById('acoes-container');
        const acaoHTML = `
            <div class="acao-row" data-index="${acaoIndex}">
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div>
                        <label class="form-label" style="font-size: 0.85rem;">Título da Ação/Meta <span style="color: #dc2626;">*</span></label>
                        <input type="text" name="acoes[${acaoIndex}][titulo]" class="form-control" placeholder="Ex: Realizar o curso XPTO de Comunicação Assertiva" required>
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Como realizar / Critério de sucesso (Opcional)</label>
                        <textarea name="acoes[${acaoIndex}][descricao]" class="form-control" rows="2" placeholder="Ex: Assistir todas as aulas e aplicar a técnica de escuta ativa nas reuniões."></textarea>
                    </div>
                </div>
                <div style="padding-top: 26px;">
                    <button type="button" class="btn-danger-icon btn-remove-acao" onclick="removerAcao(this)">
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

    function removerAcao(button) {
        button.closest('.acao-row').remove();
        verificarBotoesRemocao();
    }

    function verificarBotoesRemocao() {
        const rows = document.querySelectorAll('.acao-row');
        const buttons = document.querySelectorAll('.btn-remove-acao');
        if (rows.length <= 1) {
            buttons.forEach(btn => btn.style.display = 'none');
        } else {
            buttons.forEach(btn => btn.style.display = 'flex');
        }
    }

    // ─── FormDraft Helper ─────────────────────────────
    const STORAGE_KEY = 'draft_pdi';

    function inicializarRascunho() {
        const form = document.getElementById('form-pdi');
        if (!form) return;
        restaurarRascunho(form);
        form.addEventListener('input', () => salvarRascunho(form));
        form.addEventListener('change', () => salvarRascunho(form));
        form.addEventListener('submit', function() { localStorage.removeItem(STORAGE_KEY); });
    }

    function salvarRascunho(form) {
        const data = {};
        data['tipo_criacao'] = form.querySelector('input[name="tipo_criacao"]:checked')?.value || 'individual';
        data['titulo'] = form.querySelector('[name="titulo"]').value;
        data['categoria'] = form.querySelector('[name="categoria"]').value;
        data['data_inicio'] = form.querySelector('[name="data_inicio"]').value;
        data['data_fim'] = form.querySelector('[name="data_fim"]').value;
        data['descricao'] = form.querySelector('[name="descricao"]').value;

        const acoesData = [];
        form.querySelectorAll('.acao-row').forEach(row => {
            const index = row.getAttribute('data-index');
            const titulo = row.querySelector(`[name="acoes[${index}][titulo]"]`)?.value || '';
            const descricao = row.querySelector(`[name="acoes[${index}][descricao]"]`)?.value || '';
            if (titulo || descricao) acoesData.push({ titulo, descricao });
        });
        data['acoes'] = acoesData;
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    }

    function restaurarRascunho(form) {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (!saved) return;

        try {
            const data = JSON.parse(saved);
            if (data.tipo_criacao) {
                const radio = form.querySelector(`input[name="tipo_criacao"][value="${data.tipo_criacao}"]`);
                if (radio) { radio.checked = true; alternarTipoCriacao(data.tipo_criacao); }
            }
            if (data.titulo) form.querySelector('[name="titulo"]').value = data.titulo;
            if (data.categoria) form.querySelector('[name="categoria"]').value = data.categoria;
            if (data.data_inicio) form.querySelector('[name="data_inicio"]').value = data.data_inicio;
            if (data.data_fim) form.querySelector('[name="data_fim"]').value = data.data_fim;
            if (data.descricao) form.querySelector('[name="descricao"]').value = data.descricao;

            if (data.acoes && data.acoes.length > 0) {
                const container = document.getElementById('acoes-container');
                container.innerHTML = '';
                data.acoes.forEach((item, idx) => {
                    const acaoHTML = `
                        <div class="acao-row" data-index="${idx}">
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                <div>
                                    <label class="form-label" style="font-size: 0.85rem;">Título da Ação/Meta <span style="color: #dc2626;">*</span></label>
                                    <input type="text" name="acoes[${idx}][titulo]" class="form-control" value="${item.titulo}" required>
                                </div>
                                <div>
                                    <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Como realizar / Critério de sucesso (Opcional)</label>
                                    <textarea name="acoes[${idx}][descricao]" class="form-control" rows="2">${item.descricao}</textarea>
                                </div>
                            </div>
                            <div style="padding-top: 26px;">
                                <button type="button" class="btn-danger-icon btn-remove-acao" onclick="removerAcao(this)">
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
            exibirBannerRascunho(form);
        } catch (e) {
            localStorage.removeItem(STORAGE_KEY);
        }
    }

    function exibirBannerRascunho(form) {
        const banner = document.createElement('div');
        banner.className = 'banner-rascunho';
        banner.id = 'rascunho-banner';
        banner.innerHTML = `
            <span>📝 Rascunho do PDI carregado automaticamente.</span>
            <button type="button" onclick="descartarRascunho()" style="background: white; border: 1px solid rgba(79,70,229,0.3); color: var(--color-primary); padding: 6px 14px; border-radius: 8px; font-weight: 700; cursor: pointer; font-family: var(--font-main);">Descartar rascunho</button>
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
