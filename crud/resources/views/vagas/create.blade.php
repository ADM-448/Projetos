@extends('layouts.app')

@section('content')
<div style="max-width: 600px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="margin: 0; font-size: 1.6rem; font-weight: 600;">Criar Nova Vaga</h1>
        <a href="{{ route('vagas.index') }}" class="btn btn-secondary">← Voltar</a>
    </div>

    <div class="card">
        <form action="{{ route('vagas.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="titulo">Título da Vaga</label>
                <input type="text" name="titulo" id="titulo" class="form-control" value="{{ old('titulo') }}" placeholder="Ex: Desenvolvedor Full Stack PHP" required autofocus>
                @error('titulo')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="descricao">Descrição Completa</label>
                <textarea name="descricao" id="descricao" class="form-control" rows="5" placeholder="Descreva os requisitos, responsabilidades e benefícios..." required>{{ old('descricao') }}</textarea>
                @error('descricao')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="salario">Salário (R$ - opcional)</label>
                <input type="number" step="0.01" min="0" name="salario" id="salario" class="form-control" value="{{ old('salario') }}" placeholder="Ex: 8500.00">
                @error('salario')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="checkbox-group">
                <input type="checkbox" name="ativa" id="ativa" value="1" {{ old('ativa', 1) ? 'checked' : '' }}>
                <label for="ativa" style="margin: 0; cursor: pointer;">Vaga Ativa?</label>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <a href="{{ route('vagas.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar Vaga</button>
            </div>
        </form>
    </div>
</div>
@endsection
