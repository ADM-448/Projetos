@extends('layouts.app')

@section('title', 'PDIs - Planos de Desenvolvimento')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<style>
    :root {
        --color-primary: #4f46e5;
        --color-primary-hover: #3730a3;
        --color-accent: #6d28d9;
        --color-slate-50: #f8fafc;
        --color-slate-100: #f1f5f9;
        --color-slate-500: #64748b;
        --color-slate-900: #0f172a;
        --radius-card: 16px;
        --radius-element: 10px;
        --radius-pill: 99px;
        --shadow-card: 0 4px 16px rgba(0,0,0,.08);
        --shadow-hover: 0 10px 40px rgba(0,0,0,.14);
        --border-soft: 1px solid #e9eaf0;
        --font-main: 'Inter', system-ui, sans-serif;
    }

    .pdi-page {
        font-family: var(--font-main);
        background: var(--color-slate-50);
        min-height: 100vh;
        padding: 24px;
    }

    /* ── Responsividade Mobile & Zoom ── */
    @media (max-width: 1024px) {
        .pdi-page {
            padding: 16px;
        }

        .page-header {
            flex-direction: column;
            align-items: stretch;
        }

        .page-title {
            font-size: 1.35rem !important;
        }

        .kpi-grid {
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .kpi-card {
            padding: 16px;
            gap: 12px;
        }

        .kpi-icon {
            width: 42px;
            height: 42px;
            font-size: 1.1rem;
        }

        .kpi-value {
            font-size: 1.5rem;
        }

        /* Abas em coluna no mobile */
        .tab-nav {
            flex-wrap: wrap;
            border-bottom: none;
            gap: 8px;
        }

        .tab-btn {
            border: var(--border-soft);
            border-radius: var(--radius-element) !important;
            border-bottom: 2px solid transparent !important;
            padding: 9px 14px;
            font-size: 0.82rem;
            flex: 1;
            white-space: nowrap;
        }

        .tab-btn.active {
            background: rgba(79,70,229,0.07);
            border-color: var(--color-primary) !important;
        }

        /* Ocultar thead no mobile */
        .modern-table thead {
            display: none;
        }

        .modern-table,
        .modern-table tbody,
        .modern-table tr,
        .modern-table td {
            display: block;
            width: 100%;
        }

        /* Cada linha vira um card */
        .modern-table tbody tr {
            background: #fff;
            border-radius: var(--radius-card);
            border: var(--border-soft);
            box-shadow: var(--shadow-card);
            margin-bottom: 14px;
            padding: 12px 16px;
            overflow: hidden;
            box-sizing: border-box;
        }

        .modern-table tbody tr:hover td {
            background: transparent;
        }

        /* Cada célula usa flex coluna: label em cima, valor embaixo */
        .modern-table td {
            display: flex !important;
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
            border: none;
            border-bottom: 1px solid #f1f5f9;
            padding: 10px 0;
            box-sizing: border-box;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        .modern-table td:last-child {
            border-bottom: none;
            padding-bottom: 4px;
        }

        /* Rótulo via data-label como pseudo-elemento inline no fluxo */
        .modern-table td::before {
            content: attr(data-label);
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--color-slate-500);
            margin-bottom: 2px;
            /* SEM position absolute - fluxo normal */
            position: static;
        }

        /* Progresso ocupa a largura total */
        .modern-table td[data-label="Progresso"] > div {
            width: 100%;
        }

        /* Botão "Criar Novo PDI" full-width */
        .btn-primary-full {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .kpi-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 32px;
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--color-slate-900);
        margin: 0 0 4px 0;
        letter-spacing: -0.02em;
    }

    .page-subtitle {
        color: var(--color-slate-500);
        font-size: 0.95rem;
        margin: 0;
    }

    /* KPI Cards */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .kpi-card {
        background: #fff;
        border-radius: var(--radius-card);
        padding: 22px 24px;
        box-shadow: var(--shadow-card);
        border: var(--border-soft);
        display: flex;
        align-items: center;
        gap: 18px;
        transition: all 0.3s ease;
    }

    .kpi-card:hover {
        box-shadow: var(--shadow-hover);
        transform: translateY(-3px);
    }

    .kpi-icon {
        width: 52px;
        height: 52px;
        border-radius: var(--radius-element);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    .kpi-label {
        font-size: 0.82rem;
        color: var(--color-slate-500);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
    }

    .kpi-value {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--color-slate-900);
        line-height: 1;
    }

    /* Tabs */
    .tab-nav {
        display: flex;
        gap: 4px;
        margin-bottom: 20px;
        border-bottom: 2px solid #e9eaf0;
        padding-bottom: 0;
    }

    .tab-btn {
        padding: 12px 22px;
        font-size: 0.95rem;
        border: none;
        background: none;
        font-weight: 600;
        color: var(--color-slate-500);
        cursor: pointer;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        transition: all 0.2s;
        border-radius: 8px 8px 0 0;
        font-family: var(--font-main);
    }

    .tab-btn:hover {
        color: var(--color-primary);
        background: rgba(79, 70, 229, 0.05);
    }

    .tab-btn.active {
        color: var(--color-primary);
        border-bottom-color: var(--color-primary);
        font-weight: 700;
    }

    /* Card Panel */
    .card-panel {
        background: #fff;
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-card);
        border: var(--border-soft);
        overflow: hidden;
    }

    /* Wrapper para scroll horizontal da tabela em telas médias */
    .table-scroll-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 4px;
    }

    /* Scrollbar Estilizado Roxo */
    .table-scroll-wrap::-webkit-scrollbar {
        height: 8px;
    }
    .table-scroll-wrap::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 8px;
    }
    .table-scroll-wrap::-webkit-scrollbar-thumb {
        background: rgba(79, 70, 229, 0.6); /* primary com opacidade */
        border-radius: 8px;
    }
    .table-scroll-wrap::-webkit-scrollbar-thumb:hover {
        background: rgba(79, 70, 229, 1);
    }

    @media (max-width: 768px) {
        .card-panel {
            background: transparent;
            box-shadow: none;
            border: none;
            border-radius: 0;
            overflow: visible;
        }

        .table-scroll-wrap {
            overflow: visible;
        }
    }

    /* Modern Table */
    .modern-table {
        width: 100%;
        min-width: unset; /* Permite espremer a tabela sem scroll horizontal no zoom */
        border-collapse: separate;
        border-spacing: 0;
    }

    .modern-table th {
        padding: 12px 10px;
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--color-slate-500);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #edf2f7;
        background: var(--color-slate-50);
        white-space: nowrap;
        }

    .modern-table td {
        padding: 14px 10px;
        border-bottom: 1px solid #edf2f7;  /* divisória mais visível */
        vertical-align: middle;
        }

    .modern-table tbody tr {
        transition: background 0.15s;
    }

    .modern-table tr:last-child td {
        border-bottom: none;
    }

    .modern-table tbody tr:hover td {
        background: rgba(79, 70, 229, 0.03);
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: var(--radius-pill);
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        border: none;
        font-family: var(--font-main);
    }

    .btn-primary {
        background: var(--color-primary);
        color: #fff;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
    }

    .btn-primary:hover {
        background: var(--color-primary-hover);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
    }

    .btn-secondary {
        background: var(--color-slate-50);
        color: var(--color-slate-900);
        border: 1px solid #e2e8f0;
    }

    .btn-secondary:hover {
        background: var(--color-slate-100);
        transform: translateY(-1px);
    }

    .btn-sm {
        padding: 6px 14px;
        font-size: 0.82rem;
    }

    .btn-danger {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .btn-danger:hover {
        background: rgba(239, 68, 68, 0.2);
    }

    /* Empty State */
    .empty-state {
        padding: 60px 20px;
        color: var(--color-slate-500);
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .empty-state i {
        font-size: 2.5rem;
        margin-bottom: 12px;
        display: block;
        color: #cbd5e1;
    }
</style>

<div class="pdi-page">
    <div class="page-header">
        <div>
            <h1 class="page-title">Planos de Desenvolvimento Individual (PDI)</h1>
            <p class="page-subtitle">Acompanhe e gerencie metas de capacitação e competências.</p>
        </div>
        @can('create', App\Models\Pdi::class)
            <a href="{{ route('pdis.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Criar Novo PDI
            </a>
        @endcan
    </div>

    <!-- KPIs Rápidos -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(79, 70, 229, 0.1); color: var(--color-primary);">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div>
                <div class="kpi-label">Total de PDIs</div>
                <div class="kpi-value">{{ $kpiTotal }}</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(245, 158, 11, 0.1); color: #d97706;">
                <i class="fa-solid fa-spinner"></i>
            </div>
            <div>
                <div class="kpi-label">Em Andamento</div>
                <div class="kpi-value">{{ $kpiAndamento }}</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(16, 185, 129, 0.1); color: #059669;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="kpi-label">Concluídos</div>
                <div class="kpi-value">{{ $kpiConcluidos }}</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(100, 116, 139, 0.1); color: var(--color-slate-500);">
                <i class="fa-solid fa-hourglass-start"></i>
            </div>
            <div>
                <div class="kpi-label">Não Iniciados</div>
                <div class="kpi-value">{{ $kpiNaoIniciados }}</div>
            </div>
        </div>
    </div>

    @if(auth()->user()->perfil_acesso->value !== 'colaborador')
        <!-- Abas de Navegação -->
        <div class="tab-nav">
            <button class="tab-btn active" onclick="switchTab('meus-pdis', this)">
                <i class="fa-solid fa-user"></i> Meus PDIs
            </button>
            <button class="tab-btn" onclick="switchTab('pdis-equipe', this)">
                <i class="fa-solid fa-people-group"></i> PDIs da Equipe
            </button>
            <button class="tab-btn" onclick="switchTab('grupos-pdi', this)">
                <i class="fa-solid fa-boxes-stacked"></i> PDIs em Grupo (Lotes)
            </button>
        </div>
    @endif

    <!-- Conteúdo das Abas -->
    <div id="meus-pdis" class="tab-content">
        <div class="card-panel">
            <div class="table-scroll-wrap">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Título do PDI</th>
                        <th>Gestor Responsável</th>
                        <th>Status</th>
                        <th style="width: 180px;">Progresso</th>
                        <th>Data Limite</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($meusPdis as $pdi)
                        <tr>
                            <td data-label="Título do PDI" style="font-weight: 700; color: var(--color-slate-900);">{{ $pdi->titulo }}</td>
                            <td data-label="Gestor" style="color: var(--color-slate-500);">{{ $pdi->gestor->name ?? 'RH/Administração' }}</td>
                            <td data-label="Status">
                                @include('pdis.partials.badge-status', ['status' => $pdi->status->value])
                            </td>
                            <td data-label="Progresso">
                                @include('pdis.partials.progress-bar', ['progresso' => $pdi->progresso])
                            </td>
                            <td data-label="Data Limite" style="color: var(--color-slate-500); font-size: 0.9rem;">
                                {{ $pdi->data_fim ? $pdi->data_fim->format('d/m/Y') : 'Sem prazo' }}
                            </td>
                            <td data-label="Ações">
                                <div style="display: inline-flex; gap: 8px; align-items: center;">
                                    <a href="{{ route('pdis.show', $pdi->id) }}" class="btn btn-secondary btn-sm">
                                        <i class="fa-solid fa-eye"></i> Ver Detalhes
                                    </a>
                                    @can('delete', $pdi)
                                        <form action="{{ route('pdis.destroy', $pdi->id) }}" method="POST" style="display:inline-block;" data-confirm="Tem certeza que deseja excluir permanentemente este PDI e todos os seus dados associados?" data-confirm-title="Excluir PDI">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Excluir PDI">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="fa-solid fa-folder-open"></i>
                                    Você não possui nenhum PDI atribuído no momento.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div><!-- /.table-scroll-wrap -->
        </div>
    </div>

    @if(auth()->user()->perfil_acesso->value !== 'colaborador')
        <!-- Aba PDIs da Equipe -->
        <div id="pdis-equipe" class="tab-content" style="display: none;">
            <div class="card-panel">
                <div class="table-scroll-wrap">
                <table class="modern-table">
                    <thead>
                    <tr>
                            <th>Colaborador</th>
                            <th>Título do PDI</th>
                            <th>Status</th>
                            <th style="width: 180px;">Progresso</th>
                            <th>Data Limite</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pdisEquipe as $pdi)
                            <tr>
                                <td data-label="Colaborador">
                                    <div style="font-weight: 700; color: var(--color-slate-900);">{{ $pdi->colaborador->name }}</div>
                                    <div style="font-size: 0.8rem; color: var(--color-slate-500);">{{ $pdi->colaborador->cargo->nome ?? 'Sem Cargo' }}</div>
                                </td>
                                <td data-label="Título" style="color: var(--color-slate-500);">{{ $pdi->titulo }}</td>
                                <td data-label="Status">
                                    @include('pdis.partials.badge-status', ['status' => $pdi->status->value])
                                </td>
                                <td data-label="Progresso">
                                    @include('pdis.partials.progress-bar', ['progresso' => $pdi->progresso])
                                </td>
                                <td data-label="Data Limite" style="color: var(--color-slate-500); font-size: 0.9rem;">
                                    {{ $pdi->data_fim ? $pdi->data_fim->format('d/m/Y') : 'Sem prazo' }}
                                </td>
                                <td data-label="Ações">
                                    <div style="display: inline-flex; gap: 8px; align-items: center;">
                                        <a href="{{ route('pdis.show', $pdi->id) }}" class="btn btn-secondary btn-sm">
                                            <i class="fa-solid fa-eye"></i> Ver Detalhes
                                        </a>
                                        @can('update', $pdi)
                                            @if($pdi->pdi_grupo_id === null)
                                                <a href="{{ route('pdis.edit', $pdi->id) }}" class="btn btn-secondary btn-sm" title="Editar PDI">
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>
                                            @endif
                                        @endcan
                                        @can('delete', $pdi)
                                            <form action="{{ route('pdis.destroy', $pdi->id) }}" method="POST" data-confirm="Deseja excluir permanentemente este PDI e todas as suas metas associadas?" data-confirm-title="Confirmar Exclusão" data-confirm-type="danger" style="margin:0;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Excluir PDI">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fa-solid fa-people-group"></i>
                                        Nenhum colaborador da sua equipe possui PDI cadastrado.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div><!-- /.table-scroll-wrap -->
            </div>
        </div>

        <!-- Aba PDIs em Grupo -->
        <div id="grupos-pdi" class="tab-content" style="display: none;">
            <div class="card-panel">
                <div class="table-scroll-wrap">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Título do Lote</th>
                            <th>Categoria</th>
                            <th style="">Colaboradores</th>
                            <th style="">Período</th>
                            <th style="">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pdiGrupos as $grupo)
                            <tr>
                                <td data-label="Título do Lote">
                                    <div style="font-weight: 700; color: var(--color-slate-900);">{{ $grupo->titulo }}</div>
                                    <div style="font-size: 0.8rem; color: var(--color-slate-500);">Criado por: {{ $grupo->gestor->name ?? 'RH' }}</div>
                                </td>
                                <td data-label="Categoria" style="color: var(--color-slate-500);">{{ $grupo->categoria ?? 'Não definida' }}</td>
                                <td data-label="Colaboradores" style="">
                                    <span style="background: rgba(79, 70, 229, 0.1); color: var(--color-primary); padding: 5px 12px; font-size: 0.85rem; font-weight: 700; border-radius: var(--radius-pill);">
                                        {{ $grupo->pdis_count }} colaboradores
                                    </span>
                                </td>
                                <td data-label="Período" style="color: var(--color-slate-500); font-size: 0.9rem;">
                                    {{ $grupo->data_inicio ? $grupo->data_inicio->format('d/m/Y') : '' }}
                                    {{ $grupo->data_fim ? ' até ' . $grupo->data_fim->format('d/m/Y') : 'Sem prazo' }}
                                </td>
                                <td data-label="Ações" style="">
                                    <div style="display: inline-flex; gap: 8px; align-items: center; justify-content: center;">
                                        @can('update', $grupo)
                                            <a href="{{ route('pdi-grupos.edit', $grupo->id) }}" class="btn btn-secondary btn-sm">
                                                <i class="fa-solid fa-pen-to-square"></i> Editar Lote
                                            </a>
                                        @endcan
                                        @can('delete', $grupo)
                                            <form action="{{ route('pdi-grupos.destroy', $grupo->id) }}" method="POST" data-confirm="ATENÇÃO: Isso excluirá o lote de PDIs de TODOS os colaboradores vinculados a este grupo. Deseja prosseguir?" data-confirm-title="Confirmar Exclusão do Lote" data-confirm-type="danger" style="margin:0;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fa-solid fa-trash"></i> Excluir Lote
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="fa-solid fa-boxes-stacked"></i>
                                        Nenhum lote de PDIs em grupo foi criado até o momento.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div><!-- /.table-scroll-wrap -->
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        
        if (tab === 'equipe') {
            const btn = document.querySelector('button[onclick*="pdis-equipe"]');
            if (btn) btn.click();
        } else if (tab === 'grupos') {
            const btn = document.querySelector('button[onclick*="grupos-pdi"]');
            if (btn) btn.click();
        }
    });

    function switchTab(tabId, buttonEl) {
        document.querySelectorAll('.tab-content').forEach(function(content) {
            content.style.display = 'none';
        });
        
        document.querySelectorAll('.tab-btn').forEach(function(btn) {
            btn.classList.remove('active');
        });

        document.getElementById(tabId).style.display = 'block';
        buttonEl.classList.add('active');
    }
</script>
@endpush
