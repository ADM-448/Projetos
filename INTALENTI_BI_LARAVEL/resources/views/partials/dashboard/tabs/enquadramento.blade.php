<!-- Aba: Enquadramento e Elegibilidade -->
<div x-show="currentTab === 'enquadramento' || currentTab === 'elegibilidade'" style="display: none;">

    <div class="mb-12 flex flex-col md:flex-row justify-between md:items-end gap-4">
        <div>
            <h2 class="text-3xl font-normal text-main font-display tracking-tight break-words"
                x-text="currentTab === 'enquadramento' ? 'Enquadramento de Colaboradores' : 'Avaliação de Desempenho e Mérito'">
            </h2>
        </div>
        <div class="flex gap-4 items-center">
            <select x-show="currentTab === 'enquadramento'" x-model="filtroCargoEnquadramento"
                class="rounded-cards border border-border-tron bg-surface text-main text-sm py-2 px-4 shadow-none focus:ring-neon focus:border-neon transition-colors w-48">
                <option value="">Todos os Cargos</option>
                <template x-for="cargo in cargos" :key="cargo.id">
                    <option :value="cargo.nome" x-text="cargo.nome"></option>
                </template>
            </select>
            <input x-model="searchElegibilidade" type="text" placeholder="Buscar colaborador..."
                class="w-full sm:w-64 rounded-cards border border-border-tron bg-surface text-main text-sm py-2 px-4 shadow-none focus:ring-neon focus:border-neon transition-colors">
        </div>
    </div>

    <!-- Cards de Resumo da Faixa Salarial -->
    <div x-show="currentTab === 'enquadramento' && filtroCargoEnquadramento && resumoMatrizCargo"
        class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-10">
        <div class="bg-surface rounded-cards p-6 border border-border-tron text-center">
            <div class="text-[10px] uppercase font-medium text-muted mb-2">Piso (Júnior N1)</div>
            <div class="font-normal font-display text-main" x-text="formatMoney(resumoMatrizCargo.piso)"></div>
        </div>
        <div class="bg-surface rounded-cards p-6 border border-border-tron text-center">
            <div class="text-[10px] uppercase font-medium text-muted mb-2">Mediana (Pleno N5)</div>
            <div class="font-normal font-display text-main" x-text="formatMoney(resumoMatrizCargo.mediana)"></div>
        </div>
        <div class="bg-surface rounded-cards p-6 border border-border-tron text-center shadow-[0_0_8px_rgba(255,255,255,0.05)]">
            <div class="text-[10px] uppercase font-medium text-muted mb-2">Teto (Sênior N12)</div>
            <div class="font-normal font-display text-neon" x-text="formatMoney(resumoMatrizCargo.teto)"></div>
        </div>
        <div class="bg-surface rounded-cards p-6 border border-border-tron text-center">
            <div class="text-[10px] uppercase font-medium text-muted mb-2">Evolução Degrau</div>
            <div class="font-normal font-display text-main" x-text="resumoMatrizCargo.evolucao + '%'"></div>
        </div>
        <div class="bg-surface rounded-cards p-6 border border-border-tron text-center">
            <div class="text-[10px] uppercase font-medium text-muted mb-2">Amplitude Total</div>
            <div class="font-normal font-display text-main" x-text="resumoMatrizCargo.amplitude + '%'"></div>
        </div>
    </div>

    <div class="bg-surface border border-border-tron rounded-cards shadow-none overflow-hidden w-full max-w-full">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left min-w-[1000px]">
                <thead class="bg-transparent border-b border-divider cursor-pointer">
                    <tr>
                        <th @click="sortBy('nome')" class="px-6 py-5 text-[10px] font-medium text-muted uppercase hover:bg-panel transition">Colaborador ↕</th>
                        <th @click="sortBy('cargo.nome')" class="px-6 py-5 text-[10px] font-medium text-muted uppercase hover:bg-panel transition">Função ↕</th>
                        <th @click="sortBy('tempo_de_casa_meses')" class="px-6 py-5 text-[10px] font-medium text-muted uppercase text-center hover:bg-panel transition">T. Casa ↕</th>
                        <th @click="sortBy('salario_atual')" class="px-6 py-5 text-[10px] font-medium text-muted uppercase text-right hover:bg-panel transition">Salário Atual ↕</th>

                        <template x-if="currentTab === 'enquadramento'">
                            <>
                                <th @click="sortBy('tabelaIdeal')" class="px-6 py-5 text-[10px] font-medium text-muted uppercase text-right hover:bg-panel transition">Roadmap (Ideal) ↕</th>
                                <th @click="sortBy('senioridadeSugerida')" class="px-6 py-5 text-[10px] font-medium text-muted uppercase text-center hover:bg-panel transition">Senioridade ↕</th>
                                <th @click="sortBy('status')" class="px-6 py-5 text-[10px] font-medium text-muted uppercase text-center hover:bg-panel transition">Status ↕</th>
                            </>
                        </template>

                        <template x-if="currentTab === 'elegibilidade'">
                            <>
                                <th @click="sortBy('status')" class="px-6 py-5 text-[10px] font-medium text-muted uppercase text-center hover:bg-panel transition">Status ↕</th>
                                <th @click="sortBy('senioridadeSugerida')" class="px-6 py-5 text-[10px] font-medium text-muted uppercase hover:bg-panel transition">Senioridade Sugerida ↕</th>
                                <th @click="sortBy('recomendacao')" class="px-6 py-5 text-[10px] font-medium text-muted uppercase hover:bg-panel transition">Recomendação Sistema ↕</th>
                            </>
                        </template>
                    </tr>
                </thead>
                <tbody class="divide-y divide-divider">
                    <template x-for="f in arrayFiltradoEnquadramento" :key="f.id">
                        <tr class="hover:bg-panel transition-colors cursor-pointer" @click="openModal(f)">
                            <td class="px-6 py-4 text-sm font-medium text-main break-words whitespace-normal max-w-[200px]" x-text="f.nome"></td>
                            <td class="px-6 py-4 text-xs text-muted break-words whitespace-normal max-w-[150px]" x-text="f.cargo ? f.cargo.nome : '-'"></td>
                            <td class="px-6 py-4 text-xs text-muted text-center" x-text="formatTempoCasa(f.tempo_de_casa_meses)"></td>
                            <td class="px-6 py-4 text-sm font-medium text-main text-right font-sans" x-text="formatMoney(f.salario_atual)"></td>

                            <template x-if="currentTab === 'enquadramento'">
                                <>
                                    <td class="px-6 py-4 text-sm font-medium text-main text-right font-sans" x-text="formatMoney(f.tabelaIdeal)"></td>
                                    <td class="px-6 py-4 text-xs text-main text-center font-medium" x-text="f.senioridadeSugerida"></td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 text-[10px] uppercase whitespace-nowrap"
                                            :class="f.ventrilocStatusClass" x-text="f.status"></span>
                                    </td>
                                </>
                            </template>

                            <template x-if="currentTab === 'elegibilidade'">
                                <>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 text-[10px] uppercase whitespace-nowrap"
                                            :class="f.ventrilocStatusClass" x-text="f.status"></span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-main break-words whitespace-normal max-w-[200px]">
                                        <div class="font-medium flex items-center gap-2">
                                            <span x-text="f.senioridadeSugerida"></span>
                                            <span class="text-[10px] px-2 py-0.5 bg-panel border border-border-tron text-muted rounded-sm" x-text="'Nível ' + f.nivel_calculado"></span>
                                        </div>
                                        <div class="text-[10px] text-muted mt-1" x-text="'Esperado: ' + f.tempoMedioEsperado"></div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-main break-words whitespace-normal max-w-[250px]">
                                        <span class="font-medium mb-1 inline-block whitespace-nowrap"
                                            :class="f.ventrilocRecClass" x-text="f.recomendacao"></span>
                                        <div class="text-[11px] text-muted leading-tight mt-1" x-text="f.motivoStatus"></div>
                                    </td>
                                </>
                            </template>
                        </tr>
                    </template>

                    <tr x-show="arrayFiltradoEnquadramento.length === 0">
                        <td colspan="8" class="px-6 py-16 text-center text-muted text-sm font-sans">Nenhum colaborador encontrado para os filtros.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
