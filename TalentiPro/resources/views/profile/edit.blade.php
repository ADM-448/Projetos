@extends('layouts.app')

@section('title', 'Meus Dados')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title">Meus Dados</h1>
        <p style="color: #666; font-size: 0.95rem;">Gerencie suas informações pessoais e visualize seus detalhes profissionais.</p>
    </div>
</div>

@if(session('success'))
    <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 25px; font-weight: 500; border: 1px solid #c3e6cb;">
        <i class="fa-solid fa-circle-check" style="margin-right: 8px;"></i> {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 25px; font-weight: 500; border: 1px solid #f5c6cb;">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<style>
    .profile-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
        align-items: start;
    }

    @media (max-width: 992px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }
    }

    .card-profile {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 700;
        color: #4a5568;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border-radius: 8px;
        border: 2px solid #e2e8f0;
        background-color: #ffffff;
        color: #2d3748;
        font-size: 0.95rem;
        font-weight: 500;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .form-control:focus {
        outline: none;
        border-color: #6a4c7c;
        box-shadow: 0 0 0 3px rgba(106, 76, 124, 0.1);
    }

    .form-control:disabled {
        background-color: #f7fafc;
        color: #718096;
        cursor: not-allowed;
        border-color: #edf2f7;
    }

    .btn-save {
        background: linear-gradient(90deg, #6a4c7c 0%, #4a3b55 100%);
        color: white;
        padding: 12px 30px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1rem;
        font-weight: bold;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(106, 76, 124, 0.2);
    }

    .btn-save:hover {
        opacity: 0.95;
        transform: translateY(-1px);
        box-shadow: 0 6px 15px rgba(106, 76, 124, 0.3);
    }

    .metadata-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 25px;
    }

    .metadata-title {
        font-size: 1.1rem;
        font-weight: 800;
        color: #2d3748;
        margin-bottom: 20px;
        border-bottom: 2px solid #6a4c7c;
        padding-bottom: 10px;
    }

    .metadata-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px dashed #e2e8f0;
        font-size: 0.9rem;
    }

    .metadata-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .metadata-label {
        font-weight: 700;
        color: #718096;
    }

    .metadata-value {
        font-weight: 600;
        color: #2d3748;
        text-align: right;
    }

    .password-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .password-toggle-btn {
        position: absolute;
        right: 12px;
        background: none;
        border: none;
        cursor: pointer;
        color: #a0aec0;
        font-size: 1.1rem;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .password-toggle-btn:hover {
        color: #4a5568;
    }
</style>

<div class="profile-grid">
    <!-- Coluna Esquerda: Formulário de Edição -->
    <div class="card-profile">
        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            <h3 style="font-size: 1.2rem; font-weight: 800; color: #2d3748; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; margin-top: 0;">
                <i class="fa-solid fa-user-gear" style="color: #6a4c7c;"></i> Informações Pessoais
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group" style="grid-column: span 2;">
                    <label for="name">Nome Completo</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required placeholder="Seu nome completo">
                </div>

                <div class="form-group">
                    <label for="email">E-mail Corporativo</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" placeholder="email@empresa.com">
                    @if(empty($user->email))
                        <div style="margin-top: 5px; font-size: 0.85rem; color: #d97706; display: flex; align-items: flex-start; gap: 5px;">
                            <i class="fa-solid fa-circle-info" style="margin-top: 2px;"></i>
                            <span>Você não possui um e-mail cadastrado. Algumas notificações e a recuperação de senha poderão estar indisponíveis.</span>
                        </div>
                    @endif
                </div>

                <div class="form-group">
                    <label for="telefone">WhatsApp / Telefone</label>
                    <input type="text" id="telefone" name="telefone" class="form-control" 
                           value="{{ old('telefone', $user->telefone ? preg_replace('/^(\d{2})(\d)(\d{4})(\d{4})$/', '($1) $2 $3-$4', $user->telefone) : '') }}" 
                           placeholder="(11) 9 9999-9999" maxlength="16">
                </div>
            </div>

            {{-- Apenas perfil MASTER pode editar senha --}}
            @if($user->perfil_acesso->value === 'master')
                <div style="border-top: 1px solid #edf2f7; margin-top: 25px; padding-top: 25px;">
                    <h3 style="font-size: 1.15rem; font-weight: 800; color: #2d3748; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; margin-top: 0;">
                        <i class="fa-solid fa-lock" style="color: #dd6b20;"></i> Segurança & Senha
                    </h3>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="password">Nova Senha <span style="font-weight: normal; color: #718096;">(deixe em branco para manter)</span></label>
                            <div class="password-wrapper">
                                <input type="password" id="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" style="padding-right: 40px;">
                                <button type="button" onclick="togglePasswordVisibility('password', 'password-icon')" class="password-toggle-btn">
                                    <i class="fa-solid fa-eye" id="password-icon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirmar Nova Senha</label>
                            <div class="password-wrapper">
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Repita a nova senha" style="padding-right: 40px;">
                                <button type="button" onclick="togglePasswordVisibility('password_confirmation', 'password-conf-icon')" class="password-toggle-btn">
                                    <i class="fa-solid fa-eye" id="password-conf-icon"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div style="display: flex; justify-content: flex-end; margin-top: 30px; border-top: 1px solid #edf2f7; padding-top: 20px;">
                <button type="submit" class="btn-save">
                    <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </div>

    <!-- Coluna Direita: Dados Profissionais (Read Only) -->
    <div class="metadata-card">
        <h3 class="metadata-title">
            <i class="fa-solid fa-briefcase" style="color: #6a4c7c;"></i> Detalhes Profissionais
        </h3>

        <div class="metadata-item">
            <span class="metadata-label">Cargo</span>
            <span class="metadata-value">{{ $user->cargo->nome ?? 'Não Definido' }}</span>
        </div>

        <div class="metadata-item">
            <span class="metadata-label">Departamento</span>
            <span class="metadata-value">{{ $user->departamento->nome ?? 'Não Definido' }}</span>
        </div>

        <div class="metadata-item">
            <span class="metadata-label">Superior Direto</span>
            <span class="metadata-value">{{ $user->gestor->name ?? 'Diretoria / Master' }}</span>
        </div>

        <div class="metadata-item">
            <span class="metadata-label">Perfil de Acesso</span>
            <span class="metadata-value" style="background: rgba(106, 76, 124, 0.1); color: #6a4c7c; padding: 4px 10px; border-radius: 12px; font-size: 0.78rem; font-weight: bold;">
                {{ ucfirst($user->perfil_acesso->value) }}
            </span>
        </div>

        <div class="metadata-item">
            <span class="metadata-label">Empresa</span>
            <span class="metadata-value">{{ $user->empresa->nome ?? 'SaaS' }}</span>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Mostrar/ocultar senha
    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    // Máscara dinâmica para o telefone
    const inputTelefone = document.getElementById('telefone');
    if (inputTelefone) {
        inputTelefone.addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, '').slice(0, 11);
            if (v.length > 6) {
                v = v.replace(/(\d{2})(\d)(\d{4})(\d{0,4})/, '($1) $2 $3-$4');
            } else if (v.length > 2) {
                v = v.replace(/(\d{2})(\d*)/, '($1) $2');
            }
            e.target.value = v;
        });
    }
</script>
@endpush
