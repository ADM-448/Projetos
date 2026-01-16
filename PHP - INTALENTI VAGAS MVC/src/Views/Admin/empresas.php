<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Gestão de Empresas e Portais</h2>
            <p class="text-muted">Administração global do sistema.</p>
        </div>
        <a href="<?= BASE_URL ?>admin/nova" class="btn btn-primary">
            <i class="bx bx-plus"></i> Nova Empresa/Portal
        </a>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-2">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link <?= !$filtro_tipo ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/empresas">Todas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $filtro_tipo == 'prefeitura' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/empresas?tipo=prefeitura">Prefeituras</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $filtro_tipo == 'empresa' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/empresas?tipo=empresa">Empresas</a>
                </li>
                <!-- Removido tab RHs pois não é um tipo de empresa -->
            </ul>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['flash_success'];
            unset($_SESSION['flash_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['flash_error'];
            unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm" style="min-height: 400px;">
        <div class="card-body p-0">
            <!-- Removido .table-responsive ou aplicado overflow:visible para não cortar dropdowns -->
            <div style="overflow-x: visible;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Nome</th>
                            <th>Tipo</th>
                            <th>Responsável</th>
                            <th>Vagas</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($lista)): ?>
                            <?php foreach ($lista as $item): ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?= htmlspecialchars($item['nome_empresa']) ?></td>
                                    <td>
                                        <?php if ($item['tipo'] == 'prefeitura'): ?>
                                            <span class="badge bg-primary">Prefeitura</span>
                                        <?php elseif ($item['tipo'] == 'rh'): ?>
                                            <span class="badge bg-warning text-dark">RH</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Empresa</span>
                                        <?php endif; ?>
                                    </td>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($item['responsavel_nome'] ?? 'N/D') ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($item['responsavel_email'] ?? '') ?></div>
                                        <div class="small text-muted" style="font-size: 0.8em;"><?= htmlspecialchars($item['responsavel_cpf'] ?? '') ?></div>
                                    </td>
                                    <td><?= $item['total_vagas'] ?? 0 ?></td>
                                    <td>
                                        <?php if (($item['status'] ?? 1) == 1): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning">Pendente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-light" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                                <li>
                                                    <a class="dropdown-item" href="<?= BASE_URL ?>admin/editar/<?= $item['id'] ?>">
                                                        <i class="bx bx-edit me-2"></i> Editar
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="<?= BASE_URL ?>admin/toggleStatus/<?= $item['id'] ?>">
                                                        <i class="bx bx-power-off me-2"></i> <?= ($item['status'] ?? 1) == 1 ? 'Bloquear' : 'Aprovar / Ativar' ?>
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger confirm-delete" href="<?= BASE_URL ?>admin/excluir/<?= $item['id'] ?>">
                                                        <i class="bx bx-trash me-2"></i> Reprovar / Excluir
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Nenhum registro encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.confirm-delete').forEach(el => {
        el.addEventListener('click', function(e) {
            if (!confirm('Tem certeza? Isso apagará TODAS as vagas, convites e vínculos desta empresa/portal. Essa ação não pode ser desfeita.')) {
                e.preventDefault();
            }
        });
    });
</script>