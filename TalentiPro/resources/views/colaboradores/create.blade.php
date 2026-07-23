@extends('layouts.admin')

@section('title', isset($colaborador) ? 'Editar Colaborador' : 'Cadastrar Colaborador')

@section('content')
<div class="header-actions">
    <h1 class="page-title">{{ isset($colaborador) ? 'EDITAR COLABORADOR' : 'CADASTRAR COLABORADOR' }}</h1>
    <a href="{{ route('colaboradores.index') }}" class="btn-back" style="background-color:#3b5998; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;">Voltar</a>
</div>

<form action="{{ isset($colaborador) ? route('colaboradores.update', $colaborador->id) : route('colaboradores.store') }}" method="POST" class="form-card">
    @csrf
    @if(isset($colaborador)) @method('PUT') @endif

    <h3 style="color: #6a4c7c; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Dados Pessoais</h3>

    <div class="form-row" style="display:flex; gap:20px; margin-bottom:20px;">
        <div class="form-group" style="flex:1;">
            <label>Nome do Colaborador</label>
            <input type="text" name="name" value="{{ $colaborador->name ?? old('name') }}" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
        </div>
        <div class="form-group" style="flex:1;">
            <label>CPF</label>
            <input type="text" name="cpf" value="{{ $colaborador->cpf ?? old('cpf') }}" placeholder="000.000.000-00" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
        </div>
    </div>

    <div class="form-row" style="display:flex; gap:20px; margin-bottom:20px;">
        <div class="form-group" style="flex:1;">
            <label>Data de Nascimento</label>
            <input type="date" name="data_nascimento" value="{{ $colaborador->data_nascimento ?? old('data_nascimento') }}" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
        </div>
        <div class="form-group" style="flex:1;">
            <label>Gênero</label>
            <select name="genero" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
                <option value="">Selecione...</option>
                <option value="M" {{ (isset($colaborador) && $colaborador->genero == 'M') ? 'selected' : '' }}>Masculino</option>
                <option value="F" {{ (isset($colaborador) && $colaborador->genero == 'F') ? 'selected' : '' }}>Feminino</option>
                <option value="O" {{ (isset($colaborador) && $colaborador->genero == 'O') ? 'selected' : '' }}>Outro</option>
            </select>
        </div>
        <div class="form-group" style="flex:1;">
            <label>Escolaridade</label>
            <select name="escolaridade" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
                <option value="">Selecione...</option>
                <option value="medio" {{ (isset($colaborador) && $colaborador->escolaridade == 'medio') ? 'selected' : '' }}>Ensino Médio</option>
                <option value="superior" {{ (isset($colaborador) && $colaborador->escolaridade == 'superior') ? 'selected' : '' }}>Ensino Superior</option>
            </select>
        </div>
    </div>

    <div class="form-row" style="display:flex; gap:20px; margin-bottom:20px;">
        <div class="form-group" style="flex:1;">
            <label>E-mail (Login)</label>
            <input type="email" name="email" value="{{ $colaborador->email ?? old('email') }}" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
        </div>
        <div class="form-group" style="flex:1;">
            <label>Senha {{ isset($colaborador) ? '(Deixe em branco para manter)' : '' }}</label>
            <input type="password" name="password" {{ isset($colaborador) ? '' : 'required' }} style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
        </div>
    </div>

    <h3 style="color: #6a4c7c; margin: 30px 0 20px 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Dados Contratuais</h3>

    <div class="form-row" style="display:flex; gap:20px; margin-bottom:20px;">
        <div class="form-group" style="flex:1;">
            <label>Data de Admissão</label>
            <input type="date" name="data_admissao" value="{{ $colaborador->data_admissao ?? old('data_admissao') }}" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
        </div>
        <div class="form-group" style="flex:1;">
            <label>Status</label>
            <select name="status" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
                <option value="ativo" {{ (isset($colaborador) && $colaborador->status == 'ativo') ? 'selected' : '' }}>Ativo</option>
                <option value="inativo" {{ (isset($colaborador) && $colaborador->status == 'inativo') ? 'selected' : '' }}>Inativo</option>
                <option value="ferias" {{ (isset($colaborador) && $colaborador->status == 'ferias') ? 'selected' : '' }}>Férias</option>
            </select>
        </div>
        <div class="form-group" style="flex:1;">
            <label>Vínculo</label>
            <select name="vinculo" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
                <option value="clt" {{ (isset($colaborador) && $colaborador->vinculo == 'clt') ? 'selected' : '' }}>CLT</option>
                <option value="pj" {{ (isset($colaborador) && $colaborador->vinculo == 'pj') ? 'selected' : '' }}>PJ</option>
            </select>
        </div>
    </div>

    <div class="form-row" style="display:flex; gap:20px; margin-bottom:20px;">
        <div class="form-group" style="flex:1;">
            <label>Departamento</label>
            <select name="departamento_id" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
                <option value="">Selecione...</option>
                @foreach($departamentos as $dep)
                <option value="{{ $dep->id }}" {{ (isset($colaborador) && $colaborador->departamento_id == $dep->id) ? 'selected' : '' }}>{{ $dep->nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="flex:1;">
            <label>Cargo</label>
            <select name="cargo_id" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
                <option value="">Selecione...</option>
                @foreach($cargos as $cargo)
                <option value="{{ $cargo->id }}" {{ (isset($colaborador) && $colaborador->cargo_id == $cargo->id) ? 'selected' : '' }}>{{ $cargo->nome }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-row" style="display:flex; gap:20px; margin-bottom:20px;">
        <div class="form-group" style="flex:1;">
            <label>Perfil de Acesso</label>
            <select name="perfil_acesso" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
                <option value="colaborador" {{ (isset($colaborador) && $colaborador->perfil_acesso == 'colaborador') ? 'selected' : '' }}>Colaborador (Padrão)</option>
                <option value="gestor" {{ (isset($colaborador) && $colaborador->perfil_acesso == 'gestor') ? 'selected' : '' }}>Gestor</option>
                <option value="master" {{ (isset($colaborador) && $colaborador->perfil_acesso == 'master') ? 'selected' : '' }}>Master (Admin)</option>
                <option value="mediador" {{ (isset($colaborador) && $colaborador->perfil_acesso == 'mediador') ? 'selected' : '' }}>Mediador</option>
            </select>
        </div>
        <div class="form-group" style="flex:1;">
            <label>Gestor Direto</label>
            <select name="gestor_id" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
                <option value="">Selecione...</option>
                @foreach($gestores as $g)
                <option value="{{ $g->id }}" {{ (isset($colaborador) && $colaborador->gestor_id == $g->id) ? 'selected' : '' }}>{{ $g->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-footer" style="text-align: right; margin-top:20px;">
        <button type="submit" class="btn-save" style="background-color:#6a4c7c; color:white; padding:12px 40px; border:none; border-radius:5px; font-weight:bold; cursor:pointer;">
            {{ isset($colaborador) ? 'Atualizar' : 'Salvar' }} Colaborador
        </button>
    </div>
</form>
@endsection