@extends('layouts.app')

@section('title', 'Gestão de Avaliações')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title">Gestão de Avaliações</h1>
        <p style="color: #666; font-size: 0.95rem;">Crie e acompanhe as campanhas de avaliação da sua equipe.</p>
    </div>
    <div>
        <a href="{{ route('avaliacoes.create') }}" class="btn-primary">
            <i class="fa-solid fa-plus"></i> Nova Avaliação
        </a>
    </div>
</div>

@if (session('success'))
    <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

<div class="unified-card">
    <div class="table-responsive">
        <table class="custom-table table-cards-mobile" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background-color: #f8fafc; border-bottom: 2px solid #6a4c7c;">
                    <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: left;">Título da Campanha</th>
                    <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: left;">Período</th>
                    <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: center;">Status</th>
                    <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: center;">Participantes</th>
                    <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: right;">Ações</th>
                </tr>
            </thead>
        <tbody>
            @forelse($avaliacoes as $avaliacao)
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 15px; font-weight: bold; color: #2d3748; text-align: left;" data-label="Título da Campanha">{{ $avaliacao->titulo }}</td>
                <td style="padding: 15px; color: #64748b; font-size: 0.9rem; text-align: left;" data-label="Período">{{ $avaliacao->data_inicio->format('d/m/Y') }} até {{ $avaliacao->data_fim->format('d/m/Y') }}</td>
                <td style="padding: 15px; text-align: center;" data-label="Status">
                    @if($avaliacao->status->value === 'rascunho')
                        <span style="background: #e2e8f0; color: #4a5568; padding: 4px 8px; border-radius: 12px; font-size: 0.85rem; font-weight: bold;">Rascunho</span>
                    @elseif($avaliacao->status->value === 'andamento')
                        <span style="background: #c6f6d5; color: #22543d; padding: 4px 8px; border-radius: 12px; font-size: 0.85rem; font-weight: bold;">Em Andamento</span>
                    @else
                        <span style="background: #ebf8ff; color: #2b6cb0; padding: 4px 8px; border-radius: 12px; font-size: 0.85rem; font-weight: bold;">Concluída</span>
                    @endif
                </td>
                <td style="padding: 15px; text-align: center;" data-label="Participantes">
                    <span style="background: #edf2f7; padding: 4px 8px; border-radius: 12px; font-size: 0.85rem;">
                        <i class="fa-solid fa-users" style="color: #a0aec0;"></i> {{ $avaliacao->avaliados_count }} avaliados
                    </span>
                </td>
                <td style="padding: 15px; text-align: right;" data-label="Ações">
                    <div style="display: inline-flex; gap: 8px;">
                        @can('update', $avaliacao)
                            <a href="{{ route('avaliacoes.edit', $avaliacao->id) }}" class="btn-secondary" style="padding: 8px 12px; font-size: 0.9rem;" title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                        @endcan
                        @can('delete', $avaliacao)
                            <button type="button" class="btn-secondary" style="padding: 8px 12px; font-size: 0.9rem; color: #e53e3e;" title="Excluir" onclick="openDeleteModal('{{ route('avaliacoes.destroy', $avaliacao->id) }}', '{{ $avaliacao->titulo }}')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; padding: 30px; color:#666;">
                    Nenhuma avaliação cadastrada ainda. <br>
                    <a href="{{ route('avaliacoes.create') }}" class="btn-primary" style="margin-top: 15px; display: inline-block;">Criar a primeira</a>
                </td>
            </tr>
            @endforelse
        </tbody>
        </table>
    </div>
</div>

{{-- MODAL DE EXCLUSÃO CUSTOMIZADO --}}
<div id="deleteModal" style="display: none; position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 1000; padding: 20px;">
    <div class="form-card" style="max-width: 400px; padding: 30px; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border-radius: 16px; text-align: center;">
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
