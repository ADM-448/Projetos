<div class="container main-content-container py-4">

    <!-- Header com Título e Botão Voltar -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h2 class="fw-bold">Candidatos: <?= htmlspecialchars($vaga['titulo']) ?></h2>
            <p class="text-muted">
                <i class='bx bx-map'></i> Vaga em: <?= htmlspecialchars($vaga['localizacao'] ?? 'Localização não informada') ?>
            </p>

            <!-- Filtros (Abas) -->
            <div class="d-flex align-items-center mt-3">
                <span class="fw-bold me-2">Ordenar por:</span>

                <a href="?ordem=recentes" class="btn btn-sm me-2 rounded-pill px-3 text-decoration-none <?= ($ordem_atual == 'recentes') ? 'btn-dark-custom text-white' : 'btn-light border text-muted' ?>">
                    Recentes
                </a>

                <a href="?ordem=proximos" class="btn btn-sm rounded-pill px-3 text-decoration-none <?= ($ordem_atual == 'proximos') ? 'btn-dark-custom text-white' : 'btn-light border text-muted' ?>">
                    Próximos
                </a>
            </div>
        </div>

        <a href="<?= BASE_URL ?>dashboard/vagas" class="btn btn-secondary" style="background-color: #6c757d; border: none; color: #fff; padding: 8px 20px; font-size: 0.95rem; font-weight: 500; height: fit-content;">
            <i class='bx bx-arrow-back'></i> Voltar
        </a>
    </div>

    <!-- Tabela de Candidatos -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-uppercase text-secondary small fw-bold" style="width: 30%;">Nome / Localização</th>
                            <th class="text-uppercase text-secondary small fw-bold">Email</th>
                            <th class="text-uppercase text-secondary small fw-bold">Data</th>
                            <th class="text-uppercase text-secondary small fw-bold">Status</th>
                            <th class="text-uppercase text-secondary small fw-bold text-center">Anexo</th>
                            <th class="pe-4 text-uppercase text-secondary small fw-bold text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($candidatos)): ?>
                            <?php foreach ($candidatos as $c): ?>
                                <tr>
                                    <!-- Nome e Distância -->
                                    <td class="ps-4 py-3">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($c['nome_candidato']) ?></div>
                                        <?php if (isset($c['distancia_km'])): ?>
                                            <span class="badge bg-light text-success border border-success mt-1 rounded-pill" title="Distância até a vaga">
                                                <i class='bx bx-map-pin'></i> <?= number_format($c['distancia_km'], 1, ',', '.') ?> km
                                            </span>
                                        <?php else: ?>
                                            <!-- Sem geolocalização disponível (Fallback para string) -->
                                            <span class="badge bg-light text-secondary border mt-1 rounded-pill" title="Localização do Candidato">
                                                <i class='bx bx-map'></i>
                                                <?= (!empty($c['cidade'])) ? htmlspecialchars($c['cidade'] . ($c['estado'] ? '/' . $c['estado'] : '')) : 'Local não identificado' ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Email -->
                                    <td class="text-muted"><?= htmlspecialchars($c['email']) ?></td>

                                    <!-- Data -->
                                    <td class="text-muted"><?= date('d/m/y', strtotime($c['created_at'])) ?></td>

                                    <!-- Status -->
                                    <td>
                                        <span class="text-dark fw-medium"><?= htmlspecialchars($c['status'] ?? 'Enviada') ?></span>
                                    </td>

                                    <!-- Anexo -->
                                    <td class="text-center">
                                        <?php if (!empty($c['curriculo_path'])): ?>
                                            <a href="<?= BASE_URL ?>uploads/curriculos/<?= $c['curriculo_path'] ?>" target="_blank" class="text-danger fs-4" title="Ver Currículo">
                                                <i class='bx bxs-file-pdf'></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted fs-4"><i class='bx bx-file-blank'></i></span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Ações -->
                                    <td class="pe-4 text-end">
                                        <a href="<?= BASE_URL ?>candidato/perfil/<?= $c['user_id'] ?>?vaga_id=<?= $vaga['id'] ?>" class="btn btn-dark-custom btn-sm">Detalhes</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class='bx bx-ghost fs-1 mb-2'></i><br>
                                    Nenhum candidato para esta vaga ainda.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<style>
    .btn-dark-custom {
        background-color: #581d22;
        color: #fff;
        border: none;
    }

    .btn-dark-custom:hover {
        background-color: #441318;
        color: #fff;
    }

    /* Estilo para a tabela ficar clean como na imagem */
    .table thead th {
        border-bottom: 2px solid #f0f0f0;
        padding-top: 15px;
        padding-bottom: 15px;
    }

    .table td {
        vertical-align: middle;
        border-bottom: 1px solid #f9f9f9;
        font-size: 0.95rem;
    }
</style>