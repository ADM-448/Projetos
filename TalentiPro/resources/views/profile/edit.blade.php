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
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
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
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
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

                    {{-- Dummys para desarmar o preenchimento automático agressivo do Google Chrome --}}
                    <input type="text" style="display:none" aria-hidden="true">
                    <input type="password" style="display:none" aria-hidden="true">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="password">Nova Senha <span style="font-weight: normal; color: #718096;">(deixe em branco para manter)</span></label>
                            <div class="password-wrapper">
                                <input type="password" id="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" 
                                       autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" style="padding-right: 40px;">
                                <button type="button" onclick="togglePasswordVisibility('password', 'password-icon')" class="password-toggle-btn">
                                    <i class="fa-solid fa-eye" id="password-icon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirmar Nova Senha</label>
                            <div class="password-wrapper">
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Repita a nova senha" 
                                       autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" style="padding-right: 40px;">
                                <button type="button" onclick="togglePasswordVisibility('password_confirmation', 'password-conf-icon')" class="password-toggle-btn">
                                    <i class="fa-solid fa-eye" id="password-conf-icon"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($user->perfil_acesso->value === 'master' && $user->empresa)
                <div style="margin-top: 30px; border-top: 1px solid #edf2f7; padding-top: 25px;">
                    <h3 style="font-size: 1.15rem; font-weight: 800; color: #2d3748; margin-bottom: 6px; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-palette" style="color: #6a4c7c;"></i> Identidade Visual da Empresa
                    </h3>
                    <p style="color: #718096; font-size: 0.85rem; margin-top: 0; margin-bottom: 16px;">
                        Personalize as cores do menu lateral e o logotipo da sua empresa (<strong>{{ $user->empresa->nome }}</strong>).
                    </p>

                    {{-- Logotipo --}}
                    <div style="margin-bottom: 20px;">
                        <label style="font-weight: 700; font-size: 0.9rem; color: #4a5568; display: block; margin-bottom: 8px;">
                            <i class="fa-solid fa-image"></i> Logotipo da Empresa
                        </label>
                        <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 18px;">
                            @if($user->empresa->logo)
                                <div id="profileLogoPreview" style="display: flex; align-items: center; gap: 12px; background: white; padding: 8px 14px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <img src="{{ asset('storage/' . $user->empresa->logo) }}" alt="Logo {{ $user->empresa->nome }}" 
                                         onerror="this.onerror=null; document.getElementById('profileLogoPreview').style.display='none';"
                                         style="max-height: 45px; max-width: 140px; object-fit: contain;">
                                    <label style="display: flex; align-items: center; gap: 6px; font-size: 0.82rem; color: #dc2626; cursor: pointer; font-weight: 600;">
                                        <input type="checkbox" name="remover_logo" value="1"> Remover
                                    </label>
                                </div>
                            @endif

                            <div style="flex: 1; min-width: 220px;">
                                <input type="file" id="empresa_logo" name="empresa_logo" class="form-control" accept="image/png,image/jpeg,image/webp,image/svg+xml" style="padding: 8px 12px;">
                                <small style="color: #718096; font-size: 0.78rem; display: block; margin-top: 4px;">PNG, JPG, WEBP ou SVG (Máx: 3MB)</small>
                            </div>
                        </div>
                    </div>

                    {{-- Cores do Tema (Degradê do Menu / Topo) --}}
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px;">
                        <label style="font-weight: 700; font-size: 0.9rem; color: #4a5568; display: block; margin-bottom: 6px;">
                            <i class="fa-solid fa-brush"></i> Cores do Sistema (Degradê de 3 Tons da Sidebar e Topo)
                        </label>
                        <p style="font-size: 0.82rem; color: #718096; margin-top: 0; margin-bottom: 14px;">
                            Escolha as 3 cores da sua marca para uma transição elegante e profissional em todo o visual do TalentiPro.
                        </p>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="cor_primaria" style="font-weight: 600; font-size: 0.82rem; color: #4a5568;">1. Cor Topo (Início)</label>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <input type="color" id="cor_primaria_picker" value="{{ old('cor_primaria', $user->empresa->cor_primaria ?? '#24a9d6') }}" 
                                           style="width: 40px; height: 38px; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer;"
                                           onchange="document.getElementById('cor_primaria').value = this.value">
                                    <input type="text" id="cor_primaria" name="cor_primaria" class="form-control" 
                                           value="{{ old('cor_primaria', $user->empresa->cor_primaria ?? '#24a9d6') }}" 
                                           placeholder="#24a9d6" onchange="document.getElementById('cor_primaria_picker').value = this.value">
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="cor_secundaria" style="font-weight: 600; font-size: 0.82rem; color: #4a5568;">2. Cor Meio (Transição)</label>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <input type="color" id="cor_secundaria_picker" value="{{ old('cor_secundaria', $user->empresa->cor_secundaria ?? '#5ff771') }}" 
                                           style="width: 40px; height: 38px; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer;"
                                           onchange="document.getElementById('cor_secundaria').value = this.value">
                                    <input type="text" id="cor_secundaria" name="cor_secundaria" class="form-control" 
                                           value="{{ old('cor_secundaria', $user->empresa->cor_secundaria ?? '#5ff771') }}" 
                                           placeholder="#5ff771" onchange="document.getElementById('cor_secundaria_picker').value = this.value">
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="cor_terciaria" style="font-weight: 600; font-size: 0.82rem; color: #4a5568;">3. Cor Base (Sidebar)</label>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <input type="color" id="cor_terciaria_picker" value="{{ old('cor_terciaria', $user->empresa->cor_terciaria ?? '#1e1b4b') }}" 
                                           style="width: 40px; height: 38px; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer;"
                                           onchange="document.getElementById('cor_terciaria').value = this.value">
                                    <input type="text" id="cor_terciaria" name="cor_terciaria" class="form-control" 
                                           value="{{ old('cor_terciaria', $user->empresa->cor_terciaria ?? '#1e1b4b') }}" 
                                           placeholder="#1e1b4b" onchange="document.getElementById('cor_terciaria_picker').value = this.value">
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="cor_fundo" style="font-weight: 600; font-size: 0.82rem; color: #4a5568;">4. Cor Fundo Geral (Body)</label>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <input type="color" id="cor_fundo_picker" value="{{ old('cor_fundo', $user->empresa->cor_fundo ?? '#f4f6fa') }}" 
                                           style="width: 40px; height: 38px; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer;"
                                           onchange="document.getElementById('cor_fundo').value = this.value">
                                    <input type="text" id="cor_fundo" name="cor_fundo" class="form-control" 
                                           value="{{ old('cor_fundo', $user->empresa->cor_fundo ?? '#f4f6fa') }}" 
                                           placeholder="#f4f6fa" onchange="document.getElementById('cor_fundo_picker').value = this.value">
                                </div>
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
