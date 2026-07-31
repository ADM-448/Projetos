<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de Vagas - Dev Pleno</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0f172a;
            --surface-color: #1e293b;
            --primary-color: #3b82f6;
            --primary-hover: #2563eb;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --danger-color: #ef4444;
            --success-color: #10b981;
            --warning-color: #f59e0b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 1000px;
            padding: 2rem;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .navbar .brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }

        .user-nav {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-nav span {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            font-size: 0.95rem;
        }

        .btn-primary { background-color: var(--primary-color); color: white; }
        .btn-primary:hover { background-color: var(--primary-hover); transform: translateY(-1px); }

        .btn-danger { background-color: transparent; color: var(--danger-color); border: 1px solid var(--danger-color); padding: 0.4rem 0.8rem; font-size: 0.85rem; }
        .btn-danger:hover { background-color: var(--danger-color); color: white; }
        
        .btn-secondary { background-color: transparent; color: var(--text-muted); border: 1px solid var(--border-color); padding: 0.4rem 0.8rem; font-size: 0.85rem; }
        .btn-secondary:hover { background-color: var(--border-color); color: white; }

        .card {
            background-color: var(--surface-color);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            padding: 2rem;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }

        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-muted); }
        .form-control {
            width: 100%;
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-family: inherit;
            font-size: 1rem;
            box-sizing: border-box;
        }
        .form-control:focus { outline: none; border-color: var(--primary-color); }
        
        .error-message {
            color: var(--danger-color);
            font-size: 0.85rem;
            margin-top: 0.3rem;
            display: block;
        }

        .checkbox-group { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; }

        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { padding: 1rem; font-weight: 600; color: var(--text-muted); border-bottom: 1px solid var(--border-color); }
        td { padding: 1rem; border-bottom: 1px solid var(--border-color); color: var(--text-main); }

        .badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
        .badge-active { background-color: rgba(16, 185, 129, 0.1); color: var(--success-color); border: 1px solid rgba(16, 185, 129, 0.2); }
        .badge-inactive { background-color: rgba(239, 68, 68, 0.1); color: var(--danger-color); border: 1px solid rgba(239, 68, 68, 0.2); }
        .badge-premium { background-color: rgba(245, 158, 11, 0.15); color: var(--warning-color); border: 1px solid rgba(245, 158, 11, 0.3); margin-left: 0.5rem; }

        .actions { display: flex; gap: 0.5rem; }
        .alert-success {
            padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;
            background-color: rgba(16, 185, 129, 0.1); color: var(--success-color); border: 1px solid rgba(16, 185, 129, 0.2);
        }
        .alert-danger {
            padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;
            background-color: rgba(239, 68, 68, 0.1); color: var(--danger-color); border: 1px solid rgba(239, 68, 68, 0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="navbar">
            <a href="{{ route('vagas.index') }}" class="brand">💼 VagasDev</a>
            <div class="user-nav">
                @auth
                    <span>Olá, <strong>{{ Auth::user()->name }}</strong></span>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-secondary">Sair</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-secondary">Entrar</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Cadastrar</a>
                @endauth
            </div>
        </header>

        @if (session('sucess'))
            <div class="alert-success">{{ session('sucess') }}</div>
        @endif

        @if (session('error'))
            <div class="alert-danger">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>
</body>
</html>
