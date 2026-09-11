@extends('layouts.app')

@section('title', 'Mediar Avaliação')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title">Mediação e Fechamento: {{ $avaliacao->titulo }}</h1>
        <p style="color: #666; font-size: 0.95rem;">Mediando avaliação de: <strong>{{ $avaliado->name }}</strong></p>
    </div>
    <div>
        <a href="{{ route('avaliacoes.show', $avaliacao->id) }}" class="btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

@php
    // Agrupar respostas já dadas por outros participantes (para cálculo dos KPIs gerais e comparativos rápidos)
    $skillsNotas = [];
    $totalAuto = 0;
    $countAuto = 0;
    $totalGestor = 0;
    $countGestor = 0;

    foreach ($participantesRespostas as $part) {
        foreach ($part->respostas as $resp) {
            $skillId = $resp->skill_id;
            $tipo = $part->tipo->value ?? $part->tipo;
            
            if (!isset($skillsNotas[$skillId])) {
                $skillsNotas[$skillId] = [
                    'nome' => $resp->skill->nome,
                    'autoavaliacao' => null,
                    'gestores' => [],
                ];
            }
            
            if ($tipo === 'autoavaliacao') {
                $skillsNotas[$skillId]['autoavaliacao'] = $resp->nota;
                $totalAuto += $resp->nota;
                $countAuto++;
            } else {
                $skillsNotas[$skillId]['gestores'][] = $resp->nota;
                $totalGestor += $resp->nota;
                $countGestor++;
            }
        }
    }

    $mediaGeralAuto = $countAuto > 0 ? round($totalAuto / $countAuto, 2) : null;
    $mediaGeralGestor = $countGestor > 0 ? round($totalGestor / $countGestor, 2) : null;
    
    // Nota sugerida geral (média de todos os envolvidos)
    $totalRespostas = $countAuto + $countGestor;
    $notaSugerida = $totalRespostas > 0 ? round(($totalAuto + $totalGestor) / $totalRespostas, 2) : null;

    // Respostas anteriores do próprio mediador (se existirem)
    $respostasMediador = $participanteMediador ? $participanteMediador->respostas->pluck('nota', 'skill_id')->toArray() : [];
@endphp

{{-- KPIs de Panorama Geral --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px;">
    <div class="form-card" style="padding: 20px; border-radius: 12px; display: flex; align-items: center; justify-content: space-between; border-left: 5px solid #3182ce; margin-bottom: 0; background: white; border: 1px solid #e2e8f0; border-left-width: 5px;">
        <div>
            <p style="color: #718096; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; margin-bottom: 5px;">Média Autoavaliação</p>
            <h2 style="color: #2b6cb0; font-size: 1.8rem; margin: 0;">{{ $mediaGeralAuto ? number_format($mediaGeralAuto, 2) : 'N/A' }}</h2>
        </div>
        <div style="width: 45px; height: 45px; border-radius: 50%; background: #ebf8ff; color: #3182ce; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
            <i class="fa-solid fa-user"></i>
        </div>
    </div>

    <div class="form-card" style="padding: 20px; border-radius: 12px; display: flex; align-items: center; justify-content: space-between; border-left: 5px solid #e53e3e; margin-bottom: 0; background: white; border: 1px solid #e2e8f0; border-left-width: 5px;">
        <div>
            <p style="color: #718096; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; margin-bottom: 5px;">Média Avaliadores</p>
            <h2 style="color: #e53e3e; font-size: 1.8rem; margin: 0;">{{ $mediaGeralGestor ? number_format($mediaGeralGestor, 2) : 'N/A' }}</h2>
        </div>
        <div style="width: 45px; height: 45px; border-radius: 50%; background: #fff5f5; color: #e53e3e; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
            <i class="fa-solid fa-users"></i>
        </div>
    </div>

    <div class="form-card" style="padding: 20px; border-radius: 12px; display: flex; align-items: center; justify-content: space-between; border-left: 5px solid #f39c12; margin-bottom: 0; background: #fffaf0; border: 1px solid #fbd38d; border-left-width: 5px;">
        <div>
            <p style="color: #718096; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; margin-bottom: 5px;">Nota Sugerida Geral</p>
            <h2 style="color: #dd6b20; font-size: 1.8rem; margin: 0;">{{ $notaSugerida ? number_format($notaSugerida, 2) : 'N/A' }}</h2>
        </div>
        <div style="width: 45px; height: 45px; border-radius: 50%; background: #fffff0; color: #dd6b20; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
            <i class="fa-solid fa-square-poll-vertical"></i>
        </div>
    </div>
</div>

<form action="{{ route('avaliacoes.salvar-fechamento', [$avaliacao->id, $avaliado->id]) }}" method="POST" data-confirm="Deseja consolidar e fechar esta mediação? Esta ação gravará a nota final calculada." data-confirm-title="Confirmar Consolidação" data-confirm-type="success">
    @csrf

    <div style="display: flex; flex-wrap: wrap; gap: 30px; align-items: start;">
        
        {{-- Área das Perguntas por Competência --}}
        <div style="flex: 2; min-width: 350px;">
            
            @forelse($avaliacao->competencias as $competencia)
                <div class="form-card" style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 20px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); padding: 0;">
                    <div style="background: #f8fafc; padding: 15px 20px; border-bottom: 1px solid #e2e8f0;">
                        <h3 style="margin: 0; color: #2d3748; font-size: 1.1rem; font-weight: bold; display: flex; align-items: center; gap: 8px;">
                            <span style="width: 4px; height: 16px; background-color: #6a4c7c; border-radius: 2px;"></span>
                            Competência: {{ $competencia->nome }}
                        </h3>
                        @if($competencia->descricao)
                            <p style="margin: 5px 0 0 0; font-size: 0.85rem; color: #718096;">{{ $competencia->descricao }}</p>
                        @endif
                    </div>
                    
                    <div style="padding: 20px;">
                        @forelse($competencia->skills as $skill)
                            @php
                                $skillId = $skill->id;
                                $notaAuto = $skillsNotas[$skillId]['autoavaliacao'] ?? null;
                                $mediaGestores = null;
                                if (isset($skillsNotas[$skillId]['gestores']) && !empty($skillsNotas[$skillId]['gestores'])) {
                                    $mediaGestores = array_sum($skillsNotas[$skillId]['gestores']) / count($skillsNotas[$skillId]['gestores']);
                                }
                                $notaAnteriorMediador = $respostasMediador[$skillId] ?? null;
                            @endphp
                            <div style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px dashed #e2e8f0; text-align: center;">
                                <p style="margin: 0 0 10px 0; font-weight: 600; font-size: 1.05rem; color: #2d3748;">{{ $skill->nome }}</p>
                                
                                {{-- Badges de Notas Comparativas --}}
                                <div style="display: flex; gap: 10px; justify-content: center; margin-bottom: 15px; flex-wrap: wrap;">
                                    <span style="display: inline-flex; align-items: center; gap: 4px; background-color: #ebf8ff; color: #2b6cb0; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold;">
                                        <i class="fa-solid fa-user" style="font-size: 0.7rem;"></i> Autoavaliação: {{ $notaAuto ? number_format($notaAuto, 1) : '-' }}
                                    </span>
                                    <span style="display: inline-flex; align-items: center; gap: 4px; background-color: #fff5f5; color: #e53e3e; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold;">
                                        <i class="fa-solid fa-users" style="font-size: 0.7rem;"></i> Média Avaliadores: {{ $mediaGestores ? number_format($mediaGestores, 1) : '-' }}
                                    </span>
                                </div>
                                
                                {{-- Decisão do Mediador (Escala de 1 a 5) --}}
                                <div style="display: flex; justify-content: center; gap: 15px; align-items: center;">
                                    <span style="font-size: 0.8rem; color: #a0aec0; font-weight: 500;">Discordo</span>
                                    
                                    @for($i = 1; $i <= 5; $i++)
                                        <label style="display: flex; flex-direction: column; align-items: center; cursor: pointer; padding: 5px 8px; border-radius: 6px; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f7fafc'" onmouseout="this.style.backgroundColor='transparent'">
                                            <input type="radio" name="respostas[{{ $skill->id }}]" value="{{ $i }}" required style="width: 22px; height: 22px; cursor: pointer; accent-color: #dd6b20;" {{ $notaAnteriorMediador == $i ? 'checked' : '' }}>
                                            <span style="margin-top: 5px; font-weight: bold; color: #4a5568; font-size: 0.95rem;">{{ $i }}</span>
                                        </label>
                                    @endfor
                                    
                                    <span style="font-size: 0.8rem; color: #a0aec0; font-weight: 500;">Concordo</span>
                                </div>
                            </div>
                        @empty
                            <p style="color: #718096; text-align: center; font-style: italic;">Nenhuma skill cadastrada para esta competência.</p>
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="form-card" style="text-align: center; padding: 30px;">
                    <p style="color: #718096; font-style: italic;">Esta avaliação não possui competências vinculadas.</p>
                </div>
            @endforelse
            
        </div>

        {{-- Painel Lateral de Fechamento Geral --}}
        <div style="flex: 1; min-width: 300px; position: sticky; top: 20px;">
            <div class="form-card" style="border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border-top: 4px solid #dd6b20; background: white; border: 1px solid #e2e8f0; border-top-width: 4px;">
                <h3 style="margin-top: 0; color: #dd6b20; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; font-weight: bold; font-size: 1.15rem; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-gavel"></i> Decisão da Mediação
                </h3>

                @if($fechamento)
                    <div style="background-color: #ebf8ff; border: 1px solid #bee3f8; border-radius: 8px; padding: 12px; margin-bottom: 20px; font-size: 0.85rem; color: #2b6cb0;">
                        <i class="fa-solid fa-circle-info"></i> Fechamento já realizado anteriormente com nota final consolidada de <strong>{{ number_format($fechamento->nota_final, 2) }}</strong>. Ao reenviar, a nota e o comentário serão atualizados.
                    </div>
                @endif
                
                <div class="form-group" style="margin-bottom: 25px;">
                    <label class="form-label" for="comentario" style="font-weight: 600; color: #2d3748;">Feedback Final de Fechamento <span style="color: red;">*</span></label>
                    <textarea id="comentario" name="comentario" class="form-control" rows="8" placeholder="Sintetize os resultados da mediação e fundamente o feedback de fechamento do colaborador..." style="resize: none; border-radius: 8px; border-color: #cbd5e0;" required>{{ old('comentario', $fechamento->comentario ?? '') }}</textarea>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 1rem; background: linear-gradient(90deg, #dd6b20 0%, #b7791f 100%); border: none;">
                    <i class="fa-solid fa-lock"></i> Encerrar & Consolidar
                </button>
            </div>
        </div>

    </div>
</form>
@endsection
