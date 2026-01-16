<style>
    .badge {
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 0.85em;
        font-weight: 600;
        text-transform: uppercase;
        color: #fff;
    }

    .role-admin {
        background-color: #28a745;
    }

    .role-membro {
        background-color: #6c757d;
    }

    .actions-form {
        display: inline-block;
        margin: 0 5px 0 0;
    }

    /* Modal Styles */
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
        transition: opacity 0.3s ease;
    }

    .modal-overlay.active {
        display: flex;
        opacity: 1;
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
        color: #999;
        cursor: pointer;
    }

    .modal-options label {
        display: block;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
        cursor: pointer;
    }

    .modal-options label:hover {
        background-color: #f8f9fa;
    }

    .modal-options input[type="radio"] {
        margin-right: 10px;
    }

    .modal-warning {
        font-size: 0.9em;
        color: #dc3545;
        margin-left: 25px;
    }
</style>

<div class="container main-content-container">

    <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
        <div>
            <h2 class="fw-bold mb-0">Gerenciar Equipe</h2>
            <p class="text-muted">Gerencie membros e convites da empresa <strong><?= htmlspecialchars($empresa['nome_empresa']) ?></strong></p>
        </div>
        <a href="<?= BASE_URL ?>dashboard" class="btn btn-secondary" style="background-color: #6c757d; border: none; color: #fff; padding: 8px 20px; font-size: 0.95rem; font-weight: 500;"><i class='bx bx-arrow-back'></i> Voltar</a>
    </div>

    <?php if (!empty($msg_convite)): ?>
        <div class="alert alert-success"><?= $msg_convite ?></div>
    <?php endif; ?>
    <?php if (!empty($msg_convite_error)): ?>
        <div class="alert alert-danger"><?= $msg_convite_error ?></div>
    <?php endif; ?>

    <div class="row">

        <!-- Coluna da Esquerda: Convites -->
        <div class="col-md-5 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold text-primary mb-3">Convidar Novo Membro (RH)</h5>
                    <p class="text-muted small">Adicione recrutadores à sua equipe.</p>

                    <!-- Código Único -->
                    <div class="mb-4 p-3 bg-light rounded border">
                        <h6 class="fw-bold"><i class='bx bx-key'></i> Código de Uso Único</h6>
                        <p class="small text-muted mb-2">Expira em 24 horas. Para um único uso.</p>
                        <form action="<?= BASE_URL ?>empresa/gerarConvite" method="POST">
                            <input type="hidden" name="type" value="gerar_convite_unico">
                            <button type="submit" class="btn btn-secondary btn-sm w-100">Gerar Código</button>
                        </form>
                    </div>

                    <!-- Link Reutilizável -->
                    <div class="p-3 bg-light rounded border">
                        <h6 class="fw-bold"><i class='bx bx-link'></i> Link Reutilizável</h6>
                        <p class="small text-muted mb-2">Código permanente para múltiplos cadastros.</p>
                        <form action="<?= BASE_URL ?>empresa/gerarConvite" method="POST">
                            <input type="hidden" name="type" value="gerar_convite_reutilizavel">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Gerar/Ver Código</button>
                        </form>

                        <?php if (!empty($msg_convite_reutilizavel)): ?>
                            <div class="alert alert-success mt-3 mb-0 p-2 small">
                                <strong>Código da Empresa:</strong><br>
                                <input type="text" value="<?= $msg_convite_reutilizavel ?>" readonly class="form-control form-control-sm mt-1 text-center font-monospace" onclick="this.select()">
                                <div class="mt-1 text-muted" style="font-size:0.75rem">Envie este código para o cadastro de "Empregador".</div>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>

        <!-- Coluna da Direita: Lista de Membros -->
        <div class="col-md-7 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Membros da Equipe</h5>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nome</th>
                                    <th>Função</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($equipe as $membro): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($membro['name']) ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($membro['email']) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge <?= $membro['role'] == 'admin' ? 'role-admin' : 'role-membro' ?>">
                                                <?= $membro['role'] == 'admin' ? 'Admin' : 'Membro' ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <?php if ($membro['id'] == $_SESSION['user_id']): ?>
                                                <small class="text-muted">(Você)</small>
                                            <?php else: ?>
                                                <div class="btn-group">
                                                    <form action="<?= BASE_URL ?>empresa/gerenciarMembro" method="POST" class="d-inline">
                                                        <input type="hidden" name="membro_user_id" value="<?= $membro['id'] ?>">
                                                        <?php if ($membro['role'] == 'membro'): ?>
                                                            <input type="hidden" name="action" value="promover">
                                                            <button type="submit" class="btn btn-outline-success btn-sm" title="Promover a Admin"><i class='bx bx-chevrons-up'></i></button>
                                                        <?php else: ?>
                                                            <input type="hidden" name="action" value="rebaixar">
                                                            <button type="submit" class="btn btn-outline-secondary btn-sm" title="Rebaixar a Membro"><i class='bx bx-chevrons-down'></i></button>
                                                        <?php endif; ?>
                                                    </form>

                                                    <button type="button" class="btn btn-outline-danger btn-sm btn-remover-membro ms-1"
                                                        title="Remover da Equipe"
                                                        data-id="<?= $membro['id'] ?>"
                                                        data-name="<?= htmlspecialchars($membro['name']) ?>">
                                                        <i class='bx bx-trash'></i>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Remoção -->
<div id="modal-remocao" class="modal-overlay">
    <div class="modal-content">
        <button class="modal-close-btn" id="modal-close-btn">&times;</button>

        <form action="<?= BASE_URL ?>empresa/gerenciarMembro" method="POST">
            <input type="hidden" name="action" value="remover_membro">
            <input type="hidden" id="membro-id-modal" name="membro_user_id" value="">

            <h4 class="text-danger mb-3">Remover Membro</h4>
            <p>Você vai remover <strong id="membro-name-modal"></strong> da equipe.</p>

            <fieldset class="border p-3 rounded mb-3">
                <legend class="float-none w-auto px-2 fs-6 fw-bold">Vagas e Candidaturas</legend>
                <div class="modal-options">
                    <label>
                        <input type="radio" name="metodo_exclusao" value="transferir" checked>
                        <strong>Transferir para mim (Recomendado)</strong>
                        <div class="small text-muted ms-4">As vagas deste recrutador serão passadas para sua conta.</div>
                    </label>
                    <label>
                        <input type="radio" name="metodo_exclusao" value="excluir">
                        <strong>Excluir permanentemente</strong>
                        <div class="modal-warning">Apaga o usuário e TODAS as vagas dele. Não pode desfazer.</div>
                    </label>
                </div>
            </fieldset>

            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light" onclick="document.getElementById('modal-close-btn').click()">Cancelar</button>
                <button type="submit" class="btn btn-danger">Confirmar Remoção</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('modal-remocao');
        const closeBtn = document.getElementById('modal-close-btn');
        const modalName = document.getElementById('membro-name-modal');
        const modalIdInput = document.getElementById('membro-id-modal');
        const removeButtons = document.querySelectorAll('.btn-remover-membro');

        function closeModal() {
            modal.classList.remove('active');
            setTimeout(() => modal.style.display = 'none', 300);
        }

        removeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                modalName.textContent = name;
                modalIdInput.value = id;
                modal.style.display = 'flex';
                setTimeout(() => modal.classList.add('active'), 10);
            });
        });

        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (modal) {
            modal.addEventListener('click', function(event) {
                if (event.target === modal) closeModal();
            });
        }
    });
</script>