@extends('layouts.app')

@section('title', 'Gestão de Empresas (Tenants)')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title">Empresas Clientes</h1>
        <p style="color: #666; font-size: 0.95rem;">Gerencie as contas, status e acessos de todos os clientes SaaS.</p>
    </div>
    
    <div>
        <a href="{{ route('admin.empresas.create') }}" class="btn-primary" style="display: flex; gap: 8px; align-items: center; text-decoration: none;">
            <i class="fa-solid fa-plus"></i> Nova Empresa
        </a>
    </div>
</div>

@if(session('success'))
    <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid #28a745;">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
@endif

<div class="table-responsive">
    <table class="table" style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                <th style="padding: 12px 15px; font-weight: 600; color: #4a5568;">Empresa</th>
                <th style="padding: 12px 15px; font-weight: 600; color: #4a5568;">CNPJ</th>
                <th style="padding: 12px 15px; font-weight: 600; color: #4a5568;">Users</th>
                <th style="padding: 12px 15px; font-weight: 600; color: #4a5568;">Uso IA (Mês)</th>
                <th style="padding: 12px 15px; font-weight: 600; color: #4a5568;">Status</th>
                <th style="padding: 12px 15px; font-weight: 600; color: #4a5568;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($empresas as $empresa)
            <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 15px;" data-label="Empresa">
                    <div style="font-weight: bold; color: #2d3748;">{{ $empresa->nome }}</div>
                    <span style="font-size: 0.75rem; color: #718096;">Contratação: {{ $empresa->data_contratacao ? \Carbon\Carbon::parse($empresa->data_contratacao)->format('d/m/Y') : 'N/A' }}</span>
                </td>
                <td style="padding: 15px; color: #4a5568; font-size: 0.9rem;" data-label="CNPJ">{{ $empresa->cnpj }}</td>
                <td style="padding: 15px;" data-label="Users">
                    @php
                        $userEstourado = $empresa->usuarios_count >= $empresa->max_usuarios;
                    @endphp
                    <span style="font-weight: bold; color: {{ $userEstourado ? '#e53e3e' : '#4a5568' }};">
                        {{ $empresa->usuarios_count }}
                    </span> / {{ $empresa->max_usuarios }}
                </td>
                <td style="padding: 15px;" data-label="Uso IA (Mês)">
                    @if($empresa->ia_ativa)
                        @php
                            $limiteMes = $empresa->ia_limite_transcricoes_mes;
                            $usoIaEstourado = $limiteMes > 0 && $empresa->ia_transcricoes_utilizadas >= $limiteMes;
                        @endphp
                        <span style="font-weight: bold; color: {{ $usoIaEstourado ? '#e53e3e' : '#4a5568' }};">
                            {{ $empresa->ia_transcricoes_utilizadas }}
                        </span> / {{ $limiteMes > 0 ? $limiteMes : 'Ilimitado' }}
                    @else
                        <span style="color: #a0aec0; font-size: 0.85rem;">Inativo</span>
                    @endif
                </td>
                <td style="padding: 15px;" data-label="Status">
                    @if($empresa->status->value === 'ativa')
                        <span style="background-color: #d4edda; color: #155724; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold;">Ativa</span>
                    @else
                        <span style="background-color: #f8d7da; color: #721c24; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold;">Suspensa</span>
                    @endif
                </td>
                <td style="padding: 15px; display: flex; gap: 10px;" data-label="Ações">
                    <a href="{{ route('admin.empresas.edit', $empresa) }}" class="btn-secondary" style="text-decoration: none; padding: 5px 10px; font-size: 0.85rem;">
                        <i class="fa-solid fa-pen"></i> Editar
                    </a>
                    
                    <form action="{{ route('admin.empresas.destroy', $empresa) }}" method="POST" data-confirm="ATENÇÃO: Isso excluirá permanentemente a empresa e TODOS os seus usuários. Deseja continuar?" data-confirm-title="Confirmar Exclusão de Empresa" data-confirm-type="danger">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-secondary" style="border-color: #e53e3e; color: #e53e3e; padding: 5px 10px; font-size: 0.85rem; background: transparent;">
                            <i class="fa-solid fa-trash"></i> Excluir
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding: 30px; text-align: center; color: #888;">
                    Nenhuma empresa cadastrada.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div style="margin-top: 20px;">
        {{ $empresas->links() }}
    </div>
</div>
@endsection
