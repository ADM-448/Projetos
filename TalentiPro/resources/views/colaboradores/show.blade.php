@extends('layouts.admin')

@section('title', 'Detalhes do Colaborador')

@section('content')
<div class="header-actions">
    <h1 class="page-title">DETALHES DO COLABORADOR</h1>
    <a href="{{ route('colaboradores.index') }}" class="btn-back"
        style="background-color:#3b5998; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;">Voltar</a>
</div>

<div class="form-card"
    style="background:white; padding:40px; border-radius:15px; box-shadow:0 4px 15px rgba(0,0,0,0.05);">

    <div
        style="display:flex; align-items:center; gap:20px; margin-bottom:30px; border-bottom:1px solid #eee; padding-bottom:20px;">
        @if($colaborador->foto_perfil)
        <img src="{{ $colaborador->foto_perfil }}"
            style="width:80px; height:80px; border-radius:50%; object-fit:cover;">
        @else
        <div
            style="width:80px; height:80px; background:#ccc; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-size:30px; font-weight:bold;">
            {{ substr($colaborador->name, 0, 1) }}
        </div>
        @endif
        <div>
            <h2 style="color:#333; margin-bottom:5px;">{{ $colaborador->name }}</h2>
            <span
                style="background:#eee; padding:5px 10px; border-radius:15px; font-size:12px; font-weight:bold; color:#555;">{{ strtoupper($colaborador->perfil_acesso) }}</span>
        </div>
    </div>

    <div class="info-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        <div>
            <span style="display:block; font-weight:bold; color:#555; margin-bottom:5px;">E-mail</span>
            <span style="color:#333;">{{ $colaborador->email }}</span>
        </div>
        <div>
            <span style="display:block; font-weight:bold; color:#555; margin-bottom:5px;">CPF</span>
            <span style="color:#333;">{{ $colaborador->cpf ?? 'Não informado' }}</span>
        </div>

        <div>
            <span style="display:block; font-weight:bold; color:#555; margin-bottom:5px;">Data de Admissão</span>
            <span
                style="color:#333;">{{ $colaborador->data_admissao ? \Carbon\Carbon::parse($colaborador->data_admissao)->format('d/m/Y') : '-' }}</span>
        </div>
        <div>
            <span style="display:block; font-weight:bold; color:#555; margin-bottom:5px;">Vínculo</span>
            <span style="color:#333;">{{ strtoupper($colaborador->vinculo ?? '-') }}</span>
        </div>

        <div>
            <span style="display:block; font-weight:bold; color:#555; margin-bottom:5px;">Departamento</span>
            <span style="color:#333;">{{ $colaborador->departamento->nome ?? 'Sem Depto' }}</span>
        </div>
        <div>
            <span style="display:block; font-weight:bold; color:#555; margin-bottom:5px;">Cargo</span>
            <span style="color:#333;">{{ $colaborador->cargo->nome ?? 'Sem Cargo' }}</span>
        </div>

        <div>
            <span style="display:block; font-weight:bold; color:#555; margin-bottom:5px;">Gestor Responsável</span>
            <span style="color:#333;">{{ $colaborador->gestor->name ?? 'Nenhum' }}</span>
        </div>
    </div>

    <div style="margin-top:40px; text-align:right;">
        <a href="{{ route('colaboradores.edit', $colaborador->id) }}"
            style="background-color:#6a4c7c; color:white; padding:10px 30px; border:none; border-radius:5px; font-weight:bold; text-decoration:none;">Editar
            Dados</a>
    </div>

</div>
@endsection
