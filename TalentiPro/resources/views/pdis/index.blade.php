@extends('layouts.app')

@section('title', 'PDIs - Planos de Desenvolvimento')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title">Planos de Desenvolvimento Individual (PDI)</h1>
        <p style="color: #666; font-size: 0.95rem;">Acompanhe e gerencie metas de capacitação e competências.</p>
    </div>
    @can('create', App\Models\Pdi::class)
        <div>
            <a href="{{ route('pdis.create') }}" class="btn-primary">
                <i class="fa-solid fa-plus"></i> Novo PDI
            </a>
        </div>
    @endcan
</div>

<!-- KPIs Rápidos -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <!-- Total -->
    <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 15px;">
        <div style="background: #ebf8ff; color: #3182ce; width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <div>
            <span style="font-size: 0.85rem; color: #718096; font-weight: bold; text-transform: uppercase;">Total de PDIs</span>
            <h3 style="margin: 0; font-size: 1.8rem; color: #2d3748;">{{ $kpiTotal }}</h3>
        </div>
    </div>
    <!-- Em Andamento -->
    <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 15px;">
        <div style="background: #fefcbf; color: #b7791f; width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fa-solid fa-spinner fa-spin"></i>
        </div>
        <div>
            <span style="font-size: 0.85rem; color: #718096; font-weight: bold; text-transform: uppercase;">Em Andamento</span>
            <h3 style="margin: 0; font-size: 1.8rem; color: #2d3748;">{{ $kpiAndamento }}</h3>
        </div>
    </div>
    <!-- Concluídos -->
    <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 15px;">
        <div style="background: #c6f6d5; color: #38a169; width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <span style="font-size: 0.85rem; color: #718096; font-weight: bold; text-transform: uppercase;">Concluídos</span>
            <h3 style="margin: 0; font-size: 1.8rem; color: #2d3748;">{{ $kpiConcluidos }}</h3>
        </div>
    </div>
    <!-- Não Iniciados -->
    <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 15px;">
        <div style="background: #edf2f7; color: #4a5568; width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fa-solid fa-hourglass-start"></i>
        </div>
        <div>
            <span style="font-size: 0.85rem; color: #718096; font-weight: bold; text-transform: uppercase;">Não Iniciados</span>
            <h3 style="margin: 0; font-size: 1.8rem; color: #2d3748;">{{ $kpiNaoIniciados }}</h3>
        </div>
    </div>
</div>

@if(auth()->user()->perfil_acesso->value !== 'colaborador')
    <!-- Abas de Navegação (Master e Gestor) -->
    <div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
        <button class="tab-btn active" onclick="switchTab('meus-pdis', this)" style="padding: 10px 20px; font-size: 1rem; border: none; background: none; font-weight: bold; color: #6a4c7c; border-bottom: 3px solid #6a4c7c; cursor: pointer;">
            Meus PDIs
        </button>
        <button class="tab-btn" onclick="switchTab('pdis-equipe', this)" style="padding: 10px 20px; font-size: 1rem; border: none; background: none; font-weight: bold; color: #718096; cursor: pointer;">
            PDIs da Equipe
        </button>
        <button class="tab-btn" onclick="switchTab('grupos-pdi', this)" style="padding: 10px 20px; font-size: 1rem; border: none; background: none; font-weight: bold; color: #718096; cursor: pointer;">
            PDIs em Grupo (Lotes)
        </button>
    </div>
@endif

<!-- Conteúdo das Abas -->
<div id="meus-pdis" class="tab-content">
    <div class="unified-card" style="padding: 0; overflow: hidden;">
        <table class="table table-cards-mobile" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f7fafc; border-bottom: 1px solid #e2e8f0;">
                    <th style="padding: 15px 20px; text-align: left; color: #4a5568; font-weight: 600;">Título do PDI</th>
                    <th style="padding: 15px 20px; text-align: left; color: #4a5568; font-weight: 600;">Gestor Responsável</th>
                    <th style="padding: 15px 20px; text-align: center; color: #4a5568; font-weight: 600;">Status</th>
                    <th style="padding: 15px 20px; text-align: left; color: #4a5568; font-weight: 600; width: 200px;">Progresso</th>
                    <th style="padding: 15px 20px; text-align: center; color: #4a5568; font-weight: 600;">Data Limite</th>
                    <th style="padding: 15px 20px; text-align: center; color: #4a5568; font-weight: 600;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($meusPdis as $pdi)
                    <tr style="border-bottom: 1px solid #edf2f7; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#f7fafc'" onmouseout="this.style.backgroundColor='transparent'">
                        <td data-label="Título do PDI" style="padding: 15px 20px; font-weight: bold; color: #2d3748;">{{ $pdi->titulo }}</td>
                        <td data-label="Gestor Responsável" style="padding: 15px 20px; color: #4a5568;">{{ $pdi->gestor->name ?? 'RH/Administração' }}</td>
                        <td data-label="Status" style="padding: 15px 20px; text-align: center;">
                            @include('pdis.partials.badge-status', ['status' => $pdi->status->value])
                        </td>
                        <td data-label="Progresso" style="padding: 15px 20px;">
                            @include('pdis.partials.progress-bar', ['progresso' => $pdi->progresso])
                        </td>
                        <td data-label="Data Limite" style="padding: 15px 20px; text-align: center; color: #718096;">
                            {{ $pdi->data_fim ? $pdi->data_fim->format('d/m/Y') : 'Sem prazo' }}
                        </td>
                        <td data-label="Ações" style="padding: 15px 20px; text-align: center;">
                            <div style="display: inline-flex; gap: 5px;">
                                <a href="{{ route('pdis.show', $pdi->id) }}" class="btn-secondary" style="padding: 5px 12px; font-size: 0.85rem;">
                                    <i class="fa-solid fa-eye"></i> Acessar
                                </a>
                                @can('delete', $pdi)
                                    <form action="{{ route('pdis.destroy', $pdi->id) }}" method="POST" style="display:inline-block;" data-confirm="Tem certeza que deseja excluir permanentemente este PDI e todos os seus dados associados?" data-confirm-title="Excluir PDI">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger" style="padding: 5px 12px; font-size: 0.85rem; border:none; border-radius: 6px; cursor: pointer;" title="Excluir PDI">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 40px; text-align: center; color: #a0aec0;">
                            <i class="fa-solid fa-folder-open" style="font-size: 2.5rem; margin-bottom: 10px; display: block;"></i>
                            Você não possui nenhum PDI atribuído no momento.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(auth()->user()->perfil_acesso->value !== 'colaborador')
    <!-- Aba PDIs da Equipe -->
    <div id="pdis-equipe" class="tab-content" style="display: none;">
        <div class="unified-card" style="padding: 0; overflow: hidden;">
            <table class="table table-cards-mobile" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f7fafc; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 15px 20px; text-align: left; color: #4a5568; font-weight: 600;">Colaborador</th>
                        <th style="padding: 15px 20px; text-align: left; color: #4a5568; font-weight: 600;">Título do PDI</th>
                        <th style="padding: 15px 20px; text-align: center; color: #4a5568; font-weight: 600;">Status</th>
                        <th style="padding: 15px 20px; text-align: left; color: #4a5568; font-weight: 600; width: 200px;">Progresso</th>
                        <th style="padding: 15px 20px; text-align: center; color: #4a5568; font-weight: 600;">Data Limite</th>
                        <th style="padding: 15px 20px; text-align: center; color: #4a5568; font-weight: 600;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pdisEquipe as $pdi)
                        <tr style="border-bottom: 1px solid #edf2f7; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#f7fafc'" onmouseout="this.style.backgroundColor='transparent'">
                            <td data-label="Colaborador" style="padding: 15px 20px; font-weight: bold; color: #2d3748;">
                                <div style="display: flex; flex-direction: column;">
                                    <span>{{ $pdi->colaborador->name }}</span>
                                    <span style="font-size: 0.8rem; font-weight: normal; color: #718096;">{{ $pdi->colaborador->cargo->nome ?? 'Sem Cargo' }}</span>
                                </div>
                            </td>
                            <td data-label="Título do PDI" style="padding: 15px 20px; color: #4a5568;">{{ $pdi->titulo }}</td>
                            <td data-label="Status" style="padding: 15px 20px; text-align: center;">
                                @include('pdis.partials.badge-status', ['status' => $pdi->status->value])
                            </td>
                            <td data-label="Progresso" style="padding: 15px 20px;">
                                @include('pdis.partials.progress-bar', ['progresso' => $pdi->progresso])
                            </td>
                            <td data-label="Data Limite" style="padding: 15px 20px; text-align: center; color: #718096;">
                                {{ $pdi->data_fim ? $pdi->data_fim->format('d/m/Y') : 'Sem prazo' }}
                            </td>
                            <td data-label="Ações" style="padding: 15px 20px; text-align: center;">
                                <div style="display: inline-flex; gap: 5px;">
                                    <a href="{{ route('pdis.show', $pdi->id) }}" class="btn-secondary" style="padding: 5px 12px; font-size: 0.85rem;">
                                        <i class="fa-solid fa-eye"></i> Acessar
                                    </a>
                                    @can('update', $pdi)
                                        @if($pdi->pdi_grupo_id === null)
                                            <a href="{{ route('pdis.edit', $pdi->id) }}" class="btn-secondary" style="padding: 5px 12px; font-size: 0.85rem;" title="Editar PDI">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                        @endif
                                    @endcan
                                    @can('delete', $pdi)
                                        <form action="{{ route('pdis.destroy', $pdi->id) }}" method="POST" data-confirm="Deseja excluir permanentemente este PDI e todas as suas metas associadas?" data-confirm-title="Confirmar Exclusão" data-confirm-type="danger" style="margin:0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger" style="padding: 5px 12px; font-size: 0.85rem; background-color: #e74c3c;" title="Excluir PDI">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 40px; text-align: center; color: #a0aec0;">
                                <i class="fa-solid fa-people-group" style="font-size: 2.5rem; margin-bottom: 10px; display: block;"></i>
                                Nenhum colaborador da sua equipe possui PDI cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Aba PDIs em Grupo -->
    <div id="grupos-pdi" class="tab-content" style="display: none;">
        <div class="unified-card" style="padding: 0; overflow: hidden;">
            <table class="table table-cards-mobile" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f7fafc; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 15px 20px; text-align: left; color: #4a5568; font-weight: 600;">Título do Lote</th>
                        <th style="padding: 15px 20px; text-align: left; color: #4a5568; font-weight: 600;">Categoria</th>
                        <th style="padding: 15px 20px; text-align: center; color: #4a5568; font-weight: 600;">Colaboradores Vinculados</th>
                        <th style="padding: 15px 20px; text-align: center; color: #4a5568; font-weight: 600;">Período</th>
                        <th style="padding: 15px 20px; text-align: center; color: #4a5568; font-weight: 600;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pdiGrupos as $grupo)
                        <tr style="border-bottom: 1px solid #edf2f7; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#f7fafc'" onmouseout="this.style.backgroundColor='transparent'">
                            <td data-label="Título do Lote" style="padding: 15px 20px; font-weight: bold; color: #2d3748;">
                                <div style="display: flex; flex-direction: column;">
                                    <span>{{ $grupo->titulo }}</span>
                                    <span style="font-size: 0.8rem; font-weight: normal; color: #718096;">Criado por: {{ $grupo->gestor->name ?? 'RH' }}</span>
                                </div>
                            </td>
                            <td data-label="Categoria" style="padding: 15px 20px; color: #4a5568;">{{ $grupo->categoria ?? 'Não definida' }}</td>
                            <td data-label="Colaboradores Vinculados" style="padding: 15px 20px; text-align: center;">
                                <span class="badge" style="background-color: #ebf8ff; color: #2b6cb0; padding: 4px 10px; font-size: 0.9rem; font-weight: bold; border-radius: 20px;">
                                    {{ $grupo->pdis_count }} colaboradores
                                </span>
                            </td>
                            <td data-label="Período" style="padding: 15px 20px; text-align: center; color: #718096;">
                                {{ $grupo->data_inicio ? $grupo->data_inicio->format('d/m/Y') : '' }}
                                {{ $grupo->data_fim ? ' até ' . $grupo->data_fim->format('d/m/Y') : 'Sem prazo' }}
                            </td>
                            <td data-label="Ações" style="padding: 15px 20px; text-align: center;">
                                <div style="display: flex; gap: 10px; justify-content: center; align-items: center;">
                                    @can('update', $grupo)
                                        <a href="{{ route('pdi-grupos.edit', $grupo->id) }}" class="btn-secondary" style="padding: 5px 12px; font-size: 0.85rem;" title="Editar Lote">
                                            <i class="fa-solid fa-pen-to-square"></i> Editar Lote
                                        </a>
                                    @endcan
                                    @can('delete', $grupo)
                                        <form action="{{ route('pdi-grupos.destroy', $grupo->id) }}" method="POST" data-confirm="ATENÇÃO: Isso excluirá o lote de PDIs de TODOS os colaboradores vinculados a este grupo. Deseja prosseguir?" data-confirm-title="Confirmar Exclusão do Lote" data-confirm-type="danger" style="margin:0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger" style="padding: 5px 12px; font-size: 0.85rem; background-color: #e74c3c;">
                                                <i class="fa-solid fa-trash"></i> Excluir Lote
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 40px; text-align: center; color: #a0aec0;">
                                <i class="fa-solid fa-boxes-stacked" style="font-size: 2.5rem; margin-bottom: 10px; display: block;"></i>
                                Nenhum lote de PDIs em grupo foi criado até o momento.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

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
        // Ocultar todos os conteúdos das abas
        document.querySelectorAll('.tab-content').forEach(function(content) {
            content.style.display = 'none';
        });
        
        // Remover classe ativa de todos os botões
        document.querySelectorAll('.tab-btn').forEach(function(btn) {
            btn.classList.remove('active');
            btn.style.color = '#718096';
            btn.style.borderBottom = 'none';
        });

        // Mostrar a aba selecionada
        document.getElementById(tabId).style.display = 'block';

        // Ativar o botão clicado
        buttonEl.classList.add('active');
        buttonEl.style.color = '#6a4c7c';
        buttonEl.style.borderBottom = '3px solid #6a4c7c';
    }
</script>
@endpush
