<div class="container main-content-container py-4" style="max-width: 900px;">

    <div style="margin-bottom: 25px; display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1 style="color: #2c3e50; font-weight: 700; margin: 0;">Minhas Candidaturas</h1>
            <p style="color: #666; margin: 5px 0 0;">Histórico de aplicações e status atualizados.</p>
        </div>
        <a href="<?= BASE_URL ?>vagas" class="btn btn-secondary btn-sm" style="background:#581c21; border:none; color:#fff;">
            <i class='bx bx-search-alt'></i> Buscar Vagas
        </a>
    </div>

    <?php if (empty($minhas_vagas)): ?>
        <div style="text-align: center; padding: 60px 20px; background: #fff; border-radius: 12px; border: 1px solid #e0e0e0; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
            <i class='bx bx-paper-plane' style="font-size: 4rem; color: #ddd; margin-bottom: 15px;"></i>
            <h3 style="margin-bottom: 10px; color: #555;">Nenhuma candidatura enviada.</h3>
            <p style="color: #888; margin-bottom: 25px;">Que tal explorar novas oportunidades agora mesmo?</p>
            <a href="<?= BASE_URL ?>vagas" class="btn btn-primary px-4 py-2" style="background-color: var(--primary-color); border:none;">
                <i class='bx bx-briefcase'></i> Ver Vagas Disponíveis
            </a>
        </div>
    <?php else: ?>

        <div style="display: flex; flex-direction: column; gap: 15px;">
            <?php foreach ($minhas_vagas as $vaga): ?>
                <?php
                // Lógica de Status e Cores
                $status_raw = mb_strtolower($vaga['status'] ?? 'enviada', 'UTF-8');

                $classe_bg_badge = '#ebf5fb';
                $classe_color_badge = '#3498db';
                $icone = 'bx-check';
                $border_color = '#3498db';

                if (strpos($status_raw, 'visualizada') !== false || strpos($status_raw, 'visto') !== false) {
                    $classe_bg_badge = '#fcf3cf';
                    $classe_color_badge = '#d4ac0d';
                    $icone = 'bx-show';
                    $border_color = '#f1c40f';
                }
                if (strpos($status_raw, 'processo') !== false || strpos($status_raw, 'entrevista') !== false) {
                    $classe_bg_badge = '#f5eef8';
                    $classe_color_badge = '#8e44ad';
                    $icone = 'bx-calendar';
                    $border_color = '#9b59b6';
                }
                if (strpos($status_raw, 'aprovado') !== false || strpos($status_raw, 'contratado') !== false) {
                    $classe_bg_badge = '#e8f8f5';
                    $classe_color_badge = '#27ae60';
                    $icone = 'bx-trophy';
                    $border_color = '#2ecc71';
                }
                if (strpos($status_raw, 'rejeitado') !== false || strpos($status_raw, 'reprovado') !== false) {
                    $classe_bg_badge = '#fdedec';
                    $classe_color_badge = '#c0392b';
                    $icone = 'bx-x-circle';
                    $border_color = '#e74c3c';
                }
                ?>

                <div class="card-inscricao" style="
                    background: #fff; 
                    border: 1px solid #e0e0e0; 
                    border-radius: 10px; 
                    padding: 20px; 
                    display: flex; 
                    flex-wrap: wrap; 
                    gap: 15px; 
                    align-items: center; 
                    box-shadow: 0 2px 6px rgba(0,0,0,0.02);
                    border-left: 5px solid <?= $border_color ?>;
                    transition: transform 0.2s ease, box-shadow 0.2s ease;
                ">

                    <!-- Logo -->
                    <div style="
                        width: 60px; height: 60px; border-radius: 8px; border: 1px solid #eee;
                        display: flex; align-items: center; justify-content: center; background: #fafafa; flex-shrink: 0;
                    ">
                        <?php if (!$vaga['confidencial'] && !empty($vaga['logo_path'])): ?>
                            <img src="<?= BASE_URL ?>uploads/logos/<?= htmlspecialchars($vaga['logo_path']) ?>" style="width: 100%; height: 100%; object-fit: contain; border-radius: 8px;">
                        <?php else: ?>
                            <i class='bx bxs-briefcase' style="font-size: 2rem; color: #ccc;"></i>
                        <?php endif; ?>
                    </div>

                    <!-- Info -->
                    <div style="flex-grow: 1; min-width: 250px;">
                        <h3 style="margin: 0 0 5px 0; font-size: 1.15rem; color: #333;">
                            <a href="<?= BASE_URL ?>vagas/show/<?= $vaga['vaga_id'] ?>" style="text-decoration:none; color:inherit; font-weight: 700;">
                                <?= htmlspecialchars($vaga['titulo']) ?>
                            </a>
                        </h3>
                        <p style="margin: 0; color: #666; font-size: 0.95rem;">
                            <?= $vaga['confidencial'] ? 'Empresa Confidencial' : htmlspecialchars($vaga['nome_empresa']) ?>
                            &bull;
                            <small class="text-muted"><?= htmlspecialchars($vaga['localizacao']) ?></small>
                        </p>
                        <span style="font-size: 0.85rem; color: #999; margin-top: 6px; display: block;">
                            Candidatou-se: <?= date('d/m/Y \à\s H:i', strtotime($vaga['data_candidatura'])) ?>
                        </span>
                    </div>

                    <!-- Status e Ações -->
                    <div style="text-align: center; min-width: 140px; display: flex; flex-direction: column; align-items: center; gap: 8px;">

                        <span style="
                            padding: 6px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;
                            background-color: <?= $classe_bg_badge ?>; color: <?= $classe_color_badge ?>;
                        ">
                            <i class='bx <?= $icone ?>'></i> <?= htmlspecialchars($vaga['status'] ?? 'Enviada') ?>
                        </span>

                        <?php if (!empty($vaga['chat_id']) && !empty($vaga['msg_count']) && $vaga['msg_count'] > 0): ?>
                            <a href="<?= BASE_URL ?>chat/index/<?= $vaga['chat_id'] ?>" class="btn-chat" style="
                                background-color: #581c21; color: #fff; padding: 6px 14px; border-radius: 6px; text-decoration: none;
                                font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px; font-weight: 500;
                            ">
                                <i class='bx bx-message-square-dots'></i> Mensagem
                            </a>
                        <?php endif; ?>

                        <?php
                        // Permitir cancelamento em quase todos os status, exceto finalizados
                        $status_bloqueados = ['aprovado', 'contratado', 'rejeitado', 'reprovado'];
                        $pode_cancelar = !in_array($status_raw, $status_bloqueados);
                        ?>

                        <?php if ($pode_cancelar): ?>
                            <button type="button" onclick="confirmarCancelamento(<?= $vaga['vaga_id'] ?>)" style="
                                    color: #c0392b; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;
                                    cursor: pointer; background: none; border: none; padding: 0; margin-top: 5px;
                                ">
                                <i class='bx bx-trash'></i> Cancelar
                            </button>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- Modal Customizado de Cancelamento -->
<div id="cancelar-modal" class="modal-overlay">
    <div class="modal-content" style="max-width: 400px; text-align: center;">
        <button class="modal-close-btn" onclick="closeModal('cancelar-modal')">&times;</button>
        <div style="margin-bottom: 20px;">
            <i class='bx bx-error-circle' style='font-size: 4rem; color: #dc3545;'></i>
        </div>
        <h3>Cancelar Candidatura?</h3>
        <p class="subtitle" style="margin-bottom: 20px;">Tem certeza que deseja cancelar sua candidatura para esta vaga?</p>

        <form id="form-cancelar" action="<?= BASE_URL ?>candidatura/cancelar" method="POST">
            <input type="hidden" name="vaga_id" id="modal-vaga-id" value="">
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('cancelar-modal')">Não, voltar</button>
                <button type="submit" class="btn btn-danger">Sim, cancelar</button>
            </div>
        </form>
    </div>
</div>

<style>
    .card-inscricao:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.08) !important;
    }

    .btn-chat:hover {
        opacity: 0.9;
    }

    /* Modal Styles (Copiados de detalhe.php) */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        justify-content: center;
        align-items: center;
        z-index: 1050;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }

    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .modal-content {
        background-color: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
        width: 90%;
        max-width: 600px;
        position: relative;
        transform: translateY(-50px);
        transition: transform 0.3s ease;
    }

    .modal-overlay.active .modal-content {
        transform: translateY(0);
    }

    .modal-close-btn {
        position: absolute;
        top: 10px;
        right: 15px;
        background: none;
        border: none;
        font-size: 2rem;
        color: #6c757d;
        cursor: pointer;
    }
</style>

<script>
    function confirmarCancelamento(vagaId) {
        document.getElementById('modal-vaga-id').value = vagaId;
        openModal('cancelar-modal');
    }

    function openModal(id) {
        const m = document.getElementById(id);
        if (m) {
            m.style.display = 'flex';
            setTimeout(() => m.classList.add('active'), 10);
        }
    }

    function closeModal(id) {
        const m = document.getElementById(id);
        if (m) {
            m.classList.remove('active');
            setTimeout(() => m.style.display = 'none', 300);
        }
    }
</script>