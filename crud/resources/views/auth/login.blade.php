@extends('layouts.app')

@section('content')
<div style="max-width: 450px; margin: 2rem auto;">
    <div class="card">
        <h2 style="margin-top: 0; margin-bottom: 1.5rem; text-align: center;">Acessar Conta</h2>

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" name="password" id="password" class="form-control" required>
                @error('password')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="checkbox-group">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember" style="margin: 0;">Lembrar de mim</label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Entrar</button>
        </form>

        <p style="text-align: center; margin-top: 1.5rem; color: var(--text-muted); font-size: 0.9rem;">
            Ainda não tem conta? <a href="{{ route('register') }}" style="color: var(--primary-color);">Cadastre-se</a>
        </p>
    </div>
</div>
@endsection
