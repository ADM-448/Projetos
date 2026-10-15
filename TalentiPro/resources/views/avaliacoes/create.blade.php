@extends('layouts.app')

@section('title', $avaliacao->exists ? 'Editar Avaliação' : 'Delegar Avaliação')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions" style="margin-bottom: 25px;">
    <div>
        <h1 class="page-title">{{ $avaliacao->exists ? 'Editar Avaliação' : 'Delegar Avaliação' }}</h1>
        <p style="color: #64748b; font-size: 0.95rem; margin-top: 4px;">Selecione o modelo, defina o período, recorrência e quem avaliará quem.</p>
    </div>
    <div>
        <a href="{{ route('avaliacoes.index') }}" class="btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<style>
    /* Fix for mobile overflow on forms and choices.js */
    .choices, .form-control {
        min-width: 0 !important;
        max-width: 100% !important;
    }
    .choices__inner {
        max-width: 100% !important;
        overflow: hidden;
    }
    .choices__list--multiple .choices__item {
        word-break: break-word;
        white-space: normal;
        max-width: 100%;
    }
    .table-container {
        max-width: 100%;
        box-sizing: border-box;
    }
</style>

@if ($errors->any())
    <div style="background-color: #fee2e2; color: #991b1b; padding: 15px 18px; border-radius: 10px; margin-bottom: 25px; border-left: 5px solid #ef4444;">
        <div style="font-weight: bold; margin-bottom: 5px;"><i class="fa-solid fa-triangle-exclamation"></i> Por favor, corrija os erros abaixo:</div>
        <ul style="margin: 0; padding-left: 20px; font-size: 0.9rem;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $avaliacao->exists ? route('avaliacoes.update', $avaliacao->id) : route('avaliacoes.store') }}" method="POST">
    @csrf
    @if($avaliacao->exists)
        @method('PUT')
    @endif

    <div style="display: grid; gap: 25px;">
        
        <!-- 1. MODELO E IDENTIFICAÇÃO -->
        <div class="table-container" style="padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
            <h2 style="font-size: 1.15rem; font-weight: 700; color: #1e293b; margin-bottom: 18px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                <span style="background: rgba(106, 76, 124, 0.12); color: var(--primary-color, #6a4c7c); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.95rem;">1</span>
                <span>Modelo e Identificação da Avaliação</span>
            </h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="template_id" style="font-weight: 600; color: #334155;">
                        Modelo de Avaliação (Competências) <span style="color: red;">*</span>
                    </label>
                    <select id="template_id" name="template_id" class="form-control" required onchange="onTemplateChange(this)" style="height: 44px;">
                        <option value="">-- Selecione o Modelo Base --</option>
                        @foreach($templates as $tpl)
                            <option value="{{ $tpl->id }}" 
                                data-grau="{{ $tpl->grau }}" 
                                data-titulo="{{ $tpl->titulo }}"
                                data-descricao="{{ $tpl->descricao }}"
                                data-comps="{{ $tpl->competencias->pluck('nome')->implode(', ') }}"
                                {{ old('template_id', $selectedTemplateId ?? $avaliacao->template_id) == $tpl->id ? 'selected' : '' }}>
                                {{ $tpl->titulo }} ({{ $tpl->grau }}° - {{ $tpl->competencias->count() }} competências)
                            </option>
                        @endforeach
                    </select>
                    <div style="margin-top: 6px; font-size: 0.82rem; color: #64748b;">
                        Não encontrou o modelo desejado? <a href="{{ route('avaliacoes.templates.create') }}" target="_blank" style="color: var(--primary-color, #6a4c7c); font-weight: 600; text-decoration: underline;">Criar novo modelo</a>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="titulo" style="font-weight: 600; color: #334155;">Nome desta Campanha <span style="color: red;">*</span></label>
                    <input type="text" id="titulo" name="titulo" class="form-control" required placeholder="Ex: Avaliação de Desempenho 2026 - Q3" value="{{ old('titulo', $avaliacao->titulo) }}" style="height: 44px;">
                </div>
            </div>

            <!-- Preview do Modelo Selecionado -->
            <div id="templatePreviewCard" style="display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 15px; margin-top: 18px;">
                <div style="font-size: 0.85rem; color: #475569; display: flex; flex-wrap: wrap; gap: 15px; align-items: center;">
                    <div><strong>Grau:</strong> <span id="previewGrau" style="background: #e2e8f0; padding: 2px 8px; border-radius: 6px; font-weight: bold;">-</span></div>
                    <div><strong>Competências Inclusas:</strong> <span id="previewCompetencias" style="color: var(--primary-color, #6a4c7c); font-weight: 600;">-</span></div>
                </div>
            </div>

            <div class="form-group" style="margin-top: 18px; margin-bottom: 0;">
                <label class="form-label" for="descricao" style="font-weight: 600; color: #334155;">Instruções aos Participantes (Opcional)</label>
                <textarea id="descricao" name="descricao" class="form-control" rows="2" placeholder="Orientações ou recados que aparecerão para os avaliadores e colaboradores...">{{ old('descricao', $avaliacao->descricao) }}</textarea>
            </div>
        </div>

        <!-- 2. DATAS E RECORRÊNCIA -->
        <div class="table-container" style="padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
            <h2 style="font-size: 1.15rem; font-weight: 700; color: #1e293b; margin-bottom: 18px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                <span style="background: rgba(106, 76, 124, 0.12); color: var(--primary-color, #6a4c7c); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.95rem;">2</span>
                <span>Período de Execução & Recorrência</span>
            </h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="data_inicio" style="font-weight: 600; color: #334155;">Data de Início <span style="color: red;">*</span></label>
                    <input type="date" id="data_inicio" name="data_inicio" class="form-control" required value="{{ old('data_inicio', $avaliacao->data_inicio?->format('Y-m-d') ?? date('Y-m-d')) }}" style="height: 44px;">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="data_fim" style="font-weight: 600; color: #334155;">Data de Término <span style="font-size: 0.82rem; font-weight: normal; color: #64748b;">(Opcional se recorrente)</span></label>
                    <input type="date" id="data_fim" name="data_fim" class="form-control" value="{{ old('data_fim', $avaliacao->data_fim?->format('Y-m-d')) }}" style="height: 44px;">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="recorrencia" style="font-weight: 600; color: #334155;">
                        <i class="fa-solid fa-repeat" style="color: var(--primary-color, #6a4c7c);"></i> Recorrência Automática
                    </label>
                    <select id="recorrencia" name="recorrencia" class="form-control" style="height: 44px;">
                        <option value="none" {{ old('recorrencia', $avaliacao->recorrencia ?? 'none') == 'none' ? 'selected' : '' }}>Sem recorrência (Apenas uma vez)</option>
                        <option value="diario" {{ old('recorrencia', $avaliacao->recorrencia) == 'diario' ? 'selected' : '' }}>Diária</option>
                        <option value="semanal" {{ old('recorrencia', $avaliacao->recorrencia) == 'semanal' ? 'selected' : '' }}>Semanal</option>
                        <option value="quinzenal" {{ old('recorrencia', $avaliacao->recorrencia) == 'quinzenal' ? 'selected' : '' }}>Quinzenal</option>
                        <option value="mensal" {{ old('recorrencia', $avaliacao->recorrencia) == 'mensal' ? 'selected' : '' }}>Mensal</option>
                        <option value="bimestral" {{ old('recorrencia', $avaliacao->recorrencia) == 'bimestral' ? 'selected' : '' }}>Bimestral (A cada 2 meses)</option>
                        <option value="trimestral" {{ old('recorrencia', $avaliacao->recorrencia) == 'trimestral' ? 'selected' : '' }}>Trimestral (A cada 3 meses)</option>
                        <option value="semestral" {{ old('recorrencia', $avaliacao->recorrencia) == 'semestral' ? 'selected' : '' }}>Semestral (A cada 6 meses)</option>
                        <option value="anual" {{ old('recorrencia', $avaliacao->recorrencia) == 'anual' ? 'selected' : '' }}>Anual</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 3. PARTICIPANTES (DELEGAÇÃO) -->
        <div class="table-container" style="padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
            <h2 style="font-size: 1.15rem; font-weight: 700; color: #1e293b; margin-bottom: 18px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                <span style="background: rgba(106, 76, 124, 0.12); color: var(--primary-color, #6a4c7c); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.95rem;">3</span>
                <span>Definição dos Participantes (Quem avalia quem)</span>
            </h2>
            
            <div style="display: flex; gap: 24px; flex-wrap: wrap;">
                <!-- ESQUERDA: Filtros Rápidos -->
                <div style="flex: 1; min-width: 260px; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 18px; border-radius: 10px;">
                    <h3 style="font-size: 0.98rem; font-weight: 700; color: #166534; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-filter"></i> Filtro Rápido de Avaliados
                    </h3>
                    <p style="font-size: 0.82rem; color: #15803d; margin-bottom: 14px;">Selecione os critérios e clique no botão para auto-selecionar os colaboradores.</p>
                    
                    @php
                        $filtros = old('filtros', $avaliacao->filtros_aplicados ?? []);
                    @endphp
                    
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" style="color: #166534; font-size: 0.85rem; font-weight: 600;">Departamento</label>
                        <select name="filtros[departamento_id][]" id="filtro_departamento" class="form-control" multiple>
                            @foreach($departamentos as $dep)
                                <option value="{{ $dep->id }}" {{ in_array($dep->id, (array)($filtros['departamento_id'] ?? [])) ? 'selected' : '' }}>{{ $dep->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" style="color: #166534; font-size: 0.85rem; font-weight: 600;">Cargo</label>
                        <select name="filtros[cargo_id][]" id="filtro_cargo" class="form-control" multiple>
                            @foreach($cargos as $cargo)
                                <option value="{{ $cargo->id }}" {{ in_array($cargo->id, (array)($filtros['cargo_id'] ?? [])) ? 'selected' : '' }}>{{ $cargo->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 14px;">
                        <label class="form-label" style="color: #166534; font-size: 0.85rem; font-weight: 600;">Vínculo</label>
                        <select name="filtros[vinculo][]" id="filtro_vinculo" class="form-control" multiple>
                            <option value="clt" {{ in_array('clt', (array)($filtros['vinculo'] ?? [])) ? 'selected' : '' }}>CLT</option>
                            <option value="pj" {{ in_array('pj', (array)($filtros['vinculo'] ?? [])) ? 'selected' : '' }}>PJ</option>
                            <option value="estagio" {{ in_array('estagio', (array)($filtros['vinculo'] ?? [])) ? 'selected' : '' }}>Estágio</option>
                        </select>
                    </div>

                    <button type="button" class="btn-primary" onclick="aplicarFiltros()" style="width: 100%; justify-content: center; background: #16a34a; border-color: #16a34a; padding: 10px;">
                        <i class="fa-solid fa-check-double"></i> Auto-Selecionar Avaliados
                    </button>
                </div>

                <!-- DIREITA: Selects Múltiplos dos Participantes -->
                <div style="flex: 2; min-width: 200px; display: flex; flex-direction: column; gap: 18px; width: 100%;">
                    
                    @php
                        $selectedAvaliadores = old('avaliadores', $avaliacao->participantes->where('tipo', 'gestor')->pluck('avaliador_id')->unique()->toArray());
                        $selectedAvaliados = old('avaliados', $avaliacao->participantes->pluck('avaliado_id')->unique()->toArray());
                        $selectedMediadores = old('mediadores', $avaliacao->mediadores ?? []);
                    @endphp

                    <div class="form-group" style="margin-bottom: 0; width: 100%;">
                        <label class="form-label" style="font-weight: 700; color: #1e293b; white-space: normal;">
                            1. Avaliadores (Líderes que vão atribuir notas) <span style="color: red;">*</span>
                        </label>
                        <select name="avaliadores[]" id="avaliadores" class="form-control" multiple required>
                            @foreach($gestores as $gestor)
                                <option value="{{ $gestor->id }}" {{ in_array($gestor->id, $selectedAvaliadores) ? 'selected' : '' }}>
                                    {{ $gestor->name }} ({{ $gestor->cargo->nome ?? 'Sem Cargo' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0; width: 100%;">
                        <label class="form-label" style="font-weight: 700; color: #1e293b; white-space: normal;">
                            2. Avaliados (Colaboradores que receberão avaliação) <span style="color: red;">*</span>
                        </label>
                        <select name="avaliados[]" id="avaliados" class="form-control" multiple required>
                            @foreach($colaboradores as $colab)
                                <option value="{{ $colab->id }}" 
                                    data-dep="{{ $colab->departamento_id }}" 
                                    data-cargo="{{ $colab->cargo_id }}" 
                                    data-vinculo="{{ $colab->vinculo }}"
                                    {{ in_array($colab->id, $selectedAvaliados) ? 'selected' : '' }}
                                >
                                    {{ $colab->name }} ({{ $colab->cargo->nome ?? 'Sem Cargo' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0; width: 100%;">
                        <label class="form-label" style="font-weight: 700; color: #1e293b; white-space: normal;">
                            3. Mediadores (Opcional - RH ou Diretor que acompanha e fecha mediações)
                        </label>
                        <select name="mediadores[]" id="mediadores" class="form-control" multiple>
                            @foreach($gestores as $gestor)
                                <option value="{{ $gestor->id }}" {{ in_array($gestor->id, $selectedMediadores) ? 'selected' : '' }}>
                                    {{ $gestor->name }} ({{ $gestor->cargo->nome ?? 'Sem Cargo' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <!-- SUBMIT -->
    <div style="margin-top: 25px; text-align: right; margin-bottom: 50px;">
        <button type="submit" class="btn-primary" style="font-size: 1.05rem; padding: 12px 30px; font-weight: 700; box-shadow: 0 4px 12px rgba(106, 76, 124, 0.25);">
            <i class="fa-solid fa-paper-plane"></i> {{ $avaliacao->exists ? 'Salvar Alterações' : 'Lançar e Delegar Avaliação' }}
        </button>
    </div>

</form>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<style>
    .choices__inner {
        border-radius: 8px;
        background-color: #fff;
        border: 1px solid #cbd5e1;
    }
    .choices[data-type*="select-multiple"] .choices__button, .choices[data-type*="text"] .choices__button {
        border-left: 1px solid rgba(255,255,255,0.5);
    }
    .choices__list--multiple .choices__item {
        background-color: var(--primary-color, #6a4c7c);
        border: 1px solid var(--primary-color, #6a4c7c);
        border-radius: 6px;
    }
</style>

<script>
    let choicesAvaliadores, choicesAvaliados, choicesMediadores;
    let filtroDep, filtroCargo, filtroVinculo;

    document.addEventListener('DOMContentLoaded', function() {
        choicesAvaliadores = new Choices('#avaliadores', { removeItemButton: true, searchEnabled: true, itemSelectText: '', placeholderValue: 'Pesquise e selecione os avaliadores...', searchPlaceholderValue: 'Digite o nome ou cargo...' });
        choicesAvaliados = new Choices('#avaliados', { removeItemButton: true, searchEnabled: true, itemSelectText: '', placeholderValue: 'Pesquise e selecione os avaliados...', searchPlaceholderValue: 'Digite o nome ou cargo...' });
        choicesMediadores = new Choices('#mediadores', { removeItemButton: true, searchEnabled: true, itemSelectText: '', placeholderValue: 'Selecione mediadores (opcional)...', searchPlaceholderValue: 'Digite o nome ou cargo...' });
        
        filtroDep = new Choices('#filtro_departamento', { removeItemButton: true, searchEnabled: true, itemSelectText: '', placeholderValue: 'Departamentos...' });
        filtroCargo = new Choices('#filtro_cargo', { removeItemButton: true, searchEnabled: true, itemSelectText: '', placeholderValue: 'Cargos...' });
        filtroVinculo = new Choices('#filtro_vinculo', { removeItemButton: true, searchEnabled: false, itemSelectText: '', placeholderValue: 'Vínculos...' });

        // Atualiza preview se já tiver template selecionado no load
        const tplSelect = document.getElementById('template_id');
        if (tplSelect && tplSelect.value) {
            onTemplateChange(tplSelect, false);
        }
    });

    function onTemplateChange(selectEl, updateTitle = true) {
        const selectedOpt = selectEl.options[selectEl.selectedIndex];
        const previewCard = document.getElementById('templatePreviewCard');
        const previewGrau = document.getElementById('previewGrau');
        const previewComps = document.getElementById('previewCompetencias');
        const tituloInput = document.getElementById('titulo');

        if (selectedOpt && selectedOpt.value) {
            const grau = selectedOpt.getAttribute('data-grau');
            const comps = selectedOpt.getAttribute('data-comps');
            const tplTitulo = selectedOpt.getAttribute('data-titulo');
            const tplDescricao = selectedOpt.getAttribute('data-descricao');

            previewGrau.textContent = grau + '°';
            previewComps.textContent = comps || 'Nenhuma competência cadastrada';
            previewCard.style.display = 'block';

            if (updateTitle && (!tituloInput.value || tituloInput.value.includes('Avaliação'))) {
                const now = new Date();
                const year = now.getFullYear();
                tituloInput.value = tplTitulo + ' - ' + year;
            }
            
            if (updateTitle) {
                const descricaoInput = document.getElementById('descricao');
                if (descricaoInput && tplDescricao) {
                    descricaoInput.value = tplDescricao;
                }
            }
        } else {
            previewCard.style.display = 'none';
        }
    }

    function getSelectedValues(selectElement) {
        if (!selectElement) return [];
        return Array.from(selectElement.selectedOptions).map(opt => opt.value);
    }

    function aplicarFiltros() {
        const depIds = getSelectedValues(document.getElementById('filtro_departamento'));
        const cargoIds = getSelectedValues(document.getElementById('filtro_cargo'));
        const vinculos = getSelectedValues(document.getElementById('filtro_vinculo'));

        const selectAvaliados = document.getElementById('avaliados');
        const options = selectAvaliados.options;

        let selectedValues = [];

        for (let i = 0; i < options.length; i++) {
            const opt = options[i];
            
            let matchDep = depIds.length === 0 || depIds.includes(opt.getAttribute('data-dep'));
            let matchCargo = cargoIds.length === 0 || cargoIds.includes(opt.getAttribute('data-cargo'));
            let matchVinculo = vinculos.length === 0 || vinculos.includes(opt.getAttribute('data-vinculo'));

            if (matchDep && matchCargo && matchVinculo) {
                selectedValues.push(opt.value);
            }
        }

        // Usar a API do Choices para atualizar a seleção
        choicesAvaliados.removeActiveItems();
        selectedValues.forEach(val => {
            choicesAvaliados.setChoiceByValue(val);
        });

        alert(selectedValues.length + " avaliados selecionados de acordo com os filtros.");
    }
</script>

@endsection

