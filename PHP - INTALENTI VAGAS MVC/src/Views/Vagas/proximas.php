<div class="container" style="max-width: 1200px; margin-top: 20px;">

    <div style="text-align: center; margin-bottom: 20px;">
        <h1 class="page-title" id="titulo-contador">Vagas Próximas (<?= count($vagas_proximas) ?>)</h1>
        <p>Raio de busca: <strong><?= $raio_km ?>km</strong> a partir de <?= htmlspecialchars($candidato['cidade']) ?></p>
    </div>

    <div class="view-controls">
        <button class="btn-view active" onclick="switchView('map')">
            <i class='bx bx-map-alt'></i> Mapa
        </button>
        <button class="btn-view" onclick="switchView('match')">
            <i class='bx bxs-radar'></i> Radar
        </button>
    </div>

    <div id="view-map" class="view-section active">
        <div id="map"></div>

        <?php if (empty($vagas_proximas)): ?>
            <div style="text-align:center; padding:40px; color:#666;">
                <i class='bx bx-sad' style="font-size:3rem;"></i><br>
                Nenhuma vaga nova encontrada por perto.
            </div>
        <?php else: ?>
            <div class="job-list">
                <?php foreach ($vagas_proximas as $vaga): ?>
                    <div class="job-card-nearby" id="list-card-<?= $vaga['id'] ?>">
                        <div>
                            <strong><?= htmlspecialchars($vaga['titulo']) ?></strong><br>
                            <small><?= $vaga['confidencial'] ? 'Confidencial' : htmlspecialchars($vaga['nome_empresa']) ?></small>
                        </div>
                        <div style="text-align:right;">
                            <strong style="color:#007bff;"><?= number_format($vaga['distancia_km'], 1) ?> km</strong><br>
                            <a href="<?= BASE_URL ?>vagas/show/<?= $vaga['id'] ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="view-match" class="view-section">
        <div id="match-content-wrapper">
            <?php if (empty($vagas_proximas)): ?>
                <div style="text-align:center; padding:50px;">
                    <h2>Acabaram as vagas próximas!</h2>
                    <p>Tente aumentar o raio ou volte mais tarde.</p>
                </div>
            <?php else: ?>

                <div class="tutorial-text">
                    <i class='bx bx-left-arrow-alt'></i> Pular
                    <span><b>Arraste o cartão</b></span>
                    Gostei <i class='bx bx-right-arrow-alt'></i>
                </div>

                <div class="match-container" id="cardStack">
                    <?php foreach ($vagas_proximas as $vaga): ?>
                        <?php
                        $salario = "A Combinar";
                        if (!empty($vaga['faixa_salarial_min'])) {
                            $salario = "R$ " . number_format($vaga['faixa_salarial_min'], 0, ',', '.');
                        }
                        ?>
                        <div class="tinder-card" data-id="<?= $vaga['id'] ?>">
                            <div class="status-icon like-icon">Gostei</div>
                            <div class="status-icon nope-icon">Pular</div>

                            <div class="card-header-img">
                                <?php if (!empty($vaga['logo_path']) && !$vaga['confidencial']): ?>
                                    <img src="<?= BASE_URL ?>uploads/logos/<?= $vaga['logo_path'] ?>">
                                <?php else: ?>
                                    <i class='bx bxs-briefcase'></i>
                                <?php endif; ?>
                            </div>

                            <div class="card-body">
                                <h2><?= htmlspecialchars($vaga['titulo']) ?></h2>
                                <h4><?= $vaga['confidencial'] ? 'Empresa Confidencial' : htmlspecialchars($vaga['nome_empresa']) ?></h4>

                                <div class="pill-dist">
                                    <i class='bx bx-map'></i> <?= number_format($vaga['distancia_km'], 1) ?> km
                                </div>

                                <p style="font-size:0.85rem; color:#666;">
                                    <i class='bx bx-money'></i> <?= $salario ?><br>
                                    <i class='bx bx-briefcase'></i> <?= htmlspecialchars($vaga['modalidade']) ?>
                                </p>

                                <p style="font-size:0.75rem; color:#999; margin-top:auto;">
                                    Toque no botão azul <b>( i )</b> para ver detalhes completos.
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="controls">
                    <div style="text-align:center">
                        <button class="btn-round btn-nope" onclick="triggerSwipe('left')"><i class='bx bx-x'></i></button>
                        <span style="font-size:0.7rem; color:#dc3545; font-weight:bold;">PULAR</span>
                    </div>

                    <div style="text-align:center; margin-top:-10px;">
                        <button class="btn-round btn-info" onclick="verDetalhesMatch()"><i class='bx bx-info-circle'></i></button>
                        <span style="font-size:0.7rem; color:#17a2b8;">DETALHES</span>
                    </div>

                    <div style="text-align:center">
                        <button class="btn-round btn-like" onclick="triggerSwipe('right')"><i class='bx bx-check'></i></button>
                        <span style="font-size:0.7rem; color:#28a745; font-weight:bold;">APLICAR</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Styles and Scripts -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<style>
    /* --- CONTROLES DE ABA --- */
    .view-controls {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
        justify-content: center;
    }

    .btn-view {
        padding: 10px 20px;
        border: 2px solid var(--primary-color, #007bff);
        background: #fff;
        color: var(--primary-color, #007bff);
        border-radius: 25px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .btn-view.active {
        background: var(--primary-color, #007bff);
        color: #fff;
    }

    .view-section {
        display: none;
    }

    .view-section.active {
        display: block;
    }

    /* --- MAPA --- */
    #map {
        height: 400px;
        width: 100%;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        z-index: 1;
    }

    .job-card-nearby {
        background: #fff;
        padding: 15px;
        border: 1px solid #eee;
        border-radius: 8px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* --- MODO MATCH (COMPACTO) --- */
    .match-container {
        position: relative;
        width: 100%;
        max-width: 360px;
        height: 450px;
        /* Altura fixa e menor */
        margin: 0 auto;
    }

    .tinder-card {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transition: transform 0.3s ease;
        cursor: grab;
        user-select: none;
        border: 1px solid #e0e0e0;
    }

    @keyframes wiggle {

        0%,
        100% {
            transform: rotate(0deg);
        }

        25% {
            transform: rotate(2deg);
        }

        75% {
            transform: rotate(-2deg);
        }
    }

    .wiggle-effect {
        animation: wiggle 0.8s ease-in-out 3;
    }

    .tinder-card:nth-last-child(1) {
        z-index: 3;
    }

    .tinder-card:nth-last-child(2) {
        z-index: 2;
        transform: scale(0.96) translateY(8px);
        opacity: 0.8;
    }

    .tinder-card:nth-last-child(3) {
        z-index: 1;
        transform: scale(0.92) translateY(16px);
        opacity: 0.5;
    }

    .tinder-card:nth-last-child(n+4) {
        display: none;
    }

    .card-header-img {
        height: 100px;
        background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .card-header-img img {
        max-height: 70px;
        max-width: 70%;
        background: #fff;
        padding: 4px;
        border-radius: 6px;
    }

    .card-header-img i {
        font-size: 3rem;
        color: rgba(255, 255, 255, 0.3);
    }

    .card-body {
        padding: 15px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        gap: 5px;
        text-align: center;
    }

    .card-body h2 {
        margin: 0;
        font-size: 1.1rem;
        color: #333;
        line-height: 1.2;
    }

    .card-body h4 {
        margin: 0;
        font-size: 0.9rem;
        color: #666;
        font-weight: normal;
    }

    .pill-dist {
        background: #e3f2fd;
        color: #007bff;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: bold;
        display: inline-block;
        margin: 5px auto;
    }

    .status-icon {
        position: absolute;
        top: 20px;
        font-size: 1.5rem;
        font-weight: bold;
        text-transform: uppercase;
        padding: 5px 10px;
        border-radius: 8px;
        opacity: 0;
        z-index: 10;
        border: 3px solid;
    }

    .like-icon {
        right: 20px;
        color: #28a745;
        border-color: #28a745;
        transform: rotate(15deg);
    }

    .nope-icon {
        left: 20px;
        color: #dc3545;
        border-color: #dc3545;
        transform: rotate(-15deg);
    }

    .controls {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 15px;
    }

    .btn-round {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: none;
        font-size: 1.5rem;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        transition: transform 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-nope {
        background: #fff;
        color: #dc3545;
        border: 2px solid #dc3545;
    }

    .btn-like {
        background: #fff;
        color: #28a745;
        border: 2px solid #28a745;
    }

    .btn-info {
        background: #fff;
        color: #17a2b8;
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
        margin-top: 5px;
        border: 2px solid #17a2b8;
    }

    .btn-round:active {
        transform: scale(0.9);
        background-color: #f8f9fa;
    }

    .tutorial-text {
        text-align: center;
        color: #888;
        font-size: 0.85rem;
        margin-top: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
</style>

<script>
    // =========================================
    // 1. LÓGICA DE NAVEGAÇÃO (COM MEMÓRIA)
    // =========================================
    function switchView(viewName) {
        document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.btn-view').forEach(el => el.classList.remove('active'));

        document.getElementById('view-' + viewName).classList.add('active');

        const btns = document.querySelectorAll('.btn-view');
        if (viewName === 'map') {
            btns[0].classList.add('active');
            if (map) map.invalidateSize();
        } else {
            btns[1].classList.add('active');
            setTimeout(aplicarAnimacaoDica, 500);
        }

        localStorage.setItem('vagas_view_preference', viewName);
    }

    document.addEventListener("DOMContentLoaded", function() {
        const lastView = localStorage.getItem('vagas_view_preference');
        if (lastView === 'match') {
            switchView('match');
        }
    });

    // =========================================
    // 2. LÓGICA DO MAPA (LEAFLET)
    // =========================================
    const candLat = <?= $candidato['latitude'] ?? 0 ?>;
    const candLng = <?= $candidato['longitude'] ?? 0 ?>;
    const vagasData = <?= $vagas_json ?>;

    var map = L.map('map').setView([candLat, candLng], 12);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    var redIcon = new L.Icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    if (candLat && candLng) {
        L.marker([candLat, candLng], {
            icon: redIcon
        }).addTo(map).bindPopup("<b>Você</b>");
    }

    vagasData.forEach(function(vaga) {
        let nome = vaga.confidencial ? "Confidencial" : vaga.nome_empresa;
        L.marker([vaga.latitude, vaga.longitude]).addTo(map)
            .bindPopup(`<b>${vaga.titulo}</b><br>${nome}<br>${vaga.distancia_km.toFixed(1)} km<br><a href="<?= BASE_URL ?>vagas/show/${vaga.id}">Ver</a>`);
    });

    // =========================================
    // 3. LÓGICA TINDER (MATCH) & AJAX
    // =========================================
    const cards = document.querySelectorAll('.tinder-card');

    function aplicarAnimacaoDica() {
        let visibleCards = getVisibleCards();
        let cardTopo = visibleCards[visibleCards.length - 1];
        if (cardTopo) {
            cardTopo.classList.add('wiggle-effect');
            setTimeout(() => {
                cardTopo.classList.remove('wiggle-effect');
            }, 2500);
        }
    }

    function getVisibleCards() {
        return Array.from(document.querySelectorAll('.tinder-card'));
    }

    // --- FUNÇÃO QUE ATUALIZA A TELA (CONTADOR + FIM) ---
    function atualizarInterface() {
        let totalRestante = getVisibleCards().length;

        // 1. Atualiza o título
        let titulo = document.getElementById('titulo-contador');
        if (titulo) titulo.innerText = `Vagas Próximas (${totalRestante})`;

        // 2. Se acabou, substitui o conteúdo
        if (totalRestante === 0) {
            let wrapper = document.getElementById('match-content-wrapper');
            if (wrapper) {
                wrapper.innerHTML = `
                    <div style="text-align:center; padding:50px; animation: fadeIn 0.5s;">
                        <i class='bx bx-check-circle' style="font-size:4rem; color:#28a745;"></i>
                        <h3>Você zerou as vagas!</h3>
                        <p>Não há mais vagas novas nesta região por hoje.</p>
                        <a href="?reset=1" class="btn btn-sm btn-outline-primary" style="margin-top:10px;">Recarregar</a>
                    </div>
                `;
            }
        }
    }

    cards.forEach(card => {
        let isDragging = false,
            startX = 0,
            currentX = 0;

        // Touch
        card.addEventListener('touchstart', (e) => {
            isDragging = true;
            startX = e.touches[0].clientX;
            card.style.transition = 'none';
        });
        card.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            moveCard(card, e.touches[0].clientX, startX);
        });
        card.addEventListener('touchend', (e) => {
            isDragging = false;
            endDrag(card, e.changedTouches[0].clientX, startX);
        });

        // Mouse
        card.addEventListener('mousedown', (e) => {
            isDragging = true;
            startX = e.clientX;
            card.style.transition = 'none';
        });
        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            moveCard(card, e.clientX, startX);
        });
        document.addEventListener('mouseup', (e) => {
            if (isDragging) {
                isDragging = false;
                endDrag(card, e.clientX, startX);
            }
        });
    });

    function moveCard(card, currentX, startX) {
        let deltaX = currentX - startX;
        let rotate = deltaX * 0.05;
        card.style.transform = `translateX(${deltaX}px) rotate(${rotate}deg)`;

        if (deltaX > 0) {
            card.querySelector('.like-icon').style.opacity = deltaX / 150;
            card.querySelector('.nope-icon').style.opacity = 0;
        } else {
            card.querySelector('.nope-icon').style.opacity = Math.abs(deltaX) / 150;
            card.querySelector('.like-icon').style.opacity = 0;
        }
    }

    function endDrag(card, currentX, startX) {
        let deltaX = currentX - startX;
        card.style.transition = 'transform 0.4s ease';

        if (deltaX > 100) {
            swipeCard(card, 'right');
        } else if (deltaX < -100) {
            swipeCard(card, 'left');
        } else {
            card.style.transform = 'translateX(0) rotate(0)';
            card.querySelector('.like-icon').style.opacity = 0;
            card.querySelector('.nope-icon').style.opacity = 0;
        }
    }

    function triggerSwipe(dir) {
        let visibleCards = getVisibleCards();
        let card = visibleCards[visibleCards.length - 1];
        if (card) swipeCard(card, dir);
    }

    function swipeCard(card, dir) {
        let moveX = dir === 'right' ? 1000 : -1000;
        let rotate = dir === 'right' ? 20 : -20;
        card.style.transform = `translateX(${moveX}px) rotate(${rotate}deg)`;

        let id = card.getAttribute('data-id');

        // 1. Animação e atualização visual
        setTimeout(() => {
            card.remove(); // Remove do DOM para que :nth-last-child recalcule corretamente
            atualizarInterface(); // <--- ATUALIZA O CONTADOR E MENSAGEM
        }, 400);

        if (dir === 'right') {
            // LIKE: Vai para detalhes
            setTimeout(() => {
                window.location.href = `<?= BASE_URL ?>vagas/show/${id}`;
            }, 300);
        } else if (dir === 'left') {
            // NOPE: AJAX para ignorar
            const formData = new FormData();
            formData.append('vaga_id', id);

            fetch('<?= BASE_URL ?>proximas/ignorar', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => console.log("Ignorado:", data))
                .catch(error => console.error('Erro AJAX:', error));
        }
    }

    function verDetalhesMatch() {
        let visibleCards = getVisibleCards();
        let card = visibleCards[visibleCards.length - 1];
        if (card) window.location.href = `<?= BASE_URL ?>vagas/show/${card.getAttribute('data-id')}`;
    }
</script>