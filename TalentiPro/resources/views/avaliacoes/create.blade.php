@extends('layouts.app')

@section('title', $avaliacao->exists ? 'Editar Avaliação' : 'Nova Avaliação')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title">{{ $avaliacao->exists ? 'Editar Avaliação' : 'Nova Avaliação' }}</h1>
        <p style="color: #666; font-size: 0.95rem;">Configure as diretrizes, competências e quem avaliará quem.</p>
    </div>
    <div>
        <a href="{{ route('avaliacoes.index') }}" class="btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<form action="{{ $avaliacao->exists ? route('avaliacoes.update', $avaliacao->id) : route('avaliacoes.store') }}" method="POST">
    @csrf
    @if($avaliacao->exists)
        @method('PUT')
    @endif

    <div style="display: grid; gap: 20px;">
        
        <!-- DADOS GERAIS -->
        <div class="table-container" style="padding: 20px;">
            <h2 style="font-size: 1.1rem; color: #2d3748; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                <i class="fa-solid fa-circle-info" style="color: var(--primary-color);"></i> 1. Informações da Avaliação
            </h2>
            <div class="form-group" style="display: flex; gap: 20px;">
                <div style="flex: 2;">
                    <label class="form-label" for="titulo">Nome da Avaliação <span style="color: red;">*</span></label>
                    <input type="text" id="titulo" name="titulo" class="form-control" required placeholder="Ex: Avaliação de Desempenho 2026" value="{{ old('titulo', $avaliacao->titulo) }}">
                </div>
                <div style="flex: 1;">
                    <label class="form-label" for="grau">Grau da Avaliação <span style="color: red;">*</span></label>
                    <select id="grau" name="grau" class="form-control" required>
                        <option value="90" {{ old('grau', $avaliacao->grau) == '90' ? 'selected' : '' }}>90° (Somente o Avaliador dá nota)</option>
                        <option value="180" {{ old('grau', $avaliacao->grau ?? '180') == '180' ? 'selected' : '' }}>180° (Avaliador + Autoavaliação)</option>
                        <option value="360" {{ old('grau', $avaliacao->grau) == '360' ? 'selected' : '' }}>360° (Líderes, Pares, etc)</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="display: flex; gap: 20px;">
                <div style="flex: 1;">
                    <label class="form-label" for="data_inicio">Data de Início <span style="color: red;">*</span></label>
                    <input type="date" id="data_inicio" name="data_inicio" class="form-control" required value="{{ old('data_inicio', $avaliacao->data_inicio?->format('Y-m-d')) }}">
                </div>
                <div style="flex: 1;">
                    <label class="form-label" for="data_fim">Data de Fim <span style="color: red;">*</span></label>
                    <input type="date" id="data_fim" name="data_fim" class="form-control" required value="{{ old('data_fim', $avaliacao->data_fim?->format('Y-m-d')) }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="descricao">Instruções / Descrição</label>
                <textarea id="descricao" name="descricao" class="form-control" rows="3">{{ old('descricao', $avaliacao->descricao) }}</textarea>
            </div>
        </div>

        <!-- COMPETÊNCIAS -->
        <div class="table-container" style="padding: 20px;">
            <h2 style="font-size: 1.1rem; color: #2d3748; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                <i class="fa-solid fa-list-check" style="color: var(--primary-color);"></i> 2. O que será avaliado? (Competências)
            </h2>
            <p style="margin-bottom: 15px; color: #4a5568; font-size: 0.9rem;">Selecione quais competências farão parte desta avaliação.</p>
            
            @php
                $selectedCompetencias = old('competencias', $avaliacao->competencias->pluck('id')->toArray());
            @endphp
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;">
                @foreach($competencias as $comp)
                <label style="display: flex; align-items: flex-start; gap: 10px; background: #f8fafc; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer;">
                    <input type="checkbox" name="competencias[]" value="{{ $comp->id }}" style="margin-top: 4px; width: 16px; height: 16px;" {{ in_array($comp->id, $selectedCompetencias) ? 'checked' : '' }}>
                    <div>
                        <strong style="display: block; color: #2d3748;">{{ $comp->nome }}</strong>
                        <span style="font-size: 0.8rem; color: #718096;">Contém {{ $comp->skills()->count() }} skills</span>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <!-- PARTICIPANTES -->
        <div class="table-container" style="padding: 20px;">
            <h2 style="font-size: 1.1rem; color: #2d3748; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                <i class="fa-solid fa-users" style="color: var(--primary-color);"></i> 3. Quem vai participar?
            </h2>
            
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <!-- ESQUERDA: Filtros -->
                <div style="flex: 1; min-width: 250px; background: #ebf8ff; border: 1px solid #bee3f8; padding: 15px; border-radius: 8px;">
                    <h3 style="font-size: 1rem; color: #2b6cb0; margin-bottom: 15px;">Filtros de Avaliados</h3>
                    <p style="font-size: 0.85rem; color: #2c5282; margin-bottom: 15px;">Use os filtros abaixo para popular automaticamente a lista de avaliados.</p>
                    
                    @php
                        $filtros = old('filtros', $avaliacao->filtros_aplicados ?? []);
                    @endphp
                    
                    <div class="form-group">
                        <label class="form-label" style="color: #2b6cb0;">Departamento</label>
                        <select name="filtros[departamento_id][]" id="filtro_departamento" class="form-control" multiple>
                            @foreach($departamentos as $dep)
                                <option value="{{ $dep->id }}" {{ in_array($dep->id, (array)($filtros['departamento_id'] ?? [])) ? 'selected' : '' }}>{{ $dep->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="color: #2b6cb0;">Cargo</label>
                        <select name="filtros[cargo_id][]" id="filtro_cargo" class="form-control" multiple>
                            @foreach($cargos as $cargo)
                                <option value="{{ $cargo->id }}" {{ in_array($cargo->id, (array)($filtros['cargo_id'] ?? [])) ? 'selected' : '' }}>{{ $cargo->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="color: #2b6cb0;">Vínculo</label>
                        <select name="filtros[vinculo][]" id="filtro_vinculo" class="form-control" multiple>
                            <option value="clt" {{ in_array('clt', (array)($filtros['vinculo'] ?? [])) ? 'selected' : '' }}>CLT</option>
                            <option value="pj" {{ in_array('pj', (array)($filtros['vinculo'] ?? [])) ? 'selected' : '' }}>PJ</option>
                            <option value="estagio" {{ in_array('estagio', (array)($filtros['vinculo'] ?? [])) ? 'selected' : '' }}>Estágio</option>
                        </select>
                    </div>

                    <button type="button" class="btn-primary" onclick="aplicarFiltros()" style="width: 100%; justify-content: center; margin-top: 10px;">
                        <i class="fa-solid fa-filter"></i> Aplicar Filtros aos Avaliados
                    </button>
                </div>

                <!-- DIREITA: Selects Múltiplos -->
                <div style="flex: 2; min-width: 300px; display: flex; flex-direction: column; gap: 15px;">
                    
                    @php
                        $selectedAvaliadores = old('avaliadores', $avaliacao->participantes->pluck('avaliador_id')->unique()->toArray());
                        $selectedAvaliados = old('avaliados', $avaliacao->participantes->pluck('avaliado_id')->unique()->toArray());
                        $selectedMediadores = old('mediadores', $avaliacao->mediadores ?? []);
                    @endphp

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">1. Avaliadores (Quem avalia) <span style="color: red;">*</span></label>
                        <select name="avaliadores[]" id="avaliadores" class="form-control" multiple style="height: 120px;" required>
                            @foreach($gestores as $gestor)
                                <option value="{{ $gestor->id }}" {{ in_array($gestor->id, $selectedAvaliadores) ? 'selected' : '' }}>
                                    {{ $gestor->name }} ({{ $gestor->cargo->nome ?? 'Sem Cargo' }})
                                </option>
                            @endforeach
                        </select>
                        <span style="font-size: 0.8rem; color: #718096;">Segure CTRL (ou CMD) para selecionar múltiplos.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">2. Avaliados (Quem recebe a nota) <span style="color: red;">*</span></label>
                        <select name="avaliados[]" id="avaliados" class="form-control" multiple style="height: 150px;" required>
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
                        <span style="font-size: 0.8rem; color: #718096;">Pode ser preenchido automaticamente clicando em "Aplicar Filtros".</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">3. Mediadores (Opcional - RH/Líder que acompanha)</label>
                        <select name="mediadores[]" id="mediadores" class="form-control" multiple style="height: 100px;">
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
    <div style="margin-top: 20px; text-align: right; margin-bottom: 50px;">
        <button type="submit" class="btn-primary" style="font-size: 1.1rem; padding: 12px 25px;">
            <i class="fa-solid fa-save"></i> {{ $avaliacao->exists ? 'Salvar Alterações' : 'Criar Avaliação' }}
        </button>
    </div>

</form>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<style>
    /* Estilos extras para o Choices ficar mais bonito */
    .choices__inner {
        border-radius: 8px;
        background-color: #fff;
        border: 1px solid #e2e8f0;
    }
    .choices[data-type*="select-multiple"] .choices__button, .choices[data-type*="text"] .choices__button {
        border-left: 1px solid rgba(255,255,255,0.5);
    }
    .choices__list--multiple .choices__item {
        background-color: var(--primary-color, #6a4c7c);
        border: 1px solid var(--primary-color, #6a4c7c);
        border-radius: 4px;
    }
</style>

<script>
    let choicesAvaliadores, choicesAvaliados, choicesMediadores;
    let filtroDep, filtroCargo, filtroVinculo;

    document.addEventListener('DOMContentLoaded', function() {
        choicesAvaliadores = new Choices('#avaliadores', { removeItemButton: true, searchEnabled: true, itemSelectText: '', placeholderValue: 'Pesquise por nome ou cargo...', searchPlaceholderValue: 'Digite o nome ou cargo para filtrar...' });
        choicesAvaliados = new Choices('#avaliados', { removeItemButton: true, searchEnabled: true, itemSelectText: '', placeholderValue: 'Pesquise por nome ou cargo...', searchPlaceholderValue: 'Digite o nome ou cargo para filtrar...' });
        choicesMediadores = new Choices('#mediadores', { removeItemButton: true, searchEnabled: true, itemSelectText: '', placeholderValue: 'Pesquise por nome ou cargo...', searchPlaceholderValue: 'Digite o nome ou cargo para filtrar...' });
        
        filtroDep = new Choices('#filtro_departamento', { removeItemButton: true, searchEnabled: true, itemSelectText: '', placeholderValue: 'Selecione para filtrar...' });
        filtroCargo = new Choices('#filtro_cargo', { removeItemButton: true, searchEnabled: true, itemSelectText: '', placeholderValue: 'Selecione para filtrar...' });
        filtroVinculo = new Choices('#filtro_vinculo', { removeItemButton: true, searchEnabled: false, itemSelectText: '', placeholderValue: 'Selecione para filtrar...' });
    });

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

        showAlert(selectedValues.length + " avaliados selecionados de acordo com os filtros.", "Filtro Aplicado", "success");
    }
</script>

@endsection
