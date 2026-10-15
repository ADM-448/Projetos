<!-- Aba: Mercado -->
<div x-show="currentTab === 'mercado'" style="display: none;" 
     x-data="{ 
         showContextModal: false, 
         showTagsModal: false,
         activeTagsJobId: null,
         activeTagsJobName: '',
         newTagInput: '',
         marketContext: JSON.parse(localStorage.getItem('marketContext')) || { porte: 'startup', segmento: 'tecnologia', regiao: 'SP' },
         saveContext() {
             localStorage.setItem('marketContext', JSON.stringify(this.marketContext));
             this.showContextModal = false;
         },
         openTagsModal(jobId, jobName) {
             this.activeTagsJobId = jobId;
             this.activeTagsJobName = jobName;
             const job = this.marketData.find(m => m.id === jobId);
             if (job && !job.tags) {
                 job.tags = Array.isArray(job.competencias_chave) ? [...job.competencias_chave] : [];
             }
             this.newTagInput = '';
             this.showTagsModal = true;
         },
         addTag() {
             if (this.newTagInput.trim() === '') return;
             const job = this.marketData.find(m => m.id === this.activeTagsJobId);
             if (job) {
                 if (!job.tags) job.tags = [];
                 if (!job.tags.includes(this.newTagInput.trim())) {
                     job.tags.push(this.newTagInput.trim());
                 }
             }
             this.newTagInput = '';
         },
         removeTag(tagToRemove) {
             const job = this.marketData.find(m => m.id === this.activeTagsJobId);
             if (job && job.tags) {
                 job.tags = job.tags.filter(t => t !== tagToRemove);
             }
         }
     }">
    <div class="mb-12 flex flex-col md:flex-row gap-4 justify-between md:items-center">
        <div>
            <h2 class="text-3xl font-normal font-display tracking-tight text-main break-words whitespace-normal">Pesquisa de Mercado Automática</h2>
            <p class="text-sm text-muted mt-2">
                Contexto atual: <span class="text-neon font-medium" x-text="marketContext.porte"></span> / 
                <span class="text-neon font-medium" x-text="marketContext.segmento"></span> / 
                <span class="text-neon font-medium" x-text="marketContext.regiao"></span>
                <button @click="showContextModal = true" type="button" class="ml-2 underline text-muted hover:text-main">Editar</button>
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button type="button" @click="showNovaVagaModal = true"
                class="px-4 py-3 bg-panel border border-blue-500 text-blue-400 font-medium rounded-none hover:bg-blue-500 hover:text-white transition text-sm flex items-center gap-2 shadow-[0_0_8px_rgba(59,130,246,0.2)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Nova Vaga (Pesquisa CEP)</span>
            </button>

            <form action="{{ route('dashboard.market-sync-all') }}" method="POST" @submit="
                $event.preventDefault();
                $el.submit();
            ">
                @csrf
                <input type="hidden" name="porte_empresa" :value="marketContext.porte">
                <input type="hidden" name="segmento_empresa" :value="marketContext.segmento">
                <input type="hidden" name="regiao_empresa" :value="marketContext.regiao">
                
                <template x-for="m in marketData" :key="'form_tag_'+m.id">
                    <template x-if="(m.tags && m.tags.length > 0) || (m.competencias_chave && m.competencias_chave.length > 0)">
                        <template x-for="(tag, index) in (m.tags || m.competencias_chave)" :key="index">
                            <input type="hidden" :name="'tags['+(m.cargo_id ?? m.cargo)+'][]'" :value="tag">
                        </template>
                    </template>
                </template>
                
                <button type="submit"
                    class="px-5 py-3 bg-surface border border-neon text-neon font-medium rounded-none hover:bg-neon hover:text-white transition text-sm">
                    Pesquisar Todos
                </button>
            </form>
        </div>
    </div>

    <div class="bg-surface border border-border-tron rounded-cards shadow-none overflow-hidden w-full max-w-full">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left min-w-[800px]">
                <thead class="bg-transparent border-b border-divider">
                    <tr>
                        <th class="px-6 py-5 text-[10px] font-medium text-muted uppercase hover:bg-panel transition">Cargo</th>
                        <th class="px-6 py-5 text-[10px] font-medium text-muted uppercase hover:bg-panel transition">Competências</th>
                        <th class="px-6 py-5 text-[10px] font-medium text-muted uppercase text-right hover:bg-panel transition">Piso</th>
                        <th class="px-6 py-5 text-[10px] font-medium text-main uppercase text-right hover:bg-panel transition">Mediana</th>
                        <th class="px-6 py-5 text-[10px] font-medium text-muted uppercase text-right hover:bg-panel transition">Teto</th>
                        <th class="px-6 py-5 text-[10px] font-medium text-muted uppercase text-center hover:bg-panel transition">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-divider">
                    <template x-for="m in marketData" :key="m.id">
                        <tr class="hover:bg-panel transition-colors">
                            <td class="px-6 py-4 text-sm text-main break-words whitespace-normal font-medium" x-text="m.cargo"></td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2 items-center">
                                    <template x-if="(m.tags || m.competencias_chave) && (m.tags || m.competencias_chave).length > 0">
                                        <template x-for="tag in (m.tags || m.competencias_chave)" :key="tag">
                                            <span class="px-2 py-1 text-xs font-medium bg-panel text-muted border border-border-tron rounded-tags" x-text="tag"></span>
                                        </template>
                                    </template>
                                    <button @click="openMarketRaioX(m.id)" x-show="m.status === 'completed'" type="button" class="p-1 rounded-full text-muted hover:text-neon hover:bg-neon/10 transition" title="Ver Análise Completa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>
                                    <button @click="openTagsModal(m.id, m.cargo)" type="button" class="p-1 rounded-full text-muted hover:text-neon hover:bg-neon/10 transition" title="Editar Competências">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-muted text-right font-sans" x-text="m.piso ? formatMoney(m.piso) : '-'"></td>
                            <td class="px-6 py-4 text-sm text-main font-medium text-right font-sans" x-text="m.mediana ? formatMoney(m.mediana) : '-'"></td>
                            <td class="px-6 py-4 text-sm text-muted text-right font-sans" x-text="m.teto ? formatMoney(m.teto) : '-'"></td>
                            <td class="px-6 py-4 text-xs text-center font-medium">
                                <span x-show="m.status === 'completed'" class="text-neon bg-surface border border-neon px-2 py-1 rounded-sm">Atualizado</span>
                                <span x-show="m.status === 'pending'" class="text-muted border border-border-tron bg-panel px-2 py-1 rounded-sm">Pendente</span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de Contexto -->
    <div x-show="showContextModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="bg-surface border border-border-tron rounded-cards p-8 w-full max-w-md shadow-2xl">
            <h3 class="text-xl font-display text-main mb-4">Configurar Contexto da Empresa</h3>
            <p class="text-sm text-muted mb-6">Esses dados nos ajudam a encontrar salários mais precisos (ex: uma Startup de Tecnologia paga diferente de uma Indústria Gigante).</p>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-muted uppercase mb-1">Porte da Empresa</label>
                    <select x-model="marketContext.porte" class="w-full bg-panel border border-border-tron rounded-none px-4 py-2 text-main focus:border-neon focus:ring-0">
                        <option value="startup">Startup / Seed</option>
                        <option value="pme">PME (Pequena/Média)</option>
                        <option value="grande_nacional">Grande (Nacional)</option>
                        <option value="multinacional">Multinacional</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-muted uppercase mb-1">Segmento</label>
                    <input type="text" x-model="marketContext.segmento" class="w-full bg-panel border border-border-tron rounded-none px-4 py-2 text-main focus:border-neon focus:ring-0" placeholder="Ex: Tecnologia, Varejo, Saúde...">
                </div>
                <div>
                    <label class="block text-xs font-medium text-muted uppercase mb-1">Região de Atuação (Estado/País)</label>
                    <input type="text" x-model="marketContext.regiao" class="w-full bg-panel border border-border-tron rounded-none px-4 py-2 text-main focus:border-neon focus:ring-0" placeholder="Ex: SP, PR, Remoto BR">
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-4">
                <button @click="showContextModal = false" type="button" class="px-4 py-2 text-muted hover:text-main text-sm">Cancelar</button>
                <button @click="saveContext()" type="button" class="px-5 py-2 bg-neon text-white font-medium text-sm rounded-none hover:bg-neon/90">Salvar Contexto</button>
            </div>
        </div>
    </div>

    <!-- Modal de Tags/Competências -->
    <div x-show="showTagsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="bg-surface border border-border-tron rounded-cards p-8 w-full max-w-lg shadow-2xl">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-xl font-display text-main">Competências do Cargo</h3>
                    <p class="text-sm text-neon mt-1" x-text="activeTagsJobName"></p>
                </div>
                <button @click="showTagsModal = false" class="text-muted hover:text-main p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <p class="text-sm text-muted mb-6">Adicione as tecnologias, certificações ou habilidades exigidas para este cargo. Pressione <kbd class="px-2 py-0.5 mx-1 font-sans text-xs font-bold text-neon bg-neon/10 border border-neon/30 rounded shadow-[0_0_8px_rgba(var(--color-neon-rgb),0.3)]">Enter ↵</kbd> para adicionar.</p>
            
            <div class="space-y-4">
                <div>
                    <input type="text" x-model="newTagInput" @keydown.enter.prevent="addTag()" class="w-full bg-panel border border-border-tron rounded-none px-4 py-3 text-main focus:border-neon focus:ring-0" placeholder="Ex: Python, Inglês Fluente, AWS...">
                </div>

                <div class="flex flex-wrap gap-2 mt-4 min-h-[50px] p-4 border border-dashed border-border-tron rounded-cards bg-canvas">
                    <!-- Lista de tags ativas -->
                    <template x-if="marketData.find(m => m.id === activeTagsJobId)?.tags">
                        <template x-for="tag in marketData.find(m => m.id === activeTagsJobId).tags" :key="tag">
                            <span class="px-3 py-1 text-sm font-medium bg-surface text-main border border-neon/50 rounded-tags flex items-center gap-2 shadow-sm">
                                <span x-text="tag"></span>
                                <button @click="removeTag(tag)" class="text-muted hover:text-red-500 focus:outline-none">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </span>
                        </template>
                    </template>
                    <template x-if="!marketData.find(m => m.id === activeTagsJobId)?.tags || marketData.find(m => m.id === activeTagsJobId).tags.length === 0">
                        <span class="text-xs text-muted italic my-auto">Nenhuma competência vinculada ainda.</span>
                    </template>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button @click="showTagsModal = false" type="button" class="px-6 py-2 bg-neon text-white font-medium text-sm rounded-none hover:bg-neon/90 transition shadow-neon">Concluído</button>
            </div>
        </div>
    </div>
    
    <!-- Modal Análise Completa do Mercado -->
    @include('partials.dashboard.modals.mercado_raio_x')
</div>
