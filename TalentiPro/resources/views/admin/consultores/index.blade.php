@extends('layouts.app')

@section('title', 'Gestão de Consultores')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-user-tie" style="color: #3b82f6; margin-right: 8px;"></i> Consultores</h1>
        <p style="color: #666; font-size: 0.95rem;">Gerencie os perfis de consultores e as empresas clientes às quais possuem acesso analítico.</p>
    </div>
    
    <div>
        <a href="{{ route('admin.consultores.create') }}" class="btn-primary" style="display: flex; gap: 8px; align-items: center; text-decoration: none;">
            <i class="fa-solid fa-plus"></i> Novo Consultor
        </a>
    </div>
</div>

@if(session('success'))
    <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid #28a745;">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
@endif

<div style="background: white; border-radius: 10px; padding: 15px 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.04);">
    <form method="GET" action="{{ route('admin.consultores.index') }}" style="display: flex; gap: 15px; align-items: center;">
        <div style="flex-grow: 1; position: relative;">
            <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Buscar consultor por nome, e-mail ou CPF..." class="form-control" style="width: 100%; padding-left: 35px;">
            <i class="fa-solid fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #a0aec0;"></i>
        </div>
        <button type="submit" class="btn-primary" style="padding: 10px 20px;">
            <i class="fa-solid fa-filter"></i> Filtrar
        </button>
        @if(request('busca'))
            <a href="{{ route('admin.consultores.index') }}" class="btn-secondary" style="text-decoration: none; padding: 10px 15px;">Limpar</a>
        @endif
    </form>
</div>

<div class="table-responsive" style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); overflow: hidden;">
    <table class="table" style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                <th style="padding: 12px 15px; font-weight: 600; color: #4a5568;">Consultor</th>
                <th style="padding: 12px 15px; font-weight: 600; color: #4a5568;">Contato</th>
                <th style="padding: 12px 15px; font-weight: 600; color: #4a5568;">Empresas Vinculadas</th>
                <th style="padding: 12px 15px; font-weight: 600; color: #4a5568;">Status</th>
                <th style="padding: 12px 15px; font-weight: 600; color: #4a5568; text-align: right;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($consultores as $consultor)
            <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 15px;" data-label="Consultor">
                    <div style="font-weight: bold; color: #2d3748; font-size: 0.95rem;">{{ $consultor->name }}</div>
                    @if($consultor->cpf)
                        <span style="font-size: 0.78rem; color: #718096;"><i class="fa-regular fa-id-card"></i> {{ $consultor->cpf }}</span>
                    @endif
                </td>
                <td style="padding: 15px; color: #4a5568; font-size: 0.9rem;" data-label="Contato">
                    <div><i class="fa-regular fa-envelope" style="color: #718096;"></i> {{ $consultor->email }}</div>
                    @if($consultor->telefone)
                        <div style="font-size: 0.8rem; color: #718096;"><i class="fa-solid fa-phone" style="font-size: 0.75rem;"></i> {{ $consultor->telefone }}</div>
                    @endif
                </td>
                <td style="padding: 15px;" data-label="Empresas Vinculadas">
                    <div style="display: flex; flex-wrap: wrap; gap: 6px; max-width: 380px;">
                        @forelse($consultor->empresasConsultoria as $empresa)
                            <span style="background-color: #ebf8ff; color: #2b6cb0; border: 1px solid #bee3f8; padding: 3px 8px; border-radius: 6px; font-size: 0.78rem; font-weight: 600;">
                                <i class="fa-solid fa-building" style="font-size: 0.7rem;"></i> {{ $empresa->nome }}
                            </span>
                        @empty
                            <span style="color: #e53e3e; font-size: 0.8rem; font-weight: 600;">
                                <i class="fa-solid fa-triangle-exclamation"></i> Nenhuma empresa vinculada
                            </span>
                        @endforelse
                    </div>
                </td>
                <td style="padding: 15px;" data-label="Status">
                    @php
                        $statusValor = $consultor->status instanceof \BackedEnum ? $consultor->status->value : ($consultor->status ?? 'inativo');
                    @endphp
                    @if($statusValor === 'ativo')
                        <span style="background-color: #d4edda; color: #155724; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold;">Ativo</span>
                    @else
                        <span style="background-color: #f8d7da; color: #721c24; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold;">Inativo</span>
                    @endif
                </td>
                <td style="padding: 15px; text-align: right;" data-label="Ações">
                    <div style="display: inline-flex; gap: 8px;">
                        <a href="{{ route('admin.consultores.edit', $consultor) }}" class="btn-secondary" style="text-decoration: none; padding: 6px 12px; font-size: 0.85rem; border-radius: 6px;">
                            <i class="fa-solid fa-pen"></i> Editar
                        </a>
                        <form action="{{ route('admin.consultores.destroy', $consultor) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir o consultor {{ $consultor->name }}? Ele perderá acesso a todas as empresas.');" style="margin: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: #fff; border: 1px solid #fed7d7; color: #e53e3e; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; transition: background 0.2s;" onmouseover="this.style.background='#fff5f5'" onmouseout="this.style.background='#fff'">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding: 40px; text-align: center; color: #718096;">
                    <i class="fa-solid fa-user-tie" style="font-size: 2.5rem; color: #cbd5e0; margin-bottom: 10px; display: block;"></i>
                    Nenhum consultor encontrado.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($consultores->hasPages())
    <div style="margin-top: 20px;">
        {{ $consultores->links() }}
    </div>
@endif
@endsection
