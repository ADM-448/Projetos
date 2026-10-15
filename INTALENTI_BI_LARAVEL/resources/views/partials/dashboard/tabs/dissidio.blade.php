<!-- Aba: Dissídio -->
<div x-show="currentTab === 'dissidio'" style="display: none;">
    <div class="mb-12 flex flex-col md:flex-row justify-between md:items-end gap-4">
        <h2 class="text-3xl font-normal font-display tracking-tight text-main">Projeção de Dissídio Coletivo</h2>
        <input x-model="searchDissidio" type="text" placeholder="Buscar na tabela..."
            class="w-full sm:w-64 rounded-none border-border-tron bg-canvas text-main text-sm py-2 px-4 shadow-none focus:ring-neon focus:border-neon transition-colors">
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="bg-surface border border-border-tron rounded-cards p-6 md:p-8 flex flex-col justify-center items-center text-center">
            <label class="block text-xs font-medium text-muted uppercase mb-4">Simular Reajuste (%)</label>
            <input x-model.number="porcentagemDissidio" type="number" step="0.1"
                class="w-full text-3xl font-display font-normal text-main bg-canvas rounded-md border-border-tron focus:ring-neon focus:border-neon text-center py-4 shadow-none transition-colors"
                placeholder="0.0">
        </div>
        <div class="bg-surface border border-border-tron rounded-cards p-6 md:p-8 w-full overflow-hidden flex flex-col justify-center items-center text-center">
            <div class="text-xs font-medium text-muted uppercase mb-4">Custo Adicional Mensal</div>
            <div class="text-4xl font-display font-normal text-main truncate"
                x-text="'+ ' + formatMoney(dissidioGlobalMensal)"></div>
        </div>
        <div class="bg-surface border border-border-tron rounded-cards p-6 md:p-8 w-full overflow-hidden flex flex-col justify-center items-center text-center">
            <div class="text-xs font-medium text-muted uppercase mb-4">Custo Adicional Anual</div>
            <div class="text-4xl font-display font-normal text-neon truncate"
                x-text="'+ ' + formatMoney(dissidioGlobalMensal * 12)"></div>
        </div>
    </div>

    <div class="bg-surface border border-border-tron rounded-cards shadow-none overflow-hidden w-full max-w-full">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left min-w-[800px]">
                <thead class="bg-transparent border-b border-divider cursor-pointer">
                    <tr>
                        <th @click="sortBy('nome')" class="px-6 py-5 text-[10px] font-medium text-muted uppercase hover:bg-panel transition">Colaborador ↕</th>
                        <th @click="sortBy('cargo.nome')" class="px-6 py-5 text-[10px] font-medium text-muted uppercase hover:bg-panel transition">Cargo ↕</th>
                        <th @click="sortBy('salario_atual')" class="px-6 py-5 text-[10px] font-medium text-muted uppercase text-right hover:bg-panel transition">Salário Atual ↕</th>
                        <th @click="sortBy('aumento_valor')" class="px-6 py-5 text-[10px] font-medium text-muted uppercase text-right hover:bg-panel transition">Aumento ↕</th>
                        <th @click="sortBy('novo_salario')" class="px-6 py-5 text-[10px] font-medium text-muted uppercase text-right hover:bg-panel transition">Salário Projetado ↕</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-divider">
                    <template x-for="f in arrayProjetadoDissidio" :key="f.id">
                        <tr class="hover:bg-panel transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-main break-words whitespace-normal" x-text="f.nome"></td>
                            <td class="px-6 py-4 text-xs text-muted break-words whitespace-normal" x-text="f.cargo ? f.cargo.nome : '-'"></td>
                            <td class="px-6 py-4 text-sm text-muted text-right font-sans" x-text="formatMoney(f.salario_atual)"></td>
                            <td class="px-6 py-4 text-sm font-medium text-neon text-right font-sans" x-text="'+ ' + formatMoney(f.aumento_valor)"></td>
                            <td class="px-6 py-4 text-sm font-medium text-main text-right font-sans" x-text="formatMoney(f.novo_salario)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
