@extends('layouts.app')

@section('title', 'Nova Empresa (Tenant)')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title">Cadastrar Nova Empresa</h1>
        <p style="color: #666; font-size: 0.95rem;">Crie um novo tenant e o seu usuário administrador inicial (Master).</p>
    </div>
    <div>
        <a href="{{ route('admin.empresas.index') }}" class="btn-secondary" style="text-decoration: none;">Voltar</a>
    </div>
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 800px;">
    
    @if ($errors->any())
        <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid #e53e3e;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="form-empresa" action="{{ route('admin.empresas.store') }}" method="POST">
        @csrf
        
        <h3 style="margin-bottom: 20px; color: #2980b9; border-bottom: 1px solid #eee; padding-bottom: 10px;">1. Dados da Empresa</h3>
        
        <div class="form-group">
            <label class="form-label" for="nome">Nome Fantasia <span style="color: red;">*</span></label>
            <input type="text" id="nome" name="nome" class="form-control" value="{{ old('nome') }}" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label" for="cnpj">CNPJ <span style="color: red;">*</span></label>
                <input type="text" id="cnpj" name="cnpj" class="form-control" value="{{ old('cnpj') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="razao_social">Razão Social</label>
                <input type="text" id="razao_social" name="razao_social" class="form-control" value="{{ old('razao_social') }}">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label" for="email">E-mail de Contato <span style="color: red;">*</span></label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="telefone">Telefone</label>
                <input type="text" id="telefone" name="telefone" class="form-control" value="{{ old('telefone') }}">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label" for="max_usuarios">Limite de Usuários <span style="color: red;">*</span></label>
                <input type="number" id="max_usuarios" name="max_usuarios" class="form-control" value="{{ old('max_usuarios', 10) }}" min="1" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="ativa" {{ old('status') == 'ativa' ? 'selected' : '' }}>Ativa</option>
                    <option value="suspensa" {{ old('status') == 'suspensa' ? 'selected' : '' }}>Suspensa</option>
                </select>
            </div>
        </div>

        <h3 style="margin-top: 40px; margin-bottom: 20px; color: #8e44ad; border-bottom: 1px solid #eee; padding-bottom: 10px;">2. Usuário Administrador (Master)</h3>
        
        <div class="form-group">
            <label class="form-label" for="master_name">Nome do Gestor Master <span style="color: red;">*</span></label>
            <input type="text" id="master_name" name="master_name" class="form-control" value="{{ old('master_name') }}" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="master_email">E-mail de Login do Master <span style="color: red;">*</span></label>
            <input type="email" id="master_email" name="master_email" class="form-control" value="{{ old('master_email') }}" required>
            <small style="color: #666;">Este e-mail será usado para o login inicial da empresa.</small>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label" for="master_password">Senha Provisória <span style="color: red;">*</span></label>
                <input type="password" id="master_password" name="master_password" class="form-control" required minlength="8">
            </div>
            <div class="form-group">
                <label class="form-label" for="master_password_confirmation">Confirmar Senha <span style="color: red;">*</span></label>
                <input type="password" id="master_password_confirmation" name="master_password_confirmation" class="form-control" required minlength="8">
            </div>
        </div>

        <div style="margin-top: 30px; display: flex; justify-content: flex-end;">
            <button type="submit" class="btn-primary" style="padding: 12px 24px; font-size: 1rem;">
                <i class="fa-solid fa-save"></i> Criar Empresa e Master
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Inicializa rascunho local (UX RF-80) para evitar perda de dados
        if(typeof FormDraft !== 'undefined') {
            FormDraft.init('#form-empresa', 'draft_empresa');
        }
    });
</script>
@endpush
@endsection
