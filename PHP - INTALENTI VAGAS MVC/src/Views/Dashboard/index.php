<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<div class="container main-content-container">

    <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
        <div>
            <h2 class="fw-bold mb-0">Minhas Vagas</h2>
            <p class="text-muted">Gerencie suas vagas e veja candidatos.</p>
        </div>
        <a href="<?= BASE_URL ?>vagas/create" class="btn btn-custom-primary">
            <i class='bx bx-plus'></i> Criar Vaga
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['msg'])): ?>
        <div class="alert alert-success"><?= $_SESSION['msg'];
                                            unset($_SESSION['msg']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['msg_error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['msg_error'];
                                        unset($_SESSION['msg_error']); ?></div>
    <?php endif; ?>

    <div class="vagas-list-container">
        <?php if (!empty($vagas)): ?>
            <?php foreach ($vagas as $vaga): ?>
                <div class="card vaga-card mb-3 shadow-sm border-0">
                    <div class="card-body d-flex align-items-center justify-content-between flex-wrap">

                        <!-- Coluna 1: Info da Vaga -->
                        <div class="d-flex align-items-center flex-grow-1">
                            <div class="vaga-logo-wrapper me-3">
                                <?php if (!empty($vaga['logo_path'])): ?>
                                    <img src="<?= BASE_URL ?>uploads/logos/<?= $vaga['logo_path'] ?>" alt="Logo" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class='bx bx-briefcase text-muted'></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h5 class="mb-1 fw-bold"><?= htmlspecialchars($vaga['titulo']) ?></h5>
                                <small class="text-muted">
                                    <?php
                                    $labelEmpresa = !empty($vaga['nome_fantasia']) ? $vaga['nome_fantasia'] : $vaga['nome_empresa'];
                                    ?>
                                    <?= htmlspecialchars($labelEmpresa) ?> • <?= htmlspecialchars($vaga['localizacao']) ?>
                                </small>
                                <?php if (!empty($vaga['criador_nome'])): ?>
                                    <div style="font-size: 0.85em; margin-top: 2px;">
                                        <span class="text-secondary"><i class='bx bx-user-circle'></i> Criado por: <?= htmlspecialchars($vaga['criador_nome']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Coluna 2: Ações -->
                        <div class="d-flex align-items-center mt-3 mt-md-0">

                            <a href="<?= BASE_URL ?>vagas/candidatos/<?= $vaga['id'] ?>" class="btn btn-light btn-sm me-2 d-flex align-items-center">
                                <i class='bx bx-user me-1'></i> <?= $vaga['total_candidatos'] ?>
                            </a>

                            <div class="d-flex align-items-center gap-3">
                                <a href="<?= BASE_URL ?>vagas/candidatos/<?= $vaga['id'] ?>" class="btn btn-dark-custom btn-sm px-3 rounded-3">Ver Candidatos</a>

                                <?php
                                // Permissão: Admin OU Dono da Vaga
                                $podeEditar = ($user_role === 'admin' || $vaga['recrutador_id'] == $user_id);
                                ?>

                                <?php if ($podeEditar): ?>
                                    <a href="<?= BASE_URL ?>vagas/edit/<?= $vaga['id'] ?>" class="btn btn-sm p-0 border-0" title="Editar">
                                        <i class="fa-solid fa-pen" style="color: #404040; font-size: 1.1rem;"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger btn-sm p-0 border-0 btn-delete-vaga" style="background:none;" title="Excluir" data-id="<?= $vaga['id'] ?>">
                                        <i class='bx bx-trash' style="font-size: 1.3rem;"></i>
                                    </button>
                                <?php endif; ?>
                            </div>

                        </div>

                    </div>
                    <!-- Status Strip (Borda lateral colorida conforme status) -->
                    <div class="status-strip" style="position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background-color: #792e35; border-radius: 4px 0 0 4px;"></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5 my-5 bg-white rounded-4 shadow-sm border border-light">
                <div class="mb-4 text-muted" style="opacity: 0.15;">
                    <i class='bx bx-briefcase-alt-2' style="font-size: 6rem;"></i>
                </div>
                <h4 class="text-dark fw-bold mb-2">Seu mural está vazio</h4>
                <p class="text-muted mb-4 px-3" style="max-width: 400px; margin: 0 auto;">Crie oportunidades para encontrar os melhores talentos para sua empresa.</p>
                <a href="<?= BASE_URL ?>vagas/create" class="btn btn-custom-primary px-5 py-2 rounded-pill shadow-sm transition-hover d-inline-flex align-items-center justify-content-center gap-2">
                    <i class='bx bx-plus' style="font-size: 1.2em; line-height: 1;"></i> <span>Criar Primeira Vaga</span>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="text-center mt-5 text-muted small">
        &copy; <?= date('Y') ?> IntalenTI Vagas. Todos os direitos reservados.
    </div>

</div>

<!-- Modal de Confirmação de Exclusão -->
<div id="modal-exclusao" class="dashboard-modal-overlay" style="display: none;">
    <div class="dashboard-modal-card">
        <button type="button" class="close-modal" onclick="fecharModalExclusao()">×</button>

        <div class="modal-icon">
            <i class='bx bx-error-circle'></i>
        </div>

        <h3>Excluir Vaga?</h3>
        <p>Tem certeza que deseja excluir esta vaga permanentemente?</p>

        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="fecharModalExclusao()">Não, voltar</button>
            <form id="form-exclusao" action="<?= BASE_URL ?>vagas/delete" method="POST" style="display:inline;" onsubmit="console.log('Enviando form vaga delete...'); return true;">
                <input type="hidden" name="id" id="id-vaga-excluir">
                <button type="submit" class="btn-confirm">Sim, excluir</button>
            </form>
        </div>
    </div>
</div>

<style>
    /* CSS do Modal (Renomeado) */
    .dashboard-modal-overlay {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(0, 0, 0, 0.5) !important;
        display: flex;
        align-items: center !important;
        justify-content: center !important;
        z-index: 9999 !important;
        backdrop-filter: blur(2px);
    }

    .dashboard-modal-card {
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        width: 90%;
        max-width: 400px;
        text-align: center;
        position: relative;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        animation: modalFadeIn 0.3s ease;
    }

    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .close-modal {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 24px;
        background: none;
        border: none;
        cursor: pointer;
        color: #999;
    }

    .modal-icon {
        font-size: 3rem;
        color: #dc3545;
        margin-bottom: 15px;
    }

    .dashboard-modal-card h3 {
        margin: 0 0 10px;
        color: #333;
        font-weight: 600;
    }

    .dashboard-modal-card p {
        color: #666;
        margin-bottom: 25px;
        line-height: 1.5;
    }

    .modal-actions {
        display: flex;
        justify-content: center;
        gap: 10px;
    }

    .btn-cancel {
        padding: 10px 20px;
        border: none;
        background-color: #6c757d;
        color: white;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        transition: background 0.2s;
    }

    .btn-cancel:hover {
        background-color: #5a6268;
    }

    .btn-confirm {
        padding: 10px 20px;
        border: none;
        background-color: #dc3545;
        color: white;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        transition: background 0.2s;
    }

    .btn-confirm:hover {
        background-color: #c82333;
    }

    /* Pequeno ajuste inline para garantir o botão marrom igual ao print */
    .btn-custom-primary {
        background-color: #581c21;
        color: #fff;
        border: none;
        padding: 10px 20px;
        font-weight: 600;
    }

    .btn-custom-primary:hover {
        background-color: #441318;
        color: #fff;
    }

    .btn-dark-custom {
        background-color: #792e35;
        color: #fff;
        border: none;
    }

    .btn-dark-custom:hover {
        background-color: #5c1f25;
        color: #fff;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log("Dashboard Script Loaded");

        // Elementos do modal
        const modal = document.getElementById('modal-exclusao');
        const inputId = document.getElementById('id-vaga-excluir');
        const btnsDelete = document.querySelectorAll('.btn-delete-vaga');
        const btnClose = document.querySelector('.close-modal');
        const btnCancel = document.querySelector('.btn-cancel');

        // Função de abrir
        function abrirModal(id) {
            console.log("Abrindo modal para vaga:", id);
            if (!modal) {
                console.error("ERRO: Modal não encontrado no DOM");
                alert("Erro interno: Modal de exclusão não encontrado.");
                return;
            }

            // Garantir que está no body
            if (modal.parentNode !== document.body) {
                document.body.appendChild(modal);
            }

            if (inputId) inputId.value = id;
            modal.style.display = 'flex';
        }

        // Função de fechar
        function fecharModal() {
            if (modal) modal.style.display = 'none';
        }

        // Listeners nos botões de excluir
        btnsDelete.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Evita clicks indesejados
                const id = this.getAttribute('data-id');
                abrirModal(id);
            });
        });

        // Listeners de fechar
        if (btnClose) btnClose.addEventListener('click', fecharModal);
        if (btnCancel) btnCancel.addEventListener('click', fecharModal);

        // Fechar clicando fora
        window.addEventListener('click', function(e) {
            if (e.target === modal) {
                fecharModal();
            }
        });
    });
</script>