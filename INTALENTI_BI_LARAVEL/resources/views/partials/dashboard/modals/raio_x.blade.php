<!-- Modal Raio-X -->
<div x-show="modalOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
    style="display: none;">
    <div @click.away="closeModal()"
        class="bg-surface w-full max-w-[95vw] 2xl:max-w-[1500px] overflow-hidden border border-border-tron shadow-2xl flex flex-col max-h-[95vh] rounded-cards relative">
        
        <!-- Botão Fechar reto no topo direito -->
        <button @click="closeModal()" class="absolute top-0 right-0 p-4 bg-panel hover:bg-border-tron text-main transition-colors rounded-none z-10">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <div class="p-8 md:p-12 overflow-y-auto">
            <div class="mb-10 max-w-3xl">
                <h3 class="text-4xl font-normal font-display text-main tracking-tight mb-2">Análise Salarial</h3>
                <p class="text-lg text-muted" x-text="'Enquadramento de mercado para ' + (selectedFunc.nome ? selectedFunc.nome : '')"></p>
            </div>

            <!-- Cargo Info -->
            <div class="bg-panel rounded-cards p-8 md:p-10 mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-8 border border-neon/30 shadow-[0_0_15px_rgba(255,85,0,0.05)] relative overflow-hidden">
                <div class="absolute top-0 left-0 w-2 h-full bg-neon"></div>
                <div>
                    <div class="text-[10px] uppercase font-medium text-muted mb-2">Cargo Atual</div>
                    <div class="text-2xl font-normal font-display text-main break-words" x-text="selectedFunc.cargo ? selectedFunc.cargo.nome : '-'"></div>
                    <div class="text-sm text-muted mt-2" x-text="'Tempo de casa: ' + formatTempoCasa(selectedFunc.tempo_de_casa_meses)"></div>
                </div>
                <div class="text-left md:text-right">
                    <div class="text-[10px] uppercase font-medium text-muted mb-2">Senioridade</div>
                    <div class="text-xl font-normal font-display text-main flex items-center gap-3">
                        <span x-text="selectedFunc.senioridadeSugerida"></span>
                        <span class="text-muted text-sm font-sans" x-text="'Nível ' + selectedFunc.nivel_calculado"></span>
                    </div>
                </div>
            </div>

            <!-- Salary Blocks -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div class="bg-canvas border border-border-tron rounded-cards p-8">
                    <div class="text-[10px] font-medium text-muted uppercase mb-4">Salário Atual</div>
                    <div class="text-4xl font-normal font-display text-main break-words whitespace-normal" x-text="formatMoney(selectedFunc.salario_atual)"></div>
                </div>
                <div class="bg-canvas border border-border-tron rounded-cards p-8 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-8 h-8 bg-neon/10 flex items-center justify-center">
                        <div class="w-1.5 h-1.5 rounded-full bg-neon"></div>
                    </div>
                    <div class="text-[10px] font-medium text-muted uppercase mb-4">Salário Ideal (Curva do Mercado)</div>
                    <div class="text-4xl font-normal font-display text-main break-words whitespace-normal" x-text="formatMoney(selectedFunc.tabelaIdeal)"></div>
                    <div class="text-xs text-muted mt-4" x-text="'Calculado a partir do Nível ' + selectedFunc.nivel_calculado + ' (Progresso Geométrico)'"></div>
                </div>
            </div>

            <!-- Status Banner -->
            <div class="bg-panel p-6 mb-12 border-l-[3px] border-l-main flex items-center gap-6 rounded-r-cards shadow-none">
                <div>
                    <div class="font-display font-normal text-xl mb-1 text-main" x-text="selectedFunc.recomendacao + ' (' + selectedFunc.status + ')'"></div>
                    <div class="text-sm text-muted font-medium" x-text="selectedFunc.motivoStatus"></div>
                </div>
            </div>

            <!-- Plataformas de Mercado -->
            <div x-show="selectedFunc.market_fontes" class="mb-12">
                <h4 class="text-[10px] font-medium text-muted uppercase mb-6 tracking-widest border-b border-border-tron pb-3">Fontes de Mercado</h4>
                <div class="flex flex-wrap justify-center gap-6">
                    <template x-for="(fonte, key) in selectedFunc.market_fontes" :key="key">
                        <div class="flex-1 min-w-[280px] max-w-md bg-canvas border border-border-tron rounded-cards p-6 hover:border-neon/50 transition-all duration-300">
                            <div class="text-sm font-medium text-main uppercase mb-6 truncate tracking-wide" x-text="fonte.nome"></div>

                            <div class="space-y-4 font-sans">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-muted">Piso</span>
                                    <span class="text-main font-medium" x-text="formatMoney(fonte.piso)"></span>
                                </div>
                                <div class="flex justify-between items-center text-sm py-2 border-y border-border-tron">
                                    <span class="text-main font-medium">Média</span>
                                    <span class="text-main font-medium" x-text="formatMoney(fonte.mediana)"></span>
                                </div>
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-muted">Teto</span>
                                    <span class="text-main font-medium" x-text="formatMoney(fonte.teto)"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Matrix Table -->
            <div class="bg-canvas border border-border-tron overflow-hidden rounded-cards mb-4">
                <div class="bg-panel text-main text-left px-6 py-4 text-[10px] font-medium uppercase tracking-widest border-b border-border-tron">
                    Classe de Salários (Projeção Base Mercado)
                </div>
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-center text-xs min-w-[900px]">
                        <thead class="bg-transparent border-b border-border-tron text-muted uppercase text-[10px]">
                            <tr>
                                <th class="p-4 text-left border-r border-border-tron font-normal">Senioridade</th>
                                <template x-for="n in 12">
                                    <th class="p-4 border-r border-border-tron last:border-0 font-normal"
                                        :class="selectedFunc.nivel_calculado === n ? 'bg-panel text-main font-medium border-b-2 border-b-neon' : ''"
                                        x-text="'Nível ' + n"></th>
                                </template>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-tron">
                            <tr class="bg-transparent hover:bg-panel/50 transition-colors">
                                <td class="p-4 text-muted text-left border-r border-border-tron uppercase text-[10px]">Categorias</td>
                                <td colspan="3" class="p-4 text-main border-r border-border-tron">Júnior</td>
                                <td colspan="5" class="p-4 text-main border-r border-border-tron">Pleno</td>
                                <td colspan="4" class="p-4 text-main">Sênior</td>
                            </tr>
                            <tr class="bg-transparent hover:bg-panel/50 transition-colors">
                                <td class="p-4 text-muted text-left border-r border-border-tron uppercase text-[10px]">Valores</td>
                                <template x-for="n in 12">
                                    <td class="p-4 border-r border-border-tron last:border-0 whitespace-nowrap"
                                        :class="selectedFunc.nivel_calculado === n ? 'text-neon font-medium bg-panel/50' : 'text-main'"
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
