@extends('layouts.app')

@section('title', 'Minhas Candidaturas - IntalenTI Vagas')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .candidaturas-shell {
        max-width: 1100px;
        margin: 0 auto;
    }
    .pl-section-card {
        background: #ffffff;
        border: 1px solid var(--pl-border);
        border-radius: var(--pl-radius-lg);
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .status-badge-pendente {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }
    .status-badge-analisando {
        background-color: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }
    .status-badge-aprovado {
        background-color: var(--pl-green-soft);
        color: var(--pl-green);
        border: 1px solid #bbf7d0;
    }
    .status-badge-reprovado {
        background-color: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }
    .action-group {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-lg-4 py-4 candidaturas-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="pl-portal-badge">
                    <i class="fa-solid fa-id-card me-1"></i> Área do Candidato
                </span>
                <span class="badge bg-light text-muted border">Processos</span>
            </div>
            <h2 class="h3 fw-bold mb-1" style="color: var(--pl-dark); letter-spacing: -0.02em;">Minhas Candidaturas</h2>
            <p class="text-muted small mb-0">Acompanhe em tempo real o status dos seus processos seletivos e interaja com os recrutadores.</p>
        </div>
        <a href="{{ route('vagas.index') }}" class="btn btn-pl-primary btn-sm">
            <i class="fa-solid fa-magnifying-glass me-1"></i> Explorar Mais Vagas
        </a>
    </div>

    <div class="pl-section-card">
        @if($candidaturas->count() > 0)
            <!-- Visualização em Tabela (Desktop) -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                    <thead class="table-light">
                        <tr style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b;">
                            <th class="ps-3" style="width: 42%;">Vaga & Empresa</th>
                            <th>Data de Inscrição</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-3">Ações & Mensagens</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($candidaturas as $c)
                            @php
                                $statusClass = match(strtolower($c->status)) {
                                    'analisando' => 'status-badge-analisando',
                                    'aprovado' => 'status-badge-aprovado',
                                    'reprovado' => 'status-badge-reprovado',
                                    default => 'status-badge-pendente',
                                };
                            @endphp
                            <tr>
                                <td class="ps-3 py-3">
                                    <h6 class="fw-bold mb-1">
                                        <a href="{{ route('vagas.show', $c->vaga->id) }}" style="color: var(--pl-dark); text-decoration: none;">
                                            {{ $c->vaga->titulo }}
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="fa-solid fa-building me-1"></i> {{ $c->vaga->confidencial ? 'Empresa Confidencial' : ($c->vaga->empresa->nome_empresa ?? 'Empresa') }} &bull;
                                        <i class="fa-solid fa-location-dot me-1" style="color: var(--pl-green);"></i> {{ $c->vaga->localizacao }}
                                    </small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark small">{{ $c->data_candidatura->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $c->data_candidatura->format('H:i') }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $statusClass }} px-3 py-1 text-uppercase fw-bold rounded-pill small">
                                        {{ $c->status }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="action-group">
                                        @if($c->chat)
                                            <a href="{{ route('chat.show', $c->chat->id) }}" class="btn btn-sm btn-pl-primary py-1 px-3" title="Abrir Mensagens">
                                                <i class="fa-solid fa-comments me-1"></i> Chat
                                            </a>
                                        @endif

                                        <form method="POST" action="{{ route('candidaturas.cancelar', $c->id) }}" class="d-inline form-cancelar-candidatura">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-cancelar py-1" data-vaga="{{ $c->vaga->titulo }}" title="Cancelar Candidatura">
                                                <i class="fa-solid fa-ban me-1"></i> Cancelar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Visualização em Cards (Mobile Responsivo) -->
            <div class="d-md-none d-flex flex-column gap-3">
                @foreach($candidaturas as $c)
                    @php
                        $statusClass = match(strtolower($c->status)) {
                            'analisando' => 'status-badge-analisando',
                            'aprovado' => 'status-badge-aprovado',
                            'reprovado' => 'status-badge-reprovado',
                            default => 'status-badge-pendente',
                        };
                    @endphp
                    <div class="card bg-light border p-3 rounded-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="fw-bold mb-1">
                                    <a href="{{ route('vagas.show', $c->vaga->id) }}" class="text-dark text-decoration-none">
                                        {{ $c->vaga->titulo }}
                                    </a>
                                </h6>
                                <small class="text-muted d-block">
                                    {{ $c->vaga->confidencial ? 'Empresa Confidencial' : ($c->vaga->empresa->nome_empresa ?? 'Empresa') }}
                                </small>
                            </div>
                            <span class="badge {{ $statusClass }} px-2 py-1 text-uppercase fw-bold rounded-pill small">
                                {{ $c->status }}
                            </span>
                        </div>

                        <div class="small text-muted mb-3">
                            <i class="fa-regular fa-clock me-1"></i> Candidatou-se em {{ $c->data_candidatura->format('d/m/Y H:i') }}
                        </div>

                        <div class="d-flex gap-2 pt-2 border-top">
                            @if($c->chat)
                                <a href="{{ route('chat.show', $c->chat->id) }}" class="btn btn-sm btn-pl-primary flex-grow-1">
                                    <i class="fa-solid fa-comments me-1"></i> Chat
                                </a>
                            @endif
                            <form method="POST" action="{{ route('candidaturas.cancelar', $c->id) }}" class="flex-grow-1 form-cancelar-candidatura">
                                @csrf
                                <button type="button" class="btn btn-sm btn-outline-danger w-100 btn-cancelar" data-vaga="{{ $c->vaga->titulo }}">
                                    <i class="fa-solid fa-ban me-1"></i> Cancelar
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $candidaturas->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <div class="rounded-circle d-inline-flex p-4 mb-3" style="background: var(--pl-green-soft); color: var(--pl-green);">
                    <i class="fa-solid fa-paper-plane fa-3x"></i>
                </div>
                <h4 class="fw-bold" style="color: var(--pl-dark);">Nenhuma candidatura registrada</h4>
                <p class="text-muted small mb-4">Você ainda não se inscreveu para nenhuma oportunidade de trabalho.</p>
                <a href="{{ route('vagas.index') }}" class="btn btn-pl-primary px-4 py-2">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> Explorar Vagas Disponíveis
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-cancelar').forEach(btn => {
            btn.addEventListener('click', function () {
                const form = this.closest('form');
                const vagaTitulo = this.dataset.vaga || 'esta vaga';

                Swal.fire({
                    title: 'Cancelar Candidatura?',
                    text: `Deseja realmente retirar sua candidatura para "${vagaTitulo}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Sim, cancelar candidatura',
                    cancelButtonText: 'Manter inscrição'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endpush
