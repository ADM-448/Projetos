@extends('layouts.app')

@section('title', 'Editar Modelo de Avaliação')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title">Editar Modelo: {{ $avaliacao->titulo }}</h1>
        <p style="color: #666; font-size: 0.95rem;">Ajuste a estrutura padrão (Título e Competências) do seu modelo.</p>
    </div>
    <div>
        <a href="{{ route('avaliacoes.index', ['tab' => 'templates']) }}" class="btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<form action="{{ route('avaliacoes.templates.update', $avaliacao->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div style="display: grid; gap: 20px;">
        
        <!-- DADOS GERAIS -->
        <div class="table-container" style="padding: 20px;">
            <h2 style="font-size: 1.1rem; color: #2d3748; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                <i class="fa-solid fa-circle-info" style="color: var(--primary-color);"></i> 1. Informações Básicas do Modelo
            </h2>
            <div class="form-group" style="display: flex; flex-wrap: wrap; gap: 20px;">
                <div style="flex: 2; min-width: 250px;">
                    <label class="form-label" for="titulo">Nome do Modelo <span style="color: red;">*</span></label>
                    <input type="text" id="titulo" name="titulo" class="form-control" required placeholder="Ex: Modelo Padrão de Vendas" value="{{ old('titulo', $avaliacao->titulo) }}">
                </div>
                <div style="flex: 1; min-width: 250px;">
                    <label class="form-label" for="grau">Grau da Avaliação <span style="color: red;">*</span></label>
                    <select id="grau" name="grau" class="form-control" required>
                        <option value="90" {{ old('grau', $avaliacao->grau) == '90' ? 'selected' : '' }}>90° (Somente o Avaliador dá nota)</option>
                        <option value="180" {{ old('grau', $avaliacao->grau) == '180' ? 'selected' : '' }}>180° (Avaliador + Autoavaliação)</option>
                        <option value="360" {{ old('grau', $avaliacao->grau) == '360' ? 'selected' : '' }}>360° (Líderes, Pares, etc)</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="descricao">Instruções / Descrição</label>
                <textarea id="descricao" name="descricao" class="form-control" rows="3">{{ old('descricao', $avaliacao->descricao) }}</textarea>
            </div>
        </div>

        <!-- COMPETÊNCIAS COM PESOS -->
        <div class="table-container" style="padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; background: #fff;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                <h2 style="font-size: 1.15rem; color: #1e293b; margin: 0; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-list-check" style="color: var(--primary-color);"></i> 2. Competências e Ponderação de Pesos
                </h2>
                <span id="badgeTotalPesos" style="background: #eff6ff; color: #1d4ed8; font-size: 0.85rem; font-weight: 700; padding: 4px 14px; border-radius: 20px; border: 1px solid #bfdbfe;">
                    Total: 0%
                </span>
            </div>
            <p style="margin-bottom: 18px; color: #64748b; font-size: 0.88rem;">
                Selecione as competências que farão parte deste modelo e atribua o peso de importância para cada uma.
            </p>
            
            @php
                $selectedCompetencias = old('competencias', $avaliacao->competencias->pluck('id')->toArray());
                $existingPesos = $avaliacao->competencias->pluck('pivot.peso', 'id')->toArray();
                $oldPesos = old('pesos', $existingPesos);
            @endphp
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                @foreach($competencias as $comp)
                @php
                    $isChecked = in_array($comp->id, $selectedCompetencias);
                    $compPeso = $oldPesos[$comp->id] ?? 1;
                    $isTecnica = ($comp->tipo === 'tecnica');
                @endphp
                <div class="comp-card" id="card_comp_{{ $comp->id }}" style="background: {{ $isChecked ? '#f8fafc' : '#ffffff' }}; border: 1px solid {{ $isChecked ? '#cbd5e1' : '#e2e8f0' }}; border-radius: 10px; padding: 14px; display: flex; flex-direction: column; justify-content: space-between; gap: 12px; transition: all 0.2s;">
                    <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; margin: 0;">
                        <input type="checkbox" name="competencias[]" value="{{ $comp->id }}" class="comp-checkbox" data-comp-id="{{ $comp->id }}" style="margin-top: 3px; width: 17px; height: 17px; accent-color: var(--primary-color);" {{ $isChecked ? 'checked' : '' }} onchange="toggleCompPeso({{ $comp->id }})">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px;">
                                <strong style="color: #1e293b; font-size: 0.95rem;">{{ $comp->nome }}</strong>
                                <span style="font-size: 0.68rem; font-weight: 700; padding: 2px 7px; border-radius: 6px; text-transform: uppercase; background: {{ $isTecnica ? '#e0e7ff' : '#fef3c7' }}; color: {{ $isTecnica ? '#4338ca' : '#b45309' }};">
                                    {{ $isTecnica ? 'Técnica' : 'Comportamental' }}
                                </span>
                            </div>
                            <span style="font-size: 0.78rem; color: #64748b; display: block; margin-top: 2px;">
                                {{ $comp->skills()->count() }} {{ $comp->skills()->count() === 1 ? 'skill cadastrada' : 'skills cadastradas' }}
                            </span>
                        </div>
                    </label>

                    <div id="peso_wrapper_{{ $comp->id }}" style="display: {{ $isChecked ? 'flex' : 'none' }}; align-items: center; justify-content: space-between; border-top: 1px dashed #e2e8f0; padding-top: 10px; margin-top: 4px;">
                        <span style="font-size: 0.8rem; font-weight: 600; color: #475569;">
                            <i class="fa-solid fa-weight-hanging" style="color: #64748b; margin-right: 4px;"></i> Peso:
                        </span>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <input type="number" name="pesos[{{ $comp->id }}]" id="peso_input_{{ $comp->id }}" class="form-control peso-input" min="1" max="100" value="{{ $compPeso }}" style="width: 75px; height: 32px; padding: 4px 8px; font-weight: 700; text-align: center; border-radius: 6px;" oninput="recalcularTotalPesos()">
                            <span style="font-size: 0.85rem; font-weight: 700; color: #64748b;">%</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <script>
            function toggleCompPeso(compId) {
                const checkbox = document.querySelector(`.comp-checkbox[data-comp-id="${compId}"]`);
                const wrapper = document.getElementById(`peso_wrapper_${compId}`);
                const card = document.getElementById(`card_comp_${compId}`);
                
                if (checkbox.checked) {
                    wrapper.style.display = 'flex';
                    card.style.background = '#f8fafc';
                    card.style.borderColor = '#cbd5e1';
                } else {
                    wrapper.style.display = 'none';
                    card.style.background = '#ffffff';
                    card.style.borderColor = '#e2e8f0';
                }
                recalcularTotalPesos();
            }

            function recalcularTotalPesos() {
                let total = 0;
                document.querySelectorAll('.comp-checkbox:checked').forEach(cb => {
                    const compId = cb.getAttribute('data-comp-id');
                    const input = document.getElementById(`peso_input_${compId}`);
                    if (input) {
                        total += (parseInt(input.value) || 0);
                    }
                });

                const badge = document.getElementById('badgeTotalPesos');
                badge.textContent = `Total: ${total}%`;

                if (total === 100) {
                    badge.style.background = '#ecfdf5';
                    badge.style.color = '#047857';
                    badge.style.borderColor = '#a7f3d0';
                } else {
                    badge.style.background = '#fef3c7';
                    badge.style.color = '#b45309';
                    badge.style.borderColor = '#fde68a';
                }
            }

            document.addEventListener('DOMContentLoaded', recalcularTotalPesos);
        </script>

        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
            <a href="{{ route('avaliacoes.index', ['tab' => 'templates']) }}" class="btn-secondary">Cancelar</a>
            <button type="submit" class="btn-primary">Atualizar Modelo</button>
        </div>
    </div>
</form>
@endsection
