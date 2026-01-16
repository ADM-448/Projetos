<style>
    /* Estilos Customizados para Dados da Empresa (Similar ao Screenshot) */
    .page-header-title {
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .page-header-subtitle {
        color: #666;
        font-size: 0.95rem;
        margin-bottom: 30px;
    }

    .form-section-title {
        font-size: 1.25rem;
        color: #333;
        font-weight: 600;
        margin-top: 40px;
        margin-bottom: 20px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }

    .form-group label {
        font-weight: 500;
        font-size: 0.9rem;
        color: #444;
        margin-bottom: 0.5rem;
    }

    .form-control {
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        padding: 10px;
    }

    .form-control:focus {
        background-color: #fff;
        border-color: #aaa;
        box-shadow: none;
    }

    .form-control[readonly] {
        background-color: #eee;
        cursor: not-allowed;
    }

    .help-text {
        font-size: 0.8rem;
        color: #777;
        margin-top: 4px;
    }

    .btn-save {
        background-color: #5d2e2e;
        /* Tom marrom avermelhado do screenshot */
        color: #fff;
        border: none;
        padding: 12px 30px;
        font-weight: 600;
        margin-top: 30px;
        width: 100%;
        font-size: 1rem;
    }

    .btn-save:hover {
        background-color: #4a2424;
        color: #fff;
    }

    .logo-preview {
        margin-top: 10px;
        max-width: 100px;
        border: 1px solid #ddd;
        padding: 5px;
        border-radius: 4px;
    }
</style>

<div class="container" style="max-width: 1000px; padding: 40px 20px;">

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="page-header-title">Dados da Empresa</h1>
            <p class="page-header-subtitle" style="margin-bottom: 0;">Aqui você pode atualizar as informações cadastrais da sua empresa.</p>
        </div>
        <a href="<?= BASE_URL ?>dashboard" class="btn btn-secondary" style="background-color: #6c757d; border: none; color: #fff; padding: 8px 20px; font-size: 0.95rem; font-weight: 500; height: fit-content; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
            <i class='bx bx-arrow-back'></i> Voltar
        </a>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['flash_error'];
                                        unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['msg_error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['msg_error'];
                                        unset($_SESSION['msg_error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['flash_success'];
                                            unset($_SESSION['flash_success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['msg'])): ?>
        <div class="alert alert-success"><?= $_SESSION['msg'];
                                            unset($_SESSION['msg']); ?></div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>empresa/update" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $empresa['id'] ?>">

        <?php $disabled = ($role !== 'admin') ? 'disabled' : ''; ?>
        <?php if ($disabled): ?>
            <div class="alert alert-warning">
                <i class='bx bx-lock-alt'></i> Apenas administradores podem alterar os dados da empresa.
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="form-group">
                    <label>Nome da Empresa / Razão Social:</label>
                    <input type="text" name="razao_social" class="form-control" value="<?= htmlspecialchars($empresa['nome_empresa'] ?? '') ?>" disabled title="Contate o suporte para alterar"> <!-- Usando nome_empresa como razao social se nao tiver outro campo -->
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="form-group">
                    <label>CNPJ:</label>
                    <input type="text" name="cnpj" class="form-control" value="<?= htmlspecialchars($empresa['cnpj'] ?? '') ?>" readonly>
                    <p class="help-text">O CNPJ não pode ser alterado após o cadastro.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="form-group">
                    <label>Nome Fantasia:</label>
                    <input type="text" name="nome_fantasia" class="form-control" value="<?= htmlspecialchars($empresa['nome_fantasia'] ?? '') ?>" required <?= $disabled ?>>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="form-group">
                    <label>Logo da Empresa (JPG, PNG, WEBP):</label>
                    <input type="file" name="logo" class="form-control" accept="image/*" onchange="previewLogo(this)" <?= $disabled ?>>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mb-3">
                <label>Logo Atual:</label>
                <div>
                    <?php
                    $logoExibe = !empty($empresa['logo_path']) ? BASE_URL . 'uploads/logos/' . $empresa['logo_path'] : '';
                    ?>
                    <?php if ($logoExibe): ?>
                        <img src="<?= $logoExibe ?>" alt="Logo Atual" class="logo-preview" id="logo-preview-img">
                    <?php else: ?>
                        <img src="" alt="Preview" class="logo-preview" id="logo-preview-img" style="display: none;">
                        <p class="help-text" id="no-logo-text">Nenhuma logo carregada.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <h3 class="form-section-title">Endereço</h3>

        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="form-group">
                    <label>CEP:</label>
                    <input type="text" name="cep" id="cep" class="form-control" value="<?= htmlspecialchars($empresa['cep'] ?? '') ?>" maxlength="9" onkeyup="mascaraCep(this)" <?= $disabled ?>>
                </div>
            </div>
            <div class="col-md-7 mb-3">
                <div class="form-group">
                    <label>Logradouro (Rua, Avenida, etc.):</label>
                    <input type="text" name="logradouro" id="logradouro" class="form-control" value="<?= htmlspecialchars($empresa['logradouro'] ?? '') ?>" <?= $disabled ?>>
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <div class="form-group">
                    <label>Número:</label>
                    <input type="text" name="numero" class="form-control" value="<?= htmlspecialchars($empresa['numero'] ?? '') ?>" <?= $disabled ?>>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="form-group">
                    <label>Complemento:</label>
                    <input type="text" name="complemento" class="form-control" value="<?= htmlspecialchars($empresa['complemento'] ?? '') ?>" <?= $disabled ?>>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="form-group">
                    <label>Bairro:</label>
                    <input type="text" name="bairro" id="bairro" class="form-control" value="<?= htmlspecialchars($empresa['bairro'] ?? '') ?>" <?= $disabled ?>>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="form-group">
                    <label>Cidade:</label>
                    <input type="text" name="cidade" id="cidade" class="form-control" value="<?= htmlspecialchars($empresa['cidade'] ?? '') ?>" <?= $disabled ?>>
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <div class="form-group">
                    <label>UF:</label>
                    <input type="text" name="estado" id="uf" class="form-control" value="<?= htmlspecialchars($empresa['estado'] ?? '') ?>" maxlength="2" <?= $disabled ?>>
                </div>
            </div>
        </div>

        <?php if (!$disabled): ?>
            <button type="submit" class="btn-save">Salvar Alterações</button>
        <?php endif; ?>

    </form>
</div>

<script>
    function mascaraCep(input) {
        let value = input.value.replace(/\D/g, "");
        if (value.length > 5) value = value.substring(0, 5) + "-" + value.substring(5, 8);
        input.value = value;
        if (value.replace(/\D/g, "").length === 8) {
            buscarCep(value);
        }
    }

    function buscarCep(cep) {
        let cepLimpo = cep.replace(/\D/g, '');
        fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`)
            .then(res => res.json())
            .then(data => {
                if (!data.erro) {
                    document.getElementById('logradouro').value = data.logradouro;
                    document.getElementById('bairro').value = data.bairro;
                    document.getElementById('cidade').value = data.localidade;
                    document.getElementById('uf').value = data.uf;
                }
            })
            .catch(err => console.error(err));
    }

    function previewLogo(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function(e) {
                var img = document.getElementById('logo-preview-img');
                var text = document.getElementById('no-logo-text');

                img.src = e.target.result;
                img.style.display = 'block';

                if (text) text.style.display = 'none';
            }

            reader.readAsDataURL(input.files[0]);
        }
    }
</script>