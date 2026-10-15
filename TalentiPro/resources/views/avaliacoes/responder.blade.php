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
                        
                        <!-- Escala de 1 a 5 (Responsiva) -->
                        <div style="max-width: 400px; margin: 0 auto;">
                            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #a0aec0; margin-bottom: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                <span style="text-align: left; max-width: 45%; line-height: 1.2;">Discordo Totalmente</span>
                                <span style="text-align: right; max-width: 45%; line-height: 1.2;">Concordo Totalmente</span>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0 5px;">
                                @for($i = 1; $i <= 5; $i++)
                                    <label style="display: flex; flex-direction: column; align-items: center; cursor: pointer;">
                                        <input type="radio" name="respostas[{{ $skill->id }}]" value="{{ $i }}" 
                                               {{ isset($respostasSalvas[$skill->id]) && $respostasSalvas[$skill->id] == $i ? 'checked' : '' }}
                                               required style="width: 24px; height: 24px; cursor: pointer; accent-color: #6a4c7c; margin: 0;">
                                        <span style="margin-top: 8px; font-weight: 700; color: #4a5568; font-size: 1rem;">{{ $i }}</span>
                                    </label>
                                @endfor
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 20px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
        <div style="background: #f7fafc; padding: 15px 20px; border-bottom: 1px solid #e2e8f0;">
            <h3 style="margin: 0; color: #2d3748; font-size: 1.1rem;">Deseja adicionar uma observação? (Opcional)</h3>
        </div>
        <div style="padding: 20px;">
            <textarea name="observacao" rows="4" style="width: 100%; border: 1px solid #cbd5e0; border-radius: 4px; padding: 10px; font-size: 1rem; color: #4a5568;" placeholder="Escreva aqui qualquer consideração final sobre a avaliação..."></textarea>
        </div>
    </div>

    <div style="text-align: right; margin-bottom: 40px;">
        <button type="submit" class="btn-primary" style="font-size: 1.1rem; padding: 10px 30px;">
            <i class="fa-solid fa-paper-plane"></i> Finalizar Avaliação
        </button>
    </div>
</form>
@endsection
