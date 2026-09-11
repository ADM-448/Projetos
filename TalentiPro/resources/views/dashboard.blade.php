@extends('layouts.app')

@section('title', 'Dashboard')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
    <div class="header-actions"
        style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
        <div>
            <h1 class="page-title" style="margin-bottom: 5px;">Bem-vindo, {{ auth()->user()->name }}!</h1>
            <p style="color: #64748b; font-size: 0.95rem;">
                Painel Corporativo — <strong
                    style="color: #6a4c7c;">{{ auth()->user()->empresa->nome ?? 'Empresa Não Vinculada' }}</strong>
            </p>
        </div>


    </div>

    <style>
        .dashboard-kpi-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
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
    </style>

    {{-- CARDS DE INDICADORES (KPIs) --}}
    @if(in_array(auth()->user()->perfil_acesso->value, ['master', 'gestor']))
        <div class="dashboard-kpi-grid">
            <!-- Card 1 (Pesquisa de Clima - Substituindo Colaboradores) -->
            <div onclick="window.location='{{ route('gestao.clima.index') }}'"
                style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; border-left: 5px solid #00b894; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
                <div
                    style="background: rgba(0, 184, 148, 0.1); width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #00b894; font-size: 1.5rem;">
                    <i class="fa-solid fa-cloud-sun"></i>
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: #888; font-weight: 600; text-transform: uppercase;">Pesquisa de Clima
                    </div>
                    <div style="font-size: 1.3rem; font-weight: 800; color: #2d3748; line-height: 1.2;">Acessar Módulo
                    </div>
                </div>
            </div>

            <!-- Card 2 -->
            <div onclick="openModalAvaliacoesAtivas()"
                style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; border-left: 5px solid #27ae60; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
                <div
                    style="background: rgba(39, 174, 96, 0.1); width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #27ae60; font-size: 1.5rem;">
                    <i class="fa-solid fa-star-half-stroke"></i>
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: #888; font-weight: 600; text-transform: uppercase;">Avaliações Ativas
                    </div>
                    <div style="font-size: 1.8rem; font-weight: 800; color: #2d3748; line-height: 1.2;">{{ $avaliacoesAtivas }}
                    </div>
                </div>
            </div>

            <!-- Card 3 -->
            <div onclick="openModalPdisPendentes()"
                style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; border-left: 5px solid #f39c12; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
                <div
                    style="background: rgba(243, 156, 18, 0.1); width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #f39c12; font-size: 1.5rem;">
                    <i class="fa-solid fa-bullseye"></i>
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: #888; font-weight: 600; text-transform: uppercase;">PDIs Pendentes
                    </div>
                    <div style="font-size: 1.8rem; font-weight: 800; color: #2d3748; line-height: 1.2;">{{ $pdisPendentes }}
                    </div>
                </div>
            </div>

            <!-- Card 4 -->
            <div onclick="document.getElementById('modalTarefasPendentes').style.display='flex'"
                style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; border-left: 5px solid #2980b9; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
                <div
                    style="background: rgba(41, 128, 185, 0.1); width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #2980b9; font-size: 1.5rem;">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: #888; font-weight: 600; text-transform: uppercase;">Tarefas Pendentes
                    </div>
                    <div style="font-size: 1.8rem; font-weight: 800; color: #2d3748; line-height: 1.2;">
                        {{ $tarefasPendentes->count() + $meusPdisPendentes->count() }}</div>
                </div>
            </div>

            <!-- Card 5 (Créditos IA) -->
            @php
                $empresaIa = auth()->user()->empresa;
            @endphp
            @if($empresaIa && $empresaIa->ia_ativa)
            <div
                style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; border-left: 5px solid #9b59b6;">
                <div
                    style="background: rgba(155, 89, 182, 0.1); width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #9b59b6; font-size: 1.5rem;">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: #888; font-weight: 600; text-transform: uppercase;">Transcrições IA (Mês)
                    </div>
                    <div style="font-size: 1.8rem; font-weight: 800; color: #2d3748; line-height: 1.2;">
                        {{ $empresaIa->ia_limite_transcricoes_mes > 0 
                            ? max(0, $empresaIa->ia_limite_transcricoes_mes - $empresaIa->ia_transcricoes_utilizadas) 
                            : '∞' }}
                        <span style="font-size: 0.8rem; color: #888; font-weight: 600;">restantes</span>
                    </div>
                </div>
            </div>
            @endif
            <!-- Card 6 (Histórico) -->
            <div onclick="document.getElementById('modalAndamento').style.display='flex'"
                style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; border-left: 5px solid #e84393; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
                <div
                    style="background: rgba(232, 67, 147, 0.1); width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #e84393; font-size: 1.5rem;">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: #888; font-weight: 600; text-transform: uppercase;">Histórico</div>
                    <div style="font-size: 1rem; color: #888; font-weight: 600; line-height: 1.2; margin-top: 5px;">
                        Ver detalhes
                    </div>
                </div>
            </div>

            <!-- Card 7 (Criar PDI) -->
            <div onclick="window.location='{{ route('pdis.create') }}'"
                style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; border-left: 5px solid #34495e; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
                <div
                    style="background: rgba(52, 73, 94, 0.1); width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #34495e; font-size: 1.5rem;">
                    <i class="fa-solid fa-plus-circle"></i>
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: #888; font-weight: 600; text-transform: uppercase;">Ação Rápida</div>
                    <div style="font-size: 1.3rem; font-weight: 800; color: #2d3748; line-height: 1.2;">Criar PDI</div>
                </div>
            </div>

            <!-- Card 8 (Criar Competência) -->
            <div onclick="window.location='{{ route('competencias.index', ['action' => 'create']) }}'"
                style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; border-left: 5px solid #e67e22; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
                <div
                    style="background: rgba(230, 126, 34, 0.1); width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #e67e22; font-size: 1.5rem;">
                    <i class="fa-solid fa-brain"></i>
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: #888; font-weight: 600; text-transform: uppercase;">Ação Rápida</div>
                    <div style="font-size: 1.3rem; font-weight: 800; color: #2d3748; line-height: 1.2;">Nova Competência</div>
                </div>
            </div>

            <!-- Card 9 (Criar Avaliação) -->
            <div onclick="window.location='{{ route('avaliacoes.create') }}'"
                style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; border-left: 5px solid #16a085; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
                <div
                    style="background: rgba(22, 160, 133, 0.1); width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #16a085; font-size: 1.5rem;">
                    <i class="fa-solid fa-file-circle-plus"></i>
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: #888; font-weight: 600; text-transform: uppercase;">Ação Rápida</div>
                    <div style="font-size: 1.3rem; font-weight: 800; color: #2d3748; line-height: 1.2;">Nova Avaliação</div>
                </div>
            </div>
        </div>
    @endif

    {{-- AREA DE CONTEÚDO EM DUAS COLUNAS (Apenas para Colaboradores agora, pois Master/Gestor usam apenas os Cards) --}}
    @if(!in_array(auth()->user()->perfil_acesso->value, ['master', 'gestor']))
    <div class="dashboard-grid">

        <!-- Coluna Esquerda -->
        <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            {{-- MINHAS TAREFAS PENDENTES --}}
            <h2
                style="font-size: 1.25rem; font-weight: 700; color: #2d3748; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-list-check" style="color: #6a4c7c;"></i> Minhas Tarefas Pendentes
                @if($tarefasPendentes->count() > 0 || $meusPdisPendentes->count() > 0)
                    <span
                        style="background: #e53e3e; color: white; font-size: 0.75rem; padding: 2px 8px; border-radius: 12px; font-weight: bold;">{{ $tarefasPendentes->count() + $meusPdisPendentes->count() }}</span>
                @endif
            </h2>

            @if($tarefasPendentes->count() > 0 || $meusPdisPendentes->count() > 0)
                <div style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 40px;">

                    {{-- Avaliações Pendentes --}}
                    @foreach($tarefasPendentes as $tarefa)
                        <div class="dashboard-task-item"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.05)';"
                            onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)';">
                            <div style="display: flex; gap: 15px;">
                                <div
                                    style="background: rgba(243, 156, 18, 0.1); width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #f39c12; flex-shrink: 0;">
                                    <i class="fa-solid fa-star-half-stroke" style="font-size: 1.2rem;"></i>
                                </div>
                                <div>
                                    <h4 style="font-size: 0.95rem; font-weight: 700; color: #2d3748; margin-bottom: 2px;">Avaliar:
                                        {{ $tarefa->avaliado->name }}</h4>
                                    <p style="font-size: 0.85rem; color: #666; margin-bottom: 4px;">{{ $tarefa->avaliacao->titulo }}
                                        ({{ ucfirst($tarefa->tipo->value) }})</p>
                                    <span style="font-size: 0.75rem; color: #e53e3e; font-weight: 600;"><i
                                            class="fa-regular fa-clock"></i> Pendente</span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <button type="button" onclick="abrirModalLembrete('App\\Models\\AvaliacaoParticipante', {{ $tarefa->id }})" 
                                    style="padding: 8px 12px; font-size: 0.85rem; border-radius: 8px; background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; cursor: pointer; transition: all 0.2s;"
                                    onmouseover="this.style.background='#e2e8f0'; this.style.color='#475569';" onmouseout="this.style.background='#f1f5f9'; this.style.color='#64748b';">
                                    <i class="fa-solid fa-bell"></i>
                                </button>
                                <a href="{{ route('avaliacoes.responder', $tarefa->id) }}" class="btn-primary"
                                    style="padding: 8px 16px; font-size: 0.85rem; border-radius: 8px; background: linear-gradient(90deg, #f39c12 0%, #d35400 100%);">
                                    Responder Agora
                                </a>
                            </div>
                        </div>
                    @endforeach

                    {{-- PDIs Pendentes --}}
                    @foreach($meusPdisPendentes as $pdi)
                        <div class="dashboard-task-item"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.05)';"
                            onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)';">
                            <div style="display: flex; gap: 15px;">
                                <div
                                    style="background: rgba(106, 76, 124, 0.1); width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #6a4c7c; flex-shrink: 0;">
                                    <i class="fa-solid fa-bullseye" style="font-size: 1.2rem;"></i>
                                </div>
                                <div>
                                    <h4 style="font-size: 0.95rem; font-weight: 700; color: #2d3748; margin-bottom: 2px;">PDI:
                                        {{ $pdi->colaborador->name }}</h4>
                                    <p style="font-size: 0.85rem; color: #666; margin-bottom: 4px;">{{ $pdi->titulo }}</p>
                                    <span style="font-size: 0.75rem; color: #e53e3e; font-weight: 600;">
                                        <i class="fa-solid fa-bars-progress"></i>
                                        {{ $pdi->status->value == 'nao_iniciado' ? 'Não Iniciado' : 'Em Andamento' }}
                                        ({{ $pdi->progresso }}%)
                                    </span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <button type="button" onclick="abrirModalLembrete('App\\Models\\Pdi', {{ $pdi->id }})" 
                                    style="padding: 8px 12px; font-size: 0.85rem; border-radius: 8px; background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; cursor: pointer; transition: all 0.2s;"
                                    onmouseover="this.style.background='#e2e8f0'; this.style.color='#475569';" onmouseout="this.style.background='#f1f5f9'; this.style.color='#64748b';">
                                    <i class="fa-solid fa-bell"></i>
                                </button>
                                <a href="{{ route('pdis.show', $pdi->id) }}" class="btn-primary"
                                    style="padding: 8px 16px; font-size: 0.85rem; border-radius: 8px; background: linear-gradient(90deg, #6a4c7c 0%, #4a3556 100%); border: none;">
                                    Acompanhar PDI
                                </a>
                            </div>
                        </div>
                    @endforeach

                </div>
            @else
                <div
                    style="text-align: center; padding: 30px; background: #fafbfc; border: 1px dashed #e2e8f0; border-radius: 12px; color: #a0aec0; margin-bottom: 40px;">
                    <i class="fa-solid fa-mug-hot" style="font-size: 2.5rem; margin-bottom: 15px; color: #cbd5e0;"></i>
                    <p style="margin: 0; font-size: 0.9rem;">Tudo limpo por aqui! Você não tem avaliações ou PDIs pendentes no
                        momento.</p>
                </div>
            @endif
        </div>

        <!-- Coluna Direita: Ações Rápidas -->
        <div
            style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; flex-direction: column; gap: 15px;">
            <h2 style="font-size: 1.25rem; font-weight: 700; color: #2d3748; margin-bottom: 10px;">Atalhos Rápidos</h2>

            <a href="{{ route('avaliacoes.minhas') }}" class="btn-primary"
                style="width: 100%; display: inline-flex; text-align: center; text-decoration: none; box-sizing: border-box;">
                <i class="fa-solid fa-file-signature"></i> Ver Minhas Avaliações
            </a>

            <a href="{{ route('pdis.index', ['tab' => 'meus']) }}" class="btn-secondary"
                style="width: 100%; border-color: #6a4c7c; color: #6a4c7c; background: transparent; display: inline-flex; text-align: center; text-decoration: none; box-sizing: border-box;">
                <i class="fa-solid fa-route"></i> Meu PDI
            </a>

        </div>
    </div>
    @endif


        @if(in_array(auth()->user()->perfil_acesso->value, ['master', 'gestor']))
        {{-- Modal de Avaliações Ativas --}}
        <div id="modalAvaliacoesAtivas" style="display: none; position: fixed; inset: 0; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); align-items: center; justify-content: center; z-index: 1000; padding: 20px; animation: fadeIn 0.3s ease;">
            <div class="form-card" style="width: 100%; max-width: 650px; max-height: 85vh; display: flex; flex-direction: column; padding: 0; position: relative; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); border-radius: 20px; background: rgba(255,255,255,0.95); border: 1px solid rgba(255,255,255,0.2); overflow: hidden; animation: slideDown 0.4s cubic-bezier(0.16, 1, 0.3, 1);">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 25px 30px; border-bottom: 1px solid rgba(0,0,0,0.05); background: white;">
                    <h3 style="font-size: 1.35rem; font-weight: 800; color: #1a202c; margin: 0; display: flex; align-items: center; gap: 12px;">
                        <div style="background: rgba(39, 174, 96, 0.1); width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-star-half-stroke" style="color: #27ae60; font-size: 1.1rem;"></i>
                        </div>
                        Avaliações Ativas (Status)
                    </h3>
                    <button type="button" onclick="document.getElementById('modalAvaliacoesAtivas').style.display='none'" style="background: #f7fafc; border: none; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: #a0aec0; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#e2e8f0'; this.style.color='#4a5568';" onmouseout="this.style.background='#f7fafc'; this.style.color='#a0aec0';">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div id="modalAvaliacoesContent" style="overflow-y: auto; padding: 20px 30px; flex: 1; background: #fafbfc;">
                    <div style="text-align: center; color: #a0aec0; padding: 40px 0;"><i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem;"></i><p>Carregando avaliações...</p></div>
                </div>
            </div>
        </div>

        {{-- Modal de PDIs Pendentes --}}
        <div id="modalPdisPendentes" style="display: none; position: fixed; inset: 0; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); align-items: center; justify-content: center; z-index: 1000; padding: 20px; animation: fadeIn 0.3s ease;">
            <div class="form-card" style="width: 100%; max-width: 650px; max-height: 85vh; display: flex; flex-direction: column; padding: 0; position: relative; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); border-radius: 20px; background: rgba(255,255,255,0.95); border: 1px solid rgba(255,255,255,0.2); overflow: hidden; animation: slideDown 0.4s cubic-bezier(0.16, 1, 0.3, 1);">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 25px 30px; border-bottom: 1px solid rgba(0,0,0,0.05); background: white;">
                    <h3 style="font-size: 1.35rem; font-weight: 800; color: #1a202c; margin: 0; display: flex; align-items: center; gap: 12px;">
                        <div style="background: rgba(243, 156, 18, 0.1); width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-bullseye" style="color: #f39c12; font-size: 1.1rem;"></i>
                        </div>
                        PDIs Pendentes (Progresso)
                    </h3>
                    <button type="button" onclick="document.getElementById('modalPdisPendentes').style.display='none'" style="background: #f7fafc; border: none; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: #a0aec0; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#e2e8f0'; this.style.color='#4a5568';" onmouseout="this.style.background='#f7fafc'; this.style.color='#a0aec0';">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div id="modalPdisContent" style="overflow-y: auto; padding: 20px 30px; flex: 1; background: #fafbfc;">
                    <div style="text-align: center; color: #a0aec0; padding: 40px 0;"><i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem;"></i><p>Carregando PDIs...</p></div>
                </div>
            </div>
        </div>

        {{-- Modal de Andamento das Campanhas --}}
        <div id="modalAndamento" style="display: none; position: fixed; inset: 0; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); align-items: center; justify-content: center; z-index: 1000; padding: 20px; animation: fadeIn 0.3s ease;">
            <div class="form-card" style="width: 100%; max-width: 650px; max-height: 85vh; display: flex; flex-direction: column; padding: 0; position: relative; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); border-radius: 20px; background: rgba(255,255,255,0.95); border: 1px solid rgba(255,255,255,0.2); overflow: hidden; animation: slideDown 0.4s cubic-bezier(0.16, 1, 0.3, 1);">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 25px 30px; border-bottom: 1px solid rgba(0,0,0,0.05); background: white;">
                    <h3 style="font-size: 1.35rem; font-weight: 800; color: #1a202c; margin: 0; display: flex; align-items: center; gap: 12px;">
                        <div style="background: rgba(232, 67, 147, 0.1); width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-chart-line" style="color: #e84393; font-size: 1.1rem;"></i>
                        </div>
                        Andamento das Campanhas
                    </h3>
                    <button type="button" onclick="document.getElementById('modalAndamento').style.display='none'" style="background: #f7fafc; border: none; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: #a0aec0; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#e2e8f0'; this.style.color='#4a5568';" onmouseout="this.style.background='#f7fafc'; this.style.color='#a0aec0';">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div style="overflow-y: auto; padding: 20px 30px; flex: 1; background: #fafbfc; display: flex; flex-direction: column; gap: 15px;">
                    {{-- Avaliações --}}
                    @foreach($avaliacoesAtivasList as $avAtiva)
                        @php
                            $terminaram = $avAtiva->participantes->where('status', 'respondido')->unique('avaliado_id')->count();
                        @endphp
                        <a href="{{ route('avaliacoes.show', $avAtiva->id) }}" style="text-decoration: none; background: white; border: 1px solid #edf2f7; border-radius: 12px; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 6px rgba(0,0,0,0.05)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)';">
                            <div style="font-size: 1.1rem; font-weight: 800; color: #6a4c7c; display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0; padding-right: 15px;">
                                <i class="fa-solid fa-star-half-stroke"></i> <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">Avaliações: {{ $avAtiva->nome }}</span>
                            </div>
                            <div style="font-size: 0.9rem; color: #718096; white-space: nowrap; flex-shrink: 0; text-align: right;"><strong style="color: #4a5568; font-size: 1.05rem;">{{ $terminaram }}</strong> Respostas computadas</div>
                        </a>
                    @endforeach

                    {{-- Pesquisa de Clima --}}
                    @foreach($climasAtivosList as $climaAtivo)
                        @php
                            $terminaramClima = $climaAtivo->participacoes->where('status', '!=', 'pendente')->count();
                        @endphp
                        <a href="{{ route('gestao.clima.relatorio', $climaAtivo->id) }}" style="text-decoration: none; background: white; border: 1px solid #edf2f7; border-radius: 12px; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 6px rgba(0,0,0,0.05)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)';">
                            <div style="font-size: 1.1rem; font-weight: 800; color: #00b894; display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0; padding-right: 15px;">
                                <i class="fa-solid fa-cloud-sun"></i> <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">{{ $climaAtivo->titulo }}</span>
                            </div>
                            <div style="font-size: 0.9rem; color: #718096; white-space: nowrap; flex-shrink: 0; text-align: right;"><strong style="color: #4a5568; font-size: 1.05rem;">{{ $terminaramClima }}</strong> Respostas computadas</div>
                        </a>
                    @endforeach

                    {{-- PDIs --}}
                    <a href="{{ route('pdis.index') }}" style="text-decoration: none; background: white; border: 1px solid #edf2f7; border-radius: 12px; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 6px rgba(0,0,0,0.05)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)';">
                        <div style="font-size: 1.1rem; font-weight: 800; color: #f39c12; display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0; padding-right: 15px;">
                            <i class="fa-solid fa-bullseye"></i> <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">Planos de Desenvolvimento (PDI)</span>
                        </div>
                        <div style="font-size: 0.9rem; color: #718096; white-space: nowrap; flex-shrink: 0; text-align: right;"><strong style="color: #4a5568; font-size: 1.05rem;">{{ $pdisConcluidos }}</strong> Finalizadas e <strong style="color: #4a5568; font-size: 1.05rem;">{{ $pdisEmAndamentoTotal }}</strong> em Andamento</div>
                    </a>
                </div>
            </div>
        </div>
        </div>
    @endif

    {{-- Modal de Minhas Tarefas Pendentes (Para Master/Gestor) --}}
    @if(in_array(auth()->user()->perfil_acesso->value, ['master', 'gestor']))
        <div id="modalTarefasPendentes" style="display: none; position: fixed; inset: 0; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); align-items: center; justify-content: center; z-index: 1000; padding: 20px; animation: fadeIn 0.3s ease;">
            <div class="form-card" style="width: 100%; max-width: 650px; max-height: 85vh; display: flex; flex-direction: column; padding: 0; position: relative; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); border-radius: 20px; background: rgba(255,255,255,0.95); border: 1px solid rgba(255,255,255,0.2); overflow: hidden; animation: slideDown 0.4s cubic-bezier(0.16, 1, 0.3, 1);">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 25px 30px; border-bottom: 1px solid rgba(0,0,0,0.05); background: white;">
                    <h3 style="font-size: 1.35rem; font-weight: 800; color: #1a202c; margin: 0; display: flex; align-items: center; gap: 12px;">
                        <div style="background: rgba(41, 128, 185, 0.1); width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-list-check" style="color: #2980b9; font-size: 1.1rem;"></i>
                        </div>
                        Minhas Tarefas Pendentes
                        @if($tarefasPendentes->count() > 0 || $meusPdisPendentes->count() > 0)
                            <span style="background: #e53e3e; color: white; font-size: 0.75rem; padding: 2px 8px; border-radius: 12px; font-weight: bold;">{{ $tarefasPendentes->count() + $meusPdisPendentes->count() }}</span>
                        @endif
                    </h3>
                    <button type="button" onclick="document.getElementById('modalTarefasPendentes').style.display='none'" style="background: #f7fafc; border: none; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: #a0aec0; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#e2e8f0'; this.style.color='#4a5568';" onmouseout="this.style.background='#f7fafc'; this.style.color='#a0aec0';">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div style="overflow-y: auto; padding: 20px 30px; flex: 1; background: #fafbfc;">
                    @if($tarefasPendentes->count() > 0 || $meusPdisPendentes->count() > 0)
                        <div style="display: flex; flex-direction: column; gap: 15px;">
                            {{-- Avaliações Pendentes --}}
                            @foreach($tarefasPendentes as $tarefa)
                                <div style="display: flex; gap: 15px; padding: 15px; background: white; border: 1px solid #f0f2f5; border-radius: 12px; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.02); align-items: center;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.05)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)';">
                                    <div style="background: rgba(243, 156, 18, 0.1); width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #f39c12; flex-shrink: 0;">
                                        <i class="fa-solid fa-star-half-stroke" style="font-size: 1.2rem;"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <h4 style="font-size: 0.95rem; font-weight: 700; color: #2d3748; margin-bottom: 2px;">Avaliar: {{ $tarefa->avaliado->name }}</h4>
                                        <p style="font-size: 0.85rem; color: #666; margin-bottom: 4px;">{{ $tarefa->avaliacao->titulo }} ({{ ucfirst($tarefa->tipo->value) }})</p>
                                    </div>
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <a href="{{ route('avaliacoes.responder', $tarefa->id) }}" class="btn-primary" style="padding: 8px 16px; font-size: 0.85rem; border-radius: 8px; background: linear-gradient(90deg, #f39c12 0%, #d35400 100%);">
                                            Responder
                                        </a>
                                    </div>
                                </div>
                            @endforeach

                            {{-- PDIs Pendentes --}}
                            @foreach($meusPdisPendentes as $pdi)
                                <div style="display: flex; gap: 15px; padding: 15px; background: white; border: 1px solid #f0f2f5; border-radius: 12px; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.02); align-items: center;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.05)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)';">
                                    <div style="background: rgba(106, 76, 124, 0.1); width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #6a4c7c; flex-shrink: 0;">
                                        <i class="fa-solid fa-bullseye" style="font-size: 1.2rem;"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <h4 style="font-size: 0.95rem; font-weight: 700; color: #2d3748; margin-bottom: 2px;">PDI: {{ $pdi->colaborador->name }}</h4>
                                        <p style="font-size: 0.85rem; color: #666; margin-bottom: 4px;">{{ $pdi->titulo }}</p>
                                    </div>
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <a href="{{ route('pdis.show', $pdi->id) }}" class="btn-primary" style="padding: 8px 16px; font-size: 0.85rem; border-radius: 8px; background: linear-gradient(90deg, #6a4c7c 0%, #4a3556 100%); border: none;">
                                            Acompanhar
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="text-align: center; color: #a0aec0; padding: 40px 0;">
                            <i class="fa-solid fa-mug-hot" style="font-size: 2.5rem; margin-bottom: 15px; color: #cbd5e0;"></i>
                            <p style="margin: 0;">Tudo limpo por aqui! Você não tem tarefas pendentes.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Agendar Lembrete --}}
    <div id="modalLembrete" style="display: none; position: fixed; inset: 0; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); align-items: center; justify-content: center; z-index: 1050; padding: 20px; animation: fadeIn 0.3s ease;">
        <div class="form-card" style="width: 100%; max-width: 450px; display: flex; flex-direction: column; padding: 0; position: relative; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); border-radius: 20px; background: rgba(255,255,255,0.95); border: 1px solid rgba(255,255,255,0.2); overflow: hidden; animation: slideDown 0.4s cubic-bezier(0.16, 1, 0.3, 1);">
            <form action="{{ route('lembretes.store') }}" method="POST">
                @csrf
                <input type="hidden" name="lembreteable_type" id="lembreteable_type">
                <input type="hidden" name="lembreteable_id" id="lembreteable_id">
                
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 25px 30px; border-bottom: 1px solid rgba(0,0,0,0.05); background: white;">
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: #1a202c; margin: 0; display: flex; align-items: center; gap: 12px;">
                        <div style="background: rgba(100, 116, 139, 0.1); width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-bell" style="color: #64748b; font-size: 1.1rem;"></i>
                        </div>
                        Lembrar-me Depois
                    </h3>
                    <button type="button" onclick="document.getElementById('modalLembrete').style.display='none'" style="background: #f7fafc; border: none; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: #a0aec0; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#e2e8f0'; this.style.color='#4a5568';" onmouseout="this.style.background='#f7fafc'; this.style.color='#a0aec0';">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                
                <div style="padding: 25px 30px; background: #fafbfc;">
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #4a5568; margin-bottom: 8px;">Quando deseja ser lembrado?</label>
                        <select name="dias" style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #e2e8f0; background: white; font-size: 0.95rem; color: #2d3748; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                            <option value="1">Amanhã</option>
                            <option value="2">Em 2 dias</option>
                            <option value="3">Em 3 dias</option>
                            <option value="7">Em 1 semana</option>
                        </select>
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #4a5568; margin-bottom: 8px;">Mensagem para você mesmo (Opcional)</label>
                        <input type="text" name="mensagem" placeholder="Ex: Preciso finalizar essa avaliação" value="Lembrete automático do sistema" style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #e2e8f0; background: white; font-size: 0.95rem; color: #2d3748; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                    </div>
                </div>
                
                <div style="padding: 20px 30px; background: white; border-top: 1px solid rgba(0,0,0,0.05); text-align: right;">
                    <button type="button" onclick="document.getElementById('modalLembrete').style.display='none'" style="padding: 10px 18px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; color: #4a5568; font-weight: 600; cursor: pointer; margin-right: 10px;">Cancelar</button>
                    <button type="submit" style="padding: 10px 18px; border-radius: 8px; border: none; background: #3b82f6; color: white; font-weight: 600; cursor: pointer;">Agendar Lembrete</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>


        function abrirModalLembrete(type, id) {
            document.getElementById('lembreteable_type').value = type;
            document.getElementById('lembreteable_id').value = id;
            document.getElementById('modalLembrete').style.display = 'flex';
        }

        // Funções para abrir modais AJAX
        function openModalAvaliacoesAtivas() {
            document.getElementById('modalAvaliacoesAtivas').style.display = 'flex';
            document.getElementById('modalAvaliacoesContent').innerHTML = '<div style="text-align: center; color: #a0aec0; padding: 40px 0;"><i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem;"></i><p>Carregando avaliações...</p></div>';
            
            fetch("{{ route('dashboard.api.avaliacoes-ativas') }}")
                .then(response => response.text())
                .then(html => {
                    document.getElementById('modalAvaliacoesContent').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('modalAvaliacoesContent').innerHTML = '<div style="text-align: center; color: #e53e3e; padding: 20px;">Erro ao carregar dados.</div>';
                });
        }

        function openModalPdisPendentes() {
            document.getElementById('modalPdisPendentes').style.display = 'flex';
            document.getElementById('modalPdisContent').innerHTML = '<div style="text-align: center; color: #a0aec0; padding: 40px 0;"><i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem;"></i><p>Carregando PDIs...</p></div>';
            
            fetch("{{ route('dashboard.api.pdis-pendentes') }}")
                .then(response => response.text())
                .then(html => {
                    document.getElementById('modalPdisContent').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('modalPdisContent').innerHTML = '<div style="text-align: center; color: #e53e3e; padding: 20px;">Erro ao carregar dados.</div>';
                });
        }
    </script>
@endpush