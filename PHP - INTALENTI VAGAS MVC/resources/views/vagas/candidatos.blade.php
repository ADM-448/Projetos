@extends('layouts.app')

@section('title', 'Candidatos - ' . $vaga->titulo)

@section('content')
<div class="container-fluid px-lg-4 py-4" style="max-width: 1440px;">
    
    <!-- Topo da Triagem -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <h2 class="h3 fw-bold mb-1 text-dark">Candidatos: {{ $vaga->titulo }}</h2>
            <p class="text-muted small mb-2">
                <i class="fa-solid fa-location-dot text-danger me-1"></i>
                Vaga em: <strong>{{ $vaga->localizacao ?: ($vaga->cidade . ' - ' . $vaga->estado) }}</strong>
                &bull; Total: <strong>{{ count($candidaturas) }}</strong> candidato(s)
            </p>

            <!-- Filtros (Abas em Pílula) -->
            <div class="d-flex align-items-center gap-2 mt-2">
                <span class="small fw-bold text-muted me-1">Ordenar por:</span>
                <a href="?ordem=recentes" class="btn btn-sm rounded-pill px-3 fw-semibold {{ $ordem === 'recentes' ? 'btn-dark' : 'btn-light border text-muted' }}">
                    <i class="fa-regular fa-clock me-1"></i> Recentes
                </a>
                <a href="?ordem=proximos" class="btn btn-sm rounded-pill px-3 fw-semibold {{ $ordem === 'proximos' ? 'btn-dark' : 'btn-light border text-muted' }}">
                    <i class="fa-solid fa-location-crosshairs me-1 text-success"></i> Próximos
                </a>
            </div>
        </div>

        <div class="d-flex gap-2 align-self-start">
            <a href="{{ route('vagas.show', $vaga->id) }}" class="btn btn-pl-secondary btn-sm" target="_blank">
                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Ver no Portal
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-pl-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Voltar
            </a>
        </div>
    </div>

    <!-- Tabela Estilo Peerlist / MVC -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="ps-4 py-3 text-uppercase text-secondary small fw-bold" style="width: 32%;">Nome / Localização</th>
                            <th class="py-3 text-uppercase text-secondary small fw-bold">Email</th>
                            <th class="py-3 text-uppercase text-secondary small fw-bold">Data</th>
                            <th class="py-3 text-uppercase text-secondary small fw-bold">Status</th>
                            <th class="py-3 text-uppercase text-secondary small fw-bold text-center">Anexo</th>
                            <th class="pe-4 py-3 text-uppercase text-secondary small fw-bold text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($candidaturas as $c)
                            <tr>
                                <!-- Nome e Localização / Distância -->
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold fs-6" style="width: 40px; height: 40px; flex-shrink: 0;">
                                            {{ strtoupper(substr($c->candidato->nome_completo, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark mb-0" style="font-size: 0.95rem;">
                                                <a href="{{ route('candidatos.perfil', ['userId' => $c->candidato->user_id, 'vaga_id' => $vaga->id]) }}" class="text-dark text-decoration-none">
                                                    {{ $c->candidato->nome_completo }}
                                                </a>
                                            </div>
                                            <div class="d-flex align-items-center gap-1 mt-1">
                                                @if($c->distancia_km !== null)
                                                    <span class="badge bg-light text-success border border-success-subtle rounded-pill py-1 px-2" style="font-size: 0.72rem;" title="Distância estimada até o local da vaga">
                                                        <i class="fa-solid fa-location-dot me-1"></i> {{ number_format($c->distancia_km, 1, ',', '.') }} km
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-secondary border rounded-pill py-1 px-2" style="font-size: 0.72rem;">
                                                        <i class="fa-solid fa-map-pin me-1"></i> {{ $c->candidato->cidade ?: 'Não informada' }}{{ $c->candidato->estado ? '/' . $c->candidato->estado : '' }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Email -->
                                <td class="text-muted small">
                                    {{ $c->candidato->user->email ?? '-' }}
                                    @if($c->candidato->celular)
                                        <div class="text-muted" style="font-size: 0.78rem;">
                                            <i class="fa-solid fa-phone me-1"></i> {{ $c->candidato->celular }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Data -->
                                <td class="text-muted small">
                                    {{ $c->data_candidatura->format('d/m/Y') }}
                                    <div style="font-size: 0.75rem;">{{ $c->data_candidatura->format('H:i') }}</div>
                                </td>

                                <!-- Status -->
                                <td>
                                    @php
                                        $badgeClass = match(strtolower($c->status)) {
                                            'aprovado' => 'bg-success text-white',
                                            'em_processo', 'analisando' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                            'reprovado', 'rejeitado' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                            default => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                                        };
                                        $statusLabel = match(strtolower($c->status)) {
                                            'pendente' => 'Pendente',
                                            'em_processo' => 'Em Processo',
                                            'aprovado' => 'Aprovado',
                                            'reprovado' => 'Reprovado',
                                            default => ucfirst($c->status)
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }} px-3 py-2 rounded-pill small fw-semibold">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <!-- Anexo (PDF) -->
                                <td class="text-center">
                                    @if(!empty($c->candidato->curriculo_path))
                                        <a href="{{ asset('storage/' . $c->candidato->curriculo_path) }}" target="_blank" class="text-danger fs-5" title="Baixar/Ver Currículo em PDF">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </a>
                                    @else
                                        <span class="text-muted fs-5" title="Currículo PDF não anexado"><i class="fa-regular fa-file"></i></span>
                                    @endif
                                </td>

                                <!-- Ações -->
                                <td class="pe-4 text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                        <a href="{{ route('candidatos.perfil', ['userId' => $c->candidato->user_id, 'vaga_id' => $vaga->id]) }}" class="btn btn-sm btn-dark rounded-pill px-3 fw-semibold">
                                            Detalhes
                                        </a>
                                        @if($c->chat)
                                            <a href="{{ route('chat.show', $c->chat->id) }}" class="btn btn-sm btn-outline-success rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Conversar no Chat">
                                                <i class="fa-solid fa-comments"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="p-4">
                                        <i class="fa-solid fa-users-slash fa-3x text-muted mb-3 opacity-50"></i>
                                        <h5 class="fw-bold mb-1">Nenhum candidato inscrito</h5>
                                        <p class="text-muted small mb-0">Divulgue o link da vaga para receber candidaturas.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
