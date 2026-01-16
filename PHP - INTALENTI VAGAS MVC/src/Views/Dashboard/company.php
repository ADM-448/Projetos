<div class="main-content-fluid" style="background-color: #f5f8fa; min-height: 100vh; padding: 20px;">

    <!-- Cabeçalho Clean -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold text-dark" style="font-family: 'Inter', sans-serif; letter-spacing: -0.5px;">Painel da Empresa</h2>
            <p class="text-secondary mb-0">Visão geral das suas vagas e candidaturas.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>vagas/create" class="btn btn-primary border shadow-sm" style="background: #0d6efd; color: white;">
                <i class='bx bx-plus'></i> Nova Vaga
            </a>
            <button class="btn btn-white border shadow-sm" style="background: #fff;" onclick="window.print()">
                <i class='bx bx-printer'></i> Exportar
            </button>
        </div>
    </div>

    <!-- Mensagens Flash -->
    <?php if (isset($_SESSION['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['msg']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <?php unset($_SESSION['msg']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['msg_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['msg_error']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <?php unset($_SESSION['msg_error']); ?>
        </div>
    <?php endif; ?>

    <!-- KPIs Hubspot Style -->
    <div class="row g-4 mb-5">

        <!-- Impacto Econômico (Destaque Clean) -->
        <div class="col-md-12 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #00bda5 !important;">
                <div class="card-body p-4">
                    <h6 class="text-uppercase text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Impacto Econômico</h6>
                    <h2 class="fw-bold text-dark mt-2 mb-1" style="font-size: 2rem;">R$ <?= number_format($kpis['impacto_economico'], 2, ',', '.') ?></h2>
                    <div class="d-flex align-items-center gap-1 text-success font-monospace" style="font-size: 0.85rem;">
                        <i class='bx bx-trending-up'></i> Estimativa Mensal
                    </div>
                </div>
            </div>
        </div>

        <!-- Vagas (Com Link/Ação) -->
        <div class="col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm h-100 hover-card" style="cursor: pointer; border-top: 4px solid #ff7a59 !important;" data-bs-toggle="modal" data-bs-target="#modalVagas">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-uppercase text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Vagas Abertas</h6>
                            <h2 class="fw-bold text-dark mt-2 mb-0" style="font-size: 2.2rem;"><?= number_format($kpis['vagas_ativas'], 0, ',', '.') ?></h2>
                        </div>
                        <div class="icon-circle bg-light text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class='bx bx-briefcase'></i>
                        </div>
                    </div>
                    <p class="mt-3 mb-0 text-primary fw-bold" style="font-size: 0.85rem;">Ver lista completa <i class='bx bx-chevron-right'></i></p>
                </div>
            </div>
        </div>

        <!-- Candidaturas (Recebidas) -->
        <div class="col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm h-100 hover-card" style="cursor: pointer; border-top: 4px solid #6a78d1 !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-uppercase text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Candidaturas Recebidas</h6>
                            <h2 class="fw-bold text-dark mt-2 mb-0" style="font-size: 2.2rem;"><?= number_format($kpis['interacoes'], 0, ',', '.') ?></h2>
                        </div>
                        <div class="icon-circle bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class='bx bx-file'></i>
                        </div>
                    </div>
                    <p class="mt-3 mb-0 text-muted" style="font-size: 0.85rem;">Total de currículos recebidos</p>
                </div>
            </div>
        </div>

        <!-- Candidatos -->
        <div class="col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm h-100 hover-card" style="cursor: pointer; border-top: 4px solid #f2545b !important;" data-bs-toggle="modal" data-bs-target="#modalCandidatos">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-uppercase text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Candidatos</h6>
                            <h2 class="fw-bold text-dark mt-2 mb-0" style="font-size: 2.2rem;"><?= number_format($kpis['total_candidatos'], 0, ',', '.') ?></h2>
                        </div>
                        <div class="icon-circle bg-light text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class='bx bx-user'></i>
                        </div>
                    </div>
                    <p class="mt-3 mb-0 text-muted" style="font-size: 0.85rem;">Talentos cadastrados</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-dark mb-0">Evolução de Vagas Criadas</h5>
                    <div class="btn-group btn-group-sm" role="group" id="chart-controls">
                        <button type="button" class="btn btn-outline-primary active" data-period="semana" onclick="atualizarKpiEvolucao('semana')">Semana</button>
                        <button type="button" class="btn btn-outline-primary" data-period="mes" onclick="atualizarKpiEvolucao('mes')">Mês</button>
                        <button type="button" class="btn btn-outline-primary" data-period="semestre" onclick="atualizarKpiEvolucao('semestre')">Semestre</button>
                    </div>
                </div>
                <!-- KPI Comparativo Display -->
                <div class="row align-items-center h-75 px-3">
                    <!-- KPI Side -->
                    <div class="col-md-4 d-flex flex-column justify-content-center border-end">
                        <!-- Loading Overlay -->
                        <div id="kpi-loader" class="d-none position-absolute w-100 h-100 top-0 start-0 bg-white d-flex align-items-center justify-content-center" style="z-index: 10;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                        </div>

                        <div id="kpi-content" class="w-100">
                            <p class="text-uppercase text-secondary fw-bold small mb-1">Total no Período</p>
                            <h1 class="fw-bold text-dark display-3 mb-2" id="kpi-total">
                                <?= $kpi_evolucao['total_atual'] ?>
                            </h1>

                            <div class="d-flex align-items-center gap-2">
                                <span id="kpi-badge" class="badge rounded-pill fs-6 <?= $kpi_evolucao['diferenca'] >= 0 ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' ?>">
                                    <?= $kpi_evolucao['diferenca'] >= 0 ? '+' : '' ?><?= $kpi_evolucao['diferenca'] ?>
                                    (<?= $kpi_evolucao['diferenca'] >= 0 ? '+' : '' ?><?= $kpi_evolucao['percentual'] ?>%)
                                </span>
                                <span class="text-muted small">vs. período anterior</span>
                            </div>
                        </div>
                    </div>
                    <!-- Chart Side -->
                    <div class="col-md-8 h-100">
                        <div id="chart-loader" class="d-none position-absolute w-100 h-100 top-0 start-0 bg-white d-flex align-items-center justify-content-center" style="z-index: 10;">
                            <!-- Loader shared/separate -->
                        </div>
                        <div style="height: 220px; width: 100%;">
                            <canvas id="graficoEvolucao"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold text-dark mb-4">Por Setor</h5>
                <div style="height: 250px;">
                    <canvas id="graficoSetor"></canvas>
                </div>
                <div class="mt-4 text-center">
                    <small class="text-muted">Principais áreas demandando mão de obra.</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela Rápida & Mapa -->
    <div class="row g-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0">Geolocalização de Oportunidades vs. Talentos</h5>
                    <!-- Legenda do Mapa -->
                    <div class="d-flex gap-3 align-items-center bg-light px-3 py-1 rounded">
                        <div class="d-flex align-items-center gap-2">
                            <span style="display:inline-block; width: 12px; height: 12px; border-radius: 50%; background-color: #00bda5;"></span>
                            <small class="text-secondary fw-bold">Vagas</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span style="display:inline-block; width: 12px; height: 12px; border-radius: 50%; background-color: #f2545b;"></span>
                            <small class="text-secondary fw-bold">Candidatos</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="map-calor" style="height: 500px; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal Vagas -->
<div class="modal fade" id="modalVagas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Detalhamento de Vagas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-4">Listagem das últimas vagas cadastradas no portal.</p>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 text-secondary small text-uppercase">Título</th>
                                <th class="border-0 text-secondary small text-uppercase">Empresa</th>
                                <th class="border-0 text-secondary small text-uppercase">Salário</th>
                                <th class="border-0 text-secondary small text-uppercase">Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($lista_vagas)): ?>
                                <?php foreach ($lista_vagas as $vaga): ?>
                                    <tr>
                                        <td><span class="fw-bold text-primary"><?= htmlspecialchars($vaga['titulo']) ?></span></td>
                                        <td><?= htmlspecialchars($vaga['nome_empresa'] ?? 'Confidencial') ?></td>
                                        <td>
                                            <?php
                                            if (!empty($vaga['salario_a_combinar'])) {
                                                echo 'A Combinar';
                                            } elseif (!empty($vaga['faixa_salarial'])) {
                                                echo htmlspecialchars($vaga['faixa_salarial']);
                                            } elseif (!empty($vaga['faixa_salarial_min'])) {
                                                $min = number_format($vaga['faixa_salarial_min'], 2, ',', '.');
                                                $max = !empty($vaga['faixa_salarial_max']) ? number_format($vaga['faixa_salarial_max'], 2, ',', '.') : null;
                                                echo "R$ " . $min . ($max ? " - R$ " . $max : "");
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </td>
                                        <td class="text-muted small"><?= date('d/m/Y', strtotime($vaga['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Nenhuma vaga encontrada.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Empresas -->
<div class="modal fade" id="modalEmpresas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Empresas Parceiras</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-4">Empresas recentemente cadastradas e ativas.</p>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 text-secondary small text-uppercase">Empresa</th>
                                <th class="border-0 text-secondary small text-uppercase">Setor</th>
                                <th class="border-0 text-secondary small text-uppercase">Vagas Ativas</th>
                                <th class="border-0 text-secondary small text-uppercase">Cidade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($lista_empresas)): ?>
                                <?php foreach ($lista_empresas as $empresa): ?>
                                    <tr>
                                        <td><span class="fw-bold text-dark"><?= htmlspecialchars($empresa['nome_empresa']) ?></span></td>
                                        <td><?= htmlspecialchars($empresa['tipo'] ?? 'Empresa') ?></td>
                                        <td><span class="badge bg-success bg-opacity-10 text-success">Ativa</span></td>
                                        <td class="text-muted small"><?= htmlspecialchars($empresa['cidade'] . ' - ' . $empresa['estado']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Nenhuma empresa encontrada.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Candidatos -->
<div class="modal fade" id="modalCandidatos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Talentos Recentes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-4">Últimos candidatos cadastrados no banco de talentos.</p>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 text-secondary small text-uppercase">Nome</th>
                                <th class="border-0 text-secondary small text-uppercase">Área de Interesse</th>
                                <th class="border-0 text-secondary small text-uppercase">Cidade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($lista_candidatos)): ?>
                                <?php foreach ($lista_candidatos as $candidato): ?>
                                    <tr>
                                        <td><span class="fw-bold text-dark"><?= htmlspecialchars($candidato['nome']) ?></span></td>
                                        <td>Disponível</td> <!-- Ajuste se tiver area_interesse -->
                                        <td class="text-muted small"><?= htmlspecialchars($candidato['cidade'] . ' - ' . $candidato['estado']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Nenhum candidato recente.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Dependências JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://leaflet.github.io/Leaflet.heat/dist/leaflet-heat.js"></script>

<script>
    // --- Configurações Gráficas (Estilo Clean) ---
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#6c757d';

    // --- Nova Lógica do Gráfico de Evolução (Mais Robusta) ---

    let chartEvolucao = null; // Instância global
    const chartCanvas = document.getElementById('graficoEvolucao');
    const chartLoader = document.getElementById('chart-loader');

    // Configuração Base do Chart.js
    const chartConfig = {
        type: 'bar', // Mudando para Barra para testar visualização melhor
        data: {
            labels: [],
            datasets: [{
                label: 'Novas Vagas',
                data: [],
                backgroundColor: '#ff7a59',
                borderRadius: 4,
                barThickness: 'flex',
                maxBarThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        borderDash: [2, 2],
                        color: '#f0f0f0'
                    },
                    ticks: {
                        font: {
                            size: 10
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 10
                        }
                    }
                }
            }
        }
    };

    // 1. Inicializa o gráfico com dados do PHP (carga inicial)
    function iniciarGrafico() {
        // Dados vindos do PHP (Server Side Render)
        const labelsIniciais = <?= $grafico_evolucao['labels'] ?>;
        const dadosIniciais = <?= $grafico_evolucao['data'] ?>;

        chartConfig.data.labels = labelsIniciais;
        chartConfig.data.datasets[0].data = dadosIniciais;

        const ctx = chartCanvas.getContext('2d');
        chartEvolucao = new Chart(ctx, chartConfig);

        // Marca o botão 'Semestre' como ativo inicialmente, pois é o padrão do Controller
        atualizarBotoes('semestre');
    }

    // 2. Função chamada pelos botões
    function mudarPeriodoGrafico(periodo) {
        if (!chartEvolucao) return;

        // Visual: Loading e Botões
        chartLoader.classList.remove('d-none');
        atualizarBotoes(periodo);

        // Fetch de dados
        const url = `<?= BASE_URL ?>dashboard/dadosEvolucao?periodo=${periodo}`;

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Erro na rede');
                return response.json();
            })
            .then(data => {
                // Atualiza dados do gráfico
                chartEvolucao.data.labels = data.labels;
                chartEvolucao.data.datasets[0].data = data.data;

                // Animação suave de update
                chartEvolucao.update();
            })
            .catch(err => {
                console.error('Erro ao buscar dados:', err);
                alert('Não foi possível atualizar o gráfico.');
            })
            .finally(() => {
                // Remove Loading
                chartLoader.classList.add('d-none');
            });
    }

    // 3. Gerencia classes CSS dos botões
    function atualizarBotoes(ativo) {
        document.querySelectorAll('#chart-controls button').forEach(btn => {
            if (btn.dataset.period === ativo) {
                btn.classList.add('active', 'btn-primary');
                btn.classList.remove('btn-outline-primary');
            } else {
                btn.classList.remove('active', 'btn-primary');
                btn.classList.add('btn-outline-primary');
            }
        });
    }

    // Dispara a inicialização
    document.addEventListener('DOMContentLoaded', iniciarGrafico);

    // --- Nova Lógica do KPI Comparativo + Gráfico ---
    function atualizarKpiEvolucao(periodo) {
        const elTotal = document.getElementById('kpi-total');
        const elBadge = document.getElementById('kpi-badge');
        const elLoader = document.getElementById('kpi-loader');
        const elContent = document.getElementById('kpi-content');

        // Visual: Loading
        if (elLoader) elLoader.classList.remove('d-none');
        if (elContent) elContent.style.opacity = '0.5';

        // Atualiza Botões
        atualizarBotoes(periodo);

        // Request
        fetch(`<?= BASE_URL ?>dashboard/dadosEvolucao?periodo=${periodo}`)
            .then(res => res.json())
            .then(data => {
                // 1. Atualiza KPI Numérico
                const kpi = data.kpi;
                if (elTotal) elTotal.innerText = kpi.total_atual;

                const diff = kpi.diferenca;
                const pct = kpi.percentual;
                const isPos = diff >= 0;

                if (elBadge) {
                    elBadge.className = `badge rounded-pill fs-6 ${isPos ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger'}`;
                    const sinal = isPos ? '+' : '';
                    elBadge.innerText = `${sinal}${diff} (${sinal}${pct}%)`;
                }

                // 2. Atualiza Gráfico
                if (chartEvolucao && data.grafico) {
                    chartEvolucao.data.labels = data.grafico.labels;
                    chartEvolucao.data.datasets[0].data = data.grafico.data;
                    chartEvolucao.update();
                }
            })
            .catch(err => {
                console.error(err);
                alert('Não foi possível atualizar o indicador.');
            })
            .finally(() => {
                if (elLoader) elLoader.classList.add('d-none');
                if (elContent) elContent.style.opacity = '1';
            });
    }

    // Gráfico Setores (Doughnut - Limpo sem legenda)
    const ctxSetor = document.getElementById('graficoSetor').getContext('2d');
    new Chart(ctxSetor, {
        type: 'doughnut',
        data: {
            labels: <?= $grafico_setor['labels'] ?>,
            datasets: [{
                data: <?= $grafico_setor['data'] ?>,
                backgroundColor: ['#00bda5', '#6a78d1', '#ff7a59', '#f2545b', '#ffc107'],
                borderWidth: 0,
                hoverOffset: 12
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    display: true, // Reativando legenda
                    position: 'right',
                    labels: {
                        usePointStyle: true,
                        font: {
                            size: 11
                        }
                    }
                },
                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            let value = context.parsed;
                            let total = context.chart._metasets[context.datasetIndex].total;
                            let percentage = ((value / total) * 100).toFixed(1) + "%";
                            return label + value + ' (' + percentage + ')';
                        }
                    }
                }
            }
        }
    });

    // --- Mapa Inteligente (Vagas + Candidatos) ---
    const pontosVagas = <?= $pontos_vagas ?>;
    const pontosCandidatos = <?= $pontos_candidatos ?>;

    // Inicializa Mapa (Padrão Fallback)
    const map = L.map('map-calor').setView([-23.420999, -51.933056], 12);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CartoDB',
        maxZoom: 19
    }).addTo(map);

    const bounds = L.latLngBounds();
    let hasData = false;

    // 1. Processar Vagas (Verde)
    if (pontosVagas.length > 0) {
        pontosVagas.forEach(p => bounds.extend([p.latitude, p.longitude]));
        hasData = true;

        if (pontosVagas.length < 10) {
            // Modo Precisão: Vagas (Poucos dados -> Mostra Markers)
            pontosVagas.forEach(p => {
                L.circleMarker([p.latitude, p.longitude], {
                    radius: 7,
                    fillColor: "#00bda5",
                    color: "#fff",
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.9
                }).bindPopup(`<b>Vaga: ${p.titulo}</b>`).addTo(map);
            });
        } else {
            // Modo Heatmap (Muitos dados -> Mapa de Calor)
            const heatPointsVagas = pontosVagas.map(p => [p.latitude, p.longitude, 0.8]);
            L.heatLayer(heatPointsVagas, {
                radius: 35,
                blur: 20,
                maxZoom: 14,
                gradient: {
                    0.4: '#aeece6',
                    0.65: '#00bda5',
                    1.0: '#008c7a'
                }
            }).addTo(map);
        }
    }

    // 2. Processar Candidatos (Vermelho)
    if (pontosCandidatos.length > 0) {
        pontosCandidatos.forEach(p => bounds.extend([p.latitude, p.longitude]));
        hasData = true;

        if (pontosCandidatos.length < 10) {
            // Modo Precisão: Candidatos
            pontosCandidatos.forEach(p => {
                L.circleMarker([p.latitude, p.longitude], {
                    radius: 6,
                    fillColor: "#f2545b",
                    color: "#fff",
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.9
                }).bindPopup(`<b>Candidato</b><br>${p.cidade}`).addTo(map);
            });

            // Aviso discreto (Toast)
            showMapToast(`Visualização de precisão: ${pontosCandidatos.length} candidatos encontrados.`);
        } else {
            // Modo Heatmap
            const heatPointsCand = pontosCandidatos.map(p => [p.latitude, p.longitude, 0.6]);
            L.heatLayer(heatPointsCand, {
                radius: 25,
                blur: 15,
                maxZoom: 14,
                gradient: {
                    0.4: '#ffcccc',
                    0.65: '#f2545b',
                    1.0: '#b02a30'
                }
            }).addTo(map);
        }
    }

    // 3. Zoom Inteligente (FitBounds)
    // Ajusta o zoom para caber todos os pontos na tela
    if (hasData) {
        map.fitBounds(bounds, {
            padding: [50, 50],
            maxZoom: 15
        });
    }

    // Helper Toast (Notificação flutuante no mapa)
    function showMapToast(msg) {
        const toast = document.createElement('div');
        toast.style.cssText = "position: absolute; bottom: 20px; right: 20px; background: rgba(255,255,255,0.95); padding: 12px 20px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); z-index: 1000; font-family: 'Inter', sans-serif; font-size: 0.85rem; color: #333; display: flex; align-items: center; gap: 10px; border-left: 4px solid #f2545b; transform: translateY(20px); transition: all 0.3s ease; opacity: 0;";
        toast.innerHTML = `<i class='bx bx-check-circle' style='color: #f2545b; font-size: 1.2rem;'></i> <span style='font-weight: 500;'>${msg}</span>`;

        const mapContainer = document.getElementById('map-calor');
        if (mapContainer) {
            mapContainer.style.position = 'relative';
            mapContainer.appendChild(toast);

            // Animation
            requestAnimationFrame(() => {
                toast.style.transform = 'translateY(0)';
                toast.style.opacity = '1';
            });

            setTimeout(() => {
                toast.style.transform = 'translateY(20px)';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 6000);
        }
    }
</script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

    .hover-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .hover-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05) !important;
    }

    .btn-white:hover {
        background-color: #f8f9fa !important;
    }
</style>