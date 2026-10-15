@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="login-page">
    <div class="login-card">
        <div class="login-logo">
            <i class="fa-solid fa-brain"></i> TALENTIPRO
        </div>
        <p class="login-subtitle">Plataforma de Gestão de Talentos</p>

        {{-- Erro global de autenticação (credenciais inválidas ou rate limit) --}}
        @error('login')
            <div class="login-error-global">
                <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
            </div>
        @enderror

        <form action="{{ route('login') }}" method="POST" class="login-form">
            @csrf
            @if(request()->has('redirect_to'))
                <input type="hidden" name="redirect_to" value="{{ request('redirect_to') }}">
            @endif

            <div class="form-group">
                <label for="login">E-mail ou CPF</label>
                <input 
                    type="text" 
                    id="login" 
                    name="login" 
                    value="{{ old('login') }}" 
                    required 
                    autofocus 
                    placeholder="E-mail ou CPF"
                    class="@error('login') is-invalid @enderror"
                >
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <div class="password-container">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        placeholder="Sua senha"
                    >
                    <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility()" aria-label="Mostrar/ocultar senha">
                        <i class="fa-solid fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Lembrar de mim</label>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%;">
                <i class="fa-solid fa-right-to-bracket"></i> Entrar
            </button>

            <div style="margin-top: 20px; text-align: center; font-size: 0.85rem; color: #64748b;">
                <p style="margin-bottom: 5px;"><strong>Esqueceu sua senha?</strong></p>
                <p style="line-height: 1.4;">
                    Se você não possui um e-mail cadastrado ou não consegue acessar, 
                    solicite a alteração de senha ao seu Gestor ou setor de RH.
                </p>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }

    // Auto-dismiss flash message
    setTimeout(() => {
        const flash = document.querySelector('.alert-success');
        if (flash) flash.style.display = 'none';
    }, 5000);

    // Máscara dinâmica para CPF caso usuário comece a digitar números
    const loginInput = document.getElementById('login');
    if(loginInput) {
        loginInput.addEventListener('input', function(e) {
            let v = e.target.value;
            // Se tiver "@" ou letras, não aplica máscara (trata como e-mail)
            if (/[a-zA-Z@]/.test(v)) return;

            // Se começou a digitar números, tenta formatar como CPF
            if (/^\d/.test(v)) {
                let nums = v.replace(/\D/g, '').slice(0, 11);
                let formatted = nums;
                if (nums.length > 9) {
                    formatted = nums.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
                } else if (nums.length > 6) {
                    formatted = nums.replace(/(\d{3})(\d{3})(\d{1,3})/, "$1.$2.$3");
                } else if (nums.length > 3) {
                    formatted = nums.replace(/(\d{3})(\d{1,3})/, "$1.$2");
                }
                
                if(e.target.value !== formatted) {
                    e.target.value = formatted;
                }
            }
        });
    }
</script>
@endsection
