<!-- Modal Detalhes do Mercado -->
<div x-show="marketRaioXOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
    style="display: none;">
    <div @click.away="closeMarketRaioX()"
        class="bg-surface w-full max-w-[95vw] 2xl:max-w-[1500px] overflow-hidden border border-border-tron shadow-2xl flex flex-col max-h-[95vh] rounded-cards relative">
        
        <!-- Botão Fechar reto no topo direito -->
        <button @click="closeMarketRaioX()" class="absolute top-0 right-0 p-4 bg-panel hover:bg-border-tron text-main transition-colors rounded-none z-10">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <div class="p-8 md:p-12 overflow-y-auto">
            <div class="mb-10 max-w-3xl">
                <h3 class="text-4xl font-normal font-display text-main tracking-tight mb-2">Análise de Mercado</h3>
                <p class="text-lg text-muted" x-text="'Projeção salarial para o cargo de ' + (selectedMarket.cargo ? selectedMarket.cargo : '')"></p>
                <p class="text-sm text-muted mt-2" x-show="(selectedMarket.tags || selectedMarket.competencias_chave || []).length">
                    Competências da pesquisa:
                    <span class="text-neon" x-text="(selectedMarket.tags || selectedMarket.competencias_chave || []).join(', ')"></span>
                </p>
            </div>

            <!-- Plataformas de Mercado -->
            <div x-show="selectedMarket.raw_data && selectedMarket.raw_data.fontes" class="mb-12">
                <h4 class="text-[10px] font-medium text-muted uppercase mb-6 tracking-widest border-b border-border-tron pb-3">Fontes de Mercado</h4>
                <div class="flex flex-wrap justify-center gap-6">
                    <template x-for="(fonte, key) in (selectedMarket.raw_data ? selectedMarket.raw_data.fontes : {})" :key="key">
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
                                    <th class="p-4 border-r border-border-tron last:border-0 font-normal" x-text="'Nível ' + n"></th>
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
                                    <td class="p-4 border-r border-border-tron last:border-0 whitespace-nowrap text-main"
                                        x-text="selectedMarket.niveis_matriz && selectedMarket.niveis_matriz[n-1] ? formatMoney(selectedMarket.niveis_matriz[n-1]) : '-'">
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
