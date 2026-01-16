<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h4 class="fw-bold mb-0"><?= $action == 'edit' ? 'Editar Empresa/Portal' : 'Nova Empresa/Portal' ?></h4>
                </div>
                <div class="card-body p-4">
                    <form action="<?= BASE_URL ?>admin/salvar" method="POST">
                        <input type="hidden" name="id" value="<?= $dado['id'] ?? '' ?>">

                        <div class="mb-3">
                            <label for="nome_empresa" class="form-label">Nome da Empresa / Prefeitura</label>
                            <input type="text" class="form-control" id="nome_empresa" name="nome_empresa" value="<?= htmlspecialchars($dado['nome_empresa'] ?? '') ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tipo" class="form-label">Tipo de Organização</label>
                                <select class="form-select" id="tipo" name="tipo" required>
                                    <option value="empresa" <?= ($dado['tipo'] ?? '') == 'empresa' ? 'selected' : '' ?>>Empresa</option>
                                    <option value="prefeitura" <?= ($dado['tipo'] ?? '') == 'prefeitura' ? 'selected' : '' ?>>Prefeitura (Portal)</option>
                                    <option value="rh" <?= ($dado['tipo'] ?? '') == 'rh' ? 'selected' : '' ?>>Consultoria de RH</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="dominio" class="form-label">Domínio Personalizado (Opcional)</label>
                                <input type="text" class="form-control" id="dominio" name="dominio" value="<?= htmlspecialchars($dado['dominio'] ?? '') ?>" placeholder="ex: vagas.cidade.gov.br">
                                <div class="form-text">Usado para separar portais (Multi-tenancy).</div>
                            </div>
                            <!-- Added CNPJ Field -->
                            <div class="col-md-12 mb-3">
                                <label for="cnpj" class="form-label">CNPJ (Opcional se não obrigatório no banco, mas recomendado)</label>
                                <input type="text" class="form-control" id="cnpj" name="cnpj" value="<?= htmlspecialchars($dado['cnpj'] ?? '') ?>" placeholder="00.000.000/0000-00">
                            </div>
                        </div>

                        <!-- Status -->
                        <?php if ($action == 'edit'): ?>
                            <div class="mb-3">
                                <label class="form-label d-block">Status</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" id="status1" value="1" <?= ($dado['status'] ?? 1) == 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="status1">Ativo</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" id="status0" value="0" <?= ($dado['status'] ?? 1) == 0 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="status0">Inativo</label>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="<?= BASE_URL ?>admin/empresas" class="btn btn-light">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-4">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>