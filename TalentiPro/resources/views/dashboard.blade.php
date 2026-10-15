@extends('layouts.app')

@section('title', 'Dashboard')
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

        .dashboard-kpi-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 24px;
            margin-bottom: 40px;
        }

        @media (min-width: 768px) {
            .dashboard-kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .dashboard-kpi-grid {
                grid-template-columns: repeat(3, 1fr);
            }
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
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .kpi-card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-4px);
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
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--color-slate-900);
            line-height: 1.2;
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

        /* Task Items */
        .dashboard-task-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            background: #ffffff;
            border: var(--border-soft);
            border-radius: 12px;
            transition: all 0.3s ease;
            margin-bottom: 12px;
        }

        .dashboard-task-item:hover {
            box-shadow: var(--shadow-card);
            transform: translateY(-2px);
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

        .btn-sm {
            padding: 8px 16px;
            font-size: 0.85rem;
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

        /* Modals */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background-color: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 16px;
            animation: fadeIn 0.3s ease;
            box-sizing: border-box;
        }

        .modal-card {
            width: 650px !important;
            max-width: 100% !important;
            max-height: calc(100dvh - 32px);
            display: flex;
            flex-direction: column;
            padding: 0;
            position: relative;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border-radius: var(--radius-card);
            background: #ffffff;
            border: var(--border-soft);
            overflow: hidden;
            animation: slideDown 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            box-sizing: border-box;
            margin: 0 auto;
            min-width: 0;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            background: white;
            box-sizing: border-box;
            width: 100%;
            flex-shrink: 0;
        }

        .modal-close {
            background: #f7fafc;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--color-slate-500);
            cursor: pointer;
            transition: all 0.2s;
        }

        .modal-close:hover {
            background: #e2e8f0;
            color: var(--color-slate-900);
        }

        /* Animation Keyframes */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>

    <div class="saas-dashboard">
        <div class="header-actions">
            <div>
                <h1 class="page-title">Bem-vindo, {{ auth()->user()->name }}!</h1>
                <p class="page-subtitle">
                    Painel Corporativo — <strong
                        style="color: var(--color-primary);">{{ auth()->user()->empresa->nome ?? 'Empresa Não Vinculada' }}</strong>
                </p>
            </div>
        </div>

        {{-- CARDS DE INDICADORES (KPIs) --}}
        @if(in_array(auth()->user()->perfil_acesso->value, ['master', 'consultor']))
            <div class="dashboard-kpi-grid">
                <!-- Card 1 (Pesquisa de Clima) -->
                <div class="kpi-card" onclick="window.location='{{ route('gestao.clima.index') }}'">
                    <div class="kpi-icon" style="background: rgba(16, 185, 129, 0.1); color: #059669;">
                        <i class="fa-solid fa-cloud-sun"></i>
                    </div>
                    <div>
                        <div class="kpi-label">Pesquisa de Clima</div>
                        <div class="kpi-value">Acessar Módulo</div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="kpi-card" onclick="openModalAvaliacoesAtivas()">
                    <div class="kpi-icon" style="background: rgba(39, 174, 96, 0.1); color: #27ae60;">
                        <i class="fa-solid fa-star-half-stroke"></i>
                    </div>
                    <div>
                        <div class="kpi-label">Avaliações Ativas</div>
                        <div class="kpi-value">{{ $avaliacoesAtivas }}</div>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="kpi-card" onclick="openModalPdisPendentes()">
                    <div class="kpi-icon" style="background: rgba(245, 158, 11, 0.1); color: #d97706;">
                        <i class="fa-solid fa-bullseye"></i>
                    </div>
                    <div>
                        <div class="kpi-label">PDIs Pendentes</div>
                        <div class="kpi-value">{{ $pdisPendentes }}</div>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="kpi-card" onclick="openModalTarefasPendentes()">
                    <div class="kpi-icon" style="background: rgba(79, 70, 229, 0.1); color: var(--color-primary);">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <div>
                        <div class="kpi-label">Tarefas Pendentes</div>
                        <div class="kpi-value">
                            {{ $tarefasPendentes->count() + $meusPdisPendentes->count() }}
                        </div>
                    </div>
                </div>

                <!-- Card 5 (Créditos IA) -->
                @php
                    $empresaIa = auth()->user()->empresa;
                @endphp
                @if($empresaIa && $empresaIa->ia_ativa)
                    <div class="kpi-card">
                        <div class="kpi-icon" style="background: rgba(109, 40, 217, 0.1); color: var(--color-accent);">
                            <i class="fa-solid fa-robot"></i>
                        </div>
                        <div>
                            <div class="kpi-label">Transcrições IA (Mês)</div>
                            <div class="kpi-value">
                                {{ $empresaIa->ia_limite_transcricoes_mes > 0
                        ? max(0, $empresaIa->ia_limite_transcricoes_mes - $empresaIa->ia_transcricoes_utilizadas)
                        : '∞' }}
                                <span style="font-size: 0.8rem; color: var(--color-slate-500); font-weight: 600;">restantes</span>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Card 6 (Histórico) -->
                <div class="kpi-card" onclick="openModalAndamento()">
                    <div class="kpi-icon" style="background: rgba(236, 72, 153, 0.1); color: #ec4899;">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div>
                        <div class="kpi-label">Histórico</div>
                        <div
                            style="font-size: 1rem; color: var(--color-slate-500); font-weight: 600; line-height: 1.2; margin-top: 5px;">
                            Ver detalhes
                        </div>
                    </div>
                </div>

                @if(auth()->user()->perfil_acesso->value === 'master')
                    <!-- Card 7 (Criar PDI) -->
                    <div class="kpi-card" onclick="window.location='{{ route('pdis.create') }}'">
                        <div class="kpi-icon" style="background: rgba(51, 65, 85, 0.1); color: #334155;">
                            <i class="fa-solid fa-plus-circle"></i>
                        </div>
                        <div>
                            <div class="kpi-label">Ação Rápida</div>
                            <div class="kpi-value">Criar PDI</div>
                        </div>
                    </div>

                    <!-- Card 8 (Criar Competência) -->
                    <div class="kpi-card" onclick="window.location='{{ route('competencias.index', ['action' => 'create']) }}'">
                        <div class="kpi-icon" style="background: rgba(234, 88, 12, 0.1); color: #ea580c;">
                            <i class="fa-solid fa-brain"></i>
                        </div>
                        <div>
                            <div class="kpi-label">Ação Rápida</div>
                            <div class="kpi-value">Nova Competência</div>
                        </div>
                    </div>

                    <!-- Card 9 (Criar Avaliação) -->
                    <div class="kpi-card" onclick="window.location='{{ route('avaliacoes.create') }}'">
                        <div class="kpi-icon" style="background: rgba(13, 148, 136, 0.1); color: #0d9488;">
                            <i class="fa-solid fa-file-circle-plus"></i>
                        </div>
                        <div>
                            <div class="kpi-label">Ação Rápida</div>
                            <div class="kpi-value">Nova Avaliação</div>
                        </div>
                    </div>
                @elseif(auth()->user()->isConsultor())
                    <!-- Card 7 Consultor (Relatórios de Avaliação) -->
                    <div class="kpi-card" onclick="window.location='{{ route('avaliacoes.relatorios') }}'">
                        <div class="kpi-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                            <i class="fa-solid fa-chart-column"></i>
                        </div>
                        <div>
                            <div class="kpi-label">Análise de Desempenho</div>
                            <div class="kpi-value" style="font-size: 1.25rem;">Relatórios da Equipe</div>
                        </div>
                    </div>

                    <!-- Card 8 Consultor (Mapa DISC Equipe) -->
                    <div class="kpi-card" onclick="window.location='{{ route('disc.equipe') }}'">
                        <div class="kpi-icon" style="background: rgba(168, 85, 247, 0.1); color: #a855f7;">
                            <i class="fa-solid fa-puzzle-piece"></i>
                        </div>
                        <div>
                            <div class="kpi-label">Perfil Comportamental</div>
                            <div class="kpi-value" style="font-size: 1.25rem;">Mapa DISC Equipe</div>
                        </div>
                    </div>

                    <!-- Card 9 Consultor (Reuniões Feedback) -->
                    <div class="kpi-card" onclick="window.location='{{ route('reunioes.index') }}'">
                        <div class="kpi-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                            <i class="fa-solid fa-headset"></i>
                        </div>
                        <div>
                            <div class="kpi-label">Comunicação & IA</div>
                            <div class="kpi-value" style="font-size: 1.25rem;">Reuniões 1:1</div>
                        </div>
                    </div>
                @endif
            </div>
            </div>
        @elseif(auth()->user()->perfil_acesso->value === 'gestor')
            <div class="dashboard-kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                <!-- Avaliar Colab -->
                <div class="kpi-card" onclick="openModalTarefasPendentes()">
                    <div class="kpi-icon" style="background: rgba(79, 70, 229, 0.1); color: var(--color-primary);">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <div>
                        <div class="kpi-label">Ação Rápida</div>
                        <div class="kpi-value" style="font-size: 1.25rem;">Avaliar Colaboradores</div>
                    </div>
                </div>

                <!-- Acompanhar PDI -->
                <div class="kpi-card" onclick="window.location='{{ route('pdis.index') }}'">
                    <div class="kpi-icon" style="background: rgba(245, 158, 11, 0.1); color: #d97706;">
                        <i class="fa-solid fa-bullseye"></i>
                    </div>
                    <div>
                        <div class="kpi-label">Ação Rápida</div>
                        <div class="kpi-value" style="font-size: 1.25rem;">Acompanhar PDIs</div>
                    </div>
                </div>

                <!-- Acompanhar Feedbacks -->
                <div class="kpi-card" onclick="window.location='{{ route('reunioes.index') }}'">
                    <div class="kpi-icon" style="background: rgba(16, 185, 129, 0.1); color: #059669;">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <div>
                        <div class="kpi-label">Ação Rápida</div>
                        <div class="kpi-value" style="font-size: 1.25rem;">Acompanhar Feedbacks</div>
                    </div>
                </div>
            </div>
        @endif

        {{-- AREA DE CONTEÚDO EM DUAS COLUNAS (Colaborador / Gestor) --}}
        @if(!in_array(auth()->user()->perfil_acesso->value, ['master', 'consultor']))
            <div class="content-grid">

                <!-- Coluna Esquerda -->
                <div class="card-panel">
                    {{-- MINHAS TAREFAS PENDENTES --}}
                    <h2 class="panel-title">
                        <i class="fa-solid fa-list-check" style="color: var(--color-primary);"></i> Minhas Tarefas Pendentes
                        @if($tarefasPendentes->count() > 0 || $meusPdisPendentes->count() > 0)
                            <span
                                style="background: #ef4444; color: white; font-size: 0.75rem; padding: 2px 8px; border-radius: 12px; font-weight: bold; display: inline-flex; align-items: center; justify-content: center;">
                                {{ $tarefasPendentes->count() + $meusPdisPendentes->count() }}
                            </span>
                        @endif
                    </h2>

                    @if($tarefasPendentes->count() > 0 || $meusPdisPendentes->count() > 0)
                        <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px;">

                            {{-- Avaliações Pendentes --}}
                            @foreach($tarefasPendentes as $tarefa)
                                <div class="dashboard-task-item">
                                    <div style="display: flex; gap: 15px; align-items: center;">
                                        <div class="kpi-icon"
                                            style="background: rgba(245, 158, 11, 0.1); color: #d97706; width: 45px; height: 45px; font-size: 1.2rem;">
                                            <i class="fa-solid fa-star-half-stroke"></i>
                                        </div>
                                        <div>
                                            <h4
                                                style="font-size: 0.95rem; font-weight: 700; color: var(--color-slate-900); margin: 0 0 2px 0;">
                                                Avaliar: {{ $tarefa->avaliado->name }}</h4>
                                            <p style="font-size: 0.85rem; color: var(--color-slate-500); margin: 0 0 4px 0;">
                                                {{ $tarefa->avaliacao->titulo }} ({{ ucfirst($tarefa->tipo->value) }})</p>
                                            <span style="font-size: 0.75rem; color: #ef4444; font-weight: 600;"><i
                                                    class="fa-regular fa-clock"></i> Pendente</span>
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 10px; align-items: center;">

                                        <a href="{{ route('avaliacoes.responder', $tarefa->id) }}" class="btn btn-primary btn-sm">
                                            Responder Agora
                                        </a>
                                    </div>
                                </div>
                            @endforeach

                            {{-- PDIs Pendentes --}}
                            @foreach($meusPdisPendentes as $pdi)
                                <div class="dashboard-task-item">
                                    <div style="display: flex; gap: 15px; align-items: center;">
                                        <div class="kpi-icon"
                                            style="background: rgba(109, 40, 217, 0.1); color: var(--color-accent); width: 45px; height: 45px; font-size: 1.2rem;">
                                            <i class="fa-solid fa-bullseye"></i>
                                        </div>
                                        <div>
                                            <h4
                                                style="font-size: 0.95rem; font-weight: 700; color: var(--color-slate-900); margin: 0 0 2px 0;">
                                                PDI: {{ $pdi->colaborador->name }}</h4>
                                            <p style="font-size: 0.85rem; color: var(--color-slate-500); margin: 0 0 4px 0;">
                                                {{ $pdi->titulo }}</p>
                                            <span style="font-size: 0.75rem; color: #ef4444; font-weight: 600;">
                                                <i class="fa-solid fa-bars-progress"></i>
                                                {{ $pdi->status->value == 'nao_iniciado' ? 'Não Iniciado' : 'Em Andamento' }}
                                                ({{ $pdi->progresso }}%)
                                            </span>
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 10px; align-items: center;">

                                        <a href="{{ route('pdis.show', $pdi->id) }}" class="btn btn-primary btn-sm"
                                            style="background-color: var(--color-accent);">
                                            Acompanhar PDI
                                        </a>
                                    </div>
                                </div>
                            @endforeach

                        </div>
                    @else
                        <div
                            style="text-align: center; padding: 40px 20px; background: var(--color-slate-50); border: 1px dashed #cbd5e1; border-radius: var(--radius-card); color: var(--color-slate-500); margin-bottom: 20px;">
                            <i class="fa-solid fa-mug-hot" style="font-size: 2.5rem; margin-bottom: 15px; color: #cbd5e1;"></i>
                            <p style="margin: 0; font-size: 0.95rem; font-weight: 500;">Tudo limpo por aqui! Você não tem avaliações
                                ou PDIs pendentes no momento.</p>
                        </div>
                    @endif
                </div>

                <!-- Coluna Direita: Ações Rápidas -->
                <div class="card-panel" style="display: flex; flex-direction: column; gap: 16px;">
                    <h2 class="panel-title" style="margin-bottom: 8px;">
                        <i class="fa-solid fa-bolt" style="color: var(--color-primary);"></i> Atalhos Rápidos
                    </h2>

                    <a href="{{ route('avaliacoes.minhas') }}" class="btn btn-primary" style="width: 100%;">
                        <i class="fa-solid fa-file-signature"></i> Minhas Avaliações
                    </a>

                    <a href="{{ route('pdis.index', ['tab' => 'meus']) }}" class="btn btn-secondary" style="width: 100%;">
                        <i class="fa-solid fa-route" style="color: var(--color-accent);"></i> Acessar Meu PDI
                    </a>
                </div>
            </div>
        @endif
    </div> <!-- Fim saas-dashboard -->
@endsection

@push('scripts')
    {{-- Modais Master/Gestor/Consultor --}}
    @if(in_array(auth()->user()->perfil_acesso->value, ['master', 'gestor', 'consultor']))
        {{-- Modal de Avaliações Ativas --}}
        <div id="modalAvaliacoesAtivas" class="modal-overlay"
            style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; width: 100vw !important; height: 100dvh !important; padding: 16px !important; box-sizing: border-box !important; display: none; align-items: center !important; justify-content: center !important; z-index: 9999 !important;">
            <div class="modal-card"
                style="max-height: calc(100dvh - 32px) !important; height: auto !important; display: flex !important; flex-direction: column !important; overflow: hidden !important; box-sizing: border-box !important; width: 650px !important; max-width: 100% !important;">
                <div class="modal-header" style="flex-shrink: 0 !important;">
                    <h3
                        style="font-size: 1.35rem; font-weight: 800; color: var(--color-slate-900); margin: 0; display: flex; align-items: center; gap: 12px;">
                        <div
                            style="background: rgba(39, 174, 96, 0.1); width: 36px; height: 36px; border-radius: var(--radius-element); display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-star-half-stroke" style="color: #27ae60; font-size: 1.1rem;"></i>
                        </div>
                        Avaliações Ativas
                    </h3>
                    <button type="button" onclick="document.getElementById('modalAvaliacoesAtivas').style.display='none'"
                        class="modal-close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div id="modalAvaliacoesContent"
                    style="overflow-y: auto !important; padding: 20px 24px; flex: 1 1 auto !important; min-height: 0 !important; background: var(--color-slate-50); box-sizing: border-box;">
                    <div style="text-align: center; color: var(--color-slate-500); padding: 40px 0;">
                        <i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem;"></i>
                        <p style="margin-top: 12px; font-weight: 500;">Carregando avaliações...</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal de PDIs Pendentes --}}
        <div id="modalPdisPendentes" class="modal-overlay"
            style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; width: 100vw !important; height: 100dvh !important; padding: 16px !important; box-sizing: border-box !important; display: none; align-items: center !important; justify-content: center !important; z-index: 9999 !important;">
            <div class="modal-card"
                style="max-height: calc(100dvh - 32px) !important; height: auto !important; display: flex !important; flex-direction: column !important; overflow: hidden !important; box-sizing: border-box !important; width: 650px !important; max-width: 100% !important;">
                <div class="modal-header" style="flex-shrink: 0 !important;">
                    <h3
                        style="font-size: 1.35rem; font-weight: 800; color: var(--color-slate-900); margin: 0; display: flex; align-items: center; gap: 12px;">
                        <div
                            style="background: rgba(245, 158, 11, 0.1); width: 36px; height: 36px; border-radius: var(--radius-element); display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-bullseye" style="color: #d97706; font-size: 1.1rem;"></i>
                        </div>
                        PDIs Pendentes
                    </h3>
                    <button type="button" onclick="document.getElementById('modalPdisPendentes').style.display='none'"
                        class="modal-close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div id="modalPdisContent"
                    style="overflow-y: auto !important; padding: 20px 24px; flex: 1 1 auto !important; min-height: 0 !important; background: var(--color-slate-50); box-sizing: border-box;">
                    <div style="text-align: center; color: var(--color-slate-500); padding: 40px 0;">
                        <i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem;"></i>
                        <p style="margin-top: 12px; font-weight: 500;">Carregando PDIs...</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal de Andamento das Campanhas --}}
        <div id="modalAndamento" class="modal-overlay"
            style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; width: 100vw !important; height: 100dvh !important; padding: 16px !important; box-sizing: border-box !important; display: none; align-items: center !important; justify-content: center !important; z-index: 9999 !important;">
            <div class="modal-card"
                style="max-height: calc(100dvh - 32px) !important; height: auto !important; display: flex !important; flex-direction: column !important; overflow: hidden !important; box-sizing: border-box !important; width: 650px !important; max-width: 100% !important;">
                <div class="modal-header" style="flex-shrink: 0 !important;">
                    <h3
                        style="font-size: 1.35rem; font-weight: 800; color: var(--color-slate-900); margin: 0; display: flex; align-items: center; gap: 12px;">
                        <div
                            style="background: rgba(236, 72, 153, 0.1); width: 36px; height: 36px; border-radius: var(--radius-element); display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-chart-line" style="color: #ec4899; font-size: 1.1rem;"></i>
                        </div>
                        Andamento das Campanhas
                    </h3>
                    <button type="button" onclick="document.getElementById('modalAndamento').style.display='none'"
                        class="modal-close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div
                    style="overflow-y: auto !important; padding: 20px 24px; flex: 1 1 auto !important; min-height: 0 !important; background: var(--color-slate-50); display: flex; flex-direction: column; gap: 12px; box-sizing: border-box;">
                    {{-- Avaliações --}}
                    @foreach($avaliacoesAtivasList as $avAtiva)
                        @php
                            $terminaram = $avAtiva->participantes->where('status', 'respondido')->unique('avaliado_id')->count();
                        @endphp
                        <a href="{{ route('avaliacoes.show', $avAtiva->id) }}" class="dashboard-task-item"
                            style="text-decoration: none;">
                            <div
                                style="font-size: 1rem; font-weight: 700; color: var(--color-accent); display: flex; align-items: center; gap: 10px; flex: 1;">
                                <i class="fa-solid fa-star-half-stroke"></i>
                                <span>Avaliações: {{ $avAtiva->titulo }}</span>
                            </div>
                            <div style="font-size: 0.85rem; color: var(--color-slate-500); text-align: right;">
                                <strong
                                    style="color: var(--color-slate-900); font-size: 1rem;">{{ $terminaram }}</strong>&nbsp;Respostas
                            </div>
                        </a>
                    @endforeach

                    {{-- Pesquisa de Clima --}}
                    @foreach($climasAtivosList as $climaAtivo)
                        @php
                            $terminaramClima = $climaAtivo->participacoes->where('status', '!=', 'pendente')->count();
                        @endphp
                        <a href="{{ route('gestao.clima.relatorio', $climaAtivo->id) }}" class="dashboard-task-item"
                            style="text-decoration: none;">
                            <div
                                style="font-size: 1rem; font-weight: 700; color: #059669; display: flex; align-items: center; gap: 10px; flex: 1;">
                                <i class="fa-solid fa-cloud-sun"></i>
                                <span>{{ $climaAtivo->titulo }}</span>
                            </div>
                            <div style="font-size: 0.85rem; color: var(--color-slate-500); text-align: right;">
                                <strong
                                    style="color: var(--color-slate-900); font-size: 1rem;">{{ $terminaramClima }}</strong>&nbsp;Respostas
                            </div>
                        </a>
                    @endforeach

                    {{-- PDIs --}}
                    <a href="{{ route('pdis.index') }}" class="dashboard-task-item" style="text-decoration: none;">
                        <div
                            style="font-size: 1rem; font-weight: 700; color: #d97706; display: flex; align-items: center; gap: 10px; flex: 1;">
                            <i class="fa-solid fa-bullseye"></i>
                            <span>Planos de Desenvolvimento (PDI)</span>
                        </div>
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 6px;">
                            <div style="font-size: 0.85rem; color: var(--color-slate-500); text-align: right;">
                                <strong style="color: var(--color-slate-900); font-size: 1rem;">{{ $pdisConcluidos }}</strong>&nbsp;Finalizadas&nbsp;e&nbsp;<strong style="color: var(--color-slate-900); font-size: 1rem;">{{ $pdisEmAndamentoTotal }}</strong>&nbsp;em Andamento
                            </div>
                            <div style="background: rgba(217, 119, 6, 0.1); color: #d97706; padding: 4px 12px; border-radius: 12px; font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                                Acessar PDIs <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Minhas Tarefas Pendentes (Para Master/Gestor) --}}
    @if(in_array(auth()->user()->perfil_acesso->value, ['master', 'gestor']))
        <div id="modalTarefasPendentes" class="modal-overlay"
            style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; width: 100vw !important; height: 100dvh !important; padding: 16px !important; box-sizing: border-box !important; display: none; align-items: center !important; justify-content: center !important; z-index: 9999 !important;">
            <div class="modal-card"
                style="max-height: calc(100dvh - 32px) !important; height: auto !important; display: flex !important; flex-direction: column !important; overflow: hidden !important; box-sizing: border-box !important; width: 650px !important; max-width: 100% !important;">
                <div class="modal-header" style="flex-shrink: 0 !important;">
                    <h3
                        style="font-size: 1.35rem; font-weight: 800; color: var(--color-slate-900); margin: 0; display: flex; align-items: center; gap: 12px;">
                        <div
                            style="background: rgba(79, 70, 229, 0.1); width: 36px; height: 36px; border-radius: var(--radius-element); display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-list-check" style="color: var(--color-primary); font-size: 1.1rem;"></i>
                        </div>
                        Minhas Tarefas Pendentes
                        @if($tarefasPendentes->count() > 0 || $meusPdisPendentes->count() > 0)
                            <span
                                style="background: #ef4444; color: white; font-size: 0.75rem; padding: 2px 8px; border-radius: 12px; font-weight: bold;">
                                {{ $tarefasPendentes->count() + $meusPdisPendentes->count() }}
                            </span>
                        @endif
                    </h3>
                    <button type="button" onclick="document.getElementById('modalTarefasPendentes').style.display='none'"
                        class="modal-close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div id="modalTarefasContent"
                    style="overflow-y: auto !important; padding: 20px 24px; flex: 1 1 auto !important; min-height: 0 !important; background: var(--color-slate-50); box-sizing: border-box;">
                    @if($tarefasPendentes->count() > 0 || $meusPdisPendentes->count() > 0)
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            {{-- Avaliações Pendentes --}}
                            @foreach($tarefasPendentes as $tarefa)
                                <div class="dashboard-task-item">
                                    <div class="kpi-icon"
                                        style="background: rgba(245, 158, 11, 0.1); color: #d97706; width: 45px; height: 45px; font-size: 1.2rem;">
                                        <i class="fa-solid fa-star-half-stroke"></i>
                                    </div>
                                    <div style="flex: 1; padding: 0 16px;">
                                        <h4
                                            style="font-size: 0.95rem; font-weight: 700; color: var(--color-slate-900); margin: 0 0 2px 0;">
                                            Avaliar: {{ $tarefa->avaliado->name }}</h4>
                                        <p style="font-size: 0.85rem; color: var(--color-slate-500); margin: 0;">
                                            {{ $tarefa->avaliacao->titulo }}</p>
                                    </div>
                                    <div>
                                        <a href="{{ route('avaliacoes.responder', $tarefa->id) }}" class="btn btn-primary btn-sm">
                                            Responder
                                        </a>
                                    </div>
                                </div>
                            @endforeach

                            {{-- PDIs Pendentes --}}
                            @foreach($meusPdisPendentes as $pdi)
                                <div class="dashboard-task-item">
                                    <div class="kpi-icon"
                                        style="background: rgba(109, 40, 217, 0.1); color: var(--color-accent); width: 45px; height: 45px; font-size: 1.2rem;">
                                        <i class="fa-solid fa-bullseye"></i>
                                    </div>
                                    <div style="flex: 1; padding: 0 16px;">
                                        <h4
                                            style="font-size: 0.95rem; font-weight: 700; color: var(--color-slate-900); margin: 0 0 2px 0;">
                                            PDI: {{ $pdi->colaborador->name }}</h4>
                                        <p style="font-size: 0.85rem; color: var(--color-slate-500); margin: 0;">{{ $pdi->titulo }}</p>
                                    </div>
                                    <div>
                                        <a href="{{ route('pdis.show', $pdi->id) }}" class="btn btn-primary btn-sm"
                                            style="background-color: var(--color-accent);">
                                            Acompanhar
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="text-align: center; color: var(--color-slate-500); padding: 40px 0;">
                            <i class="fa-solid fa-mug-hot" style="font-size: 2.5rem; margin-bottom: 15px; color: #cbd5e1;"></i>
                            <p style="margin: 0; font-weight: 500;">Tudo limpo por aqui! Você não tem tarefas pendentes.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Agendar Lembrete --}}
    <div id="modalLembrete" class="modal-overlay"
        style="box-sizing: border-box; padding: 16px; align-items: center; justify-content: center;">
        <div class="modal-card"
            style="max-width: 450px; max-height: calc(100dvh - 32px); display: flex; flex-direction: column; overflow: hidden;">
            <form action="{{ route('lembretes.store') }}" method="POST">
                @csrf
                <input type="hidden" name="lembreteable_type" id="lembreteable_type">
                <input type="hidden" name="lembreteable_id" id="lembreteable_id">

                <div class="modal-header">
                    <h3
                        style="font-size: 1.25rem; font-weight: 800; color: var(--color-slate-900); margin: 0; display: flex; align-items: center; gap: 12px;">
                        <div
                            style="background: rgba(100, 116, 139, 0.1); width: 36px; height: 36px; border-radius: var(--radius-element); display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-bell" style="color: var(--color-slate-500); font-size: 1.1rem;"></i>
                        </div>
                        Lembrar-me Depois
                    </h3>
                    <button type="button" onclick="document.getElementById('modalLembrete').style.display='none'"
                        class="modal-close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div style="padding: 24px 32px; background: var(--color-slate-50);">
                    <div style="margin-bottom: 20px;">
                        <label
                            style="display: block; font-size: 0.9rem; font-weight: 700; color: var(--color-slate-900); margin-bottom: 8px;">Quando
                            deseja ser lembrado?</label>
                        <select name="dias"
                            style="width: 100%; padding: 12px 16px; border-radius: var(--radius-element); border: var(--border-soft); background: white; font-size: 0.95rem; color: var(--color-slate-900); outline: none; transition: all 0.2s; cursor: pointer; font-family: var(--font-main);"
                            onfocus="this.style.borderColor='var(--color-primary)'"
                            onblur="this.style.borderColor='#e9eaf0'">
                            <option value="1">Amanhã</option>
                            <option value="2">Em 2 dias</option>
                            <option value="3">Em 3 dias</option>
                            <option value="7">Em 1 semana</option>
                            <option value="15">Em 15 dias</option>
                        </select>
                    </div>

                    <div>
                        <label
                            style="display: block; font-size: 0.9rem; font-weight: 700; color: var(--color-slate-900); margin-bottom: 8px;">Observação
                            Opcional</label>
                        <input type="text" name="observacao" placeholder="Ex: Preciso finalizar essa avaliação"
                            value="Lembrete automático do sistema"
                            style="width: 100%; padding: 12px 16px; border-radius: var(--radius-element); border: var(--border-soft); background: white; font-size: 0.95rem; color: var(--color-slate-900); outline: none; transition: all 0.2s; font-family: var(--font-main);"
                            onfocus="this.style.borderColor='var(--color-primary)'"
                            onblur="this.style.borderColor='#e9eaf0'">
                    </div>
                </div>

                <div
                    style="padding: 20px 32px; background: white; border-top: 1px solid rgba(0,0,0,0.05); display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="button" onclick="document.getElementById('modalLembrete').style.display='none'"
                        class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Agendar Lembrete</button>
                </div>
            </form>
        </div> <!-- Fim modalLembrete wrapper -->

        <script>
            function fecharTodosOsModais() {
                document.querySelectorAll('.modal-overlay').forEach(modal => {
                    modal.style.display = 'none';
                });
            }

            function abrirModalLembrete(type, id) {
                fecharTodosOsModais();
                document.getElementById('lembreteable_type').value = type;
                document.getElementById('lembreteable_id').value = id;
                document.getElementById('modalLembrete').style.display = 'flex';
            }

            function openModalTarefasPendentes() {
                fecharTodosOsModais();
                document.getElementById('modalTarefasPendentes').style.display = 'flex';
            }

            function openModalAndamento() {
                fecharTodosOsModais();
                document.getElementById('modalAndamento').style.display = 'flex';
            }

            // Funções para abrir modais AJAX
            function openModalAvaliacoesAtivas() {
                fecharTodosOsModais();
                document.getElementById('modalAvaliacoesAtivas').style.display = 'flex';
                document.getElementById('modalAvaliacoesContent').innerHTML = '<div style="text-align: center; color: var(--color-slate-500); padding: 40px 0;"><i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem;"></i><p style="margin-top:12px;font-weight:500;">Carregando avaliações...</p></div>';

                fetch("{{ route('dashboard.api.avaliacoes-ativas') }}")
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('modalAvaliacoesContent').innerHTML = html;
                    })
                    .catch(error => {
                        document.getElementById('modalAvaliacoesContent').innerHTML = '<div style="text-align: center; color: #ef4444; padding: 20px;">Erro ao carregar dados.</div>';
                    });
            }

            function openModalPdisPendentes() {
                fecharTodosOsModais();
                document.getElementById('modalPdisPendentes').style.display = 'flex';
                document.getElementById('modalPdisContent').innerHTML = '<div style="text-align: center; color: var(--color-slate-500); padding: 40px 0;"><i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem;"></i><p style="margin-top:12px;font-weight:500;">Carregando PDIs...</p></div>';

                fetch("{{ route('dashboard.api.pdis-pendentes') }}")
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('modalPdisContent').innerHTML = html;
                    })
                    .catch(error => {
                        document.getElementById('modalPdisContent').innerHTML = '<div style="text-align: center; color: #ef4444; padding: 20px;">Erro ao carregar dados.</div>';
                    });
            }
        </script>
@endpush