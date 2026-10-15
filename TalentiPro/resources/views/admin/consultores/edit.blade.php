@extends('layouts.app')

@section('title', 'Editar Consultor')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-user-pen" style="color: #3b82f6; margin-right: 8px;"></i> Editar Consultor: {{ $consultor->name }}</h1>
        <p style="color: #666; font-size: 0.95rem;">Atualize os dados e as empresas às quais o consultor tem permissão de acesso.</p>
    </div>
    <div>
        <a href="{{ route('admin.consultores.index') }}" class="btn-secondary" style="text-decoration: none;">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 850px;">
    
    @if ($errors->any())
        <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 25px; border-left: 5px solid #e53e3e;">
            <div style="font-weight: bold; margin-bottom: 5px;"><i class="fa-solid fa-circle-exclamation"></i> Por favor, corrija os erros abaixo:</div>
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="form-consultor" action="{{ route('admin.consultores.update', $consultor) }}" method="POST">
        @csrf
        @method('PUT')
        
        <h3 style="margin-bottom: 20px; color: #2c3e50; border-bottom: 1px solid #edf2f7; padding-bottom: 10px; font-size: 1.15rem;">
            <i class="fa-solid fa-user" style="color: #3b82f6; margin-right: 6px;"></i> 1. Dados Pessoais e Acesso
        </h3>
        
        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" for="name" style="font-weight: 600; color: #374151;">Nome Completo <span style="color: red;">*</span></label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $consultor->name) }}" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div class="form-group">
                <label class="form-label" for="email" style="font-weight: 600; color: #374151;">E-mail de Login <span style="color: red;">*</span></label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $consultor->email) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="password" style="font-weight: 600; color: #374151;">Nova Senha <small style="font-weight: normal; color: #718096;">(Deixe em branco para manter a atual)</small></label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 30px;">
            <div class="form-group">
                <label class="form-label" for="cpf" style="font-weight: 600; color: #374151;">CPF</label>
                <input type="text" id="cpf" name="cpf" class="form-control" value="{{ old('cpf', $consultor->cpf) }}" placeholder="000.000.000-00">
            </div>
            <div class="form-group">
                <label class="form-label" for="telefone" style="font-weight: 600; color: #374151;">Telefone / WhatsApp</label>
                <input type="text" id="telefone" name="telefone" class="form-control" value="{{ old('telefone', $consultor->telefone) }}" placeholder="(00) 00000-0000">
            </div>
            <div class="form-group">
                <label class="form-label" for="status" style="font-weight: 600; color: #374151;">Status <span style="color: red;">*</span></label>
                @php
                    $statusAtual = old('status', $consultor->status instanceof \BackedEnum ? $consultor->status->value : $consultor->status);
                @endphp
                <select id="status" name="status" class="form-control" required>
                    <option value="ativo" {{ $statusAtual === 'ativo' ? 'selected' : '' }}>Ativo</option>
                    <option value="inativo" {{ $statusAtual === 'inativo' ? 'selected' : '' }}>Inativo</option>
                </select>
            </div>
        </div>

        <h3 style="margin-bottom: 15px; color: #2c3e50; border-bottom: 1px solid #edf2f7; padding-bottom: 10px; font-size: 1.15rem; display: flex; justify-content: space-between; align-items: center;">
            <span><i class="fa-solid fa-building" style="color: #3b82f6; margin-right: 6px;"></i> 2. Empresas Liberadas para Consulta <span style="color: red;">*</span></span>
            <div style="font-size: 0.85rem; font-weight: normal;">
                <button type="button" onclick="marcarTodasEmpresas(true)" style="background: none; border: none; color: #3b82f6; cursor: pointer; text-decoration: underline; margin-right: 10px;">Marcar Todas</button>
                <button type="button" onclick="marcarTodasEmpresas(false)" style="background: none; border: none; color: #64748b; cursor: pointer; text-decoration: underline;">Desmarcar Todas</button>
            </div>
        </h3>
        
        <p style="color: #64748b; font-size: 0.88rem; margin-bottom: 15px;">
            Selecione as empresas que o consultor poderá monitorar e analisar.
        </p>

        @php
            $selectedEmpresas = old('empresas', $empresasVinculadasIds);
        @endphp

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 12px; max-height: 280px; overflow-y: auto; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; margin-bottom: 30px;">
            @forelse($empresas as $empresa)
                <label style="display: flex; align-items: center; gap: 10px; background: white; padding: 10px 14px; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer; transition: border-color 0.2s;" onmouseover="this.style.borderColor='#3b82f6'" onmouseout="this.style.borderColor='#e2e8f0'">
                    <input type="checkbox" name="empresas[]" value="{{ $empresa->id }}" class="empresa-checkbox" {{ in_array($empresa->id, $selectedEmpresas) ? 'checked' : '' }} style="width: 17px; height: 17px; accent-color: #3b82f6; cursor: pointer;">
                    <div>
                        <div style="font-weight: 600; color: #1e293b; font-size: 0.9rem;">
                            {{ $empresa->nome }}
                            @if($empresa->status->value !== 'ativa')
                                <span style="font-size: 0.7rem; color: #e53e3e;">(Inativa)</span>
                            @endif
                        </div>
                        <div style="font-size: 0.75rem; color: #64748b;">CNPJ: {{ $empresa->cnpj }}</div>
                    </div>
                </label>
            @empty
                <div style="grid-column: 1 / -1; padding: 20px; text-align: center; color: #94a3b8;">
                    Nenhuma empresa cadastrada.
                </div>
            @endforelse
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 15px; border-top: 1px solid #edf2f7; padding-top: 20px;">
            <a href="{{ route('admin.consultores.index') }}" class="btn-secondary" style="text-decoration: none;">Cancelar</a>
            <button type="submit" class="btn-primary" style="padding: 10px 24px; font-weight: 600;">
                <i class="fa-solid fa-check"></i> Salvar Alterações
            </button>
        </div>
    </form>
</div>

<script>
    function marcarTodasEmpresas(marcar) {
        document.querySelectorAll('.empresa-checkbox').forEach(cb => {
            cb.checked = marcar;
        });
    }
</script>
@endsection
