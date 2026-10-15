@extends('layouts.app')

@section('title', 'Novo Modelo de Avaliação')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title">Novo Modelo de Avaliação</h1>
        <p style="color: #666; font-size: 0.95rem;">Crie a estrutura padrão (Título e Competências). A delegação será feita ao lançar a campanha.</p>
    </div>
    <div>
        <a href="{{ route('avaliacoes.index', ['tab' => 'templates']) }}" class="btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<form action="{{ route('avaliacoes.templates.store') }}" method="POST">
    @csrf

    <div style="display: grid; gap: 20px;">
        
        <!-- DADOS GERAIS -->
        <div class="table-container" style="padding: 20px;">
            <h2 style="font-size: 1.1rem; color: #2d3748; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                <i class="fa-solid fa-circle-info" style="color: var(--primary-color);"></i> 1. Informações Básicas do Modelo
            </h2>
            <div class="form-group" style="display: flex; gap: 20px;">
                <div style="flex: 2;">
                    <label class="form-label" for="titulo">Nome do Modelo <span style="color: red;">*</span></label>
                    <input type="text" id="titulo" name="titulo" class="form-control" required placeholder="Ex: Modelo Padrão de Vendas" value="{{ old('titulo', $avaliacao->titulo) }}">
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
            <p style="margin-bottom: 15px; color: #4a5568; font-size: 0.9rem;">Selecione quais competências farão parte deste modelo padrão.</p>
            
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

        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
            <a href="{{ route('avaliacoes.templates.index') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" class="btn-primary">Salvar Modelo de Avaliação</button>
        </div>
    </div>
</form>
@endsection
