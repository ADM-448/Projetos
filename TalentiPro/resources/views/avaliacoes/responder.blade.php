@extends('layouts.app')

@section('title', 'Responder Avaliação')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title">Respondendo Avaliação: {{ $participante->avaliacao->titulo }}</h1>
        <p style="color: #666; font-size: 0.95rem;">
            Você está avaliando: <strong>{{ $participante->avaliado->name }}</strong> 
            (Tipo: <span style="text-transform: capitalize;">{{ $participante->tipo->value }}</span>)
        </p>
    </div>
    <div>
        <a href="{{ route('dashboard') }}" class="btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Cancelar
        </a>
    </div>
</div>

@if($participante->avaliacao->descricao)
    <div style="background: #ebf8ff; border-left: 4px solid #3182ce; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
        <strong>Instruções do RH:</strong>
        <p style="margin: 5px 0 0 0; color: #2c5282;">{{ $participante->avaliacao->descricao }}</p>
    </div>
@endif

<form action="{{ route('avaliacoes.store-resposta', $participante->id) }}" method="POST" data-confirm="{{ $participante->tipo->value === 'autoavaliacao' ? 'Deseja enviar suas respostas? Não será possível alterá-las depois.' : 'Deseja enviar suas respostas?' }}" data-confirm-title="Confirmar Envio" data-confirm-type="success">
    @csrf

    @foreach($participante->avaliacao->competencias as $competencia)
        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 20px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
            <div style="background: #f7fafc; padding: 15px 20px; border-bottom: 1px solid #e2e8f0;">
                <h3 style="margin: 0; color: #2d3748; font-size: 1.1rem;">Competência: {{ $competencia->nome }}</h3>
                @if($competencia->descricao)
                    <p style="margin: 5px 0 0 0; font-size: 0.9rem; color: #718096;">{{ $competencia->descricao }}</p>
                @endif
            </div>
            
            <div style="padding: 20px;">
                @foreach($competencia->skills as $skill)
                    <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px dashed #e2e8f0; text-align: center;">
                        <p style="margin: 0 0 15px 0; font-weight: 600; font-size: 1.05rem; color: #4a5568;">{{ $skill->nome }}</p>
                        
                        <!-- Escala de 1 a 5 -->
                        <div style="display: flex; justify-content: center; gap: 15px; align-items: center;">
                            <span style="font-size: 0.8rem; color: #a0aec0;">Discordo Totalmente</span>
                            
                            @for($i = 1; $i <= 5; $i++)
                                <label style="display: flex; flex-direction: column; align-items: center; cursor: pointer;">
                                    <input type="radio" name="respostas[{{ $skill->id }}]" value="{{ $i }}" 
                                           {{ isset($respostasSalvas[$skill->id]) && $respostasSalvas[$skill->id] == $i ? 'checked' : '' }}
                                           required style="width: 22px; height: 22px; cursor: pointer; accent-color: #6a4c7c;">
                                    <span style="margin-top: 5px; font-weight: bold; color: #4a5568; font-size: 0.95rem;">{{ $i }}</span>
                                </label>
                            @endfor
                            
                            <span style="font-size: 0.8rem; color: #a0aec0;">Concordo Totalmente</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <div style="text-align: right; margin-bottom: 40px;">
        <button type="submit" class="btn-primary" style="font-size: 1.1rem; padding: 10px 30px;">
            <i class="fa-solid fa-paper-plane"></i> Finalizar Avaliação
        </button>
    </div>
</form>
@endsection
