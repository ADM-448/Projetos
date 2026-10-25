@extends('layouts.app')

@section('title', 'Gestão de Portais e Empresas - Painel do Administrador')

@push('styles')
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
    .pl-nav-tabs .nav-link {
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--pl-text-muted);
        border: none;
        padding: 8px 16px;
        border-radius: 20px;
        transition: all 0.15s ease;
    }
    .pl-nav-tabs .nav-link:hover {
        background: #f1f5f9;
        color: var(--pl-text-main);
    }
    .pl-nav-tabs .nav-link.active {
        background: var(--pl-dark);
        color: #ffffff !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-lg-5 py-4" style="max-width: 1440px;">
    
    <!-- Topo do Painel -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="pl-portal-badge" style="background: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-shield-halved me-1"></i> Controle Master
                </span>
                <span class="badge bg-light text-muted border">Super Admin</span>
            </div>
            <h2 class="h3 fw-bold mb-1">Gestão de Portais & Empresas</h2>
            <p class="text-muted small mb-0">Validação de prefeituras, liberação de acessos governamentais e roteamento multi-tenancy por domínio.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-sm">
                <i class="fa-solid fa-server text-success me-1"></i> Multi-tenancy Ativo
            </span>
        </div>
    </div>

    <!-- Mensagens de Flash -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 rounded-3 border-0 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check text-success fa-lg"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 rounded-3 border-0 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-exclamation text-danger fa-lg"></i>
            <div>
                @foreach($errors->all() as $err)
                    <div>{{ $err }}</div>
                @endforeach
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 4 KPIs do Super Admin -->
    <div class="row g-3 mb-4">
        <!-- Total de Organizações -->
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon-circle" style="background: #f1f5f9; color: #334155;">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <div class="h3 fw-bold mb-0 text-dark">{{ $kpis['total_organizacoes'] }}</div>
                <small class="text-muted fw-semibold">Total de Organizações</small>
            </div>
        </div>

        <!-- Prefeituras Ativas -->
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon-circle" style="background: var(--pl-green-soft); color: var(--pl-green);">
                    <i class="fa-solid fa-landmark"></i>
                </div>
                <div class="h3 fw-bold mb-0 text-dark">{{ $kpis['prefeituras_ativas'] }}</div>
                <small class="text-muted fw-semibold">Prefeituras Ativas</small>
            </div>
        </div>

        <!-- Prefeituras Pendentes de Liberação -->
        <div class="col-6 col-md-3">
            <div class="kpi-card border-warning-subtle" style="background: {{ $kpis['prefeituras_pendentes'] > 0 ? '#fffbeb' : '#ffffff' }};">
                <div class="kpi-icon-circle" style="background: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <div class="h3 fw-bold mb-0 text-dark">{{ $kpis['prefeituras_pendentes'] }}</div>
                <small class="text-muted fw-semibold">Prefeituras Pendentes</small>
            </div>
        </div>

        <!-- Empresas Ativas -->
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon-circle" style="background: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-building"></i>
                </div>
                <div class="h3 fw-bold mb-0 text-dark">{{ $kpis['empresas_ativas'] }}</div>
                <small class="text-muted fw-semibold">Empresas Privadas</small>
            </div>
        </div>
    </div>

    <!-- Tabela e Filtros de Gestão -->
    <div class="pl-card p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <!-- Abas de Filtro -->
            <ul class="nav pl-nav-tabs d-flex align-items-center gap-1">
                <li class="nav-item">
                    <a class="nav-link {{ empty($tipo) ? 'active' : '' }}" href="{{ route('admin.empresas') }}">
                        Todas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tipo === 'prefeitura' ? 'active' : '' }}" href="{{ route('admin.empresas', ['tipo' => 'prefeitura']) }}">
                        <i class="fa-solid fa-landmark me-1"></i> Prefeituras
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tipo === 'empresa' ? 'active' : '' }}" href="{{ route('admin.empresas', ['tipo' => 'empresa']) }}">
                        <i class="fa-solid fa-building me-1"></i> Empresas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tipo === 'pendentes' ? 'active' : '' }}" href="{{ route('admin.empresas', ['tipo' => 'pendentes']) }}">
                        <i class="fa-solid fa-clock me-1 text-warning"></i> Pendentes de Liberação
                        @if($kpis['prefeituras_pendentes'] > 0)
                            <span class="badge bg-warning text-dark rounded-pill ms-1">{{ $kpis['prefeituras_pendentes'] }}</span>
                        @endif
                    </a>
                </li>
            </ul>

            <!-- Busca rápida -->
            <form action="{{ route('admin.empresas') }}" method="GET" class="d-flex align-items-center gap-2">
                @if(!empty($tipo))
                    <input type="hidden" name="tipo" value="{{ $tipo }}">
                @endif
                <div class="position-relative" style="min-width: 250px;">
                    <input type="text" name="busca" value="{{ $busca }}" class="form-control form-control-sm ps-4 rounded-pill" placeholder="Buscar por nome, cidade ou CNPJ...">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 translate-middle-y text-muted" style="left: 12px; font-size: 0.8rem;"></i>
                </div>
                <button type="submit" class="btn btn-sm btn-pl-primary rounded-pill px-3">Filtrar</button>
                @if(!empty($busca))
                    <a href="{{ route('admin.empresas', ['tipo' => $tipo]) }}" class="btn btn-sm btn-light rounded-circle" title="Limpar busca">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="small text-muted text-uppercase">
                        <th style="font-size: 0.75rem;">Organização / Município</th>
                        <th style="font-size: 0.75rem;">Tipo</th>
                        <th style="font-size: 0.75rem;">Domínio do Portal</th>
                        <th style="font-size: 0.75rem;">Gestor / Responsável</th>
                        <th style="font-size: 0.75rem;">Vagas</th>
                        <th style="font-size: 0.75rem;">Status</th>
                        <th style="font-size: 0.75rem;" class="text-end">Ações de Controle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lista as $item)
                        @php
                            $gestor = $item->usuarios->first();
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->nome_empresa }}</div>
                                <div class="small text-muted">
                                    <i class="fa-solid fa-location-dot me-1 text-danger"></i>
                                    {{ $item->cidade ? $item->cidade . ' - ' . $item->estado : 'Não informada' }}
                                    &bull; CNPJ: {{ $item->cnpj ?: 'Não informado' }}
                                </div>
                            </td>
                            <td>
                                @if($item->tipo === 'prefeitura')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill fw-semibold">
                                        <i class="fa-solid fa-landmark me-1"></i> Prefeitura
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 rounded-pill fw-semibold">
                                        <i class="fa-solid fa-building me-1"></i> Empresa
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if(!empty($item->dominio))
                                    <span class="badge bg-dark-subtle text-dark border px-2 py-1 rounded-pill font-monospace" style="font-size: 0.75rem;">
                                        <i class="fa-solid fa-globe me-1 text-primary"></i> {{ $item->dominio }}
                                    </span>
                                @else
                                    <span class="text-muted small fst-italic">Padrão da plataforma</span>
                                @endif
                                <button type="button" class="btn btn-link btn-sm p-0 ms-1 text-decoration-none text-muted" title="Configurar Domínio" data-bs-toggle="modal" data-bs-target="#modalDominio{{ $item->id }}">
                                    <i class="fa-solid fa-pen-to-square" style="font-size: 0.78rem;"></i>
                                </button>
                            </td>
                            <td>
                                @if($gestor)
                                    <div class="fw-semibold text-dark small">{{ $gestor->name }}</div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">{{ $gestor->email }}</small>
                                @else
                                    <span class="text-muted small fst-italic">Sem gestor vinculado</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1 rounded-pill small fw-semibold">
                                    {{ $item->vagas_count }} vagas
                                </span>
                            </td>
                            <td>
                                @if($item->status)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-semibold">
                                        <i class="fa-solid fa-circle-check me-1"></i> Ativo / Liberado
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 rounded-pill fw-semibold">
                                        <i class="fa-solid fa-clock me-1"></i> Pendente de Liberação
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <!-- Botão Toggle de Aprovação / Bloqueio -->
                                    <form action="{{ route('admin.toggleStatus', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @if($item->status)
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2 rounded-pill" title="Bloquear / Desativar acesso" onclick="return confirm('Deseja realmente inativar o acesso de {{ $item->nome_empresa }}?')">
                                                <i class="fa-solid fa-ban me-1"></i> Bloquear
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-sm btn-success py-1 px-3 rounded-pill fw-semibold text-white shadow-sm" title="Aprovar e Liberar Acesso">
                                                <i class="fa-solid fa-check-circle me-1"></i> Aprovar Acesso
                                            </button>
                                        @endif
                                    </form>

                                    <!-- Botão Dropdown Mais Opções -->
                                    <div class="dropdown d-inline">
                                        <button class="btn btn-sm btn-pl-secondary rounded-circle py-1 px-2" type="button" data-bs-toggle="dropdown">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border p-1" style="font-size: 0.82rem;">
                                            <li>
                                                <button type="button" class="dropdown-item py-2 rounded" data-bs-toggle="modal" data-bs-target="#modalDominio{{ $item->id }}">
                                                    <i class="fa-solid fa-globe me-2 text-primary"></i> Configurar Domínio
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <form action="{{ route('admin.destroy', $item->id) }}" method="POST" onsubmit="return confirm('ATENÇÃO: Deseja realmente excluir este cadastro? Isso removerá a organização do portal.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item py-2 text-danger rounded">
                                                        <i class="fa-solid fa-trash me-2"></i> Excluir Cadastro
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Modal de Configuração de Domínio -->
                                <div class="modal fade" id="modalDominio{{ $item->id }}" tabindex="-1" aria-hidden="true" style="text-align: left;">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-4 border-0 shadow">
                                            <form action="{{ route('admin.salvarDominio', $item->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header border-bottom p-3">
                                                    <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                        <i class="fa-solid fa-globe text-primary"></i>
                                                        <span>Configurar Domínio do Portal</span>
                                                    </h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold text-muted text-uppercase">Organização</label>
                                                        <div class="fw-bold text-dark">{{ $item->nome_empresa }}</div>
                                                        <small class="text-muted">{{ $item->cidade ? $item->cidade . ' - ' . $item->estado : 'Portal Municipal' }}</small>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="inputDominio{{ $item->id }}" class="form-label small fw-bold text-dark">Domínio Personalizado (Multi-tenancy)</label>
                                                        <input type="text" id="inputDominio{{ $item->id }}" name="dominio" value="{{ $item->dominio }}" class="form-control" placeholder="ex: vagas.cidade.pr.gov.br ou localhost">
                                                        <div class="form-text small text-muted">
                                                            Quando o usuário acessar através deste hostname no navegador, o sistema isolará automaticamente as vagas e empresas deste município.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top p-3 d-flex justify-content-between">
                                                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-sm btn-pl-primary px-3">Salvar Domínio</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-folder-open fa-2x mb-2 opacity-50"></i>
                                <div class="fw-semibold">Nenhuma organização encontrada para este filtro.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($lista->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                {{ $lista->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
