@extends('layouts.app')

@section('title', 'Visualizar Modelo: ' . $template->titulo)
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title">Modelo: {{ $template->titulo }}</h1>
        <p style="color: #666; font-size: 0.95rem;">Grau: {{ $template->grau }}° @if($template->descricao) | {{ $template->descricao }} @endif</p>
    </div>
    <div style="display: flex; gap: 10px;">
        @if(!auth()->user()->isConsultor())
        <a href="{{ route('avaliacoes.templates.edit', $template->id) }}" class="btn-primary" style="background: #d69e2e; border-color: #d69e2e;">
            <i class="fa-solid fa-pen"></i> Editar
        </a>
        @endif
        <a href="{{ route('avaliacoes.index', ['tab' => 'templates']) }}" class="btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="unified-card" style="padding: 25px;">
    <h2 style="font-size: 1.2rem; color: #2d3748; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
        <i class="fa-solid fa-list-check" style="color: var(--primary-color);"></i> Competências e Perguntas Escolhidas
    </h2>

    @forelse($template->competencias as $comp)
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 20px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 8px;">
                <h3 style="font-size: 1.05rem; font-weight: bold; color: #1e293b; margin: 0;">{{ $comp->nome }}</h3>
                <span style="background: #eff6ff; color: #1d4ed8; font-weight: 700; font-size: 0.82rem; padding: 3px 10px; border-radius: 20px; border: 1px solid #bfdbfe;">
                    <i class="fa-solid fa-weight-hanging" style="margin-right: 4px;"></i> Peso: {{ $comp->pivot->peso ?? 1 }}%
                </span>
            </div>
            @if($comp->descricao)
                <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 15px;">{{ $comp->descricao }}</p>
            @endif

            <div style="background: white; border: 1px solid #edf2f7; border-radius: 6px; padding: 15px;">
                <h4 style="font-size: 0.85rem; font-weight: bold; color: #a0aec0; text-transform: uppercase; margin-bottom: 10px;">Skills (Perguntas) Avaliadas:</h4>
                <ul style="list-style-type: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                    @forelse($comp->skills as $skill)
                        <li style="display: flex; align-items: flex-start; font-size: 0.9rem; color: #4a5568;">
                            <i class="fa-solid fa-check" style="color: #38a169; margin-top: 3px; margin-right: 10px;"></i>
                            <span>{{ $skill->nome }}</span>
                        </li>
                    @empty
                        <li style="font-size: 0.9rem; color: #a0aec0; font-style: italic;">Nenhuma skill cadastrada para esta competência.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    @empty
        <div style="text-align: center; padding: 40px; color: #a0aec0;">
            <i class="fa-solid fa-folder-open" style="font-size: 2.5rem; margin-bottom: 15px; display: block;"></i>
            Nenhuma competência vinculada a este modelo.
        </div>
    @endforelse
</div>
@endsection
