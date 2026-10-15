@extends('layouts.app')

@section('title', 'Modelos de Avaliação')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title">Modelos de Avaliação</h1>
        <p style="color: #666; font-size: 0.95rem;">Gerencie as cascas/templates das avaliações. Depois você as aplica em Campanhas.</p>
    </div>
    <div>
        <a href="{{ route('avaliacoes.templates.create') }}" class="btn-primary">
            <i class="fa-solid fa-plus"></i> Novo Modelo
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
                    <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: left;">Título do Modelo</th>
                    <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: center;">Grau</th>
                    <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: right;">Ações</th>
                </tr>
            </thead>
        <tbody>
            @forelse($templates as $template)
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 15px; font-weight: bold; color: #2d3748; text-align: left;" data-label="Título do Modelo">{{ $template->titulo }}</td>
                <td style="padding: 15px; color: #64748b; font-size: 0.9rem; text-align: center;" data-label="Grau">{{ $template->grau }}°</td>
                <td style="padding: 15px; text-align: right;" data-label="Ações">
                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                        <a href="{{ route('avaliacoes.templates.show', $template->id) }}" class="btn-secondary" style="padding: 7px 11px; font-size: 0.85rem;" title="Visualizar Modelo">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <a href="{{ route('avaliacoes.templates.edit', $template->id) }}" class="btn-secondary" style="padding: 7px 11px; font-size: 0.85rem;" title="Editar Modelo">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <form action="{{ route('avaliacoes.templates.destroy', $template->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este modelo?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-secondary" style="padding: 7px 11px; font-size: 0.85rem; color: #ef4444; border-color: #fee2e2;" title="Excluir Modelo">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="padding: 30px; text-align: center; color: #718096;">
                    Nenhum modelo cadastrado. Comece criando o seu primeiro modelo.
                </td>
            </tr>
            @endforelse
        </tbody>
        </table>
    </div>
</div>
@endsection
