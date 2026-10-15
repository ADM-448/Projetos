@extends('layouts.app')

@section('title', 'Minhas Avaliações')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title">Minhas Avaliações</h1>
        <p style="color: #666; font-size: 0.95rem;">Gerencie as avaliações que você precisa responder, mediar ou já respondeu.</p>
    </div>
</div>

@if (session('success'))
    <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

{{-- Painel de Busca, Filtros e Lista de Avaliações Unificados em um único Card --}}
<div class="unified-card">
    <div style="margin-bottom: 25px; display: flex; flex-direction: column; gap: 15px;">
        <div style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center; justify-content: space-between;">
            {{-- Barra de Pesquisa --}}
            <div style="position: relative; flex: 1; min-width: 280px;">
                <span style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #a0aec0;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" id="search-input" placeholder="Buscar por título da campanha ou nome do colaborador..." style="width: 100%; padding: 12px 12px 12px 45px; border: 1px solid #cbd5e0; border-radius: 8px; font-size: 0.95rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#6a4c7c'" onblur="this.style.borderColor='#cbd5e0'">
            </div>

            {{-- Filtros Rápidos (Tipo) --}}
            <div style="display: flex; gap: 10px; flex-wrap: wrap;" id="type-filter-group">
                <button class="filter-btn active" data-filter="all" style="padding: 10px 18px; font-size: 0.9rem; border-radius: 8px; font-weight: bold; border: 1px solid #cbd5e0; background-color: white; color: #4a5568; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center;">
                    Todos <span id="count-all" style="background-color: #edf2f7; color: #4a5568; margin-left: 8px; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; font-weight: bold;">0</span>
                </button>
                <button class="filter-btn" data-filter="autoavaliacao" style="padding: 10px 18px; font-size: 0.9rem; border-radius: 8px; font-weight: bold; border: 1px solid #cbd5e0; background-color: white; color: #6a4c7c; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center;">
                    Autoavaliações <span id="count-auto" style="background-color: #f3ebf7; color: #6a4c7c; margin-left: 8px; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; font-weight: bold;">0</span>
                </button>
                <button class="filter-btn" data-filter="avaliador" style="padding: 10px 18px; font-size: 0.9rem; border-radius: 8px; font-weight: bold; border: 1px solid #cbd5e0; background-color: white; color: #2b6cb0; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center;">
                    Avaliar Colaboradores <span id="count-eval" style="background-color: #ebf8ff; color: #2b6cb0; margin-left: 8px; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; font-weight: bold;">0</span>
                </button>
                <button class="filter-btn" data-filter="mediador" style="padding: 10px 18px; font-size: 0.9rem; border-radius: 8px; font-weight: bold; border: 1px solid #cbd5e0; background-color: white; color: #dd6b20; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center;">
                    Mediações <span id="count-media" style="background-color: #fffaf0; color: #dd6b20; margin-left: 8px; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; font-weight: bold;">0</span>
                </button>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="custom-table table-cards-mobile" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background-color: #f8fafc; border-bottom: 2px solid #6a4c7c;">
                    <th style="padding: 12px 15px; font-weight: bold; color: #333; width: 45%;">Avaliação e Função</th>
                    <th style="padding: 12px 15px; font-weight: bold; color: #333; width: 20%;">Prazo</th>
                    <th style="padding: 12px 15px; font-weight: bold; color: #333; width: 15%;">Status</th>
                    <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: center; width: 20%;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($participacoes as $participacao)
                @php
                    $tipoValue = $participacao->tipo->value ?? $participacao->tipo;
                    
                    // Define o grupo simplificado para o JS filtrar
                    if ($tipoValue === 'autoavaliacao') {
                        $jsTipo = 'autoavaliacao';
                    } elseif ($tipoValue === 'mediador') {
                        $jsTipo = 'mediador';
                    } else {
                        $jsTipo = 'avaliador';
                    }
                    
                    // Constrói a string de busca em caixa baixa
                    $buscaStr = strtolower($participacao->avaliacao->titulo . ' ' . ($participacao->avaliado->name ?? '') . ' ' . ($participacao->avaliado->cargo->nome ?? ''));
                @endphp
                <tr style="border-bottom: 1px solid #eee;" data-tipo="{{ $jsTipo }}" data-busca="{{ $buscaStr }}">
                    <td style="padding: 15px; color: #2d3748;" data-label="Avaliação">
                        <div style="margin-bottom: 6px;">
                            @if($tipoValue === 'autoavaliacao')
                                <span style="display: inline-flex; align-items: center; gap: 6px; background-color: #f3ebf7; color: #6a4c7c; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: bold;">
                                    <i class="fa-solid fa-user"></i> Autoavaliação
                                </span>
                            @elseif($tipoValue === 'mediador')
                                <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 6px;">
                                    <span style="display: inline-flex; align-items: center; gap: 6px; background-color: #fffaf0; color: #dd6b20; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: bold; white-space: nowrap;">
                                        <i class="fa-solid fa-people-arrows"></i> Mediação
                                    </span>
                                    <span style="font-size: 0.9rem; color: #4a5568; font-weight: 600; word-break: break-word; overflow-wrap: anywhere;">
                                        {{ $participacao->avaliado->name }}
                                    </span>
                                </div>
                            @else
                                <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 6px;">
                                    <span style="display: inline-flex; align-items: center; gap: 6px; background-color: #ebf8ff; color: #2b6cb0; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: bold; white-space: nowrap;">
                                        <i class="fa-solid fa-users"></i> Avaliar Colaborador
                                    </span>
                                    <span style="font-size: 0.9rem; color: #4a5568; font-weight: 600; word-break: break-word; overflow-wrap: anywhere;">
                                        {{ $participacao->avaliado->name }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div style="font-size: 0.85rem; color: #718096; font-weight: normal; word-break: break-word; overflow-wrap: anywhere;">
                            Campanha: <strong style="color: #4a5568;">{{ $participacao->avaliacao->titulo }}</strong>
                        </div>
                    </td>
                    <td style="padding: 15px; color: #4a5568;" data-label="Prazo">
                        <i class="fa-regular fa-calendar" style="margin-right: 5px; color: #a0aec0;"></i>
                        {{ \Carbon\Carbon::parse($participacao->avaliacao->data_fim)->format('d/m/Y') }}
                    </td>
                    <td style="padding: 15px;" data-label="Status">
                        @if($participacao->status === 'pendente')
                            <span style="background: #fefcbf; color: #b7791f; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 6px; height: 6px; background: #b7791f; border-radius: 50%;"></span>
                                Pendente
                            </span>
                        @elseif($participacao->status === 'respondido')
                            <span style="background: #c6f6d5; color: #276749; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 6px; height: 6px; background: #276749; border-radius: 50%;"></span>
                                Concluída
                            </span>
                        @else
                            <span style="background: #e2e8f0; color: #4a5568; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: bold;">
                                {{ ucfirst($participacao->status) }}
                            </span>
                        @endif
                    </td>
                    <td style="padding: 15px; text-align: center;" data-label="Ações">
                        @php
                            $prazoEncerrado = $participacao->avaliacao->data_fim && \Carbon\Carbon::parse($participacao->avaliacao->data_fim)->endOfDay()->isPast();
                            $emCarencia = false;

                            // Se for o Master ou o Criador da campanha, eles têm superpoderes e nunca são bloqueados
                            $isPoderoso = auth()->user()->perfil_acesso->value === 'master' || $participacao->avaliacao->created_by === auth()->id();
                            
                            if ($isPoderoso) {
                                $prazoEncerrado = false;
                            } else {
                                // Regra de carência: 48h extras se teve solicitação aprovada
                                if ($prazoEncerrado) {
                                    $solicitacaoRecente = \App\Models\Solicitacao::where('avaliacao_id', $participacao->avaliacao_id)
                                        ->where('user_id', $participacao->avaliador_id)
                                        ->where('status', 'aprovada')
                                        ->latest('updated_at')
                                        ->first();

                                    if ($solicitacaoRecente && $solicitacaoRecente->updated_at->copy()->addHours(48)->isFuture()) {
                                        $prazoEncerrado = false;
                                        $emCarencia = true;
                                    }
                                }
                            }
                        @endphp

                        @if($tipoValue === 'mediador')
                            @if($participacao->status === 'pendente')
                                @if($prazoEncerrado)
                                    <button class="btn-secondary" style="padding: 6px 12px; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px; background-color: #f7fafc; color: #a0aec0; border-color: #e2e8f0; cursor: not-allowed;" disabled title="Prazo Encerrado">
                                        <i class="fa-solid fa-lock"></i> Bloqueado
                                    </button>
                                @else
                                    <a href="{{ route('avaliacoes.mediar', [$participacao->avaliacao_id, $participacao->avaliado_id]) }}" class="btn-primary" style="background: linear-gradient(90deg, #d69e2e 0%, #b7791f 100%); border: none; padding: 6px 12px; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                                        <i class="fa-solid fa-gavel"></i> Mediar
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('avaliacoes.mediar', [$participacao->avaliacao_id, $participacao->avaliado_id]) }}" class="btn-secondary" style="color: #dd6b20; border-color: #ffd8a8; background: #fff9db; padding: 6px 12px; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                                    <i class="fa-solid fa-gavel"></i> Editar Mediação
                                </a>
                            @endif
                        @else
                            @if($participacao->status === 'pendente')
                                @if($prazoEncerrado)
                                    <button class="btn-secondary" style="padding: 6px 12px; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px; background-color: #f7fafc; color: #a0aec0; border-color: #e2e8f0; cursor: not-allowed;" disabled title="Prazo Encerrado">
                                        <i class="fa-solid fa-lock"></i> Bloqueado
                                    </button>
                                @else
                                    <a href="{{ route('avaliacoes.responder', $participacao->id) }}" class="btn-primary" style="padding: 6px 12px; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                                        <i class="fa-solid fa-pen-to-square"></i> Responder
                                    </a>
                                @endif
                            @else
                                @if($prazoEncerrado)
                                    <div style="display:inline-flex; align-items:center; gap:8px; position:relative;">
                                        <button class="btn-secondary" style="padding: 6px 12px; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px;" disabled title="Prazo Encerrado (Já respondido)">
                                            <i class="fa-solid fa-check"></i> Feito
                                        </button>
                                        <button onclick="toggleDropdown('dropdown-{{ $participacao->id }}')" style="background:none; border:none; cursor:pointer; font-size:1.2rem; color:#4a5568; padding:0 4px;">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <div id="dropdown-{{ $participacao->id }}" style="display:none; position:absolute; right:0; top:100%; background:white; border:1px solid #e2e8f0; border-radius:8px; box-shadow:0 4px 6px rgba(0,0,0,0.1); padding:8px 0; z-index:10; min-width:200px; text-align:left;">
                                            <a href="#" onclick="abrirModalNovaSolicitacao({{ $participacao->avaliacao_id }}, 'reabertura'); return false;" style="display:block; padding:8px 16px; color:#4a5568; text-decoration:none; font-size:0.85rem;">
                                                <i class="fa-solid fa-rotate-left" style="margin-right:6px;"></i> Solicitar Reabertura
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <div style="display:inline-flex; align-items:center; gap:8px; position:relative;">
                                        <a href="{{ route('avaliacoes.responder', $participacao->id) }}" class="btn-secondary" style="padding: 6px 12px; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;" title="Editar respostas">
                                            <i class="fa-solid fa-pen-to-square"></i> Editar
                                        </a>
                                        <button onclick="toggleDropdown('dropdown-{{ $participacao->id }}')" style="background:none; border:none; cursor:pointer; font-size:1.2rem; color:#4a5568; padding:0 4px;">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <div id="dropdown-{{ $participacao->id }}" style="display:none; position:absolute; right:0; top:100%; background:white; border:1px solid #e2e8f0; border-radius:8px; box-shadow:0 4px 6px rgba(0,0,0,0.1); padding:8px 0; z-index:10; min-width:200px; text-align:left;">
                                            <a href="#" onclick="abrirModalNovaSolicitacao({{ $participacao->avaliacao_id }}, 'reabertura'); return false;" style="display:block; padding:8px 16px; color:#4a5568; text-decoration:none; font-size:0.85rem;">
                                                <i class="fa-solid fa-rotate-left" style="margin-right:6px;"></i> Solicitar Reabertura
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        @endif
                    </td>
                </tr>
                @empty
                <tr id="no-records-row">
                    <td colspan="4" style="text-align:center; padding: 30px; color:#666;">
                        Você não possui nenhuma avaliação vinculada no momento.
                    </td>
                </tr>
                @endforelse
                
                {{-- Linha para quando o filtro via JS não encontrar nenhum registro --}}
                <tr id="empty-row" style="display: none;">
                    <td colspan="4" style="text-align: center; padding: 40px; color: #718096;">
                        <div style="font-size: 1.5rem; margin-bottom: 10px; color: #a0aec0;"><i class="fa-solid fa-folder-open"></i></div>
                        Nenhuma avaliação encontrada para os filtros e busca selecionados.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- ──────────────────────────────────────────────────────────────
     MODAL: Nova Solicitação (apenas para avaliações concluídas)
──────────────────────────────────────────────────────────────── --}}
<div id="modal-nova-solicitacao" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:9999;">
    <div class="card" style="width:100%; max-width:560px; margin:20px; animation:slideUp 0.3s ease; background:white; border-radius:12px; padding:24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="font-size:1.2rem; font-weight:700; color:#1e293b; margin:0;">
                <i class="fa-solid fa-paper-plane" style="color:#8b5cf6;"></i> Nova Solicitação
            </h2>
            <button onclick="document.getElementById('modal-nova-solicitacao').style.display = 'none'"
                    style="background:none; border:none; cursor:pointer; font-size:1.2rem; color:#64748b;">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>

        <form action="{{ route('solicitacoes.store') }}" method="POST">
            @csrf
            <input type="hidden" name="avaliacao_id" id="modal-solicitacao-avaliacao-id">
            <input type="hidden" name="tipo" id="modal-solicitacao-tipo">
            
            <p id="modal-solicitacao-tipo-texto" style="background:#f1f5f9; padding:12px; border-radius:8px; color:#334155; font-weight:600; font-size:0.9rem; margin-bottom:16px;">
                Tipo: ...
            </p>

            <div style="margin-bottom:20px;">
                <label class="form-label" style="display:block; margin-bottom:8px; font-weight:600; color:#1e293b;">Justificativa <span style="color:#ef4444;">*</span></label>
                <textarea name="justificativa" class="form-control" rows="5" required minlength="20"
                    style="width:100%; padding:12px; border:1px solid #cbd5e1; border-radius:8px;"
                    placeholder="Descreva detalhadamente o motivo da sua solicitação (mínimo 20 caracteres)..."></textarea>
            </div>
            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('modal-nova-solicitacao').style.display = 'none'"
                        class="btn" style="background:#f1f5f9; color:#475569; padding:8px 16px; border-radius:8px; border:none; cursor:pointer;">Cancelar</button>
                <button type="submit" class="btn btn-primary" style="padding:8px 16px; border-radius:8px; border:none; cursor:pointer;">
                    <i class="fa-solid fa-paper-plane"></i> Enviar Solicitação
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const filterButtons = document.querySelectorAll('.filter-btn');
    const tableRows = document.querySelectorAll('tbody tr:not(#empty-row):not(#no-records-row)');
    const emptyRow = document.getElementById('empty-row');
    const noRecordsRow = document.getElementById('no-records-row');
    
    // Fechar dropdowns ao clicar fora
    document.addEventListener('click', function(e) {
        if (!e.target.closest('[id^="dropdown-"]') && !e.target.closest('button[onclick^="toggleDropdown"]')) {
            document.querySelectorAll('[id^="dropdown-"]').forEach(el => el.style.display = 'none');
        }
    });

    window.toggleDropdown = function(id) {
        const dropdown = document.getElementById(id);
        const isVisible = dropdown.style.display === 'block';
        document.querySelectorAll('[id^="dropdown-"]').forEach(el => el.style.display = 'none');
        if (!isVisible) dropdown.style.display = 'block';
    }

    window.abrirModalNovaSolicitacao = function(avaliacaoId, tipo) {
        document.querySelectorAll('[id^="dropdown-"]').forEach(el => el.style.display = 'none');
        document.getElementById('modal-solicitacao-avaliacao-id').value = avaliacaoId;
        document.getElementById('modal-solicitacao-tipo').value = tipo;
        
        let tipoTexto = '<i class="fa-solid fa-rotate-left" style="color:#8b5cf6;"></i> Solicitação de Reabertura (quero refazer minha avaliação)';
            
        document.getElementById('modal-solicitacao-tipo-texto').innerHTML = tipoTexto;
        document.getElementById('modal-nova-solicitacao').style.display = 'flex';
    }
    
    // Contadores dinâmicos de registros
    let countAll = 0;
    let countAuto = 0;
    let countEval = 0;
    let countMedia = 0;
    
    tableRows.forEach(row => {
        const tipo = row.getAttribute('data-tipo');
        countAll++;
        if (tipo === 'autoavaliacao') countAuto++;
        else if (tipo === 'avaliador') countEval++;
        else if (tipo === 'mediador') countMedia++;
    });
    
    const countAllEl = document.getElementById('count-all');
    const countAutoEl = document.getElementById('count-auto');
    const countEvalEl = document.getElementById('count-eval');
    const countMediaEl = document.getElementById('count-media');
    
    if (countAllEl) countAllEl.textContent = countAll;
    if (countAutoEl) countAutoEl.textContent = countAuto;
    if (countEvalEl) countEvalEl.textContent = countEval;
    if (countMediaEl) countMediaEl.textContent = countMedia;
    
    let activeFilter = 'all';

    // Ler parâmetro da URL para pré-selecionar filtro
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab) {
        const btn = document.querySelector(`.filter-btn[data-filter="${tab}"]`);
        if (btn) {
            // Apenas aciona o click logo após definir as funções,
            // então vamos agendar o click pro final do bloco atual.
            setTimeout(() => btn.click(), 50);
        }
    }
    
    function applyFilters() {
        const searchText = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;
        
        tableRows.forEach(row => {
            const tipo = row.getAttribute('data-tipo');
            const busca = row.getAttribute('data-busca');
            
            const matchTipo = (activeFilter === 'all' || tipo === activeFilter);
            const matchText = (searchText === '' || busca.includes(searchText));
            
            if (matchTipo && matchText) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Se houver registros na lista, gerencia a linha de "nenhum encontrado"
        if (tableRows.length > 0) {
            if (visibleCount === 0) {
                emptyRow.style.display = '';
            } else {
                emptyRow.style.display = 'none';
            }
        }
    }
    
    // Configura botões de filtro
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove classe active e estilos de todos
            filterButtons.forEach(b => {
                b.classList.remove('active');
                b.style.backgroundColor = 'white';
                b.style.borderColor = '#cbd5e0';
                b.style.boxShadow = 'none';
            });
            
            // Adiciona estilos ativos específicos para o botão clicado
            this.classList.add('active');
            activeFilter = this.getAttribute('data-filter');
            
            this.style.boxShadow = '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)';
            if (activeFilter === 'all') {
                this.style.backgroundColor = '#edf2f7';
                this.style.borderColor = '#4a5568';
            } else if (activeFilter === 'autoavaliacao') {
                this.style.backgroundColor = '#f3ebf7';
                this.style.borderColor = '#6a4c7c';
            } else if (activeFilter === 'avaliador') {
                this.style.backgroundColor = '#ebf8ff';
                this.style.borderColor = '#2b6cb0';
            } else if (activeFilter === 'mediador') {
                this.style.backgroundColor = '#fffaf0';
                this.style.borderColor = '#dd6b20';
            }
            
            applyFilters();
        });
    });
    
    // Inicializa o estilo ativo do botão "Todos" no carregamento
    const allBtn = document.querySelector('[data-filter="all"]');
    if (allBtn) {
        allBtn.style.backgroundColor = '#edf2f7';
        allBtn.style.borderColor = '#4a5568';
        allBtn.style.boxShadow = '0 1px 3px 0 rgba(0, 0, 0, 0.1)';
    }
    
    // Evento de digitação na pesquisa
    searchInput.addEventListener('input', applyFilters);
});
</script>
@endsection
