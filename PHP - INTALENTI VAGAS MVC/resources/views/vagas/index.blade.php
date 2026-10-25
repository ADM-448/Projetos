@extends('layouts.app')

@section('title', 'Explorar Vagas - Peerlist Edition')

@push('styles')
<style>
    /* 3-Column Layout Utilities */
    .pl-layout-container {
        padding: 24px 0;
    }

    /* Left Sidebar */
    .pl-left-sidebar {
        position: sticky;
        top: 84px;
        height: calc(100vh - 104px);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    /* Center Stream */
    .pl-tabs-container {
        border-bottom: 1px solid var(--pl-border);
        margin-bottom: 20px;
    }

    .pl-tab-item {
        color: var(--pl-text-muted);
        font-weight: 600;
        font-size: 0.88rem;
        padding: 12px 18px;
        text-decoration: none;
        display: inline-block;
        position: relative;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        transition: color 0.15s ease;
    }

    .pl-tab-item:hover {
        color: var(--pl-text-main);
    }

    .pl-tab-item.active {
        color: var(--pl-green);
    }

    .pl-tab-item.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        right: 0;
        height: 2.5px;
        background-color: var(--pl-green);
        border-radius: 2px 2px 0 0;
    }

    /* Job Card Peerlist Style */
    .pl-job-card {
        background: #ffffff;
        border: 1px solid var(--pl-border);
        border-radius: var(--pl-radius-lg);
        padding: 20px 24px;
        margin-bottom: 14px;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        cursor: pointer;
    }

    .pl-job-card:hover {
        border-color: var(--pl-border-hover);
        box-shadow: var(--pl-shadow-hover);
        transform: translateY(-2px);
    }

    .pl-job-card:hover .pl-job-title {
        color: var(--pl-green);
    }

    .pl-company-logo {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid var(--pl-border);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        overflow: hidden;
        font-weight: 700;
        color: var(--pl-text-main);
        font-size: 1.15rem;
    }

    .pl-job-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--pl-text-main);
        text-decoration: none;
        display: inline;
        transition: color 0.15s ease;
    }

    .pl-job-title:hover {
        color: var(--pl-green);
    }

    .pl-job-meta {
        color: var(--pl-text-muted);
        font-size: 0.85rem;
        margin-top: 4px;
        margin-bottom: 12px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
    }

    .pl-bookmark-btn {
        background: transparent;
        border: none;
        color: var(--pl-text-light);
        font-size: 1.25rem;
        cursor: pointer;
        padding: 6px;
        border-radius: 8px;
        transition: all 0.15s ease;
    }

    .pl-bookmark-btn:hover {
        color: var(--pl-green);
        background-color: var(--pl-green-soft);
    }

    .pl-bookmark-btn.active {
        color: var(--pl-green);
    }

    /* Right Sidebar Filters */
    .pl-filter-card {
        background: #ffffff;
        border: 1px solid var(--pl-border);
        border-radius: var(--pl-radius-lg);
        padding: 20px;
        margin-bottom: 20px;
    }

    .pl-filter-label {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--pl-text-muted);
        margin-bottom: 6px;
    }

    .pl-filter-select, .pl-filter-input {
        background-color: var(--pl-chip-bg);
        border: 1px solid var(--pl-border);
        border-radius: var(--pl-radius-md);
        font-size: 0.88rem;
        padding: 8px 12px;
        width: 100%;
        color: var(--pl-text-main);
        transition: all 0.15s ease;
    }

    .pl-filter-select:focus, .pl-filter-input:focus {
        background-color: #ffffff;
        border-color: var(--pl-green);
        outline: none;
        box-shadow: 0 0 0 3px rgba(0, 170, 69, 0.15);
    }

    /* Profile Completion Widget */
    .pl-profile-card {
        background: #ffffff;
        border: 1px solid var(--pl-border);
        border-radius: var(--pl-radius-lg);
        padding: 20px;
        position: relative;
        overflow: hidden;
    }

    .pl-profile-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #f59e0b, #10b981);
    }

    /* Filter Dismissible Chip */
    .pl-tag-dismiss {
        background: var(--pl-chip-bg);
        border: 1px solid var(--pl-border);
        border-radius: var(--pl-radius-pill);
        padding: 3px 10px;
        font-size: 0.78rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--pl-text-main);
        text-decoration: none;
    }

    .pl-tag-dismiss:hover {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #ef4444;
    }

    /* Empty State Illustration */
    .pl-empty-state {
        background: #ffffff;
        border: 1px dashed var(--pl-border);
        border-radius: var(--pl-radius-lg);
        padding: 60px 24px;
        text-align: center;
    }

    .pl-empty-icon {
        width: 80px;
        height: 80px;
        background: var(--pl-chip-bg);
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--pl-text-muted);
        font-size: 2.2rem;
        margin-bottom: 16px;
    }

    /* Otimizações Responsivas Mobile */
    @media (max-width: 576px) {
        .pl-layout-container {
            padding: 10px 0 !important;
        }

        /* Abas no Mobile: Distribuição uniforme, sem rolagem cinza ou quebra */
        .pl-tabs-container {
            display: flex !important;
            width: 100% !important;
            overflow-x: auto !important;
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
            gap: 0 !important;
            margin-bottom: 14px !important;
        }
        .pl-tabs-container::-webkit-scrollbar {
            display: none !important;
        }
        .pl-tab-item {
            flex: 1 1 0 !important;
            text-align: center !important;
            padding: 10px 6px !important;
            font-size: 0.78rem !important;
            white-space: nowrap !important;
            letter-spacing: 0.02em !important;
        }
        .pl-tab-item .badge {
            font-size: 0.65rem !important;
            padding: 2px 5px !important;
            margin-left: 2px !important;
        }

        /* Card de Vaga no Mobile */
        .pl-job-card {
            padding: 14px 12px !important;
            margin-bottom: 10px !important;
            border-radius: var(--pl-radius-md) !important;
        }
        .pl-company-logo {
            width: 40px !important;
            height: 40px !important;
            font-size: 1rem !important;
            border-radius: 10px !important;
        }
        .pl-job-title {
            font-size: 0.98rem !important;
            line-height: 1.35 !important;
        }
        .pl-job-meta {
            font-size: 0.78rem !important;
            gap: 4px !important;
            margin-top: 3px !important;
            margin-bottom: 8px !important;
        }
        .pl-badge-responsive {
            font-size: 0.72rem !important;
            padding: 4px 8px !important;
            line-height: 1.3 !important;
            white-space: normal !important;
            text-align: left !important;
        }
        .pl-chip {
            font-size: 0.72rem !important;
            padding: 3px 8px !important;
        }
        .pl-sort-bar {
            padding: 8px 10px !important;
        }
        .pl-bookmark-btn {
            padding: 4px !important;
            font-size: 1.15rem !important;
        }
    }
</style>
@endpush

@section('content')
<div class="pl-layout-container">
    <div class="row g-4">
        <!-- ========================================== -->
        <!-- COLUNA 2: Fluxo Central de Vagas          -->
        <!-- ========================================== -->
        <div class="col-12 col-lg-8 col-xl-8">
            
            <!-- Header do Topo com Título e Ação de Preferências -->
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div>
                    <h2 class="h4 fw-bold mb-0">
                        @if(Auth::check() && Auth::user()->isEmpresa())
                            Minhas Vagas
                        @elseif(Auth::check() && Auth::user()->isPrefeitura())
                            Vagas no Município ({{ Auth::user()->getCidadePrefeitura() }})
                        @elseif(!empty($busca) || !empty($localizacao) || !empty($ramo) || !empty($tipoContrato) || !empty($modalidade))
                            Resultados da Busca
                        @elseif($tab === 'applied')
                            Minhas Candidaturas
                        @elseif($tab === 'saved')
                            Vagas Salvas
                        @else
                            Todas as Vagas
                        @endif
                    </h2>
                    <small class="text-muted">
                        @if(Auth::check() && Auth::user()->isEmpresa())
                            Painel exclusivo com as oportunidades da sua empresa
                        @elseif(Auth::check() && Auth::user()->isPrefeitura())
                            Vagas ativas no município de {{ Auth::user()->getCidadePrefeitura() }}
                        @else
                            {{ $vagas->total() }} {{ $vagas->total() == 1 ? 'oportunidade encontrada' : 'oportunidades encontradas' }}
                        @endif
                    </small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    @if(Auth::check() && Auth::user()->isEmpresa())
                        <a href="{{ route('vagas.create') }}" class="btn btn-pl-primary btn-sm d-flex align-items-center gap-2">
                            <i class="fa-solid fa-plus-circle"></i>
                            <span>Publicar Nova Vaga</span>
                        </a>
                    @endif
                    <button type="button" class="btn btn-pl-secondary btn-sm d-flex align-items-center gap-2" data-bs-toggle="collapse" data-bs-target="#mobileFilters">
                        <i class="fa-solid fa-sliders"></i>
                        <span>Filtros</span>
                    </button>
                </div>
            </div>

            @if(!Auth::check() || !Auth::user()->isEmpresa())
                <!-- Abas Estilo Peerlist: JOBS | SAVED | APPLIED (Apenas para Candidato / Visitante) -->
                <div class="pl-tabs-container d-flex align-items-center gap-1 overflow-auto">
                    <a href="{{ route('vagas.index', array_merge(request()->except(['page', 'tab']), ['tab' => 'jobs'])) }}" 
                       class="pl-tab-item {{ $tab === 'jobs' ? 'active' : '' }}">
                        Vagas
                    </a>
                    <a href="{{ route('vagas.index', array_merge(request()->except(['page', 'tab']), ['tab' => 'saved'])) }}" 
                       class="pl-tab-item {{ $tab === 'saved' ? 'active' : '' }}">
                        Salvas
                        @if(count($salvas) > 0)
                            <span class="badge bg-light text-dark rounded-pill ms-1">{{ count($salvas) }}</span>
                        @endif
                    </a>
                    <a href="{{ route('vagas.index', array_merge(request()->except(['page', 'tab']), ['tab' => 'applied'])) }}" 
                       class="pl-tab-item {{ $tab === 'applied' ? 'active' : '' }}">
                        Candidaturas
                        @if(count($candidaturasUsuario) > 0)
                            <span class="badge bg-light text-dark rounded-pill ms-1">{{ count($candidaturasUsuario) }}</span>
                        @endif
                    </a>
                </div>

                <!-- Barra de Ordenação: Recentes vs Próximas de Mim por KM -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 py-2 px-3 bg-white border rounded-3 shadow-sm">
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted fw-semibold me-1"><i class="fa-solid fa-arrow-down-wide-short me-1"></i> Ordenar:</span>
                        <a href="{{ route('vagas.index', array_merge(request()->except(['page', 'ordem']), ['ordem' => 'recentes'])) }}" 
                           class="btn btn-sm {{ $ordem !== 'proximos' ? 'btn-pl-primary' : 'btn-light text-muted' }} rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.78rem;">
                            Mais Recentes
                        </a>
                        <a href="{{ route('vagas.index', array_merge(request()->except(['page', 'ordem']), ['ordem' => 'proximos'])) }}" 
                           class="btn btn-sm {{ $ordem === 'proximos' ? 'btn-pl-primary' : 'btn-light text-muted' }} rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.78rem;">
                            <i class="fa-solid fa-location-crosshairs me-1 text-danger"></i> Próximas de Mim (KM)
                        </a>
                    </div>

                    @if($ordem === 'proximos')
                        <div class="d-flex align-items-center gap-2">
                            <span class="small text-muted" style="font-size: 0.75rem;">Raio máx:</span>
                            <div class="btn-group btn-group-sm" role="group">
                                @foreach([5 => '5 km', 10 => '10 km', 25 => '25 km', 50 => '50 km'] as $r => $label)
                                    <a href="{{ route('vagas.index', array_merge(request()->except(['page', 'raio_km']), ['raio_km' => $raioKm == $r ? null : $r])) }}" 
                                       class="btn {{ $raioKm == $r ? 'btn-success text-white' : 'btn-outline-secondary' }}" style="font-size: 0.75rem;">
                                        {{ $label }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                @if(!empty($candidatoSemEndereco) && $ordem === 'proximos')
                    <div class="alert alert-light border border-warning-subtle d-flex flex-wrap align-items-center justify-content-between gap-2 p-3 rounded-3 mb-3" style="background: #fffbeb;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation text-warning fa-lg"></i>
                            <div class="small text-dark">
                                <strong>Cadastre seu CEP:</strong> Para calcularmos a distância exata em KM até cada vaga, complete seu endereço no currículo.
                            </div>
                        </div>
                        <a href="{{ route('curriculo.edit') }}" class="btn btn-sm btn-pl-primary py-1 px-3 text-nowrap" style="font-size: 0.78rem;">
                            Cadastrar Endereço
                        </a>
                    </div>
                @endif
            @endif

            <!-- Chips de Filtros Ativos (Removíveis) -->
            @php
                $hasActiveFilters = !empty($busca) || !empty($localizacao) || !empty($ramo) || !empty($tipoContrato) || !empty($modalidade) || $remoteOnly;
            @endphp

            @if($hasActiveFilters)
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    @if(!empty($busca))
                        <a href="{{ route('vagas.index', request()->except('busca')) }}" class="pl-tag-dismiss">
                            <span>"{{ $busca }}"</span>
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif

                    @if(!empty($localizacao))
                        <a href="{{ route('vagas.index', request()->except('localizacao')) }}" class="pl-tag-dismiss">
                            <i class="fa-solid fa-location-dot text-danger"></i>
                            <span>{{ $localizacao }}</span>
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif

                    @if(!empty($ramo))
                        <a href="{{ route('vagas.index', request()->except('ramo')) }}" class="pl-tag-dismiss">
                            <span>{{ $ramo }}</span>
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif

                    @if(!empty($tipoContrato))
                        <a href="{{ route('vagas.index', request()->except('tipo_contrato')) }}" class="pl-tag-dismiss">
                            <span>{{ $tipoContrato }}</span>
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif

                    @if($remoteOnly)
                        <a href="{{ route('vagas.index', request()->except('remote_only')) }}" class="pl-tag-dismiss">
                            <span>Remoto Apenas</span>
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @elseif(!empty($modalidade))
                        <a href="{{ route('vagas.index', request()->except('modalidade')) }}" class="pl-tag-dismiss">
                            <span>{{ $modalidade }}</span>
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif

                    @if(!empty($experiencia))
                        <a href="{{ route('vagas.index', request()->except('experiencia')) }}" class="pl-tag-dismiss">
                            <span>{{ $experiencia }}</span>
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif

                    <a href="{{ route('vagas.index', ['tab' => $tab]) }}" class="small text-danger text-decoration-none fw-bold ms-2">
                        Limpar Todos
                    </a>
                </div>
            @endif

            <!-- Filtros Mobile (Collapse) -->
            <div class="collapse d-lg-none mb-3" id="mobileFilters">
                <div class="pl-filter-card">
                    <form method="GET" action="{{ route('vagas.index') }}">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        
                        <div class="mb-3">
                            <label class="pl-filter-label">Buscar Cargo ou Palavra-chave</label>
                            <input type="text" name="busca" value="{{ $busca }}" class="pl-filter-input" placeholder="Ex: Desenvolvedor, RH...">
                        </div>

                        <div class="mb-3">
                            <label class="pl-filter-label">Tipo de Contrato</label>
                            <select name="tipo_contrato" class="pl-filter-select">
                                <option value="">Todos os tipos</option>
                                @foreach($tiposContratoDisponiveis as $t)
                                    <option value="{{ $t }}" {{ $tipoContrato == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="pl-filter-label">Localização</label>
                            <select name="localizacao" class="pl-filter-select">
                                <option value="">Todas as Cidades</option>
                                @foreach($locaisDisponiveis as $loc)
                                    <option value="{{ $loc }}" {{ $localizacao == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="remote_only" value="1" id="mRemoteOnly" {{ $remoteOnly ? 'checked' : '' }}>
                            <label class="form-check-label small fw-semibold" for="mRemoteOnly">Apenas Remoto</label>
                        </div>

                        <button type="submit" class="btn btn-pl-primary w-100 btn-sm">Aplicar Filtros</button>
                    </form>
                </div>
            </div>

            <!-- Lista de Vagas no Estilo Peerlist -->
            <div class="d-flex flex-column gap-2">
                @forelse($vagas as $vaga)
                    <div class="pl-job-card" onclick="if (!event.target.closest('a, button, form, input, select')) { window.location.href = '{{ route('vagas.show', $vaga->id) }}'; }">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            
                            <div class="d-flex align-items-start gap-3 flex-grow-1 overflow-hidden">
                                <!-- Logo da Empresa -->
                                <div class="pl-company-logo">
                                    @if($vaga->empresa && $vaga->empresa->logo_path)
                                        <img src="{{ asset('storage/' . $vaga->empresa->logo_path) }}" alt="Logo" class="img-fluid" style="max-height: 100%; object-fit: contain;">
                                    @else
                                        <span>{{ strtoupper(substr($vaga->confidencial ? 'C' : ($vaga->empresa->nome_empresa ?? 'E'), 0, 1)) }}</span>
                                    @endif
                                </div>

                                <!-- Detalhes da Vaga -->
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex align-items-center flex-wrap gap-2">
                                        <a href="{{ route('vagas.show', $vaga->id) }}" class="pl-job-title">
                                            {{ $vaga->titulo }}
                                        </a>
                                        <span class="text-muted fw-normal d-inline-flex align-items-center gap-1" style="font-size: 0.92rem;">
                                            <i class="fa-solid fa-building text-muted opacity-75" style="font-size: 0.82rem;"></i>
                                            <strong class="text-dark">{{ $vaga->confidencial ? 'Empresa Confidencial' : ($vaga->empresa->nome_empresa ?? 'Empresa') }}</strong>
                                        </span>
                                    </div>

                                    <!-- Linha de Metadados -->
                                    <div class="pl-job-meta">
                                        <span>
                                            <i class="fa-solid fa-location-dot text-danger me-1"></i>
                                            {{ $vaga->modalidade == 'Remoto' ? 'Remoto' : ($vaga->localizacao ?: 'Maringá, PR') }}
                                        </span>
                                        <span>&bull;</span>
                                        <span>{{ $vaga->tipo_contrato ?: 'CLT' }}</span>
                                        @if($vaga->salario_formatado)
                                            <span>&bull;</span>
                                            <span class="text-success fw-semibold">{{ $vaga->salario_formatado }}</span>
                                        @endif
                                        @if($vaga->experiencia)
                                            <span>&bull;</span>
                                            <span>{{ $vaga->experiencia }}</span>
                                        @endif
                                        <span>&bull;</span>
                                        <span class="text-muted">{{ $vaga->created_at->diffForHumans() }}</span>
                                    </div>

                                    <!-- Distância em KM e Status de Candidatura -->
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                        @if(isset($vaga->distancia_km) && $vaga->distancia_km !== null)
                                            <span class="badge bg-light text-dark border px-2 py-1 rounded-pill small fw-semibold pl-badge-responsive" title="Distância estimada com base no seu CEP">
                                                <i class="fa-solid fa-location-crosshairs text-success me-1"></i> Aprox. {{ number_format($vaga->distancia_km, 1, ',', '.') }} km de você
                                            </span>
                                        @endif

                                        @if(isset($candidaturasUsuario[$vaga->id]))
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small fw-semibold pl-badge-responsive">
                                                <i class="fa-solid fa-circle-check me-1"></i> Candidatou-se em {{ \Carbon\Carbon::parse($candidaturasUsuario[$vaga->id])->format('d/m/Y') }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Tags de Skills / Competências -->
                                    <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                                        @foreach($vaga->tags as $tag)
                                            <span class="pl-chip">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            @if(Auth::check() && Auth::user()->isEmpresa())
                                <div class="d-flex flex-column align-items-end gap-2 flex-shrink-0">
                                    <a href="{{ route('vagas.candidatos', $vaga->id) }}" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-bold" style="font-size: 0.8rem;">
                                        <i class="fa-solid fa-users me-1"></i>
                                        <span>{{ $vaga->candidaturas_count ?? 0 }} {{ ($vaga->candidaturas_count ?? 0) == 1 ? 'candidato' : 'candidatos' }}</span>
                                    </a>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('vagas.edit', $vaga->id) }}" class="btn btn-sm btn-light border rounded-circle text-muted" title="Editar vaga" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="fa-solid fa-pencil" style="font-size: 0.75rem;"></i>
                                        </a>
                                        <a href="{{ route('vagas.show', $vaga->id) }}" class="btn btn-sm btn-light border rounded-circle text-muted" title="Visualizar vaga" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.75rem;"></i>
                                        </a>
                                    </div>
                                </div>
                            @else
                                <!-- Botão de Salvar / Favoritar Vaga -->
                                <form action="{{ route('vagas.salvar', $vaga->id) }}" method="POST" class="form-salvar-vaga">
                                    @csrf
                                    <button type="submit" 
                                            class="pl-bookmark-btn {{ in_array($vaga->id, $salvas) ? 'active' : '' }}" 
                                            title="{{ in_array($vaga->id, $salvas) ? 'Remover das salvas' : 'Salvar vaga' }}">
                                        <i class="{{ in_array($vaga->id, $salvas) ? 'fa-solid' : 'fa-regular' }} fa-bookmark"></i>
                                    </button>
                                </form>
                            @endif

                        </div>
                    </div>
                @empty
                    <!-- Empty State (Refero Design Step 2) -->
                    <div class="pl-empty-state">
                        <div class="pl-empty-icon">
                            <i class="fa-solid fa-briefcase"></i>
                        </div>
                        <h4 class="fw-bold mb-2">Não encontramos vagas com seus critérios.</h4>
                        <p class="text-muted mb-4 small" style="max-width: 420px; margin: 0 auto;">
                            Tente buscar por termos mais genéricos, limpe alguns filtros ou explore as oportunidades recentes.
                        </p>
                        <a href="{{ route('vagas.index', ['tab' => 'jobs']) }}" class="btn btn-pl-primary btn-sm">
                            Limpar Filtros e Ver Todas
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- Paginação Moderna -->
            <div class="mt-4 d-flex justify-content-center">
                {{ $vagas->links() }}
            </div>

        </div>

        <!-- ========================================== -->
        <!-- COLUNA 3: Filtros e Widget de Perfil       -->
        <!-- ========================================== -->
        <aside class="col-12 col-lg-4 col-xl-4 d-none d-lg-block">
            
            <!-- Card de Filtros Laterais -->
            <div class="pl-filter-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0" style="font-size: 1rem;">Filtros</h5>
                    @if($hasActiveFilters)
                        <a href="{{ route('vagas.index', ['tab' => $tab]) }}" class="btn btn-pl-secondary btn-sm py-1 px-2" style="font-size: 0.75rem;">
                            Limpar
                        </a>
                    @endif
                </div>

                <form method="GET" action="{{ route('vagas.index') }}" id="sidebarFilterForm">
                    <input type="hidden" name="tab" value="{{ $tab }}">

                    <!-- Filtro: Cargo / Palavra-Chave -->
                    <div class="mb-3">
                        <label class="pl-filter-label">Função / Cargo</label>
                        <input type="text" name="busca" value="{{ $busca }}" class="pl-filter-input" placeholder="Ex: Product Designer, PHP...">
                    </div>

                    <!-- Filtro: Tipo de Vaga / Contrato -->
                    <div class="mb-3">
                        <label class="pl-filter-label">Tipo de Contrato</label>
                        <select name="tipo_contrato" class="pl-filter-select">
                            <option value="">Todos os tipos</option>
                            @foreach($tiposContratoDisponiveis as $t)
                                <option value="{{ $t }}" {{ $tipoContrato == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro: Ramo de Atividade -->
                    <div class="mb-3">
                        <label class="pl-filter-label">Área / Setor</label>
                        <select name="ramo" class="pl-filter-select">
                            <option value="">Todas as Áreas</option>
                            @foreach($ramosDeAtividade as $r)
                                <option value="{{ $r }}" {{ $ramo == $r ? 'selected' : '' }}>{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro: Experiência / Nível -->
                    <div class="mb-3">
                        <label class="pl-filter-label">Experiência</label>
                        <select name="experiencia" class="pl-filter-select">
                            <option value="">Qualquer nível</option>
                            @foreach($experienciasDisponiveis as $exp)
                                <option value="{{ $exp }}" {{ $experiencia == $exp ? 'selected' : '' }}>{{ $exp }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro: Localização -->
                    <div class="mb-3">
                        <label class="pl-filter-label">Localização (Cidade)</label>
                        <select name="localizacao" class="pl-filter-select">
                            <option value="">Todas as cidades</option>
                            @foreach($locaisDisponiveis as $loc)
                                <option value="{{ $loc }}" {{ $localizacao == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Switch: Apenas Remoto -->
                    <div class="form-check form-switch mb-3 pt-1">
                        <input class="form-check-input" type="checkbox" name="remote_only" value="1" id="remoteOnlySwitch" {{ $remoteOnly ? 'checked' : '' }} onchange="document.getElementById('sidebarFilterForm').submit();">
                        <label class="form-check-label small fw-semibold" for="remoteOnlySwitch">Apenas Vagas Remotas</label>
                    </div>

                    <button type="submit" class="btn btn-pl-primary w-100 btn-sm fw-bold">
                        Aplicar Filtros
                    </button>
                </form>
            </div>

            <!-- Card de Recomendações de Perfil (Peerlist Widget Step 1 & 2) -->
            @auth
                @if(Auth::user()->isCandidato())
                    <div class="pl-profile-card">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fa-solid fa-bolt text-warning fs-5"></i>
                            <span class="small fw-bold text-dark">
                                {{ count($profileWidget['recommendations']) > 0 ? 'Temos ' . count($profileWidget['recommendations']) . ' recomendações para você' : 'Perfil completo!' }}
                            </span>
                        </div>

                        <!-- Barra de Progresso do Perfil -->
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $profileWidget['score'] }}%;"></div>
                        </div>

                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-bold text-dark small" style="font-size: 0.8rem;">FORÇA DO PERFIL</span>
                                <span class="badge bg-success-subtle text-success fw-bold">{{ $profileWidget['score'] }}%</span>
                            </div>
                            <p class="text-muted small mb-0" style="font-size: 0.75rem;">
                                Complete seu currículo para se destacar nos processos seletivos.
                            </p>
                        </div>

                        @if(!empty($profileWidget['recommendations']))
                            <div class="d-flex flex-column gap-2 mb-3">
                                @foreach(array_slice($profileWidget['recommendations'], 0, 2) as $rec)
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="fa-regular fa-circle-check text-muted mt-1" style="font-size: 0.8rem;"></i>
                                        <div>
                                            <div class="fw-semibold text-dark" style="font-size: 0.8rem;">{{ $rec['title'] }}</div>
                                            <div class="text-muted" style="font-size: 0.72rem;">{{ $rec['desc'] }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <a href="{{ route('curriculo.edit') }}" class="btn btn-pl-secondary btn-sm w-100 fw-bold d-flex align-items-center justify-content-center gap-2" style="font-size: 0.8rem;">
                            <span>ATUALIZAR PERFIL</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                @endif
            @else
                <!-- Banner para Visitante -->
                <div class="pl-profile-card">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fa-solid fa-sparkles text-success fs-5"></i>
                        <span class="small fw-bold text-dark">Cadastre seu Perfil</span>
                    </div>
                    <p class="text-muted small mb-3">
                        Crie seu perfil profissional no estilo Peerlist, candidate-se às melhores vagas e receba convites de recrutadores.
                    </p>
                    <a href="{{ route('register') }}" class="btn btn-pl-primary btn-sm w-100 fw-bold">
                        Criar Conta Grátis
                    </a>
                </div>
            @endauth

        </aside>

    </div>
</div>
@endsection

@push('scripts')
<script>
    // AJAX Bookmark / Save Vaga
    document.querySelectorAll('.form-salvar-vaga').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = this.querySelector('button');
            const icon = btn.querySelector('i');
            
            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.saved) {
                        btn.classList.add('active');
                        icon.classList.remove('fa-regular');
                        icon.classList.add('fa-solid');
                        btn.title = 'Remover das salvas';
                    } else {
                        btn.classList.remove('active');
                        icon.classList.remove('fa-solid');
                        icon.classList.add('fa-regular');
                        btn.title = 'Salvar vaga';
                    }
                }
            } catch (err) {
                console.error('Erro ao salvar vaga:', err);
            }
        });
    });
</script>
@endpush