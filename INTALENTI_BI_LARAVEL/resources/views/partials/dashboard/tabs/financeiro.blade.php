<!-- Aba: Financeiro (Visão CFO) -->
<div x-show="currentTab === 'financeiro'" style="display: none;">
    <div class="mb-12">
        <h2 class="text-3xl font-normal font-display tracking-tight text-main">Visão Financeira e Risco de Evasão</h2>
        <p class="text-muted text-sm mt-2 max-w-2xl">Análise de risco de perda de talentos por defasagem e dispersão orçamentária.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
        <div class="bg-surface border border-border-tron rounded-cards p-6 md:p-8 flex flex-col justify-center items-center text-center">
            <div>
                <div class="text-[10px] font-medium text-muted uppercase mb-2">Risco de Turnover Alto</div>
                <div class="text-5xl font-normal font-display tracking-tight text-main" x-text="riscoEvasao.criticos.length"></div>
                <div class="text-sm text-muted mt-4">Colaboradores com defasagem &gt; 10% do mercado</div>
            </div>
            <div class="mt-8 text-xs font-medium text-main">
                Folha em Risco: <span class="font-display font-normal text-base ml-1" x-text="formatMoney(riscoEvasao.custoTotalRiscoMensal)"></span>
            </div>
        </div>

        <div class="bg-panel border border-neon/30 shadow-[0_0_10px_rgba(255,85,0,0.1)] rounded-cards p-6 md:p-8 flex flex-col justify-center items-center text-center">
            <div>
                <div class="text-[10px] font-medium text-muted uppercase mb-2">Custo de Substituição (Anual)</div>
                <div class="text-4xl font-normal font-display tracking-tight text-neon" x-text="formatMoney(riscoEvasao.impactoSubstituicaoAnual)"></div>
                <div class="text-sm text-muted mt-4">Custo extra ao perder talentos e repor a preço de mercado</div>
            </div>
        </div>

        <div class="bg-surface border border-border-tron rounded-cards p-6 md:p-8">
            <h3 class="text-[10px] font-medium text-muted uppercase mb-6 text-center">Dispersão por Senioridade</h3>
            <div class="relative w-full h-[180px]">
                <canvas id="chartFinanceiro"></canvas>
            </div>
        </div>
    </div>

    <div class="bg-surface border border-border-tron rounded-cards shadow-none overflow-hidden w-full max-w-full">
        <div class="p-6 bg-transparent border-b border-divider">
            <span class="font-normal font-display text-main text-lg">Colaboradores em Zona Crítica</span>
        </div>
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left min-w-[800px]">
                <thead class="bg-transparent border-b border-divider cursor-pointer">
                    <tr>
                        <th class="px-6 py-5 text-[10px] font-medium text-muted uppercase">Colaborador</th>
                        <th class="px-6 py-5 text-[10px] font-medium text-muted uppercase">Função</th>
                        <th class="px-6 py-5 text-[10px] font-medium text-muted uppercase text-right">Salário Atual</th>
                        <th class="px-6 py-5 text-[10px] font-medium text-main uppercase text-right">Ideal (Mercado)</th>
                        <th class="px-6 py-5 text-[10px] font-medium text-neon uppercase text-right">Defasagem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-divider">
                    <template x-for="f in riscoEvasao.criticos" :key="f.id">
                        <tr class="hover:bg-panel transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-main" x-text="f.nome"></td>
                            <td class="px-6 py-4 text-xs text-muted" x-text="f.cargo ? f.cargo.nome : '-'"></td>
                            <td class="px-6 py-4 text-sm text-muted text-right font-sans" x-text="formatMoney(f.salario_atual)"></td>
                            <td class="px-6 py-4 text-sm text-main font-medium text-right font-sans" x-text="formatMoney(f.tabelaIdeal)"></td>
                            <td class="px-6 py-4 text-sm font-medium text-neon text-right font-sans">
                                <span class="block" x-text="'- ' + formatMoney(f.defasagemMensal)"></span>
                                <span class="text-[10px] text-neon/80 mt-1 inline-block" x-text="f.defasagemPercentual + '% abaixo'"></span>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="riscoEvasao.criticos.length === 0">
                        <td colspan="5" class="px-6 py-16 text-center text-muted text-sm font-sans">Nenhum risco crítico de defasagem detectado.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
