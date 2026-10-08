<template>
  <div 
    class="fixed bottom-6 z-50 flex flex-col select-none transition-all duration-300 ease-in-out"
    :class="isChatOpen ? 'left-6 items-start' : 'right-6 items-end'"
  >
    <!-- Janela Flutuante do Webphone -->
    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="transform scale-95 opacity-0 translate-y-4"
      enter-to-class="transform scale-100 opacity-100 translate-y-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="transform scale-100 opacity-100 translate-y-0"
      leave-to-class="transform scale-95 opacity-0 translate-y-4"
    >
      <div 
        v-if="isOpen"
        class="w-80 sm:w-88 rounded-3xl bg-[#f9fbf2] border border-[#130e30]/15 shadow-2xl overflow-hidden mb-3.5 flex flex-col text-[#130e30]"
        style="max-height: 580px;"
      >
        <!-- Header do Webphone -->
        <div class="px-5 py-3.5 bg-[#eff2e5] border-b border-[#130e30]/10 flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-2xl bg-[#130e30] text-[#ffe228] flex items-center justify-center font-bold text-sm shadow-xs">
              📞
            </div>
            <div>
              <div class="flex items-center gap-1.5">
                <h3 class="text-xs font-serif-brand font-bold text-[#130e30]">Webphone FreePBX</h3>
                <span 
                  class="w-2 h-2 rounded-full"
                  :class="sipStatus === 'registered' ? 'bg-emerald-500 animate-pulse' : (sipStatus === 'calling' ? 'bg-amber-500 animate-ping' : 'bg-emerald-500')"
                ></span>
              </div>
              <p class="text-[10px] text-[#5f5c6e]">
                Ramal {{ credentials.extension || '1001' }} • {{ sipStatusLabel }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-1">
            <!-- Minimizar / Fechar -->
            <button 
              type="button"
              @click="isOpen = false"
              class="w-7 h-7 rounded-full hover:bg-white/80 text-[#5f5c6e] hover:text-[#130e30] flex items-center justify-center transition-colors cursor-pointer text-xs"
              title="Minimizar Webphone"
            >
              ✕
            </button>
          </div>
        </div>

        <!-- Conteúdo Principal: Discador ou Chamada Ativa ou Desfecho Pós-Chamada -->
        <div class="p-4 flex-1 flex flex-col justify-between overflow-y-auto">
          
          <!-- ================= ESTADO 1: DISCADOR NUMÉRICO ================= -->
          <div v-if="callState === 'idle'" class="space-y-3.5">
            <!-- Contexto do Contato Selecionado (se houver) -->
            <div v-if="targetContact" class="p-2.5 rounded-2xl bg-white border border-[#130e30]/10 flex items-center justify-between text-xs">
              <div class="min-w-0 pr-2">
                <p class="font-bold text-[#130e30] truncate">{{ targetContact.name }}</p>
                <p class="text-[10px] text-[#5f5c6e] truncate">{{ targetContact.company }}</p>
              </div>
              <button 
                type="button"
                @click="clearTargetContact"
                class="text-[10px] text-rose-600 hover:underline cursor-pointer"
              >
                Limpar
              </button>
            </div>

            <!-- Campo de Número Discado -->
            <div class="relative">
              <input 
                v-model="phoneNumber"
                type="text" 
                placeholder="(00) 00000-0000"
                class="w-full h-12 px-4 pr-10 rounded-2xl bg-white border border-[#130e30]/15 text-center text-lg font-mono font-bold text-[#130e30] placeholder:text-[#5f5c6e]/40 focus:outline-none focus:ring-2 focus:ring-[#130e30]/20"
                @keydown.enter="startCall"
              />
              <button 
                v-if="phoneNumber"
                type="button"
                @click="phoneNumber = phoneNumber.slice(0, -1)"
                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#5f5c6e] hover:text-rose-600 p-1 text-sm cursor-pointer"
                title="Apagar dígito"
              >
                ⌫
              </button>
            </div>

            <!-- Teclado Numérico DTMF (Estilo iOS / Softphone Moderno) -->
            <div class="grid grid-cols-3 gap-2 pt-1">
              <button 
                v-for="btn in dialpadButtons"
                :key="btn.digit"
                type="button"
                @click="pressDigit(btn.digit)"
                class="h-11 rounded-2xl bg-white hover:bg-[#eff2e5] border border-[#130e30]/10 active:scale-95 transition-all flex flex-col items-center justify-center cursor-pointer shadow-2xs"
              >
                <span class="text-sm font-bold text-[#130e30] leading-none">{{ btn.digit }}</span>
                <span v-if="btn.letters" class="text-[8px] text-[#5f5c6e] font-semibold tracking-wider leading-none mt-0.5">{{ btn.letters }}</span>
              </button>
            </div>

            <!-- Botão Ligar Verde -->
            <div class="pt-2">
              <button 
                type="button"
                @click="startCall"
                :disabled="!cleanPhone"
                class="w-full h-12 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-sm transition-all cursor-pointer active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed"
              >
                <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <span>Chamar Agora (FreePBX)</span>
              </button>
            </div>
          </div>

          <!-- ================= ESTADO 2: CHAMADA ATIVA / EM ANDAMENTO ================= -->
          <div v-else-if="callState === 'calling' || callState === 'connected'" class="space-y-4 py-2 text-center">
            <!-- Avatar / Pulso da Chamada -->
            <div class="relative w-20 h-20 mx-auto flex items-center justify-center">
              <div 
                class="absolute inset-0 rounded-full bg-emerald-500/20"
                :class="{ 'animate-ping': callState === 'calling', 'animate-pulse': callState === 'connected' }"
              ></div>
              <div class="w-16 h-16 rounded-full bg-[#130e30] text-[#ffe228] flex items-center justify-center text-2xl font-bold z-10 shadow-md">
                📞
              </div>
            </div>

            <!-- Dados da Chamada -->
            <div>
              <p class="text-xs font-bold uppercase tracking-wider text-[#5f5c6e]">
                {{ callState === 'calling' ? 'Chamando...' : 'Chamada Conectada' }}
              </p>
              <h4 class="text-base font-serif-brand font-bold text-[#130e30] mt-0.5">
                {{ targetContact?.name || phoneNumber }}
              </h4>
              <p v-if="targetContact?.company" class="text-xs text-[#5f5c6e]">
                {{ targetContact.company }}
              </p>
              <!-- Timer de Conversação -->
              <div class="mt-2 inline-block px-3 py-1 rounded-full bg-white border border-[#130e30]/15 font-mono text-sm font-bold text-[#130e30] shadow-2xs">
                {{ formattedTimer }}
              </div>
            </div>

            <!-- Controles da Chamada (Mudo, Espera, DTMF) -->
            <div class="grid grid-cols-3 gap-2.5 pt-2">
              <button 
                type="button"
                @click="toggleMute"
                class="h-12 rounded-2xl border transition-all flex flex-col items-center justify-center cursor-pointer"
                :class="isMuted ? 'bg-amber-100 border-amber-300 text-amber-900' : 'bg-white border-[#130e30]/10 hover:bg-[#eff2e5] text-[#130e30]'"
              >
                <span class="text-sm">{{ isMuted ? '🔇' : '🎙️' }}</span>
                <span class="text-[9px] font-bold mt-0.5">{{ isMuted ? 'Mutado' : 'Mudo' }}</span>
              </button>

              <button 
                type="button"
                @click="toggleHold"
                class="h-12 rounded-2xl border transition-all flex flex-col items-center justify-center cursor-pointer"
                :class="isOnHold ? 'bg-amber-100 border-amber-300 text-amber-900' : 'bg-white border-[#130e30]/10 hover:bg-[#eff2e5] text-[#130e30]'"
              >
                <span class="text-sm">⏸️</span>
                <span class="text-[9px] font-bold mt-0.5">{{ isOnHold ? 'Em Espera' : 'Espera' }}</span>
              </button>

              <button 
                type="button"
                @click="showInCallKeypad = !showInCallKeypad"
                class="h-12 rounded-2xl border transition-all flex flex-col items-center justify-center cursor-pointer"
                :class="showInCallKeypad ? 'bg-[#130e30] text-[#ffe228]' : 'bg-white border-[#130e30]/10 hover:bg-[#eff2e5] text-[#130e30]'"
              >
                <span class="text-sm">🔢</span>
                <span class="text-[9px] font-bold mt-0.5">Teclado</span>
              </button>
            </div>

            <!-- Mini Teclado DTMF na Chamada -->
            <div v-if="showInCallKeypad" class="grid grid-cols-3 gap-1.5 p-2 bg-white rounded-2xl border border-[#130e30]/10">
              <button 
                v-for="d in ['1','2','3','4','5','6','7','8','9','*','0','#']"
                :key="d"
                type="button"
                @click="sendDtmf(d)"
                class="h-8 rounded-xl bg-[#eff2e5] hover:bg-[#130e30] hover:text-[#ffe228] text-xs font-bold transition-colors cursor-pointer"
              >
                {{ d }}
              </button>
            </div>

            <!-- Botão Encerrar Chamada Vermelho -->
            <div class="pt-2">
              <button 
                type="button"
                @click="endCall"
                class="w-full h-12 rounded-full bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-sm transition-all cursor-pointer active:scale-95"
              >
                <span>🛑 Encerrar Chamada</span>
              </button>
            </div>
          </div>

          <!-- ================= ESTADO 3: DESFECHO PÓS-CHAMADA (WRAP-UP) ================= -->
          <div v-else-if="callState === 'wrapup'" class="space-y-3 py-1">
            <div class="text-center pb-2 border-b border-[#130e30]/10">
              <span class="text-xl">📋</span>
              <h4 class="text-xs font-serif-brand font-bold text-[#130e30] mt-0.5">Desfecho Comercial da Chamada</h4>
              <p class="text-[10px] text-[#5f5c6e]">Duração: {{ formattedTimer }} • Registre o resultado na timeline</p>
            </div>

            <!-- Seleção do Desfecho (Outcome) -->
            <div class="space-y-1">
              <label class="text-[10px] font-bold uppercase text-[#5f5c6e]">Qual foi o resultado?</label>
              <div class="grid grid-cols-1 gap-1.5 text-left">
                <button
                  v-for="out in outcomes"
                  :key="out.id"
                  type="button"
                  @click="selectedOutcome = out.id"
                  class="px-3 py-2 rounded-xl border text-xs font-bold transition-all flex items-center justify-between cursor-pointer"
                  :class="selectedOutcome === out.id ? 'bg-[#130e30] text-[#ffe228] border-[#130e30]' : 'bg-white border-[#130e30]/10 hover:bg-[#eff2e5] text-[#130e30]'"
                >
                  <span class="flex items-center gap-2">
                    <span>{{ out.icon }}</span>
                    <span>{{ out.label }}</span>
                  </span>
                  <span v-if="selectedOutcome === out.id" class="text-xs">✓</span>
                </button>
              </div>
            </div>

            <!-- Notas da Chamada -->
            <div class="space-y-1">
              <label class="text-[10px] font-bold uppercase text-[#5f5c6e]">Notas / Resumo da Conversa:</label>
              <textarea 
                v-model="callNotes"
                rows="2"
                placeholder="Ex: Decisor pediu retorno na terça ou alinhar proposta com sócio..."
                class="w-full p-2.5 rounded-xl bg-white border border-[#130e30]/15 text-xs text-[#130e30] placeholder:text-[#5f5c6e]/50 focus:outline-none focus:ring-2 focus:ring-[#130e30]/20 resize-none"
              ></textarea>
            </div>

            <!-- Painel de Conversation Intelligence por IA -->
            <div class="p-2.5 rounded-2xl bg-[#eff2e5] border border-[#130e30]/15 space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[#130e30] flex items-center gap-1">
                  <span>✨</span>
                  <span>JARVIS Speech-to-Text & Ata de IA</span>
                </span>
                <button 
                  v-if="!callAiAnalysis"
                  type="button"
                  @click="generateAiCallSummary"
                  :disabled="isAnalyzingAi"
                  class="px-2.5 py-1 rounded-full bg-[#130e30] hover:bg-[#20184f] text-[#ffe228] text-[10px] font-bold cursor-pointer transition-all active:scale-95 disabled:opacity-50 flex items-center gap-1 shadow-2xs"
                >
                  <span v-if="!isAnalyzingAi">✨ Gerar Ata</span>
                  <span v-else class="animate-spin text-[10px]">⏳</span>
                </button>
              </div>

              <!-- Animação de Carregamento -->
              <div v-if="isAnalyzingAi" class="flex items-center justify-center py-2 text-[10px] text-[#5f5c6e] gap-1.5">
                <div class="animate-spin rounded-full h-3 w-3 border-b-2 border-[#130e30]"></div>
                <span>Transcrevendo áudio e formulando ata...</span>
              </div>

              <!-- Card de Resultado da IA -->
              <div v-else-if="callAiAnalysis" class="p-2 rounded-xl bg-white border border-[#130e30]/10 text-[11px] space-y-1.5 animate-in fade-in">
                <div class="flex items-center justify-between font-bold text-[10px]">
                  <span class="text-[#130e30]">{{ callAiAnalysis.sentiment_label }}</span>
                  <span class="px-1.5 py-0.2 bg-[#ffe228] text-[#130e30] rounded-full">
                    Score: {{ callAiAnalysis.engagement_score }}/100
                  </span>
                </div>
                
                <!-- Tópicos -->
                <ul class="text-[10px] text-[#130e30] list-disc list-inside space-y-0.5 leading-snug">
                  <li v-for="(b, bIdx) in callAiAnalysis.summary_bullets" :key="bIdx">{{ b }}</li>
                </ul>

                <!-- Próxima Ação & Coaching -->
                <div class="pt-1 border-t border-[#130e30]/8 text-[9px] text-[#5f5c6e]">
                  <p><strong class="text-[#130e30]">🎯 Próxima Ação:</strong> {{ callAiAnalysis.next_actions?.[0] }}</p>
                  <p class="italic mt-0.5">💡 {{ callAiAnalysis.coaching_tip }}</p>
                </div>
              </div>
            </div>

            <!-- Botões Salvar e Descartar -->
            <div class="flex items-center gap-2 pt-1">
              <button 
                type="button" 
                @click="resetCall"
                class="flex-1 h-10 rounded-full bg-white hover:bg-[#eff2e5] border border-[#130e30]/15 text-[#5f5c6e] text-xs font-bold cursor-pointer transition-colors"
              >
                Pular
              </button>
              <button 
                type="button" 
                @click="saveCallLog"
                :disabled="isSavingLog || isAnalyzingAi"
                class="flex-2 h-10 rounded-full bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] text-xs font-bold shadow-xs cursor-pointer transition-all active:scale-95 disabled:opacity-50 flex items-center justify-center gap-1"
              >
                <span>{{ isSavingLog ? 'Salvando...' : (callAiAnalysis ? '✓ Concluir & Salvar' : 'Salvar com Ata de IA') }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Botão Flutuante Principal (FAB) -->
    <button 
      type="button"
      @click="isOpen = !isOpen"
      class="group relative h-14 px-4 rounded-full bg-[#130e30] hover:bg-[#1a1442] text-white shadow-xl hover:shadow-2xl transition-all cursor-pointer flex items-center gap-3 border border-white/10 active:scale-95"
      :class="{ 'ring-4 ring-emerald-500/30': callState === 'connected', 'ring-4 ring-amber-500/30': callState === 'calling' }"
      title="Abrir Discador WebRTC FreePBX"
    >
      <!-- Ícone com animação de pulso -->
      <div class="w-8 h-8 rounded-full bg-[#ffe228] text-[#130e30] flex items-center justify-center font-bold text-sm shrink-0">
        <span :class="{ 'animate-bounce': callState === 'calling' }">📞</span>
      </div>

      <div class="text-left pr-1 hidden sm:block">
        <div class="flex items-center gap-1.5">
          <span class="text-xs font-bold text-white tracking-wide">Webphone</span>
          <span 
            class="w-2 h-2 rounded-full"
            :class="callState === 'connected' ? 'bg-emerald-400 animate-pulse' : (callState === 'calling' ? 'bg-amber-400 animate-ping' : 'bg-emerald-400')"
          ></span>
        </div>
        <p class="text-[10px] text-white/70 font-mono">
          {{ callState === 'connected' ? formattedTimer : (callState === 'calling' ? 'Chamando...' : `Ramal ${credentials.extension || '1001'}`) }}
        </p>
      </div>
    </button>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  isChatOpen: {
    type: Boolean,
    default: false,
  },
})

const isOpen = ref(false)
const phoneNumber = ref('')
const callState = ref('idle') // idle, calling, connected, wrapup
const sipStatus = ref('registered') // registered, calling, disconnected
const sipStatusLabel = computed(() => {
  if (callState.value === 'calling') return 'Discando...'
  if (callState.value === 'connected') return 'Em Chamada'
  return 'Pronto (WebRTC)'
})

const targetContact = ref(null)
const isMuted = ref(false)
const isOnHold = ref(false)
const showInCallKeypad = ref(false)
const callDurationSeconds = ref(0)
let timerInterval = null

const selectedOutcome = ref('connected')
const callNotes = ref('')
const isSavingLog = ref(false)
const isAnalyzingAi = ref(false)
const callAiAnalysis = ref(null)
const lastSavedCallLogId = ref(null)

const credentials = ref({
  extension: '1001',
  webrtc_ws_url: '',
  server_host: '',
})

const dialpadButtons = [
  { digit: '1', letters: '' },
  { digit: '2', letters: 'ABC' },
  { digit: '3', letters: 'DEF' },
  { digit: '4', letters: 'GHI' },
  { digit: '5', letters: 'JKL' },
  { digit: '6', letters: 'MNO' },
  { digit: '7', letters: 'PQRS' },
  { digit: '8', letters: 'TUV' },
  { digit: '9', letters: 'WXYZ' },
  { digit: '*', letters: '' },
  { digit: '0', letters: '+' },
  { digit: '#', letters: '' },
]

const outcomes = [
  { id: 'connected', icon: '📞', label: 'Conversou com Decisor' },
  { id: 'meeting_scheduled', icon: '🎯', label: 'Reunião Agendada!' },
  { id: 'gatekeeper', icon: '🛑', label: 'Barrou na Secretária / Recepção' },
  { id: 'no_answer', icon: '⏳', label: 'Não Atendeu / Tocou até cair' },
  { id: 'busy', icon: '📵', label: 'Número Ocupado' },
  { id: 'voicemail', icon: '📼', label: 'Caixa Postal' },
]

const cleanPhone = computed(() => {
  return phoneNumber.value.replace(/[^0-9]/g, '')
})

const formattedTimer = computed(() => {
  const m = Math.floor(callDurationSeconds.value / 60).toString().padStart(2, '0')
  const s = (callDurationSeconds.value % 60).toString().padStart(2, '0')
  return `${m}:${s}`
})

function pressDigit(digit) {
  phoneNumber.value += digit
  playTone()
}

function clearTargetContact() {
  targetContact.value = null
  phoneNumber.value = ''
}

// Iniciar chamada
function startCall() {
  if (!cleanPhone.value) return
  callState.value = 'calling'
  callDurationSeconds.value = 0
  playRingback()

  // Simulação de atendimento em 3 segundos ou disparo real
  setTimeout(() => {
    if (callState.value === 'calling') {
      callState.value = 'connected'
      startTimer()
    }
  }, 2800)
}

function startTimer() {
  clearInterval(timerInterval)
  timerInterval = setInterval(() => {
    callDurationSeconds.value++
  }, 1000)
}

function toggleMute() {
  isMuted.value = !isMuted.value
}

function toggleHold() {
  isOnHold.value = !isOnHold.value
}

function sendDtmf(digit) {
  playTone()
}

function endCall() {
  clearInterval(timerInterval)
  callState.value = 'wrapup'
}

function resetCall() {
  callState.value = 'idle'
  callDurationSeconds.value = 0
  isMuted.value = false
  isOnHold.value = false
  showInCallKeypad.value = false
  callNotes.value = ''
  callAiAnalysis.value = null
  lastSavedCallLogId.value = null
}

async function generateAiCallSummary() {
  isAnalyzingAi.value = true
  try {
    const payload = {
      phone_dialed: phoneNumber.value,
      company_id: targetContact.value?.companyId || null,
      lead_id: targetContact.value?.leadId || null,
      duration: callDurationSeconds.value,
      billable_seconds: Math.max(0, callDurationSeconds.value - 3),
      status: 'completed',
      outcome: selectedOutcome.value,
      notes: callNotes.value,
      auto_analyze: true,
    }

    const res = await axios.post('/voip/log-call', payload)
    if (res.data?.success) {
      callAiAnalysis.value = res.data.ai_analysis
      if (res.data.ai_analysis?.quick_note && !callNotes.value) {
        callNotes.value = res.data.ai_analysis.quick_note
      }
      lastSavedCallLogId.value = res.data.call_log?.id
      router.reload({ only: ['companies'] })
    }
  } catch (err) {
    console.error('Erro ao gerar ata por IA:', err)
  } finally {
    isAnalyzingAi.value = false
  }
}

async function saveCallLog() {
  if (lastSavedCallLogId.value && callAiAnalysis.value) {
    resetCall()
    isOpen.value = false
    return
  }

  isSavingLog.value = true
  try {
    const payload = {
      phone_dialed: phoneNumber.value,
      company_id: targetContact.value?.companyId || null,
      lead_id: targetContact.value?.leadId || null,
      duration: callDurationSeconds.value,
      billable_seconds: Math.max(0, callDurationSeconds.value - 3),
      status: 'completed',
      outcome: selectedOutcome.value,
      notes: callNotes.value,
      auto_analyze: true,
    }

    const res = await axios.post('/voip/log-call', payload)
    if (res.data?.success) {
      resetCall()
      isOpen.value = false
      router.reload({ only: ['companies'] })
    }
  } catch (err) {
    console.error('Erro ao salvar log de chamada:', err)
    alert('Erro ao registrar chamada.')
  } finally {
    isSavingLog.value = false
  }
}

// Efeitos Sonoros Sutis via Web Audio API (Sem dependência de MP3 externo)
let audioCtx = null
function getAudioContext() {
  if (!audioCtx && typeof window !== 'undefined') {
    const AudioContext = window.AudioContext || window.webkitAudioContext
    if (AudioContext) audioCtx = new AudioContext()
  }
  return audioCtx
}

function playTone(freq = 440, duration = 0.1) {
  try {
    const ctx = getAudioContext()
    if (!ctx) return
    const osc = ctx.createOscillator()
    const gain = ctx.createGain()
    osc.frequency.value = freq
    gain.gain.setValueAtTime(0.05, ctx.currentTime)
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration)
    osc.connect(gain)
    gain.connect(ctx.destination)
    osc.start()
    osc.stop(ctx.currentTime + duration)
  } catch (e) {}
}

function playRingback() {
  playTone(425, 0.4)
}

// Carregar credenciais do VoIP do usuário logado
async function loadCredentials() {
  try {
    const res = await axios.get('/voip/credentials')
    if (res.data?.success) {
      credentials.value = res.data.credentials
    }
  } catch (e) {
    console.warn('VoIP credentials não carregadas:', e)
  }
}

// Listener para Click-to-Call disparado de qualquer lugar do CRM
function handleGlobalCallEvent(event) {
  const data = event.detail
  if (!data || !data.phone) return

  targetContact.value = {
    name: data.leadName || data.name || 'Decisor',
    company: data.companyName || data.company || '',
    companyId: data.companyId || null,
    leadId: data.leadId || null,
  }
  phoneNumber.value = data.phone
  isOpen.value = true
  startCall()
}

onMounted(() => {
  loadCredentials()
  window.addEventListener('jarvis:call-phone', handleGlobalCallEvent)
})

onUnmounted(() => {
  clearInterval(timerInterval)
  window.removeEventListener('jarvis:call-phone', handleGlobalCallEvent)
})
</script>
