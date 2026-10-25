@extends('layouts.app')

@section('title', 'Gestão de Equipe - ' . $empresa->nome_empresa)

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
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
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
                    <i class="fa-solid fa-building me-1"></i> {{ $empresa->nome_empresa }}
                </span>
                <span class="badge bg-light text-muted border">Gestão Corporativa</span>
            </div>
            <h2 class="h3 fw-bold mb-1">Equipe e Recrutadores</h2>
            <p class="text-muted small mb-0">Gerencie os colaboradores com acesso para publicar vagas, triar candidatos e trocar mensagens.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap w-100 w-sm-auto mt-2 mt-sm-0">
            <a href="{{ route('dashboard') }}" class="btn btn-pl-secondary d-flex align-items-center justify-content-center gap-2 flex-grow-1 flex-sm-grow-0">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Voltar ao Painel</span>
            </a>
            @if($isEmpresaAdmin)
                <button type="button" class="btn btn-pl-primary d-flex align-items-center justify-content-center gap-2 shadow-sm flex-grow-1 flex-sm-grow-0" data-bs-toggle="modal" data-bs-target="#modalAdicionarUsuario">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Adicionar Usuário</span>
                </button>
            @endif
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

    <!-- 4 KPIs da Equipe -->
    <div class="row g-3 mb-4">
        <!-- Total de Membros -->
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon-circle" style="background: #f1f5f9; color: #334155;">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="h3 fw-bold mb-0 text-dark">{{ $kpis['total_membros'] }}</div>
                <small class="text-muted fw-semibold">Total de Membros</small>
            </div>
        </div>

        <!-- Administradores -->
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon-circle" style="background: var(--pl-green-soft); color: var(--pl-green);">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div class="h3 fw-bold mb-0 text-dark">{{ $kpis['total_admins'] }}</div>
                <small class="text-muted fw-semibold">Administradores</small>
            </div>
        </div>

        <!-- Recrutadores / RH -->
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon-circle" style="background: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <div class="h3 fw-bold mb-0 text-dark">{{ $kpis['total_recrutadores'] }}</div>
                <small class="text-muted fw-semibold">Recrutadores / RH</small>
            </div>
        </div>

        <!-- Membros Ativos -->
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon-circle" style="background: #dcfce7; color: #16a34a;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="h3 fw-bold mb-0 text-dark">{{ $kpis['membros_ativos'] }}</div>
                <small class="text-muted fw-semibold">Acessos Ativos</small>
            </div>
        </div>
    </div>

    <!-- Tabela de Membros -->
    <div class="pl-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-1 text-dark">Colaboradores da Empresa</h5>
                <p class="text-muted small mb-0">Lista de usuários vinculados com acesso ao painel de recrutamento.</p>
            </div>
            @if($isEmpresaAdmin)
                <button type="button" class="btn btn-sm btn-pl-primary" data-bs-toggle="modal" data-bs-target="#modalAdicionarUsuario">
                    <i class="fa-solid fa-plus me-1"></i> Novo Colaborador
                </button>
            @endif
        </div>

        <!-- Tabela Desktop -->
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="small text-muted text-uppercase">
                        <th style="font-size: 0.75rem;">Usuário</th>
                        <th style="font-size: 0.75rem;">Função</th>
                        <th style="font-size: 0.75rem;">Vagas Criadas</th>
                        <th style="font-size: 0.75rem;">Status do Acesso</th>
                        <th style="font-size: 0.75rem;">Membro desde</th>
                        <th style="font-size: 0.75rem;" class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($membros as $membro)
                        @php
                            $isSelf = Auth::id() === $membro->id;
                            $role = $membro->pivot->role;
                            $status = $membro->pivot->status;
                            $totalVagasMembro = $vagasPorUsuario[$membro->id] ?? 0;
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-circle {{ $role === 'admin' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }}">
                                        {{ strtoupper(substr($membro->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                            <span>{{ $membro->name }}</span>
                                            @if($isSelf)
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size: 0.65rem;">Você</span>
                                            @endif
                                        </div>
                                        <div class="small text-muted">
                                            <i class="fa-regular fa-envelope me-1"></i> {{ $membro->email }}
                                            @if($membro->cpf)
                                                &bull; CPF: {{ $membro->cpf }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($role === 'admin')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-semibold">
                                        <i class="fa-solid fa-user-shield me-1"></i> Administrador
                                    </span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill fw-semibold">
                                        <i class="fa-solid fa-user-tie me-1"></i> Recrutador
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1 rounded-pill fw-semibold small">
                                    {{ $totalVagasMembro }} {{ $totalVagasMembro === 1 ? 'vaga' : 'vagas' }}
                                </span>
                            </td>
                            <td>
                                @if($status)
                                    <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill fw-semibold small">
                                        <i class="fa-solid fa-circle-check me-1"></i> Ativo
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill fw-semibold small">
                                        <i class="fa-solid fa-ban me-1"></i> Suspenso
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="small text-muted">
                                    {{ $membro->pivot->created_at ? $membro->pivot->created_at->format('d/m/Y') : 'Inicial' }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if($isSelf)
                                    <span class="text-muted small fst-italic">Conta atual</span>
                                @elseif(!$isEmpresaAdmin)
                                    <span class="text-muted small">&mdash;</span>
                                @else
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <!-- Alternar Função -->
                                        <form action="{{ route('empresa.equipe.funcao', $membro->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @if($role === 'recrutador')
                                                <input type="hidden" name="role" value="admin">
                                                <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2 rounded-pill" title="Promover a Administrador">
                                                    <i class="fa-solid fa-arrow-up me-1"></i> Promover Admin
                                                </button>
                                            @else
                                                <input type="hidden" name="role" value="recrutador">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary py-1 px-2 rounded-pill" title="Rebaixar para Recrutador">
                                                    <i class="fa-solid fa-arrow-down me-1"></i> Recrutador
                                                </button>
                                            @endif
                                        </form>

                                        <!-- Alternar Status (Ativar / Suspender) -->
                                        <form action="{{ route('empresa.equipe.status', $membro->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @if($status)
                                                <button type="submit" class="btn btn-sm btn-light py-1 px-2 rounded-circle" title="Suspender Acesso" onclick="return confirm('Deseja realmente suspender o acesso de {{ $membro->name }}?')">
                                                    <i class="fa-solid fa-user-slash text-warning"></i>
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-sm btn-light py-1 px-2 rounded-circle" title="Reativar Acesso">
                                                    <i class="fa-solid fa-user-check text-success"></i>
                                                </button>
                                            @endif
                                        </form>

                                        <!-- Botão Remover com Modal de Confirmação -->
                                        <button type="button" class="btn btn-sm btn-light py-1 px-2 rounded-circle text-danger" title="Remover da Equipe" data-bs-toggle="modal" data-bs-target="#modalRemover{{ $membro->id }}">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>

                                    <!-- Modal de Remoção -->
                                    <div class="modal fade" id="modalRemover{{ $membro->id }}" tabindex="-1" aria-hidden="true" style="text-align: left;">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <form action="{{ route('empresa.equipe.remover', $membro->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <div class="modal-header border-bottom p-3">
                                                        <h6 class="modal-title fw-bold text-danger d-flex align-items-center gap-2">
                                                            <i class="fa-solid fa-triangle-exclamation"></i>
                                                            <span>Remover Colaborador da Equipe</span>
                                                        </h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <p class="mb-3">
                                                            Você está prestes a remover o vínculo de <strong>{{ $membro->name }}</strong> ({{ $membro->email }}) com a empresa <strong>{{ $empresa->nome_empresa }}</strong>.
                                                        </p>

                                                        @if($totalVagasMembro > 0)
                                                            <div class="p-3 bg-light rounded-3 border mb-3">
                                                                <label class="form-label small fw-bold text-dark mb-2">Destino das Vagas Publicadas ({{ $totalVagasMembro }} vagas):</label>
                                                                <div class="form-check mb-2">
                                                                    <input class="form-check-input" type="radio" name="metodo" id="metodoTransferir{{ $membro->id }}" value="transferir" checked>
                                                                    <label class="form-check-label small" for="metodoTransferir{{ $membro->id }}">
                                                                        <strong>Transferir para mim (Recomendado)</strong>
                                                                        <div class="text-muted" style="font-size: 0.78rem;">As vagas permanecerão ativas na empresa sob sua responsabilidade.</div>
                                                                    </label>
                                                                </div>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="radio" name="metodo" id="metodoManter{{ $membro->id }}" value="manter">
                                                                    <label class="form-check-label small" for="metodoManter{{ $membro->id }}">
                                                                        <strong>Manter histórico sem reatribuição</strong>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        <div class="text-muted small">
                                                            O colaborador perderá imediatamente o acesso ao painel de recrutamento desta empresa.
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top p-3 d-flex justify-content-between">
                                                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-sm btn-danger px-3">Confirmar Remoção</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-users fa-2x mb-2 opacity-50"></i>
                                <div class="fw-semibold">Nenhum membro encontrado na equipe.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Visualização Mobile (Cards Responsivos) -->
        <div class="d-md-none d-flex flex-column gap-2">
            @forelse($membros as $membro)
                @php
                    $isSelf = Auth::id() === $membro->id;
                    $role = $membro->pivot->role;
                    $status = $membro->pivot->status;
                    $totalVagasMembro = $vagasPorUsuario[$membro->id] ?? 0;
                @endphp
                <div class="p-3 bg-light rounded-3 border">
                    <!-- Topo: Avatar, Nome e Status -->
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                            <div class="avatar-circle flex-shrink-0 {{ $role === 'admin' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }}" style="width: 38px; height: 38px; font-size: 0.95rem;">
                                {{ strtoupper(substr($membro->name, 0, 1)) }}
                            </div>
                            <div class="overflow-hidden">
                                <div class="fw-bold text-dark d-flex align-items-center gap-1 text-truncate">
                                    <span class="text-truncate">{{ $membro->name }}</span>
                                    @if($isSelf)
                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill flex-shrink-0" style="font-size: 0.6rem;">Você</span>
                                    @endif
                                </div>
                                <div class="small text-muted text-truncate" style="font-size: 0.75rem;">
                                    {{ $membro->email }}
                                </div>
                            </div>
                        </div>

                        <div>
                            @if($status)
                                <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill fw-semibold" style="font-size: 0.68rem;">
                                    <i class="fa-solid fa-circle-check me-1"></i> Ativo
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill fw-semibold" style="font-size: 0.68rem;">
                                    <i class="fa-solid fa-ban me-1"></i> Suspenso
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($membro->cpf)
                        <div class="text-muted small mb-2 ps-1" style="font-size: 0.75rem;">
                            <i class="fa-regular fa-id-card me-1 text-muted"></i> CPF: {{ $membro->cpf }}
                        </div>
                    @endif

                    <!-- Metadados: Função, Vagas e Data -->
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2 pb-2 border-top border-bottom pt-2">
                        @if($role === 'admin')
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-semibold" style="font-size: 0.72rem;">
                                <i class="fa-solid fa-user-shield me-1"></i> Admin
                            </span>
                        @else
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill fw-semibold" style="font-size: 0.72rem;">
                                <i class="fa-solid fa-user-tie me-1"></i> Recrutador
                            </span>
                        @endif

                        <span class="badge bg-white text-dark border px-2 py-1 rounded-pill fw-semibold" style="font-size: 0.72rem;">
                            <i class="fa-solid fa-briefcase text-muted me-1"></i> {{ $totalVagasMembro }} {{ $totalVagasMembro === 1 ? 'vaga' : 'vagas' }}
                        </span>

                        <span class="text-muted ms-auto" style="font-size: 0.72rem;">
                            <i class="fa-regular fa-calendar me-1"></i> {{ $membro->pivot->created_at ? $membro->pivot->created_at->format('d/m/Y') : 'Inicial' }}
                        </span>
                    </div>

                    <!-- Ações Mobile -->
                    <div class="d-flex align-items-center justify-content-between pt-1">
                        @if($isSelf)
                            <span class="text-muted small fst-italic" style="font-size: 0.75rem;">Sua conta ativa</span>
                        @elseif(!$isEmpresaAdmin)
                            <span class="text-muted small" style="font-size: 0.75rem;">Apenas Admin gerencia</span>
                        @else
                            <div class="d-flex align-items-center gap-1 w-100 justify-content-end flex-wrap">
                                <!-- Alternar Função -->
                                <form action="{{ route('empresa.equipe.funcao', $membro->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @if($role === 'recrutador')
                                        <input type="hidden" name="role" value="admin">
                                        <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2 rounded-pill" style="font-size: 0.72rem;" title="Promover a Administrador">
                                            <i class="fa-solid fa-arrow-up me-1"></i> Promover Admin
                                        </button>
                                    @else
                                        <input type="hidden" name="role" value="recrutador">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary py-1 px-2 rounded-pill" style="font-size: 0.72rem;" title="Rebaixar para Recrutador">
                                            <i class="fa-solid fa-arrow-down me-1"></i> Rebaixar RH
                                        </button>
                                    @endif
                                </form>

                                <!-- Alternar Status (Ativar / Suspender) -->
                                <form action="{{ route('empresa.equipe.status', $membro->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @if($status)
                                        <button type="submit" class="btn btn-sm btn-light py-1 px-2 rounded-pill text-warning border" style="font-size: 0.72rem;" title="Suspender Acesso" onclick="return confirm('Deseja realmente suspender o acesso de {{ $membro->name }}?')">
                                            <i class="fa-solid fa-user-slash me-1"></i> Suspender
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-light py-1 px-2 rounded-pill text-success border" style="font-size: 0.72rem;" title="Reativar Acesso">
                                            <i class="fa-solid fa-user-check me-1"></i> Reativar
                                        </button>
                                    @endif
                                </form>

                                <!-- Botão Remover (abre modal) -->
                                <button type="button" class="btn btn-sm btn-light py-1 px-2 rounded-circle text-danger border" title="Remover da Equipe" data-bs-toggle="modal" data-bs-target="#modalRemover{{ $membro->id }}">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted small">
                    <i class="fa-solid fa-users fa-2x mb-2 opacity-50"></i>
                    <div>Nenhum membro encontrado na equipe.</div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal Adicionar Usuário -->
@if($isEmpresaAdmin)
<div class="modal fade" id="modalAdicionarUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('empresa.equipe.adicionar') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom p-3">
                    <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-user-plus text-success"></i>
                        <span>Adicionar Novo Colaborador à Equipe</span>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        Cadastre um novo usuário ou vincule um usuário já existente à empresa <strong>{{ $empresa->nome_empresa }}</strong>.
                    </p>

                    <div class="mb-3">
                        <label for="inputNome" class="form-label small fw-bold text-dark">Nome Completo *</label>
                        <input type="text" id="inputNome" name="name" class="form-control" placeholder="ex: Ana Paula Silva" required value="{{ old('name') }}">
                    </div>

                    <div class="mb-3">
                        <label for="inputEmail" class="form-label small fw-bold text-dark">E-mail Corporativo *</label>
                        <input type="email" id="inputEmail" name="email" class="form-control" placeholder="ex: ana.rh@empresa.com" required value="{{ old('email') }}">
                        <div class="form-text small text-muted">Será utilizado para login no portal.</div>
                    </div>

                    <div class="mb-3">
                        <label for="inputCpf" class="form-label small fw-bold text-dark">CPF (Opcional)</label>
                        <input type="text" id="inputCpf" name="cpf" class="form-control" placeholder="000.000.000-00" value="{{ old('cpf') }}">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-7">
                            <label for="inputSenha" class="form-label small fw-bold text-dark">Senha Inicial *</label>
                            <input type="password" id="inputSenha" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required minlength="6">
                        </div>
                        <div class="col-md-5">
                            <label for="inputFuncao" class="form-label small fw-bold text-dark">Função *</label>
                            <select id="inputFuncao" name="role" class="form-select" required>
                                <option value="recrutador" selected>Recrutador / RH</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 border">
                        <div class="small text-muted">
                            <i class="fa-solid fa-circle-info text-primary me-1"></i>
                            <strong>Recrutador:</strong> pode publicar vagas, gerenciar candidatos e responder chats.<br>
                            <strong>Administrador:</strong> tem acesso total, incluindo gerenciar outros membros da equipe.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top p-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-pl-primary px-3">
                        <i class="fa-solid fa-check me-1"></i> Salvar e Adicionar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
