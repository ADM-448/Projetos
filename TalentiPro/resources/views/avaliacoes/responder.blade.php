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
            <div style="background: #f8fafc; padding: 15px 20px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h3 style="margin: 0; color: #1e293b; font-size: 1.15rem; font-weight: 700;">{{ $competencia->nome }}</h3>
                        @if(($competencia->tipo ?? 'comportamental') === 'tecnica')
                            <span style="background: #e0e7ff; color: #4338ca; font-size: 0.75rem; font-weight: 700; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase;">
                                <i class="fa-solid fa-code"></i> Competência Técnica
                            </span>
                        @else
                            <span style="background: #fef3c7; color: #b45309; font-size: 0.75rem; font-weight: 700; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase;">
                                <i class="fa-solid fa-users"></i> Comportamental
                            </span>
                        @endif
                    </div>
                    @if($competencia->descricao)
                        <p style="margin: 5px 0 0 0; font-size: 0.88rem; color: #64748b;">{{ $competencia->descricao }}</p>
                    @endif
                </div>
            </div>
            
            <div style="padding: 20px;">
                @foreach($competencia->skills as $skill)
                    <div style="margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px dashed #e2e8f0; text-align: center;">
                        <p style="margin: 0 0 16px 0; font-weight: 600; font-size: 1.05rem; color: #1e293b;">{{ $skill->nome }}</p>
                        
                        <!-- Legenda de Proficiência Descritiva -->
                        @if(($competencia->tipo ?? 'comportamental') === 'tecnica')
                            <div style="max-width: 650px; margin: 0 auto 14px; background: #f1f5f9; border-radius: 8px; padding: 10px 14px; font-size: 0.76rem; color: #475569; display: grid; grid-template-columns: repeat(auto-fit, minmax(95px, 1fr)); gap: 6px; text-align: center;">
                                <div><strong>0:</strong> Não conhece</div>
                                <div><strong>1:</strong> Básico / c/ auxílio</div>
                                <div><strong>2:</strong> Intermediário</div>
                                <div><strong>3:</strong> Proficiente</div>
                                <div><strong>4:</strong> Avançado</div>
                                <div><strong>5:</strong> Domina / Instrutor</div>
                            </div>

                            <!-- Escala de 0 a 5 Técnica -->
                            <div style="max-width: 480px; margin: 0 auto;">
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0 5px;">
                                    @for($i = 0; $i <= 5; $i++)
                                        <label style="display: flex; flex-direction: column; align-items: center; cursor: pointer; flex: 1;" title="{{ match($i) {
                                            0 => '0: Não conhece / Nunca aplicou',
                                            1 => '1: Básico / Necessita de supervisão constante',
                                            2 => '2: Intermediário / Executa com autonomia parcial',
                                            3 => '3: Proficiente / Plena autonomia',
                                            4 => '4: Avançado / Referência para a equipe',
                                            5 => '5: Domina o conhecimento / Capaz de treinar outros',
                                        } }}">
                                            <input type="radio" name="respostas[{{ $skill->id }}]" value="{{ $i }}" 
                                                   {{ isset($respostasSalvas[$skill->id]) && (int)$respostasSalvas[$skill->id] === $i ? 'checked' : '' }}
                                                   required style="width: 22px; height: 22px; cursor: pointer; accent-color: var(--brand-primary, #6a4c7c); margin: 0;">
                                            <span style="margin-top: 6px; font-weight: 700; color: #334155; font-size: 0.95rem;">{{ $i }}</span>
                                        </label>
                                    @endfor
                                </div>
                            </div>
                        @else
                            <!-- Escala Comportamental (1 a 5) -->
                            <div style="max-width: 420px; margin: 0 auto;">
                                <div style="display: flex; justify-content: space-between; font-size: 0.74rem; color: #64748b; margin-bottom: 10px; font-weight: 600; text-transform: uppercase;">
                                    <span>Discordo Totalmente</span>
                                    <span>Concordo Totalmente</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0 5px;">
                                    @for($i = 1; $i <= 5; $i++)
                                        <label style="display: flex; flex-direction: column; align-items: center; cursor: pointer; flex: 1;">
                                            <input type="radio" name="respostas[{{ $skill->id }}]" value="{{ $i }}" 
                                                   {{ isset($respostasSalvas[$skill->id]) && (int)$respostasSalvas[$skill->id] === $i ? 'checked' : '' }}
                                                   required style="width: 22px; height: 22px; cursor: pointer; accent-color: var(--brand-primary, #6a4c7c); margin: 0;">
                                            <span style="margin-top: 6px; font-weight: 700; color: #334155; font-size: 0.95rem;">{{ $i }}</span>
                                        </label>
                                    @endfor
                                </div>
                            </div>
                        @endif

                        <!-- Campo de Evidências Opcional -->
                        <div style="max-width: 650px; margin: 18px auto 0; text-align: left; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                                <label for="evidencia_{{ $skill->id }}" style="font-size: 0.82rem; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 6px;">
                                    <i class="fa-solid fa-lightbulb" style="color: #eab308;"></i> Evidências e Exemplos Reais <span style="font-weight: normal; color: #94a3b8; font-size: 0.75rem;">(Opcional)</span>
                                </label>
                            </div>
                            <p style="margin: 0 0 8px 0; font-size: 0.78rem; color: #64748b; line-height: 1.4;">
                                <em>💡 É importante colocar evidências e situações reais que aconteceram para te ajudar na hora de dar o feedback e justificar a nota.</em>
                            </p>
                            <textarea name="evidencias[{{ $skill->id }}]" id="evidencia_{{ $skill->id }}" rows="2" 
                                      placeholder="Ex: No projeto X, entregou o relatório com antecedência e resolveu o conflito com calma..."
                                      style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; font-size: 0.88rem; color: #334155; resize: vertical; box-sizing: border-box; background: white; line-height: 1.45;">{{ $evidenciasSalvas[$skill->id] ?? '' }}</textarea>
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
