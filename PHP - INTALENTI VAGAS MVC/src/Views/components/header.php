<header class="main-header">
    <div class="header-brand">
        <?php
        $homeLink = BASE_URL . "dashboard";
        if (isset($_SESSION['tipo_usuario']) || isset($_SESSION['user_type'])) {
            $type = $_SESSION['tipo_usuario'] ?? $_SESSION['user_type'];
            if ($type == 1) {
                $homeLink = BASE_URL . "vagas";
            }
        }
        ?>
        <a href="<?= $homeLink ?>">
            <span style="font-size: 1.5rem; color: var(--primary-color); font-weight: bold;">IntalenTI Vagas</span>
        </a>
    </div>

    <!-- Toggle Mobile -->
    <button class="mobile-menu-toggle" id="mobile-menu-toggle">
        <i class='bx bx-menu'></i>
    </button>

    <?php if (isset($_SESSION['user_id'])): ?>
        <nav class="header-nav" id="header-nav">
            <ul>
                <?php
                $tipo = $_SESSION['tipo_usuario'] ?? $_SESSION['user_type'] ?? 0;

                if ($tipo == 3): // ADMIN 
                ?>
                    <li><a href="<?= BASE_URL ?>prefeitura/dashboard">Painel</a></li>
                    <li><a href="<?= BASE_URL ?>admin/empresas">Gestão</a></li>
                    <!-- Links antigos
                    <li><a href="<?= BASE_URL ?>admin/empregadores">Empregadores</a></li>
                    <li><a href="<?= BASE_URL ?>admin/usuarios">Usuários</a></li>
                    <li><a href="<?= BASE_URL ?>admin/planos">Planos</a></li>
                    -->
                    <li><a href="<?= BASE_URL ?>auth/logout">Sair</a></li>

                <?php elseif ($tipo == 2): // RH 
                    $empresaModelHeader = new \src\Models\Empresa();
                    $isPrefeitura = $empresaModelHeader->isUserPrefeitura($_SESSION['user_id']);
                ?>
                    <?php if ($isPrefeitura): ?>
                        <li><a href="<?= BASE_URL ?>prefeitura/dashboard">Painel</a></li>
                    <?php endif; ?>
                    <?php if (!$isPrefeitura): ?>
                        <li><a href="<?= BASE_URL ?>dashboard">Painel</a></li>
                    <?php endif; ?>
                    <li><a href="<?= BASE_URL ?>dashboard/vagas">Minhas Vagas</a></li>
                    <li><a href="<?= BASE_URL ?>empresa/equipe">Minha Equipe</a></li>
                    <li><a href="<?= BASE_URL ?>empresa/dados">Dados da Empresa</a></li>
                    <li><a href="<?= BASE_URL ?>perfil">Meu Perfil</a></li>
                    <li><a href="<?= BASE_URL ?>auth/logout">Sair</a></li>

                <?php elseif ($tipo == 1): // CANDIDATO 
                ?>
                    <li><a href="<?= BASE_URL ?>vagas">Buscar Vagas</a></li>
                    <li><a href="<?= BASE_URL ?>curriculo">Meu Currículo</a></li>
                    <li><a href="<?= BASE_URL ?>candidaturas">Minhas Candidaturas</a></li>
                    <li><a href="<?= BASE_URL ?>perfil">Meu Perfil</a></li>
                    <li><a href="<?= BASE_URL ?>auth/logout">Sair</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="header-user-actions" style="display: flex; align-items: center; gap: 20px;">

            <!-- SINO DE NOTIFICAÇÕES -->
            <div class="notification-container" style="position: relative; cursor: pointer;">
                <div id="notif-bell" style="position: relative;">
                    <i class='bx bxs-bell' style="font-size: 24px; color: #581c21;"></i> <!-- Cor #581c21 do tema -->
                    <span id="notif-badge" style="position: absolute; top: -5px; right: -5px; background: #dc3545; color: #fff; border-radius: 50%; padding: 2px 6px; font-size: 10px; display: none;">0</span>
                </div>

                <!-- Dropdown -->
                <div id="notif-dropdown" class="dropdown-menu shadow" style="display: none; position: absolute; right: 0; top: 35px; width: 320px; background: #fff; z-index: 1000; border: 1px solid #ddd; border-radius: 8px;">
                    <div class="dropdown-header" style="padding: 10px 15px; border-bottom: 1px solid #eee; font-weight: bold; color: #581c21;">
                        Notificações
                    </div>
                    <div id="notif-list" style="max-height: 300px; overflow-y: auto;">
                        <div style="padding: 20px; text-align: center; color: #999;">Carregando...</div>
                    </div>
                </div>
            </div>

            <div class="header-welcome">
                <?php
                $full_name = $_SESSION['user_name'] ?? $_SESSION['name'] ?? 'Usuário';
                $first_name = explode(' ', trim($full_name))[0];
                ?>
                <span style="font-weight: 500; color: #444;">Olá, <?= htmlspecialchars($first_name) ?></span>
            </div>
        </div>
    <?php else: ?>
        <nav class="header-nav">
            <ul>
                <li><a href="<?= BASE_URL ?>auth/login">Login</a></li>
                <li><a href="<?= BASE_URL ?>auth/register">Cadastro</a></li>
            </ul>
        </nav>
    <?php endif; ?>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile Menu
        const mobileToggle = document.getElementById('mobile-menu-toggle');
        if (mobileToggle) {
            mobileToggle.addEventListener('click', function() {
                document.getElementById('header-nav')?.classList.toggle('is-open');
            });
        }

        // --- NOTIFICAÇÕES ---
        const bell = document.getElementById('notif-bell');
        const badge = document.getElementById('notif-badge');
        const dropdown = document.getElementById('notif-dropdown');
        const list = document.getElementById('notif-list');
        const baseUrl = '<?= BASE_URL ?>'; // Injeta via PHP

        // Função para carregar
        function checkNotifications() {
            fetch(baseUrl + 'notificacao/check')
                .then(res => res.json())
                .then(data => {
                    // Check Force Logout
                    if (data.force_logout) {
                        window.location.href = baseUrl + 'auth/login?msg=access_revoked';
                        return;
                    }

                    // Atualiza Badge
                    if (data.nao_lidas > 0) {
                        badge.innerText = data.nao_lidas;
                        badge.style.display = 'block';
                        bell.querySelector('i').classList.add('bx-tada'); // Animação
                    } else {
                        badge.style.display = 'none';
                        bell.querySelector('i').classList.remove('bx-tada');
                    }

                    // Atualiza Lista (somente se dropdown estiver aberto ou para cachear?)
                    // Vamos atualizar o HTML da lista localmente para usar quando abrir
                    renderList(data.lista);
                })
                .catch(err => console.error('Erro notificacao:', err));
        }

        function renderList(items) {
            if (!items || items.length === 0) {
                list.innerHTML = '<div style="padding: 20px; text-align: center; color: #999;">Nenhuma notificação recente.</div>';
                return;
            }

            let html = '';
            items.forEach(item => {
                const bg = item.lida == 0 ? '#f8f9fa' : '#fff'; // Destaque para não lidas
                const date = new Date(item.criada_em).toLocaleString('pt-BR');

                // Se o item tiver link, usa ele, senão #
                const link = item.link && item.link !== '' ? item.link : '#';

                html += `
                <a href="${link}" class="notif-item" style="display: block; padding: 10px 15px; border-bottom: 1px solid #eee; text-decoration: none; color: #333; background: ${bg}; transition: background 0.2s;">
                    <div style="font-size: 0.9rem;">${item.mensagem}</div>
                    <div style="font-size: 0.75rem; color: #999; margin-top: 5px;">${date}</div>
                </a>
                `;
            });
            list.innerHTML = html;
        }

        // Click no Sino
        if (bell) {
            bell.addEventListener('click', function(e) {
                e.stopPropagation();
                const isOpen = dropdown.style.display === 'block';

                if (isOpen) {
                    dropdown.style.display = 'none';
                } else {
                    dropdown.style.display = 'block';
                    // Marcar como lidas ao abrir
                    fetch(baseUrl + 'notificacao/markRead')
                        .then(() => {
                            badge.style.display = 'none'; // Some badge na hora
                        });
                }
            });

            // Fechar ao clicar fora
            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target) && !bell.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });

            // Polling Inicial e Intervalo
            checkNotifications();
            setInterval(checkNotifications, 30000); // 30s
        }
    });
</script>

<style>
    .notif-item:hover {
        background-color: #f0f0f0 !important;
    }

    /* Box Shadow do dropdown */
    .shadow {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    @media (max-width: 768px) {
        #notif-dropdown {
            position: fixed !important;
            top: 70px !important;
            left: 50% !important;
            right: auto !important;
            transform: translateX(-50%);
            width: 90vw !important;
            max-width: 350px;
        }
    }
</style>