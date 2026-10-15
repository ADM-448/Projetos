<!-- Aba: Convenções Coletivas (CCT) -->
<div x-show="currentTab === 'convencoes'" style="display: none;" class="w-full" x-cloak x-data="{
    subTab: 'cruzamento',
    editandoConvencao: null,
    novaConvencao: {
        id: null,
        sindicato_nome: '',
        ano_vigencia: '',
        data_base: '',
        reajuste_geral_percentual: 0,
        regras: [
            { tipo_regra: 'piso_cargo', descricao: '', valor: 0 }
        ]
    },
    addRegra() {
        this.novaConvencao.regras.push({ tipo_regra: 'piso_cargo', descricao: '', valor: 0 });
    },
    removeRegra(index) {
        this.novaConvencao.regras.splice(index, 1);
    },
    editarConvencao(convencao) {
        this.editandoConvencao = convencao;
        this.novaConvencao = {
            id: convencao.id,
            sindicato_nome: convencao.sindicato_nome,
            ano_vigencia: convencao.ano_vigencia,
            data_base: convencao.data_base,
            reajuste_geral_percentual: parseFloat(convencao.reajuste_geral_percentual) || 0,
            regras: convencao.regras && convencao.regras.length > 0 
                ? JSON.parse(JSON.stringify(convencao.regras))
                : [{ tipo_regra: 'piso_cargo', descricao: '', valor: 0 }]
        };
        this.subTab = 'cadastro';
    },
    limparFormulario() {
        this.editandoConvencao = null;
        this.novaConvencao = {
            id: null,
            sindicato_nome: '',
            ano_vigencia: '',
            data_base: '',
            reajuste_geral_percentual: 0,
            regras: [
                { tipo_regra: 'piso_cargo', descricao: '', valor: 0 }
            ]
        };
    },
    get formAction() {
        if (this.editandoConvencao) {
            return '/convencoes/' + this.editandoConvencao.id;
        }
        return '{{ route("convencoes.store") }}';
    },
    get formMethod() {
        return this.editandoConvencao ? 'PUT' : 'POST';
    }
}">

    <!-- Sub Header Tabs -->
    <div class="flex gap-2 sm:gap-6 border-b border-divider pb-3 mb-8 overflow-x-auto hide-scrollbar w-full">
        <button @click="subTab = 'cruzamento'; limparFormulario()"
            :class="subTab === 'cruzamento' ? 'text-neon font-medium border-b-2 border-neon' : 'text-muted hover:text-main'"
            class="px-2 pb-2 text-sm sm:text-base whitespace-nowrap transition-colors">
            Motor de Cruzamento
        </button>
        <button @click="subTab = 'cadastro'"
            :class="subTab === 'cadastro' ? 'text-neon font-medium border-b-2 border-neon' : 'text-muted hover:text-main'"
            class="px-2 pb-2 text-sm sm:text-base whitespace-nowrap transition-colors">
            <span x-text="editandoConvencao ? 'Editar Convenção' : 'Cadastrar Convenção'"></span>
        </button>
        <button @click="subTab = 'comparativo'; limparFormulario()"
            :class="subTab === 'comparativo' ? 'text-neon font-medium border-b-2 border-neon' : 'text-muted hover:text-main'"
            class="px-2 pb-2 text-sm sm:text-base whitespace-nowrap transition-colors">
            Comparativo Histórico
        </button>
    </div>

    <!-- Seção A: Motor de Cruzamento -->
    <div x-show="subTab === 'cruzamento'" class="space-y-8 w-full p-1" x-transition.opacity.duration.300ms>
        <div>
            <h2 class="text-3xl font-normal text-main font-display tracking-tight">Motor de Cruzamento CCT</h2>
            <p class="text-muted text-sm mt-2 max-w-2xl">Cruzamento automático entre a convenção coletiva ativa e os dados da folha de pagamento carregada.</p>
        </div>

        <!-- Card da Convenção Ativa -->
        <template x-if="convencaoAtiva">
            <div class="bg-surface border border-neon/30 rounded-cards p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="text-[10px] uppercase font-semibold tracking-wider text-muted mb-1">Convenção Ativa</div>
                    <div class="text-lg font-display text-main" x-text="convencaoAtiva.sindicato_nome"></div>
                    <div class="flex items-center gap-3 mt-1 text-xs text-muted">
                        <span class="bg-canvas border border-border-tron px-2 py-0.5 rounded" x-text="convencaoAtiva.ano_vigencia"></span>
                        <span x-text="'Data-base: ' + convencaoAtiva.data_base"></span>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-[10px] uppercase font-medium text-muted">Reajuste Geral</div>
                    <div class="text-3xl font-display text-neon" x-text="convencaoAtiva.reajuste_geral_percentual + '%'"></div>
                </div>
            </div>
        </template>

        <!-- Aviso sem convenção -->
        <template x-if="!convencaoAtiva">
            <div class="bg-surface border border-border-tron border-dashed rounded-cards p-12 flex flex-col items-center justify-center text-center">
                <svg class="w-12 h-12 text-muted/40 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <div class="text-base font-medium text-main mb-1">Nenhuma convenção cadastrada</div>
                <p class="text-xs text-muted mb-4 max-w-sm">Cadastre a primeira convenção coletiva para habilitar o motor de cruzamento salarial.</p>
                <button @click="subTab = 'cadastro'" class="inline-flex items-center justify-center gap-2 text-xs bg-neon text-black font-semibold px-4 py-2 rounded-md hover:opacity-90 transition">
                    Cadastrar Convenção
                </button>
            </div>
        </template>

        <!-- Tabela de cruzamento -->
        <template x-if="convencaoAtiva && funcionariosRaw && funcionariosRaw.length > 0">
            <div class="space-y-4">

                <!-- Label da seção -->
                <div class="flex items-center gap-3">
                    <span class="text-[10px] uppercase font-semibold tracking-wider text-muted">Resultado do Cruzamento</span>
                    <div class="flex-1 h-px bg-divider"></div>
                    <span class="text-[10px] text-muted" x-text="funcionariosRaw.length + ' colaborador(es)'"></span>
                </div>

                <!-- Container da tabela com borda -->
                <div class="bg-surface border border-border-tron rounded-cards overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-canvas border-b border-divider">
                                <tr>
                                    <th class="py-3 px-5 text-left text-[10px] uppercase font-semibold tracking-wider text-muted">Colaborador</th>
                                    <th class="py-3 px-5 text-left text-[10px] uppercase font-semibold tracking-wider text-muted">Cargo</th>
                                    <th class="py-3 px-5 text-right text-[10px] uppercase font-semibold tracking-wider text-muted">Salário Atual</th>
                                    <th class="py-3 px-5 text-right text-[10px] uppercase font-semibold tracking-wider text-muted">Piso / Projeção CCT</th>
                                    <th class="py-3 px-5 text-center text-[10px] uppercase font-semibold tracking-wider text-muted">Situação</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-divider">
                                <template x-for="func in funcionariosRaw" :key="func.id ?? func.nome">
                                    <tr class="hover:bg-canvas/50 transition-colors">
                                        <td class="py-4 px-5 font-medium text-main" x-text="func.nome"></td>
                                        <td class="py-4 px-5 text-muted text-xs" x-text="typeof func.cargo === 'object' ? (func.cargo?.nome ?? '-') : (func.cargo ?? '-')"></td>
                                        <td class="py-4 px-5 text-right font-sans text-main" x-text="formatMoney(func.salario_atual)"></td>
                                        <td class="py-4 px-5 text-right font-sans">
                                            <span x-show="func.valor_piso_cct !== null && func.valor_piso_cct !== undefined"
                                                :class="func.abaixo_piso_cct ? 'text-red-400' : 'text-neon'"
                                                x-text="func.tipo_regra_cct === 'adicional_percentual'
                                                    ? formatMoney(func.valor_piso_cct) + ' (adic.)'
                                                    : func.tipo_regra_cct === 'reajuste_geral'
                                                        ? formatMoney(func.valor_piso_cct) + ' (proj.)'
                                                        : formatMoney(func.valor_piso_cct)"></span>
                                            <span x-show="func.valor_piso_cct === null || func.valor_piso_cct === undefined"
                                                class="text-muted/50 italic text-xs">—</span>
                                        </td>
                                        <td class="py-4 px-5 text-center">
                                            <span x-show="func.abaixo_piso_cct"
                                                class="inline-block text-[10px] font-semibold uppercase tracking-wider bg-red-500/15 text-red-400 border border-red-500/30 px-2.5 py-1 rounded-full">
                                                Abaixo do Piso
                                            </span>
                                            <span x-show="!func.abaixo_piso_cct && func.tipo_regra_cct === 'reajuste_geral'"
                                                class="inline-block text-[10px] font-semibold uppercase tracking-wider bg-blue-500/10 text-blue-400 border border-blue-500/30 px-2.5 py-1 rounded-full">
                                                Reajuste Geral
                                            </span>
                                            <span x-show="!func.abaixo_piso_cct && func.tipo_regra_cct !== 'reajuste_geral' && func.valor_piso_cct !== null && func.valor_piso_cct !== undefined"
                                                class="inline-block text-[10px] font-semibold uppercase tracking-wider bg-neon/10 text-neon border border-neon/30 px-2.5 py-1 rounded-full">
                                                Conforme
                                            </span>
                                            <span x-show="func.valor_piso_cct === null || func.valor_piso_cct === undefined"
                                                class="inline-block text-[10px] font-semibold uppercase tracking-wider bg-canvas text-muted border border-border-tron px-2.5 py-1 rounded-full">
                                                Sem Mapeamento
                                            </span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </template>

        <!-- Aviso sem funcionários -->
        <template x-if="convencaoAtiva && (!funcionariosRaw || funcionariosRaw.length === 0)">
            <div class="bg-surface border border-border-tron border-dashed rounded-cards p-10 flex flex-col items-center justify-center text-center">
                <svg class="w-10 h-10 text-muted/40 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"></path>
                </svg>
                <div class="text-sm font-medium text-main mb-1">Nenhuma folha carregada</div>
                <p class="text-xs text-muted max-w-xs">Importe uma base de funcionários para que o motor realize o cruzamento com a convenção ativa.</p>
            </div>
        </template>
    </div>

    <!-- Seção B: Cadastro/Edição -->
    <div x-show="subTab === 'cadastro'" class="w-full space-y-8" x-transition.opacity.duration.300ms>
        <form :action="formAction" method="POST"
            class="bg-surface border border-border-tron rounded-cards p-8 md:p-10 space-y-8 w-full box-border shadow-lg">
            @csrf
            <template x-if="editandoConvencao">
                @method('PUT')
            </template>

            <!-- Cabeçalho do Form -->
            <div class="border-b border-divider pb-6 space-y-2 gap-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-normal font-display text-main tracking-tight"
                            x-text="editandoConvencao ? 'Editar Convenção Coletiva' : 'Nova Convenção Coletiva'"></h2>
                        <p class="text-sm text-muted max-w-3xl leading-relaxed">Preencha os dados da CCT e adicione as
                            regras vigentes para que o motor de análise realize o cruzamento automático com a folha de
                            pagamento.</p>
                    </div>
                    <template x-if="editandoConvencao">
                        <button type="button" @click="limparFormulario()"
                            class="text-xs text-muted hover:text-main transition-colors">
                            Cancelar edição
                        </button>
                    </template>
                </div>
            </div>
            <br>

            <!-- Dados Principais -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 w-full pt-2">
                <div class="flex flex-col gap-2.5">
                    <label class="text-xs uppercase font-semibold tracking-wider text-muted text-left">Nome do
                        Sindicato</label>
                    <input type="text" name="sindicato_nome" x-model="novaConvencao.sindicato_nome" required
                        class="w-full rounded-md border-border-tron bg-canvas text-main text-sm py-3 px-4 shadow-none focus:ring-neon focus:border-neon transition-colors"
                        placeholder="Ex: SINDIPD">
                </div>
                <div class="flex flex-col gap-2.5">
                    <label class="text-xs uppercase font-semibold tracking-wider text-muted text-left">Ano de
                        Vigência</label>
                    <input type="text" name="ano_vigencia" x-model="novaConvencao.ano_vigencia" required
                        class="w-full rounded-md border-border-tron bg-canvas text-main text-sm py-3 px-4 shadow-none focus:ring-neon focus:border-neon transition-colors"
                        placeholder="Ex: 2024/2025">
                </div>
                <div class="flex flex-col gap-2.5">
                    <label class="text-xs uppercase font-semibold tracking-wider text-muted text-left">Data-Base</label>
                    <input type="date" name="data_base" x-model="novaConvencao.data_base" required
                        class="w-full rounded-md border-border-tron bg-canvas text-main text-sm py-3 px-4 shadow-none focus:ring-neon focus:border-neon transition-colors">
                </div>
                <div class="flex flex-col gap-2.5">
                    <label class="text-xs uppercase font-semibold tracking-wider text-muted text-left">Reajuste Geral
                        (%)</label>
                    <div
                        class="flex items-center w-full rounded-md border border-border-tron bg-canvas focus-within:border-neon focus-within:ring-1 focus-within:ring-neon transition-colors px-4 py-3">
                        <input type="number" step="0.01" name="reajuste_geral_percentual"
                            x-model.number="novaConvencao.reajuste_geral_percentual" required
                            class="w-full bg-transparent border-0 text-main text-sm p-0 text-right font-sans focus:ring-0 no-spinner"
                            placeholder="0.00">
                        <span class="ml-2 text-xs font-semibold text-muted select-none">%</span>
                    </div>
                </div>
            </div>

            <div class="border-t border-divider my-6"></div>

            <!-- Regras Específicas Dinâmicas -->
            <div class="space-y-6 w-full">
                <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 w-full">
                    <div class="text-left max-w-xl space-y-1">
                        <h3 class="text-lg font-normal font-display text-main">Regras Específicas da CCT</h3>
                        <p class="text-xs text-muted leading-relaxed">Defina pisos salariais por cargo para que o
                            sistema aponte colaboradores com remuneração abaixo da convenção.</p>
                    </div>
                    <button type="button" @click="addRegra()"
                        class="inline-flex items-center justify-center gap-2 text-sm bg-transparent hover:bg-neon hover:text-black border border-neon text-neon px-5 py-2.5 rounded-md font-medium transition-colors shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                        <span>Nova Regra</span>
                    </button>
                </div>

                <div class="space-y-4 w-full pt-2">
                    <template x-for="(regra, index) in novaConvencao.regras" :key="index">
                        <div
                            class="bg-canvas border border-border-tron rounded-cards p-5 grid grid-cols-1 md:grid-cols-12 gap-5 items-end w-full shadow-sm">
                            <div class="md:col-span-3 flex flex-col gap-2">
                                <label
                                    class="text-[10px] uppercase font-semibold tracking-wider text-muted text-left">Tipo
                                    da Regra</label>
                                <select :name="`regras[${index}][tipo_regra]`" x-model="regra.tipo_regra"
                                    class="w-full rounded-md border-border-tron bg-surface text-main text-sm py-3 px-3 focus:ring-neon focus:border-neon transition-colors">
                                    <option value="piso_cargo">Piso por Cargo</option>
                                    <option value="beneficio_fixo">Benefício Fixo (R$)</option>
                                    <option value="adicional_percentual">Adicional (%)</option>
                                </select>
                            </div>
                            <div class="md:col-span-5 flex flex-col gap-2">
                                <label
                                    class="text-[10px] uppercase font-semibold tracking-wider text-muted text-left">Cargo
                                    ou Descrição</label>
                                <input type="text" :name="`regras[${index}][descricao]`" x-model="regra.descricao"
                                    required
                                    class="w-full rounded-md border-border-tron bg-surface text-main text-sm py-3 px-3.5 focus:ring-neon focus:border-neon transition-colors"
                                    placeholder="Ex: Analista de Sistemas Pleno">
                            </div>
                            <div class="md:col-span-3 flex flex-col gap-2">
                                <label
                                    class="text-[10px] uppercase font-semibold tracking-wider text-muted text-left"
                                    x-text="regra.tipo_regra === 'adicional_percentual' ? 'Valor (%)' : 'Valor (R$)'"></label>
                                <div class="flex items-center w-full rounded-md border border-border-tron bg-surface focus-within:border-neon focus-within:ring-1 focus-within:ring-neon transition-colors px-3.5 py-3">
                                    <input type="number" step="0.01" :name="`regras[${index}][valor]`"
                                        x-model.number="regra.valor" required
                                        class="w-full bg-transparent border-0 text-main text-sm p-0 text-right font-sans focus:ring-0 no-spinner"
                                        placeholder="0.00">
                                    <span class="ml-2 text-xs font-semibold text-muted select-none shrink-0"
                                        x-text="regra.tipo_regra === 'adicional_percentual' ? '%' : 'R$'"></span>
                                </div>
                            </div>
                            <div class="md:col-span-1 flex items-center justify-end md:justify-center">
                                <button type="button" @click="removeRegra(index)"
                                    class="w-11 h-[46px] flex items-center justify-center text-muted hover:text-red-500 hover:bg-red-500/10 rounded-md border border-border-tron hover:border-red-500/30 transition-colors"
                                    title="Remover esta regra">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="border-t border-divider my-6"></div>

            <!-- Botão Salvar -->
            <div class="flex justify-end pt-3 w-full">
                <button type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-neon text-black font-semibold px-8 py-4 rounded-md hover:opacity-90 active:scale-[0.99] transition shadow-md">
                    <span x-text="editandoConvencao ? 'Atualizar Convenção' : 'Salvar Convenção'"></span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>

    <!-- Seção C: Comparativo -->
    <div x-show="subTab === 'comparativo'" class="space-y-8 w-full" x-transition.opacity.duration.300ms>
        <div>
            <h2 class="text-3xl font-normal text-main font-display tracking-tight">Acervo de Convenções Coletivas</h2>
            <p class="text-muted text-sm mt-2 max-w-2xl">Histórico das convenções cadastradas e seus respectivos
                reajustes e cláusulas.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 w-full">
            <template x-for="cct in convencoes" :key="cct.id">
                <div
                    class="bg-surface border border-border-tron rounded-cards p-6 relative flex flex-col justify-between hover:border-neon transition-colors group">
                    <div class="absolute top-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click="editarConvencao(cct)"
                            class="w-8 h-8 flex items-center justify-center text-muted hover:text-neon hover:bg-neon/10 rounded border border-border-tron transition-colors"
                            title="Editar convenção">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                        </button>
                        <form :action="`/convencoes/${cct.id}`" method="POST"
                            onsubmit="return confirm('ATENÇÃO: Deseja realmente excluir esta convenção e todas as suas regras?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-8 h-8 flex items-center justify-center text-muted hover:text-red-500 hover:bg-red-500/10 rounded border border-border-tron transition-colors"
                                title="Excluir convenção">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                </svg>
                            </button>
                        </form>
                    </div>

                    <div>
                        <h3 class="text-lg font-display text-main pr-10" x-text="cct.sindicato_nome"></h3>
                        <div class="flex items-center gap-3 mt-1 mb-4 text-xs text-muted">
                            <span class="bg-canvas border border-border-tron px-2 py-0.5 rounded"
                                x-text="cct.ano_vigencia"></span>
                            <span x-text="'Data-base: ' + cct.data_base"></span>
                        </div>

                        <div
                            class="bg-canvas border border-border-tron rounded p-4 mb-4 flex justify-between items-center">
                            <div>
                                <div class="text-[10px] uppercase font-medium text-muted">Reajuste Geral</div>
                                <div class="text-2xl font-display text-neon font-normal"
                                    x-text="cct.reajuste_geral_percentual + '%'"></div>
                            </div>
                            <div class="text-right">
                                <div class="text-[10px] uppercase font-medium text-muted">Regras</div>
                                <div class="text-lg font-display text-main" x-text="cct.regras ? cct.regras.length : 0">
                                </div>
                            </div>
                        </div>

                        <div class="text-[10px] uppercase font-medium text-muted mb-2">Cláusulas e Pisos</div>
                        <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                            <template x-for="regra in cct.regras" :key="regra.id">
                                <div
                                    class="flex justify-between items-center bg-canvas border border-border-tron/60 rounded px-3 py-2 text-xs">
                                    <div class="flex flex-col pr-2">
                                        <span class="font-medium text-main" x-text="regra.descricao"></span>
                                        <span class="text-[10px] text-muted capitalize"
                                            x-text="regra.tipo_regra.replace('_', ' ')"></span>
                                    </div>
                                    <span class="font-sans font-medium text-neon shrink-0"
                                        x-text="regra.tipo_regra === 'adicional_percentual' ? regra.valor + '%' : formatMoney(regra.valor)"></span>
                                </div>
                            </template>
                            <div x-show="!cct.regras || cct.regras.length === 0"
                                class="text-xs text-muted italic p-2 text-center">
                                Nenhuma regra específica cadastrada.
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <div x-show="convencoes.length === 0"
                class="col-span-full bg-surface border border-border-tron border-dashed rounded-cards p-12 flex flex-col items-center justify-center text-center">
                <svg class="w-12 h-12 text-muted/40 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                <div class="text-base font-medium text-main mb-1">Nenhuma convenção cadastrada</div>
                <p class="text-xs text-muted mb-4 max-w-sm">Cadastre a primeira convenção coletiva para habilitar o
                    motor de cruzamento salarial.</p>
                <button @click="subTab = 'cadastro'"
                    class="inline-flex items-center justify-center gap-2 text-xs bg-neon text-black font-semibold px-4 py-2 rounded-md hover:opacity-90 transition">
                    Cadastrar Convenção
                </button>
            </div>
        </div>
    </div>
</div>