<!-- Modal de Adicionar Nova Vaga & Pesquisa de Mercado por CEP -->
<div x-show="showNovaVagaModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm" style="display: none;"
     x-transition.opacity.duration.250ms>
    <div class="bg-surface border border-border-tron rounded-cards p-6 sm:p-8 w-full max-w-2xl shadow-2xl overflow-y-auto max-h-[90vh]"
         @click.away="showNovaVagaModal = false">
        
        <!-- Header do Modal com Badge Promocional -->
        <div class="flex justify-between items-start mb-4 border-b border-border-tron pb-4">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-xl font-display text-main font-semibold">Nova Intenção de Vaga</h3>
                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/30">
                        Bônus de Degustação
                    </span>
                </div>
                <p class="text-xs text-muted mt-1">
                    Pesquisa salarial automática por CEP e emissão de descrição de cargo com governança CBO.
                </p>
            </div>
            <button @click="showNovaVagaModal = false" class="text-muted hover:text-main p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Formulário de Consulta -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-muted uppercase mb-1">Título do Cargo Pretendido</label>
                <input type="text" x-model="novaVagaForm.cargo" 
                       placeholder="Ex: Faz tudo comercial, Analista de Vendas, etc."
                       class="w-full bg-panel border border-border-tron px-4 py-2 text-main text-sm rounded-md focus:border-neon focus:ring-0">
            </div>
            <div>
                <label class="block text-xs font-medium text-muted uppercase mb-1">CEP do Local de Trabalho</label>
                <div class="flex gap-2">
                    <input type="text" x-model="novaVagaForm.cep" 
                           placeholder="01310-100" maxlength="9"
                           class="w-full bg-panel border border-border-tron px-3 py-2 text-main text-sm rounded-md focus:border-neon focus:ring-0">
                    <button type="button" @click="consultarVagaCep()" :disabled="carregandoCep"
                            class="px-4 py-2 bg-neon text-white font-medium text-xs rounded-md hover:bg-neon/90 transition flex items-center justify-center shrink-0">
                        <span x-show="!carregandoCep">Buscar</span>
                        <span x-show="carregandoCep">...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Card de Resultado da Pesquisa -->
        <template x-if="vagaResultado">
            <div class="space-y-4">
                
                <!-- Ajuste CBO -->
                <div class="p-4 rounded-md border text-sm"
                     :class="vagaResultado.foi_corrigido ? 'bg-amber-500/10 border-amber-500/30 text-amber-200' : 'bg-emerald-500/10 border-emerald-500/30 text-emerald-200'">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs uppercase font-bold tracking-wider">Cargo Oficial (CBO):</span>
                            <div class="text-base font-semibold text-main" x-text="vagaResultado.cargo_real"></div>
                        </div>
                        <div class="text-right">
                            <span class="px-2.5 py-1 text-xs font-mono font-bold rounded bg-panel border border-border-tron text-main" x-text="vagaResultado.cbo"></span>
                        </div>
                    </div>
                    <div x-show="vagaResultado.foi_corrigido" class="mt-2 text-xs text-amber-400 font-medium" x-text="vagaResultado.aviso_ajuste"></div>
                </div>

                <!-- Faixa Salarial de Mercado -->
                <div class="bg-panel border border-border-tron rounded-md p-4">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs font-bold uppercase text-muted tracking-wider">Média Salarial na Região</span>
                        <span class="text-xs text-neon font-medium" x-text="vagaResultado.localidade.cidade + ' - ' + vagaResultado.localidade.uf"></span>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="p-2 bg-surface rounded border border-border-tron">
                            <div class="text-[10px] text-muted uppercase">Piso Regional</div>
                            <div class="text-sm font-bold text-main" x-text="formatMoney(vagaResultado.faixa_salarial.piso)"></div>
                        </div>
                        <div class="p-2 bg-surface rounded border border-neon/40 shadow-[0_0_8px_rgba(255,85,0,0.15)]">
                            <div class="text-[10px] text-neon uppercase font-bold">Mediana Mercado</div>
                            <div class="text-sm font-bold text-neon" x-text="formatMoney(vagaResultado.faixa_salarial.mediana)"></div>
                        </div>
                        <div class="p-2 bg-surface rounded border border-border-tron">
                            <div class="text-[10px] text-muted uppercase">Teto Salarial</div>
                            <div class="text-sm font-bold text-main" x-text="formatMoney(vagaResultado.faixa_salarial.teto)"></div>
                        </div>
                    </div>
                </div>

                <!-- Descrição Sugerida -->
                <div class="bg-panel border border-border-tron rounded-md p-4">
                    <label class="block text-xs font-bold text-muted uppercase tracking-wider mb-2">Descrição e Escopo do Cargo</label>
                    <textarea x-model="vagaResultado.descricao_cargo" rows="3"
                              class="w-full bg-surface border border-border-tron rounded p-2.5 text-xs text-main focus:border-neon focus:ring-0"></textarea>
                </div>

                <!-- Aviso Legal Obrigatório -->
                <div class="p-3.5 bg-red-500/10 border border-red-500/30 rounded-md text-xs text-red-300 leading-relaxed">
                    <div class="font-bold uppercase tracking-wider mb-1 text-red-400">Aviso Legal & Diretriz de Compliance:</div>
                    <p class="italic" x-text="vagaResultado.aviso_legal"></p>
                </div>

                <!-- Ações de Exportação -->
                <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-3 border-t border-border-tron">
                    <div class="text-[11px] text-muted">
                        Exporta documento Word editável com logotipo e cores da sua empresa.
                    </div>
                    <div class="flex gap-2">
                        <form action="{{ route('plano-cargos.exportar-word') }}" method="POST" target="_blank">
                            @csrf
                            <input type="hidden" name="cargo" :value="novaVagaForm.cargo">
                            <input type="hidden" name="cep" :value="novaVagaForm.cep">
                            <button type="submit" 
                                    class="px-4 py-2 bg-panel border border-blue-500 text-blue-400 hover:bg-blue-500 hover:text-white transition rounded-md text-xs font-medium flex items-center gap-1.5 shadow-[0_0_8px_rgba(59,130,246,0.2)]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span>Exportar Descrição (.docx)</span>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </template>

    </div>
</div>
