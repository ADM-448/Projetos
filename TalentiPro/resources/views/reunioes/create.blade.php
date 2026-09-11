@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<style>
    .choices__inner {
        border: 1px solid #cbd5e1 !important;
        border-radius: 6px !important;
        background-color: #fff !important;
        padding: 5px !important;
        min-height: 44px !important;
    }
    .choices__input {
        background-color: transparent !important;
        border: none !important;
        box-shadow: none !important;
        outline: none !important;
        padding: 0 5px !important;
        margin: 0 !important;
        width: auto !important;
        min-width: 200px !important;
    }
    .choices__input:focus {
        box-shadow: none !important;
        border: none !important;
    }
    .choices[data-type*="select-multiple"] .choices__button, .choices[data-type*="text"] .choices__button {
        border-left: 1px solid rgba(255,255,255,0.5);
    }
    .choices__list--multiple .choices__item {
        background-color: var(--primary-color, #5a3c6c) !important;
        border: 1px solid var(--primary-color, #5a3c6c) !important;
        border-radius: 4px !important;
        font-size: 0.85rem !important;
    }
    .choices__list--dropdown {
        z-index: 100 !important;
    }
</style>
@endpush


@section('title', 'Agendar Reunião')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-calendar-plus" style="color: var(--primary-color);"></i> Agendar Reunião</h1>
        <p style="color: #666; font-size: 0.95rem;">Preencha os dados abaixo para marcar um novo encontro.</p>
    </div>
    <div>
        <a href="{{ route('reunioes.index') }}" class="btn-secondary" style="text-decoration: none;">Voltar</a>
    </div>
</div>

<div class="card" style="padding: 30px; max-width: 600px; margin: 0 auto;">
    
    @if ($errors->any())
        <div style="background-color: #fef2f2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('reunioes.store') }}" method="POST">
        @csrf
        
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="titulo" style="display: block; margin-bottom: 8px; font-weight: 600; color: #334155;">Assunto / Título</label>
            <input type="text" id="titulo" name="titulo" value="{{ old('titulo', 'Reunião de Alinhamento') }}" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #334155;">Tipo de Reunião</label>
            <div style="display: flex; gap: 20px; align-items: center;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: #475569; white-space: nowrap; margin-bottom: 0; font-weight: normal; width: auto;">
                    <input type="radio" name="tipo_reuniao" value="local" {{ old('tipo_reuniao') === 'local' ? 'checked' : '' }} required style="accent-color: var(--primary-color); width: auto; margin: 0;">
                    <i class="fa-solid fa-microphone-lines"></i> Local (Áudio/Ata)
                </label>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: #475569; white-space: nowrap; margin-bottom: 0; font-weight: normal; width: auto;">
                    <input type="radio" name="tipo_reuniao" value="google_meet" {{ old('tipo_reuniao', 'google_meet') === 'google_meet' ? 'checked' : '' }} required style="accent-color: var(--primary-color); width: auto; margin: 0;">
                    <i class="fa-solid fa-video" style="color: #4285F4;"></i> Google Meet
                </label>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label for="participantes" style="display: block; margin-bottom: 8px; font-weight: 600; color: #334155;">Convidados</label>
            <select id="participantes" name="participantes[]" multiple required style="width: 100%;">
                @foreach($colaboradores as $colaborador)
                    <option value="{{ $colaborador->id }}" {{ in_array($colaborador->id, old('participantes', [])) ? 'selected' : '' }}>
                        {{ $colaborador->name }} ({{ $colaborador->email }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 25px;">
            <label for="data_hora" style="display: block; margin-bottom: 8px; font-weight: 600; color: #334155;">Data e Hora</label>
            <input type="datetime-local" id="data_hora" name="data_hora" value="{{ old('data_hora') }}" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
        </div>

        <div style="text-align: right;">
            <button type="submit" class="btn-primary" style="padding: 10px 20px;">
                <i class="fa-solid fa-save"></i> Agendar Reunião
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const element = document.getElementById('participantes');
        const choices = new Choices(element, {
            removeItemButton: true,
            searchPlaceholderValue: 'Buscar colaboradores...',
            noResultsText: 'Nenhum resultado encontrado',
            itemSelectText: 'Pressione Enter para selecionar',
            placeholderValue: 'Selecione os convidados'
        });
    });
</script>
@endpush
