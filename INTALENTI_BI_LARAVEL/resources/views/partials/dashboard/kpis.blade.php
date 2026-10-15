<!-- KPI Cards Superiores -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="bg-surface border border-border-tron rounded-cards p-6 shadow-none flex flex-col justify-between relative overflow-hidden">
        <div class="absolute top-4 right-4 bg-panel text-neon text-[10px] font-medium px-2 py-1 rounded-sm border border-neon/30">
            ↗ 100%</div>
        <div class="text-sm text-muted mb-4 font-display">Headcount Total</div>
        <div>
            <div class="text-4xl font-normal text-main tracking-tight font-display" x-text="globalHeadcount">
            </div>
            <div class="text-xs text-muted mt-2">Colaboradores ativos</div>
        </div>
    </div>
    <div class="bg-surface border border-border-tron rounded-cards p-6 shadow-none flex flex-col justify-between relative overflow-hidden">
        <div class="absolute top-4 right-4 border border-border-tron text-muted text-[10px] font-medium px-2 py-1 rounded-sm">
            Atual</div>
        <div class="text-sm text-muted mb-4 font-display">Folha Mensal</div>
        <div class="w-full overflow-hidden">
            <div class="font-normal text-main tracking-tight truncate font-display transition-all"
                :class="String(formatMoney(globalFolhaMensal)).length > 11 ? 'text-2xl lg:text-2xl xl:text-3xl' : 'text-3xl lg:text-4xl'"
                x-text="formatMoney(globalFolhaMensal)"></div>
            <div class="text-xs text-muted mt-2">Custo base atual</div>
        </div>
    </div>
    <div class="bg-surface border border-border-tron rounded-cards p-6 shadow-none flex flex-col justify-between relative overflow-hidden">
        <div class="absolute top-4 right-4 bg-neon text-black text-[10px] font-medium px-2 py-1 rounded-sm shadow-[0_0_8px_rgba(255,85,0,0.5)]">
            + Ajustes</div>
        <div class="text-sm text-muted mb-4 font-display">Folha Projetada</div>
        <div class="w-full overflow-hidden">
            <div class="font-normal text-neon tracking-tight truncate font-display transition-all"
                :class="String(formatMoney(globalFolhaProjetada)).length > 11 ? 'text-2xl lg:text-2xl xl:text-3xl' : 'text-3xl lg:text-4xl'"
                x-text="formatMoney(globalFolhaProjetada)"></div>
            <div class="text-xs text-muted mt-2">Impacto mensal projetado</div>
        </div>
    </div>
    <div class="bg-surface border border-border-tron rounded-cards p-6 shadow-none flex flex-col justify-between relative overflow-hidden">
        <div class="text-sm text-muted mb-4 font-display">Impacto Anual</div>
        <div class="w-full overflow-hidden">
            <div class="font-normal text-neon tracking-tight truncate font-display transition-all"
                :class="String(formatMoney(globalImpactoAnualBruto)).length > 11 ? 'text-2xl lg:text-2xl xl:text-3xl' : 'text-3xl lg:text-4xl'"
                x-text="formatMoney(globalImpactoAnualBruto)"></div>
            <div class="text-xs text-muted mt-2">Diferença anual bruta</div>
        </div>
    </div>
</div>
