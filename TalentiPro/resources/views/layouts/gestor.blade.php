<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TalentiPro - @yield('title')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body>

    <nav class="sidebar">
        <ul class="menu-list">
            <li>
                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                    @csrf
                    <a href="#" class="menu-link"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fa-solid fa-arrow-left"></i> Sair
                    </a>
                </form>
            </li>

            <li class="menu-item">
                <a href="{{ route('dashboard') }}"
                    class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <div><i class="fa-solid fa-house"></i> Home</div>
                </a>
            </li>

            {{-- ÁREA PESSOAL (DO PRÓPRIO GESTOR) --}}
            <li class="menu-category" style="color: #ccc; font-size: 12px; margin-top: 15px; padding-left: 15px;">
                PESSOAL</li>

            <li class="menu-item">
                <a href="{{ route('avaliacoes.pendentes') }}"
                    class="menu-link {{ request()->routeIs('avaliacoes.pendentes') ? 'active' : '' }}">
                    <div><i class="fa-solid fa-clipboard-list"></i> Minhas Avaliações</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="{{ route('pdis.meus') }}"
                    class="menu-link {{ request()->routeIs('pdis.meus') ? 'active' : '' }}">
                    <div><i class="fa-solid fa-medal"></i> Meu PDI</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="{{ route('avaliacoes.relatorios.meus') }}"
                    class="menu-link {{ request()->routeIs('avaliacoes.relatorios.meus') ? 'active' : '' }}">
                    <div><i class="fa-solid fa-chart-pie"></i> Meus Resultados</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="{{ route('perfil.index') }}"
                    class="menu-link {{ request()->routeIs('perfil.index') ? 'active' : '' }}">
                    <div><i class="fa-regular fa-user"></i> Meus Dados</div>
                </a>
            </li>

            {{-- ÁREA DE GESTÃO (DA EQUIPE) --}}
            <li class="menu-category" style="color: #ffcc00; font-size: 12px; margin-top: 20px; padding-left: 15px;">
                GESTÃO DE EQUIPE</li>

            <li class="menu-item">
                <a href="{{ route('reunioes.index') }}" class="menu-link {{ request()->routeIs('reunioes.*') ? 'active' : '' }}">
                    <div><i class="fa-solid fa-headset"></i> Reuniões de Feedback (IA)</div>
                </a>
            </li>

            <li class="menu-item">
                <div class="menu-link" onclick="toggleMenu('menu-gestao-av')">
                    <div><i class="fa-solid fa-star"></i> Gestão das Avaliações</div>
                    <i class="fa-solid fa-chevron-down" style="font-size: 0.8em;"></i>
                </div>

                <ul class="submenu" id="menu-gestao-av">
                    {{-- Gestor gerencia equipe --}}
                    <li><a href="{{ route('avaliacoes.index') }}" class="menu-link"><i
                                class="fa-solid fa-caret-right"></i> Avaliações da Equipe</a></li>
                    <li><a href="{{ route('avaliacoes.gerenciar') }}" class="menu-link"><i
                                class="fa-solid fa-caret-right"></i> Gerenciar Avaliações</a></li>
                    <li><a href="{{ route('relatorios.individuais') }}" class="menu-link"><i
                                class="fa-solid fa-caret-right"></i> Relatórios da Equipe</a></li>
                    <li><a href="{{ route('clima.index') }}" class="menu-link"><i
                                class="fa-solid fa-caret-right"></i> Pesquisa de Clima</a></li>
                    <li><a href="#" class="menu-link"><i
                                class="fa-solid fa-caret-right"></i> Teste DISC</a></li>
                </ul>
            </li>

            <li class="menu-item">
                <div class="menu-link" onclick="toggleMenu('menu-gestao-pdi')">
                    <div><i class="fa-solid fa-rocket"></i> PDIs da Equipe</div>
                    <i class="fa-solid fa-chevron-down" style="font-size: 0.8em;"></i>
                </div>
                <ul class="submenu" id="menu-gestao-pdi">
                    <li><a href="{{ route('pdis.index') }}" class="menu-link"><i class="fa-solid fa-caret-right"></i>
                            Gerenciar PDIs</a></li>
                </ul>
            </li>

            <li class="menu-item">
                <a href="{{ route('solicitacoes.index') }}"
                    class="menu-link {{ request()->routeIs('solicitacoes.index') ? 'active' : '' }}">
                    <div><i class="fa-solid fa-envelope"></i> Solicitações</div>
                </a>
            </li>

            @php
                $isMediator = auth()->user()->perfil_acesso == 'mediador' ||
                    \App\Models\AvaliacaoParticipante::where('avaliador_id', auth()->id())
                        ->where('tipo', 'mediador')
                        ->exists();
            @endphp

            @if($isMediator)
                {{-- ÁREA DE MEDIAÇÃO --}}
                <li class="menu-category" style="color: #27ae60; font-size: 12px; margin-top: 20px; padding-left: 15px;">
                    MEDIAÇÃO DE EQUIPE</li>
                <li class="menu-item">
                    <div class="menu-link" onclick="toggleMenu('menu-mediacao')">
                        <div><i class="fa-solid fa-scale-balanced"></i> Mediação</div>
                        <i class="fa-solid fa-chevron-down" style="font-size: 0.8em;"></i>
                    </div>
                    <ul class="submenu" id="menu-mediacao">
                        <li><a href="{{ route('avaliacoes.mediar') }}" class="menu-link"><i
                                    class="fa-solid fa-caret-right"></i> Avaliações para Mediar</a></li>
                        <li><a href="{{ route('avaliacoes.relatorios.mediacao') }}" class="menu-link"><i
                                    class="fa-solid fa-caret-right"></i> Relatórios de Mediação</a></li>
                    </ul>
                </li>
            @endif

        </ul>
    </nav>

    <main class="main-content">
        <header class="top-bar">
            <div style="display: flex; align-items: center;">
                <button class="btn-mobile-menu" onclick="document.querySelector('.sidebar').classList.toggle('sidebar-open')"><i class="fa-solid fa-bars"></i></button>
                <div class="top-logo"><i class="fa-solid fa-brain"></i> TALENTIPRO</div>
            </div>

            <div class="header-profile-info">
                {{-- Notification Icon --}}
                <div class="notification-wrapper" onclick="toggleNotifications()">
                    <i class="fa-solid fa-bell"></i>
                    <span id="notif-badge" class="notification-badge" style="display:none;">0</span>

                    <div id="notif-dropdown" class="notification-dropdown">
                        <div class="notif-header">
                            <span>Notificações</span>
                            <small onclick="marcarTodasLidas(event)" style="cursor:pointer; color: #3b5998;">Ler
                                todas</small>
                        </div>
                        <ul id="notif-list" class="notif-list">
                            <li style="padding:10px; color:#777;">Aguarde...</li>
                        </ul>
                    </div>
                </div>

                <div style="color: white; font-weight: bold; text-align: right;">
                    <div>Olá, {{ auth()->user()->name }}</div>
                    <div style="font-size: 0.8em; font-weight: normal; opacity: 0.8;">
                        {{ ucfirst(auth()->user()->perfil_acesso) }} /
                        {{ auth()->user()->departamento->nome ?? 'Dept' }}
                    </div>
                </div>
            </div>
        </header>

        <div class="page-content">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script>
        function toggleMenu(menuId) {
            var submenu = document.getElementById(menuId);
            if (submenu.style.display === "block") {
                submenu.style.display = "none";
            } else {
                var allSubmenus = document.querySelectorAll('.submenu');
                allSubmenus.forEach(function (item) {
                    item.style.display = 'none';
                });
                submenu.style.display = "block";
            }
        }

        /* Notification Logic (Reused) */
        async function updateNotifCount() {
            try {
                const res = await fetch('{{ url("/api/notificacoes/count") }}');
                const data = await res.json();
                const badge = document.getElementById('notif-badge');
                if (data.count > 0) {
                    badge.innerText = data.count;
                    badge.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                }
            } catch (e) { }
        }

        function toggleNotifications() {
            const dd = document.getElementById('notif-dropdown');
            if (dd.style.display === 'block') {
                dd.style.display = 'none';
            } else {
                dd.style.display = 'block';
                carregarNotificacoes();
            }
        }

        async function carregarNotificacoes() {
            const list = document.getElementById('notif-list');
            list.innerHTML = '<li style="padding:10px;">Carregando...</li>';
            try {
                const res = await fetch('{{ url("/api/notificacoes") }}');
                const data = await res.json();
                list.innerHTML = '';
                if (data.length === 0) {
                    list.innerHTML = '<li style="padding:10px; text-align:center;">Nenhuma notificação.</li>';
                    return;
                }
                data.forEach(n => {
                    const li = document.createElement('li');
                    li.className = 'notif-item ' + (n.lida ? '' : 'unread');
                    li.innerHTML = `<div>${n.mensagem}</div><div style="font-size:10px; color:#999; margin-top:5px;">${new Date(n.created_at).toLocaleString()}</div>`;
                    li.onclick = (e) => {
                        e.stopPropagation();
                        marcarLida(n.id, n.link);
                    };
                    list.appendChild(li);
                });
            } catch (e) {
                list.innerHTML = '<li style="padding:10px;">Erro ao carregar.</li>';
            }
        }

        async function marcarLida(id, link) {
            await fetch('{{ url("/api/notificacoes") }}/' + id + '/ler', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
            updateNotifCount();
            if (link) window.location.href = link;
            else carregarNotificacoes();
        }

        async function marcarTodasLidas(e) {
            e.stopPropagation();
            await fetch('{{ url("/api/notificacoes/ler-todas") }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
            updateNotifCount();
            carregarNotificacoes();
        }

        window.addEventListener('click', function (e) {
            const wrapper = document.querySelector('.notification-wrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                const dd = document.getElementById('notif-dropdown');
                if (dd) dd.style.display = 'none';
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            updateNotifCount();
            setInterval(updateNotifCount, 60000);
        });
    </script>
    <style>
        .header-profile-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .notification-wrapper {
            position: relative;
            cursor: pointer;
            color: white;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #ffcc00;
            color: #333;
            font-size: 10px;
            padding: 2px 5px;
            border-radius: 50%;
            font-weight: bold;
        }

        .notification-dropdown {
            display: none;
            position: absolute;
            top: 30px;
            right: 0;
            width: 300px;
            background: white;
            color: #333;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            overflow: hidden;
            text-align: left;
        }

        @media (max-width: 768px) {
            .notification-dropdown {
                position: fixed;
                top: 70px;
                left: 50%;
                transform: translateX(-50%);
                width: 90%;
                right: auto;
            }
        }

        .notif-header {
            background: #f4f4f4;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: bold;
        }

        .notif-list {
            list-style: none;
            max-height: 300px;
            overflow-y: auto;
            margin: 0;
            padding: 0;
        }

        .notif-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
            cursor: pointer;
        }

        .notif-item:hover {
            background: #f9f9f9;
        }

        .notif-item.unread {
            background: #eef7ff;
            border-left: 3px solid #3b5998;
        }
    </style>
    @stack('scripts')
    {{-- GLOBAL CONFIRMATION MODAL --}}
    <div id="customConfirmModal" class="custom-modal-overlay">
        <div class="custom-modal-content">
            <div class="custom-modal-icon">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 id="customConfirmTitle">Confirmação</h3>
            <p id="customConfirmMessage">Tem certeza que deseja realizar esta ação?</p>

            <div class="custom-modal-actions">
                <button class="btn-cancel" onclick="closeConfirmModal()">Cancelar</button>
                <button class="btn-confirm" id="btnConfirmAction">Confirmar</button>
            </div>
        </div>
    </div>

    <style>
        /* Custom Modal Styles */
        .custom-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.2s;
        }

        .custom-modal-content {
            background: white;
            padding: 25px;
            border-radius: 12px;
            width: 400px;
            max-width: 90%;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            transform: scale(0.9);
            animation: scaleUp 0.2s forwards;
        }

        @keyframes scaleUp {
            to {
                transform: scale(1);
            }
        }

        .custom-modal-icon {
            font-size: 40px;
            color: #f39c12;
            margin-bottom: 15px;
        }

        .custom-modal-content h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 20px;
        }

        .custom-modal-content p {
            color: #666;
            margin-bottom: 25px;
            font-size: 15px;
            line-height: 1.5;
        }

        .custom-modal-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .btn-cancel,
        .btn-confirm {
            padding: 10px 24px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            font-size: 14px;
        }

        .btn-cancel {
            background-color: #f1f2f6;
            color: #57606f;
        }

        .btn-cancel:hover {
            background-color: #e2e6ea;
        }

        .btn-confirm {
            background-color: #e74c3c;
            /* Red for delete actions usually */
            color: white;
        }

        .btn-confirm:hover {
            background-color: #c0392b;
            box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
        }
    </style>

    <script>
        // Global Confirmation Logic
        let confirmCallback = null;

        function showConfirmModal(title, message, callback) {
            document.getElementById('customConfirmTitle').innerText = title || 'Confirmação';
            document.getElementById('customConfirmMessage').innerText = message || 'Tem certeza?';

            const btnConfirm = document.getElementById('btnConfirmAction');

            // Remove old listeners to avoid stacking
            btnConfirm.replaceWith(btnConfirm.cloneNode(true));

            document.getElementById('btnConfirmAction').addEventListener('click', function () {
                closeConfirmModal();
                if (callback) callback();
            });

            document.getElementById('customConfirmModal').style.display = 'flex';
        }

        function closeConfirmModal() {
            document.getElementById('customConfirmModal').style.display = 'none';
        }

        // Close on Esc
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeConfirmModal();
        });

        // Close on click outside
        document.getElementById('customConfirmModal').addEventListener('click', function (e) {
            if (e.target === this) closeConfirmModal();
        });

        // Intercept Forms with 'confirm-delete' class
        document.addEventListener('DOMContentLoaded', function () {
            document.body.addEventListener('submit', function (e) {
                if (e.target.classList.contains('confirm-delete')) {
                    e.preventDefault();

                    // Specific message?
                    const msg = e.target.getAttribute('data-confirm-message') || 'Tem certeza que deseja excluir este item? Esta ação não pode ser desfeita.';

                    showConfirmModal('Excluir Item', msg, function () {
                        // Submit programmatically
                        HTMLFormElement.prototype.submit.call(e.target);
                    });
                }
            });
        });
    </script>
</body>

</html>