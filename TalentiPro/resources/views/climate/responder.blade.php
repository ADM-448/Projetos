@extends('layouts.app')

@section('title', 'Responder Pesquisa de Clima')

@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<style>
.escala-btn {
    flex: 1;
    min-width: 35px;
    height: 45px;
    border-radius: 8px;
    border: 2px solid #e2e8f0;
    background: #fafbfc;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    color: #475569;
    transition: all 0.2s;
    user-select: none;
}
.escala-btn:hover {
    border-color: #ec4899;
    transform: translateY(-2px);
}
.question-card {
    background: white;
    border-radius: 16px;
    padding: 22px 25px;
    border: 1px solid #f0f2f5;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    margin-bottom: 16px;
    transition: border-color 0.2s;
}
@media (max-width: 640px) {
    .escala-btn {
        min-width: 25px;
        height: 35px;
        font-size: 0.8rem;
    }
}
</style>

<div class="header-actions" style="margin-bottom: 20px;">
    <div>
        <h1 class="page-title" style="margin-bottom: 6px;">
            <i class="fa-solid fa-heart-pulse" style="color: #ec4899;"></i> {{ $survey->titulo }}
        </h1>
        <p style="color: #64748b; font-size: 0.95rem;">
            Avalie cada questão com base na sua experiência sincera do dia a dia.
        </p>
    </div>
</div>

{{-- BANNER DE GARANTIA DE ANONIMATO --}}
<div style="background: linear-gradient(135deg, #fdf2f8 0%, #fbcfe8 100%); border: 1px solid #f472b6; border-radius: 16px; padding: 18px 24px; margin-bottom: 25px; display: flex; align-items: center; gap: 16px;">
    <div style="width: 48px; height: 48px; border-radius: 12px; background: white; color: #db2777; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
        <i class="fa-solid fa-user-shield"></i>
    </div>
    <div>
        <h4 style="font-size: 1rem; font-weight: 800; color: #831843; margin: 0 0 2px 0;">Sua participação é 100% Anônima e Confidencial</h4>
        <p style="color: #9d174d; font-size: 0.88rem; margin: 0; line-height: 1.4;">
            As respostas não ficam vinculadas ao seu nome ou e-mail. Apenas os dados consolidados e agregados serão apresentados à liderança.
        </p>
    </div>
</div>

@if($errors->any())
    <div style="background: #fff5f5; border: 1px solid #feb2b2; color: #c53030; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 600;">
        <i class="fa-solid fa-circle-exclamation"></i> Por favor, responda a todas as questões antes de enviar.
    </div>
@endif

<form action="{{ route('clima.salvar-resposta', $survey->id) }}" method="POST" id="climate-form">
    @csrf

    @foreach($perguntasPorDimensao as $dimensaoNome => $perguntas)
        <div style="margin-bottom: 30px;">
            <h3 style="font-size: 1.05rem; font-weight: 800; color: #6a4c7c; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-layer-group"></i> {{ $dimensaoNome }}
            </h3>

            @foreach($perguntas as $q)
                <div class="question-card" id="q-card-{{ $q->id }}">
                    <div style="font-size: 0.98rem; font-weight: 700; color: #1e293b; margin-bottom: 12px; line-height: 1.4;">
                        {{ $q->texto_pergunta }}
                    </div>

                    @if($q->tipo_resposta === 'escala_1_10')
                        <input type="hidden" name="respostas[{{ $q->id }}]" id="inp-q-{{ $q->id }}" value="" required>

                        <div style="display: flex; gap: 6px; margin-top: 10px; flex-wrap: wrap;">
                            @for($i = 1; $i <= 10; $i++)
                                <div type="button" 
                                     class="escala-btn" 
                                     id="btn-q-{{ $q->id }}-{{ $i }}" 
                                     onclick="selectEscala10({{ $q->id }}, {{ $i }})">
                                    {{ $i }}
                                </div>
                            @endfor
                        </div>
                    @else
                        <textarea name="respostas[{{ $q->id }}]" required rows="3" placeholder="Sua resposta..." style="width: 100%; padding: 12px; border: 1px solid #cbd5e0; border-radius: 8px; font-size: 0.95rem; outline: none; resize: vertical; box-sizing: border-box;"></textarea>
                    @endif
                </div>
            @endforeach
        </div>
    @endforeach

    <div style="display: flex; justify-content: flex-end; margin-top: 30px;">
        <button type="submit" 
                class="btn-primary" 
                style="padding: 15px 40px; border-radius: 12px; font-weight: 800; font-size: 1.05rem; display: inline-flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); border: none; box-shadow: 0 4px 18px rgba(236, 72, 153, 0.4); color: white; cursor: pointer;">
            <i class="fa-solid fa-paper-plane"></i> Enviar Respostas Anônimas
        </button>
    </div>
</form>

<script>
function selectEscala10(questionId, val) {
    document.getElementById(`inp-q-${questionId}`).value = val;

    for(let n = 1; n <= 10; n++) {
        const btn = document.getElementById(`btn-q-${questionId}-${n}`);
        if(btn) {
            if (n === val) {
                btn.style.borderColor = '#ec4899';
                btn.style.background = '#fdf2f8';
                btn.style.color = '#db2777';
                btn.style.transform = 'scale(1.05)';
            } else {
                btn.style.borderColor = '#e2e8f0';
                btn.style.background = '#fafbfc';
                btn.style.color = '#475569';
                btn.style.transform = 'scale(1)';
            }
        }
    }

    const card = document.getElementById(`q-card-${questionId}`);
    card.style.borderColor = '#10b981';
}
</script>
@endsection
