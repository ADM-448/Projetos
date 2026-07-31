@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h1 style="margin: 0; font-size: 1.8rem; font-weight: 600;">Gerenciador de Vagas</h1>
    <a href="{{ route('vagas.create') }}" class="btn btn-primary">+ Nova Vaga</a>
</div>

<div class="card" style="padding: 0; overflow-x: auto;">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Salário</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($vagas as $vaga)
                <tr>
                    <td>#{{ $vaga->id }}</td>
                    <td>
                        <strong>{{ $vaga->titulo }}</strong>
                        @if ($vaga->isPremium())
                            <span class="badge badge-premium">⭐ Premium</span>
                        @endif
                    </td>
                    <td>
                        @if ($vaga->salario)
                            R$ {{ number_format($vaga->salario, 2, ',', '.') }}
                        @else
                            <span style="color: var(--text-muted);">A combinar</span>
                        @endif
                    </td>
                    <td>
                        @if ($vaga->ativa)
                            <span class="badge badge-active">Ativa</span>
                        @else
                            <span class="badge badge-inactive">Inativa</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('vagas.show', $vaga) }}" class="btn btn-secondary" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Ver</a>
                            <a href="{{ route('vagas.edit', $vaga) }}" class="btn btn-secondary" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Editar</a>
                            <form action="{{ route('vagas.destroy', $vaga) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta vaga?');" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Excluir</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">Nenhuma vaga cadastrada no momento.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($vagas->hasPages())
    <div style="margin-top: 1.5rem;">
        {{ $vagas->links() }}
    </div>
@endif
@endsection
