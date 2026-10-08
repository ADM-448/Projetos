<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['close', 'notify'])

// Estado das Instâncias
const instances = ref([])
const activeInstance = ref(null)
const isLoadingInstances = ref(false)
const gatewayInfo = ref({ base_url: '', is_mock: false })

// Estado do QR Code
const qrCodeData = ref(null)
const isLoadingQr = ref(false)
const isPolling = ref(false)
let pollTimer = null

// Estado de Teste de Envio
const testPhone = ref('')
const isSendingTest = ref(false)
const testResult = ref(null)

// Estado do Ping Gateway
const isPinging = ref(false)
const pingResult = ref(null)
const showDockerHelp = ref(false)

// Nova Instância
const showNewInstanceForm = ref(false)
const newInstanceName = ref('')
const isCreatingInstance = ref(false)

// Carregar Instâncias
async function loadInstances() {
  isLoadingInstances.value = true
  try {
    const res = await axios.get('/whatsapp/instances')
    if (res.data?.success) {
      instances.value = res.data.instances || []
      gatewayInfo.value = res.data.gateway || {}
      if (instances.value.length > 0 && !activeInstance.value) {
        activeInstance.value = instances.value[0]
      }
    }
  } catch (err) {
    console.error('Erro ao carregar instâncias:', err)
  } finally {
    isLoadingInstances.value = false
  }
}

// Gerar / Buscar QR Code
async function fetchQrCode(instance) {
  if (!instance) return
  activeInstance.value = instance
  isLoadingQr.value = true
  qrCodeData.value = null
  testResult.value = null

  try {
    const res = await axios.get(`/whatsapp/instances/${instance.id}/qr`)
    if (res.data?.success) {
      qrCodeData.value = res.data
      instance.status = 'connecting'
      startPollingStatus(instance)
    }
  } catch (err) {
    emit('notify', {
      type: 'error',
      message: err.response?.data?.message || 'Falha ao gerar QR Code. Verifique o gateway.',
    })
  } finally {
    isLoadingQr.value = false
  }
}

// Polling de Status para checar quando o celular conectar
function startPollingStatus(instance) {
  stopPolling()
  isPolling.value = true

  pollTimer = setInterval(async () => {
    try {
      const res = await axios.get(`/whatsapp/instances/${instance.id}/status`)
      if (res.data?.is_connected) {
        instance.status = 'connected'
        stopPolling()
        qrCodeData.value = null
        emit('notify', {
          type: 'success',
          message: `🎉 WhatsApp conectado com sucesso na instância ${instance.instance_name}!`,
        })
      }
    } catch (e) {
      // Ignora oscilações momentâneas
    }
  }, 3000)
}

function stopPolling() {
  if (pollTimer) {
    clearInterval(pollTimer)
    pollTimer = null
  }
  isPolling.value = false
}

// Desconectar Sessão
async function disconnectInstance(instance) {
  if (!confirm(`Deseja realmente desconectar a sessão do WhatsApp de "${instance.instance_name}"?`)) return
  try {
    const res = await axios.post(`/whatsapp/instances/${instance.id}/disconnect`)
    if (res.data?.success) {
      instance.status = 'disconnected'
      qrCodeData.value = null
      stopPolling()
      emit('notify', {
        type: 'success',
        message: 'Instância desconectada com sucesso.',
      })
    }
  } catch (err) {
    emit('notify', {
      type: 'error',
      message: 'Falha ao desconectar instância.',
    })
  }
}

// Enviar Mensagem de Teste
async function sendTestMessage() {
  if (!activeInstance.value || !testPhone.value.trim()) return
  isSendingTest.value = true
  testResult.value = null

  try {
    const res = await axios.post('/whatsapp/instances/test-send', {
      instance_id: activeInstance.value.id,
      phone: testPhone.value,
      message: '🚀 Olá! Esta é uma mensagem de teste enviada diretamente pelo seu SDR JARVIS.',
    })

    testResult.value = {
      success: true,
      message: res.data?.message || 'Mensagem enviada!',
    }
  } catch (err) {
    testResult.value = {
      success: false,
      message: err.response?.data?.message || 'Erro ao enviar mensagem de teste.',
    }
  } finally {
    isSendingTest.value = false
  }
}

// Testar Gateway (Ping)
async function testGatewayPing() {
  isPinging.value = true
  pingResult.value = null
  try {
    const res = await axios.post('/whatsapp/gateway/ping')
    pingResult.value = res.data
  } catch (err) {
    pingResult.value = {
      online: false,
      message: 'Não foi possível se comunicar com o gateway de WhatsApp.',
    }
  } finally {
    isPinging.value = false
  }
}

// Criar Nova Instância
async function createNewInstance() {
  if (!newInstanceName.value.trim()) return
  isCreatingInstance.value = true
  try {
    const res = await axios.post('/whatsapp/instances', {
      instance_name: newInstanceName.value,
    })
    if (res.data?.success) {
      instances.value.unshift(res.data.instance)
      activeInstance.value = res.data.instance
      showNewInstanceForm.value = false
      newInstanceName.value = ''
      emit('notify', {
        type: 'success',
        message: 'Nova instância criada! Agora gere o QR Code para conectar.',
      })
    }
  } catch (err) {
    emit('notify', {
      type: 'error',
      message: err.response?.data?.message || 'Erro ao criar instância.',
    })
  } finally {
    isCreatingInstance.value = false
  }
}

function handleClose() {
  stopPolling()
  emit('close')
}

onMounted(() => {
  loadInstances()
})

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    loadInstances()
  } else {
    stopPolling()
  }
})

onUnmounted(() => {
  stopPolling()
})
</script>

<template>
  <div 
    v-if="isOpen" 
    class="fixed inset-0 z-50 flex items-center justify-center bg-[#130e30]/40 backdrop-blur-sm p-4 overflow-y-auto"
    @click.self="handleClose"
  >
    <div class="w-full max-w-3xl rounded-3xl bg-[#f9fbf2] border border-[#130e30]/15 p-6 shadow-2xl my-6 text-[#130e30] space-y-6">
      <!-- Header do Modal -->
      <div class="flex items-center justify-between pb-3 border-b border-[#130e30]/10">
        <div class="flex items-center gap-3">
          <div class="w-11 h-11 rounded-2xl bg-[#130e30] text-[#ffe228] flex items-center justify-center text-xl font-bold shadow-xs">
            📱
          </div>
          <div>
            <h3 class="text-base font-serif-brand font-bold text-[#130e30] flex items-center gap-2">
              <span>Conexão WhatsApp (WhatsApp Web / QR Code)</span>
              <span 
                class="text-[10px] px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider"
                :class="activeInstance?.status === 'connected' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
              >
                {{ activeInstance?.status === 'connected' ? '● Conectado' : '○ Aguardando Conexão' }}
              </span>
            </h3>
            <p class="text-xs text-[#5f5c6e]">
              Conecte seu aparelho celular para disparar cadências e atender leads com IA de forma 100% autônoma.
            </p>
          </div>
        </div>

        <button 
          type="button" 
          @click="handleClose"
          class="w-8 h-8 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-[#130e30] flex items-center justify-center cursor-pointer transition-colors"
        >
          ✕
        </button>
      </div>

      <!-- Grid Principal: Seletor de Instância & QR Code -->
      <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <!-- Coluna Esquerda: Lista de Instâncias & Ações (5 colunas) -->
        <div class="md:col-span-5 space-y-4">
          <div class="flex items-center justify-between">
            <h4 class="text-xs font-bold uppercase tracking-wider text-[#5f5c6e]">
              Instâncias de Chip ({{ instances.length }})
            </h4>
            <button 
              type="button"
              @click="showNewInstanceForm = !showNewInstanceForm"
              class="text-[11px] font-bold text-[#130e30] hover:underline cursor-pointer"
            >
              {{ showNewInstanceForm ? '✕ Cancelar' : '+ Nova Instância' }}
            </button>
          </div>

          <!-- Formulário Rápido de Nova Instância -->
          <div v-if="showNewInstanceForm" class="p-3 rounded-2xl bg-white border border-[#130e30]/15 space-y-2">
            <label class="block text-[10px] font-bold uppercase text-[#5f5c6e]">Nome da Instância</label>
            <input 
              v-model="newInstanceName"
              type="text"
              placeholder="Ex: chip-comercial-01"
              class="w-full h-8 px-3 text-xs rounded-xl bg-[#f9fbf2] border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
            />
            <button 
              type="button"
              @click="createNewInstance"
              :disabled="isCreatingInstance || !newInstanceName.trim()"
              class="w-full h-8 rounded-xl bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] text-xs font-bold cursor-pointer disabled:opacity-50"
            >
              {{ isCreatingInstance ? 'Criando...' : 'Criar Instância' }}
            </button>
          </div>

          <!-- Lista de Instâncias -->
          <div class="space-y-2">
            <div 
              v-for="inst in instances" 
              :key="inst.id"
              @click="activeInstance = inst"
              class="p-3.5 rounded-2xl border transition-all cursor-pointer space-y-1.5"
              :class="activeInstance?.id === inst.id 
                ? 'bg-white border-[#130e30] shadow-xs ring-1 ring-[#130e30]/20' 
                : 'bg-white/60 border-[#130e30]/10 hover:bg-white'"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="w-2.5 h-2.5 rounded-full" :class="inst.status === 'connected' ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400'"></span>
                  <span class="text-xs font-bold text-[#130e30]">{{ inst.instance_name }}</span>
                </div>
                <span 
                  class="text-[9px] font-bold px-2 py-0.5 rounded-full uppercase"
                  :class="inst.status === 'connected' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700'"
                >
                  {{ inst.status }}
                </span>
              </div>

              <div class="flex items-center justify-between text-[10px] text-[#5f5c6e]">
                <span>Disparos Hoje: <strong>{{ inst.sent_today || 0 }} / {{ inst.daily_limit || 60 }}</strong></span>
                <span v-if="inst.phone_number">📱 {{ inst.phone_number }}</span>
              </div>

              <!-- Ações da Instância -->
              <div class="pt-1 flex items-center gap-2 border-t border-[#130e30]/5 text-[10px]">
                <button 
                  v-if="inst.status !== 'connected'"
                  type="button"
                  @click.stop="fetchQrCode(inst)"
                  class="text-xs font-bold text-emerald-700 hover:text-emerald-900 flex items-center gap-1 cursor-pointer"
                >
                  <span>📷 Conectar (QR Code)</span>
                </button>
                <button 
                  v-else
                  type="button"
                  @click.stop="disconnectInstance(inst)"
                  class="text-xs font-bold text-rose-600 hover:text-rose-800 cursor-pointer"
                >
                  Desconectar
                </button>
              </div>
            </div>
          </div>

          <!-- Diagnóstico do Gateway / Docker -->
          <div class="p-3.5 rounded-2xl bg-[#eff2e5] border border-[#130e30]/10 space-y-2 text-xs">
            <div class="flex items-center justify-between">
              <span class="font-bold text-[#130e30] flex items-center gap-1">
                <span>⚡ Gateway Evolution API</span>
              </span>
              <button 
                type="button"
                @click="testGatewayPing"
                :disabled="isPinging"
                class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-white border border-[#130e30]/15 hover:bg-[#ffe228] transition-colors cursor-pointer"
              >
                {{ isPinging ? 'Testando...' : 'Testar Conexão' }}
              </button>
            </div>

            <div class="text-[11px] text-[#5f5c6e] font-mono break-all">
              URL: {{ gatewayInfo.base_url || 'http://localhost:8080' }}
            </div>

            <div 
              v-if="pingResult" 
              class="p-2 rounded-xl text-[10px] leading-tight font-medium"
              :class="pingResult.online ? 'bg-emerald-100 text-emerald-900 border border-emerald-300' : 'bg-rose-100 text-rose-900 border border-rose-300'"
            >
              {{ pingResult.message }}
            </div>

            <!-- Botão para ver comando do Docker -->
            <button 
              type="button"
              @click="showDockerHelp = !showDockerHelp"
              class="text-[10px] text-[#130e30] underline font-bold cursor-pointer block pt-1"
            >
              {{ showDockerHelp ? 'Ocultar comando Docker' : 'Como rodar a Evolution API localmente com Docker?' }}
            </button>

            <div v-if="showDockerHelp" class="p-2.5 rounded-xl bg-[#130e30] text-[#ffe228] font-mono text-[9px] leading-relaxed space-y-1">
              <p class="text-white/80">Rode no terminal do seu Windows (PowerShell):</p>
              <div class="bg-black/50 p-1.5 rounded select-all break-all">
                docker run -d --name evolution-api -p 8080:8080 -e AUTHENTICATION_API_KEY=ca6ece4fca2b05087e76dc38d2a21ee4f30e31636d31c2f4931759599f6c280f atilafreire/evolution-api:v2.1.2
              </div>
            </div>
          </div>
        </div>

        <!-- Coluna Direita: Área do QR Code & Conexão em Tempo Real (7 colunas) -->
        <div class="md:col-span-7 space-y-4">
          <div class="p-6 rounded-3xl bg-white border border-[#130e30]/15 shadow-sm flex flex-col items-center justify-center text-center space-y-4 min-h-[360px]">
            <!-- CASO 1: INSTÂNCIA JÁ CONECTADA -->
            <div v-if="activeInstance?.status === 'connected'" class="space-y-4 py-4 w-full">
              <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-700 mx-auto flex items-center justify-center text-3xl">
                ✓
              </div>

              <div>
                <h4 class="text-base font-serif-brand font-bold text-emerald-800">
                  WhatsApp Conectado com Sucesso!
                </h4>
                <p class="text-xs text-[#5f5c6e] mt-1">
                  A instância <strong>{{ activeInstance.instance_name }}</strong> está operando em tempo real.
                </p>
              </div>

              <!-- Box de Teste de Envio -->
              <div class="p-4 rounded-2xl bg-[#f9fbf2] border border-[#130e30]/10 text-left space-y-2.5 max-w-sm mx-auto">
                <label class="block text-[11px] font-bold text-[#130e30]">
                  Fazer Envio de Teste (Digite seu número com DDD)
                </label>
                <div class="flex gap-2">
                  <input 
                    v-model="testPhone"
                    type="text"
                    placeholder="Ex: 11999998888"
                    class="flex-1 h-9 px-3 text-xs rounded-xl bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
                  />
                  <button 
                    type="button"
                    @click="sendTestMessage"
                    :disabled="isSendingTest || !testPhone.trim()"
                    class="h-9 px-4 rounded-xl bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] font-bold text-xs cursor-pointer transition-all disabled:opacity-50"
                  >
                    {{ isSendingTest ? 'Enviando...' : 'Testar' }}
                  </button>
                </div>

                <div 
                  v-if="testResult" 
                  class="p-2 rounded-xl text-[10px] leading-tight"
                  :class="testResult.success ? 'bg-emerald-100 text-emerald-900 border border-emerald-300' : 'bg-rose-100 text-rose-900 border border-rose-300'"
                >
                  {{ testResult.message }}
                </div>
              </div>

              <button 
                type="button"
                @click="disconnectInstance(activeInstance)"
                class="text-xs font-bold text-rose-600 hover:text-rose-800 underline cursor-pointer"
              >
                Desconectar este aparelho
              </button>
            </div>

            <!-- CASO 2: EXIBIÇÃO DO QR CODE ATIVO -->
            <div v-else-if="qrCodeData?.base64" class="space-y-4 py-2 w-full flex flex-col items-center">
              <div>
                <h4 class="text-sm font-serif-brand font-bold text-[#130e30]">
                  Escaneie o QR Code com seu WhatsApp
                </h4>
                <p class="text-xs text-[#5f5c6e] mt-0.5">
                  Aguardando leitura do aparelho celular...
                </p>
              </div>

              <!-- Container da Imagem do QR Code -->
              <div class="p-3 bg-white rounded-3xl border-2 border-[#130e30] shadow-md relative group">
                <img 
                  :src="qrCodeData.base64" 
                  alt="QR Code WhatsApp" 
                  class="w-56 h-56 object-contain rounded-2xl"
                />

                <div v-if="isPolling" class="absolute -top-2 -right-2 flex h-5 w-5">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-5 w-5 bg-emerald-600 text-white text-[10px] items-center justify-center font-bold">✓</span>
                </div>
              </div>

              <!-- Instruções de Escaneamento -->
              <div class="text-left bg-[#f9fbf2] p-3.5 rounded-2xl border border-[#130e30]/10 text-[11px] space-y-1 max-w-sm">
                <p class="font-bold text-[#130e30]">Passo a passo no seu celular:</p>
                <ol class="list-decimal list-inside space-y-0.5 text-[#5f5c6e]">
                  <li>Abra o WhatsApp no seu smartphone</li>
                  <li>Vá em <strong>Aparelhos Conectados</strong></li>
                  <li>Toque em <strong>Conectar um Aparelho</strong></li>
                  <li>Aponte a câmera para este QR Code</li>
                </ol>
              </div>

              <div class="flex items-center gap-2">
                <button 
                  type="button"
                  @click="fetchQrCode(activeInstance)"
                  :disabled="isLoadingQr"
                  class="h-8 px-4 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-[#130e30] text-xs font-bold cursor-pointer transition-all"
                >
                  🔄 Atualizar QR Code
                </button>
              </div>
            </div>

            <!-- CASO 3: ESTADO INICIAL / AGUARDANDO SOLICITAÇÃO -->
            <div v-else class="space-y-4 py-8">
              <div class="w-16 h-16 rounded-full bg-[#eff2e5] text-[#130e30] mx-auto flex items-center justify-center text-3xl shadow-2xs">
                📷
              </div>

              <div>
                <h4 class="text-sm font-serif-brand font-bold text-[#130e30]">
                  Nenhum QR Code Ativo no Momento
                </h4>
                <p class="text-xs text-[#5f5c6e] mt-1 max-w-xs mx-auto">
                  Clique no botão abaixo para gerar o QR Code oficial da sua instância do WhatsApp.
                </p>
              </div>

              <button 
                type="button"
                @click="fetchQrCode(activeInstance)"
                :disabled="isLoadingQr || !activeInstance"
                class="h-11 px-6 rounded-full bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] font-bold text-xs flex items-center gap-2 mx-auto cursor-pointer transition-all active:scale-95 disabled:opacity-50 shadow-sm"
              >
                <span v-if="!isLoadingQr">📷 Gerar QR Code Agora</span>
                <span v-else class="animate-spin text-xs">⏳ Gerando...</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
