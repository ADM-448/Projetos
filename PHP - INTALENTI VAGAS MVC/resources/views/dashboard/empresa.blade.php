@extends('layouts.app')

@section('title', 'Painel da Empresa - ' . ($empresa->nome_empresa ?? 'Minha Empresa'))

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
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
    #mapaCandidatosEmpresa {
        height: 400px;
        width: 100%;
        border-radius: var(--pl-radius-md);
        z-index: 1;
    }
    @media (max-width: 576px) {
        #mapaCandidatosEmpresa {
            height: 290px;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-lg-4 py-4" style="max-width: 1440px;">
    
    <!-- Topo do Painel -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="pl-portal-badge">
                    <i class="fa-solid fa-building me-1"></i> Área Corporativa
                </span>
                <span class="badge bg-light text-muted border">Painel da Empresa</span>
            </div>
            <h2 class="h3 fw-bold mb-1">{{ $empresa->nome_empresa }}</h2>
            <p class="text-muted small mb-0">Gestão centralizada de oportunidades, triagem de talentos e processos seletivos.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap w-100 w-sm-auto mt-2 mt-sm-0">
            <a href="{{ route('empresa.equipe') }}" class="btn btn-pl-secondary d-flex align-items-center justify-content-center gap-2 flex-grow-1 flex-sm-grow-0">
                <i class="fa-solid fa-users"></i>
                <span>Gerenciar Equipe</span>
            </a>
            <a href="{{ route('vagas.create') }}" class="btn btn-pl-primary d-flex align-items-center justify-content-center gap-2 flex-grow-1 flex-sm-grow-0">
                <i class="fa-solid fa-plus-circle"></i>
                <span>Publicar Nova Vaga</span>
            </a>
        </div>
    </div>

    <!-- 4 KPIs no formato Peerlist -->
    <div class="row g-3 mb-4">
        <!-- Vagas Ativas -->
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon-circle" style="background: var(--pl-green-soft); color: var(--pl-green);">
                    <i class="fa-solid fa-bullhorn"></i>
                </div>
                <div class="h3 fw-bold mb-0 text-dark">{{ $kpis['vagas_ativas'] }}</div>
                <small class="text-muted fw-semibold">Vagas Ativas</small>
            </div>
        </div>

        <!-- Total de Candidatos -->
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon-circle" style="background: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="h3 fw-bold mb-0 text-dark">{{ $kpis['total_candidatos'] }}</div>
                <small class="text-muted fw-semibold">Total de Candidaturas</small>
            </div>
        </div>

        <!-- Novos / Pendentes -->
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon-circle" style="background: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="h3 fw-bold mb-0 text-dark">{{ $kpis['candidatos_pendentes'] }}</div>
                <small class="text-muted fw-semibold">Novos / Pendentes</small>
            </div>
        </div>

        <!-- Em Análise -->
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon-circle" style="background: #f3e8ff; color: #9333ea;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <div class="h3 fw-bold mb-0 text-dark">{{ $kpis['candidatos_analisando'] }}</div>
                <small class="text-muted fw-semibold">Em Análise</small>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Minhas Vagas Cadastradas -->
        <div class="col-12 col-lg-8">
            <div class="pl-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0" style="font-size: 1.05rem;">Minhas Vagas Cadastradas</h5>
                    <span class="badge bg-light text-muted border">{{ $minhasVagas->total() }} no total</span>
                </div>

                <!-- Tabela Desktop -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="small text-muted text-uppercase">
                                <th style="font-size: 0.75rem;">Título da Vaga</th>
                                <th style="font-size: 0.75rem;">Modalidade</th>
                                <th style="font-size: 0.75rem;">Candidatos</th>
                                <th style="font-size: 0.75rem;">Status</th>
                                <th style="font-size: 0.75rem;" class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($minhasVagas as $vaga)
                                <tr>
                                    <td>
                                        <a href="{{ route('vagas.show', $vaga->id) }}" class="fw-bold text-dark text-decoration-none d-block">
                                            {{ $vaga->titulo }}
                                        </a>
                                        <small class="text-muted">{{ $vaga->localizacao ?: 'Maringá, PR' }}</small>
                                    </td>
                                    <td>
                                        <span class="pl-chip" style="font-size: 0.75rem;">{{ $vaga->modalidade }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('vagas.candidatos', $vaga->id) }}" class="badge bg-success-subtle text-success border border-success-subtle text-decoration-none px-3 py-2 rounded-pill fw-semibold">
                                            <i class="fa-solid fa-users me-1"></i> {{ $vaga->candidaturas_count }} inscritos
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-success badge-status">{{ $vaga->status }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('vagas.candidatos', $vaga->id) }}" class="btn btn-sm btn-pl-secondary py-1 px-2" title="Ver Candidatos">
                                            <i class="fa-solid fa-users text-success"></i>
                                        </a>
                                        <a href="{{ route('vagas.edit', $vaga->id) }}" class="btn btn-sm btn-pl-secondary py-1 px-2" title="Editar Vaga">
                                            <i class="fa-solid fa-pen text-muted"></i>
                                        </a>
                                        <a href="{{ route('vagas.show', $vaga->id) }}" class="btn btn-sm btn-pl-secondary py-1 px-2" title="Ver no Portal" target="_blank">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-muted"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-briefcase fa-2x mb-2 opacity-50"></i>
                                        <div class="fw-semibold">Nenhuma vaga cadastrada por sua empresa ainda.</div>
                                        <a href="{{ route('vagas.create') }}" class="btn btn-pl-primary btn-sm mt-3">Criar Primeira Vaga</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Cards Responsivos Mobile (Elimina corte horizontal) -->
                <div class="d-md-none d-flex flex-column gap-2">
                    @forelse($minhasVagas as $vaga)
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div class="overflow-hidden">
                                    <a href="{{ route('vagas.show', $vaga->id) }}" class="fw-bold text-dark text-decoration-none d-block text-truncate">
                                        {{ $vaga->titulo }}
                                    </a>
                                    <small class="text-muted d-block">
                                        <i class="fa-solid fa-location-dot text-danger me-1"></i> {{ $vaga->localizacao ?: 'Maringá, PR' }}
                                    </small>
                                </div>
                                <span class="badge bg-success badge-status" style="font-size: 0.7rem;">{{ $vaga->status }}</span>
                            </div>

                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2 border-top">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="pl-chip" style="font-size: 0.72rem; padding: 2px 8px;">{{ $vaga->modalidade }}</span>
                                    <a href="{{ route('vagas.candidatos', $vaga->id) }}" class="badge bg-success-subtle text-success border border-success-subtle text-decoration-none px-2 py-1 rounded-pill fw-semibold" style="font-size: 0.72rem;">
                                        <i class="fa-solid fa-users me-1"></i> {{ $vaga->candidaturas_count }}
                                    </a>
                                </div>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('vagas.candidatos', $vaga->id) }}" class="btn btn-sm btn-pl-secondary py-1 px-2" title="Ver Candidatos">
                                        <i class="fa-solid fa-users text-success" style="font-size: 0.8rem;"></i>
                                    </a>
                                    <a href="{{ route('vagas.edit', $vaga->id) }}" class="btn btn-sm btn-pl-secondary py-1 px-2" title="Editar Vaga">
                                        <i class="fa-solid fa-pen text-muted" style="font-size: 0.8rem;"></i>
                                    </a>
                                    <a href="{{ route('vagas.show', $vaga->id) }}" class="btn btn-sm btn-pl-secondary py-1 px-2" title="Ver no Portal" target="_blank">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-muted" style="font-size: 0.8rem;"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            <i class="fa-solid fa-briefcase fa-2x mb-2 opacity-50"></i>
                            <div>Nenhuma vaga cadastrada ainda.</div>
                            <a href="{{ route('vagas.create') }}" class="btn btn-pl-primary btn-sm mt-2">Criar Vaga</a>
                        </div>
                    @endforelse
                </div>

                @if($minhasVagas->hasPages())
                    <div class="mt-3 d-flex justify-content-center">
                        {{ $minhasVagas->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Candidaturas Recentes -->
        <div class="col-12 col-lg-4">
            <div class="pl-card p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0" style="font-size: 1.05rem;">Últimas Candidaturas</h5>
                    <span class="badge bg-light text-muted border">Recentes</span>
                </div>

                <div class="d-flex flex-column gap-2">
                    @forelse($candidaturasRecentes as $cand)
                        <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light border">
                            <div class="d-flex align-items-center gap-2 overflow-hidden">
                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; flex-shrink: 0; font-size: 0.85rem;">
                                    {{ strtoupper(substr($cand->candidato->nome_completo, 0, 1)) }}
                                </div>
                                <div class="overflow-hidden">
                                    <div class="fw-bold text-truncate small text-dark" title="{{ $cand->candidato->nome_completo }}">
                                        {{ $cand->candidato->nome_completo }}
                                    </div>
                                    <small class="text-muted text-truncate d-block" style="font-size: 0.72rem;">{{ $cand->vaga->titulo }}</small>
                                </div>
                            </div>
                            <a href="{{ route('vagas.candidatos', $cand->vaga_id) }}" class="btn btn-sm btn-pl-secondary py-1 px-2" title="Ver Candidato">
                                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 0.75rem;"></i>
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            <i class="fa-regular fa-paper-plane fa-2x mb-2 opacity-50"></i>
                            <div>Nenhuma candidatura recente recebida.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Mapa de Distribuição Geográfica de Candidatos (Mapeamento por CEP) -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="pl-card p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="pl-portal-badge" style="background: var(--pl-green-soft); color: var(--pl-green);">
                                <i class="fa-solid fa-map-location-dot"></i>
                            </span>
                            <h5 class="fw-bold mb-0 text-dark">Mapa de Distribuição de Candidatos</h5>
                        </div>
                        <small class="text-muted">Localização espacial dos candidatos que se inscreveram nas vagas da sua empresa (mapeamento por CEP aproximado).</small>
                    </div>
                    <div>
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold">
                            <i class="fa-solid fa-users text-success me-1"></i> {{ count($pontosCandidatos) }} candidato(s) mapeado(s)
                        </span>
                    </div>
                </div>

                <div id="mapaCandidatosEmpresa"></div>

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 pt-2 border-top small text-muted">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="d-inline-block rounded-circle flex-shrink-0" style="width: 10px; height: 10px; background: #0284c7;"></span>
                            <span class="text-dark fw-medium" style="font-size: 0.8rem;">Sede da Empresa</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="d-inline-block rounded-circle flex-shrink-0" style="width: 10px; height: 10px; background: var(--pl-green);"></span>
                            <span class="text-dark fw-medium" style="font-size: 0.8rem;">Candidatos Inscritos</span>
                        </div>
                    </div>
                    <div class="text-muted small w-100 w-md-auto mt-1 mt-md-0 d-flex align-items-center gap-1" style="font-size: 0.74rem;">
                        <i class="fa-solid fa-shield-halved text-success"></i>
                        <span>Localização aproximada por CEP respeitando a privacidade LGPD</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const empresaLat = {{ $empresaCoords['lat'] ?? -23.4209 }};
        const empresaLng = {{ $empresaCoords['lng'] ?? -51.9331 }};
        const pontos = @json($pontosCandidatos);

        // Inicializa o mapa centralizado na sede da empresa
        const map = L.map('mapaCandidatosEmpresa').setView([empresaLat, empresaLng], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Ícone customizado para a sede da Empresa (Azul)
        const empresaIcon = L.divIcon({
            className: 'custom-empresa-pin',
            html: `<div style="background: #0284c7; color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 10px rgba(2, 132, 199, 0.4); border: 2.5px solid white;"><i class="fa-solid fa-building" style="font-size: 14px;"></i></div>`,
            iconSize: [36, 36],
            iconAnchor: [18, 18]
        });

        // Marcador da Empresa
        L.marker([empresaLat, empresaLng], { icon: empresaIcon })
            .addTo(map)
            .bindPopup(`
                <div style="font-family: inherit; padding: 4px;">
                    <div style="font-weight: 700; color: #0284c7; font-size: 0.95rem; margin-bottom: 2px;">{{ $empresa->nome_empresa }}</div>
                    <small style="color: #64748b;"><i class="fa-solid fa-location-dot me-1 text-danger"></i> Sede da sua Empresa ({{ $empresa->cidade ?: 'Maringá' }})</small>
                </div>
            `);

        // Ícone customizado para Candidatos (Verde Peerlist)
        const candidatoIcon = L.divIcon({
            className: 'custom-cand-pin',
            html: `<div style="background: var(--pl-green); color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 8px rgba(0, 170, 69, 0.35); border: 2px solid white;"><i class="fa-solid fa-user" style="font-size: 12px;"></i></div>`,
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        });

        const bounds = [[empresaLat, empresaLng]];

        pontos.forEach(c => {
            if (c.lat && c.lng) {
                bounds.push([c.lat, c.lng]);

                const popupHtml = `
                    <div style="font-family: inherit; min-width: 210px; padding: 4px;">
                        <div style="font-weight: 700; color: var(--pl-dark); font-size: 0.95rem; margin-bottom: 2px;">${c.nome}</div>
                        <div style="color: var(--pl-green); font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">${c.cargo}</div>
                        <div style="font-size: 0.78rem; color: #475569; margin-bottom: 4px;">
                            <i class="fa-solid fa-briefcase me-1 text-muted"></i> <strong>Vaga:</strong> ${c.vaga}
                        </div>
                        <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 8px;">
                            <i class="fa-solid fa-location-dot me-1 text-danger"></i> ${c.bairro ? c.bairro + ', ' : ''}${c.cidade} &bull; CEP ${c.cep_aprox}
                            ${c.distancia_km !== null ? `<br><strong style="color: #0f172a;">📍 Aprox. ${c.distancia_km} km da sede</strong>` : ''}
                        </div>
                        <a href="${c.perfilUrl}" class="btn btn-sm btn-pl-primary w-100 py-1" style="font-size: 0.78rem; text-decoration: none; border-radius: 20px; display: block; text-align: center;">
                            Ver Perfil Completo
                        </a>
                    </div>
                `;

                L.marker([c.lat, c.lng], { icon: candidatoIcon })
                    .addTo(map)
                    .bindPopup(popupHtml);
            }
        });

        if (bounds.length > 1) {
            map.fitBounds(bounds, { padding: [40, 40] });
        }
    });
</script>
@endpush
