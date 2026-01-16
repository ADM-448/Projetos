<?php
// Layout baseado no projeto original (Legacy)
?>


<div class="container">
    <div class="search-header">
        <h1>Encontre a sua Próxima Oportunidade</h1>
        <p style="color: #666; font-size: 1.1rem;">Use os filtros abaixo para pesquisar vagas.</p>
    </div>

    <div class="search-form-container">
        <form action="<?= BASE_URL ?>vagas" method="GET">
            <div class="row g-3">
                <!-- Inputs na primeira linha -->
                <div class="col-lg-4 col-md-12">
                    <input type="text" name="busca" class="form-control form-control-lg"
                        placeholder="Cargo, palavra-chave ou empresa..."
                        value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
                </div>

                <div class="col-lg-4 col-md-6">
                    <select name="localizacao" class="form-select form-select-lg">
                        <option value="" selected>Todas as Cidades</option>
                        <?php
                        $local_selecionado = $_GET['localizacao'] ?? '';
                        foreach ($locais_disponiveis as $local):
                        ?>
                            <option value="<?= htmlspecialchars($local) ?>" <?= ($local_selecionado == $local) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($local) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-lg-4 col-md-6">
                    <select name="ramo" class="form-select form-select-lg">
                        <option value="" selected>Todos os Ramos</option>
                        <?php
                        $ramo_selecionado = $_GET['ramo'] ?? '';
                        foreach ($ramos_de_atividade as $ramo) :
                        ?>
                            <option value="<?= htmlspecialchars($ramo) ?>" <?= ($ramo_selecionado == $ramo) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ramo) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Botões em linha separada e lado a lado -->
                <div class="col-12 mt-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-legacy btn-legacy-primary">
                                <i class='bx bx-search'></i> Buscar Vagas
                            </button>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= BASE_URL ?>proximas" class="btn btn-legacy btn-legacy-secondary">
                                <i class='bx bx-map-pin'></i> Vagas Perto de Mim
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="search-results-section">
        <?php if (count($vagas) > 0): ?>

            <h2 class="mb-4" style="font-size: 1.5rem; color: #333; font-weight: 700;">
                <?= $busca_realizada ? 'Resultados da Busca:' : 'Vagas Recentes:' ?>
            </h2>

            <?php foreach ($vagas as $vaga): ?>
                <div class="job-card">
                    <div class="job-logo">
                        <?php if (!$vaga['confidencial'] && !empty($vaga['logo_path'])): ?>
                            <img src="<?= BASE_URL ?>uploads/logos/<?= htmlspecialchars($vaga['logo_path']) ?>"
                                alt="Logo">
                        <?php else: ?>
                            <i class='bx bx-buildings' style="font-size: 2rem; color: #ccc;"></i>
                        <?php endif; ?>
                    </div>

                    <div class="job-details">
                        <h3 class="job-title">
                            <a href="<?= BASE_URL ?>vagas/show/<?= $vaga['id'] ?>" style="text-decoration: none; color: inherit;">
                                <?= htmlspecialchars($vaga['titulo']) ?>
                            </a>
                        </h3>
                        <span class="job-company-name">
                            <?= $vaga['confidencial'] ? 'Empresa Confidencial' : htmlspecialchars($vaga['nome_empresa']) ?>
                        </span>

                        <?php if (isset($vaga['data_candidatura'])): ?>
                            <div style="font-size: 0.85rem; color: #28a745; margin-top: 5px; font-weight: 600;">
                                <i class='bx bx-check-double'></i> Candidatou-se: <?= date('d/m/Y \à\s H:i', strtotime($vaga['data_candidatura'])) ?>
                            </div>
                        <?php endif; ?>

                        <div class="job-meta-badges">
                            <div class="meta-badge"><i class='bx bx-map'></i> <?= htmlspecialchars($vaga['localizacao']) ?></div>
                            <div class="meta-badge"><i class='bx bx-file'></i> <?= htmlspecialchars($vaga['tipo_contrato']) ?></div>

                            <?php if (!empty($vaga['faixa_salarial']) && $vaga['faixa_salarial'] !== 'A combinar'): ?>
                                <div class="meta-badge salary-badge">
                                    <i class='bx bx-dollar'></i> R$ <?= number_format((float)$vaga['faixa_salarial'], 2, ',', '.') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="job-action">
                        <a href="<?= BASE_URL ?>vagas/show/<?= $vaga['id'] ?>" class="btn btn-legacy btn-legacy-primary" style="padding: 10px 25px; width: auto;">
                            Ver Detalhes
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php else: ?>
            <div class="text-center py-5">
                <i class='bx bx-search-alt' style="font-size: 4rem; color: #ddd; margin-bottom: 20px;"></i>
                <?php if ($busca_realizada): ?>
                    <h3 style="color: #666;">Nenhuma vaga encontrada para sua busca.</h3>
                    <p class="text-muted">Tente usar termos diferentes ou filtros mais amplos.</p>
                <?php else: ?>
                    <h3 style="color: #666;">Nenhuma vaga publicada recentemente.</h3>
                    <p class="text-muted">Volte mais tarde para novas oportunidades!</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>