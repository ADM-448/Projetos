@extends('layouts.app')

@section('content')
<div style="max-width: 450px; margin: 2rem auto;">
    <div class="card">
        <h2 style="margin-top: 0; margin-bottom: 1.5rem; text-align: center;">Criar Nova Conta</h2>

        <form action="{{ route('register') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name">Nome Completo</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required autofocus>
                @error('name')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
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

            <div class="form-group">
                <label for="password_confirmation">Confirmar Senha</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Cadastrar</button>
        </form>

        <p style="text-align: center; margin-top: 1.5rem; color: var(--text-muted); font-size: 0.9rem;">
            Já possui uma conta? <a href="{{ route('login') }}" style="color: var(--primary-color);">Entrar</a>
        </p>
    </div>
</div>
@endsection
