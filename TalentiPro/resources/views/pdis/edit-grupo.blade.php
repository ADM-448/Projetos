@extends('layouts.app')

@section('title', 'Editar Lote de PDIs')
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

    .pdi-page { font-family: var(--font-main); background: var(--color-slate-50); min-height: 100vh; padding: 24px; }
    .form-card { background: #fff; border-radius: var(--radius-card); padding: 28px 32px; box-shadow: var(--shadow-card); border: var(--border-soft); margin-bottom: 24px; }
    .form-card-title { font-size: 1.1rem; font-weight: 700; color: var(--color-slate-900); margin: 0 0 20px 0; padding-bottom: 14px; border-bottom: var(--border-soft); display: flex; align-items: center; gap: 10px; }
    .form-label { font-size: 0.9rem; font-weight: 700; color: var(--color-slate-900); margin-bottom: 8px; display: block; }
    .form-control { width: 100%; padding: 12px 16px; border-radius: var(--radius-element); border: var(--border-soft); background: #fff; font-size: 0.95rem; color: var(--color-slate-900); font-family: var(--font-main); transition: all 0.2s; box-sizing: border-box; }
    .form-control:focus { outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
    .acao-row { background: var(--color-slate-50); padding: 18px 20px; border-radius: var(--radius-element); border: var(--border-soft); display: grid; grid-template-columns: 1fr auto; gap: 16px; align-items: start; animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 24px; border-radius: var(--radius-pill); font-weight: 600; font-size: 0.95rem; cursor: pointer; transition: all 0.3s ease; text-decoration: none; border: none; font-family: var(--font-main); }
    .btn-primary { background: var(--color-primary); color: #fff; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); }
    .btn-primary:hover { background: var(--color-primary-hover); transform: translateY(-2px); box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4); }
    .btn-secondary { background: var(--color-slate-50); color: var(--color-slate-900); border: var(--border-soft); }
    .btn-secondary:hover { background: var(--color-slate-100); transform: translateY(-1px); }
    .btn-danger-icon { background: rgba(239, 68, 68, 0.1); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.2); border-radius: var(--radius-element); padding: 10px 14px; cursor: pointer; transition: all 0.2s; font-family: var(--font-main); }
    .btn-danger-icon:hover { background: rgba(239, 68, 68, 0.18); }
    .error-msg { color: #dc2626; font-size: 0.85rem; margin-top: 5px; display: block; }
    .warning-msg { display: flex; align-items: flex-start; gap: 8px; background: rgba(239, 68, 68, 0.05); border-left: 4px solid #ef4444; padding: 12px 16px; border-radius: 0 var(--radius-element) var(--radius-element) 0; font-size: 0.88rem; color: #dc2626; font-weight: 600; }
</style>

<div class="pdi-page">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 32px;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--color-slate-900); margin: 0 0 4px 0; letter-spacing: -0.02em;">Editar Lote de PDIs</h1>
            <p style="color: var(--color-slate-500); font-size: 0.95rem; margin: 0;">
                <strong style="color: var(--color-accent);">{{ $grupo->titulo }}</strong> — As alterações refletirão no PDI individual de todos os participantes do lote.
            </p>
        </div>
        <a href="{{ route('pdis.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Voltar para PDIs
        </a>
    </div>

    <form action="{{ route('pdi-grupos.update', $grupo->id) }}" method="POST" id="form-pdi">
        @csrf
        @method('PUT')
        <input type="hidden" name="tipo_criacao" value="grupo">

        <!-- CARD PRINCIPAL -->
        <div class="form-card">
            <h3 class="form-card-title"><i class="fa-solid fa-file-invoice" style="color: var(--color-primary);"></i> Informações Gerais do Lote</h3>

            <div id="wrapper-grupo" style="margin-bottom: 20px;">
                <label class="form-label" for="colaboradores">Colaboradores no Grupo <span style="color: #dc2626;">*</span></label>
                <select name="colaboradores[]" id="colaboradores" class="form-control" multiple required>
                    @foreach($colaboradores as $colab)
                        <option value="{{ $colab->id }}" {{ in_array($colab->id, $colaboradoresSelecionados) ? 'selected' : '' }}>
                            {{ $colab->name }} ({{ $colab->cargo->nome ?? 'Sem cargo' }})
                        </option>
                    @endforeach
                </select>
                @error('colaboradores') <span class="error-msg">{{ $message }}</span> @enderror
                <div class="warning-msg" style="margin-top: 12px;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    Se você remover um colaborador que já estava na lista, o PDI dele (e o progresso) será excluído permanentemente!
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label class="form-label" for="titulo">Título do PDI <span style="color: #dc2626;">*</span></label>
                    <input type="text" id="titulo" name="titulo" class="form-control" value="{{ old('titulo', $grupo->titulo) }}" required>
                    @error('titulo') <span class="error-msg">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="form-label" for="categoria">Categoria</label>
                    <select id="categoria" name="categoria" class="form-control">
                        <option value="Técnico" {{ old('categoria', $grupo->categoria) == 'Técnico' ? 'selected' : '' }}>Técnico (Hard Skills)</option>
                        <option value="Comportamental" {{ old('categoria', $grupo->categoria) == 'Comportamental' ? 'selected' : '' }}>Comportamental (Soft Skills)</option>
                        <option value="Liderança" {{ old('categoria', $grupo->categoria) == 'Liderança' ? 'selected' : '' }}>Liderança / Gestão</option>
                        <option value="Outros" {{ old('categoria', $grupo->categoria) == 'Outros' ? 'selected' : '' }}>Outros</option>
                    </select>
                    @error('categoria') <span class="error-msg">{{ $message }}</span> @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label class="form-label" for="data_inicio">Data de Início</label>
                    <input type="date" id="data_inicio" name="data_inicio" class="form-control" value="{{ old('data_inicio', $grupo->data_inicio ? $grupo->data_inicio->format('Y-m-d') : '') }}">
                    @error('data_inicio') <span class="error-msg">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="form-label" for="data_fim">Data de Conclusão (Prazo)</label>
                    <input type="date" id="data_fim" name="data_fim" class="form-control" value="{{ old('data_fim', $grupo->data_fim ? $grupo->data_fim->format('Y-m-d') : '') }}">
                    @error('data_fim') <span class="error-msg">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="form-label" for="descricao">Descrição e Objetivo Geral do PDI</label>
                <textarea id="descricao" name="descricao" class="form-control" rows="3">{{ old('descricao', $grupo->descricao) }}</textarea>
                @error('descricao') <span class="error-msg">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- CARD DE AÇÕES -->
        <div class="form-card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: var(--border-soft); padding-bottom: 14px; margin-bottom: 20px; gap: 16px; flex-wrap: wrap;">
                <div>
                    <h3 style="margin: 0 0 4px 0; font-size: 1.1rem; font-weight: 700; color: var(--color-slate-900); display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-list-check" style="color: var(--color-primary);"></i> Etapas / Ações do Lote
                    </h3>
                    <p style="color: var(--color-slate-500); font-size: 0.85rem; margin: 0;">Mapeie as ações que devem ser realizadas pelos colaboradores.</p>
                </div>
                <button type="button" class="btn btn-secondary" onclick="adicionarAcao()" style="padding: 8px 18px; font-size: 0.88rem;">
                    <i class="fa-solid fa-plus"></i> Adicionar Ação
                </button>
            </div>

            <div id="acoes-container" style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 16px;">
                @foreach(old('acoes', $acoes) as $index => $acao)
                    <div class="acao-row" data-index="{{ $index }}">
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <div>
                                <label class="form-label" style="font-size: 0.85rem;">Título da Ação/Meta <span style="color: #dc2626;">*</span></label>
                                @php
                                    $tituloOld = is_array($acao) ? ($acao['titulo'] ?? '') : $acao->titulo;
                                    $descricaoOld = is_array($acao) ? ($acao['descricao'] ?? '') : $acao->descricao;
                                    $originalOld = is_array($acao) ? ($acao['original_titulo'] ?? '') : $acao->titulo;
                                @endphp
                                <input type="hidden" name="acoes[{{ $index }}][original_titulo]" value="{{ $originalOld }}">
                                <input type="text" name="acoes[{{ $index }}][titulo]" class="form-control" value="{{ $tituloOld }}" required>
                            </div>
                            <div>
                                <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Como realizar / Critério de sucesso (Opcional)</label>
                                <textarea name="acoes[{{ $index }}][descricao]" class="form-control" rows="2">{{ $descricaoOld }}</textarea>
                            </div>
                        </div>
                        <div style="padding-top: 26px;">
                            <button type="button" class="btn-danger-icon btn-remove-acao" onclick="removerAcao(this)" style="{{ count(old('acoes', $acoes)) > 1 ? '' : 'display:none;' }}">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="warning-msg">
                <i class="fa-solid fa-circle-exclamation"></i>
                Ações removidas aqui serão apagadas dos PDIs individuais do lote, mesmo que os colaboradores já as tenham concluído.
            </div>
            @error('acoes') <span class="error-msg" style="margin-top: 8px;">{{ $message }}</span> @enderror
        </div>

        <!-- SUBMIT -->
        <div style="margin-top: 8px; text-align: right; margin-bottom: 50px;">
            <button type="submit" class="btn btn-primary" style="font-size: 1rem; padding: 14px 32px;">
                <i class="fa-solid fa-floppy-disk"></i> Salvar Alterações do Lote
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
    let choicesGrupo;
    let acaoIndex = {{ count(old('acoes', $acoes)) + 10 }};

    document.addEventListener('DOMContentLoaded', function() {
        choicesGrupo = new Choices('#colaboradores', { removeItemButton: true, searchEnabled: true, itemSelectText: '', placeholderValue: 'Selecione um ou mais colaboradores...' });
    });

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
        atualizarBotoesRemover();
        setTimeout(() => { isAdding = false; }, 300);
    }

    function removerAcao(button) {
        const row = button.closest('.acao-row');
        const totalAcoes = document.querySelectorAll('.acao-row').length;
        if (totalAcoes > 1) {
            row.remove();
            atualizarBotoesRemover();
        } else {
            alert('É necessário ter pelo menos uma ação no PDI.');
        }
    }

    function atualizarBotoesRemover() {
        const botoes = document.querySelectorAll('.btn-remove-acao');
        if (botoes.length <= 1) {
            botoes.forEach(b => b.style.display = 'none');
        } else {
            botoes.forEach(b => b.style.display = 'flex');
        }
    }
</script>
@endpush
