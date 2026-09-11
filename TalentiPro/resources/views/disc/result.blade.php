@extends('layouts.app')

@section('title', 'Resultado DISC')

@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<style>
.disc-result-card {
    background: white;
    border-radius: 16px;
    padding: 35px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    border: 1px solid #f0f2f5;
}
.disc-result-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    align-items: center;
}
@media (max-width: 900px) {
    .disc-result-card {
        padding: 20px 15px !important;
    }
    .disc-result-grid {
        grid-template-columns: 1fr !important;
        gap: 25px !important;
    }
    .disc-chart-box {
        padding: 10px !important;
    }
    .disc-actions-wrap {
        justify-content: center !important;
    }
    .disc-actions-wrap button,
    .disc-actions-wrap a {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="header-actions" style="margin-bottom: 25px;">
    <div>
        <h1 class="page-title" style="margin-bottom: 8px;"><i class="fa-solid fa-chart-radar" style="color: #6a4c7c;"></i> Seu Perfil DISC</h1>
        <p style="color: #64748b; font-size: 0.95rem;">Veja os resultados da sua avaliação comportamental detalhados abaixo.</p>
    </div>
</div>

<div class="disc-result-card">
    
    <div class="disc-result-grid">
        
        <!-- Lado Esquerdo: Gráfico Radar -->
        <div class="disc-chart-box" style="display: flex; justify-content: center; align-items: center; background: #fafbfc; border-radius: 16px; padding: 20px; border: 1px dashed #e2e8f0; width: 100%; box-sizing: border-box;">
            <div style="width: 100%; max-width: 450px;">
                <canvas id="discChart"></canvas>
            </div>
        </div>
        
        <!-- Lado Direito: Resultados Textuais -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            <div style="background: linear-gradient(135deg, rgba(106, 76, 124, 0.1) 0%, rgba(106, 76, 124, 0.05) 100%); border-left: 5px solid #6a4c7c; padding: 20px 25px; border-radius: 0 12px 12px 0;">
                <h2 style="font-size: 1.6rem; font-weight: 800; color: #2d3748; margin-bottom: 5px;">
                    Perfil Primário: <span style="color: #6a4c7c;">{{ mb_strtoupper($assessment->primary_profile_br) }}</span>
                </h2>
                @if($assessment->secondary_profile_br)
                <p style="font-size: 1rem; color: #4a5568; font-weight: 600; text-transform: uppercase;">
                    PERFIL SECUNDÁRIO: {{ mb_strtoupper($assessment->secondary_profile_br) }}
                </p>
                @endif
            </div>

            <div style="background: #ffffff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                <h4 style="font-size: 1.05rem; font-weight: 700; color: #2d3748; margin-bottom: 15px; border-bottom: 1px solid #edf2f7; padding-bottom: 10px;"><i class="fa-solid fa-list-ol" style="color: #a0aec0; margin-right: 8px;"></i> Distribuição de Pontos</h4>
                
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px;">
                    <li style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #fff5f5; border-radius: 8px;">
                        <span style="color: #c53030; font-weight: 700; display: flex; align-items: center; gap: 10px;"><span style="width: 12px; height: 12px; background: #e53e3e; border-radius: 50%; display: inline-block;"></span> Dominância (D)</span>
                        <span style="font-weight: 800; background: white; padding: 4px 12px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); color: #2d3748;">{{ $assessment->score_d }}</span>
                    </li>
                    <li style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #fffff0; border-radius: 8px;">
                        <span style="color: #b7791f; font-weight: 700; display: flex; align-items: center; gap: 10px;"><span style="width: 12px; height: 12px; background: #d69e2e; border-radius: 50%; display: inline-block;"></span> Influência (I)</span>
                        <span style="font-weight: 800; background: white; padding: 4px 12px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); color: #2d3748;">{{ $assessment->score_i }}</span>
                    </li>
                    <li style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #f0fff4; border-radius: 8px;">
                        <span style="color: #276749; font-weight: 700; display: flex; align-items: center; gap: 10px;"><span style="width: 12px; height: 12px; background: #38a169; border-radius: 50%; display: inline-block;"></span> Estabilidade (S)</span>
                        <span style="font-weight: 800; background: white; padding: 4px 12px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); color: #2d3748;">{{ $assessment->score_s }}</span>
                    </li>
                    <li style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #ebf8ff; border-radius: 8px;">
                        <span style="color: #2b6cb0; font-weight: 700; display: flex; align-items: center; gap: 10px;"><span style="width: 12px; height: 12px; background: #3182ce; border-radius: 50%; display: inline-block;"></span> Conformidade (C)</span>
                        <span style="font-weight: 800; background: white; padding: 4px 12px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); color: #2d3748;">{{ $assessment->score_c }}</span>
                    </li>
                </ul>
            </div>
            
            <div class="disc-actions-wrap" style="text-align: right; margin-top: 5px; display: flex; justify-content: flex-end; gap: 10px; flex-wrap: wrap;">

                @php
                    $user = auth()->user();
                    $isMasterOrAdmin = in_array($user->perfil_acesso->value, ['master', 'admin']);

                    // Verifica solicitação pendente existente para não-masters
                    $solicitacaoPendente = null;
                    if (!$isMasterOrAdmin) {
                        $solicitacaoPendente = \App\Models\Solicitacao::where('user_id', $user->id)
                            ->where('disc_assessment_id', $assessment->id)
                            ->where('tipo', 'reabertura_disc')
                            ->whereIn('status', ['pendente', 'aprovada'])
                            ->first();
                    }
                @endphp

                @if($isMasterOrAdmin)
                    {{-- Master/Admin: refaz imediatamente --}}
                    <form action="{{ route('disc.retake') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn-secondary" style="padding: 12px 25px; border-radius: 8px; font-weight: bold; display: inline-flex; align-items: center; gap: 8px; background: transparent; border: 1px solid #6a4c7c; color: #6a4c7c; cursor: pointer;">
                            <i class="fa-solid fa-rotate-right"></i> Responder Novamente
                        </button>
                    </form>
                @elseif($solicitacaoPendente)
                    {{-- Já tem solicitação em aberto --}}
                    <span style="padding: 12px 20px; border-radius: 8px; background: #fffbeb; border: 1px solid #f6e05e; color: #92400e; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-clock"></i>
                        @if($solicitacaoPendente->status === 'pendente')
                            Solicitação de refazer aguardando aprovação do RH
                        @else
                            Solicitação aprovada — entre em contato com o RH
                        @endif
                    </span>
                @else
                    {{-- Colaborador/Gestor: abre modal de justificativa --}}
                    <button type="button"
                            onclick="document.getElementById('modal-disc-solicitar').style.display='flex'"
                            style="padding: 12px 25px; border-radius: 8px; font-weight: bold; display: inline-flex; align-items: center; gap: 8px; background: transparent; border: 1px solid #6a4c7c; color: #6a4c7c; cursor: pointer;">
                        <i class="fa-solid fa-paper-plane"></i> Solicitar Refazer
                    </button>
                @endif

                <a href="{{ route('dashboard') }}" class="btn-primary" style="padding: 12px 25px; border-radius: 8px; font-weight: bold; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel
                </a>
            </div>

        </div>
    </div>
</div>


{{-- Modal: Solicitar Refazer DISC (apenas para não-masters) --}}
@if(!in_array(auth()->user()->perfil_acesso->value, ['master', 'admin']))
<div id="modal-disc-solicitar"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:white; border-radius:16px; padding:40px; max-width:520px; width:100%; box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <h3 style="font-size:1.3rem; font-weight:800; color:#2d3748; margin-bottom:8px;">
            <i class="fa-solid fa-paper-plane" style="color:#6a4c7c;"></i> Solicitar Refazer o Teste DISC
        </h3>
        <p style="color:#64748b; font-size:0.9rem; margin-bottom:20px; line-height:1.6;">
            Sua solicitação será enviada para o RH para aprovação. Justifique abaixo o motivo pelo qual deseja refazer o teste.
        </p>

        @if(session('error'))
            <div style="background:#fff5f5; border:1px solid #feb2b2; color:#c53030; padding:12px; border-radius:8px; margin-bottom:16px; font-size:0.9rem;">
                <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('disc.solicitar-refazer') }}" method="POST">
            @csrf
            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:700; color:#4a5568; margin-bottom:8px; font-size:0.9rem;">
                    Justificativa <span style="color:#e53e3e;">*</span>
                </label>
                <textarea name="justificativa"
                          rows="4"
                          placeholder="Ex: Errei ao preencher algumas respostas e gostaria de refazer o teste para ter um resultado mais preciso..."
                          style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:8px; font-size:0.9rem; resize:vertical; font-family:inherit; box-sizing:border-box; outline:none;"
                          required minlength="20">{{ old('justificativa') }}</textarea>
                <p style="font-size:0.78rem; color:#a0aec0; margin-top:4px;">Mínimo de 20 caracteres.</p>
            </div>

            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button"
                        onclick="document.getElementById('modal-disc-solicitar').style.display='none'"
                        style="padding:10px 20px; border-radius:8px; border:1px solid #e2e8f0; background:white; color:#4a5568; font-weight:600; cursor:pointer;">
                    Cancelar
                </button>
                <button type="submit"
                        class="btn-primary"
                        style="padding:10px 24px; border-radius:8px; font-weight:700; display:inline-flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-paper-plane"></i> Enviar Solicitação
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tenta carregar o Chart.js do Vite (window.Chart) ou do CDN (Chart)
    const ChartClass = window.Chart || Chart;
    
    if (typeof ChartClass !== 'undefined') {
        const ctx = document.getElementById('discChart').getContext('2d');
        
        new ChartClass(ctx, {
            type: 'radar',
            data: {
                labels: [
                    'Dominância {{ $assessment->score_d }}', 
                    'Influência {{ $assessment->score_i }}', 
                    'Estabilidade {{ $assessment->score_s }}', 
                    'Conformidade {{ $assessment->score_c }}'
                ],
                datasets: [{
                    label: 'Sua Pontuação',
                    data: [
                        {{ $assessment->score_d }}, 
                        {{ $assessment->score_i }}, 
                        {{ $assessment->score_s }}, 
                        {{ $assessment->score_c }}
                    ],
                    backgroundColor: 'rgba(106, 76, 124, 0.2)', // Roxinho da marca
                    borderColor: 'rgba(106, 76, 124, 1)',
                    pointBackgroundColor: [
                        '#e53e3e', // Red
                        '#d69e2e', // Yellow
                        '#38a169', // Green
                        '#3182ce'  // Blue
                    ],
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(106, 76, 124, 1)',
                    borderWidth: 3,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                layout: {
                    padding: { left: 30, right: 30, top: 20, bottom: 20 }
                },
                scales: {
                    r: {
                        angleLines: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.15)' 
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.15)' 
                        },
                        pointLabels: {
                            font: function(context) {
                                var width = context.chart.width;
                                return {
                                    size: width < 400 ? 10 : 13, // Menor no mobile, maior no desktop
                                    weight: 'bold',
                                    family: "'Inter', sans-serif"
                                };
                            },
                            color: '#2d3748'
                        },
                        ticks: {
                            display: false, // Esconde os numerozinhos (25, 50, 75...) para não encavalar
                            min: 0,
                            max: 100, // Maximum points possible is 100
                            stepSize: 25,
                            color: '#718096', 
                            backdropColor: 'transparent',
                            font: {
                                size: 11,
                                weight: 'bold',
                                family: "'Inter', sans-serif"
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(45, 55, 72, 0.9)',
                        titleFont: { size: 14, family: "'Inter', sans-serif" },
                        bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: true
                    }
                }
            }
        });
    } else {
        console.error('Chart.js não foi carregado corretamente.');
    }
});
</script>
@endpush
@endsection
