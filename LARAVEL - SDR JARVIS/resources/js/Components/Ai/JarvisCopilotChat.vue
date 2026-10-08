<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  lead: {
    type: Object,
    required: true,
  },
  currentDraft: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['insert-text', 'stage-advanced', 'notify'])

// Estado interno do Copilot
const isExpanded = ref(true)
const activeTab = ref('suggestions') // 'suggestions' | 'qualification' | 'polish'
const isLoading = ref(false)
const isQualifying = ref(false)
const isPolishing = ref(false)
const isAdvancing = ref(false)

const suggestions = ref([])
const qualification = ref(null)
const customContext = ref('')
const polishedText = ref('')

// Computed properties
const leadName = computed(() => {
  return props.lead?.name ? props.lead.name.split(' ')[0] : 'Decisor'
})

const companyName = computed(() => {
  return props.lead?.company_name || props.lead?.company?.name || 'Empresa'
})

const existingAnalysis = computed(() => {
  return props.lead?.custom_attributes?.ai_analysis || null
})

// Busca sugestões da IA
async function fetchSuggestions(overrideContext = null) {
  if (!props.lead?.id) return
  isLoading.value = true

  try {
    const res = await axios.post('/ai/suggest-replies', {
      lead_id: props.lead.id,
      context: overrideContext !== null ? overrideContext : customContext.value,
    })

    if (res.data.success) {
      suggestions.value = res.data.suggestions || []
    }
  } catch (err) {
    console.error('Erro ao buscar sugestões de IA:', err)
  } finally {
    isLoading.value = false
  }
}

// Analisa e qualifica o lead
async function fetchQualification() {
  if (!props.lead?.id) return
  isQualifying.value = true

  try {
    const res = await axios.post('/ai/qualify-lead', {
      lead_id: props.lead.id,
    })

    if (res.data.success) {
      qualification.value = res.data.qualification
      if (props.lead.custom_attributes) {
        props.lead.custom_attributes.ai_analysis = res.data.qualification
      } else {
        props.lead.custom_attributes = { ai_analysis: res.data.qualification }
      }
    }
  } catch (err) {
    console.error('Erro ao qualificar lead:', err)
  } finally {
    isQualifying.value = false
  }
}

// Refina o tom do rascunho
async function polishDraft(tone) {
  if (!props.currentDraft.trim() || !props.lead?.id) return
  isPolishing.value = true
  polishedText.value = ''

  try {
    const res = await axios.post('/ai/improve-draft', {
      lead_id: props.lead.id,
      text: props.currentDraft,
      tone: tone,
    })

    if (res.data.success) {
      polishedText.value = res.data.text
    }
  } catch (err) {
    console.error('Erro ao polir mensagem:', err)
  } finally {
    isPolishing.value = false
  }
}

// Avança a etapa recomendada pela IA
async function applyRecommendedStage() {
  if (!props.lead?.id || !qualification.value?.suggested_stage) return
  isAdvancing.value = true

  try {
    const res = await axios.post('/ai/auto-advance-stage', {
      lead_id: props.lead.id,
      target_stage_name: qualification.value.suggested_stage,
    })

    if (res.data.success) {
      emit('stage-advanced', res.data)
      emit('notify', {
        type: 'success',
        message: res.data.message || 'Conta movida com sucesso!',
      })
    }
  } catch (err) {
    console.error('Erro ao avançar etapa:', err)
    emit('notify', {
      type: 'error',
      message: 'Não foi possível mover a etapa automaticamente.',
    })
  } finally {
    isAdvancing.value = false
  }
}

function handleInsert(text) {
  emit('insert-text', text)
}

function applyPolishedText() {
  if (polishedText.value) {
    emit('insert-text', polishedText.value)
    polishedText.value = ''
  }
}

// Monitora mudança de lead selecionado
watch(
  () => props.lead?.id,
  (newId) => {
    if (newId) {
      customContext.value = ''
      polishedText.value = ''
      if (existingAnalysis.value) {
        qualification.value = existingAnalysis.value
      } else {
        qualification.value = null
      }
      fetchSuggestions()
    }
  },
  { immediate: true }
)
</script>

<template>
  <div class="border-b border-[#130e30]/15 bg-[#f5f8ed] transition-all">
    <!-- Header / Toggle Bar -->
    <div 
      class="px-4 py-2.5 flex items-center justify-between cursor-pointer select-none bg-[#eff2e5] hover:bg-[#e6ebdc] transition-colors"
      @click="isExpanded = !isExpanded"
    >
      <div class="flex items-center gap-2">
        <div class="w-6 h-6 rounded-lg bg-[#130e30] text-[#ffe228] flex items-center justify-center font-bold text-xs shadow-xs animate-pulse">
          ✨
        </div>
        <div class="flex items-center gap-2">
          <span class="text-xs font-bold text-[#130e30] font-serif-brand tracking-wide">JARVIS Co-Pilot IA</span>
          <span v-if="qualification?.sentiment_label" class="text-[10px] font-bold px-2 py-0.2 rounded-full bg-[#130e30] text-[#ffe228]">
            {{ qualification.sentiment_label }}
          </span>
          <span v-else-if="isLoading" class="text-[10px] text-[#5f5c6e] italic">
            analisando contexto...
          </span>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <button 
          v-if="isExpanded"
          type="button"
          @click.stop="fetchSuggestions()" 
          title="Regenerar sugestões"
          :disabled="isLoading"
          class="p-1 rounded-full text-[#130e30] hover:bg-white transition-colors cursor-pointer disabled:opacity-50"
        >
          <svg class="w-3.5 h-3.5" :class="{ 'animate-spin': isLoading }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
        </button>

        <span class="text-xs text-[#5f5c6e]">
          {{ isExpanded ? '▲' : '▼' }}
        </span>
      </div>
    </div>

    <!-- Expanded Copilot Panel -->
    <div v-show="isExpanded" class="p-3 border-t border-[#130e30]/8 space-y-3">
      <!-- Tabs Selector -->
      <div class="flex items-center gap-1 bg-[#e4ebd7] p-1 rounded-xl text-[11px] font-medium text-[#130e30]">
        <button 
          type="button"
          @click="activeTab = 'suggestions'"
          class="flex-1 py-1 px-2 rounded-lg text-center transition-all cursor-pointer"
          :class="activeTab === 'suggestions' ? 'bg-[#130e30] text-[#ffe228] font-bold shadow-xs' : 'hover:bg-white/60 text-[#5f5c6e]'"
        >
          💡 Sugestões ({{ suggestions.length }})
        </button>
        <button 
          type="button"
          @click="activeTab = 'qualification'; if (!qualification && !isQualifying) fetchQualification()"
          class="flex-1 py-1 px-2 rounded-lg text-center transition-all cursor-pointer flex items-center justify-center gap-1"
          :class="activeTab === 'qualification' ? 'bg-[#130e30] text-[#ffe228] font-bold shadow-xs' : 'hover:bg-white/60 text-[#5f5c6e]'"
        >
          <span>🎯 Qualificação</span>
          <span v-if="qualification?.score" class="text-[9px] px-1 py-0.1 bg-[#ffe228] text-[#130e30] rounded-full font-bold">
            {{ qualification.score }}
          </span>
        </button>
        <button 
          type="button"
          @click="activeTab = 'polish'"
          class="flex-1 py-1 px-2 rounded-lg text-center transition-all cursor-pointer"
          :class="activeTab === 'polish' ? 'bg-[#130e30] text-[#ffe228] font-bold shadow-xs' : 'hover:bg-white/60 text-[#5f5c6e]'"
        >
          🪄 Refinar Tom
        </button>
      </div>

      <!-- TAB 1: SUGESTÕES DE RESPOSTA -->
      <div v-if="activeTab === 'suggestions'" class="space-y-2">
        <div v-if="isLoading" class="flex items-center justify-center py-6 text-xs text-[#5f5c6e] gap-2">
          <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-[#130e30]"></div>
          <span>JARVIS formulando respostas personalizadas...</span>
        </div>

        <div v-else-if="suggestions.length === 0" class="text-center py-4 text-xs text-[#5f5c6e]">
          <p>Nenhuma sugestão gerada no momento.</p>
          <button 
            @click="fetchSuggestions()" 
            class="mt-2 px-3 py-1 bg-[#130e30] text-[#ffe228] rounded-full text-[10px] font-bold cursor-pointer"
          >
            Gerar Sugestões
          </button>
        </div>

        <div v-else class="space-y-2 max-h-56 overflow-y-auto pr-1">
          <div 
            v-for="(sug, index) in suggestions" 
            :key="index"
            class="p-2.5 rounded-2xl bg-white border border-[#130e30]/10 hover:border-[#130e30]/30 transition-all shadow-xs group"
          >
            <div class="flex items-center justify-between mb-1">
              <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#eff2e5] text-[#130e30] border border-[#130e30]/10">
                {{ sug.badge }}
              </span>
              <button 
                type="button"
                @click="handleInsert(sug.text)"
                class="text-[10px] font-bold text-[#130e30] hover:text-black bg-[#ffe228] hover:bg-[#fad81d] px-2.5 py-0.5 rounded-full border border-[#130e30]/15 transition-all cursor-pointer active:scale-95 flex items-center gap-1 shadow-2xs"
              >
                <span>Usar Mensagem</span>
                <span>↳</span>
              </button>
            </div>
            
            <p class="text-xs text-[#130e30] leading-relaxed select-text font-sans">
              {{ sug.text }}
            </p>

            <div v-if="sug.strategy" class="mt-1.5 pt-1.5 border-t border-[#130e30]/5 text-[9px] text-[#5f5c6e] italic">
              💡 {{ sug.strategy }}
            </div>
          </div>
        </div>

        <!-- Custom Guidance / Prompt Box -->
        <div class="pt-1 flex gap-1.5">
          <input 
            v-model="customContext"
            type="text"
            placeholder="Instruir JARVIS: ex: 'Lead achou caro' ou 'Pedir e-mail'..."
            class="flex-1 px-3 py-1.5 text-xs rounded-xl bg-white border border-[#130e30]/15 text-[#130e30] placeholder-[#5f5c6e] focus:outline-none focus:border-[#130e30]"
            @keydown.enter.prevent="fetchSuggestions(customContext)"
          />
          <button 
            type="button"
            @click="fetchSuggestions(customContext)"
            :disabled="isLoading || !customContext.trim()"
            class="px-3 py-1.5 bg-[#130e30] hover:bg-[#20184f] text-[#ffe228] font-bold text-xs rounded-xl disabled:opacity-50 cursor-pointer transition-all active:scale-95"
          >
            Pedir
          </button>
        </div>
      </div>

      <!-- TAB 2: QUALIFICAÇÃO & SENTIMENTO -->
      <div v-else-if="activeTab === 'qualification'" class="space-y-3">
        <div v-if="isQualifying" class="flex items-center justify-center py-6 text-xs text-[#5f5c6e] gap-2">
          <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-[#130e30]"></div>
          <span>Analisando engajamento e sinais de compra...</span>
        </div>

        <div v-else-if="!qualification" class="text-center py-4">
          <p class="text-xs text-[#5f5c6e] mb-2">Nenhuma análise salva para este contato ainda.</p>
          <button 
            type="button"
            @click="fetchQualification"
            class="px-4 py-1.5 rounded-full bg-[#130e30] text-[#ffe228] font-bold text-xs cursor-pointer shadow-xs active:scale-95"
          >
            Executar Análise de IA Agora
          </button>
        </div>

        <div v-else class="space-y-2.5">
          <!-- Score & Sentiment Header -->
          <div class="p-3 rounded-2xl bg-white border border-[#130e30]/10 flex items-center justify-between">
            <div>
              <div class="text-[10px] text-[#5f5c6e] font-semibold uppercase tracking-wider">Score de Calor</div>
              <div class="flex items-baseline gap-1.5 mt-0.5">
                <span class="text-xl font-bold font-serif-brand text-[#130e30]">{{ qualification.score }}</span>
                <span class="text-[10px] text-[#5f5c6e]">/ 100</span>
              </div>
            </div>

            <div class="text-right">
              <div class="text-[10px] text-[#5f5c6e] font-semibold uppercase tracking-wider">Diagnóstico</div>
              <div class="text-xs font-bold text-[#130e30] mt-0.5">
                {{ qualification.sentiment_label }}
              </div>
            </div>
          </div>

          <!-- Objections tags -->
          <div v-if="qualification.objections && qualification.objections.length > 0" class="p-2.5 rounded-2xl bg-amber-50 border border-amber-200">
            <span class="text-[10px] font-bold text-amber-900 block mb-1">⚠️ Objeções Mapeadas:</span>
            <div class="flex flex-wrap gap-1">
              <span 
                v-for="obj in qualification.objections" 
                :key="obj"
                class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded-md text-[10px] font-medium"
              >
                {{ obj }}
              </span>
            </div>
          </div>

          <!-- Summary & Next Step -->
          <div class="p-2.5 rounded-2xl bg-white border border-[#130e30]/10 text-xs space-y-1.5">
            <p class="text-[#130e30] leading-snug">
              {{ qualification.summary }}
            </p>
            <div class="pt-1.5 border-t border-[#130e30]/8 flex items-start gap-1.5 text-[11px] text-[#130e30] font-medium">
              <span class="text-[#130e30]">🎯 Próxima Ação:</span>
              <span class="font-normal text-[#5f5c6e]">{{ qualification.next_best_action }}</span>
            </div>
          </div>

          <!-- Auto-advance button -->
          <div v-if="qualification.suggested_stage" class="pt-1">
            <button 
              type="button"
              @click="applyRecommendedStage"
              :disabled="isAdvancing"
              class="w-full py-2 px-3 rounded-2xl bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] font-bold text-xs flex items-center justify-center gap-1.5 border border-[#130e30]/20 shadow-xs cursor-pointer transition-all active:scale-[0.98] disabled:opacity-50"
            >
              <span v-if="!isAdvancing">🚀 Mover Conta para "{{ qualification.suggested_stage }}"</span>
              <span v-else class="flex items-center gap-1">
                <div class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-[#130e30]"></div>
                Movendo etapa...
              </span>
            </button>
          </div>
        </div>
      </div>

      <!-- TAB 3: REFINAR TOM (MAGIC POLISH) -->
      <div v-else-if="activeTab === 'polish'" class="space-y-2.5">
        <p class="text-[11px] text-[#5f5c6e]">
          Transforme qualquer rascunho de mensagem no padrão de persuasão executiva do JARVIS.
        </p>

        <!-- Current draft status -->
        <div class="p-2 rounded-xl bg-white border border-[#130e30]/10 text-xs text-[#130e30]">
          <span class="text-[10px] font-bold text-[#5f5c6e] uppercase block mb-0.5">Texto atual no campo de envio:</span>
          <p v-if="currentDraft.trim()" class="italic line-clamp-2">{{ currentDraft }}</p>
          <p v-else class="text-[#5f5c6e] italic">Digite algo na caixa de envio abaixo primeiro.</p>
        </div>

        <!-- Tone buttons -->
        <div class="grid grid-cols-2 gap-1.5">
          <button 
            type="button"
            @click="polishDraft('short')"
            :disabled="isPolishing || !currentDraft.trim()"
            class="py-1.5 px-2 rounded-xl bg-white hover:bg-[#eff2e5] border border-[#130e30]/15 text-[#130e30] text-xs font-semibold flex items-center justify-center gap-1 transition-all cursor-pointer disabled:opacity-50 active:scale-95"
          >
            <span>⚡ Mais Curto & Direto</span>
          </button>
          <button 
            type="button"
            @click="polishDraft('persuasive')"
            :disabled="isPolishing || !currentDraft.trim()"
            class="py-1.5 px-2 rounded-xl bg-white hover:bg-[#eff2e5] border border-[#130e30]/15 text-[#130e30] text-xs font-semibold flex items-center justify-center gap-1 transition-all cursor-pointer disabled:opacity-50 active:scale-95"
          >
            <span>🎯 Mais Persuasivo</span>
          </button>
          <button 
            type="button"
            @click="polishDraft('executive')"
            :disabled="isPolishing || !currentDraft.trim()"
            class="py-1.5 px-2 rounded-xl bg-white hover:bg-[#eff2e5] border border-[#130e30]/15 text-[#130e30] text-xs font-semibold flex items-center justify-center gap-1 transition-all cursor-pointer disabled:opacity-50 active:scale-95"
          >
            <span>👔 Mais Executivo</span>
          </button>
          <button 
            type="button"
            @click="polishDraft('friendly')"
            :disabled="isPolishing || !currentDraft.trim()"
            class="py-1.5 px-2 rounded-xl bg-white hover:bg-[#eff2e5] border border-[#130e30]/15 text-[#130e30] text-xs font-semibold flex items-center justify-center gap-1 transition-all cursor-pointer disabled:opacity-50 active:scale-95"
          >
            <span>💬 Mais Amigável</span>
          </button>
        </div>

        <!-- Polished Result Display -->
        <div v-if="isPolishing" class="flex items-center justify-center py-4 text-xs text-[#5f5c6e] gap-2">
          <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-[#130e30]"></div>
          <span>Polindo mensagem...</span>
        </div>

        <div v-else-if="polishedText" class="p-3 rounded-2xl bg-white border border-[#130e30]/20 space-y-2 animate-in fade-in">
          <div class="flex items-center justify-between text-[10px] font-bold text-[#130e30]">
            <span>✨ Mensagem Refinada:</span>
            <button 
              type="button"
              @click="applyPolishedText"
              class="px-2.5 py-0.5 bg-[#ffe228] hover:bg-[#fad81d] rounded-full border border-[#130e30]/15 cursor-pointer font-bold active:scale-95"
            >
              Substituir no Campo ↳
            </button>
          </div>
          <p class="text-xs text-[#130e30] leading-relaxed">
            {{ polishedText }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
