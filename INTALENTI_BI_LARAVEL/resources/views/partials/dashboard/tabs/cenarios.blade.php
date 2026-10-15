<!-- Aba: Cenários (Gráficos) -->
<div x-show="currentTab === 'cenarios'" style="display: none;">
    <div class="mb-12">
        <h2 class="text-3xl font-normal text-main font-display tracking-tight">Simulador de Cenários</h2>
        <p class="text-muted text-sm mt-2 max-w-2xl">Analise salários por cargo e simule novas contratações ou ajustes orçamentários.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="bg-surface border border-border-tron rounded-cards p-6 md:p-8 flex flex-col justify-start">
            <h3 class="font-normal font-display text-lg text-main mb-6 border-b border-border-tron pb-3 text-center sm:text-left">Controles da Simulação</h3>
            <div class="space-y-6">
                <div>
                    <label class="block text-xs uppercase font-medium text-muted mb-2 text-center sm:text-left">Selecionar Cargo</label>
                    <select x-model="selectedCargoId" @change="autoFillNovoSalario()"
                        class="w-full rounded-md border-border-tron bg-surface text-main text-sm py-3 shadow-none focus:ring-neon focus:border-neon transition-colors">
                        <option value="">Selecione um cargo</option>
                        <template x-for="cargo in cargos" :key="cargo.id">
                            <option :value="cargo.id" x-text="cargo.nome"></option>
                        </template>
                    </select>
                </div>
                <div x-show="selectedCargoId">
                    <label class="block text-xs uppercase font-medium text-muted mb-2 text-center sm:text-left">Filtrar por Nível</label>
                    <select x-model.number="filtroNivelSimulador" @change="autoFillNovoSalario()"
                        class="w-full rounded-md border-border-tron bg-surface text-main text-sm py-3 shadow-none focus:ring-neon focus:border-neon transition-colors">
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
                <div class="bg-surface rounded-md p-4 border border-border-tron flex flex-col sm:flex-row justify-between items-center text-sm gap-4">
                    <div class="text-muted text-center sm:text-left">
                        <div class="mb-2">Headcount:</div>
                        <div>Salário Médio:</div>
                    </div>
                    <div class="font-medium text-main text-center sm:text-right">
                        <div class="mb-2 text-lg font-display" x-text="totalFuncionariosCargo"></div>
                        <div x-text="formatMoney(mediaSalarialAtual)"></div>
                    </div>
                </div>
                <div>
                    <label class="block text-xs uppercase font-medium text-muted mb-2 text-center sm:text-left">Novo Salário Proposto (R$)</label>
                    <input x-model.number="novoSalario" type="number"
                        class="w-full rounded-md border-border-tron bg-surface text-main text-sm py-3 shadow-none focus:ring-neon focus:border-neon transition-colors text-center sm:text-left">
                </div>
                <div>
                    <label class="block text-xs uppercase font-medium text-muted mb-2 text-center sm:text-left">Novas Vagas a Contratar</label>
                    <input x-model.number="novasVagas" type="number"
                        class="w-full rounded-md border-border-tron bg-surface text-main text-sm py-3 shadow-none focus:ring-neon focus:border-neon transition-colors text-center sm:text-left"
                        placeholder="0">
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 flex flex-col gap-8">
            <div class="bg-surface border border-border-tron rounded-cards p-6 md:p-8 flex flex-col justify-start">
                <h3 class="font-normal font-display text-lg text-main mb-6 border-b border-border-tron pb-3 text-center sm:text-left">Projeção Orçamentária (Cargo)</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                    <div class="bg-surface rounded-md border border-border-tron p-6 text-main flex flex-col justify-center items-center sm:items-start text-center sm:text-left shadow-[0_0_8px_rgba(255,255,255,0.02)]">
                        <div class="text-[10px] uppercase font-medium text-muted mb-2">Impacto Adicional Mensal</div>
                        <div class="text-2xl sm:text-3xl font-normal font-display break-words" x-text="formatMoney(impactoMensal)"></div>
                    </div>
                    <div class="bg-panel rounded-md p-6 text-main border border-neon/30 shadow-[0_0_10px_rgba(255,85,0,0.1)] flex flex-col justify-center items-center sm:items-start text-center sm:text-left">
                        <div class="text-[10px] uppercase font-medium text-muted mb-2">Impacto Adicional Anual</div>
                        <div class="text-2xl sm:text-3xl font-normal font-display break-words text-neon" x-text="formatMoney(impactoAnual)"></div>
                    </div>
                </div>
                <div class="relative w-full flex-1 min-h-[300px]">
                    <canvas id="chartCenarios"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
