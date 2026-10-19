@extends('layouts.app')

@section('title', 'Gestão de Avaliações')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions" style="margin-bottom: 25px;">
    <div>
        <h1 class="page-title">Gestão de Avaliações</h1>
        <p style="color: #64748b; font-size: 0.95rem; margin-top: 4px;">Gerencie modelos de competências e delegue campanhas para sua equipe.</p>
    </div>
    @if(!auth()->user()->isConsultor())
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="{{ route('avaliacoes.templates.create') }}" class="btn-secondary avaliacao-btn" style="background: #fff; border: 1px solid #cbd5e1; color: #334155; font-weight: 600; padding: 12px 24px; display: flex; align-items: center; justify-content: center;">
            <i class="fa-solid fa-layer-group" style="color: var(--primary-color, #6a4c7c);"></i> Novo Modelo
        </a>
        <a href="{{ route('avaliacoes.create') }}" class="btn-primary avaliacao-btn" style="font-weight: 600; padding: 12px 24px; display: flex; align-items: center; justify-content: center;">
            <i class="fa-solid fa-paper-plane"></i> Delegar / Nova Avaliação
        </a>
    </div>
    @endif
</div>

<style>
@media (max-width: 640px) {
    /* Header: botões em coluna, largura total */
    .header-actions > div:last-child {
        flex-direction: column !important;
        width: 100% !important;
    }
    .avaliacao-btn {
        width: 100% !important;
        justify-content: center !important;
    }

    /* Abas: fonte e padding menores, sem quebra forçada */
    .avaliacao-tab-nav {
        gap: 6px !important;
        margin-bottom: 18px !important;
    }
    .avaliacao-tab-btn {
        font-size: 0.78rem !important;
        padding: 10px 8px !important;
        gap: 4px !important;
    }
    .avaliacao-tab-label {
        display: none !important;
    }
    .avaliacao-tab-num {
        display: inline !important;
    }
}
</style>

@if (session('success'))
    <div style="background-color: #d4edda; color: #155724; border-radius: 10px; margin-bottom: 25px; border-left: 5px solid #28a745; display: flex; align-items: center; gap: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
        <i class="fa-solid fa-circle-check" style="font-size: 1.2rem;"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

{{-- NAVEGAÇÃO POR ABAS / DIVISÓRIAS --}}
<div class="avaliacao-tab-nav" style="display: flex; gap: 10px; border-bottom: 2px solid rgba(226, 232, 240, 0.8); margin-bottom: 25px; justify-content: center;">
    <button type="button" id="tabBtnTemplates" onclick="switchTab('templates')" class="tab-btn avaliacao-tab-btn active" style="padding: 12px 20px; font-weight: 600; font-size: 1rem; border: none; background: none; cursor: pointer; border-bottom: 3px solid var(--primary-color); color: var(--primary-color); display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s; flex: 1; max-width: 400px;">
        <i class="fa-solid fa-layer-group"></i>
        <span class="avaliacao-tab-label">1. Modelos de Avaliação (Templates)</span>
        <span class="avaliacao-tab-num" style="display:none; font-weight:700;">Modelos</span>
        <span style="background: rgba(36, 169, 214, 0.12); color: var(--primary-color); padding: 2px 8px; border-radius: 12px; font-size: 0.8rem;">{{ $templates->count() }}</span>
    </button>
    <button type="button" id="tabBtnCampanhas" onclick="switchTab('campanhas')" class="tab-btn avaliacao-tab-btn" style="padding: 12px 20px; font-weight: 600; font-size: 1rem; border: none; background: none; cursor: pointer; border-bottom: 3px solid transparent; color: #64748b; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s; flex: 1; max-width: 400px;">
        <i class="fa-solid fa-bullhorn"></i>
        <span class="avaliacao-tab-label">2. Avaliações em Andamento / Campanhas</span>
        <span class="avaliacao-tab-num" style="display:none; font-weight:700;">Campanhas</span>
        <span style="background: #e2e8f0; color: #475569; padding: 2px 8px; border-radius: 12px; font-size: 0.8rem;">{{ $avaliacoes->count() }}</span>
    </button>
</div>

{{-- DIVISÓRIA 1: MODELOS (TEMPLATES) --}}
<div id="sectionTemplates" class="tab-content" style="display: block;">
    <div style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(8px); border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 12px; padding: 18px 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
        <div>
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #1e293b; margin-bottom: 4px;">
                <i class="fa-solid fa-shapes" style="color: var(--primary-color);"></i> Modelos Prontos (Estrutura de Competências)
            </h3>
            <p style="font-size: 0.88rem; color: #64748b; margin: 0;">
                Estes são os moldes com as perguntas e competências. Clique em <strong>Delegar</strong> para definir participantes e prazos.
            </p>
        </div>
        <a href="{{ route('avaliacoes.templates.create') }}" class="btn-primary" style="padding: 8px 16px; font-size: 0.9rem;">
            <i class="fa-solid fa-plus"></i> Novo Modelo
        </a>
    </div>

    <div class="unified-card" style="box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
        <div class="table-responsive">
            <table class="custom-table table-cards-mobile" style="width: 100%; border-collapse: collapse; ">
                <thead>
                    <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="font-weight: 700; color: #334155; ">Título do Modelo</th>
                        <th style="font-weight: 700; color: #334155; ">Grau</th>
                        <th style="font-weight: 700; color: #334155; ">Competências Vinculadas</th>
                        <th style="font-weight: 700; color: #334155; ">Campanhas Criadas</th>
                        <th style="font-weight: 700; color: #334155; ">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;">
                        <td style="font-weight: 700; color: #1e293b; " data-label="Título do Modelo">
                            <div style="font-size: 1rem; color: #0f172a;">{{ $template->titulo }}</div>
                            @if($template->descricao)
                                <div style="font-size: 0.8rem; color: #64748b; font-weight: normal; margin-top: 2px;">{{ Str::limit($template->descricao, 60) }}</div>
                            @endif
                        </td>
                        <td style="" data-label="Grau">
                            <span style="background: #f1f5f9; color: #334155; font-weight: 700; padding: 4px 10px; border-radius: 8px; font-size: 0.85rem; border: 1px solid #cbd5e1;">
                                {{ $template->grau }}°
                            </span>
                        </td>
                        <td style="" data-label="Competências">
                            <div style="display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
                                @forelse($template->competencias->take(3) as $comp)
                                    <span style="background: rgba(106, 76, 124, 0.08); color: var(--primary-color, #6a4c7c); font-weight: 600; padding: 3px 8px; border-radius: 6px; font-size: 0.8rem; border: 1px solid rgba(106, 76, 124, 0.2);">
                                        {{ $comp->nome }}
                                    </span>
                                @empty
                                    <span style="color: #94a3b8; font-size: 0.85rem; font-style: italic;">Nenhuma competência</span>
                                @endforelse
                                @if($template->competencias->count() > 3)
                                    <span style="background: #e2e8f0; color: #475569; font-size: 0.75rem; font-weight: bold; padding: 3px 6px; border-radius: 6px;">
                                        +{{ $template->competencias->count() - 3 }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td style="" data-label="Campanhas">
                            <span style="font-weight: 600; color: #475569; font-size: 0.9rem;">
                                {{ $template->campanhas_count }} aplicação(ões)
                            </span>
                        </td>
                        <td style="" data-label="Ações">
                            <div style="display: inline-flex; gap: 8px; align-items: center;">
                                @if(!auth()->user()->isConsultor())
                                <a href="{{ route('avaliacoes.create', ['template_id' => $template->id]) }}" class="btn-primary" style="padding: 6px 14px; font-size: 0.85rem; font-weight: 600;" title="Delegar participantes usando este modelo">
                                    <i class="fa-solid fa-users-gear"></i> Delegar
                                </a>
                                @endif
                                <a href="{{ route('avaliacoes.templates.show', $template->id) }}" class="btn-secondary" style="padding: 7px 11px; font-size: 0.85rem;" title="Visualizar Modelo">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                @if(!auth()->user()->isConsultor())
                                <a href="{{ route('avaliacoes.templates.edit', $template->id) }}" class="btn-secondary" style="padding: 7px 11px; font-size: 0.85rem;" title="Editar Modelo">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form action="{{ route('avaliacoes.templates.destroy', $template->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir o modelo \'{{ addslashes($template->titulo) }}\'?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-secondary" style="padding: 7px 11px; font-size: 0.85rem; color: #ef4444; border-color: #fee2e2;" title="Excluir Modelo">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="padding: 40px 20px; color: #64748b;">
                            <div style="max-width: 350px; margin: 0 auto;">
                                <i class="fa-solid fa-layer-group" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 12px;"></i>
                                <h4 style="font-size: 1.1rem; color: #334155; margin-bottom: 6px;">Nenhum Modelo Criado</h4>
                                <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 18px;">Crie um modelo com as competências da sua empresa para delegar avaliações rapidamente.</p>
                                @if(!auth()->user()->isConsultor())
                                <a href="{{ route('avaliacoes.templates.create') }}" class="btn-primary" style="font-size: 0.9rem;">
                                    <i class="fa-solid fa-plus"></i> Criar Primeiro Modelo
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- DIVISÓRIA 2: AVALIAÇÕES DE FATO (CAMPANHAS) --}}
<div id="sectionCampanhas" class="tab-content" style="display: none;">
    <div style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(8px); border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 12px; padding: 18px 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
        <div>
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #1e293b; margin-bottom: 4px;">
                <i class="fa-solid fa-bullhorn" style="color: var(--primary-color);"></i> Campanhas em Andamento e Histórico
            </h3>
            <p style="font-size: 0.88rem; color: #64748b; margin: 0;">
                Acompanhe o preenchimento, mediações e relatórios das avaliações ativas da sua equipe.
            </p>
        </div>
        @if(!auth()->user()->isConsultor())
        <a href="{{ route('avaliacoes.create') }}" class="btn-primary" style="padding: 8px 16px; font-size: 0.9rem;">
            <i class="fa-solid fa-paper-plane"></i> Delegar Nova Avaliação
        </a>
        @endif
    </div>

    <div class="unified-card" style="box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
        <div class="table-responsive">
            <table class="custom-table table-cards-mobile" style="width: 100%; border-collapse: collapse; ">
                <thead>
                    <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="font-weight: 700; color: #334155; ">Título da Campanha</th>
                        <th style="font-weight: 700; color: #334155; ">Modelo de Origem</th>
                        <th style="font-weight: 700; color: #334155; ">Período / Recorrência</th>
                        <th style="font-weight: 700; color: #334155; ">Status</th>
                        <th style="font-weight: 700; color: #334155; ">Participantes</th>
                        <th style="font-weight: 700; color: #334155; ">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($avaliacoes as $avaliacao)
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;">
                        <td style="font-weight: 700; color: #1e293b; " data-label="Título da Campanha">
                            <div style="font-size: 1rem; color: #0f172a;">{{ $avaliacao->titulo }}</div>
                            <div style="font-size: 0.8rem; color: #64748b; font-weight: normal; margin-top: 2px;">
                                Criado por: {{ $avaliacao->criador->name ?? 'Sistema' }}
                            </div>
                        </td>
                        <td style="" data-label="Modelo de Origem">
                            @if($avaliacao->template)
                                <span style="display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; background: rgba(106, 76, 124, 0.08); color: var(--primary-color, #6a4c7c); font-weight: 600; padding: 4px 8px; border-radius: 6px; font-size: 0.82rem;">
                                    <i class="fa-solid fa-layer-group"></i> {{ $avaliacao->template->titulo }}
                                </span>
                            @else
                                <span style="color: #94a3b8; font-size: 0.85rem;">Personalizado</span>
                            @endif
                        </td>
                        <td style="color: #475569; font-size: 0.88rem; " data-label="Período">
                            <div style="font-weight: 600; color: #334155;">
                                <i class="fa-regular fa-calendar" style="color: #94a3b8;"></i> 
                                {{ $avaliacao->data_inicio?->format('d/m/Y') ?? 'N/D' }} até {{ $avaliacao->data_fim?->format('d/m/Y') ?? 'N/D' }}
                            </div>
                            @if($avaliacao->recorrencia && $avaliacao->recorrencia !== 'none')
                                <div style="font-size: 0.78rem; color: #059669; font-weight: 600; margin-top: 3px;">
                                    <i class="fa-solid fa-repeat"></i> {{ ucfirst($avaliacao->recorrencia) }}
                                </div>
                            @endif
                        </td>
                        <td style="" data-label="Status">
                            <span style="display: inline-flex; align-items: center; white-space: nowrap; justify-content: center; background: {{ $avaliacao->status->bg() }}; color: {{ $avaliacao->status->color() }}; border: 1px solid {{ $avaliacao->status->border() }}; padding: 4px 10px; border-radius: 12px; font-size: 0.82rem; font-weight: bold;">
                                {{ $avaliacao->status->label() }}
                            </span>
                        </td>
                        <td style="" data-label="Participantes">
                            <span style="display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; justify-content: center; background: #f8fafc; padding: 5px 10px; border-radius: 10px; font-size: 0.85rem; font-weight: 600; color: #334155; border: 1px solid #e2e8f0;">
                                <i class="fa-solid fa-users" style="color: var(--primary-color, #6a4c7c);"></i> {{ $avaliacao->avaliados_count }} avaliados
                            </span>
                        </td>
                        <td style="" data-label="Ações">
                            <div style="display: inline-flex; gap: 8px;">
                                <a href="{{ route('avaliacoes.show', $avaliacao->id) }}" class="btn-secondary" style="padding: 7px 11px; font-size: 0.85rem;" title="Acompanhar / Painel de Respostas">
                                    <i class="fa-solid fa-chart-pie"></i>
                                </a>
                                @can('update', $avaliacao)
                                    <a href="{{ route('avaliacoes.edit', $avaliacao->id) }}" class="btn-secondary" style="padding: 7px 11px; font-size: 0.85rem;" title="Editar">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                @endcan
                                @can('delete', $avaliacao)
                                    <button type="button" class="btn-secondary" style="padding: 7px 11px; font-size: 0.85rem; color: #ef4444; border-color: #fee2e2;" title="Excluir" onclick="openDeleteModal('{{ route('avaliacoes.destroy', $avaliacao->id) }}', '{{ addslashes($avaliacao->titulo) }}')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding: 40px 20px; color: #64748b;">
                            <div style="max-width: 350px; margin: 0 auto;">
                                <i class="fa-solid fa-bullhorn" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 12px;"></i>
                                <h4 style="font-size: 1.1rem; color: #334155; margin-bottom: 6px;">Nenhuma Avaliação Ativa</h4>
                                <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 18px;">Selecione um dos modelos na primeira aba para delegar participantes e lançar uma avaliação.</p>
                                @if(!auth()->user()->isConsultor())
                                <a href="{{ route('avaliacoes.create') }}" class="btn-primary" style="font-size: 0.9rem;">
                                    <i class="fa-solid fa-paper-plane"></i> Delegar Avaliação
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL DE EXCLUSÃO CUSTOMIZADO --}}
<div id="deleteModal" style="display: none; position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 1000; padding: 20px;">
    <div class="form-card" style="max-width: 400px; padding: 30px; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border-radius: 16px; ">
        <div style="width: 60px; height: 60px; background-color: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.8rem; color: #ef4444;"></i>
        </div>
        
        <h3 style="font-size: 1.25rem; font-weight: bold; color: #1f2937; margin-bottom: 10px;">Excluir Avaliação</h3>
        <p style="color: #4b5563; font-size: 0.95rem; margin-bottom: 25px; line-height: 1.5;">
            Tem certeza que deseja excluir a avaliação <strong id="deleteAvaliacaoTitle" style="color: #111827;"></strong>? Esta ação não pode ser desfeita e removerá todos os vínculos.
        </p>

        <form id="deleteForm" method="POST" style="display: flex; gap: 10px; justify-content: center;">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeDeleteModal()" class="btn-secondary" style="flex: 1; justify-content: center;">
                Cancelar
            </button>
            <button type="submit" class="btn-primary" style="flex: 1; justify-content: center; background: #ef4444; border-color: #ef4444;">
                Sim, Excluir
            </button>
        </form>
    </div>
</div>

<script>
    function switchTab(tab) {
        const secTemplates = document.getElementById('sectionTemplates');
        const secCampanhas = document.getElementById('sectionCampanhas');
        const btnTemplates = document.getElementById('tabBtnTemplates');
        const btnCampanhas = document.getElementById('tabBtnCampanhas');

        if (tab === 'campanhas') {
            secTemplates.style.display = 'none';
            secCampanhas.style.display = 'block';

            btnCampanhas.style.borderBottomColor = 'var(--primary-color)';
            btnCampanhas.style.color = 'var(--primary-color)';
            btnTemplates.style.borderBottomColor = 'transparent';
            btnTemplates.style.color = '#64748b';

            history.replaceState(null, null, '?tab=campanhas');
        } else {
            secTemplates.style.display = 'block';
            secCampanhas.style.display = 'none';

            btnTemplates.style.borderBottomColor = 'var(--primary-color)';
            btnTemplates.style.color = 'var(--primary-color)';
            btnCampanhas.style.borderBottomColor = 'transparent';
            btnCampanhas.style.color = '#64748b';

            history.replaceState(null, null, '?tab=templates');
        }
    }

    // Carrega a aba correta se houver parâmetro na URL
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        if (tab === 'campanhas') {
            switchTab('campanhas');
        }
    });

    function openDeleteModal(url, titulo) {
        document.getElementById('deleteForm').action = url;
        document.getElementById('deleteAvaliacaoTitle').textContent = titulo;
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }
</script>
@endsection

