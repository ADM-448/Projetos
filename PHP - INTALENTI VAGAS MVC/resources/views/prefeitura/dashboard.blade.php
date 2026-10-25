@extends('layouts.app')

@section('title', 'Observatório do Trabalho - Peerlist Edition')

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .pl-chart-container {
        position: relative;
        height: 320px;
        width: 100%;
        max-height: 320px;
        overflow: hidden;
    }

    #mapaCalor {
        height: 420px;
        width: 100%;
        border-radius: var(--pl-radius-md);
        z-index: 1;
    }

    .kpi-card {
        background: #ffffff;
        border: 1px solid var(--pl-border);
        border-radius: var(--pl-radius-lg);
        padding: 20px;
        transition: all 0.2s ease;
    }

    .kpi-card:hover {
        border-color: var(--pl-border-hover);
        box-shadow: var(--pl-shadow-md);
    }

    .kpi-icon-circle {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 12px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-lg-5 py-4" style="max-width: 1440px;">
    
    <!-- Topo / Header da Página -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="pl-portal-badge">
                    <i class="fa-solid fa-landmark me-1"></i> Gestão Municipal
                </span>
                <span class="badge bg-light text-muted border">Painel Analítico</span>
            </div>
            <h2 class="h3 fw-bold mb-1">Observatório do Trabalho Municipal</h2>
            <p class="text-muted small mb-0">Monitoramento em tempo real de vagas ativas, empregabilidade e concentração demográfica.</p>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-pl-secondary btn-sm d-flex align-items-center gap-2" onclick="window.print()">
                <i class="fa-solid fa-print"></i>
                <span>Imprimir Relatório</span>
            </button>
            <a href="{{ route('vagas.index') }}" class="btn btn-pl-primary btn-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-briefcase"></i>
                <span>Explorar Vagas</span>
            </a>
        </div>
    </div>

    <!-- 4 KPIs do Observatório -->
    <div class="row g-3 mb-4">
        <!-- Vagas Ativas -->
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon-circle" style="background: var(--pl-green-soft); color: var(--pl-green);">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                <div class="h3 fw-bold mb-0 text-dark">{{ number_format($kpis['vagas_ativas'], 0, ',', '.') }}</div>
                <small class="text-muted fw-semibold">Vagas Ativas</small>
            </div>
        </div>

        <!-- Empresas Cadastradas -->
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon-circle" style="background: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-building"></i>
                </div>
                <div class="h3 fw-bold mb-0 text-dark">{{ number_format($kpis['total_empresas'], 0, ',', '.') }}</div>
                <small class="text-muted fw-semibold">Empresas Cadastradas</small>
            </div>
        </div>

        <!-- Candidatos no Banco -->
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon-circle" style="background: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="h3 fw-bold mb-0 text-dark">{{ number_format($kpis['total_candidatos'], 0, ',', '.') }}</div>
                <small class="text-muted fw-semibold">Candidatos Cadastrados</small>
            </div>
        </div>

        <!-- Massa Salarial Estimada -->
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon-circle" style="background: #f3e8ff; color: #9333ea;">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
                <div class="h3 fw-bold mb-0 text-dark">
                    @if($kpis['impacto_economico'] >= 1000)
                        R$ {{ number_format($kpis['impacto_economico'] / 1000, 1, ',', '.') }}k
                    @else
                        R$ {{ number_format($kpis['impacto_economico'], 2, ',', '.') }}
                    @endif
                </div>
                <small class="text-muted fw-semibold">Massa Salarial Estimada</small>
            </div>
        </div>
    </div>

    <!-- Mapa Geográfico de Vagas e Candidatos (Item 354) -->
    <div class="pl-card p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="fw-bold mb-1" style="font-size: 1.05rem;">
                    <i class="fa-solid fa-map-location-dot text-success me-2"></i> Mapeamento Geográfico: Vagas & Candidatos
                </h5>
                <p class="text-muted small mb-0">Visualização combinada da concentração de vagas (azul) e profissionais residentes (verde).</p>
            </div>
            <!-- Legenda / Filtro de Camadas -->
            <div class="d-flex gap-2">
                <span class="pl-chip">
                    <i class="fa-solid fa-circle text-primary" style="font-size: 0.6rem;"></i> Vagas ({{ count($pontosVagas) }})
                </span>
                <span class="pl-chip">
                    <i class="fa-solid fa-circle text-success" style="font-size: 0.6rem;"></i> Candidatos ({{ count($pontosCandidatos) }})
                </span>
            </div>
        </div>

        <div id="mapaCalor" class="border"></div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Gráfico de Vagas por Setor (Com Altura Estritamente Fixada) -->
        <div class="col-12 col-lg-6">
            <div class="pl-card p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0" style="font-size: 1.05rem;">Oportunidades por Setor de Atividade</h5>
                    <span class="badge bg-light text-muted border">Vagas Ativas</span>
                </div>
                
                <!-- Container com altura rigorosamente controlada para o Chart.js -->
                <div class="pl-chart-container">
                    <canvas id="chartSetores"></canvas>
                </div>
            </div>
        </div>

        <!-- Lista de Candidatos Recentes (Com data correta - Item 352) -->
        <div class="col-12 col-lg-6">
            <div class="pl-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0" style="font-size: 1.05rem;">Candidatos Cadastrados Recentemente</h5>
                    <small class="text-muted">Data oficial de inscrição</small>
                </div>

                <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="small text-muted text-uppercase">
                                <th style="font-size: 0.75rem;">Profissional</th>
                                <th style="font-size: 0.75rem;">Área Pretendida</th>
                                <th style="font-size: 0.75rem;">Data de Cadastro</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($candidatosRecentes as $cand)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center fw-bold text-success" style="width: 32px; height: 32px; font-size: 0.8rem; flex-shrink: 0;">
                                                {{ strtoupper(substr($cand->nome_completo, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark small">{{ $cand->nome_completo }}</div>
                                                <small class="text-muted" style="font-size: 0.72rem;">{{ $cand->cidade }}/{{ $cand->estado }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="pl-chip" style="font-size: 0.75rem;">{{ $cand->cargo_pretendido ?: 'Geral' }}</span>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-dark">{{ $cand->created_at->format('d/m/Y') }}</div>
                                        <small class="text-muted" style="font-size: 0.72rem;">{{ $cand->created_at->format('H:i') }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted small">Nenhum candidato cadastrado recentemente.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<!-- Leaflet & Chart.js -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Inicialização do Mapa Leaflet
        const map = L.map('mapaCalor').setView([-23.420999, -51.933056], 12); // Padrão Maringá

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const pontosVagas = @json($pontosVagas);
        const pontosCandidatos = @json($pontosCandidatos);

        // Ícones customizados estilo Peerlist
        const vagaIcon = L.divIcon({
            html: '<div style="background-color: #0284c7; color: #fff; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.25);"><i class="fa-solid fa-briefcase" style="font-size: 12px;"></i></div>',
            className: 'custom-pin-vaga',
            iconSize: [28, 28],
            iconAnchor: [14, 14]
        });

        const candIcon = L.divIcon({
            html: '<div style="background-color: #00aa45; color: #fff; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.25);"><i class="fa-solid fa-user" style="font-size: 12px;"></i></div>',
            className: 'custom-pin-cand',
            iconSize: [28, 28],
            iconAnchor: [14, 14]
        });

        // Adiciona Marcadores de Vagas (Item 354)
        pontosVagas.forEach(p => {
            if (p.lat && p.lng) {
                L.marker([p.lat, p.lng], { icon: vagaIcon })
                    .bindPopup(`<strong>Vaga: ${p.titulo}</strong><br><small>Empresa: ${p.empresa}</small>`)
                    .addTo(map);
            }
        });

        // Adiciona Marcadores de Candidatos (Item 354)
        pontosCandidatos.forEach(c => {
            if (c.lat && c.lng) {
                L.marker([c.lat, c.lng], { icon: candIcon })
                    .bindPopup(`<strong>Candidato: ${c.nome}</strong><br><small>Área: ${c.cargo}</small>`)
                    .addTo(map);
            }
        });

        // 2. Gráfico de Setores (Chart.js com Estilo Peerlist e Altura Fixa)
        const ctx = document.getElementById('chartSetores').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($labelsSetor),
                datasets: [{
                    label: 'Vagas Ativas',
                    data: @json($dataSetor),
                    backgroundColor: '#00aa45',
                    hoverBackgroundColor: '#00923b',
                    borderRadius: 6,
                    maxBarThickness: 48
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#111827',
                        titleFont: { family: 'Plus Jakarta Sans', weight: 'bold', size: 12 },
                        bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { family: 'Plus Jakarta Sans', size: 11 },
                            color: '#64748b'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            stepSize: 1,
                            font: { family: 'Plus Jakarta Sans', size: 11 },
                            color: '#64748b'
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
