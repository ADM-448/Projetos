@extends('layouts.app')

@section('content')
    <div x-data="dashboardData()" class="min-h-screen flex overflow-hidden font-sans">

        <!-- Botão Menu Mobile -->
        <button @click="sidebarOpen = !sidebarOpen"
            class="md:hidden fixed top-6 right-6 z-50 p-2 bg-emerald-500 rounded-lg text-white shadow-sm">
            <svg x-show="!sidebarOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
            <svg x-show="sidebarOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <!-- Backdrop Mobile -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="md:hidden fixed inset-0 bg-black/50 z-30"
            style="display: none;"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed md:static inset-y-0 left-0 z-40 w-64 bg-emerald-500 border-r border-emerald-600 flex flex-col shrink-0 transition-transform duration-300 md:translate-x-0 text-white">
            <div class="h-28 flex items-center justify-center p-6 shrink-0">
                <!-- Logo Original IntalenTI -->
                <img src="/img/logo.jpeg" alt="IntalenTI BI"
                    class="max-h-full rounded-2xl object-contain shadow-sm bg-white p-1">
            </div>

            <nav class="flex-1 px-4 py-4 space-y-2 overflow-y-auto">
                <template x-for="tab in tabs" :key="tab.id">
                    <button @click="currentTab = tab.id; sidebarOpen = false"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-full transition-all duration-300 font-medium text-left"
                        :class="currentTab === tab.id ? 'bg-white text-emerald-600 shadow-sm font-bold' : 'text-emerald-50 hover:text-white'">
                        <span x-show="currentTab !== tab.id" class="text-[10px] opacity-70">●</span>
                        <span x-text="tab.name" class="leading-tight text-sm"></span>
                    </button>
                </template>
            </nav>

            <!-- Botões de Importação/Exportação Transparentes -->
            <div class="p-6 space-y-6 shrink-0 mb-4 text-emerald-50 text-sm font-medium">
                <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data" x-ref="importForm"
                    class="m-0">
                    @csrf
                    <input type="file" name="arquivo" x-ref="fileInput" class="hidden" accept=".xlsx,.xls,.csv"
                        @change="$refs.importForm.submit()">
                    <button type="button" @click="$refs.fileInput.click()"
                        class="flex items-center gap-3 hover:text-white transition-colors w-full text-left">
                        <svg class="w-5 h-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Nova Base de Dados
                    </button>
                </form>
                <a href="{{ route('template.download') }}"
                    class="flex items-center gap-3 hover:text-white transition-colors">
                    <svg class="w-5 h-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Baixar Planilha Modelo
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50">

            @if (session('success'))
                <div class="bg-emerald-500 text-white px-4 py-2 text-center text-sm font-bold shadow-sm z-50">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-500 text-white px-4 py-2 text-center text-sm font-bold shadow-sm z-50">
                    {{ session('error') }}
                </div>
            @endif



            <div class="flex-1 overflow-y-auto px-4 sm:px-8 py-8 w-full max-w-full">
                <div class="max-w-[1700px] mx-auto w-full">

                    <!-- KPI Cards Superiores (Iguais ao Vue Original) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                        <div
                            class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col justify-between relative overflow-hidden">
                            <div
                                class="absolute top-4 right-4 bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-1 rounded-full">
                                ↗ 100%</div>
                            <div class="text-sm text-slate-600 font-bold mb-4">Headcount Total</div>
                            <div>
                                <div class="text-4xl font-bold text-slate-800 tracking-tight" x-text="globalHeadcount">
                                </div>
                                <div class="text-xs text-slate-400 mt-2">Colaboradores ativos</div>
                            </div>
                        </div>
                        <div
                            class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col justify-between relative overflow-hidden">
                            <div
                                class="absolute top-4 right-4 border border-slate-200 text-slate-500 text-[10px] font-bold px-2 py-1 rounded-full">
                                Atual</div>
                            <div class="text-sm text-slate-600 font-bold mb-4">Folha Mensal</div>
                            <div class="w-full overflow-hidden">
                                <div class="text-3xl lg:text-4xl font-bold text-slate-800 tracking-tight truncate"
                                    x-text="formatMoney(globalFolhaMensal)"></div>
                                <div class="text-xs text-slate-400 mt-2">Custo base atual</div>
                            </div>
                        </div>
                        <div
                            class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col justify-between relative overflow-hidden">
                            <div
                                class="absolute top-4 right-4 bg-emerald-500 text-white text-[10px] font-bold px-2 py-1 rounded-full">
                                + Ajustes</div>
                            <div class="text-sm text-slate-600 font-bold mb-4">Folha Projetada</div>
                            <div class="w-full overflow-hidden">
                                <div class="text-3xl lg:text-4xl font-bold text-slate-800 tracking-tight truncate"
                                    x-text="formatMoney(globalFolhaProjetada)"></div>
                                <div class="text-xs text-slate-400 mt-2">Impacto mensal projetado</div>
                            </div>
                        </div>
                        <div
                            class="bg-emerald-50 border border-emerald-100 rounded-2xl p-6 shadow-sm flex flex-col justify-between relative overflow-hidden">
                            <div class="text-sm text-slate-600 font-bold mb-4">Impacto Anual</div>
                            <div class="w-full overflow-hidden">
                                <div class="text-3xl lg:text-4xl font-bold text-emerald-600 tracking-tight truncate"
                                    x-text="formatMoney(globalImpactoAnualBruto)"></div>
                                <div class="text-xs text-slate-400 mt-2">Diferença anual bruta</div>
                            </div>
                        </div>
                    </div>

                    <!-- Aba: Cenários (Gráficos) -->
                    <div x-show="currentTab === 'cenarios'" style="display: none;">
                        <div class="mb-8">
                            <h2 class="text-2xl font-bold text-slate-700">Simulador de Cenários</h2>
                            <p class="text-slate-500 text-sm mt-1">Analise salários por cargo e simule novas contratações ou
                                ajustes orçamentários.</p>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                                <h3 class="font-bold text-slate-800 mb-6 border-b pb-3">Controles da Simulação</h3>
                                <div class="space-y-6">
                                    <div>
                                        <label class="block text-xs uppercase font-bold text-slate-600 mb-2">Selecionar
                                            Cargo</label>
                                        <select x-model="selectedCargoId" @change="autoFillNovoSalario()"
                                            class="w-full rounded-lg border-slate-300 text-sm py-2 shadow-sm">
                                            <option value="">Selecione um cargo</option>
                                            <template x-for="cargo in cargos" :key="cargo.id">
                                                <option :value="cargo.id" x-text="cargo.nome"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div x-show="selectedCargoId">
                                        <label class="block text-xs uppercase font-bold text-slate-600 mb-2">Filtrar por
                                            Nível</label>
                                        <select x-model.number="filtroNivelSimulador" @change="autoFillNovoSalario()"
                                            class="w-full rounded-lg border-slate-300 text-sm py-2 shadow-sm">
                                            <template x-if="niveisDisponiveis.length > 0">
                                                <option value="">Todos os Níveis</option>
                                            </template>
                                            <template x-for="n in niveisDisponiveis" :key="n">
                                                <option :value="n" x-text="'Nível ' + n"></option>
                                            </template>
                                            <template x-if="niveisDisponiveis.length === 0">
                                                <option value="">Nenhum nível encontrado (Usando média real)</option>
                                            </template>
                                        </select>
                                    </div>
                                    <div
                                        class="bg-slate-50 rounded-xl p-4 border border-slate-200 flex justify-between items-center text-sm shadow-inner">
                                        <div class="text-slate-500">
                                            <div class="mb-2">Headcount (Cargo):</div>
                                            <div>Salário Base (Matriz):</div>
                                        </div>
                                        <div class="font-bold text-slate-800 text-right">
                                            <div class="mb-2 text-lg" x-text="totalFuncionariosCargo"></div>
                                            <div x-text="formatMoney(mediaSalarialAtual)"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs uppercase font-bold text-slate-600 mb-2">Novo Salário
                                            Proposto (R$)</label>
                                        <input x-model.number="novoSalario" type="number"
                                            class="w-full rounded-lg border-slate-300 text-sm py-2 shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs uppercase font-bold text-slate-600 mb-2">Novas Vagas a
                                            Contratar</label>
                                        <input x-model.number="novasVagas" type="number"
                                            class="w-full rounded-lg border-slate-300 text-sm py-2 shadow-sm"
                                            placeholder="0">
                                    </div>
                                </div>
                            </div>

                            <div
                                class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col">
                                <h3 class="font-bold text-slate-800 mb-6 border-b pb-3">Projeção Orçamentária (Cargo)</h3>
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div class="bg-emerald-500 rounded-xl p-5 text-white shadow-sm">
                                        <div class="text-[10px] uppercase font-bold opacity-80 mb-1">Impacto Adicional
                                            Mensal</div>
                                        <div class="text-2xl sm:text-3xl font-bold break-words"
                                            x-text="formatMoney(impactoMensal)"></div>
                                    </div>
                                    <div class="bg-red-50 rounded-xl p-5 border border-red-100 text-red-500 shadow-sm">
                                        <div class="text-[10px] uppercase font-bold mb-1">Impacto Adicional Anual</div>
                                        <div class="text-2xl sm:text-3xl font-bold break-words"
                                            x-text="formatMoney(impactoAnual)"></div>
                                    </div>
                                </div>
                                <div class="relative w-full flex-1 min-h-[300px]">
                                    <canvas id="chartCenarios"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Aba: Enquadramento e Elegibilidade -->
                    <div x-show="currentTab === 'enquadramento' || currentTab === 'elegibilidade'" style="display: none;">

                        <div class="mb-8 flex flex-col md:flex-row justify-between md:items-end gap-4">
                            <div>
                                <h2 class="text-2xl font-bold text-slate-700 break-words"
                                    x-text="currentTab === 'enquadramento' ? 'Enquadramento de Colaboradores' : 'Avaliação de Desempenho e Mérito'">
                                </h2>
                            </div>
                            <div class="flex gap-4 items-center">
                                <select x-show="currentTab === 'enquadramento'" x-model="filtroCargoEnquadramento"
                                    class="rounded-full border-slate-300 text-sm py-2 px-4 shadow-sm w-48">
                                    <option value="">Todos os Cargos</option>
                                    <template x-for="cargo in cargos" :key="cargo.id">
                                        <option :value="cargo.nome" x-text="cargo.nome"></option>
                                    </template>
                                </select>
                                <input x-model="searchElegibilidade" type="text" placeholder="Buscar colaborador..."
                                    class="w-full sm:w-64 rounded-full border-slate-300 text-sm py-2 px-4 shadow-sm">
                            </div>
                        </div>

                        <!-- Cards de Resumo da Faixa Salarial -->
                        <div x-show="currentTab === 'enquadramento' && filtroCargoEnquadramento && resumoMatrizCargo"
                            class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm text-center">
                                <div class="text-[10px] uppercase font-bold text-slate-500 mb-1">Piso (Júnior N1)</div>
                                <div class="font-bold text-slate-700" x-text="formatMoney(resumoMatrizCargo.piso)"></div>
                            </div>
                            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm text-center">
                                <div class="text-[10px] uppercase font-bold text-slate-500 mb-1">Mediana (Pleno N5)</div>
                                <div class="font-bold text-slate-700" x-text="formatMoney(resumoMatrizCargo.mediana)"></div>
                            </div>
                            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm text-center">
                                <div class="text-[10px] uppercase font-bold text-slate-500 mb-1">Teto (Sênior N12)</div>
                                <div class="font-bold text-slate-700" x-text="formatMoney(resumoMatrizCargo.teto)"></div>
                            </div>
                            <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-200 shadow-sm text-center">
                                <div class="text-[10px] uppercase font-bold text-emerald-700 mb-1">Evolução Degrau</div>
                                <div class="font-bold text-emerald-700" x-text="resumoMatrizCargo.evolucao + '%'"></div>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-xl border border-blue-200 shadow-sm text-center">
                                <div class="text-[10px] uppercase font-bold text-blue-700 mb-1">Amplitude Total</div>
                                <div class="font-bold text-blue-700" x-text="resumoMatrizCargo.amplitude + '%'"></div>
                            </div>
                        </div>

                        <div
                            class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden w-full max-w-full">
                            <div class="overflow-x-auto w-full">
                                <table class="w-full text-left min-w-[1000px]">
                                    <thead class="bg-slate-50 border-b border-slate-200 cursor-pointer">
                                        <tr>
                                            <th @click="sortBy('nome')"
                                                class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase hover:bg-slate-100 transition">
                                                Colaborador ↕</th>
                                            <th @click="sortBy('cargo.nome')"
                                                class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase hover:bg-slate-100 transition">
                                                Função ↕</th>
                                            <th @click="sortBy('tempo_de_casa_meses')"
                                                class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase text-center hover:bg-slate-100 transition">
                                                T. Casa ↕</th>
                                            <th @click="sortBy('salario_atual')"
                                                class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase text-right hover:bg-slate-100 transition">
                                                Salário Atual ↕</th>

                                            <template x-if="currentTab === 'enquadramento'">
                                                <>
                                                    <th @click="sortBy('tabelaIdeal')"
                                                        class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase text-right hover:bg-slate-100 transition">
                                                        Roadmap (Ideal) ↕</th>
                                                    <th @click="sortBy('senioridadeSugerida')"
                                                        class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase text-center hover:bg-slate-100 transition">
                                                        Senioridade ↕</th>
                                                    <th @click="sortBy('status')"
                                                        class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase text-center hover:bg-slate-100 transition">
                                                        Status ↕</th>
                                                </>
                                            </template>

                                            <template x-if="currentTab === 'elegibilidade'">
                                                <>
                                                    <th @click="sortBy('status')"
                                                        class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase text-center hover:bg-slate-100 transition">
                                                        Status ↕</th>
                                                    <th @click="sortBy('senioridadeSugerida')"
                                                        class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase hover:bg-slate-100 transition">
                                                        Senioridade Sugerida ↕</th>
                                                    <th @click="sortBy('recomendacao')"
                                                        class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase hover:bg-slate-100 transition">
                                                        Recomendação Sistema ↕</th>
                                                </>
                                            </template>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="f in arrayFiltradoEnquadramento" :key="f.id">
                                            <tr class="hover:bg-slate-50 transition-colors cursor-pointer"
                                                @click="openModal(f)">
                                                <td class="px-6 py-4 text-sm font-bold text-slate-700 break-words whitespace-normal max-w-[200px]"
                                                    x-text="f.nome"></td>
                                                <td class="px-6 py-4 text-xs text-slate-600 break-words whitespace-normal max-w-[150px]"
                                                    x-text="f.cargo ? f.cargo.nome : '-'"></td>
                                                <td class="px-6 py-4 text-xs text-slate-600 text-center"
                                                    x-text="formatTempoCasa(f.tempo_de_casa_meses)"></td>
                                                <td class="px-6 py-4 text-sm font-bold text-slate-700 text-right"
                                                    x-text="formatMoney(f.salario_atual)"></td>

                                                <template x-if="currentTab === 'enquadramento'">
                                                    <>
                                                        <td class="px-6 py-4 text-sm font-bold text-slate-700 text-right"
                                                            x-text="formatMoney(f.tabelaIdeal)"></td>
                                                        <td class="px-6 py-4 text-xs text-slate-700 text-center font-medium"
                                                            x-text="f.senioridadeSugerida"></td>
                                                        <td class="px-6 py-4 text-center">
                                                            <span
                                                                class="px-3 py-1 rounded-full text-[10px] font-bold uppercase border whitespace-nowrap"
                                                                :class="f.bg + ' ' + f.color + ' border-' + f.color.split('-')[1] + '-200'"
                                                                x-text="f.status"></span>
                                                        </td>
                                                    </>
                                                </template>

                                                <template x-if="currentTab === 'elegibilidade'">
                                                    <>
                                                        <td class="px-6 py-4 text-center">
                                                            <span
                                                                class="px-3 py-1 rounded-full text-[10px] font-bold uppercase border whitespace-nowrap"
                                                                :class="f.bg + ' ' + f.color + ' border-' + f.color.split('-')[1] + '-200'"
                                                                x-text="f.status"></span>
                                                        </td>
                                                        <td
                                                            class="px-6 py-4 text-sm text-slate-700 break-words whitespace-normal max-w-[200px]">
                                                            <div class="font-bold flex items-center gap-1">
                                                                <span x-text="f.senioridadeSugerida"></span>
                                                                <span
                                                                    class="text-[10px] px-2 py-0.5 bg-slate-200 rounded-full"
                                                                    x-text="'Nível ' + f.nivel_calculado"></span>
                                                            </div>
                                                            <div class="text-[10px] text-emerald-600 mt-1"
                                                                x-text="'Esperado: ' + f.tempoMedioEsperado"></div>
                                                        </td>
                                                        <td
                                                            class="px-6 py-4 text-sm text-slate-700 break-words whitespace-normal max-w-[250px]">
                                                            <span
                                                                class="px-3 py-1 text-[10px] font-bold rounded-full border inline-block mb-1 whitespace-nowrap"
                                                                :class="f.bg + ' ' + f.color"
                                                                x-text="f.recomendacao"></span>
                                                            <div class="text-[11px] text-slate-500 leading-tight mt-1"
                                                                x-text="f.motivoStatus"></div>
                                                        </td>
                                                    </>
                                                </template>
                                            </tr>
                                        </template>

                                        <tr x-show="arrayFiltradoEnquadramento.length === 0">
                                            <td colspan="8" class="px-6 py-12 text-center text-slate-500 text-sm">Nenhum
                                                colaborador encontrado para os filtros.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Aba: Dissídio -->
                    <div x-show="currentTab === 'dissidio'" style="display: none;">
                        <div class="mb-8 flex flex-col md:flex-row justify-between md:items-end gap-4">
                            <h2 class="text-2xl font-bold text-slate-700">Projeção de Dissídio Coletivo</h2>
                            <input x-model="searchDissidio" type="text" placeholder="Buscar na tabela..."
                                class="w-full sm:w-64 rounded-full border-slate-300 text-sm py-2 px-4 shadow-sm">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <div class="bg-white border border-emerald-300 rounded-2xl p-6 shadow-sm">
                                <label class="block text-xs font-bold text-emerald-700 uppercase mb-2">Simular Reajuste
                                    (%)</label>
                                <input x-model.number="porcentagemDissidio" type="number" step="0.1"
                                    class="w-full text-2xl font-bold text-slate-800 rounded-xl border-slate-300 focus:ring-emerald-600 focus:border-emerald-600 text-center py-4 shadow-inner"
                                    placeholder="0.0">
                            </div>
                            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm w-full overflow-hidden">
                                <div class="text-xs font-bold text-slate-500 uppercase mb-2">Custo Adicional Mensal</div>
                                <div class="text-3xl font-bold text-red-500 truncate"
                                    x-text="'+ ' + formatMoney(dissidioGlobalMensal)"></div>
                            </div>
                            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm w-full overflow-hidden">
                                <div class="text-xs font-bold text-slate-500 uppercase mb-2">Custo Adicional Anual</div>
                                <div class="text-3xl font-bold text-red-600 truncate"
                                    x-text="'+ ' + formatMoney(dissidioGlobalMensal * 12)"></div>
                            </div>
                        </div>

                        <div
                            class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden w-full max-w-full">
                            <div class="overflow-x-auto w-full">
                                <table class="w-full text-left min-w-[800px]">
                                    <thead class="bg-slate-50 border-b border-slate-200 cursor-pointer">
                                        <tr>
                                            <th @click="sortBy('nome')"
                                                class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase hover:bg-slate-100 transition">
                                                Colaborador ↕</th>
                                            <th @click="sortBy('cargo.nome')"
                                                class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase hover:bg-slate-100 transition">
                                                Cargo ↕</th>
                                            <th @click="sortBy('salario_atual')"
                                                class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase text-right hover:bg-slate-100 transition">
                                                Salário Atual ↕</th>
                                            <th @click="sortBy('aumento_valor')"
                                                class="px-6 py-4 text-[10px] font-bold text-red-500 uppercase text-right hover:bg-slate-100 transition">
                                                Aumento ↕</th>
                                            <th @click="sortBy('novo_salario')"
                                                class="px-6 py-4 text-[10px] font-bold text-emerald-600 uppercase text-right hover:bg-slate-100 transition">
                                                Salário Projetado ↕</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="f in arrayProjetadoDissidio" :key="f.id">
                                            <tr class="hover:bg-slate-50 transition-colors">
                                                <td class="px-6 py-4 text-sm font-bold text-slate-700 break-words whitespace-normal"
                                                    x-text="f.nome"></td>
                                                <td class="px-6 py-4 text-xs text-slate-600 break-words whitespace-normal"
                                                    x-text="f.cargo ? f.cargo.nome : '-'"></td>
                                                <td class="px-6 py-4 text-sm text-slate-500 text-right font-mono"
                                                    x-text="formatMoney(f.salario_atual)"></td>
                                                <td class="px-6 py-4 text-sm font-bold text-red-500 text-right font-mono"
                                                    x-text="'+ ' + formatMoney(f.aumento_valor)"></td>
                                                <td class="px-6 py-4 text-sm font-bold text-emerald-600 text-right font-mono"
                                                    x-text="formatMoney(f.novo_salario)"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Aba: Mercado -->
                    <div x-show="currentTab === 'mercado'" style="display: none;">
                        <div class="mb-8 flex flex-col md:flex-row gap-4 justify-between md:items-center">
                            <h2 class="text-2xl font-bold text-slate-700 break-words whitespace-normal">Pesquisa de Mercado
                                Automática</h2>
                            <form action="{{ route('dashboard.market-sync-all') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="px-6 py-2 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 transition shadow-sm">
                                    Pesquisar Todos
                                </button>
                            </form>
                        </div>

                        <div
                            class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden w-full max-w-full">
                            <div class="overflow-x-auto w-full">
                                <table class="w-full text-left min-w-[800px]">
                                    <thead class="bg-slate-50 border-b border-slate-200">
                                        <tr>
                                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Cargo</th>
                                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">Piso
                                            </th>
                                            <th class="px-6 py-4 text-xs font-bold text-emerald-600 uppercase text-right">
                                                Mediana</th>
                                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">Teto
                                            </th>
                                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">
                                                Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="m in marketData" :key="m.id">
                                            <tr class="hover:bg-slate-50 transition-colors">
                                                <td class="px-6 py-4 text-sm text-slate-700 break-words whitespace-normal font-medium"
                                                    x-text="m.cargo"></td>
                                                <td class="px-6 py-4 text-sm text-slate-500 text-right font-mono"
                                                    x-text="m.piso ? formatMoney(m.piso) : '-'"></td>
                                                <td class="px-6 py-4 text-sm text-emerald-600 font-bold text-right font-mono"
                                                    x-text="m.mediana ? formatMoney(m.mediana) : '-'"></td>
                                                <td class="px-6 py-4 text-sm text-slate-500 text-right font-mono"
                                                    x-text="m.teto ? formatMoney(m.teto) : '-'"></td>
                                                <td class="px-6 py-4 text-xs text-center">
                                                    <span x-show="m.status === 'completed'"
                                                        class="text-green-600 bg-green-100 px-2 py-1 rounded-full font-bold">Atualizado</span>
                                                    <span x-show="m.status === 'pending'"
                                                        class="text-yellow-600 bg-yellow-100 px-2 py-1 rounded-full font-bold">Pendente</span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main>

        <!-- Modal Raio-X -->
        <div x-show="modalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
            style="display: none;">
            <div @click.away="closeModal()"
                class="bg-white rounded-3xl w-full max-w-[95vw] 2xl:max-w-[1500px] overflow-hidden shadow-2xl flex flex-col max-h-[95vh] border border-slate-200">
                <div class="p-6 md:p-8 overflow-y-auto">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-2xl font-bold text-slate-800">Análise salárial do Colaborador</h3>
                            <p class="text-sm text-slate-500 mt-1"
                                x-text="'Análise de mercado e enquadramento para ' + (selectedFunc.nome ? selectedFunc.nome.toUpperCase() : '')">
                            </p>
                        </div>
                        <button @click="closeModal()"
                            class="p-2 text-slate-400 hover:text-slate-700 transition-colors bg-slate-100 hover:bg-slate-200 rounded-full">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Cargo Info -->
                    <div
                        class="bg-slate-50 border border-slate-200 rounded-2xl p-6 mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <div class="text-[10px] uppercase font-bold text-slate-500 mb-1">Cargo Atual</div>
                            <div class="text-xl font-bold text-slate-800 break-words"
                                x-text="selectedFunc.cargo ? selectedFunc.cargo.nome : '-'"></div>
                            <div class="text-xs text-slate-500 mt-2"
                                x-text="'Tempo de casa: ' + formatTempoCasa(selectedFunc.tempo_de_casa_meses)"></div>
                        </div>
                        <div class="text-left md:text-right">
                            <div class="text-[10px] uppercase font-bold text-slate-500 mb-1">Senioridade</div>
                            <div class="text-lg font-bold text-emerald-700 flex items-center gap-2">
                                <span x-text="selectedFunc.senioridadeSugerida"></span>
                                <span class="bg-emerald-100 text-emerald-800 text-xs px-2 py-1 rounded-md font-mono"
                                    x-text="'Nível ' + selectedFunc.nivel_calculado"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Salary Blocks -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 relative overflow-hidden">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-2 h-2 rounded-full bg-slate-400"></div>
                                <div class="text-xs font-bold text-slate-500 uppercase">Salário Atual</div>
                            </div>
                            <div class="text-3xl font-bold text-slate-800 break-words whitespace-normal"
                                x-text="formatMoney(selectedFunc.salario_atual)"></div>
                        </div>
                        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-6 relative overflow-hidden">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                <div class="text-xs font-bold text-emerald-700 uppercase">Salário Ideal (Curva do Mercado)
                                </div>
                            </div>
                            <div class="text-3xl font-bold text-emerald-700 break-words whitespace-normal"
                                x-text="formatMoney(selectedFunc.tabelaIdeal)"></div>
                            <div class="text-xs text-emerald-600 mt-2 opacity-80"
                                x-text="'Calculado a partir do Nível ' + selectedFunc.nivel_calculado + ' (Progresso Geométrico)'">
                            </div>
                        </div>
                    </div>

                    <!-- Status Banner -->
                    <div class="rounded-xl p-5 mb-8 border flex items-center gap-4 shadow-sm"
                        :class="selectedFunc.bg + ' ' + selectedFunc.color + ' border-' + selectedFunc.color.split('-')[1] + '-200'">
                        <div class="p-3 bg-white rounded-full bg-opacity-60 shadow-sm shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold text-lg mb-1"
                                x-text="selectedFunc.recomendacao + ' (' + selectedFunc.status + ')'"></div>
                            <div class="text-sm opacity-90 font-medium" x-text="selectedFunc.motivoStatus"></div>
                        </div>
                    </div>

                    <!-- Plataformas de Mercado -->
                    <div x-show="selectedFunc.market_fontes" class="mb-8">
                        <h4 class="text-sm font-bold text-emerald-700 uppercase mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Pesquisa nas Plataformas
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                            <template x-for="(fonte, key) in selectedFunc.market_fontes" :key="key">
                                <div
                                    class="bg-white border border-emerald-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-300 relative overflow-hidden group">
                                    <div
                                        class="absolute top-0 left-0 w-full h-1 bg-emerald-500 opacity-50 group-hover:opacity-100 transition-opacity">
                                    </div>
                                    <div class="text-sm font-bold text-emerald-700 uppercase mb-4 truncate"
                                        x-text="fonte.nome"></div>

                                    <div class="space-y-0">
                                        <div class="flex justify-between items-center border-b border-slate-100 py-2">
                                            <span
                                                class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Piso</span>
                                            <span class="text-sm font-bold text-slate-800"
                                                x-text="formatMoney(fonte.piso)"></span>
                                        </div>
                                        <div
                                            class="flex justify-between items-center border-b border-slate-100 py-2.5 bg-emerald-50/50 -mx-5 px-5">
                                            <span
                                                class="text-xs font-extrabold text-emerald-600 uppercase tracking-wider">Média</span>
                                            <span class="text-lg font-black text-slate-900"
                                                x-text="formatMoney(fonte.mediana)"></span>
                                        </div>
                                        <div class="flex justify-between items-center py-2">
                                            <span
                                                class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Teto</span>
                                            <span class="text-sm font-bold text-slate-800"
                                                x-text="formatMoney(fonte.teto)"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Matrix Table -->
                    <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                        <div class="bg-emerald-600 text-white text-center py-3 text-xs font-bold uppercase tracking-wider">
                            Classe de Salários (Base Mercado)
                        </div>
                        <div class="overflow-x-auto w-full">
                            <table class="w-full text-center text-xs min-w-[900px]">
                                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase">
                                    <tr>
                                        <th class="p-3 text-left border-r border-slate-200">Senioridade</th>
                                        <template x-for="n in 12">
                                            <th class="p-3 border-r border-slate-100 last:border-0"
                                                :class="selectedFunc.nivel_calculado === n ? 'bg-emerald-100 text-emerald-800' : ''"
                                                x-text="'Nível ' + n"></th>
                                        </template>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    <tr class="bg-slate-50">
                                        <td class="p-3 font-bold text-slate-500 text-left border-r border-slate-200 uppercase">Categorias</td>
                                        <td colspan="3" class="p-3 font-bold text-amber-600 border-r border-slate-200">Júnior</td>
                                        <td colspan="5" class="p-3 font-bold text-blue-600 border-r border-slate-200">Pleno</td>
                                        <td colspan="4" class="p-3 font-bold text-emerald-600">Sênior</td>
                                    </tr>
                                    <tr class="bg-slate-50 font-mono">
                                        <td
                                            class="p-3 font-bold text-slate-600 text-left border-r border-slate-200 uppercase font-sans">
                                            Projeção (R$)</td>
                                        <template x-for="n in 12">
                                            <td class="p-3 border-r border-slate-100 last:border-0 whitespace-nowrap"
                                                :class="selectedFunc.nivel_calculado === n ? 'bg-emerald-600 text-white font-bold text-[13px] shadow-inner' : 'text-slate-600'"
                                                x-text="selectedFunc.niveis_matriz && selectedFunc.niveis_matriz[n-1] ? formatMoney(selectedFunc.niveis_matriz[n-1]) : '-'">
                                            </td>
                                        </template>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboardData', () => ({
                sidebarOpen: false,
                currentTab: 'cenarios',
                tabs: [
                    { id: 'cenarios', name: 'Simulador (Cenários)' },
                    { id: 'enquadramento', name: 'Enquadramento' },
                    { id: 'dissidio', name: 'Dissídio' },
                    { id: 'elegibilidade', name: 'Elegibilidade' },
                    { id: 'mercado', name: 'Pesquisa de Mercado' }
                ],

                cargos: @json($cargos),
                funcionariosRaw: @json($funcionarios),
                marketData: @json($marketData),
                matrizSalarial: @json($matrizSalarial),

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

                chartInstance: null,

                sortCol: 'nome',
                sortAsc: true,
                statusSortState: 0,

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
                            const teto = Number(market.teto);
                            const ratio = Math.pow(teto / piso, 1 / 11);
                            niveisMatriz = Array.from({ length: 12 }, (_, i) => Math.round(piso * Math.pow(ratio, i) * 100) / 100);
                            tabelaIdeal = niveisMatriz[nivel - 1] || tabelaIdeal;
                        } else if (f.cargo && this.matrizSalarial[f.cargo.nome]) {
                            niveisMatriz = this.matrizSalarial[f.cargo.nome];
                            tabelaIdeal = niveisMatriz[nivel - 1] || tabelaIdeal;
                        }

                        let status = 'Enquadrado';
                        let recomendacao = 'Alinhado com a Senioridade';
                        let color = 'text-green-600';
                        let bg = 'bg-green-50';
                        let motivoStatus = tabelaIdeal > 0 ? `Salário ideal estimado em ${this.formatMoney(tabelaIdeal)}` : 'Sem base de mercado';

                        const salAtual = Number(f.salario_atual || 0);
                        if (tabelaIdeal > 0 && salAtual < tabelaIdeal) {
                            const diff = tabelaIdeal - salAtual;
                            const pct = ((diff / tabelaIdeal) * 100).toFixed(1);
                            status = 'Defasagem (Abaixo)';
                            recomendacao = 'Elegível p/ Ajuste Salarial';
                            motivoStatus = `${this.formatMoney(diff)} abaixo do ideal (${pct}%)`;
                            color = 'text-red-600';
                            bg = 'bg-red-50';
                        } else if (tabelaIdeal > 0 && salAtual > (tabelaIdeal * 1.05)) {
                            const diff = salAtual - tabelaIdeal;
                            const pct = ((diff / tabelaIdeal) * 100).toFixed(1);
                            status = 'Acima do Teto';
                            recomendacao = 'Revisar Faixa';
                            motivoStatus = `${this.formatMoney(diff)} acima do ideal (+${pct}%)`;
                            color = 'text-yellow-600';
                            bg = 'bg-yellow-50';
                        }

                        let marketFontes = null;
                        if (market && market.raw_data && market.raw_data.fontes) {
                            marketFontes = market.raw_data.fontes;
                        }

                        return { ...f, status, recomendacao, color, bg, motivoStatus, senioridadeSugerida, tempoMedioEsperado, tabelaIdeal, nivel_calculado: nivel, niveis_matriz: niveisMatriz, market_fontes: marketFontes };
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

                init() {
                    this.$watch('currentTab', (val) => {
                        if (val === 'cenarios') setTimeout(() => this.renderChart(), 100);
                    });
                    this.$watch('impactoMensal', () => {
                        if (this.currentTab === 'cenarios') this.renderChart();
                    });
                    if (this.currentTab === 'cenarios') {
                        this.renderChart();
                    }
                },

                renderChart() {
                    const ctx = document.getElementById('chartCenarios');
                    if (!ctx) return;

                    if (this.chartInstance) this.chartInstance.destroy();

                    this.chartInstance = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Custo do Cargo (Mensal)'],
                            datasets: [
                                { label: 'Custo Base Atual', backgroundColor: '#475569', data: [this.mediaSalarialAtual * this.totalFuncionariosCargo] },
                                { label: 'Impacto Projetado', backgroundColor: '#10B981', data: [this.impactoMensal] }
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
        });
    </script>
@endpush