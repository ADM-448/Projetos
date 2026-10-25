<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'IntalenTI Vagas'))</title>
    
    <!-- Google Fonts: Plus Jakarta Sans / Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 & FontAwesome 6 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Peerlist Design System Styles -->
    <style>
        :root {
            --pl-green: #00aa45;
            --pl-green-hover: #00923b;
            --pl-green-soft: #e8f7ee;
            --pl-green-border: rgba(0, 170, 69, 0.25);
            --pl-dark: #0f172a;
            --pl-text-main: #111827;
            --pl-text-muted: #64748b;
            --pl-text-light: #94a3b8;
            --pl-bg: #fafafa;
            --pl-surface: #ffffff;
            --pl-border: #e5e7eb;
            --pl-border-light: #f1f5f9;
            --pl-border-hover: #cbd5e1;
            --pl-chip-bg: #f3f4f6;
            --pl-chip-text: #374151;
            --pl-radius-sm: 8px;
            --pl-radius-md: 12px;
            --pl-radius-lg: 16px;
            --pl-radius-pill: 9999px;
            --pl-shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.04);
            --pl-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.03);
            --pl-shadow-hover: 0 10px 20px -3px rgba(0, 0, 0, 0.06), 0 4px 6px -4px rgba(0, 0, 0, 0.02);
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--pl-bg);
            color: var(--pl-text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
            color: var(--pl-text-main);
            letter-spacing: -0.02em;
        }

        /* Top Navbar */
        .pl-navbar {
            background-color: var(--pl-surface);
            border-bottom: 1px solid var(--pl-border);
            height: 64px;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .pl-logo-badge {
            background: var(--pl-green);
            color: #ffffff;
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            font-weight: 800;
            font-size: 1.15rem;
            letter-spacing: -0.5px;
            box-shadow: 0 2px 6px rgba(0, 170, 69, 0.3);
        }

        .pl-brand-text {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--pl-dark);
            letter-spacing: -0.03em;
        }

        .pl-portal-badge {
            background-color: var(--pl-green-soft);
            color: var(--pl-green);
            font-size: 0.78rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: var(--pl-radius-pill);
            border: 1px solid var(--pl-green-border);
        }

        /* Buttons */
        .btn-pl-primary {
            background-color: var(--pl-green);
            color: #ffffff;
            border: 1px solid var(--pl-green);
            border-radius: var(--pl-radius-pill);
            padding: 8px 18px;
            font-weight: 600;
            font-size: 0.88rem;
            transition: all 0.2s ease;
        }

        .btn-pl-primary:hover {
            background-color: var(--pl-green-hover);
            border-color: var(--pl-green-hover);
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(0, 170, 69, 0.25);
        }

        .btn-pl-secondary {
            background-color: var(--pl-surface);
            color: var(--pl-text-main);
            border: 1px solid var(--pl-border);
            border-radius: var(--pl-radius-pill);
            padding: 8px 16px;
            font-weight: 600;
            font-size: 0.88rem;
            transition: all 0.2s ease;
        }

        .btn-pl-secondary:hover {
            background-color: var(--pl-chip-bg);
            border-color: var(--pl-border-hover);
            color: var(--pl-text-main);
        }

        /* Search input navbar */
        .pl-nav-search {
            position: relative;
            max-width: 320px;
            width: 100%;
        }

        .pl-nav-search input {
            background-color: var(--pl-chip-bg);
            border: 1px solid transparent;
            border-radius: var(--pl-radius-pill);
            padding: 8px 16px 8px 36px;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            width: 100%;
        }

        .pl-nav-search input:focus {
            background-color: #ffffff;
            border-color: var(--pl-green);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 170, 69, 0.15);
        }

        .pl-nav-search i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--pl-text-light);
            font-size: 0.85rem;
        }

        /* Cards */
        .pl-card {
            background: var(--pl-surface);
            border: 1px solid var(--pl-border);
            border-radius: var(--pl-radius-lg);
            transition: all 0.2s ease;
        }

        .pl-card:hover {
            border-color: var(--pl-border-hover);
            box-shadow: var(--pl-shadow-md);
        }

        /* Skill Chips */
        .pl-chip {
            background-color: var(--pl-chip-bg);
            color: var(--pl-chip-text);
            border: 1px solid transparent;
            border-radius: var(--pl-radius-pill);
            font-size: 0.78rem;
            font-weight: 500;
            padding: 4px 10px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.15s ease;
            text-decoration: none;
        }

        .pl-chip:hover {
            background-color: #e5e7eb;
            color: var(--pl-text-main);
        }

        /* Left Sidebar links */
        .pl-sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            color: var(--pl-text-main);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: var(--pl-radius-md);
            transition: all 0.15s ease;
        }

        .pl-sidebar-link:hover {
            background-color: var(--pl-chip-bg);
            color: var(--pl-text-main);
        }

        .pl-sidebar-link.active {
            background-color: var(--pl-green-soft);
            color: var(--pl-green);
        }

        .pl-sidebar-link i {
            width: 20px;
            text-align: center;
            font-size: 1.05rem;
            color: var(--pl-text-muted);
        }

        .pl-sidebar-link.active i {
            color: var(--pl-green);
        }

        /* ===== Compatibilidade com views legadas (mapeia para o design Peerlist) ===== */
        :root {
            --primary-color: var(--pl-green);
            --primary-hover: var(--pl-green-hover);
            --primary-soft: var(--pl-green-soft);
            --secondary-color: var(--pl-dark);
            --accent-color: var(--pl-green);
            --success-color: var(--pl-green);
            --bg-color: var(--pl-bg);
            --surface-color: var(--pl-surface);
            --text-main: var(--pl-text-main);
            --text-muted: var(--pl-text-muted);
            --border-color: var(--pl-border);
            --radius-md: var(--pl-radius-md);
            --radius-lg: var(--pl-radius-lg);
            --shadow-sm: var(--pl-shadow-sm);
            --shadow-md: var(--pl-shadow-md);
        }

        .card-custom {
            background: var(--pl-surface);
            border: 1px solid var(--pl-border);
            border-radius: var(--pl-radius-lg);
            box-shadow: none;
            transition: all 0.2s ease;
        }
        .card-custom:hover { border-color: var(--pl-border-hover); box-shadow: var(--pl-shadow-md); }

        .btn-primary {
            background-color: var(--pl-green);
            border-color: var(--pl-green);
            border-radius: var(--pl-radius-pill);
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .btn-primary:hover, .btn-primary:focus, .btn-primary:active {
            background-color: var(--pl-green-hover) !important;
            border-color: var(--pl-green-hover) !important;
            box-shadow: 0 4px 10px rgba(0, 170, 69, 0.25);
        }
        .btn-outline-primary {
            color: var(--pl-green);
            border-color: var(--pl-green);
            border-radius: var(--pl-radius-pill);
            font-weight: 600;
        }
        .btn-outline-primary:hover { background-color: var(--pl-green); border-color: var(--pl-green); color: #fff; }

        .text-primary { color: var(--pl-green) !important; }
        .bg-primary { background-color: var(--pl-green) !important; }
        .border-primary { border-color: var(--pl-green) !important; }
        .bg-primary-soft { background-color: var(--pl-green-soft) !important; }
        a { color: var(--pl-green); }
        a:hover { color: var(--pl-green-hover); }

        .badge-status { font-size: 0.78rem; font-weight: 600; padding: 6px 12px; border-radius: var(--pl-radius-pill); }
        .form-control:focus, .form-select:focus {
            border-color: var(--pl-green);
            box-shadow: 0 0 0 3px rgba(0, 170, 69, 0.15);
        }
        .form-check-input:checked { background-color: var(--pl-green); border-color: var(--pl-green); }
        .page-link { color: var(--pl-green); }
        .page-item.active .page-link { background-color: var(--pl-green); border-color: var(--pl-green); color: #fff; }

        /* Sidebar global */
        .pl-app-shell { display: flex; align-items: flex-start; gap: 24px; max-width: 1600px; width: 100%; margin: 0 auto; padding: 0 20px; }
        .pl-app-sidebar {
            position: sticky;
            top: 64px;
            width: 220px;
            flex-shrink: 0;
            height: calc(100vh - 64px);
            padding: 24px 0;
            flex-direction: column;
            justify-content: space-between;
        }
        .pl-app-content { flex: 1 1 0; min-width: 0; }

        .text-justify {
            text-align: justify !important;
            text-justify: inter-word !important;
            hyphens: auto;
            -webkit-hyphens: auto;
            word-break: break-word;
        }

        @media (max-width: 768px) {
            .pl-app-shell {
                padding: 0 12px !important;
                gap: 0 !important;
            }
            .pl-navbar {
                height: 58px !important;
            }
            .pl-brand-text {
                font-size: 1.1rem !important;
            }
            .pl-logo-badge {
                width: 30px !important;
                height: 30px !important;
                font-size: 0.95rem !important;
            }
        }

        /* Footer */
        .pl-footer {
            margin-top: auto;
            background: var(--pl-surface);
            border-top: 1px solid var(--pl-border);
            padding: 24px 0;
            color: var(--pl-text-muted);
            font-size: 0.85rem;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Offcanvas Menu Mobile (Hambúrguer) -->
    <div class="offcanvas offcanvas-start border-0 shadow" tabindex="-1" id="plMobileMenu" aria-labelledby="plMobileMenuLabel" style="width: 300px; max-width: 85vw;">
        <div class="offcanvas-header border-bottom px-3 py-3 d-flex align-items-center justify-content-between bg-light">
            <a href="{{ route('home') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                <div class="pl-logo-badge" style="width: 32px; height: 32px; font-size: 0.95rem;">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                <span class="pl-brand-text" style="font-size: 1.15rem;">Peerlist <span style="color: var(--pl-green); font-size: 0.85rem; font-weight: 600;">/ Vagas</span></span>
            </a>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
        </div>

        <div class="offcanvas-body d-flex flex-column justify-content-between p-3">
            <div>
                <!-- Busca Rápida Mobile -->
                <form action="{{ route('vagas.index') }}" method="GET" class="mb-3">
                    <div class="position-relative">
                        <input type="text" name="busca" value="{{ request('busca') }}" class="form-control rounded-pill ps-4 py-2 small" placeholder="Buscar cargo, skill ou empresa..." style="font-size: 0.85rem; background: var(--pl-chip-bg); border: 1px solid var(--pl-border);">
                        <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="right: 14px; top: 12px; font-size: 0.85rem;"></i>
                    </div>
                </form>

                @auth
                    <!-- Card Resumo do Usuário Logado -->
                    <div class="p-3 bg-light rounded-3 mb-3 d-flex align-items-center gap-2 border">
                        <div class="rounded-circle bg-white border d-flex align-items-center justify-content-center text-success fw-bold flex-shrink-0" style="width: 42px; height: 42px; font-size: 1.05rem;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="overflow-hidden">
                            <div class="fw-bold text-dark text-truncate small">{{ Auth::user()->name }}</div>
                            <div class="text-muted text-truncate" style="font-size: 0.74rem;">
                                {{ Auth::user()->isCandidato() ? 'Candidato' : (Auth::user()->isSuperAdmin() ? 'Super Administrador' : (Auth::user()->isPrefeitura() ? 'Gestor Municipal' : 'Empresa / RH')) }}
                            </div>
                        </div>
                    </div>
                @endauth

                <!-- Links de Navegação Mobile -->
                <nav class="d-flex flex-column gap-1">
                    @guest
                        <a href="{{ route('vagas.index') }}" class="pl-sidebar-link {{ request()->routeIs('vagas.index', 'vagas.show', 'home') && !in_array(request('tab'), ['applied', 'saved']) ? 'active' : '' }}">
                            <i class="fa-solid fa-briefcase"></i>
                            <span>Todas as Vagas</span>
                        </a>
                        <div class="d-flex flex-column gap-2 mt-3 pt-3 border-top">
                            <a href="{{ route('login') }}" class="btn btn-pl-secondary btn-sm w-100 text-center">Entrar</a>
                            <a href="{{ route('register') }}" class="btn btn-pl-primary btn-sm w-100 text-center">Cadastre-se</a>
                        </div>
                    @endguest

                    @auth
                        @if(Auth::user()->isCandidato())
                            <a href="{{ route('vagas.index') }}" class="pl-sidebar-link {{ request()->routeIs('vagas.index', 'vagas.show', 'home') && !in_array(request('tab'), ['applied', 'saved']) ? 'active' : '' }}">
                                <i class="fa-solid fa-briefcase"></i>
                                <span>Vagas Disponíveis</span>
                            </a>
                            <a href="{{ route('vagas.index', ['tab' => 'applied']) }}" class="pl-sidebar-link {{ request('tab') == 'applied' ? 'active' : '' }}">
                                <i class="fa-solid fa-paper-plane"></i>
                                <span>Minhas Candidaturas</span>
                            </a>
                            <a href="{{ route('vagas.index', ['tab' => 'saved']) }}" class="pl-sidebar-link {{ request('tab') == 'saved' ? 'active' : '' }}">
                                <i class="fa-regular fa-bookmark"></i>
                                <span>Vagas Salvas</span>
                            </a>
                            <a href="{{ route('curriculo.edit') }}" class="pl-sidebar-link {{ request()->routeIs('curriculo.*') ? 'active' : '' }}">
                                <i class="fa-regular fa-id-card"></i>
                                <span>Meu Currículo</span>
                            </a>
                        @elseif(Auth::user()->isEmpresa())
                            <a href="{{ route('dashboard') }}" class="pl-sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="fa-solid fa-gauge-high"></i>
                                <span>Painel da Empresa</span>
                            </a>
                            <a href="{{ route('vagas.index') }}" class="pl-sidebar-link {{ request()->routeIs('vagas.index') ? 'active' : '' }}">
                                <i class="fa-solid fa-briefcase"></i>
                                <span>Minhas Vagas</span>
                            </a>
                            <a href="{{ route('vagas.create') }}" class="pl-sidebar-link {{ request()->routeIs('vagas.create', 'vagas.edit', 'vagas.candidatos') ? 'active' : '' }}">
                                <i class="fa-solid fa-plus-circle"></i>
                                <span>Publicar Nova Vaga</span>
                            </a>
                            <a href="{{ route('empresa.equipe') }}" class="pl-sidebar-link {{ request()->routeIs('empresa.equipe') ? 'active' : '' }}">
                                <i class="fa-solid fa-users"></i>
                                <span>Equipe / Usuários</span>
                            </a>
                        @elseif(Auth::user()->isSuperAdmin())
                            <a href="{{ route('admin.empresas') }}" class="pl-sidebar-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-shield-halved"></i>
                                <span>Gestão de Portais</span>
                            </a>
                            <a href="{{ route('prefeitura.dashboard') }}" class="pl-sidebar-link {{ request()->routeIs('prefeitura.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-landmark"></i>
                                <span>Observatório</span>
                            </a>
                            <a href="{{ route('vagas.index') }}" class="pl-sidebar-link {{ request()->routeIs('vagas.index', 'vagas.show') ? 'active' : '' }}">
                                <i class="fa-solid fa-briefcase"></i>
                                <span>Todas as Vagas</span>
                            </a>
                        @elseif(Auth::user()->isPrefeitura())
                            <a href="{{ route('prefeitura.dashboard') }}" class="pl-sidebar-link {{ request()->routeIs('prefeitura.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-landmark"></i>
                                <span>Observatório</span>
                            </a>
                            <a href="{{ route('vagas.index') }}" class="pl-sidebar-link {{ request()->routeIs('vagas.index', 'vagas.show') ? 'active' : '' }}">
                                <i class="fa-solid fa-briefcase"></i>
                                <span>Vagas do Município</span>
                            </a>
                        @endif
                    @endauth
                </nav>
            </div>

            @auth
                <div class="pt-3 border-top mt-3">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100 d-flex align-items-center justify-content-center gap-2 py-2 rounded-pill fw-semibold">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            <span>Sair da Conta</span>
                        </button>
                    </form>
                    <div class="text-center text-muted mt-2" style="font-size: 0.72rem;">
                        &copy; {{ date('Y') }} IntalenTI &bull; Peerlist Edition
                    </div>
                </div>
            @endauth
        </div>
    </div>

    <!-- Header Navbar -->
    <header class="pl-navbar d-flex align-items-center">
        <div class="container-fluid px-lg-4 d-flex align-items-center justify-content-between">
            
            <!-- Left: Hambúrguer Mobile + Logo & Portal -->
            <div class="d-flex align-items-center gap-2 gap-md-3">
                <!-- Botão Hambúrguer Mobile (abre o menu lateral) -->
                <button type="button" class="btn btn-light rounded-circle border-0 d-flex d-lg-none align-items-center justify-content-center text-dark p-0" data-bs-toggle="offcanvas" data-bs-target="#plMobileMenu" aria-controls="plMobileMenu" style="width: 38px; height: 38px; background: var(--pl-chip-bg);" title="Abrir menu de navegação">
                    <i class="fa-solid fa-bars" style="font-size: 1.15rem;"></i>
                </button>

                <a href="{{ route('home') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                    <div class="pl-logo-badge">
                        <i class="fa-solid fa-briefcase" style="font-size: 0.95rem;"></i>
                    </div>
                    <span class="pl-brand-text">Peerlist <span style="color: var(--pl-green); font-size: 0.9rem; font-weight: 500;">/ Vagas</span></span>
                </a>

                @if(isset($currentPortal))
                    <span class="pl-portal-badge d-none d-md-inline-flex align-items-center gap-1">
                        <i class="fa-solid fa-landmark"></i> {{ $currentPortal->nome_empresa }}
                    </span>
                @endif
            </div>

            <!-- Center Search (Global) -->
            <form action="{{ route('vagas.index') }}" method="GET" class="pl-nav-search d-none d-md-block">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Buscar por cargo, skill ou empresa..." autocomplete="off">
            </form>

            <!-- Right Actions: Notifications & User -->
            <div class="d-flex align-items-center gap-2">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-pl-secondary btn-sm">Entrar</a>
                    <a href="{{ route('register') }}" class="btn btn-pl-primary btn-sm">Cadastre-se</a>
                @else
                    <!-- Dropdown de Notificações Interativo -->
                    <div class="dropdown">
                        <button type="button" class="btn btn-light rounded-circle border-0 d-flex align-items-center justify-content-center text-muted position-relative" id="btnSininhoNotificacoes" data-bs-toggle="dropdown" aria-expanded="false" style="width: 38px; height: 38px;" title="Notificações">
                            <i class="fa-regular fa-bell" style="font-size: 1.05rem;"></i>
                            @if(isset($notificacoesNaoLidasCount) && $notificacoesNaoLidasCount > 0)
                                <span id="badgeSininhoCount" class="position-absolute bg-danger text-white rounded-pill d-flex align-items-center justify-content-center border border-2 border-white shadow-sm"
                                      style="top: -2px; right: -2px; min-width: 18px; height: 18px; font-size: 0.65rem; font-weight: 700; padding: 0 4px; line-height: 1;">
                                    {{ $notificacoesNaoLidasCount > 9 ? '9+' : $notificacoesNaoLidasCount }}
                                    <span class="visually-hidden">notificações não lidas</span>
                                </span>
                            @endif
                        </button>

                        <div class="dropdown-menu dropdown-menu-end shadow border mt-2 p-0" style="width: 360px; max-width: 90vw; border-radius: var(--pl-radius-md); overflow: hidden;">
                            <div class="d-flex align-items-center justify-content-between p-3 border-bottom bg-light">
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Notificações</h6>
                                    @if(isset($notificacoesNaoLidasCount) && $notificacoesNaoLidasCount > 0)
                                        <span id="badgeHeaderNaoLidas" class="badge bg-success rounded-pill" style="font-size: 0.7rem;">{{ $notificacoesNaoLidasCount }} nova(s)</span>
                                    @endif
                                </div>
                                @if(isset($notificacoesNaoLidasCount) && $notificacoesNaoLidasCount > 0)
                                    <button type="button" id="btnMarcarTodasLidas" class="btn btn-link p-0 text-success small fw-semibold text-decoration-none" style="font-size: 0.78rem;">
                                        Marcar todas como lidas
                                    </button>
                                @endif
                            </div>

                            <div class="notificacoes-scroll" style="max-height: 380px; overflow-y: auto;">
                                @if(isset($notificacoesUsuario) && $notificacoesUsuario->count() > 0)
                                    @foreach($notificacoesUsuario as $notif)
                                        <a href="{{ route('notificacoes.abrir', $notif->id) }}" class="notif-item d-flex align-items-start gap-3 p-3 border-bottom text-decoration-none {{ $notif->lida ? 'bg-white opacity-85' : 'bg-light border-start border-3 border-success' }}" style="transition: background 0.15s ease;">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-1" style="width: 34px; height: 34px; background: {{ str_contains(strtolower($notif->titulo), 'chat') ? '#e0f2fe' : (str_contains(strtolower($notif->titulo), 'status') ? '#fef3c7' : '#dcfce7') }}; color: {{ str_contains(strtolower($notif->titulo), 'chat') ? '#0284c7' : (str_contains(strtolower($notif->titulo), 'status') ? '#d97706' : '#16a34a') }}; font-size: 0.85rem;">
                                                @if(str_contains(strtolower($notif->titulo), 'chat'))
                                                    <i class="fa-solid fa-comments"></i>
                                                @elseif(str_contains(strtolower($notif->titulo), 'status'))
                                                    <i class="fa-solid fa-arrows-rotate"></i>
                                                @elseif(str_contains(strtolower($notif->titulo), 'candidatura'))
                                                    <i class="fa-solid fa-briefcase"></i>
                                                @else
                                                    <i class="fa-solid fa-bell"></i>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <span class="fw-bold text-truncate text-dark small">{{ $notif->titulo }}</span>
                                                    @if(!$notif->lida)
                                                        <span class="notif-unread-dot p-1 bg-success rounded-circle" title="Não lida" style="width: 8px; height: 8px; display: inline-block;"></span>
                                                    @endif
                                                </div>
                                                <p class="text-muted small mb-1 text-truncate" style="font-size: 0.8rem; line-height: 1.35; white-space: normal;">{{ $notif->mensagem }}</p>
                                                <small class="text-secondary" style="font-size: 0.7rem;">{{ $notif->created_at->diffForHumans() }}</small>
                                            </div>
                                        </a>
                                    @endforeach
                                @else
                                    <div class="p-4 text-center text-muted">
                                        <i class="fa-regular fa-bell-slash fa-2x mb-2 text-secondary opacity-50"></i>
                                        <p class="small mb-0">Nenhuma notificação por enquanto.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Dropdown Usuário -->
                    <div class="dropdown">
                        <button class="btn btn-light rounded-circle p-0 border d-flex align-items-center justify-content-center overflow-hidden" type="button" data-bs-toggle="dropdown" style="width: 38px; height: 38px; background: #e0f2fe; color: #0284c7; font-weight: 700;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border p-2 mt-2" style="border-radius: var(--pl-radius-md); min-width: 230px;">
                            <li class="px-3 py-2 border-bottom mb-2">
                                <div class="fw-bold text-dark">{{ Auth::user()->name }}</div>
                                <small class="text-muted">{{ Auth::user()->email }}</small>
                            </li>

                            @if(Auth::user()->isCandidato())
                                <li>
                                    <a class="dropdown-item py-2 rounded" href="{{ route('curriculo.edit') }}">
                                        <i class="fa-regular fa-id-card me-2 text-success"></i> Meu Currículo
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 rounded" href="{{ route('vagas.index', ['tab' => 'applied']) }}">
                                        <i class="fa-solid fa-paper-plane me-2 text-success"></i> Minhas Candidaturas
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 rounded" href="{{ route('vagas.index', ['tab' => 'saved']) }}">
                                        <i class="fa-regular fa-bookmark me-2 text-success"></i> Vagas Salvas
                                    </a>
                                </li>
                            @elseif(Auth::user()->isEmpresa())
                                <li>
                                    <a class="dropdown-item py-2 rounded" href="{{ route('dashboard') }}">
                                        <i class="fa-solid fa-gauge-high me-2 text-success"></i> Painel da Empresa
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 rounded" href="{{ route('vagas.create') }}">
                                        <i class="fa-solid fa-plus-circle me-2 text-success"></i> Publicar Nova Vaga
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 rounded" href="{{ route('empresa.equipe') }}">
                                        <i class="fa-solid fa-users me-2 text-success"></i> Gerenciar Equipe
                                    </a>
                                </li>
                            @elseif(Auth::user()->isAdmin())
                                <li>
                                    <a class="dropdown-item py-2 rounded" href="{{ route('prefeitura.dashboard') }}">
                                        <i class="fa-solid fa-landmark me-2 text-success"></i> Observatório Municipal
                                    </a>
                                </li>
                            @endif

                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger py-2 rounded">
                                        <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Sair
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endguest
            </div>
        </div>
    </header>

    <!-- Alertas Flash -->
    <div class="container-fluid px-lg-4 mt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2" role="alert" style="background-color: var(--pl-green-soft); color: var(--pl-green); border-radius: var(--pl-radius-md);">
                <i class="fa-solid fa-circle-check"></i>
                <div class="fw-semibold">{{ session('success') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2" role="alert" style="border-radius: var(--pl-radius-md);">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div class="fw-semibold">{{ session('error') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('alerta_eliminatoria'))
            <div class="alert alert-warning border-start border-warning border-4 shadow-sm" role="alert" style="border-radius: var(--pl-radius-md);">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-3 text-warning">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Requisito Obrigatório Não Atendido</h6>
                        <p class="mb-0">{{ session('alerta_eliminatoria') }}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Conteúdo Principal -->
    <div class="pl-app-shell flex-grow-1">
        @unless(View::hasSection('hide_sidebar'))
            @include('layouts.partials.sidebar')
        @endunless
        <main class="pl-app-content">
            @yield('content')
        </main>
    </div>

    <!-- Footer minimalista estilo Peerlist -->
    <footer class="pl-footer">
        <div class="container-fluid px-lg-4 text-center">
            <div class="d-flex flex-wrap justify-content-center align-items-center gap-2 gap-md-3 mb-2 small text-muted">
                <a href="#" class="text-decoration-none text-muted">Blog</a>
                <span class="text-muted opacity-50 d-none d-sm-inline">&bull;</span>
                <a href="#" class="text-decoration-none text-muted">Suporte</a>
                <span class="text-muted opacity-50 d-none d-sm-inline">&bull;</span>
                <a href="#" class="text-decoration-none text-muted">Termos de Uso</a>
                <span class="text-muted opacity-50 d-none d-sm-inline">&bull;</span>
                <a href="#" class="text-decoration-none text-muted">Privacidade</a>
                <span class="text-muted opacity-50 d-none d-sm-inline">&bull;</span>
                <span>v2.0 Peerlist Edition</span>
            </div>
            <p class="mb-0 text-muted small">&copy; {{ date('Y') }} IntalenTI Vagas &bull; Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btnMarcarTodas = document.getElementById('btnMarcarTodasLidas');
            if (btnMarcarTodas) {
                btnMarcarTodas.addEventListener('click', function (e) {
                    e.preventDefault();
                    btnMarcarTodas.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Marcando...';
                    btnMarcarTodas.disabled = true;

                    fetch('{{ route("notificacoes.marcarTodasLidas") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.success) {
                            const badgeSininho = document.getElementById('badgeSininhoCount');
                            if (badgeSininho) badgeSininho.remove();

                            const badgeHeader = document.getElementById('badgeHeaderNaoLidas');
                            if (badgeHeader) badgeHeader.remove();

                            btnMarcarTodas.remove();

                            document.querySelectorAll('.notif-unread-dot').forEach(el => el.remove());
                            document.querySelectorAll('.notif-item').forEach(el => {
                                el.classList.remove('bg-light', 'border-start', 'border-3', 'border-success');
                                el.classList.add('bg-white', 'opacity-85');
                            });
                        }
                    })
                    .catch(() => {
                        window.location.reload();
                    });
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
