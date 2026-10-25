@extends('layouts.app')
@section('hide_sidebar', '1')

@section('title', 'Acessar Conta - IntalenTI Vagas')

@push('styles')
<style>
    .login-container {
        max-width: 460px;
        margin: 0 auto;
    }
    .pl-auth-card {
        background: #ffffff;
        border: 1px solid var(--pl-border);
        border-radius: var(--pl-radius-lg);
        padding: 36px 36px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }
    .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--pl-dark);
        margin-bottom: 6px;
    }
    .form-control {
        border-radius: 8px;
        border: 1px solid var(--pl-border);
        font-size: 0.9rem;
        padding: 10px 14px;
        color: var(--pl-dark);
        background-color: #ffffff;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .form-control:focus {
        border-color: var(--pl-green);
        box-shadow: 0 0 0 3px rgba(0, 170, 69, 0.15);
        outline: none;
    }
    .password-toggle-btn {
        background: none;
        border: none;
        cursor: pointer;
        color: #94a3b8;
        padding: 0 12px;
        display: flex;
        align-items: center;
        transition: color 0.15s ease;
    }
    .password-toggle-btn:hover {
        color: var(--pl-dark);
    }
</style>
@endpush

@section('content')
<div class="container py-5 login-container">
    <div class="pl-auth-card">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 52px; height: 52px; background: var(--pl-green-soft); color: var(--pl-green);">
                <i class="fa-solid fa-arrow-right-to-bracket fs-4"></i>
            </div>
            <h3 class="fw-bold mb-1" style="color: var(--pl-dark); letter-spacing: -0.02em;">Acessar Conta</h3>
            <p class="text-muted small">Informe suas credenciais para gerenciar suas vagas ou candidaturas</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- E-mail -->
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 8px 0 0 8px; border-color: var(--pl-border);">
                        <i class="fa-regular fa-envelope"></i>
                    </span>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control border-start-0 @error('email') is-invalid @enderror" placeholder="seu@email.com" required autofocus style="border-radius: 0 8px 8px 0;">
                </div>
                @error('email')
                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Senha -->
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <label for="password" class="form-label">Senha</label>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 8px 0 0 8px; border-color: var(--pl-border);">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" id="password" name="password" class="form-control border-start-0 border-end-0 @error('password') is-invalid @enderror" placeholder="••••••••" required>
                    <button class="btn btn-outline-secondary password-toggle-btn border" type="button" onclick="toggleLoginPass()" style="border-radius: 0 8px 8px 0; border-color: var(--pl-border) !important;">
                        <i class="fa-regular fa-eye" id="passIcon"></i>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Lembrar-me -->
            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label text-muted small" for="remember">Lembrar-me neste computador</label>
            </div>

            <button type="submit" class="btn btn-pl-primary w-100 py-2 fs-6">
                <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Entrar
            </button>
        </form>

        <div class="text-center mt-4 pt-3 border-top">
            <span class="text-muted small">Ainda não tem conta?</span>
            <a href="{{ route('register') }}" class="fw-bold ms-1" style="color: var(--pl-green); text-decoration: none;">Cadastre-se grátis</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleLoginPass() {
        const input = document.getElementById('password');
        const icon = document.getElementById('passIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endpush
