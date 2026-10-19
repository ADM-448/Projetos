<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TalentiPro - @yield('title', 'Plataforma')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $empresaAtiva = auth()->check() ? auth()->user()->empresa : null;
        $corPrimaria = $empresaAtiva->cor_primaria ?? '#6a4c7c';
        $corSecundaria = $empresaAtiva->cor_secundaria ?? '#8b5cf6';
        $corTerciaria = $empresaAtiva->cor_terciaria ?? '#1e1b4b';
        $corFundo = $empresaAtiva->cor_fundo ?? '#f4f6fa';
        $marcaDagua = $empresaAtiva && $empresaAtiva->marca_dagua ? asset('storage/' . ltrim($empresaAtiva->marca_dagua, '/')) : null;
    @endphp
    <style>
        :root {
            --primary-color: {{ $corPrimaria }};
            --primary-color-rgb: {{ implode(',', sscanf($corPrimaria, "#%02x%02x%02x")) }};
            --primary-dark: {{ $corTerciaria }};
            --primary-light: {{ $corSecundaria }};
            --bg-color-system: {{ $corFundo }};
            --brand-topbar: linear-gradient(135deg, {{ $corPrimaria }} 0%, {{ $corSecundaria }} 100%);
            --brand-sidebar-gradient: linear-gradient(180deg, {{ $corPrimaria }} 0%, {{ $corSecundaria }} 50%, {{ $corTerciaria }} 100%);
        }

        /* Força a aplicação das cores customizadas da empresa sobre os estilos estáticos */
        .sidebar {
            background: linear-gradient(180deg, {{ $corPrimaria }} 0%, {{ $corSecundaria }} 50%, {{ $corTerciaria }} 100%) !important;
        }

        .top-bar {
            background: linear-gradient(135deg, {{ $corPrimaria }} 0%, {{ $corSecundaria }} 100%) !important;
        }

        .btn-primary {
            background: linear-gradient(90deg, {{ $corPrimaria }}, {{ $corSecundaria }}) !important;
            border: none !important;
        }

        /* Destaques e títulos da sidebar (substituindo o laranja estático) */
        .menu-header {
            color: rgba(255, 255, 255, 0.75) !important;
            letter-spacing: 0.08em !important;
        }

        .menu-link.active {
            border-left: 4px solid #ffffff !important;
            background-color: rgba(255, 255, 255, 0.22) !important;
        }

        .menu-link:hover {
            background-color: rgba(255, 255, 255, 0.14) !important;
        }

        /* Harmonização dos elementos de destaque com a cor primária da empresa */
        .tab-btn.active {
            color: {{ $corPrimaria }} !important;
            border-bottom-color: {{ $corPrimaria }} !important;
        }

        .tab-btn:hover {
            color: {{ $corPrimaria }} !important;
        }

        /* Fundo do sistema: aplica a cor de fundo escolhida no perfil */
        body, .main-content {
            background-color: {{ $corFundo }} !important;
            background-attachment: fixed !important;
        }

        /* Adaptação dos cards e painéis para não ficarem com branco puro chapado */
        .unified-card, .form-card, .metadata-card, .card-profile, .card-panel, .kpi-card {
            background: rgba(255, 255, 255, 0.90) !important;
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
            border-color: rgba({{ implode(',', sscanf($corPrimaria, "#%02x%02x%02x")) }}, 0.18) !important;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05), 0 2px 6px -1px rgba({{ implode(',', sscanf($corPrimaria, "#%02x%02x%02x")) }}, 0.05) !important;
        }

        @if($marcaDagua)
        .page-content, main.main-content {
            position: relative;
        }
        .page-content::before {
            content: "";
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 480px;
            height: 480px;
            background-image: url('{{ $marcaDagua }}');
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            opacity: 0.05;
            pointer-events: none;
            z-index: 0;
        }
        @endif
    </style>

    @stack('styles')
</head>

<body class="@yield('body-class')">

    {{-- Se a view definir uma sidebar, ela aparece aqui --}}
    @hasSection('sidebar')
        @yield('sidebar')
        {{-- Overlay mobile para fechar sidebar --}}
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
    @endif

    {{-- Conteúdo principal (com ou sem sidebar) --}}
    @hasSection('sidebar')
        <main class="main-content">
            {{-- Header superior --}}
            <header class="top-bar">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="display: flex; align-items: center;">
                        <button class="btn-mobile-menu" onclick="toggleSidebar()">
                            <i class="fa-solid fa-bars"></i>
                        </button>
                        <div class="top-logo">
                            @if(auth()->check() && auth()->user()->empresa)
                                @if(auth()->user()->empresa->logo)
                                    <div style="background: rgba(255, 255, 255, 0.95); border-radius: 8px; padding: 3px 10px; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); max-height: 38px;">
                                        <img src="{{ asset('storage/' . auth()->user()->empresa->logo) }}" 
                                             alt="{{ auth()->user()->empresa->nome }}" 
                                             style="max-height: 28px; max-width: 130px; width: auto; height: auto; object-fit: contain;"
                                             onerror="this.parentElement.style.display='none'; this.parentElement.nextElementSibling.style.display='inline-flex';">
                                    </div>
                                    <span style="display: none; align-items: center; gap: 8px;">
                                        <i class="fa-solid fa-shapes"></i> {{ strtoupper(auth()->user()->empresa->nome) }}
                                    </span>
                                @else
                                    <span style="display: inline-flex; align-items: center; gap: 8px;">
                                        <i class="fa-solid fa-shapes"></i> {{ strtoupper(auth()->user()->empresa->nome) }}
                                    </span>
                                @endif
                            @else
                                <i class="fa-solid fa-brain"></i> TALENTIPRO
                            @endif
                        </div>
                    </div>

                    @if(auth()->check() && auth()->user()->isConsultor() && auth()->user()->empresasConsultoria->count() > 0)
                        <div class="consultor-tenant-switcher" style="display: flex; align-items: center; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.25); border-radius: 8px; padding: 4px 12px;">
                            <i class="fa-solid fa-building" style="color: #f39c12; margin-right: 8px; font-size: 0.95rem;"></i>
                            <form action="{{ route('consultor.selecionar-empresa') }}" method="POST" id="formSwitchEmpresa" style="margin: 0; display: flex; align-items: center; gap: 8px;">
                                @csrf
                                <label for="consultor_empresa_id" style="color: rgba(255,255,255,0.9); font-size: 0.8rem; font-weight: 600; white-space: nowrap; margin: 0;">Empresa:</label>
                                <select name="empresa_id" id="consultor_empresa_id" onchange="document.getElementById('formSwitchEmpresa').submit()" style="background: rgba(0,0,0,0.3); color: #fff; border: 1px solid rgba(255,255,255,0.3); border-radius: 6px; padding: 4px 10px; font-size: 0.85rem; font-weight: 600; cursor: pointer; outline: none;">
                                    @foreach(auth()->user()->empresasConsultoria as $emp)
                                        <option value="{{ $emp->id }}" {{ auth()->user()->empresa_id == $emp->id ? 'selected' : '' }} style="color: #1e293b; background: #ffffff;">
                                            {{ $emp->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    @endif
                </div>

                <div class="header-profile-info" style="display: flex; align-items: center; gap: 20px;">
                    @php
                        $notificacoes = auth()->check() ? auth()->user()->atividades()->where('lida', false)->latest()->take(5)->get() : collect();
                        $unreadCount = auth()->check() ? auth()->user()->atividades()->where('lida', false)->count() : 0;
                    @endphp
                    
                    <div class="notification-wrapper" style="position: relative;">
                        <button class="btn-notification" onclick="document.getElementById('notificationDropdown').classList.toggle('show')" style="background: none; border: none; color: white; font-size: 1.3rem; cursor: pointer; position: relative;">
                            <i class="fa-solid fa-bell"></i>
                            @if($unreadCount > 0)
                                <span style="position: absolute; top: -5px; right: -5px; background: #e74c3c; color: white; font-size: 0.65rem; font-weight: bold; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #5a3c6c;">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                            @endif
                        </button>
                        
                        <div id="notificationDropdown" style="display: none; position: absolute; top: 100%; right: -10px; width: 320px; background: white; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.15); margin-top: 15px; z-index: 1000; overflow: hidden; border: 1px solid #e0e0e0;">
                            <div style="padding: 12px 15px; border-bottom: 1px solid #edf2f7; display: flex; justify-content: space-between; align-items: center; background: #fafbfc;">
                                <h4 style="margin: 0; font-size: 0.95rem; font-weight: bold; color: #2d3748;">Notificações</h4>
                                @if($unreadCount > 0)
                                    <form action="{{ route('notificacoes.ler-todas') }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button type="submit" style="background: none; border: none; color: #3498db; font-size: 0.8rem; font-weight: bold; cursor: pointer; padding: 0;">Ler todas</button>
                                    </form>
                                @endif
                            </div>
                            
                            <div style="max-height: 350px; overflow-y: auto;">
                                @forelse($notificacoes as $notif)
                                    <a href="{{ route('notificacoes.ler', $notif->id) }}" style="display: block; padding: 12px 15px; border-bottom: 1px solid #edf2f7; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='white'">
                                        <h5 style="margin: 0 0 5px 0; font-size: 0.9rem; font-weight: bold; color: #2d3748;">{{ $notif->titulo }}</h5>
                                        @if($notif->descricao)
                                            <p style="margin: 0 0 5px 0; font-size: 0.8rem; color: #64748b; line-height: 1.3;">{{ Str::limit($notif->descricao, 60) }}</p>
                                        @endif
                                        <span class="timeago" data-date="{{ $notif->created_at->toIso8601String() }}" style="font-size: 0.75rem; color: #a0aec0;">
                                            {{ $notif->created_at->diffForHumans() }}
                                        </span>
                                    </a>
                                @empty
                                    <div style="padding: 20px; text-align: center; color: #a0aec0; font-size: 0.9rem;">
                                        Nenhuma notificação nova.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div style="color: white; font-weight: bold; text-align: right;">
                        <div>Olá, {{ auth()->user()->name }}</div>
                        <div style="font-size: 0.8em; font-weight: normal; opacity: 0.8;">{{ ucfirst(auth()->user()->perfil_acesso->value) }}</div>
                    </div>
                </div>
            </header>

            {{-- Área de conteúdo --}}
            <div class="page-content">
                {{-- Mensagens Flash --}}
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
                    </div>
                @endif
                @if(session('warning'))
                    <div class="alert alert-warning">
                        <i class="fa-solid fa-triangle-exclamation"></i> {{ session('warning') }}
                    </div>
                @endif
                @if($errors->any() && !$errors->has('email') && !$errors->has('password'))
                    <div class="alert alert-danger">
                        <ul style="margin: 0; padding-left: 20px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    @else
        {{-- Sem sidebar (páginas públicas como login) --}}
        @if(session('success'))
            <div class="alert alert-success" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif

        @yield('content')
    @endif

    @hasSection('sidebar')
        @if(auth()->check() && auth()->user()->perfil_acesso->value !== 'colaborador')
            @include('layouts.partials.gravacao-widget')
        @endif
    @endif

    @stack('scripts')

    {{-- Modal Global de Alerta/Confirmação --}}
    <div id="globalModal" style="display: none; position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 9999; padding: 20px; opacity: 0; transition: opacity 0.25s ease;">
        <div id="globalModalCard" class="form-card" style="max-width: 420px; margin-bottom: 0; padding: 35px 30px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border-radius: 16px; position: relative; transform: scale(0.9); transition: transform 0.25s ease; background: white; border: 1px solid #e0e0e0;">
            <button type="button" onclick="closeGlobalModal()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.25rem; color: #b2bec3; cursor: pointer; transition: 0.2s;" onmouseover="this.style.color='#636e72'" onmouseout="this.style.color='#b2bec3'">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div id="globalModalIcon" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 2rem;">
                <i class="fa-solid fa-circle-info"></i>
            </div>

            <h3 id="globalModalTitle" style="font-size: 1.25rem; font-weight: bold; color: #2d3748; margin-bottom: 10px;">Título</h3>
            <p id="globalModalMessage" style="color: #64748b; font-size: 0.95rem; margin-bottom: 25px; line-height: 1.5; text-align: center; white-space: pre-line;"></p>

            <div style="display: flex; gap: 15px; justify-content: center;">
                <button id="globalModalCancelBtn" type="button" onclick="closeGlobalModal()" class="btn-secondary" style="flex: 1; padding: 12px; font-weight: bold; border-radius: 8px; border: 1px solid #ccc; font-size: 14px; justify-content: center; display: none; cursor: pointer;">Cancelar</button>
                <button id="globalModalConfirmBtn" type="button" class="btn-primary" style="flex: 1; padding: 12px; font-weight: bold; border-radius: 8px; font-size: 14px; justify-content: center; cursor: pointer;">Confirmar</button>
            </div>
        </div>
    </div>

    <script>
        let globalModalCallback = null;

        function showGlobalModal(options) {
            const modal = document.getElementById('globalModal');
            const card = document.getElementById('globalModalCard');
            const iconDiv = document.getElementById('globalModalIcon');
            const titleEl = document.getElementById('globalModalTitle');
            const msgEl = document.getElementById('globalModalMessage');
            const cancelBtn = document.getElementById('globalModalCancelBtn');
            const confirmBtn = document.getElementById('globalModalConfirmBtn');
            
            titleEl.textContent = options.title || 'Confirmação';
            msgEl.textContent = options.message || '';
            
            iconDiv.innerHTML = '';
            const icon = document.createElement('i');
            if (options.type === 'danger') {
                icon.className = 'fa-solid fa-triangle-exclamation';
                iconDiv.style.backgroundColor = 'rgba(231, 76, 60, 0.1)';
                iconDiv.style.color = '#e74c3c';
                confirmBtn.className = 'btn-danger';
                confirmBtn.style.backgroundColor = '#e74c3c';
                confirmBtn.style.color = '#ffffff';
            } else if (options.type === 'success') {
                icon.className = 'fa-solid fa-circle-check';
                iconDiv.style.backgroundColor = 'rgba(46, 204, 113, 0.1)';
                iconDiv.style.color = '#2ecc71';
                confirmBtn.className = 'btn-primary';
                confirmBtn.style.backgroundColor = '#2ecc71';
                confirmBtn.style.color = '#ffffff';
            } else { // info
                icon.className = 'fa-solid fa-circle-info';
                iconDiv.style.backgroundColor = 'rgba(106, 76, 124, 0.1)';
                iconDiv.style.color = '#6a4c7c';
                confirmBtn.className = 'btn-primary';
                confirmBtn.style.backgroundColor = '#6a4c7c';
                confirmBtn.style.color = '#ffffff';
            }
            iconDiv.appendChild(icon);
            
            if (options.showCancel) {
                cancelBtn.style.display = 'flex';
                cancelBtn.textContent = options.cancelText || 'Cancelar';
            } else {
                cancelBtn.style.display = 'none';
            }
            
            confirmBtn.textContent = options.confirmText || 'OK';
            globalModalCallback = options.onConfirm || null;
            
            confirmBtn.onclick = function() {
                closeGlobalModal();
                if (globalModalCallback) {
                    globalModalCallback();
                }
            };
            
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.style.opacity = '1';
                card.style.transform = 'scale(1)';
            }, 10);
        }

        function closeGlobalModal() {
            const modal = document.getElementById('globalModal');
            const card = document.getElementById('globalModalCard');
            modal.style.opacity = '0';
            card.style.transform = 'scale(0.9)';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 250);
        }

        window.showAlert = function(message, title = 'Aviso', type = 'info') {
            showGlobalModal({
                title: title,
                message: message,
                type: type,
                showCancel: false,
                confirmText: 'OK'
            });
        };

        window.showConfirm = function(message, onConfirm, title = 'Confirmar Ação', type = 'danger') {
            showGlobalModal({
                title: title,
                message: message,
                type: type,
                showCancel: true,
                confirmText: 'Confirmar',
                cancelText: 'Cancelar',
                onConfirm: onConfirm
            });
        };

        // Interceptador global para elementos com data-confirm
        document.addEventListener('click', function(e) {
            let target = e.target.closest('[data-confirm]');
            if (target) {
                const form = target.closest('form');
                if (form && target.type === 'submit') {
                    if (form.dataset.confirmed === 'true') {
                        return;
                    }
                    e.preventDefault();
                    const message = target.getAttribute('data-confirm');
                    const title = target.getAttribute('data-confirm-title') || 'Confirmar Ação';
                    const type = target.getAttribute('data-confirm-type') || 'danger';
                    
                    window.showConfirm(message, function() {
                        form.dataset.confirmed = 'true';
                        target.click();
                    }, title, type);
                } else if (target.tagName === 'A' && target.dataset.confirmed !== 'true') {
                    e.preventDefault();
                    const message = target.getAttribute('data-confirm');
                    const title = target.getAttribute('data-confirm-title') || 'Confirmar Ação';
                    const type = target.getAttribute('data-confirm-type') || 'info';
                    
                    window.showConfirm(message, function() {
                        target.dataset.confirmed = 'true';
                        target.click();
                    }, title, type);
                }
            }
        });

        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.hasAttribute('data-confirm') && form.dataset.confirmed !== 'true') {
                e.preventDefault();
                const message = form.getAttribute('data-confirm');
                const title = form.getAttribute('data-confirm-title') || 'Confirmar Ação';
                const type = form.getAttribute('data-confirm-type') || 'danger';
                
                window.showConfirm(message, function() {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }, title, type);
            }
        });

        // Fechar modal ao clicar fora dele
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('globalModal');
            if (event.target === modal) {
                closeGlobalModal();
            }

            // Fechar dropdown de notificações
            const notificationDropdown = document.getElementById('notificationDropdown');
            const btnNotification = document.querySelector('.btn-notification');
            if (notificationDropdown && notificationDropdown.classList.contains('show')) {
                if (!notificationDropdown.contains(event.target) && !btnNotification.contains(event.target)) {
                    notificationDropdown.classList.remove('show');
                }
            }
        });

        // Script para atualizar datas relativas dinamicamente (timeago)
        function updateTimeAgo() {
            const elements = document.querySelectorAll('.timeago');
            if(elements.length === 0) return;
            
            const formatter = new Intl.RelativeTimeFormat('pt-BR', { numeric: 'auto' });
            
            elements.forEach(el => {
                const dateStr = el.getAttribute('data-date');
                if (!dateStr) return;
                
                const date = new Date(dateStr);
                const now = new Date();
                const diffMs = date.getTime() - now.getTime();
                const diffSeconds = Math.round(diffMs / 1000);
                const diffMinutes = Math.round(diffSeconds / 60);
                const diffHours = Math.round(diffMinutes / 60);
                const diffDays = Math.round(diffHours / 24);
                
                let text = '';
                if (Math.abs(diffSeconds) < 60) {
                    text = formatter.format(diffSeconds, 'second');
                } else if (Math.abs(diffMinutes) < 60) {
                    text = formatter.format(diffMinutes, 'minute');
                } else if (Math.abs(diffHours) < 24) {
                    text = formatter.format(diffHours, 'hour');
                } else {
                    text = formatter.format(diffDays, 'day');
                }
                
                // Capitalize first letter to match Carbon if wanted, or leave as is.
                text = text.charAt(0).toUpperCase() + text.slice(1);
                
                // Evita sobrescrever se for igual
                if(el.textContent.trim() !== text) {
                    el.textContent = text;
                }
            });
        }
        
        // Atualiza a cada 60 segundos
        setInterval(updateTimeAgo, 60000);
        
        // Correção visual do dropdown
        const style = document.createElement('style');
        style.innerHTML = `
            #notificationDropdown.show { display: block !important; }
            .btn-notification:hover { color: #f39c12 !important; }
        `;
        document.head.appendChild(style);

        // ===== SIDEBAR MOBILE =====
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (sidebar) {
                sidebar.classList.toggle('sidebar-open');
                overlay && overlay.classList.toggle('active');
            }
        }

        function closeSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (sidebar) {
                sidebar.classList.remove('sidebar-open');
                overlay && overlay.classList.remove('active');
            }
        }

        // Fecha sidebar ao clicar em links do menu no mobile (exceto os que ativam submenus)
        document.addEventListener('DOMContentLoaded', function() {
            const menuLinks = document.querySelectorAll('.sidebar .menu-link:not([onclick])');
            menuLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        closeSidebar();
                    }
                });
            });

            // Adiciona data-label dinâmico em tabelas para o CSS de cards no mobile
            document.querySelectorAll('.custom-table, .table, .table-solicitacoes, .table-reunioes, .table-custom').forEach(table => {
                const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText.trim());
                table.querySelectorAll('tbody tr').forEach(tr => {
                    Array.from(tr.children).forEach((td, index) => {
                        if (td.hasAttribute('colspan')) return; // Pular mensagens de erro/tabelas vazias
                        if (headers[index] && !td.hasAttribute('data-label')) {
                            td.setAttribute('data-label', headers[index]);
                        }
                    });
                });
            });
        });
    </script>

    <!-- Global SweetAlert2 for modern alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Intercepta todos os alert() do sistema e transforma num modal bonito
        window.alert = function(message) {
            Swal.fire({
                text: message,
                icon: 'info',
                confirmButtonColor: '#6a4c7c',
                confirmButtonText: 'Entendido',
                customClass: {
                    popup: 'swal-modern-popup'
                }
            });
        };
    </script>
    <style>
        .swal-modern-popup {
            border-radius: 16px !important;
            font-family: inherit;
        }
    </style>
</body>

</html>
