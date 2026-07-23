@extends('layouts.admin')

@section('title', 'Cargos')

@section('content')
<div class="header-actions">
    <h1 class="page-title">Cargos</h1>
    <a href="{{ route('cargos.create') }}" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Novo Cargo
    </a>
</div>

<div class="card-container">

    @if($errors->any())
        <div class="alert alert-danger" style="padding:15px; background:#f8d7da; color:#721c24; border-radius:5px; margin-bottom:20px;">
            {{ $errors->first() }}
        </div>
    @endif

    <table class="table-list" style="width:100%; border-collapse:collapse;">
        <thead style="background:#f8f9fa;">
            <tr>
                <th style="padding:15px; text-align:left;">Cargo</th>
                <th style="padding:15px; text-align:left;">Departamento</th>
                <th style="padding:15px; text-align:left;">Senioridade / CBO</th>
                <th style="padding:15px; text-align:center;">Qtd. Colaboradores</th>
                <th style="padding:15px; text-align:right;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cargos as $cargo)
            <tr style="border-bottom:1px solid #eee;">
                <td data-label="Cargo" style="padding:15px;">
                    <strong>{{ $cargo->nome }}</strong>
                    @if($cargo->cargoSuperior)
                        <div style="font-size:11px; color:#888;">Reporta a: {{ $cargo->cargoSuperior->nome }}</div>
                    @else
                        <div style="font-size:11px; color:#27ae60;">(Liderança/Topo)</div>
                    @endif
                </td>
                <td data-label="Departamento" style="padding:15px;">{{ $cargo->departamento->nome ?? 'N/A' }}</td>
                <td data-label="Senioridade / CBO" style="padding:15px;">
                    <div>{{ $cargo->senioridade ?? '-' }}</div>
                    <small style="color:#777;">CBO: {{ $cargo->cbo ?? '-' }}</small>
                </td>
                <td data-label="Qtd. Colaboradores" style="padding:15px; text-align:center;">
                    <span style="background:#eee; padding:5px 10px; border-radius:10px; font-size:0.8em; font-weight:bold;">
                        {{ $cargo->users_count }}
                    </span>
                </td>
                <td data-label="Ações" style="padding:15px; text-align:right;">
                    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 15px;">
                        <a href="{{ route('cargos.edit', $cargo->id) }}" style="color:#3b5998; font-size:1.2em; transition: 0.2s; margin:0;" onmouseover="this.style.color='#2d4373'" onmouseout="this.style.color='#3b5998'" title="Editar">
                            <i class="fa-solid fa-pen"></i>
                        </a>

                        <form action="{{ route('cargos.destroy', $cargo->id) }}" method="POST" style="margin: 0;" class="confirm-delete" data-confirm-message="Deseja excluir o cargo '{{ $cargo->nome }}'?">
                            @csrf @method('DELETE')
                            <button type="submit" style="background:none; border:none; cursor:pointer; color:#e74c3c; font-size:1.2em; transition: 0.2s; padding:0; margin:0;" onmouseover="this.style.color='#c0392b'" onmouseout="this.style.color='#e74c3c'" title="Excluir">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center; padding:30px;">Nenhum cargo cadastrado.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
