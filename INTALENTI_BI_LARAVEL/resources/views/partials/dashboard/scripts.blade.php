<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('dashboardData', () => ({
            sidebarOpen: false,
            currentTab: 'cenarios',
            tabs: [
                { id: 'cenarios', name: 'Simulador' },
                { id: 'enquadramento', name: 'Enquadramento' },
                { id: 'dissidio', name: 'Dissídio' },
                { id: 'elegibilidade', name: 'Elegibilidade' },
                { id: 'mercado', name: 'Pesquisa de Mercado' },
                { id: 'convencoes', name: 'Convenções Coletivas' },
                { id: 'financeiro', name: 'Visão Financeira (Risco)' }
            ],

            cargos: @json($cargos),
            funcionariosRaw: @json($funcionarios),
            marketData: @json($marketData),
            matrizSalarial: @json($matrizSalarial),
            convencoes: @json($convencoes ?? []),
            convencaoAtiva: @json($convencaoAtiva ?? null),

            selectedCargoId: '',
            filtroNivelSimulador: '',
            novoSalario: 0,
            novasVagas: 0,

            searchElegibilidade: '',
            searchDissidio: '',
            filtroCargoEnquadramento: '',

            porcentagemDissidio: 0,

            modalOpen: false,
            selectedFunc: {},
            
            marketRaioXOpen: false,
            selectedMarket: {},

            chartInstance: null,

            sortCol: 'nome',
            sortAsc: true,
            statusSortState: 0,

            // Estado para Nova Intenção de Vaga & Pesquisa por CEP
            showNovaVagaModal: false,
            carregandoCep: false,
            novaVagaForm: {
                cargo: '',
                cep: ''
            },
            vagaResultado: null,

            async consultarVagaCep() {
                if (!this.novaVagaForm.cargo || this.novaVagaForm.cargo.trim() === '') {
                    alert('Por favor, informe o cargo pretendido.');
                    return;
                }
                this.carregandoCep = true;
                try {
                    const response = await fetch('{{ route("plano-cargos.pesquisa-cep") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.novaVagaForm)
                    });
                    const data = await response.json();
                    this.vagaResultado = data;
                } catch (e) {
                    console.error('Erro ao pesquisar mercado por CEP:', e);
                    alert('Falha ao consultar pesquisa de mercado por CEP.');
                } finally {
                    this.carregandoCep = false;
                }
            },

            get niveisDisponiveis() {
                if (!this.selectedCargoId) return [];

                const funcsDoCargo = this.funcionariosRaw.filter(f => f.cargo_id == this.selectedCargoId);
                let niveis = funcsDoCargo.map(f => Number(f.nivel_mais_proximo)).filter(n => n > 0);

                return [...new Set(niveis)].sort((a, b) => a - b);
            },

            get globalHeadcount() {
                return this.funcionariosRaw.length;
            },

            get globalFolhaMensal() {
                return this.funcionariosRaw.reduce((acc, f) => acc + Number(f.salario_atual || 0), 0);
            },

            get totalFuncionariosCargo() {
                if (!this.selectedCargoId) return 0;
                let funcs = this.funcionariosRaw.filter(f => f.cargo_id == this.selectedCargoId);
                if (this.filtroNivelSimulador) {
                    funcs = funcs.filter(f => f.nivel_mais_proximo == this.filtroNivelSimulador);
                }
                return funcs.length;
            },

            get mediaSalarialAtual() {
                if (!this.selectedCargoId) return 0;

                const cargoObj = this.cargos.find(c => c.id == this.selectedCargoId);
                if (cargoObj && this.matrizSalarial[cargoObj.nome]) {
                    const niveis = this.matrizSalarial[cargoObj.nome];
                    if (this.filtroNivelSimulador) {
                        return Number(niveis[this.filtroNivelSimulador - 1] || 0);
                    }
                    return niveis.reduce((a, b) => Number(a) + Number(b), 0) / niveis.length;
                }

                let funcFilter = this.funcionariosRaw.filter(f => f.cargo_id == this.selectedCargoId);
                if (this.filtroNivelSimulador) {
                    funcFilter = funcFilter.filter(f => f.nivel_mais_proximo == this.filtroNivelSimulador);
                }
                if (funcFilter.length === 0) return 0;
                let soma = funcFilter.reduce((acc, func) => acc + Number(func.salario_atual || 0), 0);
                return soma / funcFilter.length;
            },

            autoFillNovoSalario() {
                this.novoSalario = Number(this.mediaSalarialAtual.toFixed(2));
            },

            get impactoMensal() {
                if (!this.novoSalario || this.totalFuncionariosCargo === 0) return 0;
                const custoNovasVagas = (this.novasVagas || 0) * this.novoSalario;
                const impactoAjuste = (this.novoSalario - this.mediaSalarialAtual) * this.totalFuncionariosCargo;
                return (impactoAjuste < 0 ? 0 : impactoAjuste) + custoNovasVagas;
            },

            get impactoAnual() {
                return this.impactoMensal * 12;
            },

            get globalFolhaProjetada() {
                return this.globalFolhaMensal + this.impactoMensal + this.dissidioGlobalMensal;
            },

            get globalImpactoAnualBruto() {
                return (this.globalFolhaProjetada - this.globalFolhaMensal) * 12;
            },

            get dissidioGlobalMensal() {
                const fator = (this.porcentagemDissidio || 0) / 100;
                return this.funcionariosRaw.reduce((acc, f) => acc + (Number(f.salario_atual || 0) * fator), 0);
            },

            get resumoMatrizCargo() {
                if (!this.filtroCargoEnquadramento) return null;
                const niveis = this.matrizSalarial[this.filtroCargoEnquadramento];
                if (!niveis || niveis.length < 12) return null;

                const piso = Number(niveis[0]);
                const mediana = Number(niveis[4]);
                const teto = Number(niveis[11]);
                const evolucao = piso > 0 ? (((Number(niveis[1]) - piso) / piso) * 100).toFixed(1) : 0;
                const amplitude = piso > 0 ? (((teto - piso) / piso) * 100).toFixed(1) : 0;

                return { piso, mediana, teto, evolucao, amplitude };
            },

            get arrayProjetadoDissidio() {
                const fator = (this.porcentagemDissidio || 0) / 100;
                let lista = this.funcionariosRaw.map(f => {
                    const sal = Number(f.salario_atual || 0);
                    return {
                        ...f,
                        aumento_valor: sal * fator,
                        novo_salario: sal + (sal * fator)
                    };
                });

                if (this.searchDissidio) {
                    const termo = this.searchDissidio.toLowerCase();
                    lista = lista.filter(f => f.nome.toLowerCase().includes(termo) || (f.cargo && f.cargo.nome.toLowerCase().includes(termo)));
                }

                return this.ordenarArray(lista);
            },

            get arrayFiltradoEnquadramento() {
                let processados = this.funcionariosRaw.map(f => {
                    let nivel = Number(f.nivel_mais_proximo || 1);
                    const mesesCasa = Number(f.tempo_de_casa_meses || 0);

                    let senioridadeSugerida = 'Júnior';
                    let tempoMedioEsperado = '0–2 anos';

                    if (nivel >= 9 || mesesCasa > 60) {
                        senioridadeSugerida = 'Sênior';
                        tempoMedioEsperado = 'mais de 5 anos';
                        if (nivel < 9) nivel = 9;
                    } else if ((nivel >= 4 && nivel <= 8) || mesesCasa > 24) {
                        senioridadeSugerida = 'Pleno';
                        tempoMedioEsperado = '2–5 anos';
                        if (nivel < 4) nivel = 4;
                    } else {
                        if (nivel > 3) nivel = 3;
                    }

                    const cargoNome = f.cargo ? f.cargo.nome.toLowerCase() : '';
                    const market = this.marketData.find(m => m.cargo.toLowerCase() === cargoNome && m.status === 'completed');

                    let tabelaIdeal = Number(f.salario_mais_proximo_tabela || 0);
                    let niveisMatriz = [];

                    if (market) {
                        const piso = Number(market.piso);
                        const mediana = Number(market.mediana) || ((piso + Number(market.teto)) / 2);
                        const teto = Number(market.teto);
                        
                        // Progressão Geométrica Fase 1 (Júnior N1 ao Pleno N6)
                        const ratio1 = Math.pow(mediana / piso, 1 / 5);
                        // Progressão Geométrica Fase 2 (Pleno N6 ao Sênior N12)
                        const ratio2 = Math.pow(teto / mediana, 1 / 6);
                        
                        niveisMatriz = Array.from({ length: 12 }, (_, i) => {
                            if (i < 5) {
                                return Math.round(piso * Math.pow(ratio1, i) * 100) / 100;
                            } else {
                                return Math.round(mediana * Math.pow(ratio2, i - 5) * 100) / 100;
                            }
                        });
                        tabelaIdeal = niveisMatriz[nivel - 1] || tabelaIdeal;
                    } else if (f.cargo && this.matrizSalarial[f.cargo.nome]) {
                        niveisMatriz = this.matrizSalarial[f.cargo.nome];
                        tabelaIdeal = niveisMatriz[nivel - 1] || tabelaIdeal;
                    }

                    let status = 'Enquadrado';
                    let recomendacao = 'Alinhado com a Senioridade';
                    let ventrilocStatusClass = 'text-graphite';
                    let ventrilocRecClass = 'text-graphite font-medium underline decoration-mist underline-offset-4 decoration-2';
                    let motivoStatus = tabelaIdeal > 0 ? `Salário ideal estimado em ${this.formatMoney(tabelaIdeal)}` : 'Sem base de mercado';

                    const salAtual = Number(f.salario_atual || 0);
                    if (tabelaIdeal > 0 && salAtual < tabelaIdeal) {
                        const diff = tabelaIdeal - salAtual;
                        const pct = ((diff / tabelaIdeal) * 100).toFixed(1);
                        status = 'Defasagem (Abaixo)';
                        recomendacao = 'Elegível p/ Ajuste Salarial';
                        motivoStatus = `${this.formatMoney(diff)} abaixo do ideal (${pct}%)`;
                        ventrilocStatusClass = 'text-ember font-medium';
                        ventrilocRecClass = 'text-ember font-medium';
                    } else if (tabelaIdeal > 0 && salAtual > (tabelaIdeal * 1.05)) {
                        const diff = salAtual - tabelaIdeal;
                        const pct = ((diff / tabelaIdeal) * 100).toFixed(1);
                        status = 'Acima do Teto';
                        recomendacao = 'Revisar Faixa';
                        motivoStatus = `${this.formatMoney(diff)} acima do ideal (+${pct}%)`;
                        ventrilocStatusClass = 'text-brass font-medium';
                        ventrilocRecClass = 'text-brass font-medium';
                    }

                    let marketFontes = null;
                    if (market && market.raw_data && market.raw_data.fontes) {
                        marketFontes = market.raw_data.fontes;
                    }

                    return { ...f, status, recomendacao, ventrilocStatusClass, ventrilocRecClass, motivoStatus, senioridadeSugerida, tempoMedioEsperado, tabelaIdeal, nivel_calculado: nivel, niveis_matriz: niveisMatriz, market_fontes: marketFontes };
                });

                if (this.filtroCargoEnquadramento) {
                    processados = processados.filter(f => f.cargo && f.cargo.nome === this.filtroCargoEnquadramento);
                }

                if (this.searchElegibilidade) {
                    const termo = this.searchElegibilidade.toLowerCase();
                    processados = processados.filter(f => f.nome.toLowerCase().includes(termo) || (f.cargo && f.cargo.nome.toLowerCase().includes(termo)));
                }

                return this.ordenarArray(processados);
            },

            sortBy(col) {
                if (col === 'status') {
                    this.statusSortState = (this.statusSortState === 3) ? 1 : (this.statusSortState || 0) + 1;
                    this.sortCol = 'status';
                    return;
                }

                if (this.sortCol === col) {
                    this.sortAsc = !this.sortAsc;
                } else {
                    this.sortCol = col;
                    this.sortAsc = true;
                }
            },

            ordenarArray(array) {
                return array.sort((a, b) => {
                    if (this.sortCol === 'status') {
                        const order1 = { 'Enquadrado': 1, 'Acima do Teto': 2, 'Defasagem (Abaixo)': 3 };
                        const order2 = { 'Acima do Teto': 1, 'Defasagem (Abaixo)': 2, 'Enquadrado': 3 };
                        const order3 = { 'Defasagem (Abaixo)': 1, 'Acima do Teto': 2, 'Enquadrado': 3 };

                        let map = order1;
                        if (this.statusSortState === 1) map = order1;
                        else if (this.statusSortState === 2) map = order2;
                        else if (this.statusSortState === 3) map = order3;

                        let rankA = map[a.status] || 99;
                        let rankB = map[b.status] || 99;

                        if (rankA === rankB) {
                            return String(a.nome).localeCompare(String(b.nome));
                        }
                        return rankA - rankB;
                    }

                    let valA = a;
                    let valB = b;

                    const parts = this.sortCol.split('.');
                    for (let part of parts) {
                        valA = valA ? valA[part] : '';
                        valB = valB ? valB[part] : '';
                    }

                    if (typeof valA === 'number' && typeof valB === 'number') {
                        return this.sortAsc ? valA - valB : valB - valA;
                    }

                    valA = String(valA).toLowerCase();
                    valB = String(valB).toLowerCase();

                    if (valA < valB) return this.sortAsc ? -1 : 1;
                    if (valA > valB) return this.sortAsc ? 1 : -1;
                    return 0;
                });
            },

            formatTempoCasa(meses) {
                const m = Number(meses || 0);
                const anos = Math.floor(m / 12);
                const rest = m % 12;
                return `${anos}a e ${rest}m`;
            },

            formatMoney(value) {
                return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0);
            },

            openModal(func) {
                this.selectedFunc = func;
                this.modalOpen = true;
            },

            closeModal() {
                this.modalOpen = false;
                setTimeout(() => { this.selectedFunc = {}; }, 200);
            },
            
            openMarketRaioX(marketId) {
                const market = this.marketData.find(m => m.id === marketId);
                if (!market) return;
                
                if (!market.niveis_matriz && market.status === 'completed') {
                    const piso = Number(market.piso);
                    const mediana = Number(market.mediana) || ((piso + Number(market.teto)) / 2);
                    const teto = Number(market.teto);
                    const ratio1 = Math.pow(mediana / piso, 1 / 5);
                    const ratio2 = Math.pow(teto / mediana, 1 / 6);
                    market.niveis_matriz = Array.from({ length: 12 }, (_, i) => {
                        if (i < 5) return Math.round(piso * Math.pow(ratio1, i) * 100) / 100;
                        else return Math.round(mediana * Math.pow(ratio2, i - 5) * 100) / 100;
                    });
                }
                
                this.selectedMarket = market;
                this.marketRaioXOpen = true;
            },

            closeMarketRaioX() {
                this.marketRaioXOpen = false;
                setTimeout(() => { this.selectedMarket = {}; }, 200);
            },

            get riscoEvasao() {
                const enquadrados = this.arrayFiltradoEnquadramento;
                let criticos = [];
                let custoTotalRiscoMensal = 0;
                let impactoSubstituicaoAnual = 0;

                enquadrados.forEach(f => {
                    const salAtual = Number(f.salario_atual || 0);
                    const salIdeal = Number(f.tabelaIdeal || 0);
                    if (salIdeal > 0 && salAtual < salIdeal * 0.9) { // 10% abaixo do mercado
                        const defasagemMensal = salIdeal - salAtual;
                        criticos.push({
                            ...f,
                            defasagemMensal: defasagemMensal,
                            defasagemPercentual: ((defasagemMensal / salIdeal) * 100).toFixed(1)
                        });
                        custoTotalRiscoMensal += salAtual;
                        // Estimativa de substituição: 1 a 2 meses de salário do mercado + defasagem real ao contratar novo
                        impactoSubstituicaoAnual += (salIdeal * 2) + (defasagemMensal * 12);
                    }
                });

                return { criticos, custoTotalRiscoMensal, impactoSubstituicaoAnual };
            },

            init() {
                this.$watch('currentTab', (val) => {
                    if (val === 'cenarios') setTimeout(() => this.renderChart(), 100);
                    if (val === 'financeiro') setTimeout(() => this.renderFinanceiroChart(), 100);
                });
                this.$watch('impactoMensal', () => {
                    if (this.currentTab === 'cenarios') this.renderChart();
                });
                if (this.currentTab === 'cenarios') {
                    this.renderChart();
                }
            },

            renderFinanceiroChart() {
                const ctx = document.getElementById('chartFinanceiro');
                if (!ctx) return;

                if (this.chartFinanceiroInstance) this.chartFinanceiroInstance.destroy();

                const dados = this.arrayFiltradoEnquadramento.reduce((acc, f) => {
                    const sen = f.senioridadeSugerida;
                    if (!acc[sen]) acc[sen] = 0;
                    acc[sen] += Number(f.salario_atual || 0);
                    return acc;
                }, { 'Júnior': 0, 'Pleno': 0, 'Sênior': 0 });

                const isDark = document.documentElement.classList.contains('dark');
                const cores = isDark ? ['#FF5500', '#A0A0A0', '#2A2A2A'] : ['#10B981', '#6B7280', '#D1D5DB'];

                this.chartFinanceiroInstance = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(dados),
                        datasets: [{
                            data: Object.values(dados),
                            backgroundColor: cores,
                            borderWidth: 0
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
                });
            },

            renderChart() {
                const ctx = document.getElementById('chartCenarios');
                if (!ctx) return;

                if (this.chartInstance) this.chartInstance.destroy();

                const isDark = document.documentElement.classList.contains('dark');
                const baseColor = isDark ? '#2A2A2A' : '#D1D5DB';
                const neonColor = isDark ? '#FF5500' : '#10B981';

                this.chartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Custo do Cargo (Mensal)'],
                        datasets: [
                            { label: 'Custo Base Atual', backgroundColor: baseColor, data: [this.mediaSalarialAtual * this.totalFuncionariosCargo] },
                            { label: 'Impacto Projetado', backgroundColor: neonColor, data: [this.impactoMensal] }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { x: { stacked: true }, y: { stacked: true } }
                    }
                });
            }
        }));

        window.addEventListener('theme-changed', () => {
            document.querySelectorAll('[x-data]').forEach(el => {
                const data = Alpine.$data(el);
                if (data.currentTab === 'enquadramento' && typeof data.renderFinanceiroChart === 'function') {
                    data.renderFinanceiroChart();
                }
                if (data.currentTab === 'cenarios' && typeof data.renderChart === 'function') {
                    data.renderChart();
                }
            });
        });
    });
</script>