@extends('layouts.app')

@section('title', 'Relatório de Clima Organizacional')

@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<style>
.dash-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.dash-stat-card {
    background: white;
    padding: 22px 25px;
    border-radius: 16px;
    border: 1px solid #f0f2f5;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    display: flex;
    align-items: center;
    gap: 18px;
}

.dash-stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}

.dimensao-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    border: 1px solid #f0f2f5;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    margin-bottom: 25px;
}

.progress-bar-bg {
    width: 100%;
    height: 10px;
    background: #f1f5f9;
    border-radius: 999px;
    overflow: hidden;
}
.progress-bar-fill {
    height: 100%;
    border-radius: 999px;
    transition: width 0.5s ease;
}
</style>

<div class="header-actions" style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
            <h1 class="page-title" style="margin: 0;">
                <i class="fa-solid fa-chart-pie" style="color: #ec4899;"></i> {{ $survey->titulo }}
            </h1>
            <span style="padding: 4px 12px; border-radius: 9999px; font-size: 0.76rem; font-weight: 800; background: #fdf2f8; color: #db2777; border: 1px solid #fbcfe8;">
                <i class="fa-solid fa-user-shield"></i> Dados Anônimos
            </span>
        </div>
        <p style="color: #64748b; font-size: 0.95rem; margin: 0;">
            Relatório analítico e inteligência de clima da empresa.
        </p>
    </div>

    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        @if(in_array(auth()->user()->perfil_acesso->value, ['master', 'admin']))
        <button id="btnGerarPdiClima" onclick="gerarPdiClimaIa()" style="padding: 10px 20px; border-radius: 10px; border: none; background: linear-gradient(135deg, #d946ef, #c026d3); color: white; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(217, 70, 239, 0.4); transition: transform 0.2s;">
            <i class="fa-solid fa-wand-magic-sparkles"></i> Gerar Plano de Ação Organizacional (IA)
        </button>
        @endif
        <a href="{{ route('gestao.clima.index') }}" style="padding: 10px 20px; border-radius: 10px; border: 1px solid #cbd5e0; background: white; color: #4a5568; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-arrow-left"></i> Voltar à Lista
        </a>
    </div>
</div>

{{-- METRICAS DE TOPO --}}
<div class="dash-stats-grid">
    {{-- SCORE GERAL --}}
    <div class="dash-stat-card" style="flex-direction: column; align-items: flex-start; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div class="dash-stat-icon" style="background: rgba(236, 72, 153, 0.1); color: #ec4899;">
                <i class="fa-solid fa-star"></i>
            </div>
            <div>
                <div style="font-size: 0.78rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Score Geral de Clima</div>
                <div style="font-size: 1.7rem; font-weight: 800; color: #1e293b; margin-top: 2px;">
                    {{ number_format($mediaGeral, 2, ',', '.') }} <small style="font-size: 0.9rem; color: #94a3b8;">/ 10</small>
                </div>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 5px;"><i class="fa-solid fa-circle-info"></i> Média de todas as respostas avaliativas (0 a 10).</div>
    </div>

    {{-- TAXA DE FAVORABILIDADE --}}
    <div class="dash-stat-card" style="flex-direction: column; align-items: flex-start; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div class="dash-stat-icon" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;">
                <i class="fa-solid fa-face-smile"></i>
            </div>
            <div>
                <div style="font-size: 0.78rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Favorabilidade</div>
                <div style="font-size: 1.7rem; font-weight: 800; color: #16a34a; margin-top: 2px;">
                    {{ $taxaFavorabilidade }}%
                </div>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 5px;"><i class="fa-solid fa-circle-info"></i> Respostas com notas de 8 a 10.</div>
    </div>

    {{-- TAXA DE ADESÃO --}}
    <div class="dash-stat-card" style="flex-direction: column; align-items: flex-start; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div class="dash-stat-icon" style="background: rgba(106, 76, 124, 0.1); color: #6a4c7c;">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div>
                <div style="font-size: 0.78rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Taxa de Resposta</div>
                <div style="font-size: 1.7rem; font-weight: 800; color: #1e293b; margin-top: 2px;">
                    {{ $taxaAdesao }}% 
                </div>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 5px;"><i class="fa-solid fa-circle-info"></i> {{ $totalRespondidos }} de {{ $totalParticipacoes }} participantes finalizaram.</div>
    </div>

    {{-- COMENTÁRIOS ANÔNIMOS --}}
    <div class="dash-stat-card" style="flex-direction: column; align-items: flex-start; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div class="dash-stat-icon" style="background: #ebf8ff; color: #3182ce;">
                <i class="fa-solid fa-comments"></i>
            </div>
            <div>
                <div style="font-size: 0.78rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Comentários Livres</div>
                <div style="font-size: 1.7rem; font-weight: 800; color: #1e293b; margin-top: 2px;">
                    {{ $comentarios->count() }}
                </div>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 5px;"><i class="fa-solid fa-circle-info"></i> Feedbacks anônimos deixados pela equipe.</div>
    </div>
</div>

{{-- PAINEL DE DIMENSÕES E CATEGORIAS --}}
<div class="dimensao-card">
    <h3 style="font-size: 1.1rem; font-weight: 800; color: #1e293b; margin-bottom: 20px; border-bottom: 1px solid #edf2f7; padding-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
        <div><i class="fa-solid fa-layer-group" style="color: #ec4899; margin-right: 8px;"></i> Resultados por Categoria / Tema</div>
        <span style="font-size: 0.8rem; font-weight: 600; color: #94a3b8; background: #f1f5f9; padding: 4px 10px; border-radius: 8px;">Nota máxima: 10</span>
    </h3>

    <div style="display: flex; flex-direction: column; gap: 20px;">
        @foreach($dimensoesStats as $dimNome => $dData)
            @php
                // Corrigido para dividir por 10 (nota máxima)
                $pctFill = ($dData['media'] / 10) * 100;
                
                $corBarra = '#38a169'; // verde (>= 7)
                if ($dData['media'] < 5.0) { $corBarra = '#e53e3e'; } // vermelho
                elseif ($dData['media'] < 7.0) { $corBarra = '#d69e2e'; } // amarelo
            @endphp
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <div style="font-weight: 700; color: #334155; font-size: 0.95rem;">{{ $dimNome }}</div>
                    <div style="display: flex; gap: 15px; font-weight: 800; font-size: 0.9rem;">
                        <span style="color: {{ $corBarra }};">Média: {{ number_format($dData['media'], 2, ',', '.') }}</span>
                        <span style="color: #64748b; font-weight: 600;">Fav: {{ $dData['favorabilidade'] }}%</span>
                    </div>
                </div>
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill" style="width: {{ $pctFill }}%; background: {{ $corBarra }};"></div>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- MÉDIAS POR PERGUNTA --}}
@if(count($perguntasStats ?? []) > 0)
<div class="dimensao-card">
    <h3 style="font-size: 1.1rem; font-weight: 800; color: #1e293b; margin-bottom: 25px; border-bottom: 1px solid #edf2f7; padding-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
        <div><i class="fa-solid fa-list-check" style="color: #3b82f6; margin-right: 8px;"></i> Resultados por Pergunta</div>
        <span style="font-size: 0.8rem; font-weight: 600; color: #94a3b8; background: #f1f5f9; padding: 4px 10px; border-radius: 8px;">Nota máxima: 10</span>
    </h3>

    <div style="display: flex; flex-direction: column; gap: 20px;">
        @foreach($perguntasStats as $pStat)
            @php
                $pctFillP = ($pStat['media'] / 10) * 100;
                
                $corBarraP = '#38a169'; // verde (>= 7)
                if ($pStat['media'] < 5.0) { $corBarraP = '#e53e3e'; } // vermelho
                elseif ($pStat['media'] < 7.0) { $corBarraP = '#d69e2e'; } // amarelo
            @endphp
            <div style="background: #fafbfc; border-radius: 12px; padding: 16px 20px; border: 1px solid #edf2f7; border-left: 4px solid #3b82f6;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                    <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem; flex: 1; padding-right: 15px;">
                        <i class="fa-solid fa-circle-question" style="color: #3b82f6; margin-right: 6px;"></i> {{ $pStat['pergunta'] }}
                        @if($pStat['dimensao'])
                            <span style="font-size: 0.75rem; color: #64748b; background: #e2e8f0; padding: 2px 8px; border-radius: 12px; margin-left: 8px; white-space: nowrap;">{{ $pStat['dimensao'] }}</span>
                        @endif
                    </div>
                    <div style="display: flex; gap: 15px; font-weight: 800; font-size: 0.95rem;">
                        <span style="color: {{ $corBarraP }};">Média: {{ number_format($pStat['media'], 2, ',', '.') }}</span>
                        <span style="color: #64748b; font-weight: 600;">Fav: {{ $pStat['favorabilidade'] }}%</span>
                    </div>
                </div>
                
                <div class="progress-bar-bg" style="height: 6px; margin-bottom: 15px;">
                    <div class="progress-bar-fill" style="width: {{ $pctFillP }}%; background: {{ $corBarraP }};"></div>
                </div>

                {{-- NOTAS INDIVIDUAIS --}}
                @if(count($pStat['respostas']) > 0)
                    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                        <div style="font-size: 0.75rem; font-weight: 800; color: #94a3b8; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.05em;"><i class="fa-solid fa-users" style="margin-right: 4px;"></i> Respostas Anônimas Individuais</div>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @foreach($pStat['respostas'] as $resp)
                                @php
                                    $corNota = '#38a169'; // verde (>= 7)
                                    if ($resp['nota'] < 5.0) { $corNota = '#e53e3e'; } // vermelho
                                    elseif ($resp['nota'] < 7.0) { $corNota = '#d69e2e'; } // amarelo
                                @endphp
                                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px dashed #edf2f7;">
                                    <div style="font-size: 0.8rem; color: #475569; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                        <span><i class="fa-solid fa-user-ninja" style="color: #cbd5e1; margin-right: 4px;"></i> Colaborador Anônimo</span>
                                        @if($resp['departamento'])
                                            <span><i class="fa-solid fa-building" style="color: #cbd5e1; margin-right: 4px;"></i> {{ $resp['departamento'] }}</span>
                                        @endif
                                        <span style="color: #94a3b8;"><i class="fa-solid fa-clock" style="margin-right: 4px;"></i> {{ $resp['data'] }}</span>
                                    </div>
                                    <div style="font-weight: 800; font-size: 0.9rem; color: {{ $corNota }}; background: rgba(0,0,0,0.03); padding: 2px 10px; border-radius: 6px;">
                                        Nota: {{ $resp['nota'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div style="font-size: 0.85rem; color: #94a3b8; font-style: italic;">Nenhuma resposta registrada para esta pergunta.</div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- MÉDIAS POR DEPARTAMENTO --}}
@if(count($statsDepartamentos) > 0)
<div class="dimensao-card">
    <h3 style="font-size: 1.1rem; font-weight: 800; color: #1e293b; margin-bottom: 25px;">
        <i class="fa-solid fa-building" style="color: #6a4c7c; margin-right: 8px;"></i> Score por Departamento
    </h3>

    <div style="display: flex; flex-direction: column; gap: 15px;">
        @php
            // Descobre a maior média para usar como proporção visual na barra (se quiser sempre base 10, usa 10)
            $maxScore = 10;
        @endphp

        @foreach($statsDepartamentos as $depNome => $depInfo)
            @php
                $pctFillDep = ($depInfo['media'] / $maxScore) * 100;
                $corBarraDep = '#38a169'; 
                if ($depInfo['media'] < 5.0) { $corBarraDep = '#e53e3e'; } 
                elseif ($depInfo['media'] < 7.0) { $corBarraDep = '#d69e2e'; }
            @endphp
            
            <div style="display: flex; align-items: center; gap: 20px;">
                <div style="width: 150px; font-size: 0.9rem; font-weight: 700; color: #475569; text-align: right; line-height: 1.2;">
                    {{ $depNome }}
                    <div style="font-size: 0.7rem; color: #94a3b8; font-weight: 600;">{{ $depInfo['total'] }} respostas</div>
                </div>
                
                <div style="flex: 1; display: flex; align-items: center; gap: 15px;">
                    <div style="flex: 1; height: 16px; background: #f1f5f9; border-radius: 999px; overflow: hidden;">
                        <div style="height: 100%; width: {{ $pctFillDep }}%; background: {{ $corBarraDep }}; border-radius: 999px; transition: width 0.5s ease;"></div>
                    </div>
                    <div style="width: 50px; font-weight: 800; font-size: 1.05rem; color: {{ $corBarraDep }};">
                        {{ number_format($depInfo['media'], 2, ',', '.') }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- MURAL DE COMENTÁRIOS ANÔNIMOS --}}
<div class="dimensao-card">
    <h3 style="font-size: 1.1rem; font-weight: 800; color: #1e293b; margin-bottom: 15px; border-bottom: 1px solid #edf2f7; padding-bottom: 10px;">
        <i class="fa-solid fa-comments" style="color: #ec4899; margin-right: 8px;"></i> Feedback & Comentários Anônimos da Equipe
    </h3>

    <div style="display: flex; flex-direction: column; gap: 12px;">
        @forelse($comentarios as $c)
            <div style="background: #fafbfc; border-radius: 12px; padding: 16px 20px; border: 1px solid #edf2f7; border-left: 4px solid #ec4899;">
                @if($c->pergunta)
                    <div style="font-size: 0.85rem; font-weight: 700; color: #db2777; margin-bottom: 6px;">
                        <i class="fa-solid fa-circle-question" style="margin-right: 4px;"></i> {{ $c->pergunta->texto_pergunta }}
                    </div>
                @endif
                <p style="color: #334155; font-size: 0.92rem; margin: 0 0 8px 0; line-height: 1.5; font-style: italic;">
                    "{{ $c->comentario }}"
                </p>
                <div style="font-size: 0.78rem; color: #94a3b8; font-weight: 600; display: flex; gap: 12px;">
                    <span><i class="fa-solid fa-user-ninja"></i> Colaborador Anônimo</span>
                    @if($c->departamento)
                        <span><i class="fa-solid fa-building"></i> {{ $c->departamento->nome }}</span>
                    @endif
                    <span><i class="fa-solid fa-clock"></i> {{ $c->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        @empty
            <div style="padding: 30px; text-align: center; color: #a0aec0;">
                <i class="fa-solid fa-comment-slash" style="font-size: 2rem; margin-bottom: 8px; display: block;"></i>
                Nenhum comentário em texto foi registrado nesta pesquisa.
            </div>
        @endforelse
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    function gerarPdiClimaIa() {
        window.showConfirm(
            'Deseja gerar um Plano de Ação Organizacional com IA com base nestes resultados? Isso descontará 1 crédito do seu plano.',
            async function() {
                const btn = document.getElementById('btnGerarPdiClima');
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> A IA está analisando o Clima...';
                btn.disabled = true;

                // Capturar dados visuais da página
                const dimensoesStats = @json($dimensoesStats ?? []);
                const statsDepartamentos = @json($statsDepartamentos ?? []);
                const comentariosBrutos = @json($comentarios ?? []);
                const comentarios = comentariosBrutos.map(c => c.comentario);

                const payload = {
                    dimensoesStats: dimensoesStats,
                    statsDepartamentos: statsDepartamentos,
                    comentarios: comentarios
                };

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const res = await fetch(`{{ route('gestao.clima.gerar_pdi_ia', $survey->id) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                    
                    const data = await res.json();
                    
                    if(!res.ok || !data.success) {
                        throw new Error(data.error || 'Erro desconhecido');
                    }

                    if (data.queued) {
                        window.showAlert(data.message, 'Processando...', 'success');
                    } else {
                        abrirModalPdiIa(data.dados, {{ auth()->id() }}, 'Plano Organizacional de Clima');
                    }
                    
                } catch (error) {
                    console.error(error);
                    window.showAlert('Falha ao gerar PDI com IA: ' + error.message, 'Erro', 'danger');
                } finally {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            },
            'Gerar PDI com IA',
            'info'
        );
    }
</script>

{{-- Inclusão do Modal de IA --}}
@include('pdis.partials.modal_ai_create')
@endpush
