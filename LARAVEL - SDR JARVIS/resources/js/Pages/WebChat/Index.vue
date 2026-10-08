<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
  leads: Array,
  companies: Array,
  instances: Array,
})

const activeLeadId = ref(null)
const searchQuery = ref('')
const messageText = ref('')
const isLoadingMessages = ref(false)
const selectedCompanyId = ref('')
const sendingMessage = ref(false)

const chatMessages = ref([])

const activeLead = computed(() => {
  return props.leads.find(l => l.id === activeLeadId.value)
})

const connectedInstance = computed(() => {
  return (props.instances || []).find(i => i.status === 'connected') || (props.instances || [])[0]
})

const filteredLeads = computed(() => {
  if (!searchQuery.value) return props.leads
  const q = searchQuery.value.toLowerCase()
  return props.leads.filter(l => 
    l.name?.toLowerCase().includes(q) || 
    l.phone?.includes(q) ||
    l.company?.name?.toLowerCase().includes(q)
  )
})

const selectLead = (lead) => {
  activeLeadId.value = lead.id
  loadMessages(lead.id)
}

const loadMessages = async (leadId) => {
  isLoadingMessages.value = true
  try {
    const res = await axios.get(`/leads/${leadId}/messages`)
    chatMessages.value = res.data.messages || []
    scrollToBottom()
  } catch (error) {
    console.error("Failed to load messages", error)
  } finally {
    isLoadingMessages.value = false
  }
}

const isSyncingHistory = ref(false)
const syncChatHistory = async () => {
  if (!activeLeadId.value || isSyncingHistory.value) return
  isSyncingHistory.value = true
  try {
    const res = await axios.post(`/webchat/leads/${activeLeadId.value}/sync-history`)
    if (res.data?.success && res.data?.messages) {
      chatMessages.value = res.data.messages
      scrollToBottom()
    }
  } catch (error) {
    console.error("Falha ao sincronizar histórico", error)
    alert("Falha ao buscar histórico do WhatsApp. Verifique se a instância está conectada.")
  } finally {
    isSyncingHistory.value = false
  }
}

const sendMessage = async () => {
  if (!messageText.value.trim() || !activeLeadId.value || sendingMessage.value) return

  sendingMessage.value = true
  const currentText = messageText.value
  try {
    const payload = { 
      text: currentText,
      whatsapp_instance_id: connectedInstance.value?.id 
    }
    const res = await axios.post(`/leads/${activeLeadId.value}/send-message`, payload)
    
    // Adiciona a mensagem à conversa
    if (res.data?.success && res.data?.message) {
      chatMessages.value.push(res.data.message)
    } else {
      // Recarrega mensagens
      await loadMessages(activeLeadId.value)
    }
    messageText.value = ''
    scrollToBottom()
  } catch (error) {
    console.error("Failed to send message", error)
    const errDetail = error.response?.data?.message || error.response?.data?.error || error.message || "Falha na comunicação com o servidor"
    alert("Erro ao enviar mensagem: " + errDetail)
  } finally {
    sendingMessage.value = false
  }
}

const linkCompany = async () => {
  if (!selectedCompanyId.value || !activeLeadId.value) return
  
  try {
    await router.post(`/webchat/leads/${activeLeadId.value}/link-company`, {
      company_id: selectedCompanyId.value
    }, {
      preserveScroll: true,
      onSuccess: () => {
        selectedCompanyId.value = ''
      }
    })
  } catch (error) {
    console.error(error)
  }
}

const showNewCompanyModal = ref(false)
const newCompanyForm = ref({
  name: '',
  segment: '',
  document: '',
})
const isCreatingCompany = ref(false)

const openNewCompanyModal = () => {
  newCompanyForm.value = {
    name: activeLead.value?.company_name || '',
    segment: '',
    document: '',
  }
  showNewCompanyModal.value = true
}

const submitCreateCompany = async () => {
  if (!newCompanyForm.value.name.trim() || !activeLeadId.value || isCreatingCompany.value) return
  isCreatingCompany.value = true
  try {
    await router.post(`/webchat/leads/${activeLeadId.value}/create-company`, newCompanyForm.value, {
      preserveScroll: true,
      onSuccess: () => {
        showNewCompanyModal.value = false
        newCompanyForm.value = { name: '', segment: '', document: '' }
      }
    })
  } catch (error) {
    console.error(error)
  } finally {
    isCreatingCompany.value = false
  }
}

const chatContainer = ref(null)
const scrollToBottom = () => {
  setTimeout(() => {
    if (chatContainer.value) {
      chatContainer.value.scrollTop = chatContainer.value.scrollHeight
    }
  }, 100)
}

// Auto-refresh poll to simulate real-time (a cada 3s)
let pollInterval = null
onMounted(() => {
  pollInterval = setInterval(() => {
    if (activeLeadId.value) {
      loadMessages(activeLeadId.value)
    }
    // Para atualizar a lista da barra lateral, podemos fazer um partial reload
    router.reload({ only: ['leads'], preserveState: true, preserveScroll: true })
  }, 3000)
})

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval)
})

const formatTime = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
  <Head title="WhatsApp Web" />

  <div class="h-screen w-screen flex flex-col bg-gray-100 dark:bg-gray-900">
    <!-- Header Global (Back to CRM) -->
    <div class="h-14 bg-[#130e30] flex items-center px-4 shrink-0">
      <a href="/" class="text-white text-sm flex items-center gap-2 hover:text-[#ffe228] transition-colors font-medium">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
        Voltar ao CRM (SDR JARVIS)
      </a>
    </div>

    <div class="flex-1 flex overflow-hidden border-t border-gray-200 dark:border-gray-800">

      
      <!-- Sidebar de Contatos -->
      <div class="w-1/3 min-w-[320px] max-w-[400px] bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col">
        <!-- Header Sidebar -->
        <div class="p-4 bg-gray-50 dark:bg-gray-800/80 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.423-14.416c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm.029 18.88c-1.161 0-2.305-.292-3.318-.844l-3.677.964.984-3.595c-.607-1.052-.927-2.246-.926-3.468.005-3.825 3.116-6.937 6.94-6.937 3.828 0 6.94 3.111 6.94 6.938-.003 3.825-3.112 6.942-6.943 6.942z"/></svg>
            WebChat
          </h2>
        </div>

        <!-- Barra de Pesquisa -->
        <div class="p-3 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
              <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
            </span>
            <input v-model="searchQuery" type="text" class="w-full pl-10 pr-4 py-2 border-none bg-gray-100 dark:bg-gray-700/50 rounded-lg text-sm text-gray-700 dark:text-gray-200 focus:ring-0" placeholder="Pesquisar contatos..." />
          </div>
        </div>

        <!-- Lista de Contatos -->
        <div class="flex-1 overflow-y-auto">
          <ul class="divide-y divide-gray-100 dark:divide-gray-700/50">
            <li v-for="lead in filteredLeads" :key="lead.id" 
                @click="selectLead(lead)"
                class="p-3 flex items-center gap-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
                :class="{'bg-green-50 dark:bg-green-900/10': activeLeadId === lead.id}">
              <div class="relative w-12 h-12 flex-shrink-0">
                <div class="w-full h-full rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-gray-500 dark:text-gray-300 font-bold text-lg">
                  {{ lead.name.charAt(0).toUpperCase() }}
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-baseline justify-between mb-1">
                  <h3 class="text-md font-semibold text-gray-900 dark:text-white truncate">{{ lead.name }}</h3>
                  <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ formatTime(lead.messages?.[0]?.created_at || lead.created_at) }}
                  </span>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                  <span v-if="lead.company" class="text-xs font-semibold px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 mr-1">{{ lead.company.name }}</span>
                  <span v-else class="text-xs font-semibold px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 mr-1">Sem Empresa</span>
                  {{ lead.messages?.[0]?.body || lead.phone }}
                </p>
              </div>
            </li>
          </ul>
        </div>
      </div>

      <!-- Área de Chat Principal -->
      <div class="flex-1 flex flex-col bg-[#efeae2] dark:bg-[#0b141a] relative">
        <template v-if="activeLead">
          <!-- Header do Chat -->
          <div class="h-16 px-4 bg-gray-50 dark:bg-[#202c33] border-b border-gray-200 dark:border-gray-700 flex items-center justify-between shadow-sm z-10">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-gray-500 dark:text-gray-300 font-bold text-lg">
                {{ activeLead.name.charAt(0).toUpperCase() }}
              </div>
              <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">{{ activeLead.name }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ activeLead.phone }}</p>
              </div>
            </div>
            
            <div class="flex items-center gap-3">
              <!-- Botão Sincronizar Histórico do WhatsApp -->
              <button 
                @click="syncChatHistory" 
                :disabled="isSyncingHistory" 
                class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-lg flex items-center gap-1.5 transition-colors disabled:opacity-50 shadow-sm border border-gray-200 dark:border-gray-600"
                title="Puxar mensagens antigas direto do WhatsApp"
              >
                <svg class="w-3.5 h-3.5 text-green-600 dark:text-green-400" :class="{'animate-spin': isSyncingHistory}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>{{ isSyncingHistory ? 'Sincronizando...' : 'Puxar Histórico Zap' }}</span>
              </button>

              <!-- Vínculo de Empresa -->
              <div v-if="activeLead.company" class="flex items-center gap-2">
                <span class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-lg text-sm font-semibold border border-indigo-100 dark:border-indigo-800/50 flex items-center gap-1.5 shadow-sm">
                  🏢 {{ activeLead.company.name }}
                </span>
                <button @click="openNewCompanyModal" title="Criar outra ou alterar" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
              </div>
              <div v-else class="flex items-center gap-2">
                <select v-model="selectedCompanyId" class="text-sm border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-green-500 dark:bg-gray-700 dark:text-white py-1.5 max-w-[160px]">
                  <option value="">Vincular a Empresa</option>
                  <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <button @click="linkCompany" :disabled="!selectedCompanyId" class="px-3 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm font-medium rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors disabled:opacity-50">
                  Vincular
                </button>
                <button @click="openNewCompanyModal" class="px-3 py-1.5 bg-[#ffe228] text-[#130e30] font-bold text-sm rounded-lg hover:bg-yellow-400 transition-colors shadow-sm flex items-center gap-1">
                  <span>+</span> Nova Empresa
                </button>
              </div>
            </div>
          </div>

          <!-- Mensagens -->
          <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 md:p-6 pb-20 scroll-smooth">
            <div v-if="isLoadingMessages" class="flex justify-center py-4">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-500"></div>
            </div>
            
            <div class="flex flex-col gap-2">
              <div v-for="msg in chatMessages" :key="msg.id" 
                   class="max-w-[80%] lg:max-w-[65%] rounded-lg px-4 py-2 relative shadow-sm"
                   :class="msg.direction === 'outbound' ? 'self-end bg-[#d9fdd3] dark:bg-[#005c4b] text-gray-900 dark:text-gray-100' : 'self-start bg-white dark:bg-[#202c33] text-gray-900 dark:text-gray-100'">
                <p class="text-sm whitespace-pre-wrap break-words leading-relaxed">{{ msg.body }}</p>
                <div class="flex items-center justify-end gap-1 mt-1">
                  <span class="text-[10px] text-gray-500 dark:text-gray-400">{{ formatTime(msg.created_at) }}</span>
                  <svg v-if="msg.direction === 'outbound'" class="w-3 h-3" :class="msg.status === 'read' ? 'text-blue-500' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7M5 13l4 4L19 7" />
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <!-- Input de Mensagem -->
          <div class="h-[72px] bg-gray-50 dark:bg-[#202c33] px-4 flex items-center gap-3 w-full border-t border-gray-200 dark:border-gray-700 absolute bottom-0 left-0">
            <input v-model="messageText" @keyup.enter="sendMessage" type="text" class="flex-1 rounded-full border-none bg-white dark:bg-[#2a3942] px-5 py-3 text-sm focus:ring-0 text-gray-800 dark:text-gray-200 shadow-sm" placeholder="Digite uma mensagem..." :disabled="sendingMessage" />
            <button @click="sendMessage" :disabled="!messageText.trim() || sendingMessage" class="p-3 bg-green-500 hover:bg-green-600 text-white rounded-full transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
              <svg class="w-5 h-5 translate-x-px translate-y-px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
              </svg>
            </button>
          </div>
        </template>

        <!-- Placeholder Tela Vazia -->
        <div v-else class="flex-1 flex flex-col items-center justify-center text-center p-8">
          <div class="w-64 h-64 opacity-50 mb-6 bg-[url('https://cdn-icons-png.flaticon.com/512/124/124034.png')] bg-contain bg-no-repeat bg-center mix-blend-luminosity"></div>
          <h2 class="text-2xl font-light text-gray-600 dark:text-gray-300 mb-2">WebChat JARVIS</h2>
          <p class="text-gray-500 dark:text-gray-400 max-w-md">Envie e receba mensagens rapidamente sem sair do CRM. Selecione um contato na barra lateral para começar a conversar.</p>
        </div>
      </div>
    </div>

    <!-- Modal Nova Empresa -->
    <div v-if="showNewCompanyModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fade-in">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-6 border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <span>🏢</span> Criar Nova Empresa
          </h3>
          <button @click="showNewCompanyModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <form @submit.prevent="submitCreateCompany" class="mt-4 space-y-4">
          <div>
            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase mb-1">Nome da Empresa *</label>
            <input v-model="newCompanyForm.name" required type="text" placeholder="Ex: Acerto Cobranças" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-gray-50 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-yellow-400 focus:outline-none" />
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase mb-1">Segmento / Ramo</label>
            <input v-model="newCompanyForm.segment" type="text" placeholder="Ex: Tecnologia, Finanças..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-gray-50 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-yellow-400 focus:outline-none" />
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase mb-1">CNPJ / Documento</label>
            <input v-model="newCompanyForm.document" type="text" placeholder="Ex: 00.000.000/0001-00" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-gray-50 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-yellow-400 focus:outline-none" />
          </div>

          <p class="text-xs text-gray-500 dark:text-gray-400">
            O contato <b>{{ activeLead?.name }}</b> será automaticamente associado a esta nova empresa no CRM.
          </p>

          <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
            <button type="button" @click="showNewCompanyModal = false" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
              Cancelar
            </button>
            <button type="submit" :disabled="isCreatingCompany || !newCompanyForm.name.trim()" class="px-5 py-2 text-sm font-bold bg-[#ffe228] text-[#130e30] hover:bg-yellow-400 rounded-lg shadow-sm transition disabled:opacity-50">
              {{ isCreatingCompany ? 'Criando...' : 'Criar e Vincular' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
