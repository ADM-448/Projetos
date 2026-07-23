@extends('layouts.admin')

@section('title', 'Colaboradores')

@section('content')

{{-- Bloco de Estilos CSS (Corrigido e Unificado) --}}
@push('styles')
<style>
/* Barra de Filtros e Botões */
.filters-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
}

.search-box {
    display: flex;
    align-items: center;
    background: white;
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 5px 10px;
    width: 300px;
}

.search-box input {
    border: none;
    outline: none;
    width: 100%;
    margin-left: 10px;
}

/* A Tabela */
.colab-list {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.colab-list th {
    text-align: left;
    padding: 15px;
    background-color: #f1f1f1;
    color: #555;
    font-weight: 700;
    border-bottom: 2px solid #ddd;
}

.colab-list td {
    padding: 15px;
    vertical-align: middle;
    border-bottom: 1px solid #eee;
    color: #333;
}

/* Coluna do Nome + Foto */
.colab-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.avatar-placeholder {
    width: 40px;
    height: 40px;
    background-color: #6a4c7c;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
    text-transform: uppercase;
}

.avatar-img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

/* Coluna de Cargo e Depto */
.cargo-text {
    font-weight: bold;
    display: block;
    font-size: 14px;
    color: #222;
}

.depto-text {
    font-size: 12px;
    color: #777;
}

/* Status */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: bold;
}

.status-ativo {
    background-color: #e6fffa;
    color: #00b894;
}

.status-inativo {
    background-color: #ffe6e6;
    color: #ff7675;
}

/* Botões de Ação */
.btn-view {
    text-decoration: none;
    background-color: #3b5998;
    color: white;
    padding: 6px 12px;
    border-radius: 4px;
    font-weight: bold;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: background-color 0.2s;
}

.btn-view:hover {
    background-color: #2d4373;
    text-decoration: none;
    color: white;
}

.actions-cell {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
}

.icon-action {
    color: #777;
    cursor: pointer;
    transition: 0.2s;
}

.icon-action:hover {
    color: #3b5998;
    transform: scale(1.1);
}

@media (max-width: 768px) {
    .colab-list td {
        flex-direction: column !important;
        align-items: flex-start !important;
        text-align: left !important;
    }
    .colab-list td::before {
        margin-bottom: 5px !important;
        width: 100% !important;
    }
    .colab-info {
        justify-content: flex-start !important;
    }
    .actions-cell {
        justify-content: flex-start !important;
    }
}
</style>
@endpush

{{-- Conteúdo HTML da Página --}}
<div class="header-actions">
    <h1 class="page-title">Colaboradores</h1>
    <a href="{{ route('colaboradores.create') }}" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Novo Colaborador
    </a>
</div>

<div class="card-container">
    <div class="filters-bar">
        <div class="search-box">
            <input type="text" placeholder="Pesquisar..." style="border:none; outline:none;">
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
       
    </div>

    <table class="colab-list">
        <thead>
            <tr>
                <th style="text-align: left;">Colaborador</th>
                <th style="text-align: left;">Cargo / Depto</th>
                <th style="text-align: left;">Nível de Acesso</th>
                <th style="text-align: left;">Admissão</th>
                <th style="text-align: left;">Status</th>
                <th style="text-align: center;">Detalhes</th>
                <th style="text-align: right;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($colaboradores as $colaborador)
            <tr>
                <td data-label="Colaborador">
                    <div class="colab-info">
                        @if($colaborador->foto_perfil)
                        <img src="{{ $colaborador->foto_perfil }}" class="avatar-img">
                        @else
                        <div class="avatar-placeholder">
                            {{ substr($colaborador->name, 0, 1) }}
                        </div>
                        @endif

                        <div>
                            <div style="font-weight:bold;">{{ $colaborador->name }}</div>
                            <div style="font-size:12px; color:#888;">{{ $colaborador->email }}</div>
                        </div>
                    </div>
                </td>

                <td data-label="Cargo / Departamento">
                    <span class="cargo-text">{{ $colaborador->cargo->nome ?? 'Sem Cargo' }}</span>
                    <span class="depto-text">{{ $colaborador->departamento->nome ?? 'Sem Depto' }}</span>
                </td>

                <td data-label="Perfil">
                    @php
                        $perfil = $colaborador->perfil_acesso;
                        $perfilLabel = [
                            'admin' => 'Admin',
                            'master' => 'Master',
                            'gestor' => 'Gestor',
                            'rh' => 'RH',
                            'mediador' => 'Mediador',
                            'colaborador' => 'Colaborador'
                        ][$perfil] ?? ucfirst($perfil);
                        
                        $perfilColor = [
                            'admin' => '#dc3545',
                            'master' => '#6f42c1',
                            'gestor' => '#fd7e14',
                            'rh' => '#3498db',
                            'mediador' => '#20c997',
                            'colaborador' => '#0dcaf0'
                        ][$perfil] ?? '#6c757d';
                    @endphp
                    <span style="display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; color: white; background-color: {{ $perfilColor }}">
                        {{ $perfilLabel }}
                    </span>
                </td>

                <td data-label="Admissão">
                    {{ $colaborador->data_admissao ? \Carbon\Carbon::parse($colaborador->data_admissao)->format('d/m/Y') : '-' }}
                </td>

                <td data-label="Status">
                    @if($colaborador->status == 'ativo')
                    <span class="status-badge status-ativo"><i class="fa-solid fa-circle-check"></i> Ativo</span>
                    @else
                    <span class="status-badge status-inativo"><i class="fa-solid fa-circle-xmark"></i> Inativo</span>
                    @endif
                </td>

                <td data-label="Mais" style="text-align: center;">
                    <a href="{{ route('colaboradores.show', $colaborador->id) }}" class="btn-view">
                        Ver mais +
                    </a>
                </td>

                <td data-label="Ações">
                    <div class="actions-cell" style="display: flex; justify-content: flex-end; align-items: center; gap: 15px;">
                        <a href="{{ route('colaboradores.edit', $colaborador->id) }}" title="Editar" style="color:#3b5998; font-size:1.2em; transition: 0.2s; margin:0;" onmouseover="this.style.color='#2d4373'" onmouseout="this.style.color='#3b5998'">
                            <i class="fa-solid fa-pen"></i>
                        </a>

                        <form action="{{ route('colaboradores.destroy', $colaborador->id) }}" method="POST"
                            class="confirm-delete" data-confirm-message="Deseja excluir o colaborador {{ $colaborador->name }}?" style="margin: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background:none; border:none; cursor:pointer; color:#e74c3c; font-size:1.2em; transition: 0.2s; padding:0; margin:0;" onmouseover="this.style.color='#c0392b'" onmouseout="this.style.color='#e74c3c'" title="Excluir">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:30px; color:#777;">
                    Nenhum colaborador encontrado.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
