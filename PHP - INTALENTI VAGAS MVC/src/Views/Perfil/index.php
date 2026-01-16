<div class="container" style="max-width: 800px; padding: 40px 20px;">

    <?php
    $dashboardLink = BASE_URL . 'dashboard';
    if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] == 1) { // 1 = Candidato
        $dashboardLink = BASE_URL . 'vagas'; // Candidatos geralmente vão para 'Buscar Vagas' ou 'vagas' como dashboard
    }
    ?>
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 style="color: #2c3e50; margin-bottom: 5px; margin-top: 0;">Meu Perfil</h1>
            <p class="subtitle" style="color: #666; font-size: 0.95rem; margin-bottom: 0;">Atualize seus dados pessoais e profissionais.</p>
        </div>
        <a href="<?= $dashboardLink ?>" class="btn btn-secondary" style="background-color: #6c757d; border: none; color: #fff; padding: 8px 20px; font-size: 0.95rem; font-weight: 500; display: inline-flex; align-items: center; gap: 5px; text-decoration: none;">
            <i class='bx bx-arrow-back'></i> Voltar
        </a>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['flash_success'];
                                            unset($_SESSION['flash_success']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['flash_error'];
                                        unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>perfil/update" method="POST" enctype="multipart/form-data">

        <fieldset style="border: none; border-top: 1px solid #eee; padding-top: 20px;">
            <legend style="font-size: 1.2rem; font-weight: 600; color: #333; margin-bottom: 20px;">Dados de Acesso</legend>

            <div class="mb-3">
                <label for="name" class="form-label font-weight-bold">Nome Completo</label>
                <input type="text" id="name" name="nome_completo" class="form-control" required
                    value="<?= htmlspecialchars($nome_exibicao ?? $user['name'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label font-weight-bold">E-mail</label>
                <input type="email" id="email" name="email" class="form-control" required
                    value="<?= htmlspecialchars($user['email'] ?? '') ?>">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nova_senha" class="form-label font-weight-bold">Nova Senha</label>
                    <input type="password" id="nova_senha" name="nova_senha" class="form-control" placeholder="Deixe em branco para manter">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="confirma_senha" class="form-label font-weight-bold">Confirmar Nova Senha</label>
                    <input type="password" id="confirma_senha" name="confirma_senha" class="form-control">
                </div>
            </div>

        </fieldset>

        <button type="submit" class="btn btn-primary" style="background-color: var(--primary-color); border-color: var(--primary-color);">Salvar Alterações</button>
    </form>

    <hr style="border-color: #eee; margin: 40px 0;">

    <div class="delete-section" style="text-align: center; margin-top: 40px;">
        <h2 style="font-size: 1.5rem; color: #dc3545; margin-bottom: 10px;">Zona de Perigo</h2>
        <p style="color: #6c757d; margin-bottom: 20px;">Esta ação é permanente e removerá todos os seus dados.</p>

        <button type="button" class="btn btn-outline-danger" onclick="abrirModalExclusaoConta()">
            <i class='bx bx-trash'></i> Excluir Minha Conta
        </button>
    </div>

</div>

<!-- Modal de Exclusão de Conta -->
<div id="modal-exclusao-conta" class="profile-modal-overlay" style="display: none;">
    <div class="profile-modal-card">
        <button type="button" class="close-modal" onclick="fecharModalExclusaoConta()">×</button>

        <div class="modal-icon">
            <i class='bx bx-error-circle'></i>
        </div>

        <h3 style="color: #dc3545;">Excluir Conta?</h3>
        <p>Tem certeza absoluta? Todos os seus dados, vagas e candidaturas serão apagados permanentemente.</p>

        <form action="<?= BASE_URL ?>perfil/delete" method="POST" onsubmit="console.log('Enviando form perfil delete...'); return true;">
            <div class="mb-3" style="text-align: left;">
                <label for="password_confirm" class="form-label" style="font-weight: 500; font-size: 0.9rem;">Para confirmar, digite sua senha atual:</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control" required placeholder="Sua senha">
            </div>

            <div class="modal-actions" style="margin-top: 20px;">
                <button type="button" class="btn-cancel" onclick="fecharModalExclusaoConta()">Cancelar</button>
                <button type="submit" class="btn-confirm">Sim, Excluir Conta</button>
            </div>
        </form>
    </div>
</div>

<style>
    /* CSS do Modal (Reutilizando estilo se possível, ou definindo aqui) */
    /* CSS do Modal (Reutilizando estilo se possível, ou definindo aqui) */
    .profile-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 99999;
        backdrop-filter: blur(2px);
    }

    .profile-modal-card {
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
        transition: 0.2s;
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
        transition: 0.2s;
    }

    .btn-confirm:hover {
        background-color: #c82333;
    }
</style>

<script>
    console.log('Script Perfil carregado');

    window.abrirModalExclusaoConta = function() {
        console.log('Abrindo modal exclusão conta');
        const modal = document.getElementById('modal-exclusao-conta');
        if (modal) {
            modal.style.display = 'flex';
            // Foca no input de senha após abrir
            setTimeout(() => {
                const passInput = document.getElementById('password_confirm');
                if (passInput) passInput.focus();
            }, 100);
        } else {
            console.error('Modal de conta não encontrado');
            alert('Erro: Modal não encontrado.');
        }
    }

    window.fecharModalExclusaoConta = function() {
        const modal = document.getElementById('modal-exclusao-conta');
        if (modal) {
            modal.style.display = 'none';
        }
        const passInput = document.getElementById('password_confirm');
        if (passInput) passInput.value = '';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('modal-exclusao-conta');
        if (event.target == modal) {
            window.fecharModalExclusaoConta();
        }
    }
</script>

</div>