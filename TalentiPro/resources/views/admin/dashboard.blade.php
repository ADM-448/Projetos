@extends('layouts.app')

@section('title', 'Painel Administrativo SaaS')
@section('body-class', 'has-sidebar bg-slate-50')

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
            --shadow-card: 0 4px 16px rgba(0, 0, 0, .10);
            --shadow-hover: 0 10px 40px rgba(0, 0, 0, .14);
            --border-soft: 1px solid #e9eaf0;
            --font-main: 'Inter', system-ui, sans-serif;
        }

        .saas-dashboard {
            font-family: var(--font-main);
            color: var(--color-slate-900);
            padding: 24px;
            background-color: var(--color-slate-50);
            min-height: 100vh;
        }

        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 16px;
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

        .status-badge {
            background-color: rgba(16, 185, 129, 0.1);
            /* Soft emerald */
            color: #059669;
            /* Emerald 600 */
            padding: 8px 16px;
            border-radius: var(--radius-pill);
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .kpi-card {
            background: #ffffff;
            border-radius: var(--radius-card);
            padding: 24px;
            box-shadow: var(--shadow-card);
            border: var(--border-soft);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s ease;
        }

        .kpi-card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-2px);
        }

        .kpi-icon {
            width: 56px;
            height: 56px;
            border-radius: var(--radius-element);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .kpi-label {
            font-size: 0.85rem;
            color: var(--color-slate-500);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }

        .kpi-value {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--color-slate-900);
            line-height: 1.1;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 32px;
            align-items: start;
        }

        @media (max-width: 992px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        .card-panel {
            background: #ffffff;
            border-radius: var(--radius-card);
            padding: 32px;
            box-shadow: var(--shadow-card);
            border: var(--border-soft);
        }

        .panel-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--color-slate-900);
            margin: 0 0 24px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .panel-title i {
            color: var(--color-primary);
        }

        /* Table Styles */
        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            text-align: left;
        }

        .modern-table th {
            padding: 12px 16px;
            font-weight: 600;
            color: var(--color-slate-500);
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .modern-table td {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .modern-table tr:last-child td {
            border-bottom: none;
        }

        .modern-table tr:hover td {
            background-color: var(--color-slate-50);
        }

        .company-name {
            font-weight: 700;
            color: var(--color-slate-900);
            font-size: 1rem;
            margin-bottom: 4px;
        }

        .company-meta {
            font-size: 0.8rem;
            color: var(--color-slate-500);
        }

        .badge {
            padding: 6px 12px;
            border-radius: var(--radius-element);
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
        }

        .badge-active {
            background-color: rgba(16, 185, 129, 0.1);
            color: #059669;
        }

        .badge-suspended {
            background-color: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: var(--radius-pill);
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            border: none;
            font-family: var(--font-main);
        }

        .btn-primary {
            background-color: var(--color-primary);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .btn-primary:hover {
            background-color: var(--color-primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
        }

        .btn-secondary {
            background-color: var(--color-slate-50);
            color: var(--color-slate-900);
            border: 1px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background-color: var(--color-slate-100);
            border-color: #cbd5e1;
            transform: translateY(-2px);
        }

        .btn-ghost {
            background: transparent;
            color: var(--color-primary);
            padding: 8px 16px;
            border-radius: var(--radius-element);
            font-size: 0.85rem;
        }

        .btn-ghost:hover {
            background-color: rgba(79, 70, 229, 0.05);
        }

        /* Actions Panel */
        .actions-panel {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .resource-monitor {
            margin-top: 16px;
            padding: 20px;
            border-radius: var(--radius-element);
            background-color: var(--color-slate-50);
            border: var(--border-soft);
        }

        .progress-bar-bg {
            background: #e2e8f0;
            border-radius: var(--radius-pill);
            height: 8px;
            overflow: hidden;
            margin: 12px 0 8px 0;
        }

        .progress-bar-fill {
            background: var(--color-primary);
            width: 35%;
            /* Representing 1.4/4 */
            height: 100%;
            border-radius: var(--radius-pill);
        }
    </style>

    <div class="saas-dashboard">
        <div class="header-actions">
            <div>
                <h1 class="page-title">Painel Administrativo Global (SaaS)</h1>
                <p class="page-subtitle">Controle de assinatura, infraestrutura e monitoramento da plataforma TalentiPro.
                </p>
            </div>

            <div>
                <span class="status-badge">
                    <i class="fa-solid fa-check-circle"></i> Sistema Online
                </span>
            </div>
        </div>

        {{-- CARDS DE INDICADORES (KPIs) --}}
        <div class="kpi-grid">
            <!-- Card 1 -->
            <div class="kpi-card">
                <div class="kpi-icon" style="background: rgba(79, 70, 229, 0.1); color: var(--color-primary);">
                    <i class="fa-solid fa-building"></i>
                </div>
                <div>
                    <div class="kpi-label">Empresas (Tenants)</div>
                    <div class="kpi-value">{{ $totalEmpresas }}</div>
                </div>
            </div>

            <!-- Card 2 (Consultores) -->
            <div class="kpi-card" onclick="window.location='{{ route('admin.consultores.index') }}'"
                style="cursor: pointer;" title="Gerenciar Consultores Multi-Empresa">
                <div class="kpi-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <div>
                    <div class="kpi-label">Consultores</div>


                    <div class="kpi-value">{{ $totalConsultores ?? 0 }}</div>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="kpi-card">
                <div class="kpi-icon" style="background: rgba(16, 185, 129, 0.1); color: #059669;">
                    <i class="fa-solid fa-circle-nodes"></i>
                </div>
                <div>
                    <div class="kpi-label">Filas / Redis</div>
                    <div class="kpi-value" style="font-size: 1.4rem; padding-top: 6px;">Ativo</div>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="kpi-card">
                <div class="kpi-icon" style="background: rgba(245, 158, 11, 0.1); color: #d97706;">
                    <i class="fa-solid fa-microchip"></i>
                </div>
                <div>
                    <div class="kpi-label">Reqs Gemini IA</div>
                    <div class="kpi-value">148 <span
                            style="font-size: 1rem; color: var(--color-slate-500); font-weight: 600;">/ dia</span></div>
                </div>
            </div>
        </div>

        {{-- AREA DE CONTEÚDO EM DUAS COLUNAS --}}
        <div class="content-grid">

            <!-- Coluna Esquerda: Lista de Tenants (Empresas Cadastradas) -->
            <div class="card-panel">
                <h2 class="panel-title">
                    <i class="fa-solid fa-building"></i> Empresas Clientes (Tenants)
                </h2>

                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th>CNPJ</th>
                                <th>Limite Users</th>
                                <th>Status</th>
                                <th style="text-align: right;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($empresas as $empresa)
                                <tr>
                                    <td>
                                        <div class="company-name">{{ $empresa->nome }}</div>
                                        <div class="company-meta">Contratação:
                                            {{ $empresa->data_contratacao ? \Carbon\Carbon::parse($empresa->data_contratacao)->format('d/m/Y') : 'N/A' }}
                                        </div>
                                    </td>
                                    <td style="color: var(--color-slate-900); font-size: 0.95rem;">{{ $empresa->cnpj }}</td>
                                    <td style="color: var(--color-slate-900); font-weight: 500;">{{ $empresa->max_usuarios }}
                                    </td>
                                    <td>
                                        @if($empresa->status->value === 'ativa')
                                            <span class="badge badge-active"><i class="fa-solid fa-check"
                                                    style="margin-right: 4px;"></i> Ativa</span>
                                        @else
                                            <span class="badge badge-suspended"><i class="fa-solid fa-xmark"
                                                    style="margin-right: 4px;"></i> Suspensa</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="{{ route('admin.empresas.edit', $empresa) }}" class="btn btn-ghost"
                                            title="Acessar painel da empresa">
                                            <i class="fa-solid fa-sliders"></i> Gerenciar Tenant
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="padding: 48px 24px; text-align: center;">
                                        <div style="color: var(--color-slate-500); font-size: 1rem;">
                                            <i class="fa-regular fa-folder-open"
                                                style="font-size: 2rem; margin-bottom: 12px; display: block; color: #cbd5e1;"></i>
                                            Nenhuma empresa cliente cadastrada no momento.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Coluna Direita: Atalhos SaaS -->
            <div class="card-panel">
                <h2 class="panel-title">
                    <i class="fa-solid fa-bolt"></i> Ações Administrativas
                </h2>

                <div class="actions-panel">
                    <a href="{{ route('admin.empresas.create') }}" class="btn btn-primary" style="width: 100%;">
                        <i class="fa-solid fa-plus"></i> Cadastrar Novo Tenant
                    </a>

                    <button class="btn btn-secondary" style="width: 100%;">
                        <i class="fa-solid fa-receipt"></i> Histórico de Faturamento
                    </button>

                    <button class="btn btn-secondary" style="width: 100%;">
                        <i class="fa-solid fa-microchip"></i> Configurar IA (Gemini)
                    </button>
                </div>

                <div class="resource-monitor">
                    <h4
                        style="font-size: 0.9rem; font-weight: 700; color: var(--color-slate-900); margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-server" style="color: var(--color-slate-500);"></i> Recursos de Servidor
                    </h4>
                    <p style="font-size: 0.8rem; color: var(--color-slate-500); margin: 0;">Uso de memória e serviços
                        essenciais (Containers)</p>

                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill"></div>
                    </div>

                    <div
                        style="display: flex; justify-content: space-between; font-size: 0.75rem; font-weight: 600; color: var(--color-slate-500);">
                        <span>1.4 GB Usados</span>
                        <span>4.0 GB Total</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection