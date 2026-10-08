<template>
  <div class="flex h-screen bg-[#f9fbf2] text-[#130e30] font-sans selection:bg-[#ffe228] selection:text-[#130e30] relative overflow-hidden">
    <!-- ================= SIDEBAR LATERAL (DESIGN.md Soft Meadow style) ================= -->
    <aside class="w-64 bg-[#eff2e5] border-r border-[#130e30]/10 flex flex-col justify-between shrink-0 z-30 h-full p-4 select-none">
      <div class="space-y-4 flex-1 overflow-y-auto pr-1">
        <!-- Logo Lockup Oficial (SJ Monogram) -->
        <div class="flex items-center gap-3 px-1 py-1">
          <div class="w-10 h-10 rounded-xl bg-white border border-[#130e30]/10 flex items-center justify-center p-1 shadow-2xs overflow-hidden shrink-0">
            <img src="/img/logo.jpeg" alt="SDR JARVIS" class="w-full h-full object-contain" />
          </div>
          <div class="min-w-0">
            <div class="flex items-center gap-1.5">
              <h1 class="text-base font-serif-brand font-bold text-[#130e30] tracking-tight truncate">SDR JARVIS</h1>
              <span class="text-[9px] font-bold tracking-wider uppercase px-2 py-0.5 rounded-full bg-white text-[#130e30] border border-[#130e30]/15 shrink-0">
                {{ isSuperAdmin ? 'ADMIN' : 'B2B' }}
              </span>
            </div>
            <p class="text-[11px] text-[#5f5c6e] truncate">{{ activeTenant?.name || pipeline?.name || 'CRM Ativo' }}</p>
          </div>
        </div>

        <!-- Botão Primário de Ação: Nova Conta (Hi-Yellow Pill) -->
        <button 
          @click="openNewLeadModal" 
          class="w-full h-11 px-4 rounded-full bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] text-xs font-bold flex items-center justify-center gap-2 shadow-sm transition-all cursor-pointer active:scale-[0.98]"
        >
          <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
          </svg>
          <span>Nova Conta</span>
        </button>

        <!-- Menu de Navegação Principal -->
        <div class="space-y-1">
          <p class="text-[10px] font-bold text-[#5f5c6e] uppercase tracking-wider px-3 pb-1">Navegação</p>

          <!-- 1. Funil Kanban -->
          <button 
            @click="currentView = 'kanban'" 
            class="w-full h-10 px-3.5 rounded-2xl text-xs font-semibold flex items-center justify-between transition-all cursor-pointer"
            :class="currentView === 'kanban' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#130e30] hover:bg-white/70'"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">📋</span>
              <span>Funil Kanban</span>
            </div>
            <span 
              class="px-2 py-0.5 rounded-full text-[10px] font-bold"
              :class="currentView === 'kanban' ? 'bg-white/20 text-white' : 'bg-white border border-[#130e30]/10 text-[#5f5c6e]'"
            >
              {{ (companies || []).length }}
            </span>
          </button>

          <!-- 2. Tarefas & Lembretes (NOVA VISÃO GLOBAL) -->
          <button 
            @click="currentView = 'tasks'" 
            class="w-full h-10 px-3.5 rounded-2xl text-xs font-semibold flex items-center justify-between transition-all cursor-pointer"
            :class="currentView === 'tasks' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#130e30] hover:bg-white/70'"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">📌</span>
              <span>Tarefas & Lembretes</span>
            </div>
            <span 
              v-if="pendingTasksCount > 0"
              class="px-2 py-0.5 rounded-full text-[10px] font-bold"
              :class="overdueTasksCount > 0 
                ? (currentView === 'tasks' ? 'bg-rose-500 text-white' : 'bg-rose-100 text-rose-800 border border-rose-200')
                : (currentView === 'tasks' ? 'bg-[#ffe228] text-[#130e30]' : 'bg-[#ffe228] text-[#130e30]')"
            >
              {{ pendingTasksCount }}
            </span>
          </button>

          <!-- 3. Cadências B2B (Sequências Automatizadas) -->
          <button 
            @click="currentView = 'cadences'" 
            class="w-full h-10 px-3.5 rounded-2xl text-xs font-semibold flex items-center justify-between transition-all cursor-pointer"
            :class="currentView === 'cadences' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#130e30] hover:bg-white/70'"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">⚡</span>
              <span>Cadências B2B</span>
            </div>
            <span 
              class="px-2 py-0.5 rounded-full text-[10px] font-bold"
              :class="currentView === 'cadences' ? 'bg-[#ffe228] text-[#130e30]' : 'bg-white border border-[#130e30]/10 text-[#5f5c6e]'"
            >
              {{ (cadences || []).length }}
            </span>
          </button>

          <!-- 3.5. WebChat (WhatsApp Web) -->
          <a 
            href="/webchat" 
            class="w-full h-10 px-3.5 rounded-2xl text-xs font-semibold flex items-center justify-between transition-all cursor-pointer text-[#130e30] hover:bg-white/70"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">💬</span>
              <span>WebChat WhatsApp</span>
            </div>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#25D366]/20 text-[#075E54]">
              Novo
            </span>
          </a>

          <!-- 4. Scripts & Modelos (Templates de Abordagem) -->
          <button 
            @click="currentView = 'templates'" 
            class="w-full h-10 px-3.5 rounded-2xl text-xs font-semibold flex items-center justify-between transition-all cursor-pointer"
            :class="currentView === 'templates' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#130e30] hover:bg-white/70'"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">📝</span>
              <span>Scripts & Modelos</span>
            </div>
            <span 
              class="px-2 py-0.5 rounded-full text-[10px] font-bold"
              :class="currentView === 'templates' ? 'bg-[#ffe228] text-[#130e30]' : 'bg-white border border-[#130e30]/10 text-[#5f5c6e]'"
            >
              {{ (messageTemplates || []).length }}
            </span>
          </button>

          <!-- 5. Produtividade SDR (Dashboard) -->
          <button 
            @click="currentView = 'dashboard'" 
            class="w-full h-10 px-3.5 rounded-2xl text-xs font-semibold flex items-center justify-between transition-all cursor-pointer"
            :class="currentView === 'dashboard' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#130e30] hover:bg-white/70'"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">📊</span>
              <span>Produtividade SDR</span>
            </div>
            <span 
              class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded-full"
              :class="currentView === 'dashboard' ? 'bg-white/20 text-white' : 'bg-white text-[#130e30] border border-[#130e30]/10'"
            >
              Ao Vivo
            </span>
          </button>

          <!-- 6. Laboratório & Treinamento da IA (Arena de Estresse e Instruções) -->
          <button 
            @click="currentView = 'ai_lab'" 
            class="w-full h-10 px-3.5 rounded-2xl text-xs font-semibold flex items-center justify-between transition-all cursor-pointer"
            :class="currentView === 'ai_lab' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#130e30] hover:bg-white/70'"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">🧠</span>
              <span>Treinamento da IA</span>
            </div>
            <span 
              class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded-full"
              :class="currentView === 'ai_lab' ? 'bg-[#ffe228] text-[#130e30]' : 'bg-[#ffe228] text-[#130e30]'"
            >
              Lab
            </span>
          </button>

          <!-- 6. Perfis de CRM & IA (Super Admin) -->
          <button 
            v-if="canManageTenants"
            @click="currentView = 'admin'; adminTab = 'tenants'" 
            class="w-full h-10 px-3.5 rounded-2xl text-xs font-semibold flex items-center justify-between transition-all cursor-pointer"
            :class="currentView === 'admin' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#130e30] hover:bg-white/70'"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">🏢</span>
              <span>Perfis de CRM & IA</span>
            </div>
            <span 
              class="px-2 py-0.5 rounded-full text-[10px] font-bold"
              :class="currentView === 'admin' ? 'bg-[#ffe228] text-[#130e30]' : 'bg-[#ffe228] text-[#130e30]'"
            >
              {{ (allTenants || []).length }}
            </span>
          </button>

          <!-- 7. Gestão de Equipe (Gestor Comercial da Base) -->
          <button 
            v-else-if="canManageTeam"
            @click="currentView = 'admin'; adminTab = 'users'" 
            class="w-full h-10 px-3.5 rounded-2xl text-xs font-semibold flex items-center justify-between transition-all cursor-pointer"
            :class="currentView === 'admin' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#130e30] hover:bg-white/70'"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">👥</span>
              <span>Minha Equipe</span>
            </div>
            <span 
              class="px-2 py-0.5 rounded-full text-[10px] font-bold"
              :class="currentView === 'admin' ? 'bg-[#ffe228] text-[#130e30]' : 'bg-[#ffe228] text-[#130e30]'"
            >
              {{ (allUsers || []).length }}
            </span>
          </button>
        </div>

        <!-- Ações & Ferramentas -->
        <div class="space-y-1 pt-2 border-t border-[#130e30]/10">
          <p class="text-[10px] font-bold text-[#5f5c6e] uppercase tracking-wider px-3 pb-1">Operações</p>

          <!-- Importar CSV -->
          <button 
            @click="openImportModal" 
            class="w-full h-9 px-3.5 rounded-2xl text-xs font-medium text-[#130e30] hover:bg-white/70 flex items-center gap-2.5 transition-all cursor-pointer"
          >
            <svg class="w-4 h-4 text-[#5f5c6e]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <span>Importar CSV</span>
          </button>

          <!-- Toggle Métricas Ribbon -->
          <button 
            @click="showMetrics = !showMetrics" 
            class="w-full h-9 px-3.5 rounded-2xl text-xs font-medium text-[#130e30] hover:bg-white/70 flex items-center justify-between transition-all cursor-pointer"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">📈</span>
              <span>Barra de Métricas</span>
            </div>
            <span class="text-[10px] text-[#5f5c6e]">{{ showMetrics ? 'Visível' : 'Oculto' }}</span>
          </button>

          <!-- Conectar WhatsApp Real (QR Code) -->
          <button 
            type="button"
            @click="openWhatsappModal()" 
            class="w-full h-9 px-3.5 rounded-2xl text-xs font-medium text-[#130e30] hover:bg-white/70 flex items-center justify-between transition-all cursor-pointer"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">📱</span>
              <span>WhatsApp QR Code</span>
            </div>
            <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
              Conectar
            </span>
          </button>
        </div>

        <!-- Conexões & Canais Omnichannel -->
        <div class="space-y-2 pt-2 border-t border-[#130e30]/10">
          <p class="text-[10px] font-bold text-[#5f5c6e] uppercase tracking-wider px-3">Canais de Contato</p>

          <!-- WhatsApp Card (Card inteiro clicável para abrir o QR Code / Gerenciar Instância) -->
          <div 
            v-if="activeInstance" 
            @click="openWhatsappModal(activeInstance)"
            role="button"
            tabindex="0"
            title="Clique para gerenciar a conexão do WhatsApp e escanear o QR Code"
            class="p-3 rounded-2xl bg-white border border-[#130e30]/10 space-y-2 shadow-2xs hover:shadow-md hover:border-emerald-500/50 hover:bg-emerald-50/20 transition-all cursor-pointer select-none group"
          >
            <div class="flex items-center justify-between text-xs">
              <div class="flex items-center gap-1.5 font-bold text-[#130e30] group-hover:text-emerald-900">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#59e25d] opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-[#59e25d]"></span>
                </span>
                <span class="truncate max-w-[100px]">{{ activeInstance.instance_name }}</span>
              </div>
              <div class="flex items-center gap-1.5">
                <span class="text-[10px] text-[#5f5c6e] font-mono">{{ activeInstance.sent_today }}/{{ activeInstance.daily_limit }}</span>
                <span class="text-[11px] text-[#5f5c6e] group-hover:text-[#130e30] group-hover:scale-110 transition-transform">⚙️</span>
              </div>
            </div>
            
            <div class="w-full bg-[#eff2e5] rounded-full h-1.5 overflow-hidden">
              <div class="bg-[#59e25d] h-1.5 rounded-full" :style="{ width: Math.min(100, Math.round((activeInstance.sent_today / (activeInstance.daily_limit || 1)) * 100)) + '%' }"></div>
            </div>

            <div class="pt-1.5 border-t border-[#130e30]/5 flex items-center justify-between text-[11px] font-semibold text-emerald-800">
              <span class="flex items-center gap-1">📱 Conectar / QR Code</span>
              <button 
                type="button" 
                @click.stop="openWhatsappModal(activeInstance)"
                class="text-[9px] bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full font-bold group-hover:bg-emerald-200 transition-colors cursor-pointer"
              >
                Abrir
              </button>
            </div>
          </div>
          <div v-else class="p-2 rounded-2xl bg-white border border-[#130e30]/10 shadow-2xs">
            <button 
              type="button"
              @click="openWhatsappModal()"
              class="w-full py-1.5 px-2 rounded-xl text-[11px] font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 flex items-center justify-center gap-1.5 transition-colors cursor-pointer border border-emerald-200"
            >
              <span>📱 Conectar WhatsApp</span>
            </button>
          </div>

          <!-- Gmail Card / Conectar -->
          <div class="p-2.5 rounded-2xl bg-white border border-[#130e30]/10 shadow-2xs">
            <div v-if="activeEmailAccount" class="flex items-center justify-between text-xs">
              <div class="flex items-center gap-2 min-w-0">
                <svg class="w-3.5 h-3.5 shrink-0 text-rose-500" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M24 5.457v13.909c0 .904-.732 1.636-1.636 1.636h-3.819V11.73L12 16.64l-6.545-4.91v9.273H1.636A1.636 1.636 0 0 1 0 19.366V5.457c0-2.023 2.309-3.178 3.927-1.964L5.455 4.64 12 9.548l6.545-4.91 1.528-1.145C21.69 2.28 24 3.434 24 5.457z"/>
                </svg>
                <span class="font-medium text-[#130e30] truncate text-[11px]">{{ activeEmailAccount.email }}</span>
              </div>
              <button @click="showGmailModal = true" class="text-[10px] text-[#5f5c6e] hover:text-[#130e30] underline cursor-pointer shrink-0">⚙️</button>
            </div>
            <button 
              v-else 
              @click="showGmailModal = true"
              class="w-full py-1 px-2 rounded-xl text-[11px] font-semibold text-[#130e30] flex items-center justify-center gap-1.5 hover:bg-[#eff2e5] transition-colors cursor-pointer"
            >
              <svg class="w-3 h-3 text-rose-500" viewBox="0 0 24 24" fill="currentColor">
                <path d="M24 5.457v13.909c0 .904-.732 1.636-1.636 1.636h-3.819V11.73L12 16.64l-6.545-4.91v9.273H1.636A1.636 1.636 0 0 1 0 19.366V5.457c0-2.023 2.309-3.178 3.927-1.964L5.455 4.64 12 9.548l6.545-4.91 1.528-1.145C21.69 2.28 24 3.434 24 5.457z"/>
              </svg>
              <span>Conectar Gmail</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Rodapé da Sidebar: Perfil do Usuário e Botão de Logout -->
      <div class="pt-3 border-t border-[#130e30]/10 flex flex-col gap-2 shrink-0">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-8 h-8 rounded-full bg-[#130e30] text-[#ffe228] font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
              {{ (currentUser?.name || 'SDR').charAt(0) }}
            </div>
            <div class="min-w-0">
              <div class="flex items-center gap-1.5">
                <p class="text-xs font-bold text-[#130e30] truncate">{{ currentUser?.name || 'SDR Pro' }}</p>
              </div>
              <p class="text-[10px] text-[#5f5c6e] truncate">{{ currentUser?.email || 'sdr@empresa.com' }}</p>
            </div>
          </div>

          <button 
            @click="handleLogout" 
            title="Encerrar sessão" 
            class="w-7 h-7 rounded-full bg-white hover:bg-rose-50 text-[#5f5c6e] hover:text-rose-600 border border-[#130e30]/10 flex items-center justify-center transition-all cursor-pointer shadow-2xs"
          >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </button>
        </div>

        <!-- Badge de Nível de Acesso -->
        <div class="flex items-center justify-between px-2.5 py-1 rounded-xl bg-white/70 border border-[#130e30]/10 text-[10px]">
          <span class="font-bold text-[#5f5c6e]">Perfil:</span>
          <span 
            class="font-bold px-1.5 py-0.2 rounded-full"
            :class="isSuperAdmin ? 'bg-amber-100 text-amber-900 border border-amber-300' : (isManager ? 'bg-purple-100 text-purple-900 border border-purple-200' : 'bg-emerald-100 text-emerald-900 border border-emerald-200')"
          >
            {{ isSuperAdmin ? '👑 Super Admin' : (isManager ? '👔 Gestor Comercial' : '💼 SDR Operador') }}
          </span>
        </div>
      </div>
    </aside>

    <!-- ================= ÁREA DE CONTEÚDO PRINCIPAL (DIREITA) ================= -->
    <div class="flex-1 flex flex-col h-full overflow-hidden min-w-0">
      <!-- Top Header Limpo e Espaçoso -->
      <header class="h-16 border-b border-[#130e30]/10 bg-[#eff2e5]/60 backdrop-blur-xs px-6 flex items-center justify-between z-20 shrink-0">
        <!-- Título da Visão Atual -->
        <div>
          <h2 class="text-base font-serif-brand font-bold text-[#130e30]">
            {{ currentView === 'kanban' ? 'Funil de Contas (Kanban)' : (currentView === 'tasks' ? 'Painel de Tarefas & Lembretes' : (currentView === 'cadences' ? 'Cadências & Sequências de Contato B2B' : (currentView === 'templates' ? 'Biblioteca de Scripts & Modelos de Mensagem' : (currentView === 'admin' ? 'Painel de Gestão Multi-Tenant & Acessos' : 'Dashboard de Produtividade SDR')))) }}
          </h2>
          <p class="text-[11px] text-[#5f5c6e]">
            {{ currentView === 'kanban' ? `${(companies || []).length} contas no pipeline de ${activeTenant?.name || 'CRM Ativo'}` : (currentView === 'tasks' ? `${pendingTasksCount} tarefas pendentes de acompanhamento` : (currentView === 'cadences' ? `${activeEnrollmentsTotal} contas em réguas de contato ativas` : (currentView === 'templates' ? `${(messageTemplates || []).length} scripts para WhatsApp e E-mail salvos` : (currentView === 'admin' ? `Gerencie múltiplos perfis de CRM, bases, configurações de IA e credenciais de acesso` : 'Desempenho e métricas de prospecção')))) }}
          </p>
        </div>

        <div class="flex items-center gap-3">
          <!-- Tenant Switcher Dropdown (Visível para Super Admin e Admin) -->
          <div v-if="canManageTenants && (allTenants || []).length > 0" class="relative">
            <button 
              @click="showTenantDropdown = !showTenantDropdown"
              class="h-9 px-3.5 rounded-full bg-white hover:bg-[#f9fbf2] border border-[#130e30]/20 text-[#130e30] text-xs font-bold flex items-center gap-2 shadow-2xs cursor-pointer transition-all active:scale-95"
              title="Alternar entre Perfis de CRM"
            >
              <span>🏢</span>
              <span class="max-w-[130px] truncate">{{ activeTenant?.name || 'Alternar CRM' }}</span>
              <svg class="w-3.5 h-3.5 text-[#5f5c6e] transition-transform" :class="{ 'rotate-180': showTenantDropdown }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- Dropdown Flutuante -->
            <div 
              v-if="showTenantDropdown"
              class="absolute right-0 sm:left-0 mt-1.5 w-64 bg-white rounded-2xl border border-[#130e30]/15 shadow-xl p-2 z-50 animate-in fade-in zoom-in-95 duration-100"
            >
              <div class="px-2 py-1.5 text-[10px] font-bold uppercase tracking-wider text-[#5f5c6e] border-b border-[#130e30]/10 mb-1 flex items-center justify-between">
                <span>Perfis de CRM & IA</span>
                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded-full bg-[#eff2e5] text-[#130e30]">{{ allTenants.length }}</span>
              </div>
              <div class="space-y-1 max-h-56 overflow-y-auto">
                <button 
                  v-for="t in allTenants" 
                  :key="t.id"
                  @click="switchActiveTenant(t.id)"
                  class="w-full p-2 rounded-xl text-left text-xs transition-colors flex items-center justify-between cursor-pointer"
                  :class="t.id === activeTenant?.id ? 'bg-[#130e30] text-white font-bold' : 'hover:bg-[#f9fbf2] text-[#130e30]'"
                >
                  <div class="min-w-0 pr-2">
                    <p class="truncate font-semibold">{{ t.name }}</p>
                    <p class="text-[10px] opacity-70 truncate">{{ t.companies_count || 0 }} contas • IA: {{ t.ai_config?.ai_name || 'Jarvis' }}</p>
                  </div>
                  <span v-if="t.id === activeTenant?.id" class="text-[#ffe228] text-xs">✓</span>
                </button>
              </div>
              <div class="pt-1.5 border-t border-[#130e30]/10 mt-1">
                <button 
                  @click="currentView = 'admin'; showTenantDropdown = false" 
                  class="w-full text-center py-1.5 text-[11px] font-bold text-[#130e30] hover:bg-[#eff2e5] rounded-xl transition-colors cursor-pointer"
                >
                  ⚙️ Gerenciar Todos os CRMs
                </button>
              </div>
            </div>
          </div>

          <!-- Pílula Fixa de CRM para Gestores e SDRs -->
          <div v-else-if="activeTenant" class="h-9 px-3.5 rounded-full bg-white border border-[#130e30]/15 text-[#130e30] text-xs font-semibold flex items-center gap-1.5 shadow-2xs select-none">
            <span>🏢</span>
            <span class="max-w-[140px] truncate">{{ activeTenant.name }}</span>
            <span class="text-[9px] px-1.5 py-0.2 rounded-full bg-[#eff2e5] text-[#5f5c6e] font-mono">Base</span>
          </div>

          <!-- Barra de Busca Universal -->
          <div class="relative w-64 sm:w-80">
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Buscar empresa, decisor, tarefa ou tel..."
              class="w-full h-9 pl-9 pr-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] placeholder-[#5f5c6e] focus:outline-none focus:border-[#130e30] transition-colors shadow-2xs"
            />
            <svg class="w-4 h-4 absolute left-3 top-2.5 text-[#5f5c6e]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
      </header>

    <!-- Metrics / Analytics Ribbon (Soft Meadow cards on subtle border) -->
    <div 
      v-if="showMetrics && metrics" 
      class="bg-[#eff2e5]/70 border-b border-[#130e30]/10 px-6 py-3.5 shrink-0 z-10 transition-all"
    >
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3 max-w-7xl">
        <!-- Metric 1: Total Companies & Contacts -->
        <div class="rounded-3xl bg-white border border-[#130e30]/10 p-3.5 flex items-center justify-between">
          <div>
            <p class="text-[11px] font-medium text-[#5f5c6e] uppercase tracking-wider">Empresas (Contas)</p>
            <p class="text-xl font-serif-brand font-bold text-[#130e30] tracking-tight mt-0.5">{{ metrics.total_companies || 0 }}</p>
            <p class="text-[10px] text-[#5f5c6e] mt-0.5">{{ metrics.total_leads || 0 }} decisores vinculados</p>
          </div>
          <div class="w-10 h-10 rounded-full bg-[#eff2e5] border border-[#130e30]/10 flex items-center justify-center text-[#130e30]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
        </div>

        <!-- Metric 2: Disparos Hoje / Cota Diária -->
        <div class="rounded-3xl bg-white border border-[#130e30]/10 p-3.5">
          <div class="flex items-center justify-between">
            <p class="text-[11px] font-medium text-[#5f5c6e] uppercase tracking-wider">Disparos Hoje</p>
            <span class="text-xs font-bold text-[#130e30] font-mono">
              {{ metrics.sent_today }}/{{ metrics.daily_limit }}
            </span>
          </div>
          <div class="flex items-center justify-between mt-1">
            <p class="text-xl font-serif-brand font-bold text-[#130e30] tracking-tight">{{ metrics.sent_today }}</p>
            <span class="text-[10px] font-semibold text-[#130e30] px-2 py-0.5 rounded-full bg-[#ffe228]">
              {{ metrics.daily_percent }}% cota
            </span>
          </div>
          <!-- Progress bar -->
          <div class="w-full bg-[#eff2e5] h-1.5 rounded-full mt-2 overflow-hidden border border-[#130e30]/10">
            <div 
              class="h-full rounded-full transition-all duration-500 bg-[#130e30]"
              :style="{ width: `${metrics.daily_percent}%` }"
            ></div>
          </div>
        </div>

        <!-- Metric 3: Taxa de Resposta -->
        <div class="rounded-3xl bg-white border border-[#130e30]/10 p-3.5 flex items-center justify-between">
          <div>
            <p class="text-[11px] font-medium text-[#5f5c6e] uppercase tracking-wider">Taxa de Resposta</p>
            <p class="text-xl font-serif-brand font-bold text-[#130e30] tracking-tight mt-0.5">{{ metrics.response_rate }}%</p>
            <p class="text-[10px] text-[#5f5c6e] mt-0.5">
              {{ metrics.responded_leads }} de {{ metrics.contacted_leads }} contas
            </p>
          </div>
          <div class="w-10 h-10 rounded-full bg-[#eff2e5] border border-[#130e30]/10 flex items-center justify-center text-[#130e30]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
            </svg>
          </div>
        </div>

        <!-- Metric 4: Reuniões Marcadas -->
        <div class="rounded-3xl bg-white border border-[#130e30]/10 p-3.5 flex items-center justify-between">
          <div>
            <p class="text-[11px] font-medium text-[#5f5c6e] uppercase tracking-wider">Reuniões Marcadas</p>
            <p class="text-xl font-serif-brand font-bold text-[#130e30] tracking-tight mt-0.5">{{ metrics.meetings_count }}</p>
            <p class="text-[10px] text-[#5f5c6e] mt-0.5">Empresas qualificadas</p>
          </div>
          <div class="w-10 h-10 rounded-full bg-[#ffe228] flex items-center justify-center text-[#130e30]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Smart Views Ribbon (Visões Inteligentes com 1 Clique) -->
    <div v-if="currentView === 'kanban'" class="bg-[#eff2e5]/80 border-b border-[#130e30]/10 px-6 py-2 flex items-center justify-between gap-3 text-xs z-10 shrink-0 overflow-x-auto">
      <div class="flex items-center gap-1.5 min-w-max">
        <span class="text-[10px] font-bold text-[#5f5c6e] uppercase tracking-wider mr-1 flex items-center gap-1">
          <span>✨</span>
          <span>Visões Rápidas:</span>
        </span>

        <button 
          v-for="sv in smartViewOptions" 
          :key="sv.id"
          @click="activeSmartView = sv.id"
          class="h-7 px-3 rounded-full text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer shadow-2xs"
          :class="activeSmartView === sv.id 
            ? 'bg-[#130e30] text-white shadow-xs' 
            : 'bg-white text-[#5f5c6e] hover:text-[#130e30] border border-[#130e30]/10 hover:border-[#130e30]/25'"
        >
          <span>{{ sv.icon }}</span>
          <span>{{ sv.label }}</span>
          <span 
            class="px-1.5 py-0.2 rounded-full text-[9px] font-bold"
            :class="activeSmartView === sv.id ? 'bg-[#ffe228] text-[#130e30]' : 'bg-[#eff2e5] text-[#130e30]'"
          >
            {{ sv.count }}
          </span>
        </button>
      </div>

      <div class="text-[11px] text-[#5f5c6e] flex items-center gap-1.5 shrink-0">
        <span>Exibindo:</span>
        <strong class="text-[#130e30] font-bold">{{ filteredTotalCompanies }}</strong>
        <span>de</span>
        <span class="text-[#130e30] font-medium">{{ (companies || []).length }} contas</span>
      </div>
    </div>

    <!-- Filters & Segmentation Toolbar (DESIGN.md Soft Meadow style) -->
    <div v-if="currentView === 'kanban'" class="bg-[#f9fbf2] border-b border-[#130e30]/10 px-6 py-2.5 flex items-center justify-between gap-3 text-xs z-10 shrink-0 flex-wrap">
      <div class="flex items-center gap-2 flex-wrap">
        <span class="text-[11px] font-semibold text-[#5f5c6e] uppercase tracking-wider flex items-center gap-1.5 mr-1">
          <svg class="w-3.5 h-3.5 text-[#130e30]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
          </svg>
          <span>Filtros:</span>
        </span>

        <!-- Filtro SDR -->
        <div class="relative">
          <select 
            v-model="filterSdr"
            class="h-8 pl-3 pr-7 text-xs rounded-full bg-white border border-[#130e30]/15 text-[#130e30] focus:outline-none focus:border-[#130e30] cursor-pointer appearance-none shadow-xs font-medium"
          >
            <option value="all">Todos os SDRs</option>
            <option value="unassigned">Sem responsável</option>
            <option v-for="u in users" :key="u.id" :value="u.id">
              SDR: {{ u.name }}
            </option>
          </select>
          <svg class="w-3.5 h-3.5 absolute right-2.5 top-2.5 text-[#5f5c6e] pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </div>

        <!-- Filtro Segmento -->
        <div class="relative">
          <select 
            v-model="filterSegment"
            class="h-8 pl-3 pr-7 text-xs rounded-full bg-white border border-[#130e30]/15 text-[#130e30] focus:outline-none focus:border-[#130e30] cursor-pointer appearance-none shadow-xs font-medium"
          >
            <option value="all">Todos os Segmentos</option>
            <option v-for="seg in availableSegments" :key="seg" :value="seg">
              {{ seg }}
            </option>
          </select>
          <svg class="w-3.5 h-3.5 absolute right-2.5 top-2.5 text-[#5f5c6e] pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </div>

        <!-- Filtro Status -->
        <div class="relative">
          <select 
            v-model="filterStatus"
            class="h-8 pl-3 pr-7 text-xs rounded-full bg-white border border-[#130e30]/15 text-[#130e30] focus:outline-none focus:border-[#130e30] cursor-pointer appearance-none shadow-xs font-medium"
          >
            <option value="all">Status: Todos</option>
            <option value="open">Status: Em Aberto</option>
            <option value="won">Status: Ganho</option>
            <option value="lost">Status: Perdido</option>
            <option value="unqualified">Status: Desqualificado</option>
          </select>
          <svg class="w-3.5 h-3.5 absolute right-2.5 top-2.5 text-[#5f5c6e] pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </div>

        <!-- Botão Limpar Filtros -->
        <button 
          v-if="hasActiveFilters"
          @click="clearFilters"
          class="h-8 px-3 rounded-full bg-[#eff2e5] hover:bg-rose-100 hover:text-rose-700 text-[#5f5c6e] border border-[#130e30]/10 text-xs font-medium flex items-center gap-1 transition-colors cursor-pointer"
        >
          <span>✕ Limpar filtros</span>
        </button>
      </div>
    </div>

    <!-- Kanban Board Container (Warm Canvas, Soft Meadow columns) -->
    <main v-if="currentView === 'kanban'" class="flex-1 overflow-x-auto overflow-y-hidden p-6 z-10">
      <div class="flex gap-5 h-full items-start pb-4 min-w-max">
        <!-- Stage Columns -->
        <div 
          v-for="stage in stages" 
          :key="stage.id"
          class="w-80 flex flex-col max-h-full rounded-3xl bg-[#eff2e5] border border-[#130e30]/10 transition-all"
          :class="{'ring-2 ring-[#130e30] bg-[#e4ebd7]': isDragOverStage === stage.id}"
          @dragover.prevent="onDragOver(stage.id)"
          @dragleave="onDragLeave(stage.id)"
          @drop="onDrop(stage.id)"
        >
          <!-- Stage Header -->
          <div class="p-3.5 border-b border-[#130e30]/10 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span 
                class="w-2.5 h-2.5 rounded-full" 
                :style="{ backgroundColor: stage.color || '#130e30' }"
              ></span>
              <h2 class="text-xs font-serif-brand font-bold text-[#130e30] tracking-wide">{{ stage.name }}</h2>
            </div>
            <span class="px-2.5 py-0.5 rounded-full bg-white border border-[#130e30]/10 text-[10px] font-bold text-[#130e30]">
              {{ getCompaniesForStage(stage.id).length }}
            </span>
          </div>

          <!-- Company Cards List (Pure white cards on Soft Meadow surface per DESIGN.md) -->
          <div class="flex-1 overflow-y-auto p-3 space-y-3 min-h-[140px]">
            <div 
              v-for="company in getCompaniesForStage(stage.id)" 
              :key="company.id"
              draggable="true"
              @dragstart="onDragStartCompany(company)"
              @click="openCompanyDetails(company)"
              class="p-4 rounded-3xl bg-white border border-[#130e30]/10 hover:border-[#130e30]/30 transition-all cursor-pointer group relative shadow-xs"
            >
              <!-- Card Header: Company Name & Badges -->
              <div class="flex items-start justify-between gap-2 mb-2">
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-1.5 flex-wrap">
                    <h3 class="text-sm font-bold text-[#130e30] truncate group-hover:text-[#130e30]">
                      {{ company.name }}
                    </h3>
                    <!-- Qualification Status Badge -->
                    <span 
                      v-if="company.status !== 'open'" 
                      class="text-[9px] font-bold uppercase px-2 py-0.5 rounded-full"
                      :class="{
                        'bg-[#ffe228] text-[#130e30]': company.status === 'won',
                        'bg-[#130e30] text-white': company.status === 'lost',
                        'bg-[#eff2e5] text-[#5f5c6e] border border-[#130e30]/15': company.status === 'unqualified',
                      }"
                    >
                      {{ company.status === 'won' ? 'Ganho' : (company.status === 'lost' ? 'Perdido' : 'Desqualificado') }}
                    </span>
                    <!-- Notes Indicator -->
                    <span v-if="company.custom_attributes?.notes" title="Possui anotações de qualificação" class="text-[10px]">
                      📝
                    </span>
                  </div>

                  <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                    <span v-if="company.segment" class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-[#eff2e5] text-[#5f5c6e] border border-[#130e30]/10">
                      {{ company.segment }}
                    </span>
                    <span class="text-[11px] text-[#5f5c6e]">
                      {{ (company.contacts || []).length }} {{ (company.contacts || []).length === 1 ? 'decisor' : 'decisores' }}
                    </span>
                    <!-- Activity count badge -->
                    <span v-if="(company.activities || []).length > 0" :title="`${company.activities.length} atividade(s) registrada(s)`" class="text-[10px] text-[#130e30] flex items-center gap-1 bg-[#eff2e5] px-2 py-0.5 rounded-full border border-[#130e30]/10 font-medium">
                      <span>📋</span>
                      <span>{{ company.activities.length }}</span>
                    </span>
                  </div>

                  <!-- Meeting Indicator Badge on Card -->
                  <div 
                    v-if="company.meeting_at" 
                    class="mt-2 px-2.5 py-1.5 rounded-2xl bg-[#eff2e5] border border-[#130e30]/15 flex items-center justify-between gap-1.5"
                  >
                    <div class="flex items-center gap-1.5 min-w-0">
                      <span class="w-2 h-2 rounded-full bg-[#130e30] animate-pulse shrink-0"></span>
                      <span class="text-[10px] font-bold text-[#130e30] truncate">
                        📅 {{ formatDateTimeWithHour(company.meeting_at) }}
                      </span>
                    </div>
                    <a 
                      v-if="company.meeting_link" 
                      :href="company.meeting_link" 
                      target="_blank" 
                      @click.stop
                      title="Abrir sala da Reunião"
                      class="px-2 py-0.5 rounded-full bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] font-bold text-[9px] flex items-center gap-0.5 border border-[#130e30]/15 shrink-0 transition-transform active:scale-95"
                    >
                      <span>Entrar</span>
                      <span>↗</span>
                    </a>
                  </div>

                  <!-- Task / Lembrete Indicator Badge on Card -->
                  <div 
                    v-if="getCompanyTaskSummary(company)" 
                    @click.stop="openCompanyWithTab(company, 'tasks')"
                    class="mt-1.5 px-2.5 py-1 rounded-2xl flex items-center justify-between gap-1.5 transition-all hover:scale-[1.01]"
                    :class="getCompanyTaskSummary(company).badgeClass"
                  >
                    <div class="flex items-center gap-1.5 min-w-0">
                      <span class="text-xs shrink-0">{{ getCompanyTaskSummary(company).icon }}</span>
                      <span class="text-[10px] font-bold truncate">
                        {{ getCompanyTaskSummary(company).label }}
                      </span>
                    </div>
                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded-full border border-current opacity-80 shrink-0">
                      {{ getCompanyTaskSummary(company).count }}
                    </span>
                  </div>

                  <!-- Cadence Indicator Badge on Card -->
                  <div 
                    v-if="company.active_cadence" 
                    @click.stop="openCompanyWithTab(company, 'cadence')"
                    class="mt-1.5 px-2.5 py-1 rounded-2xl bg-[#eff2e5] border border-[#130e30]/15 flex items-center justify-between gap-1.5 transition-all hover:bg-white"
                  >
                    <div class="flex items-center gap-1.5 min-w-0">
                      <span class="text-xs">⚡</span>
                      <span class="text-[10px] font-bold text-[#130e30] truncate">
                        {{ company.active_cadence.cadence?.name?.split('(')[0] || 'Cadência Ativa' }}
                      </span>
                    </div>
                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded-full bg-[#130e30] text-[#ffe228]">
                      Passo {{ company.active_cadence.current_step_number }}
                    </span>
                  </div>
                  <div 
                    v-else-if="company.latest_cadence && company.latest_cadence.status === 'replied'" 
                    @click.stop="openCompanyWithTab(company, 'cadence')"
                    class="mt-1.5 px-2.5 py-1 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-between gap-1.5 transition-all hover:bg-emerald-100/70"
                  >
                    <div class="flex items-center gap-1.5 min-w-0">
                      <span class="text-xs">🎯</span>
                      <span class="text-[10px] font-bold text-emerald-800 truncate">
                        Respondeu (Cadência Pausada)
                      </span>
                    </div>
                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded-full bg-emerald-700 text-white">
                      Engajou
                    </span>
                  </div>

                  <!-- JARVIS AI Insight Badge on Card -->
                  <div 
                    v-if="getCompanyAiInsight(company)" 
                    @click.stop="openChat(getCompanyAiInsight(company).lead)"
                    title="Ver análise e sugestões do JARVIS no chat"
                    class="mt-1.5 px-2.5 py-1 rounded-2xl border flex items-center justify-between gap-1.5 transition-all hover:scale-[1.01]"
                    :class="getCompanyAiInsight(company).class"
                  >
                    <div class="flex items-center gap-1.5 min-w-0">
                      <span class="text-xs">{{ getCompanyAiInsight(company).icon }}</span>
                      <span class="text-[10px] font-bold truncate">
                        {{ getCompanyAiInsight(company).label }}
                      </span>
                    </div>
                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded-full border border-current opacity-90">
                      {{ getCompanyAiInsight(company).score }}/100
                    </span>
                  </div>
                </div>
              </div>

              <!-- Contacts / Decision Makers List inside Card -->
              <div class="mt-2.5 pt-2 border-t border-[#130e30]/8 space-y-1.5">
                <div 
                  v-for="contact in (company.contacts || []).slice(0, 3)" 
                  :key="contact.id"
                  class="flex items-center justify-between p-2 rounded-2xl bg-[#f9fbf2] hover:bg-[#eff2e5] border border-[#130e30]/8 transition-colors"
                >
                  <div class="flex-1 min-w-0 pr-2">
                    <div class="flex items-center gap-1.5">
                      <span class="text-xs font-semibold text-[#130e30] truncate">{{ contact.name }}</span>
                      <span v-if="contact.job_title" class="text-[10px] text-[#5f5c6e] truncate">({{ contact.job_title }})</span>
                    </div>
                    <span class="text-[10px] text-[#5f5c6e] font-mono">{{ formatPhone(contact.phone) }}</span>
                  </div>

                  <div class="flex items-center gap-1 shrink-0">
                    <!-- Quick Email button if contact has email -->
                    <button 
                      v-if="contact.email"
                      @click.stop="openEmailComposer(company, contact)"
                      title="Enviar e-mail via Gmail"
                      class="p-1.5 rounded-full bg-white hover:bg-[#ffe228] text-[#130e30] border border-[#130e30]/15 transition-all cursor-pointer"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                      </svg>
                    </button>

                    <!-- Quick Webphone Call Action -->
                    <button 
                      v-if="contact.phone"
                      @click.stop="triggerCall(contact.phone, company, contact)"
                      title="Ligar com Webphone FreePBX"
                      class="p-1.5 rounded-full bg-white hover:bg-emerald-600 hover:text-white text-emerald-700 border border-[#130e30]/15 transition-all cursor-pointer shadow-2xs"
                    >
                      <svg class="w-3.5 h-3.5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                      </svg>
                    </button>

                    <!-- Quick WhatsApp Action for this specific contact -->
                    <button 
                      @click.stop="openChat(contact)"
                      title="Conversar no WhatsApp"
                      class="p-1.5 rounded-full bg-white hover:bg-[#ffe228] text-[#130e30] border border-[#130e30]/15 transition-all cursor-pointer shadow-2xs"
                    >
                      <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.074-1.127-.065-.272-.087-.624-.22-1.077-.417-1.912-.833-3.155-2.771-3.25-2.899-.095-.127-.775-1.029-.775-1.963 0-.933.491-1.393.666-1.584.175-.19.382-.239.509-.239.127 0 .254.001.365.006.117.006.273-.044.428.328.159.38.541 1.317.588 1.413.047.095.079.206.016.333-.063.127-.095.206-.19.317-.095.111-.2.248-.286.333-.095.095-.195.198-.084.388.111.19.493.813 1.057 1.316.726.646 1.339.847 1.53.942.19.095.302.079.413-.048.111-.127.476-.556.603-.746.127-.19.254-.159.428-.095.175.063 1.111.524 1.302.619.19.095.317.143.365.222.048.079.048.46-.096.865z"/>
                      </svg>
                    </button>
                  </div>
                </div>

                <div v-if="(company.contacts || []).length > 3" class="text-[10px] text-[#5f5c6e] text-center pt-0.5">
                  + {{ company.contacts.length - 3 }} outros contatos
                </div>
              </div>
            </div>

            <!-- Empty Stage State -->
            <div 
              v-if="getCompaniesForStage(stage.id).length === 0" 
              class="h-28 rounded-3xl border border-dashed border-[#130e30]/20 flex flex-col items-center justify-center text-[#5f5c6e] text-xs"
            >
              <span class="font-medium">Nenhuma conta nesta etapa</span>
              <span class="text-[10px] text-[#5f5c6e]/70 mt-0.5">Arraste uma empresa até aqui</span>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- ================= VISÃO 2: TAREFAS & LEMBRETES DO TIME (NOVA TELA COMPLETA) ================= -->
    <main v-else-if="currentView === 'tasks'" class="flex-1 overflow-y-auto p-6 z-10 space-y-6 max-w-7xl mx-auto w-full">
      <!-- Header da Tela de Tarefas -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-[#130e30]/10">
        <div>
          <div class="flex items-center gap-2.5">
            <h2 class="text-2xl font-serif-brand font-bold text-[#130e30] tracking-tight">Tarefas & Lembretes de Prospecção</h2>
            <span class="px-2.5 py-0.5 rounded-full bg-[#130e30] text-[#ffe228] text-[10px] font-bold uppercase tracking-wider shadow-2xs">
              {{ pendingTasksCount }} pendentes
            </span>
          </div>
          <p class="text-xs text-[#5f5c6e] mt-1">
            Organize follow-ups, agendamentos, ligações e retornos comerciais de todas as contas da carteira.
          </p>
        </div>

        <button 
          @click="openNewLeadModal" 
          class="h-9 px-4 rounded-full bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] text-xs font-bold flex items-center gap-1.5 transition-all cursor-pointer shadow-xs active:scale-[0.98] shrink-0"
        >
          <span>+ Nova Conta</span>
        </button>
      </div>

      <!-- 4 Mini Hero KPI Cards de Tarefas -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5">
        <!-- Card 1: Pendentes -->
        <div 
          @click="taskStatusFilter = 'pending'"
          class="p-4 rounded-3xl bg-white border border-[#130e30]/10 hover:border-[#130e30]/30 transition-all cursor-pointer shadow-xs flex items-center justify-between"
          :class="{'ring-2 ring-[#130e30] bg-[#eff2e5]': taskStatusFilter === 'pending'}"
        >
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#5f5c6e]">Tarefas Pendentes</p>
            <p class="text-2xl font-serif-brand font-bold text-[#130e30] mt-1">{{ pendingTasksCount }}</p>
            <p class="text-[10px] text-[#5f5c6e]">Todas as pendências</p>
          </div>
          <div class="w-10 h-10 rounded-2xl bg-[#eff2e5] border border-[#130e30]/10 flex items-center justify-center text-[#130e30] text-lg font-bold">
            📌
          </div>
        </div>

        <!-- Card 2: Atrasadas (Alerta) -->
        <div 
          @click="taskStatusFilter = 'overdue'"
          class="p-4 rounded-3xl bg-white border border-[#130e30]/10 hover:border-[#130e30]/30 transition-all cursor-pointer shadow-xs flex items-center justify-between"
          :class="{'ring-2 ring-rose-500 bg-rose-50/50': taskStatusFilter === 'overdue'}"
        >
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-wider text-rose-700">Atrasadas</p>
            <p class="text-2xl font-serif-brand font-bold text-rose-800 mt-1">{{ overdueTasksCount }}</p>
            <p class="text-[10px] text-rose-600 font-medium">Requer atenção imediata</p>
          </div>
          <div class="w-10 h-10 rounded-2xl bg-rose-100 border border-rose-200 flex items-center justify-center text-rose-800 text-lg font-bold">
            ⚠️
          </div>
        </div>

        <!-- Card 3: Para Hoje -->
        <div 
          @click="taskStatusFilter = 'today'"
          class="p-4 rounded-3xl bg-white border border-[#130e30]/10 hover:border-[#130e30]/30 transition-all cursor-pointer shadow-xs flex items-center justify-between"
          :class="{'ring-2 ring-[#130e30] bg-[#ffe228]/20': taskStatusFilter === 'today'}"
        >
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#130e30]">Para Hoje</p>
            <p class="text-2xl font-serif-brand font-bold text-[#130e30] mt-1">{{ todayTasksCount }}</p>
            <p class="text-[10px] text-[#5f5c6e]">Foco do dia</p>
          </div>
          <div class="w-10 h-10 rounded-2xl bg-[#ffe228] flex items-center justify-center text-[#130e30] text-lg font-bold shadow-xs">
            ⏰
          </div>
        </div>

        <!-- Card 4: Concluídas -->
        <div 
          @click="taskStatusFilter = 'completed'"
          class="p-4 rounded-3xl bg-white border border-[#130e30]/10 hover:border-[#130e30]/30 transition-all cursor-pointer shadow-xs flex items-center justify-between"
          :class="{'ring-2 ring-emerald-600 bg-emerald-50/50': taskStatusFilter === 'completed'}"
        >
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-700">Concluídas</p>
            <p class="text-2xl font-serif-brand font-bold text-emerald-800 mt-1">{{ completedTasksCount }}</p>
            <p class="text-[10px] text-[#5f5c6e]">Histórico resolvido</p>
          </div>
          <div class="w-10 h-10 rounded-2xl bg-emerald-100 border border-emerald-200 flex items-center justify-center text-emerald-800 text-lg font-bold">
            ✓
          </div>
        </div>
      </div>

      <!-- Barra de Filtros e Segmentação da Listagem de Tarefas -->
      <div class="bg-white border border-[#130e30]/10 p-3.5 rounded-3xl flex items-center justify-between gap-3 text-xs flex-wrap shadow-xs">
        <div class="flex items-center gap-2 flex-wrap">
          <span class="text-[11px] font-semibold text-[#5f5c6e] uppercase tracking-wider mr-1">Filtrar:</span>

          <!-- Pílulas de Status -->
          <div class="inline-flex items-center p-1 bg-[#eff2e5] rounded-full border border-[#130e30]/10">
            <button 
              @click="taskStatusFilter = 'pending'"
              class="px-3 py-1 rounded-full text-[11px] font-semibold transition-all cursor-pointer"
              :class="taskStatusFilter === 'pending' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#5f5c6e] hover:text-[#130e30]'"
            >
              Pendentes ({{ pendingTasksCount }})
            </button>
            <button 
              @click="taskStatusFilter = 'overdue'"
              class="px-3 py-1 rounded-full text-[11px] font-semibold transition-all cursor-pointer"
              :class="taskStatusFilter === 'overdue' ? 'bg-rose-700 text-white shadow-xs' : 'text-[#5f5c6e] hover:text-[#130e30]'"
            >
              ⚠️ Atrasadas ({{ overdueTasksCount }})
            </button>
            <button 
              @click="taskStatusFilter = 'today'"
              class="px-3 py-1 rounded-full text-[11px] font-semibold transition-all cursor-pointer"
              :class="taskStatusFilter === 'today' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#5f5c6e] hover:text-[#130e30]'"
            >
              ⏰ Hoje ({{ todayTasksCount }})
            </button>
            <button 
              @click="taskStatusFilter = 'completed'"
              class="px-3 py-1 rounded-full text-[11px] font-semibold transition-all cursor-pointer"
              :class="taskStatusFilter === 'completed' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#5f5c6e] hover:text-[#130e30]'"
            >
              ✓ Concluídas ({{ completedTasksCount }})
            </button>
            <button 
              @click="taskStatusFilter = 'all'"
              class="px-3 py-1 rounded-full text-[11px] font-semibold transition-all cursor-pointer"
              :class="taskStatusFilter === 'all' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#5f5c6e] hover:text-[#130e30]'"
            >
              Todas
            </button>
          </div>

          <!-- Filtro por Tipo de Tarefa -->
          <div class="relative">
            <select 
              v-model="taskTypeFilter"
              class="h-8 pl-3 pr-7 text-xs rounded-full bg-[#f9fbf2] border border-[#130e30]/15 text-[#130e30] focus:outline-none focus:border-[#130e30] cursor-pointer appearance-none shadow-xs font-medium"
            >
              <option value="all">Tipo: Todos</option>
              <option value="call">📞 Ligações</option>
              <option value="whatsapp">💬 WhatsApp</option>
              <option value="email">✉️ E-mails</option>
              <option value="meeting">🤝 Reuniões</option>
              <option value="followup">⚡ Follow-ups</option>
              <option value="other">📝 Outros</option>
            </select>
            <svg class="w-3.5 h-3.5 absolute right-2.5 top-2.5 text-[#5f5c6e] pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>

          <!-- Filtro por SDR -->
          <div class="relative">
            <select 
              v-model="taskSdrFilter"
              class="h-8 pl-3 pr-7 text-xs rounded-full bg-[#f9fbf2] border border-[#130e30]/15 text-[#130e30] focus:outline-none focus:border-[#130e30] cursor-pointer appearance-none shadow-xs font-medium"
            >
              <option value="all">SDR: Todos</option>
              <option v-for="u in users" :key="u.id" :value="u.id">
                SDR: {{ u.name }}
              </option>
            </select>
            <svg class="w-3.5 h-3.5 absolute right-2.5 top-2.5 text-[#5f5c6e] pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>

        <div class="text-[11px] text-[#5f5c6e]">
          Exibindo <strong class="text-[#130e30] font-bold">{{ filteredTasksList.length }}</strong> tarefas
        </div>
      </div>

      <!-- Listagem de Cards de Tarefas -->
      <div class="space-y-3">
        <div 
          v-for="task in filteredTasksList" 
          :key="task.id"
          class="p-4 rounded-3xl bg-white border border-[#130e30]/10 hover:border-[#130e30]/30 transition-all shadow-xs flex items-start justify-between gap-4 group"
          :class="{'opacity-60 bg-[#f9fbf2]': task.status === 'completed'}"
        >
          <!-- Checkbox + Informações -->
          <div class="flex items-start gap-3.5 min-w-0 flex-1">
            <!-- Checkbox Redondo -->
            <button 
              type="button" 
              @click="toggleTaskStatus(task)" 
              :title="task.status === 'pending' ? 'Marcar como concluída' : 'Reabrir tarefa'"
              class="w-6 h-6 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5 cursor-pointer transition-all"
              :class="task.status === 'completed' ? 'bg-[#130e30] border-[#130e30] text-[#ffe228] font-bold' : 'border-[#130e30]/30 hover:border-[#130e30] hover:bg-[#ffe228]/20'"
            >
              <span v-if="task.status === 'completed'" class="text-xs">✓</span>
            </button>

            <!-- Detalhes da Tarefa -->
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2 flex-wrap mb-1">
                <!-- Tipo Ícone -->
                <span class="text-sm">
                  {{ task.type === 'call' ? '📞' : (task.type === 'whatsapp' ? '💬' : (task.type === 'email' ? '✉️' : (task.type === 'meeting' ? '🤝' : '⚡'))) }}
                </span>

                <h3 
                  class="text-sm font-bold text-[#130e30] truncate"
                  :class="{'line-through text-[#5f5c6e]': task.status === 'completed'}"
                >
                  {{ task.title }}
                </h3>

                <!-- Badge Prioridade -->
                <span 
                  class="text-[9px] font-bold px-2 py-0.5 rounded-full uppercase"
                  :class="{
                    'bg-rose-100 text-rose-800 border border-rose-200': task.priority === 'high',
                    'bg-amber-100 text-amber-800 border border-amber-200': task.priority === 'medium',
                    'bg-[#eff2e5] text-[#5f5c6e] border border-[#130e30]/10': task.priority === 'low',
                  }"
                >
                  {{ task.priority === 'high' ? 'Alta' : (task.priority === 'medium' ? 'Média' : 'Baixa') }}
                </span>

                <!-- Badge Vencimento -->
                <span 
                  v-if="task.due_at" 
                  class="text-[10px] font-bold px-2.5 py-0.5 rounded-full"
                  :class="isTaskOverdue(task.due_at) && task.status === 'pending'
                    ? 'bg-rose-50 text-rose-700 border border-rose-200' 
                    : (isTaskToday(task.due_at) && task.status === 'pending'
                      ? 'bg-[#ffe228] text-[#130e30] border border-[#130e30]/20' 
                      : 'bg-[#eff2e5] text-[#5f5c6e]')"
                >
                  ⏰ {{ formatTaskDue(task.due_at) }}
                </span>
              </div>

              <!-- Descrição -->
              <p v-if="task.description" class="text-xs text-[#5f5c6e] mb-2 leading-relaxed">
                {{ task.description }}
              </p>

              <!-- Meta: Conta e Decisor Vinculados -->
              <div class="flex items-center gap-2 flex-wrap text-xs text-[#5f5c6e] pt-1">
                <!-- Empresa -->
                <button 
                  type="button" 
                  @click="openCompanyWithTab(task.company, 'tasks')"
                  class="font-bold text-[#130e30] hover:underline flex items-center gap-1 cursor-pointer bg-[#eff2e5] px-2.5 py-1 rounded-full border border-[#130e30]/10"
                >
                  <span>🏢 {{ task.company_name }}</span>
                  <span class="text-[10px] text-[#5f5c6e]">↗</span>
                </button>

                <!-- Decisor -->
                <span v-if="task.lead" class="flex items-center gap-1 bg-[#eff2e5]/60 px-2.5 py-1 rounded-full">
                  <span>👤 {{ task.lead.name }}</span>
                </span>

                <!-- SDR Atribuído -->
                <span v-if="task.user" class="text-[10px] text-[#5f5c6e] px-2 py-0.5">
                  Atribuído a: <strong class="text-[#130e30]">{{ task.user.name }}</strong>
                </span>
              </div>
            </div>
          </div>

          <!-- Ações Rápidas da Tarefa -->
          <div class="flex items-center gap-2 shrink-0">
            <!-- Ação rápida de WhatsApp se tiver contato -->
            <button 
              v-if="task.lead && task.lead.phone"
              @click="openChat(task.lead)"
              title="Abrir conversa no WhatsApp"
              class="h-8 px-3 rounded-full bg-[#eff2e5] hover:bg-[#ffe228] text-[#130e30] text-[11px] font-semibold flex items-center gap-1 border border-[#130e30]/10 transition-colors cursor-pointer"
            >
              <span>💬</span>
              <span class="hidden sm:inline">WhatsApp</span>
            </button>

            <!-- Abrir Modal da Conta -->
            <button 
              @click="openCompanyWithTab(task.company, 'tasks')"
              title="Ver detalhes da conta e gerenciar tarefas"
              class="h-8 px-3 rounded-full bg-white hover:bg-[#eff2e5] text-[#130e30] text-[11px] font-semibold border border-[#130e30]/15 transition-colors cursor-pointer"
            >
              <span>Abrir Conta</span>
            </button>

            <!-- Excluir Tarefa -->
            <button 
              @click="deleteTask(task)"
              title="Excluir tarefa"
              class="w-8 h-8 rounded-full text-[#5f5c6e]/40 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center text-xs transition-colors cursor-pointer"
            >
              ✕
            </button>
          </div>
        </div>

        <!-- Estado Vazio -->
        <div 
          v-if="!filteredTasksList.length" 
          class="h-44 rounded-3xl border border-dashed border-[#130e30]/20 bg-white/50 flex flex-col items-center justify-center text-[#5f5c6e] text-xs p-6 text-center"
        >
          <span class="text-3xl mb-2">🎉</span>
          <span class="font-bold text-sm text-[#130e30]">Tudo em dia por aqui!</span>
          <p class="text-[11px] text-[#5f5c6e] mt-1 max-w-sm">
            Nenhuma tarefa encontrada para este filtro. Crie novas tarefas nas empresas para manter sua rotina de prospecção organizada.
          </p>
        </div>
      </div>
    </main>

    <!-- ================= VISÃO 3: CADÊNCIAS & RÉGUAS DE PROSPECÇÃO B2B (NOVA TELA) ================= -->
    <main v-else-if="currentView === 'cadences'" class="flex-1 overflow-y-auto p-6 z-10 space-y-6 max-w-7xl mx-auto w-full">
      <!-- Header da Tela de Cadências -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-[#130e30]/10">
        <div>
          <div class="flex items-center gap-2.5">
            <h2 class="text-2xl font-serif-brand font-bold text-[#130e30] tracking-tight">Réguas & Cadências de Prospecção</h2>
            <span class="px-2.5 py-0.5 rounded-full bg-[#130e30] text-[#ffe228] text-[10px] font-bold uppercase tracking-wider shadow-2xs">
              {{ (cadences || []).length }} ativas
            </span>
          </div>
          <p class="text-xs text-[#5f5c6e] mt-1">
            Padronize a cadência de abordagem comercial do time com disparos automáticos de WhatsApp, E-mail e roteiros de Cold Call.
          </p>
        </div>

        <div class="flex items-center gap-2">
          <!-- Botão Disparar Motor de Cadências Agora -->
          <button 
            type="button"
            @click="processCadencesNow"
            :disabled="isProcessingCadences"
            class="h-10 px-4 rounded-full bg-white hover:bg-[#eff2e5] border border-[#130e30]/20 text-[#130e30] text-xs font-bold flex items-center gap-2 shadow-2xs transition-all cursor-pointer active:scale-95 disabled:opacity-50"
            title="Executa o motor cron de cadências imediatamente para processar mensagens vencidas"
          >
            <span :class="{ 'animate-spin': isProcessingCadences }">⚡</span>
            <span>{{ isProcessingCadences ? 'Processando Fila...' : 'Executar Motor Agora' }}</span>
          </button>

          <!-- Botão Criar Cadência -->
          <button 
            type="button"
            @click="openNewCadenceModal"
            class="h-10 px-4 rounded-full bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] text-xs font-bold flex items-center gap-2 shadow-xs transition-all cursor-pointer active:scale-95"
          >
            <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>Nova Cadência</span>
          </button>
        </div>
      </div>

      <!-- Hero KPI Cards de Cadências -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- KPI 1: Cadências Ativas -->
        <div class="p-4 rounded-3xl bg-white border border-[#130e30]/10 shadow-xs flex items-center justify-between">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#5f5c6e]">Cadências Ativas</p>
            <p class="text-2xl font-serif-brand font-bold text-[#130e30] mt-0.5">{{ (cadences || []).length }}</p>
            <p class="text-[10px] text-[#5f5c6e] mt-0.5">Estratégias multicanais</p>
          </div>
          <div class="w-10 h-10 rounded-2xl bg-[#eff2e5] flex items-center justify-center text-lg">
            ⚡
          </div>
        </div>

        <!-- KPI 2: Contas Matriculadas -->
        <div class="p-4 rounded-3xl bg-white border border-[#130e30]/10 shadow-xs flex items-center justify-between">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#5f5c6e]">Contas em Andamento</p>
            <p class="text-2xl font-serif-brand font-bold text-[#130e30] mt-0.5">{{ activeEnrollmentsTotal }}</p>
            <p class="text-[10px] text-[#5f5c6e] mt-0.5">Leads sendo trabalhados</p>
          </div>
          <div class="w-10 h-10 rounded-2xl bg-[#ffe228]/30 flex items-center justify-center text-lg">
            🎯
          </div>
        </div>

        <!-- KPI 3: Passos Automatizados -->
        <div class="p-4 rounded-3xl bg-white border border-[#130e30]/10 shadow-xs flex items-center justify-between">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#5f5c6e]">Passos Configurados</p>
            <p class="text-2xl font-serif-brand font-bold text-[#130e30] mt-0.5">{{ totalCadenceStepsConfigured }}</p>
            <p class="text-[10px] text-[#5f5c6e] mt-0.5">Gatilhos de follow-up</p>
          </div>
          <div class="w-10 h-10 rounded-2xl bg-[#eff2e5] flex items-center justify-center text-lg">
            📋
          </div>
        </div>

        <!-- KPI 4: Taxa de Resposta Global -->
        <div class="p-4 rounded-3xl bg-white border border-[#130e30]/10 shadow-xs flex items-center justify-between">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#5f5c6e]">Taxa de Resposta</p>
            <p class="text-2xl font-serif-brand font-bold text-emerald-600 mt-0.5">{{ metrics?.response_rate || 0 }}%</p>
            <p class="text-[10px] text-[#5f5c6e] mt-0.5">Conversão do funil</p>
          </div>
          <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-lg">
            📈
          </div>
        </div>
      </div>

      <!-- Lista de Cadências Cards -->
      <div class="space-y-6">
        <div 
          v-for="cadence in (cadences || [])" 
          :key="cadence.id"
          class="p-6 rounded-3xl bg-white border border-[#130e30]/10 shadow-xs hover:border-[#130e30]/25 transition-all space-y-5"
        >
          <!-- Cadence Header -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-[#130e30]/10">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-2xl bg-[#130e30] flex items-center justify-center text-[#ffe228] text-base font-bold shadow-2xs">
                ⚡
              </div>
              <div>
                <div class="flex items-center gap-2 flex-wrap">
                  <h3 class="text-base font-serif-brand font-bold text-[#130e30]">{{ cadence.name }}</h3>
                  <span v-if="cadence.target_segment" class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-[#eff2e5] text-[#5f5c6e] border border-[#130e30]/10">
                    {{ cadence.target_segment }}
                  </span>
                </div>
                <p class="text-xs text-[#5f5c6e] mt-0.5">{{ cadence.description }}</p>
              </div>
            </div>

            <!-- Stats da Cadência & Ações -->
            <div class="flex items-center gap-2">
              <span class="px-3 py-1 rounded-full bg-[#eff2e5] border border-[#130e30]/10 text-xs font-bold text-[#130e30]">
                {{ (cadence.steps || []).length }} passos
              </span>
              <span class="px-3 py-1 rounded-full bg-[#130e30] text-[#ffe228] text-xs font-bold">
                {{ cadence.active_enrollments_count || 0 }} contas ativas
              </span>
              <button 
                type="button"
                @click="deleteCadence(cadence)"
                class="w-8 h-8 rounded-full border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 flex items-center justify-center transition-all cursor-pointer shadow-2xs active:scale-95 ml-1"
                title="Excluir Cadência"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Sequência Visual dos Passos (Timeline) -->
          <div class="space-y-2">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#5f5c6e]">Régua de Contato & Roteiros:</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
              <div 
                v-for="step in (cadence.steps || [])" 
                :key="step.id"
                class="p-3.5 rounded-2xl bg-[#f9fbf2] border border-[#130e30]/10 flex flex-col justify-between space-y-2 relative group hover:border-[#130e30]/30 transition-all"
              >
                <div>
                  <div class="flex items-center justify-between gap-1 mb-1.5">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#130e30] text-white">
                      Passo {{ step.step_number }}
                    </span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#ffe228] text-[#130e30]">
                      {{ formatStepDelayBadge(step) }}
                    </span>
                  </div>

                  <div class="flex items-center gap-1.5 text-xs font-bold text-[#130e30] mt-1">
                    <span>{{ step.task_type === 'whatsapp' ? '💬' : (step.task_type === 'email' ? '✉️' : (step.task_type === 'call' ? '📞' : '📅')) }}</span>
                    <span class="truncate">{{ step.title }}</span>
                  </div>

                  <p v-if="step.instructions" class="text-[11px] text-[#5f5c6e] mt-1 line-clamp-2">
                    {{ step.instructions }}
                  </p>
                </div>

                <!-- Template Preview -->
                <div v-if="step.template_content" class="pt-2 border-t border-[#130e30]/10">
                  <div class="p-2 rounded-xl bg-white border border-[#130e30]/5 text-[10px] text-[#5f5c6e] font-mono line-clamp-3 leading-relaxed">
                    {{ step.template_content }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Dashboard de Produtividade & Métricas dos SDRs (Warm Meadow & Sunlit Wildflower) -->
    <main v-else-if="currentView === 'dashboard'" class="flex-1 overflow-y-auto p-6 z-10 space-y-6 max-w-7xl mx-auto w-full">
      <!-- Dashboard Sub-Header com Título e Filtro de Período -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-[#130e30]/10">
        <div>
          <div class="flex items-center gap-2.5">
            <h2 class="text-2xl font-serif-brand font-bold text-[#130e30] tracking-tight">Produtividade & Desempenho do Time</h2>
            <span class="px-2.5 py-0.5 rounded-full bg-[#ffe228] text-[#130e30] text-[10px] font-bold uppercase tracking-wider shadow-2xs">
              Ao Vivo
            </span>
          </div>
          <p class="text-xs text-[#5f5c6e] mt-1">
            Métricas de prospecção, assertividade em chamadas, volume omnichannel e ranking dos SDRs.
          </p>
        </div>

        <!-- Seletor de Período (Pill Tabs) -->
        <div class="flex items-center gap-2">
          <div class="inline-flex items-center p-1 bg-white rounded-full border border-[#130e30]/15 shadow-2xs">
            <button 
              v-for="opt in periodOptions" 
              :key="opt.value"
              @click="changeDashboardPeriod(opt.value)"
              :disabled="isLoadingDashboard"
              class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5"
              :class="dashboardPeriod === opt.value ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#5f5c6e] hover:text-[#130e30] hover:bg-[#eff2e5]'"
            >
              <span>{{ opt.label }}</span>
            </button>
          </div>
          <div v-if="isLoadingDashboard" class="animate-spin rounded-full h-4 w-4 border-2 border-[#130e30] border-t-transparent ml-1"></div>
        </div>
      </div>

      <!-- 4 Hero KPI Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- KPI 1: Reuniões Marcadas -->
        <div class="rounded-3xl bg-white border border-[#130e30]/10 p-5 shadow-xs relative overflow-hidden group hover:border-[#130e30]/30 transition-all">
          <div class="flex items-start justify-between">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-wider text-[#5f5c6e]">Reuniões Marcadas</p>
              <h3 class="text-3xl font-serif-brand font-bold text-[#130e30] mt-1.5 tracking-tight">
                {{ activeDashboardData?.kpis?.total_meetings || 0 }}
              </h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#ffe228] flex items-center justify-center text-[#130e30] text-xl shadow-xs">
              📅
            </div>
          </div>
          <div class="mt-4 pt-3 border-t border-[#130e30]/10 flex items-center justify-between text-xs">
            <span class="text-[#5f5c6e]">Taxa de Conversão:</span>
            <span class="font-bold text-[#130e30] bg-[#eff2e5] px-2 py-0.5 rounded-full">
              {{ activeDashboardData?.kpis?.meeting_conversion_rate || 0 }}% das contas
            </span>
          </div>
        </div>

        <!-- KPI 2: Ligações e Conexão -->
        <div class="rounded-3xl bg-white border border-[#130e30]/10 p-5 shadow-xs relative overflow-hidden group hover:border-[#130e30]/30 transition-all">
          <div class="flex items-start justify-between">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-wider text-[#5f5c6e]">Ligações Realizadas</p>
              <h3 class="text-3xl font-serif-brand font-bold text-[#130e30] mt-1.5 tracking-tight">
                {{ activeDashboardData?.kpis?.total_calls || 0 }}
              </h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#eff2e5] border border-[#130e30]/10 flex items-center justify-center text-[#130e30] text-xl shadow-xs">
              📞
            </div>
          </div>
          <div class="mt-4 pt-3 border-t border-[#130e30]/10 flex items-center justify-between text-xs">
            <span class="text-[#5f5c6e]">Conectadas (Falou):</span>
            <span class="font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
              {{ activeDashboardData?.kpis?.connected_calls || 0 }} ({{ activeDashboardData?.kpis?.call_connection_rate || 0 }}%)
            </span>
          </div>
        </div>

        <!-- KPI 3: Disparos Omnichannel -->
        <div class="rounded-3xl bg-white border border-[#130e30]/10 p-5 shadow-xs relative overflow-hidden group hover:border-[#130e30]/30 transition-all">
          <div class="flex items-start justify-between">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-wider text-[#5f5c6e]">Disparos Omnichannel</p>
              <h3 class="text-3xl font-serif-brand font-bold text-[#130e30] mt-1.5 tracking-tight">
                {{ (activeDashboardData?.kpis?.whatsapp_outbound || 0) + (activeDashboardData?.kpis?.emails_sent || 0) }}
              </h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#eff2e5] border border-[#130e30]/10 flex items-center justify-center text-[#130e30] text-xl shadow-xs">
              💬
            </div>
          </div>
          <div class="mt-4 pt-3 border-t border-[#130e30]/10 flex items-center justify-between text-xs text-[#5f5c6e]">
            <span>Whats: <strong class="text-[#130e30]">{{ activeDashboardData?.kpis?.whatsapp_outbound || 0 }}</strong></span>
            <span>•</span>
            <span>Gmail: <strong class="text-[#130e30]">{{ activeDashboardData?.kpis?.emails_sent || 0 }}</strong></span>
            <span>•</span>
            <span class="font-semibold text-emerald-700">{{ activeDashboardData?.kpis?.whatsapp_response_rate || 0 }}% resp.</span>
          </div>
        </div>

        <!-- KPI 4: Contas Ativas & Engajamento -->
        <div class="rounded-3xl bg-white border border-[#130e30]/10 p-5 shadow-xs relative overflow-hidden group hover:border-[#130e30]/30 transition-all">
          <div class="flex items-start justify-between">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-wider text-[#5f5c6e]">Contas em Prospecção</p>
              <h3 class="text-3xl font-serif-brand font-bold text-[#130e30] mt-1.5 tracking-tight">
                {{ activeDashboardData?.kpis?.total_companies || 0 }}
              </h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#eff2e5] border border-[#130e30]/10 flex items-center justify-center text-[#130e30] text-xl shadow-xs">
              🏢
            </div>
          </div>
          <div class="mt-4 pt-3 border-t border-[#130e30]/10 flex items-center justify-between text-xs">
            <span class="text-[#5f5c6e]">Engajadas / Respostas:</span>
            <span class="font-bold text-[#130e30] bg-[#eff2e5] px-2 py-0.5 rounded-full">
              {{ activeDashboardData?.kpis?.responded_companies || 0 }} ({{ activeDashboardData?.kpis?.response_rate || 0 }}%)
            </span>
          </div>
        </div>
      </div>

      <!-- Main Section: Leaderboard (8 cols) & Breakdowns (4 cols) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Coluna Esquerda: Ranking de Produtividade dos SDRs (8 cols) -->
        <div class="lg:col-span-8 space-y-6">
          <div class="rounded-3xl bg-white border border-[#130e30]/10 p-6 shadow-xs">
            <!-- Header do Leaderboard -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-[#130e30]/10">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-[#130e30] text-[#ffe228] flex items-center justify-center text-lg font-bold">
                  🏆
                </div>
                <div>
                  <h3 class="text-lg font-serif-brand font-bold text-[#130e30]">Ranking de Produtividade dos SDRs</h3>
                  <p class="text-xs text-[#5f5c6e]">Pontuação ponderada calculada por reuniões, conexões telefônicas e contatos ativos</p>
                </div>
              </div>

              <div class="flex items-center gap-2">
                <span class="text-[11px] text-[#5f5c6e] bg-[#eff2e5] px-3 py-1 rounded-full font-medium border border-[#130e30]/10">
                  ⚡ Score: Reunião (50 pts) • Conexão (10 pts)
                </span>
              </div>
            </div>

            <!-- Tabela Editorial de SDRs -->
            <div class="overflow-x-auto mt-4">
              <table class="w-full text-left text-xs">
                <thead>
                  <tr class="border-b border-[#130e30]/10 text-[11px] font-semibold text-[#5f5c6e] uppercase tracking-wider">
                    <th class="py-3 px-2 text-center w-12">Pos.</th>
                    <th class="py-3 px-3">SDR / Especialista</th>
                    <th class="py-3 px-3 text-center">Contas</th>
                    <th class="py-3 px-3 text-center">Ligações</th>
                    <th class="py-3 px-3 text-center">E-mails</th>
                    <th class="py-3 px-3 text-center">Reuniões</th>
                    <th class="py-3 px-3 text-center">Conversão</th>
                    <th class="py-3 px-4 text-right">Score</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[#130e30]/5">
                  <tr 
                    v-for="(sdr, idx) in activeDashboardData?.sdr_leaderboard || []" 
                    :key="sdr.id"
                    class="hover:bg-[#f9fbf2] transition-colors group"
                  >
                    <!-- Posição com Medalha -->
                    <td class="py-3 px-2 text-center font-bold">
                      <span v-if="idx === 0" class="text-lg" title="1º Lugar">🥇</span>
                      <span v-else-if="idx === 1" class="text-lg" title="2º Lugar">🥈</span>
                      <span v-else-if="idx === 2" class="text-lg" title="3º Lugar">🥉</span>
                      <span v-else class="w-6 h-6 rounded-full bg-[#eff2e5] inline-flex items-center justify-center text-[11px] text-[#5f5c6e]">
                        {{ idx + 1 }}
                      </span>
                    </td>

                    <!-- SDR Info -->
                    <td class="py-3 px-3">
                      <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-[#130e30] text-[#ffe228] flex items-center justify-center font-bold text-xs uppercase shadow-2xs">
                          {{ sdr.name ? sdr.name.charAt(0) : '?' }}
                        </div>
                        <div>
                          <div class="font-bold text-[#130e30] group-hover:text-[#130e30]">{{ sdr.name }}</div>
                          <div class="text-[10px] text-[#5f5c6e] truncate max-w-[160px]">{{ sdr.email }}</div>
                        </div>
                      </div>
                    </td>

                    <!-- Contas Atribuídas -->
                    <td class="py-3 px-3 text-center font-medium text-[#130e30]">
                      {{ sdr.assigned_companies }}
                    </td>

                    <!-- Ligações (Total e Conectadas) -->
                    <td class="py-3 px-3 text-center">
                      <div class="font-bold text-[#130e30]">{{ sdr.calls_count }}</div>
                      <div class="text-[10px] text-emerald-700 font-medium">{{ sdr.calls_connected }} conectadas</div>
                    </td>

                    <!-- E-mails -->
                    <td class="py-3 px-3 text-center font-medium text-[#130e30]">
                      {{ sdr.emails_sent }}
                    </td>

                    <!-- Reuniões Agendadas (Destaque Amarelo) -->
                    <td class="py-3 px-3 text-center">
                      <span class="inline-flex items-center px-3 py-1 rounded-full bg-[#ffe228] font-bold text-[#130e30] shadow-2xs">
                        {{ sdr.meetings_scheduled }} 📅
                      </span>
                    </td>

                    <!-- Taxa de Conversão -->
                    <td class="py-3 px-3 text-center font-semibold text-[#130e30]">
                      {{ sdr.conversion_rate }}%
                    </td>

                    <!-- Score -->
                    <td class="py-3 px-4 text-right">
                      <span class="font-serif-brand font-bold text-base text-[#130e30]">{{ sdr.score }}</span>
                      <span class="text-[10px] text-[#5f5c6e] block">pts</span>
                    </td>
                  </tr>

                  <tr v-if="!activeDashboardData?.sdr_leaderboard?.length">
                    <td colspan="8" class="py-8 text-center text-[#5f5c6e]">
                      Nenhuma atividade registrada no período selecionado.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Coluna Direita: Desfechos de Ligações & Etapas (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
          <!-- Card Desfechos de Ligações -->
          <div class="rounded-3xl bg-white border border-[#130e30]/10 p-5 shadow-xs">
            <div class="flex items-center justify-between pb-3 border-b border-[#130e30]/10 mb-3.5">
              <div class="flex items-center gap-2">
                <span class="text-base">📞</span>
                <h3 class="text-sm font-serif-brand font-bold text-[#130e30]">Desfecho das Ligações</h3>
              </div>
              <span class="text-[11px] font-semibold text-[#5f5c6e]">{{ activeDashboardData?.kpis?.total_calls || 0 }} total</span>
            </div>

            <div class="space-y-3 text-xs">
              <!-- Conectou com Decisor -->
              <div>
                <div class="flex justify-between items-center mb-1">
                  <span class="font-medium text-[#130e30] flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Conectou / Conversou</span>
                  </span>
                  <span class="font-bold text-[#130e30]">
                    {{ activeDashboardData?.call_outcomes?.connected || 0 }} 
                    <span class="text-[#5f5c6e] font-normal">({{ getOutcomePercent(activeDashboardData?.call_outcomes?.connected || 0) }}%)</span>
                  </span>
                </div>
                <div class="w-full bg-[#eff2e5] rounded-full h-2 overflow-hidden">
                  <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" :style="{ width: getOutcomePercent(activeDashboardData?.call_outcomes?.connected || 0) + '%' }"></div>
                </div>
              </div>

              <!-- Reunião Agendada na Chamada -->
              <div>
                <div class="flex justify-between items-center mb-1">
                  <span class="font-medium text-[#130e30] flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#ffe228]"></span>
                    <span>Reunião Agendada</span>
                  </span>
                  <span class="font-bold text-[#130e30]">
                    {{ activeDashboardData?.call_outcomes?.meeting_scheduled || 0 }} 
                    <span class="text-[#5f5c6e] font-normal">({{ getOutcomePercent(activeDashboardData?.call_outcomes?.meeting_scheduled || 0) }}%)</span>
                  </span>
                </div>
                <div class="w-full bg-[#eff2e5] rounded-full h-2 overflow-hidden">
                  <div class="bg-[#ffe228] h-2 rounded-full transition-all duration-500" :style="{ width: getOutcomePercent(activeDashboardData?.call_outcomes?.meeting_scheduled || 0) + '%' }"></div>
                </div>
              </div>

              <!-- Não Atendeu / Ocupado -->
              <div>
                <div class="flex justify-between items-center mb-1">
                  <span class="font-medium text-[#5f5c6e] flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    <span>Não Atendeu / Ocupado</span>
                  </span>
                  <span class="font-bold text-[#130e30]">
                    {{ activeDashboardData?.call_outcomes?.no_answer || 0 }} 
                    <span class="text-[#5f5c6e] font-normal">({{ getOutcomePercent(activeDashboardData?.call_outcomes?.no_answer || 0) }}%)</span>
                  </span>
                </div>
                <div class="w-full bg-[#eff2e5] rounded-full h-2 overflow-hidden">
                  <div class="bg-amber-400 h-2 rounded-full transition-all duration-500" :style="{ width: getOutcomePercent(activeDashboardData?.call_outcomes?.no_answer || 0) + '%' }"></div>
                </div>
              </div>

              <!-- Barrou na Secretária (Gatekeeper) -->
              <div>
                <div class="flex justify-between items-center mb-1">
                  <span class="font-medium text-[#5f5c6e] flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-sky-400"></span>
                    <span>Secretária / Gatekeeper</span>
                  </span>
                  <span class="font-bold text-[#130e30]">
                    {{ activeDashboardData?.call_outcomes?.gatekeeper || 0 }} 
                    <span class="text-[#5f5c6e] font-normal">({{ getOutcomePercent(activeDashboardData?.call_outcomes?.gatekeeper || 0) }}%)</span>
                  </span>
                </div>
                <div class="w-full bg-[#eff2e5] rounded-full h-2 overflow-hidden">
                  <div class="bg-sky-400 h-2 rounded-full transition-all duration-500" :style="{ width: getOutcomePercent(activeDashboardData?.call_outcomes?.gatekeeper || 0) + '%' }"></div>
                </div>
              </div>

              <!-- Caixa Postal -->
              <div>
                <div class="flex justify-between items-center mb-1">
                  <span class="font-medium text-[#5f5c6e] flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-violet-400"></span>
                    <span>Caixa Postal</span>
                  </span>
                  <span class="font-bold text-[#130e30]">
                    {{ activeDashboardData?.call_outcomes?.voicemail || 0 }} 
                    <span class="text-[#5f5c6e] font-normal">({{ getOutcomePercent(activeDashboardData?.call_outcomes?.voicemail || 0) }}%)</span>
                  </span>
                </div>
                <div class="w-full bg-[#eff2e5] rounded-full h-2 overflow-hidden">
                  <div class="bg-violet-400 h-2 rounded-full transition-all duration-500" :style="{ width: getOutcomePercent(activeDashboardData?.call_outcomes?.voicemail || 0) + '%' }"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Card Distribuição por Etapa do Funil -->
          <div class="rounded-3xl bg-white border border-[#130e30]/10 p-5 shadow-xs">
            <div class="flex items-center justify-between pb-3 border-b border-[#130e30]/10 mb-3.5">
              <div class="flex items-center gap-2">
                <span class="text-base">📊</span>
                <h3 class="text-sm font-serif-brand font-bold text-[#130e30]">Contas por Etapa do Pipeline</h3>
              </div>
              <span class="text-[11px] font-semibold text-[#5f5c6e]">
                {{ activeDashboardData?.kpis?.total_companies || 0 }} contas
              </span>
            </div>

            <div class="space-y-2.5 text-xs">
              <div 
                v-for="stg in activeDashboardData?.stage_distribution || []" 
                :key="stg.id"
                class="p-2.5 rounded-2xl bg-[#f9fbf2] border border-[#130e30]/5 flex items-center justify-between"
              >
                <div class="flex items-center gap-2 min-w-0 pr-2">
                  <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ backgroundColor: stg.color }"></span>
                  <span class="font-medium text-[#130e30] truncate">{{ stg.name }}</span>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                  <span class="font-bold text-[#130e30]">{{ stg.count }}</span>
                  <span class="text-[10px] text-[#5f5c6e]">({{ getStagePercent(stg.count) }}%)</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Biblioteca de Scripts & Modelos de Mensagens (DESIGN.md Warm Meadow) -->
    <main v-else-if="currentView === 'templates'" class="flex-1 overflow-y-auto p-6 z-10 space-y-6 max-w-7xl mx-auto w-full">
      <!-- Header da Biblioteca -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#130e30]/10">
        <div>
          <div class="flex items-center gap-2.5">
            <h2 class="text-2xl font-serif-brand font-bold text-[#130e30] tracking-tight">Biblioteca de Scripts & Abordagens B2B</h2>
            <span class="px-2.5 py-0.5 rounded-full bg-[#130e30] text-[#ffe228] text-[10px] font-bold uppercase tracking-wider shadow-2xs">
              {{ (messageTemplates || []).length }} modelos
            </span>
          </div>
          <p class="text-xs text-[#5f5c6e] mt-1">
            Scripts de prospecção, quebra de objeções, follow-ups e confirmação de reunião prontos para envio com 1 clique.
          </p>
        </div>

        <!-- Botão Novo Script -->
        <button 
          @click="openNewTemplateModal"
          class="h-10 px-4 rounded-full bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] text-xs font-bold flex items-center gap-2 shadow-xs transition-all cursor-pointer active:scale-95"
        >
          <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
          </svg>
          <span>Novo Script</span>
        </button>
      </div>

      <!-- Barra de Filtros & Categorias -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3 rounded-3xl border border-[#130e30]/10 shadow-xs">
        <!-- Abas de Categorias (Pills) -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
          <button 
            @click="templateCategoryFilter = 'all'"
            class="h-8 px-3.5 rounded-full text-xs font-semibold transition-all cursor-pointer whitespace-nowrap"
            :class="templateCategoryFilter === 'all' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#5f5c6e] hover:text-[#130e30] hover:bg-[#eff2e5]'"
          >
            🔥 Todos ({{ (messageTemplates || []).length }})
          </button>
          <button 
            @click="templateCategoryFilter = 'approach'"
            class="h-8 px-3.5 rounded-full text-xs font-semibold transition-all cursor-pointer whitespace-nowrap"
            :class="templateCategoryFilter === 'approach' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#5f5c6e] hover:text-[#130e30] hover:bg-[#eff2e5]'"
          >
            🚀 Abordagem Inicial
          </button>
          <button 
            @click="templateCategoryFilter = 'objection'"
            class="h-8 px-3.5 rounded-full text-xs font-semibold transition-all cursor-pointer whitespace-nowrap"
            :class="templateCategoryFilter === 'objection' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#5f5c6e] hover:text-[#130e30] hover:bg-[#eff2e5]'"
          >
            🛡️ Quebra de Objeção
          </button>
          <button 
            @click="templateCategoryFilter = 'followup'"
            class="h-8 px-3.5 rounded-full text-xs font-semibold transition-all cursor-pointer whitespace-nowrap"
            :class="templateCategoryFilter === 'followup' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#5f5c6e] hover:text-[#130e30] hover:bg-[#eff2e5]'"
          >
            🔄 Follow-up
          </button>
          <button 
            @click="templateCategoryFilter = 'meeting'"
            class="h-8 px-3.5 rounded-full text-xs font-semibold transition-all cursor-pointer whitespace-nowrap"
            :class="templateCategoryFilter === 'meeting' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#5f5c6e] hover:text-[#130e30] hover:bg-[#eff2e5]'"
          >
            📅 Reunião
          </button>
        </div>

        <!-- Filtro de Canal -->
        <div class="flex items-center gap-2 shrink-0">
          <span class="text-[11px] font-semibold text-[#5f5c6e]">Canal:</span>
          <select 
            v-model="templateChannelFilter"
            class="h-8 pl-3 pr-7 text-xs rounded-full bg-[#f9fbf2] border border-[#130e30]/15 text-[#130e30] focus:outline-none focus:border-[#130e30] cursor-pointer appearance-none font-medium"
          >
            <option value="all">Todos os Canais</option>
            <option value="whatsapp">💬 WhatsApp</option>
            <option value="email">✉️ Gmail</option>
          </select>
        </div>
      </div>

      <!-- Grid de Cards de Scripts -->
      <div v-if="filteredMessageTemplates.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <div 
          v-for="tpl in filteredMessageTemplates" 
          :key="tpl.id"
          class="p-5 rounded-3xl bg-white border border-[#130e30]/10 shadow-xs hover:border-[#130e30]/30 transition-all flex flex-col justify-between space-y-4 group"
        >
          <!-- Topo do Card: Categoria, Canal & Favorito -->
          <div>
            <div class="flex items-start justify-between gap-2 mb-2">
              <div class="flex items-center gap-1.5 flex-wrap">
                <!-- Badge de Categoria -->
                <span 
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                  :class="tpl.category === 'approach' ? 'bg-amber-100 text-amber-900 border border-amber-200' : (tpl.category === 'objection' ? 'bg-rose-100 text-rose-900 border border-rose-200' : (tpl.category === 'followup' ? 'bg-sky-100 text-sky-900 border border-sky-200' : 'bg-emerald-100 text-emerald-900 border border-emerald-200'))"
                >
                  {{ tpl.category === 'approach' ? '🚀 Abordagem' : (tpl.category === 'objection' ? '🛡️ Objeção' : (tpl.category === 'followup' ? '🔄 Follow-up' : (tpl.category === 'meeting' ? '📅 Reunião' : '🎯 Fechamento'))) }}
                </span>

                <!-- Badge de Canal -->
                <span 
                  class="px-2 py-0.5 rounded-full text-[9px] font-semibold"
                  :class="tpl.channel === 'whatsapp' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : (tpl.channel === 'email' ? 'bg-blue-50 text-blue-800 border border-blue-200' : 'bg-[#eff2e5] text-[#130e30] border border-[#130e30]/10')"
                >
                  {{ tpl.channel === 'whatsapp' ? '💬 WhatsApp' : (tpl.channel === 'email' ? '✉️ Gmail' : '🌐 Omnichannel') }}
                </span>
              </div>

              <!-- Botão Favorito -->
              <button 
                @click="toggleTemplateFavorite(tpl)" 
                class="text-base p-1 transition-transform active:scale-125 cursor-pointer"
                :title="tpl.is_favorite ? 'Remover dos favoritos' : 'Favoritar este script'"
              >
                <span v-if="tpl.is_favorite" class="text-amber-400">★</span>
                <span v-else class="text-[#5f5c6e]/40 hover:text-amber-400">☆</span>
              </button>
            </div>

            <!-- Título do Script -->
            <h3 class="text-sm font-serif-brand font-bold text-[#130e30] leading-snug">
              {{ tpl.title }}
            </h3>

            <!-- Conteúdo do Script -->
            <div class="mt-3 p-3.5 rounded-2xl bg-[#f9fbf2] border border-[#130e30]/5 text-xs text-[#130e30] leading-relaxed max-h-44 overflow-y-auto whitespace-pre-wrap select-all font-sans">
              {{ tpl.content }}
            </div>
          </div>

          <!-- Rodapé do Card: Tags Dinâmicas & Ações -->
          <div class="pt-3 border-t border-[#130e30]/10 flex items-center justify-between gap-2">
            <!-- Tags utilizadas no template -->
            <div class="flex items-center gap-1 overflow-x-auto text-[10px] text-[#5f5c6e]">
              <span v-if="tpl.content.includes('{{decisor}}')" class="px-1.5 py-0.2 rounded bg-white border border-[#130e30]/10 font-mono">
                decisor
              </span>
              <span v-if="tpl.content.includes('{{empresa}}')" class="px-1.5 py-0.2 rounded bg-white border border-[#130e30]/10 font-mono">
                empresa
              </span>
              <span v-if="tpl.content.includes('{{setor}}')" class="px-1.5 py-0.2 rounded bg-white border border-[#130e30]/10 font-mono">
                setor
              </span>
            </div>

            <!-- Botões de Ação -->
            <div class="flex items-center gap-1.5 shrink-0">
              <button 
                @click="copyTemplateText(tpl.content)"
                title="Copiar texto para colar em qualquer lugar"
                class="h-8 px-3 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-[#130e30] text-xs font-semibold transition-all flex items-center gap-1 cursor-pointer active:scale-95 shadow-2xs"
              >
                <span>📋</span>
                <span>Copiar</span>
              </button>

              <button 
                @click="deleteTemplate(tpl.id)"
                title="Excluir este modelo"
                class="w-8 h-8 rounded-full bg-white hover:bg-rose-50 text-[#5f5c6e] hover:text-rose-600 border border-[#130e30]/10 flex items-center justify-center transition-all cursor-pointer active:scale-95"
              >
                ✕
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State se não houver templates no filtro -->
      <div v-else class="p-12 text-center rounded-3xl bg-white border border-[#130e30]/10 shadow-xs space-y-3">
        <div class="w-12 h-12 rounded-full bg-[#eff2e5] flex items-center justify-center text-xl mx-auto text-[#130e30]">
          📝
        </div>
        <h3 class="text-base font-serif-brand font-bold text-[#130e30]">Nenhum script encontrado nesta categoria</h3>
        <p class="text-xs text-[#5f5c6e] max-w-sm mx-auto">
          Crie novos roteiros ou selecione outra categoria acima para visualizar seus modelos salvos.
        </p>
        <button 
          @click="openNewTemplateModal"
          class="h-9 px-4 rounded-full bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] text-xs font-bold transition-all cursor-pointer shadow-xs inline-flex items-center gap-1.5 mt-2"
        >
          <span>+ Criar Primeiro Script</span>
        </button>
      </div>
    </main>

    <!-- Painel de Gestão Multi-Tenant & Delegação de Acessos (DESIGN.md Warm Meadow) -->
    <main v-else-if="currentView === 'admin'" class="flex-1 overflow-y-auto p-6 z-10 space-y-6 max-w-7xl mx-auto w-full">
      <!-- Header do Painel Admin -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#130e30]/10">
        <div>
          <div class="flex items-center gap-2.5">
            <h2 class="text-2xl font-serif-brand font-bold text-[#130e30] tracking-tight">
              {{ canManageTenants ? 'Gestão Multi-CRM & Acessos' : 'Gestão da Equipe Comercial' }}
            </h2>
            <span class="px-2.5 py-0.5 rounded-full bg-[#130e30] text-[#ffe228] text-[10px] font-bold uppercase tracking-wider shadow-2xs">
              {{ isSuperAdmin ? 'Super Admin Global' : (isManager ? 'Gestor da Base' : 'Admin') }}
            </span>
          </div>
          <p class="text-xs text-[#5f5c6e] mt-1">
            {{ canManageTenants 
              ? 'Crie e administre múltiplos perfis de CRM autônomos (cada um com base, dados e IA dedicada) e delegue acessos.' 
              : `Gerencie os colaboradores e SDRs da base ${activeTenant?.name || 'Comercial'}. Adicione ou remova membros da sua equipe.` }}
          </p>
        </div>

        <!-- Botão Primário Dinâmico -->
        <button 
          v-if="canManageTenants && adminTab === 'tenants'"
          @click="openNewTenantModal"
          class="h-10 px-4 rounded-full bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] text-xs font-bold flex items-center gap-2 shadow-xs transition-all cursor-pointer active:scale-95"
        >
          <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
          </svg>
          <span>Novo Perfil de CRM</span>
        </button>

        <button 
          v-else
          @click="openNewUserModal"
          class="h-10 px-4 rounded-full bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] text-xs font-bold flex items-center gap-2 shadow-xs transition-all cursor-pointer active:scale-95"
        >
          <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
          </svg>
          <span>{{ canManageTenants ? 'Convidar / Novo Usuário' : '+ Novo Colaborador' }}</span>
        </button>
      </div>

      <!-- Abas de Navegação do Painel Admin (Apenas para Super Admin com múltiplos CRMs) -->
      <div v-if="canManageTenants" class="flex items-center gap-2 bg-white p-1.5 rounded-full border border-[#130e30]/10 shadow-xs max-w-md">
        <button 
          @click="adminTab = 'tenants'"
          class="flex-1 py-2 px-4 rounded-full text-xs font-semibold transition-all cursor-pointer flex items-center justify-center gap-2"
          :class="adminTab === 'tenants' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#5f5c6e] hover:text-[#130e30] hover:bg-[#eff2e5]'"
        >
          <span>🏢</span>
          <span>Perfis de CRM & IA</span>
          <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="adminTab === 'tenants' ? 'bg-white/20 text-white' : 'bg-[#eff2e5] text-[#130e30]'">
            {{ (allTenants || []).length }}
          </span>
        </button>

        <button 
          @click="adminTab = 'users'"
          class="flex-1 py-2 px-4 rounded-full text-xs font-semibold transition-all cursor-pointer flex items-center justify-center gap-2"
          :class="adminTab === 'users' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#5f5c6e] hover:text-[#130e30] hover:bg-[#eff2e5]'"
        >
          <span>👥</span>
          <span>Usuários & Acessos</span>
          <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="adminTab === 'users' ? 'bg-white/20 text-white' : 'bg-[#eff2e5] text-[#130e30]'">
            {{ (allUsers || []).length }}
          </span>
        </button>
      </div>

      <!-- ================= ABA 1: PERFIS DE CRM & IA ================= -->
      <div v-if="canManageTenants && adminTab === 'tenants'" class="space-y-6">
        <!-- KPIs do Sistema Multi-Tenant -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="p-4 rounded-3xl bg-white border border-[#130e30]/10 shadow-xs flex items-center justify-between">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-wider text-[#5f5c6e]">Perfis de CRM Ativos</p>
              <p class="text-2xl font-serif-brand font-bold text-[#130e30] mt-0.5">{{ (allTenants || []).length }}</p>
              <p class="text-[10px] text-[#5f5c6e] mt-0.5">Operações isoladas</p>
            </div>
            <div class="w-10 h-10 rounded-2xl bg-[#eff2e5] flex items-center justify-center text-lg">
              🏢
            </div>
          </div>

          <div class="p-4 rounded-3xl bg-white border border-[#130e30]/10 shadow-xs flex items-center justify-between">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-wider text-[#5f5c6e]">Total de Contas Gerenciadas</p>
              <p class="text-2xl font-serif-brand font-bold text-[#130e30] mt-0.5">
                {{ (allTenants || []).reduce((acc, t) => acc + (t.companies_count || 0), 0) }}
              </p>
              <p class="text-[10px] text-[#5f5c6e] mt-0.5">Em todos os pipelines</p>
            </div>
            <div class="w-10 h-10 rounded-2xl bg-[#ffe228]/30 flex items-center justify-center text-lg">
              🎯
            </div>
          </div>

          <div class="p-4 rounded-3xl bg-white border border-[#130e30]/10 shadow-xs flex items-center justify-between">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-wider text-[#5f5c6e]">Usuários & SDRs Cadastrados</p>
              <p class="text-2xl font-serif-brand font-bold text-[#130e30] mt-0.5">{{ (allUsers || []).length }}</p>
              <p class="text-[10px] text-[#5f5c6e] mt-0.5">Membros ativos</p>
            </div>
            <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-lg">
              👥
            </div>
          </div>
        </div>

        <!-- Grid de Cards de Tenants (Perfis de CRM) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div 
            v-for="t in allTenants" 
            :key="t.id"
            class="p-6 rounded-3xl bg-white border shadow-xs transition-all space-y-4 flex flex-col justify-between"
            :class="t.id === activeTenant?.id ? 'border-[#130e30] ring-2 ring-[#ffe228]/60 shadow-sm' : 'border-[#130e30]/10 hover:border-[#130e30]/25'"
          >
            <div>
              <!-- Cabeçalho do Card -->
              <div class="flex items-start justify-between gap-3 pb-3 border-b border-[#130e30]/10">
                <div class="flex items-center gap-3">
                  <div class="w-11 h-11 rounded-2xl bg-[#130e30] text-[#ffe228] flex items-center justify-center font-serif-brand font-bold text-lg shadow-2xs">
                    {{ t.name.charAt(0) }}
                  </div>
                  <div>
                    <div class="flex items-center gap-2 flex-wrap">
                      <h3 class="text-base font-serif-brand font-bold text-[#130e30]">{{ t.name }}</h3>
                      <span v-if="t.id === activeTenant?.id" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#ffe228] text-[#130e30] border border-[#130e30]/10 shadow-2xs">
                        ⭐ Ativo na Sessão
                      </span>
                    </div>
                    <p class="text-xs text-[#5f5c6e] mt-0.5 font-mono">slug: {{ t.slug || 'crm-' + t.id }}</p>
                  </div>
                </div>

                <span 
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                  :class="t.status === 'active' ? 'bg-emerald-100 text-emerald-900 border border-emerald-200' : 'bg-rose-100 text-rose-900 border border-rose-200'"
                >
                  {{ t.status === 'active' ? 'Ativo' : 'Pausado' }}
                </span>
              </div>

              <!-- Descrição -->
              <p class="text-xs text-[#5f5c6e] mt-3 leading-relaxed">
                {{ t.description || 'Operação de prospecção comercial independente com base própria.' }}
              </p>

              <!-- Métricas do Perfil -->
              <div class="grid grid-cols-3 gap-2 mt-4 p-3 rounded-2xl bg-[#f9fbf2] border border-[#130e30]/5 text-center">
                <div>
                  <p class="text-base font-serif-brand font-bold text-[#130e30]">{{ t.companies_count || 0 }}</p>
                  <p class="text-[10px] text-[#5f5c6e]">Contas B2B</p>
                </div>
                <div class="border-x border-[#130e30]/10">
                  <p class="text-base font-serif-brand font-bold text-[#130e30]">{{ t.leads_count || 0 }}</p>
                  <p class="text-[10px] text-[#5f5c6e]">Decisores</p>
                </div>
                <div>
                  <p class="text-base font-serif-brand font-bold text-[#130e30]">{{ t.users_count || 0 }}</p>
                  <p class="text-[10px] text-[#5f5c6e]">SDRs / Time</p>
                </div>
              </div>

              <!-- Caixa de Configuração de IA do CRM -->
              <div class="mt-4 p-3.5 rounded-2xl bg-[#eff2e5]/60 border border-[#130e30]/10 space-y-2 text-xs">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-1.5 font-bold text-[#130e30]">
                    <span>🤖</span>
                    <span>IA do CRM: {{ t.ai_config?.ai_name || 'Agente JARVIS' }}</span>
                  </div>
                  <span class="px-2 py-0.5 rounded-full bg-white text-[10px] font-mono border border-[#130e30]/10 text-[#5f5c6e]">
                    {{ t.ai_config?.ai_model || 'gpt-4o-mini' }}
                  </span>
                </div>
                <p class="text-[11px] text-[#130e30] font-medium leading-snug">
                  <span class="text-[#5f5c6e]">Persona:</span> {{ t.ai_config?.ai_persona }}
                </p>
                <p class="text-[10px] text-[#5f5c6e] line-clamp-2 italic bg-white/70 p-2 rounded-xl border border-[#130e30]/5">
                  "{{ t.ai_config?.ai_prompt }}"
                </p>
              </div>
            </div>

            <!-- Rodapé com Botões de Ação -->
            <div class="pt-4 border-t border-[#130e30]/10 flex items-center justify-between gap-2 flex-wrap">
              <button 
                v-if="t.id !== activeTenant?.id"
                @click="switchActiveTenant(t.id)"
                class="h-9 px-4 rounded-full bg-[#130e30] hover:bg-[#20184f] text-[#ffe228] text-xs font-bold transition-all cursor-pointer shadow-xs flex items-center gap-1.5 active:scale-95"
              >
                <span>🚀</span>
                <span>Entrar neste CRM</span>
              </button>

              <div v-else class="h-9 px-4 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold flex items-center gap-1.5">
                <span>✓</span>
                <span>CRM Selecionado</span>
              </div>

              <div class="flex items-center gap-2">
                <button 
                  @click="openEditTenantModal(t)"
                  class="h-9 px-3.5 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-[#130e30] text-xs font-semibold transition-all cursor-pointer flex items-center gap-1"
                >
                  <span>⚙️</span>
                  <span>Configurar IA</span>
                </button>

                <button 
                  v-if="(allTenants || []).length > 1"
                  @click="deleteTenant(t.id)"
                  class="w-9 h-9 rounded-full bg-white hover:bg-rose-50 text-[#5f5c6e] hover:text-rose-600 border border-[#130e30]/10 flex items-center justify-center transition-all cursor-pointer"
                  title="Excluir Perfil de CRM"
                >
                  ✕
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ================= ABA 2: USUÁRIOS & PERMISSÕES / GESTÃO DE EQUIPE ================= -->
      <div v-else-if="adminTab === 'users' || !canManageTenants" class="space-y-4">
        <div class="rounded-3xl bg-white border border-[#130e30]/10 shadow-xs overflow-hidden">
          <div class="p-4 border-b border-[#130e30]/10 flex items-center justify-between bg-[#eff2e5]/40">
            <div>
              <h3 class="text-sm font-serif-brand font-bold text-[#130e30]">
                {{ canManageTenants ? 'Membros da Plataforma' : `Colaboradores da Base: ${activeTenant?.name || 'Comercial'}` }}
              </h3>
              <p class="text-[11px] text-[#5f5c6e]">
                {{ canManageTenants ? 'Gerencie acessos, papéis (SDR, Gestor, Admin) e o perfil de CRM vinculado.' : 'Adicione ou remova operadores de prospecção (SDR) e gestores da sua equipe.' }}
              </p>
            </div>
            <span class="text-xs font-bold text-[#130e30] bg-white px-3 py-1 rounded-full border border-[#130e30]/10">
              {{ (allUsers || []).length }} colaboradores
            </span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-[#f9fbf2] border-b border-[#130e30]/10 text-[10px] font-bold uppercase tracking-wider text-[#5f5c6e]">
                <tr>
                  <th class="p-3.5 pl-6">Usuário</th>
                  <th class="p-3.5">E-mail</th>
                  <th class="p-3.5">Nível de Acesso</th>
                  <th class="p-3.5">Perfil de CRM</th>
                  <th class="p-3.5">Status</th>
                  <th class="p-3.5 pr-6 text-right">Ações</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#130e30]/5">
                <tr v-for="u in allUsers" :key="u.id" class="hover:bg-[#f9fbf2]/60 transition-colors">
                  <td class="p-3.5 pl-6">
                    <div class="flex items-center gap-2.5">
                      <div class="w-8 h-8 rounded-full bg-[#130e30] text-[#ffe228] font-bold text-xs flex items-center justify-center shrink-0">
                        {{ u.name.charAt(0) }}
                      </div>
                      <span class="font-bold text-[#130e30]">{{ u.name }}</span>
                    </div>
                  </td>

                  <td class="p-3.5 text-[#5f5c6e] font-mono text-[11px]">
                    {{ u.email }}
                  </td>

                  <td class="p-3.5">
                    <span 
                      class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                      :class="u.is_superadmin ? 'bg-amber-100 text-amber-900 border border-amber-300' : (u.role === 'admin' ? 'bg-[#130e30] text-white' : (u.role === 'manager' ? 'bg-purple-100 text-purple-900 border border-purple-200' : 'bg-emerald-100 text-emerald-900 border border-emerald-200'))"
                    >
                      {{ u.is_superadmin ? '👑 Super Admin' : (u.role === 'admin' ? '🏢 Admin' : (u.role === 'manager' ? '👔 Gestor' : '💼 SDR')) }}
                    </span>
                  </td>

                  <td class="p-3.5">
                    <span class="px-2 py-0.5 rounded-full bg-[#eff2e5] text-[#130e30] font-semibold text-[11px] border border-[#130e30]/10">
                      {{ u.tenant?.name || 'CRM Geral' }}
                    </span>
                  </td>

                  <td class="p-3.5">
                    <span 
                      class="inline-flex items-center gap-1.5 text-[11px] font-semibold"
                      :class="u.is_active !== false ? 'text-emerald-700' : 'text-rose-600'"
                    >
                      <span class="w-2 h-2 rounded-full" :class="u.is_active !== false ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                      <span>{{ u.is_active !== false ? 'Ativo' : 'Inativo' }}</span>
                    </span>
                  </td>

                  <td class="p-3.5 pr-6 text-right">
                    <!-- Se for uma conta protegida de Administrador e o usuário logado for apenas Gestor -->
                    <div v-if="!isSuperAdmin && (u.is_superadmin || u.role === 'admin')" class="flex items-center justify-end">
                      <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-amber-900 bg-amber-100/70 border border-amber-300 px-2.5 py-1 rounded-full shadow-2xs select-none">
                        <span>🔒</span>
                        <span>Conta Protegida</span>
                      </span>
                    </div>

                    <!-- Ações padrão para SDRs e colaboradores gerenciáveis -->
                    <div v-else class="flex items-center justify-end gap-1.5">
                      <button 
                        @click="openEditUserModal(u)"
                        class="h-7 px-3 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-[#130e30] text-[11px] font-semibold transition-all cursor-pointer shadow-2xs active:scale-95"
                      >
                        Editar
                      </button>
                      <button 
                        v-if="u.id !== currentUser?.id && (isSuperAdmin || (!u.is_superadmin && u.role !== 'admin'))"
                        @click="deleteUser(u.id)"
                        class="w-7 h-7 rounded-full bg-white hover:bg-rose-50 text-[#5f5c6e] hover:text-rose-600 border border-[#130e30]/10 flex items-center justify-center transition-all cursor-pointer shadow-2xs active:scale-95"
                        title="Remover colaborador"
                      >
                        ✕
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>

    <!-- 🧠 Laboratório de Treinamento e Estresse da IA (DESIGN.md Warm Canvas) -->
    <main v-else-if="currentView === 'ai_lab'" class="flex-1 overflow-y-auto p-6 z-10 space-y-6 max-w-7xl mx-auto w-full">
      <AiTrainingLab 
        :active-tenant="activeTenant"
        @notify="(e) => showToast(e.message, e.type)"
      />
    </main>
    </div>

    <!-- Chat / WhatsApp Drawer Modal (Light, Warm Sunlit Style) -->
    <div 
      v-if="selectedLead" 
      class="fixed inset-0 z-50 flex justify-end bg-[#130e30]/40 backdrop-blur-sm transition-all"
      @click.self="closeChat"
    >
      <div class="w-full max-w-lg h-full bg-[#f9fbf2] border-l border-[#130e30]/15 flex flex-col shadow-2xl animate-in slide-in-from-right duration-200">
        <!-- Chat Header -->
        <div class="p-4 border-b border-[#130e30]/10 flex items-center justify-between bg-[#eff2e5]">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-[#130e30] flex items-center justify-center font-serif-brand font-bold text-white text-base shadow-sm">
              {{ selectedLead.name.charAt(0) }}
            </div>
            <div>
              <div class="flex items-center gap-1.5">
                <h3 class="text-sm font-bold text-[#130e30]">{{ selectedLead.name }}</h3>
                <span v-if="selectedLead.job_title" class="text-[10px] text-[#5f5c6e] font-medium">({{ selectedLead.job_title }})</span>
              </div>
              <p class="text-xs text-[#5f5c6e] flex items-center gap-1.5">
                <span>{{ formatPhone(selectedLead.phone) }}</span>
                <span v-if="selectedLead.company_name">•</span>
                <span v-if="selectedLead.company_name" class="text-[#130e30] font-medium">{{ selectedLead.company_name }}</span>
              </p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <!-- Botão de Controle de IA Autônoma para este Lead -->
            <button 
              type="button"
              @click="toggleLeadAiAutonomous(selectedLead)"
              :title="selectedLead.custom_attributes?.ai_autonomous_paused 
                ? 'A IA está pausada para você falar diretamente. Clique para reativar o SDR autônomo' 
                : 'A IA está respondendo de forma autônoma. Clique para pausar e assumir a conversa manualmente'"
              class="px-2.5 py-1 rounded-full text-xs font-bold transition-all cursor-pointer shadow-2xs active:scale-95 flex items-center gap-1.5"
              :class="selectedLead.custom_attributes?.ai_autonomous_paused 
                ? 'bg-amber-100 hover:bg-amber-200 text-amber-900 border border-amber-300' 
                : 'bg-emerald-100 hover:bg-emerald-200 text-emerald-900 border border-emerald-300'"
            >
              <span>{{ selectedLead.custom_attributes?.ai_autonomous_paused ? '👤 Atendimento Humano' : '🤖 SDR Autônomo Ativo' }}</span>
              <span class="text-[10px] font-normal underline opacity-80">
                {{ selectedLead.custom_attributes?.ai_autonomous_paused ? 'Reativar IA' : 'Pausar' }}
              </span>
            </button>

            <!-- Botão de Ação Sandbox: Simular Resposta do Lead -->
            <button 
              type="button"
              @click="showReplySimulator = !showReplySimulator"
              title="Simulador de resposta do Lead (Sandbox)"
              class="px-2.5 py-1 rounded-full text-xs font-bold transition-all cursor-pointer shadow-xs active:scale-95 flex items-center gap-1"
              :class="showReplySimulator ? 'bg-[#130e30] text-[#ffe228]' : 'bg-white hover:bg-[#130e30] hover:text-[#ffe228] text-[#130e30] border border-[#130e30]/15'"
            >
              <span>🧪</span>
              <span class="hidden sm:inline">Simular Lead</span>
            </button>

            <button 
              @click="openMeetingSchedulerFromChat"
              title="Agendar Reunião com esta Conta"
              class="px-3 py-1 rounded-full bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] font-semibold text-xs flex items-center gap-1 border border-[#130e30]/15 transition-all cursor-pointer shadow-xs active:scale-95"
            >
              <span>📅 Agendar Reunião</span>
            </button>
            <button 
              @click="closeChat"
              class="w-8 h-8 rounded-full bg-white hover:bg-[#e4ebd7] text-[#130e30] border border-[#130e30]/15 flex items-center justify-center transition-colors cursor-pointer"
            >
              ✕
            </button>
          </div>
        </div>

        <!-- Painel Sandbox: Simulador de Resposta do Lead (Ambiente de Testes) -->
        <div v-if="showReplySimulator" class="p-3 bg-[#130e30] text-white border-b border-white/10 space-y-2.5 animate-in slide-in-from-top duration-150">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-1.5">
              <span class="text-base">🧪</span>
              <div>
                <p class="text-xs font-bold text-[#ffe228]">Sandbox WhatsApp • Simular Resposta do Lead</p>
                <p class="text-[10px] text-white/70">Dispare uma resposta de <strong>{{ selectedLead.name }}</strong> para validar a pausa de cadência e o avanço no funil.</p>
              </div>
            </div>
            <button 
              type="button" 
              @click="showReplySimulator = false"
              class="text-white/60 hover:text-white text-xs cursor-pointer p-1"
            >
              ✕
            </button>
          </div>

          <!-- Cenários Rápidos de Resposta (1 clique) -->
          <div class="space-y-1.5">
            <span class="text-[10px] uppercase font-bold text-white/60 tracking-wider">Cenários Rápidos (1 clique):</span>
            <div class="grid grid-cols-1 gap-1.5">
              <button 
                type="button"
                :disabled="isSimulatingChatReply"
                @click="simulateLeadResponse('Olá! Vi a sua mensagem e achei muito interessante. Como funciona a demonstração de vocês?')"
                class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-emerald-500/20 hover:border-emerald-400 border border-white/15 text-left text-xs transition-all flex items-center justify-between group cursor-pointer disabled:opacity-50"
              >
                <span class="flex items-center gap-2">
                  <span>🟢</span>
                  <span class="text-white group-hover:text-emerald-300 font-medium">Interesse: "Como funciona a demonstração de vocês?"</span>
                </span>
                <span class="text-[10px] text-white/50 group-hover:text-emerald-300">Enviar ↵</span>
              </button>

              <button 
                type="button"
                :disabled="isSimulatingChatReply"
                @click="simulateLeadResponse('Perfeito! Pode me ligar na próxima quinta-feira às 14h para conversarmos?')"
                class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-blue-500/20 hover:border-blue-400 border border-white/15 text-left text-xs transition-all flex items-center justify-between group cursor-pointer disabled:opacity-50"
              >
                <span class="flex items-center gap-2">
                  <span>📅</span>
                  <span class="text-white group-hover:text-blue-300 font-medium">Aceite de Call: "Pode me ligar na quinta às 14h?"</span>
                </span>
                <span class="text-[10px] text-white/50 group-hover:text-blue-300">Enviar ↵</span>
              </button>

              <button 
                type="button"
                :disabled="isSimulatingChatReply"
                @click="simulateLeadResponse('Qual é a faixa de investimento para a contratação da plataforma?')"
                class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-amber-500/20 hover:border-amber-400 border border-white/15 text-left text-xs transition-all flex items-center justify-between group cursor-pointer disabled:opacity-50"
              >
                <span class="flex items-center gap-2">
                  <span>💰</span>
                  <span class="text-white group-hover:text-amber-300 font-medium">Objeção/Dúvida: "Qual a faixa de investimento?"</span>
                </span>
                <span class="text-[10px] text-white/50 group-hover:text-amber-300">Enviar ↵</span>
              </button>
            </div>
          </div>

          <!-- Input para Resposta Personalizada -->
          <div class="pt-1 border-t border-white/10">
            <form @submit.prevent="submitCustomSimulatedReply" class="flex gap-2">
              <input 
                v-model="customSimulatedText"
                type="text"
                placeholder="Ou digite o que o cliente responderia..."
                class="flex-1 h-8 px-3 text-xs rounded-full bg-white/10 border border-white/20 text-white placeholder:text-white/40 focus:outline-none focus:border-[#ffe228]"
                :disabled="isSimulatingChatReply"
              />
              <button 
                type="submit"
                :disabled="isSimulatingChatReply || !customSimulatedText.trim()"
                class="px-3 h-8 rounded-full bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] font-bold text-xs flex items-center gap-1 cursor-pointer transition-all active:scale-95 disabled:opacity-50 shrink-0"
              >
                <span v-if="!isSimulatingChatReply">Simular</span>
                <span v-else class="animate-spin text-xs">⏳</span>
              </button>
            </form>
          </div>
        </div>

        <!-- JARVIS Copilot IA Integrado ao Chat (Sugestões, Qualificação e Refinamento) -->
        <JarvisCopilotChat 
          :lead="selectedLead" 
          :current-draft="newMessageText"
          @insert-text="(text) => insertTemplate(text)"
          @stage-advanced="handleAiStageAdvanced"
          @notify="handleAiNotify"
        />

        <!-- Chat Messages Area -->
        <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-3 bg-[#f9fbf2]">
          <div v-if="isLoadingMessages" class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-[#130e30]"></div>
          </div>

          <div v-else-if="messages.length === 0" class="flex flex-col items-center justify-center h-full text-[#5f5c6e] text-xs py-10">
            <svg class="w-12 h-12 mb-2 text-[#5f5c6e]/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <p>Nenhuma mensagem trocada ainda.</p>
            <p class="text-[#5f5c6e] mt-1">Envie o primeiro toque abaixo usando Spintax!</p>
          </div>

          <!-- Message Bubbles -->
          <div 
            v-for="msg in messages" 
            :key="msg.id"
            class="flex flex-col"
            :class="msg.direction === 'outbound' ? 'items-end' : 'items-start'"
          >
            <div 
              class="max-w-[85%] rounded-3xl px-4 py-2.5 text-xs leading-relaxed shadow-xs"
              :class="msg.direction === 'outbound' 
                ? 'bg-[#130e30] text-white rounded-br-sm' 
                : 'bg-white text-[#130e30] border border-[#130e30]/10 rounded-bl-sm'"
            >
              <p class="whitespace-pre-line">{{ msg.body }}</p>
              <div 
                class="flex items-center gap-1.5 mt-1 text-[10px]"
                :class="msg.direction === 'outbound' ? 'text-slate-300 justify-end' : 'text-[#5f5c6e] justify-start'"
              >
                <span>{{ formatTime(msg.sent_at || msg.created_at) }}</span>
                <span v-if="msg.direction === 'outbound'">
                  <span v-if="msg.status === 'read'" class="text-[#ffe228] font-bold">✓✓</span>
                  <span v-else-if="msg.status === 'delivered'" class="text-white">✓✓</span>
                  <span v-else>✓</span>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Chat Input / Quick Dispatch Form -->
        <div class="p-4 border-t border-[#130e30]/10 bg-[#eff2e5]">
          <!-- Spintax Quick Templates (Pill Buttons) -->
          <div class="flex gap-1.5 overflow-x-auto pb-2 mb-2 text-[10px]">
            <button 
              type="button"
              @click="scriptPickerTarget = 'whatsapp'; showScriptPickerModal = true" 
              class="px-3 py-1 rounded-full bg-[#130e30] hover:bg-[#20184f] text-[#ffe228] font-bold whitespace-nowrap cursor-pointer transition-all shadow-xs flex items-center gap-1 active:scale-95"
            >
              <span>⚡</span>
              <span>Scripts Salvos ({{ (messageTemplates || []).length }})</span>
            </button>
            <button 
              @click="insertTemplate('{Olá|Oi|Fala}, {nome}! Tudo bem? Vi que atua na {empresa}.')" 
              class="px-3 py-1 rounded-full bg-white hover:bg-[#f9fbf2] text-[#130e30] border border-[#130e30]/15 whitespace-nowrap cursor-pointer transition-colors"
            >
              + Apresentação
            </button>
            <button 
              @click="insertTemplate('{nome}, conseguiu dar uma olhada na mensagem anterior?')" 
              class="px-3 py-1 rounded-full bg-white hover:bg-[#f9fbf2] text-[#130e30] border border-[#130e30]/15 whitespace-nowrap cursor-pointer transition-colors"
            >
              + Follow-up 1
            </button>
            <button 
              @click="insertTemplate('Olá {nome}! Tudo bem? Vi o crescimento da {empresa}. Teria 15 min esta semana para avaliarmos algumas oportunidades? Qual o melhor horário?')" 
              class="px-3 py-1 rounded-full bg-white hover:bg-[#f9fbf2] text-[#130e30] border border-[#130e30]/15 whitespace-nowrap cursor-pointer transition-colors"
            >
              📅 Convite Demo
            </button>
            <button 
              @click="insertMeetingConfirmationTemplate" 
              class="px-3 py-1 rounded-full bg-white hover:bg-[#f9fbf2] text-[#130e30] border border-[#130e30]/15 whitespace-nowrap cursor-pointer transition-colors"
            >
              ✅ Confirmar Reunião
            </button>
          </div>

          <form @submit.prevent="submitMessage" class="flex gap-2">
            <textarea 
              v-model="newMessageText"
              rows="2"
              placeholder="Digite a mensagem (suporta {Olá|Oi} e tags {nome}, {empresa})..."
              class="flex-1 p-3 text-xs rounded-2xl bg-white border border-[#130e30]/20 text-[#130e30] placeholder-[#5f5c6e] focus:outline-none focus:border-[#130e30] transition-colors resize-none"
              :disabled="isSending"
            ></textarea>
            
            <button 
              type="submit"
              :disabled="isSending || !newMessageText.trim()"
              class="px-4 rounded-full bg-[#ffe228] hover:bg-[#fbd71c] active:scale-[0.98] disabled:opacity-50 text-[#130e30] flex items-center justify-center transition-all cursor-pointer shadow-sm"
            >
              <svg v-if="!isSending" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
              </svg>
              <div v-else class="animate-spin rounded-full h-4 w-4 border-b-2 border-[#130e30]"></div>
            </button>
          </form>
          <p class="text-[10px] text-[#5f5c6e] mt-1.5 flex items-center gap-1">
            <span>🛡️ Anti-ban ativo:</span> simula presença humana antes do envio.
          </p>
        </div>
      </div>
    </div>

    <!-- Modal: Detalhes da Empresa e Decisores (Sunlit Atelier Theme) -->
    <div 
      v-if="showCompanyModal && editingCompany" 
      class="fixed inset-0 z-50 flex items-center justify-center bg-[#130e30]/40 backdrop-blur-sm p-4 overflow-y-auto"
      @click.self="showCompanyModal = false"
    >
      <div class="w-full max-w-5xl xl:max-w-6xl rounded-3xl bg-[#f9fbf2] border border-[#130e30]/15 p-6 sm:p-7 shadow-2xl my-8 text-[#130e30]">
        <!-- Modal Header -->
        <div class="flex items-start justify-between pb-4 border-b border-[#130e30]/10 mb-5">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-full bg-[#130e30] flex items-center justify-center font-serif-brand font-bold text-[#ffe228] text-lg shadow-sm">
              {{ companyForm.name.charAt(0) || 'E' }}
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="text-lg font-serif-brand font-bold text-[#130e30]">{{ companyForm.name }}</h3>
                <span 
                  class="text-[10px] font-bold uppercase px-2.5 py-0.5 rounded-full"
                  :class="{
                    'bg-[#eff2e5] text-[#130e30] border border-[#130e30]/15': companyForm.status === 'open',
                    'bg-[#ffe228] text-[#130e30]': companyForm.status === 'won',
                    'bg-[#130e30] text-white': companyForm.status === 'lost',
                    'bg-slate-200 text-[#5f5c6e]': companyForm.status === 'unqualified',
                  }"
                >
                  {{ companyForm.status === 'won' ? 'Ganho' : (companyForm.status === 'lost' ? 'Perdido' : (companyForm.status === 'unqualified' ? 'Desqualificado' : 'Em Aberto')) }}
                </span>
              </div>
              <p class="text-xs text-[#5f5c6e] flex items-center gap-2 mt-0.5">
                <span>{{ (editingCompany.contacts || []).length }} contatos vinculados</span>
                <span class="text-[#5f5c6e]/40">•</span>
                <span class="font-medium text-[#130e30]">{{ (editingCompany.activities || []).length }} atividades registradas</span>
                <span class="text-[#5f5c6e]/40">•</span>
                <span>Conta criada em {{ formatDateTime(editingCompany.created_at) }}</span>
              </p>
            </div>
          </div>
          <button @click="showCompanyModal = false" class="w-8 h-8 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-[#130e30] flex items-center justify-center transition-colors cursor-pointer">✕</button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Coluna 1: Dados da Empresa & Qualificação -->
          <form @submit.prevent="submitCompanyUpdate" class="space-y-3 bg-[#eff2e5] p-4 sm:p-5 rounded-3xl border border-[#130e30]/10">
            <h4 class="text-xs font-serif-brand font-bold text-[#130e30] uppercase tracking-wider flex items-center gap-1.5 pb-1 border-b border-[#130e30]/10">
              Dados da Conta (Empresa)
            </h4>

            <div>
              <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Nome da Empresa *</label>
              <input 
                v-model="companyForm.name" 
                type="text" 
                required
                class="w-full h-9 px-3.5 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30] transition-colors"
              />
            </div>

            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Segmento / Ramo</label>
                <input 
                  v-model="companyForm.segment" 
                  type="text" 
                  placeholder="Ex: Logística"
                  class="w-full h-9 px-3.5 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30] transition-colors"
                />
              </div>

              <div>
                <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">CNPJ</label>
                <div class="relative flex items-center">
                  <input 
                    v-model="companyForm.document" 
                    type="text" 
                    placeholder="00.000.000/0001-00"
                    class="w-full h-9 pl-3.5 pr-20 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30] transition-colors font-mono"
                  />
                  <button 
                    type="button"
                    @click="enrichExistingCompany"
                    :disabled="isEnrichingCompany || !companyForm.document"
                    class="absolute right-1 h-7 px-2.5 rounded-full bg-[#130e30] hover:bg-[#20184f] text-[#ffe228] font-bold text-[10px] flex items-center gap-1 cursor-pointer transition-all active:scale-95 disabled:opacity-50 shadow-2xs"
                    title="Puxar dados cadastrais e sócios da Receita Federal"
                  >
                    <span v-if="!isEnrichingCompany">⚡ Puxar</span>
                    <span v-else class="animate-spin text-[10px]">⏳</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Card de Dados Enriquecidos da Receita Federal (se disponível) -->
            <div 
              v-if="editingCompany?.custom_attributes?.enrichment" 
              class="p-3 rounded-2xl bg-white border border-[#130e30]/10 text-xs space-y-1.5"
            >
              <div class="flex items-center justify-between font-bold text-[11px]">
                <span class="text-[#130e30] truncate">{{ editingCompany.custom_attributes.legal_name || editingCompany.custom_attributes.enrichment.legal_name }}</span>
                <span class="text-[9px] px-2 py-0.2 rounded-full bg-emerald-100 text-emerald-800">
                  {{ editingCompany.custom_attributes.status_receita || editingCompany.custom_attributes.enrichment.status }}
                </span>
              </div>
              <p class="text-[10px] text-[#5f5c6e]">
                <span class="font-semibold text-[#130e30]">Capital:</span> {{ editingCompany.custom_attributes.capital_social || editingCompany.custom_attributes.enrichment.capital_social_formatted }} • {{ editingCompany.custom_attributes.address || editingCompany.custom_attributes.enrichment.full_address }}
              </p>

              <!-- Sócios do QSA disponíveis para importação -->
              <div v-if="editingCompany.custom_attributes.enrichment.partners?.length > 0" class="pt-1.5 border-t border-[#130e30]/8">
                <span class="text-[10px] font-bold text-[#130e30] block mb-1">
                  👥 Sócios no QSA (clique para vincular como contato):
                </span>
                <div class="flex flex-wrap gap-1">
                  <button 
                    v-for="(p, pIdx) in editingCompany.custom_attributes.enrichment.partners" 
                    :key="pIdx"
                    type="button"
                    @click="importPartnerToCompany(p)"
                    class="px-2 py-0.5 rounded-full bg-[#eff2e5] hover:bg-[#ffe228] text-[9px] text-[#130e30] font-semibold border border-[#130e30]/10 transition-colors cursor-pointer flex items-center gap-1 active:scale-95"
                    :title="`Adicionar ${p.name} como contato da conta`"
                  >
                    <span>+ {{ p.name }} ({{ p.job_title }})</span>
                  </button>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Etapa no Funil</label>
                <select 
                  v-model="companyForm.pipeline_stage_id"
                  class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30] transition-colors"
                >
                  <option v-for="stg in stages" :key="stg.id" :value="stg.id">
                    {{ stg.name }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">SDR Responsável</label>
                <select 
                  v-model="companyForm.assigned_user_id"
                  class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30] transition-colors"
                >
                  <option :value="null">Sem responsável</option>
                  <option v-for="u in users" :key="u.id" :value="u.id">
                    {{ u.name }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Status selector (Pill style per DESIGN.md) -->
            <div>
              <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Status da Oportunidade</label>
              <div class="grid grid-cols-2 gap-1.5">
                <button 
                  type="button"
                  @click="companyForm.status = 'open'"
                  class="px-2.5 py-1.5 rounded-full text-xs font-semibold border transition-all cursor-pointer text-center"
                  :class="companyForm.status === 'open' ? 'bg-[#130e30] text-white border-[#130e30]' : 'bg-white text-[#5f5c6e] border-[#130e30]/15 hover:bg-[#f9fbf2]'"
                >
                  Aberto
                </button>
                <button 
                  type="button"
                  @click="companyForm.status = 'won'"
                  class="px-2.5 py-1.5 rounded-full text-xs font-semibold border transition-all cursor-pointer text-center"
                  :class="companyForm.status === 'won' ? 'bg-[#ffe228] text-[#130e30] border-[#130e30]' : 'bg-white text-[#5f5c6e] border-[#130e30]/15 hover:bg-[#f9fbf2]'"
                >
                  ✓ Ganho
                </button>
                <button 
                  type="button"
                  @click="companyForm.status = 'lost'"
                  class="px-2.5 py-1.5 rounded-full text-xs font-semibold border transition-all cursor-pointer text-center"
                  :class="companyForm.status === 'lost' ? 'bg-rose-600 text-white border-rose-600' : 'bg-white text-[#5f5c6e] border-[#130e30]/15 hover:bg-[#f9fbf2]'"
                >
                  ✕ Perdido
                </button>
                <button 
                  type="button"
                  @click="companyForm.status = 'unqualified'"
                  class="px-2.5 py-1.5 rounded-full text-xs font-semibold border transition-all cursor-pointer text-center"
                  :class="companyForm.status === 'unqualified' ? 'bg-[#5f5c6e] text-white border-[#5f5c6e]' : 'bg-white text-[#5f5c6e] border-[#130e30]/15 hover:bg-[#f9fbf2]'"
                >
                  Desqualificado
                </button>
              </div>
            </div>

            <!-- Notes -->
            <div>
              <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Anotações do SDR (Dores, Resumo da Conta)</label>
              <textarea 
                v-model="companyForm.notes" 
                rows="3"
                placeholder="Ex: Empresa possui 3 filiais. Negociação avançando com a diretoria..."
                class="w-full p-3 text-xs rounded-2xl bg-white border border-[#130e30]/20 text-[#130e30] placeholder-[#5f5c6e] focus:outline-none focus:border-[#130e30] transition-colors resize-none"
              ></textarea>
            </div>

            <div class="pt-2 flex justify-end">
              <button 
                type="submit" 
                :disabled="isSavingCompany"
                class="px-5 py-2 rounded-full bg-[#130e30] hover:bg-[#251d4d] active:scale-[0.98] disabled:opacity-50 text-xs font-medium text-white cursor-pointer transition-all"
              >
                {{ isSavingCompany ? 'Salvando...' : 'Salvar Dados da Empresa' }}
              </button>
            </div>
          </form>

          <!-- Coluna 2: Abas (Atividades & Histórico e Decisores da Empresa) -->
          <div class="bg-[#eff2e5] p-4 sm:p-5 rounded-3xl border border-[#130e30]/10 flex flex-col justify-between min-h-[480px]">
            <div>
              <!-- Seletor de Abas Responsivo e Fluido -->
              <div class="w-full overflow-x-auto pb-1 mb-3">
                <div class="inline-flex items-center gap-1 p-1 bg-white rounded-full border border-[#130e30]/10 min-w-full sm:min-w-0 shadow-2xs">
                  <button 
                    type="button"
                    @click="activeCompanyTab = 'activities'"
                    class="px-3 py-1 rounded-full text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5 whitespace-nowrap shrink-0"
                    :class="activeCompanyTab === 'activities' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#5f5c6e] hover:text-[#130e30]'"
                  >
                    <span>📋 Histórico</span>
                    <span 
                      class="px-1.5 py-0.2 rounded-full text-[10px]" 
                      :class="activeCompanyTab === 'activities' ? 'bg-white/20 text-white' : 'bg-[#eff2e5] text-[#130e30]'"
                    >
                      {{ (editingCompany.activities || []).length }}
                    </span>
                  </button>

                  <button 
                    type="button"
                    @click="activeCompanyTab = 'contacts'"
                    class="px-3 py-1 rounded-full text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5 whitespace-nowrap shrink-0"
                    :class="activeCompanyTab === 'contacts' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#5f5c6e] hover:text-[#130e30]'"
                  >
                    <span>👥 Decisores</span>
                    <span 
                      class="px-1.5 py-0.2 rounded-full text-[10px]" 
                      :class="activeCompanyTab === 'contacts' ? 'bg-white/20 text-white' : 'bg-[#eff2e5] text-[#130e30]'"
                    >
                      {{ (editingCompany.contacts || []).length }}
                    </span>
                  </button>

                  <button 
                    type="button"
                    @click="activeCompanyTab = 'meeting'"
                    title="Agendamento e Qualificação da Reunião"
                    class="px-3 py-1 rounded-full text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5 whitespace-nowrap shrink-0"
                    :class="activeCompanyTab === 'meeting' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#5f5c6e] hover:text-[#130e30]'"
                  >
                    <span>📅 Reunião</span>
                    <span 
                      v-if="editingCompany.meeting_at"
                      class="w-2 h-2 rounded-full bg-[#ffe228] border border-[#130e30]/30" 
                      title="Reunião Agendada"
                    ></span>
                  </button>

                  <button 
                    type="button" 
                    @click="activeCompanyTab = 'tasks'"
                    title="Tarefas e Lembretes da Conta"
                    class="px-3 py-1 rounded-full text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5 whitespace-nowrap shrink-0"
                    :class="activeCompanyTab === 'tasks' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#5f5c6e] hover:text-[#130e30]'"
                  >
                    <span>📌 Tarefas</span>
                    <span 
                      class="px-1.5 py-0.2 rounded-full text-[10px]" 
                      :class="activeCompanyTab === 'tasks' ? 'bg-[#ffe228] text-[#130e30] font-bold' : 'bg-[#eff2e5] text-[#130e30]'"
                    >
                      {{ (editingCompany.tasks || []).filter(t => t.status === 'pending').length }}
                    </span>
                  </button>

                  <button 
                    type="button" 
                    @click="activeCompanyTab = 'cadence'"
                    title="Cadência e Régua de Contato"
                    class="px-3 py-1 rounded-full text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5 whitespace-nowrap shrink-0"
                    :class="activeCompanyTab === 'cadence' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#5f5c6e] hover:text-[#130e30]'"
                  >
                    <span>⚡ Cadência</span>
                    <span 
                      v-if="editingCompany.active_cadence"
                      class="px-1.5 py-0.2 rounded-full text-[9px] font-bold" 
                      :class="activeCompanyTab === 'cadence' ? 'bg-[#ffe228] text-[#130e30]' : 'bg-[#ffe228] text-[#130e30]'"
                    >
                      P{{ editingCompany.active_cadence.current_step_number }}
                    </span>
                  </button>
                </div>
              </div>

              <!-- ================= ABA 1: ATIVIDADES & HISTÓRICO ================= -->
              <div v-if="activeCompanyTab === 'activities'" class="space-y-3">
                <!-- Formulário: Registrar Atividade -->
                <form @submit.prevent="submitAddActivity" class="p-3.5 rounded-3xl bg-white border border-[#130e30]/10 space-y-2.5 shadow-xs">
                  <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-[#130e30] flex items-center gap-1">
                      <span>⚡ Registrar Nova Atividade</span>
                    </p>
                    <button 
                      type="button" 
                      @click="openEmailComposer(editingCompany)"
                      class="px-2.5 py-0.5 rounded-full bg-[#eff2e5] hover:bg-white text-[#130e30] border border-[#130e30]/15 text-[10px] font-semibold flex items-center gap-1 cursor-pointer transition-colors shadow-2xs"
                    >
                      <span>✉️ Enviar E-mail</span>
                    </button>
                  </div>

                  <!-- Seletor de Tipo com Botões Pill -->
                  <div class="grid grid-cols-4 gap-1.5">
                    <button 
                      type="button"
                      @click="newActivityForm.type = 'call'"
                      class="px-2 py-1.5 rounded-full text-[11px] font-semibold border transition-all cursor-pointer text-center flex items-center justify-center gap-1"
                      :class="newActivityForm.type === 'call' ? 'bg-[#ffe228] text-[#130e30] border-[#130e30]' : 'bg-[#f9fbf2] text-[#5f5c6e] border-[#130e30]/15 hover:bg-[#eff2e5]'"
                    >
                      <span>📞 Ligação</span>
                    </button>
                    <button 
                      type="button"
                      @click="newActivityForm.type = 'note'"
                      class="px-2 py-1.5 rounded-full text-[11px] font-semibold border transition-all cursor-pointer text-center flex items-center justify-center gap-1"
                      :class="newActivityForm.type === 'note' ? 'bg-[#ffe228] text-[#130e30] border-[#130e30]' : 'bg-[#f9fbf2] text-[#5f5c6e] border-[#130e30]/15 hover:bg-[#eff2e5]'"
                    >
                      <span>📝 Nota</span>
                    </button>
                    <button 
                      type="button"
                      @click="newActivityForm.type = 'meeting'"
                      class="px-2 py-1.5 rounded-full text-[11px] font-semibold border transition-all cursor-pointer text-center flex items-center justify-center gap-1"
                      :class="newActivityForm.type === 'meeting' ? 'bg-[#ffe228] text-[#130e30] border-[#130e30]' : 'bg-[#f9fbf2] text-[#5f5c6e] border-[#130e30]/15 hover:bg-[#eff2e5]'"
                    >
                      <span>📅 Reunião</span>
                    </button>
                    <button 
                      type="button"
                      @click="newActivityForm.type = 'whatsapp'"
                      class="px-2 py-1.5 rounded-full text-[11px] font-semibold border transition-all cursor-pointer text-center flex items-center justify-center gap-1"
                      :class="newActivityForm.type === 'whatsapp' ? 'bg-[#ffe228] text-[#130e30] border-[#130e30]' : 'bg-[#f9fbf2] text-[#5f5c6e] border-[#130e30]/15 hover:bg-[#eff2e5]'"
                    >
                      <span>💬 Whats</span>
                    </button>
                  </div>

                  <!-- Campos específicos de Ligação -->
                  <div v-if="newActivityForm.type === 'call'" class="grid grid-cols-2 gap-2">
                    <div>
                      <label class="block text-[10px] font-semibold text-[#5f5c6e] mb-0.5">Desfecho da Chamada *</label>
                      <select 
                        v-model="newActivityForm.call_outcome"
                        class="w-full h-8 px-2.5 text-[11px] rounded-full bg-[#f9fbf2] border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
                      >
                        <option value="connected">✅ Falou com Decisor</option>
                        <option value="meeting_scheduled">📅 Reunião Agendada</option>
                        <option value="no_answer">🚫 Não Atendeu / Ocupado</option>
                        <option value="voicemail">📼 Caixa Postal</option>
                        <option value="gatekeeper">🛑 Secretária / Bloqueado</option>
                        <option value="wrong_number">❌ Número Incorreto</option>
                      </select>
                    </div>

                    <div>
                      <label class="block text-[10px] font-semibold text-[#5f5c6e] mb-0.5">Decisor Contatado</label>
                      <select 
                        v-model="newActivityForm.lead_id"
                        class="w-full h-8 px-2.5 text-[11px] rounded-full bg-[#f9fbf2] border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
                      >
                        <option :value="null">Geral da Conta</option>
                        <option v-for="c in (editingCompany.contacts || [])" :key="c.id" :value="c.id">
                          {{ c.name }} {{ c.job_title ? `(${c.job_title})` : '' }}
                        </option>
                      </select>
                    </div>
                  </div>

                  <!-- Contato opcional para Nota / Reunião -->
                  <div v-else-if="(editingCompany.contacts || []).length > 0">
                    <label class="block text-[10px] font-semibold text-[#5f5c6e] mb-0.5">Decisor Envolvido (Opcional)</label>
                    <select 
                      v-model="newActivityForm.lead_id"
                      class="w-full h-8 px-2.5 text-[11px] rounded-full bg-[#f9fbf2] border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
                    >
                      <option :value="null">Geral da Conta</option>
                      <option v-for="c in (editingCompany.contacts || [])" :key="c.id" :value="c.id">
                        {{ c.name }} {{ c.job_title ? `(${c.job_title})` : '' }}
                      </option>
                    </select>
                  </div>

                  <!-- Campo de Resumo / Anotação -->
                  <div>
                    <textarea 
                      v-model="newActivityForm.description"
                      rows="2"
                      required
                      :placeholder="newActivityForm.type === 'call' ? 'Resumo da ligação: o que foi conversado, objeções, próximos passos...' : 'Escreva a observação, feedback ou histórico da conta...'"
                      class="w-full p-2.5 text-xs rounded-2xl bg-[#f9fbf2] border border-[#130e30]/20 text-[#130e30] placeholder-[#5f5c6e] focus:outline-none focus:border-[#130e30] resize-none"
                    ></textarea>
                  </div>

                  <div class="flex justify-end">
                    <button 
                      type="submit"
                      :disabled="isAddingActivity || !newActivityForm.description.trim()"
                      class="px-4 py-1.5 rounded-full bg-[#130e30] hover:bg-[#251d4d] active:scale-[0.98] disabled:opacity-50 text-xs font-medium text-white cursor-pointer transition-all flex items-center gap-1.5"
                    >
                      <span>{{ isAddingActivity ? 'Registrando...' : '+ Registrar Atividade' }}</span>
                    </button>
                  </div>
                </form>

                <!-- Timeline de Atividades -->
                <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                  <div 
                    v-for="act in (editingCompany.activities || [])" 
                    :key="act.id"
                    class="p-3 rounded-2xl bg-white border border-[#130e30]/10 text-xs space-y-1.5 transition-colors shadow-xs"
                  >
                    <div class="flex items-center justify-between gap-2">
                      <div class="flex items-center gap-1.5 flex-wrap">
                        <!-- Badge de Tipo (Warm Sunlit Pills) -->
                        <span 
                          class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider flex items-center gap-1 border border-[#130e30]/10 bg-[#eff2e5] text-[#130e30]"
                        >
                          <span v-if="act.type === 'call'">📞 Ligação</span>
                          <span v-else-if="act.type === 'meeting'">📅 Reunião</span>
                          <span v-else-if="act.type === 'whatsapp'">💬 WhatsApp</span>
                          <span v-else-if="act.type === 'email'">✉️ E-mail</span>
                          <span v-else>📝 Nota</span>
                        </span>

                        <!-- Badge de Desfecho de Chamada -->
                        <span 
                          v-if="act.call_outcome" 
                          class="px-2 py-0.5 rounded-full text-[10px] font-semibold"
                          :class="formatOutcomeBadge(act.call_outcome).class"
                        >
                          {{ formatOutcomeBadge(act.call_outcome).label }}
                        </span>

                        <!-- Decisor relacionado -->
                        <span v-if="act.lead" class="text-[11px] text-[#5f5c6e] font-medium">
                          c/ <strong class="text-[#130e30]">{{ act.lead.name }}</strong>
                          <span v-if="act.lead.job_title" class="text-[#5f5c6e] text-[10px]"> ({{ act.lead.job_title }})</span>
                        </span>
                      </div>

                      <!-- Excluir Atividade -->
                      <button 
                        @click="deleteActivity(act.id)"
                        title="Remover atividade"
                        class="text-[#5f5c6e] hover:text-rose-600 transition-colors p-0.5 cursor-pointer"
                      >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>

                    <!-- Conteúdo / Anotação -->
                    <p class="text-[#130e30] whitespace-pre-line leading-relaxed text-[11px] pl-2 border-l-2 border-[#130e30]/20">
                      {{ act.description }}
                    </p>

                    <!-- Rodapé: SDR e Data/Hora -->
                    <div class="flex items-center justify-between text-[10px] text-[#5f5c6e] pt-0.5">
                      <span>Registrado por <span class="font-medium text-[#130e30]">{{ act.user?.name || 'SDR' }}</span></span>
                      <span>{{ formatDateTimeWithHour(act.created_at) }}</span>
                    </div>
                  </div>

                  <!-- Empty State Atividades -->
                  <div v-if="!(editingCompany.activities || []).length" class="text-center py-6 px-3 bg-white/60 rounded-3xl border border-dashed border-[#130e30]/20">
                    <p class="text-xs text-[#130e30] font-medium">Nenhum histórico registrado ainda</p>
                    <p class="text-[10px] text-[#5f5c6e] mt-1">
                      Use o formulário acima para registrar ligações, reuniões ou observações do SDR.
                    </p>
                  </div>
                </div>
              </div>

              <!-- ================= ABA 2: DECISORES DA EMPRESA ================= -->
              <div v-else-if="activeCompanyTab === 'contacts'" class="space-y-3">
                <!-- Cabeçalho de Ações da Aba de Decisores -->
                <div class="flex items-center justify-between pb-2 mb-1 border-b border-[#130e30]/10">
                  <div class="flex items-center gap-2">
                    <span class="text-xs font-serif-brand font-bold text-[#130e30]">Decisores e Contatos da Conta</span>
                    <span class="px-2 py-0.5 rounded-full bg-[#130e30] text-[#ffe228] text-[10px] font-bold">
                      {{ (editingCompany.contacts || []).length }}
                    </span>
                  </div>
                  <button 
                    type="button" 
                    @click="showAddContactBox = !showAddContactBox"
                    class="px-3 py-1 rounded-full bg-[#130e30] hover:bg-[#20184f] text-[#ffe228] text-xs font-bold transition-all cursor-pointer shadow-xs active:scale-95 flex items-center gap-1.5"
                  >
                    <span>{{ showAddContactBox ? '✕ Fechar Formulário' : '+ Adicionar Decisor' }}</span>
                  </button>
                </div>

                <!-- Form: Adicionar Novo Decisor -->
                <form v-if="showAddContactBox" @submit.prevent="submitAddContact" class="p-3.5 mb-3 rounded-3xl bg-white border border-[#130e30]/10 space-y-2 shadow-xs">
                  <p class="text-[11px] font-bold text-[#130e30]">Novo Contato / Decisor</p>
                  <div class="grid grid-cols-2 gap-2">
                    <input 
                      v-model="newContactForm.name" 
                      type="text" 
                      required 
                      placeholder="Nome *" 
                      class="h-8 px-3 text-xs rounded-full bg-[#f9fbf2] border border-[#130e30]/20 text-[#130e30]"
                    />
                    <input 
                      v-model="newContactForm.job_title" 
                      type="text" 
                      placeholder="Cargo (ex: CEO)" 
                      class="h-8 px-3 text-xs rounded-full bg-[#f9fbf2] border border-[#130e30]/20 text-[#130e30]"
                    />
                  </div>
                  <div class="grid grid-cols-2 gap-2">
                    <input 
                      v-model="newContactForm.phone" 
                      type="text" 
                      required 
                      placeholder="WhatsApp *" 
                      class="h-8 px-3 text-xs rounded-full bg-[#f9fbf2] border border-[#130e30]/20 text-[#130e30]"
                    />
                    <input 
                      v-model="newContactForm.email" 
                      type="email" 
                      placeholder="E-mail" 
                      class="h-8 px-3 text-xs rounded-full bg-[#f9fbf2] border border-[#130e30]/20 text-[#130e30]"
                    />
                  </div>
                  <div class="flex justify-end pt-1">
                    <button 
                      type="submit" 
                      :disabled="isAddingContact"
                      class="px-4 py-1.5 rounded-full bg-[#130e30] hover:bg-[#251d4d] text-[11px] font-semibold text-white cursor-pointer active:scale-[0.98]"
                    >
                      {{ isAddingContact ? 'Salvando...' : 'Salvar Contato' }}
                    </button>
                  </div>
                </form>

                <!-- Lista de Contatos -->
                <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                  <div 
                    v-for="contact in editingCompany.contacts" 
                    :key="contact.id"
                    class="p-3 rounded-2xl bg-white border border-[#130e30]/10 flex items-center justify-between gap-2 shadow-xs"
                  >
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center gap-1.5">
                        <span class="text-xs font-bold text-[#130e30]">{{ contact.name }}</span>
                        <span v-if="contact.job_title" class="text-[10px] text-[#5f5c6e] font-medium">({{ contact.job_title }})</span>
                      </div>
                      <div class="flex items-center gap-2 text-[10px] text-[#5f5c6e] mt-0.5">
                        <span class="font-mono">{{ formatPhone(contact.phone) }}</span>
                        <span v-if="contact.email">• {{ contact.email }}</span>
                      </div>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0">
                      <!-- Email Composer button -->
                      <button 
                        v-if="contact.email"
                        @click="openEmailComposer(editingCompany, contact)"
                        title="Enviar e-mail via Gmail"
                        class="px-3 py-1 rounded-full bg-white hover:bg-[#eff2e5] text-[#130e30] border border-[#130e30]/15 text-[11px] font-semibold flex items-center gap-1 cursor-pointer active:scale-[0.98]"
                      >
                        <svg class="w-3 h-3 text-[#130e30]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>E-mail</span>
                      </button>

                      <!-- Webphone Call button -->
                      <button 
                        v-if="contact.phone"
                        type="button"
                        @click="triggerCall(contact.phone, editingCompany, contact)"
                        title="Ligar com Webphone FreePBX"
                        class="px-3 py-1 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-semibold flex items-center gap-1 cursor-pointer active:scale-[0.98] shadow-2xs"
                      >
                        <span>📞</span>
                        <span>Ligar</span>
                      </button>

                      <!-- WhatsApp Chat button (Hi-Yellow Pill) -->
                      <button 
                        @click="openChatFromCompany(contact)"
                        title="Conversar no WhatsApp"
                        class="px-3 py-1 rounded-full bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] border border-[#130e30]/15 text-[11px] font-semibold flex items-center gap-1 cursor-pointer active:scale-[0.98]"
                      >
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                          <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.074-1.127-.065-.272-.087-.624-.22-1.077-.417-1.912-.833-3.155-2.771-3.25-2.899-.095-.127-.775-1.029-.775-1.963 0-.933.491-1.393.666-1.584.175-.19.382-.239.509-.239.127 0 .254.001.365.006.117.006.273-.044.428.328.159.38.541 1.317.588 1.413.047.095.079.206.016.333-.063.127-.095.206-.19.317-.095.111-.2.248-.286.333-.095.095-.195.198-.084.388.111.19.493.813 1.057 1.316.726.646 1.339.847 1.53.942.19.095.302.079.413-.048.111-.127.476-.556.603-.746.127-.19.254-.159.428-.095.175.063 1.111.524 1.302.619.19.095.317.143.365.222.048.079.048.46-.096.865z"/>
                        </svg>
                        <span>Conversar</span>
                      </button>
                    </div>
                  </div>

                  <div v-if="!editingCompany.contacts || editingCompany.contacts.length === 0" class="text-xs text-[#5f5c6e] text-center py-4">
                    Nenhum decisor cadastrado nesta empresa ainda.
                  </div>
                </div>
              </div>

              <!-- ================= ABA 3: AGENDAMENTO DE REUNIÃO & QUALIFICAÇÃO RÁPIDA ================= -->
              <div v-else-if="activeCompanyTab === 'meeting'" class="space-y-3">
                <!-- Se já houver reunião agendada, exibe banner informativo -->
                <div v-if="editingCompany.meeting_at" class="p-3.5 rounded-3xl bg-white border border-[#130e30]/15 space-y-2 shadow-xs">
                  <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#130e30] flex items-center gap-1.5">
                      <span class="w-2 h-2 rounded-full bg-[#59e25d]"></span>
                      <span>Reunião Confirmada</span>
                    </span>
                    <span class="text-[10px] uppercase font-bold px-2.5 py-0.5 rounded-full bg-[#ffe228] text-[#130e30] border border-[#130e30]/10">
                      Agendada
                    </span>
                  </div>

                  <div class="text-xs text-[#130e30] space-y-1">
                    <p><strong>Data & Horário:</strong> {{ formatDateTimeWithHour(editingCompany.meeting_at) }}</p>
                    <p v-if="editingCompany.meeting_link" class="flex items-center gap-1.5">
                      <strong>Link da Sala:</strong>
                      <a :href="editingCompany.meeting_link" target="_blank" class="text-[#130e30] underline font-medium hover:text-black truncate max-w-xs flex items-center gap-0.5">
                        <span>{{ editingCompany.meeting_link }}</span>
                        <span>↗</span>
                      </a>
                    </p>
                    <div v-if="editingCompany.custom_attributes?.qualification_notes" class="text-[11px] text-[#5f5c6e] mt-1 bg-[#f9fbf2] p-2.5 rounded-2xl border border-[#130e30]/10">
                      <strong class="text-[#130e30] block mb-0.5">Resumo de Qualificação:</strong>
                      <p class="whitespace-pre-line leading-relaxed">{{ editingCompany.custom_attributes.qualification_notes }}</p>
                    </div>
                  </div>
                </div>

                <!-- Formulário de Agendamento & Qualificação Rápida -->
                <form @submit.prevent="submitScheduleMeeting" class="p-3.5 rounded-3xl bg-white border border-[#130e30]/10 space-y-3 shadow-xs">
                  <div>
                    <p class="text-xs font-bold text-[#130e30] flex items-center gap-1.5">
                      <span>📅</span>
                      <span>{{ editingCompany.meeting_at ? 'Alterar / Reagendar Reunião' : 'Agendar Demonstração / Reunião' }}</span>
                    </p>
                    <p class="text-[10px] text-[#5f5c6e]">Ao agendar, move automaticamente para "Reunião Marcada" e grava na timeline</p>
                  </div>

                  <!-- Data/Hora e Decisor Responsável -->
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div>
                      <label class="block text-[10px] font-semibold text-[#5f5c6e] mb-0.5">Data e Horário *</label>
                      <input 
                        v-model="scheduleMeetingForm.meeting_at" 
                        type="datetime-local" 
                        required
                        class="w-full h-8 px-2.5 text-[11px] rounded-full bg-[#f9fbf2] border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
                      />
                    </div>

                    <div>
                      <label class="block text-[10px] font-semibold text-[#5f5c6e] mb-0.5">Decisor Principal</label>
                      <select 
                        v-model="scheduleMeetingForm.lead_id"
                        class="w-full h-8 px-2.5 text-[11px] rounded-full bg-[#f9fbf2] border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
                      >
                        <option :value="null">Geral da Conta</option>
                        <option v-for="c in (editingCompany.contacts || [])" :key="c.id" :value="c.id">
                          {{ c.name }} {{ c.job_title ? `(${c.job_title})` : '' }}
                        </option>
                      </select>
                    </div>
                  </div>

                  <!-- Link da Videochamada -->
                  <div>
                    <label class="block text-[10px] font-semibold text-[#5f5c6e] mb-0.5">Link da Sala (Google Meet / Zoom / Teams)</label>
                    <div class="relative">
                      <input 
                        v-model="scheduleMeetingForm.meeting_link" 
                        type="url" 
                        placeholder="https://meet.google.com/abc-defg-hij" 
                        class="w-full h-8 pl-8 pr-3 text-[11px] rounded-full bg-[#f9fbf2] border border-[#130e30]/20 text-[#130e30] placeholder-[#5f5c6e] focus:outline-none focus:border-[#130e30]"
                      />
                      <span class="absolute left-2.5 top-2 text-[11px]">🔗</span>
                    </div>
                  </div>

                  <!-- Qualificação Rápida / Atalhos de Dores -->
                  <div>
                    <div class="flex items-center justify-between mb-1">
                      <label class="block text-[10px] font-semibold text-[#5f5c6e]">Qualificação Rápida (Dores & BANT)</label>
                      <span class="text-[9px] text-[#5f5c6e]">Clique para inserir</span>
                    </div>
                    <div class="flex gap-1 flex-wrap mb-1.5 text-[10px]">
                      <button 
                        type="button" 
                        @click="appendPainTag('Falta de previsibilidade de vendas')" 
                        class="px-2 py-0.5 rounded-full bg-[#eff2e5] hover:bg-white text-[#130e30] border border-[#130e30]/10 cursor-pointer transition-colors"
                      >
                        + Sem previsibilidade
                      </button>
                      <button 
                        type="button" 
                        @click="appendPainTag('Prospecção outbound 100% manual')" 
                        class="px-2 py-0.5 rounded-full bg-[#eff2e5] hover:bg-white text-[#130e30] border border-[#130e30]/10 cursor-pointer transition-colors"
                      >
                        + Prospecção manual
                      </button>
                      <button 
                        type="button" 
                        @click="appendPainTag('Decisor com orçamento aprovado')" 
                        class="px-2 py-0.5 rounded-full bg-[#eff2e5] hover:bg-white text-[#130e30] border border-[#130e30]/10 cursor-pointer transition-colors"
                      >
                        + Orçamento OK
                      </button>
                      <button 
                        type="button" 
                        @click="appendPainTag('Urgência: Início imediato')" 
                        class="px-2 py-0.5 rounded-full bg-[#eff2e5] hover:bg-white text-[#130e30] border border-[#130e30]/10 cursor-pointer transition-colors"
                      >
                        + Urgência imediata
                      </button>
                    </div>
                    <textarea 
                      v-model="scheduleMeetingForm.qualification_notes" 
                      rows="2"
                      placeholder="Ex: Decisor busca ferramenta para automatizar primeiro toque e qualificação..."
                      class="w-full p-2.5 text-xs rounded-2xl bg-[#f9fbf2] border border-[#130e30]/20 text-[#130e30] placeholder-[#5f5c6e] focus:outline-none focus:border-[#130e30] resize-none"
                    ></textarea>
                  </div>

                  <!-- Botão de Ação -->
                  <div class="flex items-center justify-between pt-1">
                    <span class="text-[10px] text-[#5f5c6e]">Sincroniza etapa e timeline</span>
                    <button 
                      type="submit" 
                      :disabled="isSchedulingMeeting || !scheduleMeetingForm.meeting_at"
                      class="px-5 py-2 rounded-full bg-[#ffe228] hover:bg-[#fad81d] active:scale-[0.98] disabled:opacity-50 text-xs font-bold text-[#130e30] cursor-pointer transition-all flex items-center gap-1.5 shadow-sm"
                    >
                      <div v-if="isSchedulingMeeting" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-[#130e30]"></div>
                      <span>{{ isSchedulingMeeting ? 'Agendando...' : 'Confirmar Agendamento' }}</span>
                    </button>
                  </div>
                </form>
              </div>

              <!-- ================= ABA 4: TAREFAS & LEMBRETES ================= -->
              <div v-else-if="activeCompanyTab === 'tasks'" class="space-y-3">
                <!-- Form: Nova Tarefa -->
                <form @submit.prevent="submitAddTask" class="p-3.5 rounded-3xl bg-white border border-[#130e30]/10 space-y-2.5 shadow-xs">
                  <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-[#130e30] flex items-center gap-1">
                      <span>📌 Criar Nova Tarefa / Lembrete</span>
                    </p>
                    <span class="text-[10px] text-[#5f5c6e]">Gera lembrete no card</span>
                  </div>

                  <!-- Título da Tarefa -->
                  <div>
                    <input 
                      v-model="newTaskForm.title" 
                      type="text" 
                      required 
                      placeholder="O que precisa ser feito? (ex: Ligar para confirmar proposta...)" 
                      class="w-full h-8 px-3 text-xs rounded-full bg-[#f9fbf2] border border-[#130e30]/20 text-[#130e30] placeholder-[#5f5c6e] focus:outline-none focus:border-[#130e30]"
                    />
                  </div>

                  <!-- Seletor de Tipo e Prioridade -->
                  <div class="grid grid-cols-2 gap-2">
                    <div>
                      <label class="block text-[10px] font-semibold text-[#5f5c6e] mb-0.5">Tipo de Ação</label>
                      <select 
                        v-model="newTaskForm.type"
                        class="w-full h-8 px-2.5 text-[11px] rounded-full bg-[#f9fbf2] border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
                      >
                        <option value="call">📞 Ligação</option>
                        <option value="whatsapp">💬 WhatsApp</option>
                        <option value="email">✉️ E-mail</option>
                        <option value="meeting">🤝 Reunião</option>
                        <option value="followup">⚡ Follow-up</option>
                        <option value="other">📝 Outro</option>
                      </select>
                    </div>

                    <div>
                      <label class="block text-[10px] font-semibold text-[#5f5c6e] mb-0.5">Prioridade</label>
                      <select 
                        v-model="newTaskForm.priority"
                        class="w-full h-8 px-2.5 text-[11px] rounded-full bg-[#f9fbf2] border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
                      >
                        <option value="low">Baixa</option>
                        <option value="medium">Média</option>
                        <option value="high">Alta (Urgente)</option>
                      </select>
                    </div>
                  </div>

                  <!-- Data/Hora do Lembrete e Contato Vinculado -->
                  <div class="grid grid-cols-2 gap-2">
                    <div>
                      <label class="block text-[10px] font-semibold text-[#5f5c6e] mb-0.5">Lembrar em (Data e Hora)</label>
                      <input 
                        v-model="newTaskForm.due_at" 
                        type="datetime-local" 
                        class="w-full h-8 px-2 text-[11px] rounded-full bg-[#f9fbf2] border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
                      />
                    </div>

                    <div>
                      <label class="block text-[10px] font-semibold text-[#5f5c6e] mb-0.5">Decisor (Opcional)</label>
                      <select 
                        v-model="newTaskForm.lead_id"
                        class="w-full h-8 px-2 text-[11px] rounded-full bg-[#f9fbf2] border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
                      >
                        <option :value="null">Geral da Conta</option>
                        <option v-for="c in (editingCompany.contacts || [])" :key="c.id" :value="c.id">
                          {{ c.name }} {{ c.job_title ? `(${c.job_title})` : '' }}
                        </option>
                      </select>
                    </div>
                  </div>

                  <!-- Descrição Opcional -->
                  <div>
                    <textarea 
                      v-model="newTaskForm.description" 
                      rows="2"
                      placeholder="Detalhes ou anotações adicionais..."
                      class="w-full p-2.5 text-[11px] rounded-2xl bg-[#f9fbf2] border border-[#130e30]/20 text-[#130e30] placeholder-[#5f5c6e] focus:outline-none focus:border-[#130e30] resize-none"
                    ></textarea>
                  </div>

                  <!-- Botão de Salvar Tarefa -->
                  <div class="flex justify-end pt-1">
                    <button 
                      type="submit" 
                      :disabled="isAddingTask || !newTaskForm.title.trim()"
                      class="px-4 py-1.5 rounded-full bg-[#ffe228] hover:bg-[#fad81d] text-[11px] font-bold text-[#130e30] cursor-pointer transition-all flex items-center gap-1.5 shadow-2xs active:scale-95 disabled:opacity-50"
                    >
                      <div v-if="isAddingTask" class="animate-spin rounded-full h-3 w-3 border-b-2 border-[#130e30]"></div>
                      <span>+ Adicionar Tarefa</span>
                    </button>
                  </div>
                </form>

                <!-- Lista de Tarefas da Empresa -->
                <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                  <!-- Tarefas Pendentes -->
                  <div 
                    v-for="task in (editingCompany.tasks || []).filter(t => t.status === 'pending')" 
                    :key="task.id"
                    class="p-3 rounded-2xl bg-white border border-[#130e30]/10 flex items-start justify-between gap-2.5 shadow-xs hover:border-[#130e30]/30 transition-all group"
                  >
                    <div class="flex items-start gap-2.5 min-w-0 flex-1">
                      <!-- Checkbox customizado circular -->
                      <button 
                        type="button" 
                        @click="toggleTaskStatus(task)" 
                        title="Marcar como concluída"
                        class="w-5 h-5 rounded-full border-2 border-[#130e30]/30 hover:border-[#130e30] hover:bg-[#ffe228]/20 flex items-center justify-center shrink-0 mt-0.5 cursor-pointer transition-colors"
                      >
                      </button>

                      <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 flex-wrap">
                          <span class="text-xs font-bold text-[#130e30]">{{ task.title }}</span>
                          <span 
                            class="text-[9px] font-bold px-2 py-0.2 rounded-full uppercase"
                            :class="{
                              'bg-rose-100 text-rose-800 border border-rose-200': task.priority === 'high',
                              'bg-amber-100 text-amber-800 border border-amber-200': task.priority === 'medium',
                              'bg-[#eff2e5] text-[#5f5c6e] border border-[#130e30]/10': task.priority === 'low',
                            }"
                          >
                            {{ task.priority === 'high' ? 'Alta' : (task.priority === 'medium' ? 'Média' : 'Baixa') }}
                          </span>
                        </div>

                        <!-- Data de vencimento com indicador visual de status -->
                        <div class="flex items-center gap-2 text-[10px] mt-1 flex-wrap">
                          <span 
                            v-if="task.due_at" 
                            class="font-medium px-2 py-0.5 rounded-full"
                            :class="isTaskOverdue(task.due_at) ? 'bg-rose-50 text-rose-700 font-bold' : (isTaskToday(task.due_at) ? 'bg-[#ffe228] text-[#130e30] font-bold' : 'text-[#5f5c6e] bg-[#f9fbf2]')"
                          >
                            ⏰ {{ formatTaskDue(task.due_at) }}
                          </span>
                          <span v-if="task.lead" class="text-[#5f5c6e]">
                            👤 {{ task.lead.name }}
                          </span>
                        </div>

                        <p v-if="task.description" class="text-[11px] text-[#5f5c6e] mt-1 leading-relaxed bg-[#f9fbf2] p-2 rounded-xl border border-[#130e30]/5">
                          {{ task.description }}
                        </p>
                      </div>
                    </div>

                    <!-- Botão de Deletar Tarefa -->
                    <button 
                      type="button" 
                      @click="deleteTask(task)" 
                      title="Excluir tarefa"
                      class="text-[#5f5c6e]/40 hover:text-rose-600 p-1 text-xs cursor-pointer opacity-0 group-hover:opacity-100 transition-opacity shrink-0"
                    >
                      ✕
                    </button>
                  </div>

                  <!-- Tarefas Concluídas -->
                  <div v-if="(editingCompany.tasks || []).some(t => t.status === 'completed')" class="pt-2">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#5f5c6e] mb-1.5 flex items-center gap-1">
                      <span>✓ Concluídas ({{ (editingCompany.tasks || []).filter(t => t.status === 'completed').length }})</span>
                    </p>
                    <div class="space-y-1.5">
                      <div 
                        v-for="task in (editingCompany.tasks || []).filter(t => t.status === 'completed')" 
                        :key="task.id"
                        class="p-2.5 rounded-2xl bg-white/60 border border-[#130e30]/5 flex items-center justify-between gap-2 text-xs opacity-70 hover:opacity-100 transition-opacity"
                      >
                        <div class="flex items-center gap-2 min-w-0 flex-1">
                          <button 
                            type="button" 
                            @click="toggleTaskStatus(task)" 
                            title="Reabrir tarefa"
                            class="w-4 h-4 rounded-full bg-[#130e30] text-[#ffe228] flex items-center justify-center text-[10px] font-bold shrink-0 cursor-pointer"
                          >
                            ✓
                          </button>
                          <span class="line-through text-[#5f5c6e] truncate font-medium">{{ task.title }}</span>
                        </div>
                        <span class="text-[9px] text-[#5f5c6e]">Concluída</span>
                      </div>
                    </div>
                  </div>

                  <!-- Empty State Tarefas -->
                  <div 
                    v-if="!(editingCompany.tasks || []).length" 
                    class="h-28 rounded-2xl border border-dashed border-[#130e30]/15 flex flex-col items-center justify-center text-[#5f5c6e] text-xs"
                  >
                    <span class="text-base mb-1">📌</span>
                    <span class="font-medium">Nenhuma tarefa cadastrada</span>
                    <span class="text-[10px] text-[#5f5c6e]/70 mt-0.5">Crie um lembrete para nunca esquecer um follow-up</span>
                  </div>
                </div>
              </div>

              <!-- ================= ABA 5: CADÊNCIA & RÉGUA DE CONTATO DA CONTA ================= -->
              <div v-else-if="activeCompanyTab === 'cadence'" class="space-y-4">
                <!-- Se possui Cadência Ativa -->
                <div v-if="editingCompany.active_cadence" class="space-y-4">
                  <!-- Header do Status da Cadência -->
                  <div class="p-4 rounded-3xl bg-[#eff2e5] border border-[#130e30]/15 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                      <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-[#130e30] text-[#ffe228] flex items-center justify-center font-bold text-sm">
                          ⚡
                        </div>
                        <div>
                          <h4 class="text-xs font-bold text-[#130e30]">
                            {{ editingCompany.active_cadence.cadence?.name }}
                          </h4>
                          <p class="text-[10px] text-[#5f5c6e]">
                            Iniciada em {{ formatDate(editingCompany.active_cadence.started_at) }} • SDR: {{ editingCompany.active_cadence.user?.name || 'Automático' }}
                          </p>
                        </div>
                      </div>

                      <div class="flex items-center gap-2 self-start sm:self-auto flex-wrap">
                        <button 
                          type="button" 
                          @click="simulateLeadReply(editingCompany.id)"
                          :disabled="isSimulatingReply"
                          class="px-3 py-1 rounded-full bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] text-xs font-bold transition-all shadow-2xs cursor-pointer flex items-center gap-1.5"
                          title="Simula o lead respondendo no WhatsApp/E-mail para testar a pausa automática da cadência"
                        >
                          <span>🎯</span>
                          <span>{{ isSimulatingReply ? 'Simulando...' : 'Simular Resposta' }}</span>
                        </button>

                        <button 
                          type="button" 
                          @click="unenrollCompanyFromCadence(editingCompany.id)"
                          :disabled="isEnrollingCadence"
                          class="px-3 py-1 rounded-full bg-white hover:bg-rose-100 hover:text-rose-700 text-rose-600 border border-[#130e30]/10 text-xs font-bold transition-colors cursor-pointer"
                        >
                          ✕ Interromper Cadência
                        </button>
                      </div>
                    </div>

                    <!-- Barra de Progresso do Passo Atual -->
                    <div class="space-y-1 pt-1">
                      <div class="flex justify-between text-[11px] font-bold text-[#130e30]">
                        <span>Progresso na Sequência</span>
                        <span>
                          Passo {{ editingCompany.active_cadence.current_step_number }} de {{ (editingCompany.active_cadence.cadence?.steps || []).length }}
                        </span>
                      </div>
                      <div class="w-full bg-white rounded-full h-2.5 overflow-hidden border border-[#130e30]/10">
                        <div 
                          class="bg-[#130e30] h-2.5 rounded-full transition-all duration-500" 
                          :style="{ width: Math.min(100, Math.round((editingCompany.active_cadence.current_step_number / Math.max(1, (editingCompany.active_cadence.cadence?.steps || []).length)) * 100)) + '%' }"
                        ></div>
                      </div>
                    </div>
                  </div>

                  <!-- Timeline dos Passos da Cadência para esta Empresa -->
                  <div class="space-y-2">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#5f5c6e]">Passos da Régua de Abordagem:</p>
                    <div class="space-y-2">
                      <div 
                        v-for="step in (editingCompany.active_cadence.cadence?.steps || [])" 
                        :key="step.id"
                        class="p-3.5 rounded-2xl border transition-all"
                        :class="{
                          'bg-emerald-50/60 border-emerald-200': step.step_number < editingCompany.active_cadence.current_step_number,
                          'bg-white border-[#130e30] shadow-sm ring-1 ring-[#130e30]/20': step.step_number === editingCompany.active_cadence.current_step_number,
                          'bg-[#f9fbf2] border-[#130e30]/10 opacity-60': step.step_number > editingCompany.active_cadence.current_step_number,
                        }"
                      >
                        <div class="flex items-center justify-between gap-2">
                          <div class="flex items-center gap-2">
                            <span 
                              class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0"
                              :class="{
                                'bg-emerald-600 text-white': step.step_number < editingCompany.active_cadence.current_step_number,
                                'bg-[#130e30] text-[#ffe228]': step.step_number === editingCompany.active_cadence.current_step_number,
                                'bg-[#eff2e5] text-[#5f5c6e]': step.step_number > editingCompany.active_cadence.current_step_number,
                              }"
                            >
                              {{ step.step_number < editingCompany.active_cadence.current_step_number ? '✓' : step.step_number }}
                            </span>
                            <span class="text-xs font-bold text-[#130e30]">
                              {{ step.task_type === 'whatsapp' ? '💬 WhatsApp' : (step.task_type === 'email' ? '✉️ E-mail' : (step.task_type === 'call' ? '📞 Ligação' : '📅 Reunião')) }}: {{ step.title }}
                            </span>
                          </div>

                          <span 
                            class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                            :class="{
                              'bg-emerald-100 text-emerald-800': step.step_number < editingCompany.active_cadence.current_step_number,
                              'bg-[#ffe228] text-[#130e30]': step.step_number === editingCompany.active_cadence.current_step_number,
                              'bg-[#eff2e5] text-[#5f5c6e]': step.step_number > editingCompany.active_cadence.current_step_number,
                            }"
                          >
                            {{ step.step_number < editingCompany.active_cadence.current_step_number ? 'Concluído' : (step.step_number === editingCompany.active_cadence.current_step_number ? 'Em Andamento' : 'Aguardando') }}
                          </span>
                        </div>

                        <!-- Template do passo atual em destaque -->
                        <div v-if="step.step_number === editingCompany.active_cadence.current_step_number && step.template_content" class="mt-2.5 pt-2 border-t border-[#130e30]/10">
                          <p class="text-[10px] font-bold text-[#5f5c6e] mb-1">Roteiro / Mensagem Sugerida:</p>
                          <div class="p-2.5 rounded-xl bg-[#eff2e5] border border-[#130e30]/5 text-xs text-[#130e30] font-mono leading-relaxed whitespace-pre-wrap">
                            {{ step.template_content }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Se NÃO possui Cadência Ativa: Seletor de Matrícula -->
                <div v-else class="space-y-4">
                  <!-- Alerta se o lead respondeu à cadência anterior -->
                  <div 
                    v-if="editingCompany.latest_cadence && editingCompany.latest_cadence.status === 'replied'" 
                    class="p-4 rounded-3xl bg-emerald-50 border border-emerald-200 text-emerald-950 flex items-start gap-3 shadow-2xs"
                  >
                    <span class="text-2xl shrink-0">🎯</span>
                    <div class="text-xs space-y-1">
                      <p class="font-bold text-emerald-900 text-sm">Lead Respondeu à Prospecção!</p>
                      <p class="text-emerald-800 leading-relaxed">
                        A cadência anterior (<strong>{{ editingCompany.latest_cadence.cadence?.name }}</strong>) foi pausada automaticamente no Passo {{ editingCompany.latest_cadence.current_step_number }} para atendimento humanizado. As tarefas automáticas frias foram canceladas.
                      </p>
                      <p class="text-[11px] text-emerald-700/80 pt-1 font-medium">
                        Se desejar matricular esta conta em uma nova régua (ex: Pós-Reunião ou Negociação), selecione uma opção abaixo:
                      </p>
                    </div>
                  </div>

                  <div v-else class="p-4 rounded-3xl bg-[#eff2e5] border border-[#130e30]/15 space-y-2 text-center">
                    <span class="text-2xl">⚡</span>
                    <h4 class="text-sm font-bold text-[#130e30]">Nenhuma cadência ativa para esta conta</h4>
                    <p class="text-xs text-[#5f5c6e] max-w-md mx-auto">
                      Matricule esta empresa em uma régua de contato para que o SDR JARVIS agende automaticamente as tarefas de abordagem multicanal com os prazos ideais.
                    </p>
                  </div>

                  <div class="space-y-2">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#5f5c6e]">Escolha uma Cadência:</p>
                    <div class="grid grid-cols-1 gap-2.5">
                      <div 
                        v-for="cad in (cadences || [])" 
                        :key="cad.id"
                        @click="selectedCadenceToEnroll = cad.id"
                        class="p-4 rounded-2xl border transition-all cursor-pointer flex items-center justify-between gap-3"
                        :class="selectedCadenceToEnroll === cad.id ? 'bg-white border-[#130e30] ring-2 ring-[#130e30] shadow-sm' : 'bg-white/70 border-[#130e30]/10 hover:border-[#130e30]/30'"
                      >
                        <div class="flex items-center gap-3">
                          <div class="w-8 h-8 rounded-xl bg-[#130e30] text-[#ffe228] flex items-center justify-center font-bold text-sm shrink-0">
                            ⚡
                          </div>
                          <div>
                            <div class="flex items-center gap-2">
                              <h5 class="text-xs font-bold text-[#130e30]">{{ cad.name }}</h5>
                              <span v-if="cad.target_segment" class="text-[9px] font-medium px-2 py-0.2 rounded-full bg-[#eff2e5] text-[#5f5c6e]">
                                {{ cad.target_segment }}
                              </span>
                            </div>
                            <p class="text-[11px] text-[#5f5c6e] mt-0.5 line-clamp-1">{{ cad.description }}</p>
                          </div>
                        </div>

                        <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-[#eff2e5] text-[#130e30] shrink-0">
                          {{ (cad.steps || []).length }} passos
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Botão Iniciar Cadência -->
                  <div class="pt-2">
                    <button 
                      type="button" 
                      @click="enrollCompanyInCadence(editingCompany.id, selectedCadenceToEnroll)"
                      :disabled="!selectedCadenceToEnroll || isEnrollingCadence"
                      class="w-full h-11 rounded-full bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] text-xs font-bold flex items-center justify-center gap-2 shadow-xs transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <span>🚀 Iniciar Cadência & Gerar Passo 1</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Delete Company -->
            <div class="pt-3 border-t border-[#130e30]/10 flex justify-between items-center mt-3">
              <span class="text-[10px] text-[#5f5c6e]">Excluir empresa remove contatos e histórico associados.</span>
              <button 
                type="button" 
                @click="deleteCompany(editingCompany.id)"
                :disabled="isDeletingCompany"
                class="px-3 py-1 text-xs text-rose-700 hover:bg-rose-100 rounded-full border border-rose-300 cursor-pointer transition-colors"
              >
                Excluir Conta
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Nova Cadência Personalizada (Warm Meadow Style) -->
    <div 
      v-if="showNewCadenceModal" 
      class="fixed inset-0 z-50 flex items-center justify-center bg-[#130e30]/40 backdrop-blur-sm p-4 overflow-y-auto"
      @click.self="showNewCadenceModal = false"
    >
      <div class="w-full max-w-2xl max-h-[90vh] flex flex-col rounded-3xl bg-[#f9fbf2] border border-[#130e30]/15 p-6 shadow-2xl text-[#130e30]">
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 border-b border-[#130e30]/10 shrink-0">
          <div>
            <h3 class="text-base font-serif-brand font-bold text-[#130e30] flex items-center gap-2">
              <span>⚡</span>
              <span>Criar Nova Régua / Cadência</span>
            </h3>
            <p class="text-xs text-[#5f5c6e]">Defina os passos, intervalos e templates automáticos de abordagem.</p>
          </div>
          <button @click="showNewCadenceModal = false" class="w-8 h-8 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-[#130e30] flex items-center justify-center cursor-pointer">✕</button>
        </div>

        <!-- Body Form -->
        <div class="flex-1 overflow-y-auto py-4 space-y-4 pr-1">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-bold uppercase tracking-wider text-[#5f5c6e] mb-1">Nome da Cadência *</label>
              <input 
                v-model="newCadenceForm.name"
                type="text" 
                placeholder="Ex: Hunter Outbound Frio (7 Dias)" 
                class="w-full h-10 px-3 text-xs rounded-xl bg-white border border-[#130e30]/20 focus:border-[#130e30] focus:outline-none"
              />
            </div>
            <div>
              <label class="block text-[11px] font-bold uppercase tracking-wider text-[#5f5c6e] mb-1">Segmento / Objetivo</label>
              <input 
                v-model="newCadenceForm.target_segment"
                type="text" 
                placeholder="Ex: Tecnologia / SaaS B2B" 
                class="w-full h-10 px-3 text-xs rounded-xl bg-white border border-[#130e30]/20 focus:border-[#130e30] focus:outline-none"
              />
            </div>
          </div>

          <div>
            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#5f5c6e] mb-1">Descrição da Estratégia</label>
            <textarea 
              v-model="newCadenceForm.description"
              rows="2"
              placeholder="Explique o objetivo desta sequência para o time..."
              class="w-full p-2.5 text-xs rounded-xl bg-white border border-[#130e30]/20 focus:border-[#130e30] focus:outline-none resize-none"
            ></textarea>
          </div>

          <!-- Passos da Cadência -->
          <div class="space-y-3 pt-2 border-t border-[#130e30]/10">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="text-xs font-bold text-[#130e30]">Passos da Régua ({{ newCadenceForm.steps.length }})</h4>
                <p class="text-[10px] text-[#5f5c6e]">Cada passo gera uma tarefa no prazo programado.</p>
              </div>
              <button 
                type="button" 
                @click="addStepToNewCadence"
                class="h-8 px-3 rounded-full bg-[#130e30] text-[#ffe228] text-xs font-bold flex items-center gap-1 cursor-pointer shadow-2xs hover:bg-[#201948]"
              >
                + Adicionar Passo
              </button>
            </div>

            <!-- Steps List -->
            <div class="space-y-3">
              <div 
                v-for="(step, idx) in newCadenceForm.steps" 
                :key="idx"
                class="p-3.5 rounded-2xl bg-white border border-[#130e30]/15 space-y-2.5 relative group shadow-2xs"
              >
                <div class="flex items-center justify-between gap-2">
                  <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#130e30] text-white text-xs font-bold flex items-center justify-center shrink-0">
                      {{ step.step_number }}
                    </span>
                    <input 
                      v-model="step.title"
                      type="text" 
                      placeholder="Título do passo..."
                      class="h-8 px-2.5 text-xs font-bold rounded-lg border border-[#130e30]/20 focus:border-[#130e30] focus:outline-none w-56 sm:w-64"
                    />
                  </div>

                  <div class="flex items-center gap-2">
                    <button 
                      v-if="newCadenceForm.steps.length > 1"
                      type="button" 
                      @click="removeStepFromNewCadence(idx)"
                      class="text-xs text-rose-500 hover:text-rose-700 p-1 cursor-pointer"
                      title="Remover passo"
                    >
                      ✕
                    </button>
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                  <div>
                    <label class="block text-[10px] font-bold text-[#5f5c6e] mb-0.5">Canal</label>
                    <select 
                      v-model="step.task_type" 
                      class="w-full h-8 px-2 text-xs rounded-lg border border-[#130e30]/20 bg-[#f9fbf2]"
                    >
                      <option value="whatsapp">💬 WhatsApp</option>
                      <option value="email">✉️ E-mail</option>
                      <option value="call">📞 Ligação</option>
                      <option value="meeting">📅 Reunião</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-[10px] font-bold text-[#5f5c6e] mb-0.5 truncate">
                      {{ step.step_number === 1 ? 'Executar em (0 = Imediato)' : 'Aguardar após passo anterior' }}
                    </label>
                    <div class="flex items-center gap-1.5">
                      <input 
                        v-model.number="step.delay_value" 
                        type="number" 
                        min="0" 
                        placeholder="0"
                        class="w-16 h-8 px-2 text-xs font-bold rounded-lg border border-[#130e30]/20 bg-[#f9fbf2] focus:border-[#130e30] focus:outline-none"
                      />
                      <select 
                        v-model="step.delay_unit" 
                        class="flex-1 h-8 px-2 text-xs rounded-lg border border-[#130e30]/20 bg-[#f9fbf2] font-medium"
                      >
                        <option value="seconds">Segundos</option>
                        <option value="minutes">Minutos</option>
                        <option value="hours">Horas</option>
                        <option value="days">Dias</option>
                      </select>
                    </div>
                  </div>
                  <div>
                    <label class="block text-[10px] font-bold text-[#5f5c6e] mb-0.5">Prioridade</label>
                    <select 
                      v-model="step.priority" 
                      class="w-full h-8 px-2 text-xs rounded-lg border border-[#130e30]/20 bg-[#f9fbf2]"
                    >
                      <option value="high">Alta</option>
                      <option value="medium">Média</option>
                      <option value="low">Baixa</option>
                    </select>
                  </div>
                </div>

                <div>
                  <label class="block text-[10px] font-bold text-[#5f5c6e] mb-0.5 flex items-center justify-between">
                    <span>Template de Mensagem / Script sugerido:</span>
                    <span class="text-[9px] text-[#5f5c6e]/70">Tags: &#123;&#123;decisor&#125;&#125;, &#123;&#123;empresa&#125;&#125;, &#123;&#123;cargo&#125;&#125;</span>
                  </label>
                  <textarea 
                    v-model="step.template_content"
                    rows="2"
                    placeholder="Olá {{decisor}}, tudo bem? Notei que a {{empresa}}..."
                    class="w-full p-2 text-xs font-mono rounded-lg border border-[#130e30]/15 bg-[#f9fbf2] focus:outline-none resize-none leading-relaxed"
                  ></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="pt-3 border-t border-[#130e30]/10 flex items-center justify-end gap-2 shrink-0">
          <button 
            type="button" 
            @click="showNewCadenceModal = false"
            class="px-4 py-2 rounded-full text-xs font-medium text-[#5f5c6e] hover:bg-[#eff2e5] cursor-pointer"
          >
            Cancelar
          </button>
          <button 
            type="button" 
            @click="submitNewCadence"
            :disabled="isSavingCadence"
            class="px-5 py-2 rounded-full bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] text-xs font-bold cursor-pointer transition-transform active:scale-95 disabled:opacity-50"
          >
            {{ isSavingCadence ? 'Salvando...' : 'Salvar Cadência' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal: Novo Lead / Nova Empresa (Warm Atelier Style) -->
    <div 
      v-if="showNewLeadModal" 
      class="fixed inset-0 z-50 flex items-center justify-center bg-[#130e30]/40 backdrop-blur-sm p-4"
      @click.self="showNewLeadModal = false"
    >
      <div class="w-full max-w-md rounded-3xl bg-[#f9fbf2] border border-[#130e30]/15 p-6 shadow-2xl text-[#130e30]">
        <div class="flex items-center justify-between pb-3 border-b border-[#130e30]/10 mb-4">
          <h3 class="text-base font-serif-brand font-bold text-[#130e30] flex items-center gap-2">
            Cadastrar Nova Conta / Empresa
          </h3>
          <button @click="showNewLeadModal = false" class="w-8 h-8 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-[#130e30] flex items-center justify-center cursor-pointer">✕</button>
        </div>

        <!-- Busca e Enriquecimento Automático por CNPJ -->
        <div class="p-3 rounded-2xl bg-[#eff2e5] border border-[#130e30]/15 space-y-2 mb-3">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold text-[#130e30] flex items-center gap-1.5">
              <span>⚡</span>
              <span>Puxar Dados da Receita Federal (CNPJ)</span>
            </label>
            <span v-if="isEnriching" class="text-[10px] text-[#5f5c6e] flex items-center gap-1">
              <div class="animate-spin rounded-full h-3 w-3 border-b-2 border-[#130e30]"></div>
              Consultando...
            </span>
          </div>

          <div class="flex gap-2">
            <input 
              v-model="cnpjSearchQuery" 
              type="text" 
              placeholder="00.000.000/0001-00 (ou somente números)"
              class="flex-1 h-9 px-3.5 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] placeholder-[#5f5c6e] focus:outline-none focus:border-[#130e30]"
              @keydown.enter.prevent="searchCnpjData"
            />
            <button 
              type="button"
              @click="searchCnpjData"
              :disabled="isEnriching || !cnpjSearchQuery.trim()"
              class="px-4 h-9 rounded-full bg-[#130e30] hover:bg-[#231b54] text-[#ffe228] font-bold text-xs flex items-center gap-1 cursor-pointer transition-all active:scale-95 disabled:opacity-50 shadow-xs shrink-0"
            >
              <span>Buscar</span>
            </button>
          </div>

          <!-- Card de Resultado do Enriquecimento -->
          <div v-if="enrichedData" class="p-2.5 rounded-xl bg-white border border-[#130e30]/10 text-xs space-y-1.5 mt-2 animate-in fade-in">
            <div class="flex items-center justify-between text-[11px] font-bold">
              <span class="text-[#130e30] truncate">{{ enrichedData.legal_name }}</span>
              <span class="text-[9px] px-2 py-0.2 rounded-full bg-emerald-100 text-emerald-800">{{ enrichedData.status }}</span>
            </div>
            <p class="text-[11px] text-[#5f5c6e]">
              <span class="font-semibold text-[#130e30]">CNAE:</span> {{ enrichedData.segment }}
            </p>
            <p class="text-[10px] text-[#5f5c6e]">
              <span class="font-semibold text-[#130e30]">Capital:</span> {{ enrichedData.capital_social_formatted }} • {{ enrichedData.full_address }}
            </p>

            <!-- Lista de Sócios / QSA para escolher como Decisor -->
            <div v-if="enrichedData.partners && enrichedData.partners.length > 0" class="pt-2 border-t border-[#130e30]/10">
              <span class="text-[10px] font-bold text-[#130e30] block mb-1">
                👥 Sócios / Decisores do QSA (clique para selecionar):
              </span>
              <div class="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto pr-1">
                <button 
                  v-for="(partner, idx) in enrichedData.partners" 
                  :key="idx"
                  type="button"
                  @click="selectPartnerAsLead(partner)"
                  class="px-2.5 py-1 rounded-full bg-[#eff2e5] hover:bg-[#ffe228] border border-[#130e30]/15 text-[10px] text-[#130e30] font-medium transition-all cursor-pointer flex items-center gap-1 active:scale-95"
                  :title="`Usar ${partner.name} como decisor principal`"
                >
                  <span>👤</span>
                  <span class="font-bold">{{ partner.name }}</span>
                  <span class="text-[#5f5c6e] text-[9px]">({{ partner.job_title }})</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <form @submit.prevent="submitNewLead" class="space-y-3.5">
          <div>
            <label class="block text-xs font-semibold text-[#5f5c6e] mb-1">Nome da Empresa *</label>
            <input 
              v-model="newLeadForm.company_name" 
              type="text" 
              required
              placeholder="Ex: Comercial Ramos LTDA"
              class="w-full h-9 px-3.5 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] placeholder-[#5f5c6e] focus:outline-none focus:border-[#130e30] transition-colors"
            />
          </div>

          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-xs font-semibold text-[#5f5c6e] mb-1">Nome do Decisor *</label>
              <input 
                v-model="newLeadForm.name" 
                type="text" 
                required
                placeholder="Ex: Roberto Gomes"
                class="w-full h-9 px-3.5 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] placeholder-[#5f5c6e] focus:outline-none focus:border-[#130e30] transition-colors"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-[#5f5c6e] mb-1">Cargo</label>
              <input 
                v-model="newLeadForm.job_title" 
                type="text" 
                placeholder="Ex: Diretor Comercial"
                class="w-full h-9 px-3.5 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] placeholder-[#5f5c6e] focus:outline-none focus:border-[#130e30] transition-colors"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-xs font-semibold text-[#5f5c6e] mb-1">WhatsApp *</label>
              <input 
                v-model="newLeadForm.phone" 
                type="text" 
                required
                placeholder="5511998887766"
                class="w-full h-9 px-3.5 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] placeholder-[#5f5c6e] focus:outline-none focus:border-[#130e30] transition-colors"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-[#5f5c6e] mb-1">E-mail</label>
              <input 
                v-model="newLeadForm.email" 
                type="email" 
                placeholder="roberto@empresa.com"
                class="w-full h-9 px-3.5 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] placeholder-[#5f5c6e] focus:outline-none focus:border-[#130e30] transition-colors"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-[#5f5c6e] mb-1">Etapa Inicial no Funil *</label>
            <select 
              v-model="newLeadForm.pipeline_stage_id"
              required
              class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30] transition-colors"
            >
              <option v-for="stg in stages" :key="stg.id" :value="stg.id">
                {{ stg.name }}
              </option>
            </select>
          </div>

          <div class="pt-2 flex justify-end gap-2">
            <button 
              type="button" 
              @click="showNewLeadModal = false"
              class="px-4 py-2 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-xs font-medium text-[#130e30] cursor-pointer transition-colors"
            >
              Cancelar
            </button>
            <button 
              type="submit"
              :disabled="isSubmittingLead"
              class="px-5 py-2 rounded-full bg-[#ffe228] hover:bg-[#fad81d] disabled:opacity-50 text-xs font-semibold text-[#130e30] cursor-pointer transition-all active:scale-[0.98]"
            >
              <span>Salvar Conta</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Importar Leads (CSV) (Sunlit Atelier Theme) -->
    <div 
      v-if="showImportModal" 
      class="fixed inset-0 z-50 flex items-center justify-center bg-[#130e30]/40 backdrop-blur-sm p-4"
      @click.self="showImportModal = false"
    >
      <div class="w-full max-w-lg rounded-3xl bg-[#f9fbf2] border border-[#130e30]/15 p-6 shadow-2xl text-[#130e30]">
        <div class="flex items-center justify-between pb-3 border-b border-[#130e30]/10 mb-4">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-full bg-[#ffe228] flex items-center justify-center text-[#130e30]">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
              </svg>
            </div>
            <div>
              <h3 class="text-base font-serif-brand font-bold text-[#130e30]">Importar Planilha B2B (CSV)</h3>
              <p class="text-[11px] text-[#5f5c6e]">Mapeia múltiplos decisores sob a mesma empresa automaticamente</p>
            </div>
          </div>
          <button @click="showImportModal = false" class="w-8 h-8 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-[#130e30] flex items-center justify-center cursor-pointer">✕</button>
        </div>

        <form @submit.prevent="submitImport" class="space-y-4">
          <!-- Etapa de Destino -->
          <div>
            <label class="block text-xs font-semibold text-[#5f5c6e] mb-1">Etapa de Destino no Funil *</label>
            <select 
              v-model="importForm.pipeline_stage_id"
              required
              class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30] transition-colors"
            >
              <option v-for="stg in stages" :key="stg.id" :value="stg.id">
                {{ stg.name }}
              </option>
            </select>
          </div>

          <!-- File Upload Zone -->
          <div>
            <label class="block text-xs font-semibold text-[#5f5c6e] mb-1">Arquivo CSV / TXT *</label>
            <div 
              class="relative border-2 border-dashed border-[#130e30]/20 hover:border-[#130e30]/40 rounded-3xl p-5 text-center bg-white transition-colors cursor-pointer group"
              @click="$refs.fileInput.click()"
            >
              <input 
                ref="fileInput" 
                type="file" 
                accept=".csv,.txt" 
                class="hidden" 
                @change="handleFileSelect"
              />
              <svg class="w-8 h-8 mx-auto text-[#5f5c6e] group-hover:text-[#130e30] transition-colors mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <p v-if="!importForm.file" class="text-xs text-[#130e30] font-medium">
                Clique para selecionar ou arraste o arquivo CSV
              </p>
              <p v-else class="text-xs text-[#130e30] font-semibold flex items-center justify-center gap-1.5">
                <span>📄 {{ importForm.file.name }}</span>
                <span class="text-[#5f5c6e] font-normal">({{ (importForm.file.size / 1024).toFixed(1) }} KB)</span>
              </p>
              <p class="text-[10px] text-[#5f5c6e] mt-1">Contatos com a mesma empresa serão agrupados sob o mesmo card!</p>
            </div>
          </div>

          <!-- Helper Box & Sample Template -->
          <div class="rounded-2xl bg-[#eff2e5] border border-[#130e30]/10 p-3 text-[11px] text-[#5f5c6e] space-y-1.5">
            <div class="flex items-center justify-between">
              <span class="font-semibold text-[#130e30]">Colunas recomendadas no cabeçalho:</span>
              <button 
                type="button" 
                @click="downloadSampleCsv" 
                class="text-[#130e30] font-medium underline flex items-center gap-1 cursor-pointer"
              >
                <span>Baixar modelo CSV</span>
              </button>
            </div>
            <code class="block font-mono bg-white px-2.5 py-1.5 rounded-lg text-[#130e30] text-[10px] select-all border border-[#130e30]/10">
              empresa;nome;cargo;telefone;email;setor
            </code>
          </div>

          <!-- Buttons -->
          <div class="pt-2 flex justify-end gap-2">
            <button 
              type="button" 
              @click="showImportModal = false"
              class="px-4 py-2 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-xs font-medium text-[#130e30] cursor-pointer transition-colors"
            >
              Cancelar
            </button>
            <button 
              type="submit"
              :disabled="isImporting || !importForm.file"
              class="px-5 py-2 rounded-full bg-[#ffe228] hover:bg-[#fad81d] active:scale-[0.98] disabled:opacity-50 text-xs font-semibold text-[#130e30] cursor-pointer transition-all flex items-center gap-1.5"
            >
              <div v-if="isImporting" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-[#130e30]"></div>
              <span>{{ isImporting ? 'Processando...' : 'Iniciar Importação' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Conectar Conta Google / Gmail (HubSpot Style) -->
    <div 
      v-if="showGmailModal" 
      class="fixed inset-0 z-50 flex items-center justify-center bg-[#130e30]/40 backdrop-blur-sm p-4 overflow-y-auto"
      @click.self="showGmailModal = false"
    >
      <div class="w-full max-w-lg rounded-3xl bg-[#f9fbf2] border border-[#130e30]/15 p-6 shadow-2xl text-[#130e30]">
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 border-b border-[#130e30]/10 mb-4">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-full bg-white border border-[#130e30]/10 flex items-center justify-center shadow-xs">
              <svg class="w-5 h-5" viewBox="0 0 24 24">
                <path fill="#EA4335" d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.3 9 5 12 5z"/>
                <path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.7-.2-2.3H12v4.6h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.9z"/>
                <path fill="#FBBC05" d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.8s.2-2.1.4-2.8L1.9 6.3C.7 8.7 0 10.3 0 12s.7 3.3 1.9 5.7l3.7-2.9z"/>
                <path fill="#34A853" d="M12 23c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2.3-6.4-5.2L1.9 16C3.7 19.7 7.5 23 12 23z"/>
              </svg>
            </div>
            <div>
              <h3 class="text-base font-serif-brand font-bold text-[#130e30]">Conectar Conta do Gmail</h3>
              <p class="text-[11px] text-[#5f5c6e]">Integração oficial via Google OAuth 2.0 (estilo HubSpot)</p>
            </div>
          </div>
          <button @click="showGmailModal = false" class="w-8 h-8 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-[#130e30] flex items-center justify-center cursor-pointer">✕</button>
        </div>

        <div class="space-y-4">
          <!-- Benefícios da Conexão -->
          <div class="p-3.5 rounded-2xl bg-white border border-[#130e30]/10 text-xs space-y-2">
            <p class="font-bold text-[#130e30]">Por que vincular sua caixa de entrada?</p>
            <ul class="text-[11px] text-[#5f5c6e] space-y-1.5 list-disc pl-4">
              <li>Envie e-mails manuais direto dos cards sem sair do Kanban.</li>
              <li>Histórico de envio e respostas registrado automaticamente na timeline da empresa.</li>
              <li>Entregabilidade máxima saindo com SPF/DKIM oficial do seu próprio Gmail / Google Workspace.</li>
              <li>Pronto para sequências e cadências automáticas de outbound futuros.</li>
            </ul>
          </div>

          <!-- Status Atual / Contas Conectadas -->
          <div v-if="emailAccounts && emailAccounts.length > 0" class="space-y-2">
            <label class="block text-xs font-semibold text-[#5f5c6e]">Contas Vinculadas</label>
            <div 
              v-for="acc in emailAccounts" 
              :key="acc.id"
              class="p-3 rounded-2xl bg-white border border-[#130e30]/15 flex items-center justify-between gap-3 shadow-xs"
            >
              <div class="flex items-center gap-2.5 min-w-0">
                <img v-if="acc.avatar_url" :src="acc.avatar_url" class="w-8 h-8 rounded-full border" />
                <div v-else class="w-8 h-8 rounded-full bg-[#130e30] text-white flex items-center justify-center font-bold text-xs">
                  {{ acc.email.charAt(0).toUpperCase() }}
                </div>
                <div class="min-w-0">
                  <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold text-[#130e30] truncate">{{ acc.name || acc.email }}</span>
                    <span class="text-[9px] font-bold uppercase px-2 py-0.5 rounded-full bg-[#eff2e5] text-[#59e25d] border border-[#130e30]/10">
                      🟢 Ativo
                    </span>
                  </div>
                  <p class="text-[10px] text-[#5f5c6e] truncate">{{ acc.email }} • {{ acc.sent_today || 0 }}/{{ acc.daily_limit || 100 }} envios hoje</p>
                </div>
              </div>

              <button 
                @click="disconnectGoogleAccount(acc.id)"
                class="px-3 py-1 rounded-full text-[11px] text-rose-700 hover:bg-rose-100 border border-rose-200 cursor-pointer transition-colors shrink-0"
              >
                Desconectar
              </button>
            </div>
          </div>

          <!-- Botão Oficial Conectar com o Google -->
          <div class="pt-2">
            <a 
              href="/auth/google/redirect"
              class="w-full py-3 px-5 rounded-full bg-white hover:bg-[#f9fbf2] border-2 border-[#130e30] flex items-center justify-center gap-3 font-semibold text-xs text-[#130e30] shadow-sm active:scale-[0.99] transition-all cursor-pointer group"
            >
              <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                <path fill="#EA4335" d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.3 9 5 12 5z"/>
                <path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.7-.2-2.3H12v4.6h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.9z"/>
                <path fill="#FBBC05" d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.8s.2-2.1.4-2.8L1.9 6.3C.7 8.7 0 10.3 0 12s.7 3.3 1.9 5.7l3.7-2.9z"/>
                <path fill="#34A853" d="M12 23c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2.3-6.4-5.2L1.9 16C3.7 19.7 7.5 23 12 23z"/>
              </svg>
              <span>{{ (emailAccounts && emailAccounts.length > 0) ? '+ Conectar Outra Conta do Google' : 'Conectar com o Google (Gmail)' }}</span>
              <span class="text-xs group-hover:translate-x-0.5 transition-transform">→</span>
            </a>
          </div>

          <!-- Configurações Opcionais de Credenciais do Google Cloud Console -->
          <div class="border-t border-[#130e30]/10 pt-3">
            <details class="text-xs group">
              <summary class="font-semibold text-[#5f5c6e] hover:text-[#130e30] cursor-pointer flex items-center justify-between">
                <span>⚙️ Configurar Client ID / Secret do Google Cloud</span>
                <span class="text-[10px] text-[#5f5c6e] underline">Configurar chaves</span>
              </summary>
              <form @submit.prevent="submitSaveGoogleCredentials" class="mt-3 p-3.5 rounded-2xl bg-[#eff2e5] border border-[#130e30]/10 space-y-2.5">
                <p class="text-[10px] text-[#5f5c6e] leading-relaxed">
                  Crie seu Client ID OAuth no <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="text-[#130e30] underline font-bold">Google Cloud Console</a> como "Aplicativo da Web" e configure a URI de redirecionamento para <code class="font-mono bg-white px-1.5 py-0.5 rounded text-[10px]">http://127.0.0.1:8000/auth/google/callback</code>.
                </p>
                <div>
                  <label class="block text-[10px] font-semibold text-[#5f5c6e] mb-0.5">Google Client ID</label>
                  <input 
                    v-model="googleCredentialsForm.client_id" 
                    type="text" 
                    required 
                    placeholder="ex: 123456789-abc.apps.googleusercontent.com"
                    class="w-full h-8 px-2.5 text-[11px] rounded-full bg-white border border-[#130e30]/20 text-[#130e30]"
                  />
                </div>
                <div>
                  <label class="block text-[10px] font-semibold text-[#5f5c6e] mb-0.5">Google Client Secret</label>
                  <input 
                    v-model="googleCredentialsForm.client_secret" 
                    type="password" 
                    required 
                    placeholder="ex: GOCSPX-xxxxxxxxxx"
                    class="w-full h-8 px-2.5 text-[11px] rounded-full bg-white border border-[#130e30]/20 text-[#130e30]"
                  />
                </div>
                <div class="flex justify-end">
                  <button 
                    type="submit" 
                    :disabled="isSavingGoogleCredentials"
                    class="px-4 py-1.5 rounded-full bg-[#130e30] hover:bg-[#251d4d] text-white text-[11px] font-semibold cursor-pointer active:scale-95"
                  >
                    {{ isSavingGoogleCredentials ? 'Salvando...' : 'Salvar Chaves' }}
                  </button>
                </div>
              </form>
            </details>
          </div>
        </div>
      </div>
    </div>

    <!-- ================= MODAL: CONEXÃO WHATSAPP (QR CODE & SESSÃO REAL) ================= -->
    <div 
      v-if="showWhatsappConnectModal" 
      class="fixed inset-0 z-50 flex items-center justify-center bg-[#130e30]/50 backdrop-blur-sm p-4 overflow-y-auto"
      @click.self="closeWhatsappModal"
    >
      <div class="w-full max-w-3xl rounded-3xl bg-[#f9fbf2] border border-[#130e30]/15 p-6 shadow-2xl my-6 text-[#130e30] space-y-6 animate-in fade-in duration-200">
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
                  :class="selectedWhatsappInstance?.status === 'connected' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                >
                  {{ selectedWhatsappInstance?.status === 'connected' ? '● Conectado' : '○ Aguardando Conexão' }}
                </span>
              </h3>
              <p class="text-xs text-[#5f5c6e]">
                Conecte seu aparelho celular para disparar cadências e atender leads com IA de forma autônoma.
              </p>
            </div>
          </div>

          <button 
            type="button" 
            @click="closeWhatsappModal"
            class="w-8 h-8 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-[#130e30] flex items-center justify-center cursor-pointer transition-colors"
          >
            ✕
          </button>
        </div>

        <!-- Grid Principal -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
          <!-- Coluna Esquerda: Chips / Instâncias & Diagnóstico -->
          <div class="md:col-span-5 space-y-4">
            <div class="flex items-center justify-between">
              <h4 class="text-xs font-bold uppercase tracking-wider text-[#5f5c6e]">
                Instâncias / Chips ({{ (instances || []).length }})
              </h4>
              <button 
                type="button"
                @click="showNewInstanceModalBox = !showNewInstanceModalBox"
                class="text-[11px] font-bold text-[#130e30] hover:underline cursor-pointer"
              >
                {{ showNewInstanceModalBox ? '✕ Cancelar' : '+ Nova Instância' }}
              </button>
            </div>

            <!-- Form Nova Instância -->
            <div v-if="showNewInstanceModalBox" class="p-3 rounded-2xl bg-white border border-[#130e30]/15 space-y-2">
              <label class="block text-[10px] font-bold uppercase text-[#5f5c6e]">Nome da Instância</label>
              <input 
                v-model="newInstanceForm.name"
                type="text"
                placeholder="Ex: chip-sdr-02"
                class="w-full h-8 px-3 text-xs rounded-xl bg-[#f9fbf2] border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
              />
              <button 
                type="button"
                @click="createNewWhatsappInstance"
                :disabled="isCreatingWhatsappInstance || !newInstanceForm.name.trim()"
                class="w-full h-8 rounded-xl bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] text-xs font-bold cursor-pointer disabled:opacity-50"
              >
                {{ isCreatingWhatsappInstance ? 'Criando...' : 'Criar Instância' }}
              </button>
            </div>

            <!-- Lista de Instâncias -->
            <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
              <div 
                v-for="inst in (instances || [])" 
                :key="inst.id"
                @click="selectWhatsappInstance(inst)"
                class="p-3 rounded-2xl border transition-all cursor-pointer space-y-1.5"
                :class="selectedWhatsappInstance?.id === inst.id 
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
                  <span>Disparos: <strong>{{ inst.sent_today || 0 }}/{{ inst.daily_limit || 100 }}</strong></span>
                  <span v-if="inst.phone_number">📱 {{ inst.phone_number }}</span>
                </div>

                <div class="pt-1 flex items-center justify-between border-t border-[#130e30]/5 text-[10px]">
                  <button 
                    v-if="inst.status !== 'connected'"
                    type="button"
                    @click.stop="fetchWhatsappQr(inst)"
                    class="font-bold text-emerald-700 hover:text-emerald-900 flex items-center gap-1 cursor-pointer"
                  >
                    <span>📷 Conectar (QR Code)</span>
                  </button>
                  <button 
                    v-else
                    type="button"
                    @click.stop="disconnectWhatsappInstance(inst)"
                    class="font-bold text-rose-600 hover:text-rose-800 cursor-pointer"
                  >
                    Desconectar
                  </button>
                </div>
              </div>
            </div>

            <!-- Diagnóstico do Gateway / Docker -->
            <div class="p-3 rounded-2xl bg-[#eff2e5] border border-[#130e30]/10 space-y-2 text-xs">
              <div class="flex items-center justify-between">
                <span class="font-bold text-[#130e30]">⚡ Gateway WhatsApp</span>
                <button 
                  type="button"
                  @click="testGatewayPing"
                  :disabled="isPingingGateway"
                  class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-white border border-[#130e30]/15 hover:bg-[#ffe228] transition-colors cursor-pointer"
                >
                  {{ isPingingGateway ? 'Testando...' : 'Testar Conexão' }}
                </button>
              </div>

              <div v-if="gatewayPingResult" class="p-2 rounded-xl text-[10px] leading-tight font-medium" :class="gatewayPingResult.online ? 'bg-emerald-100 text-emerald-900 border border-emerald-300' : 'bg-rose-100 text-rose-900 border border-rose-300'">
                {{ gatewayPingResult.message }}
              </div>

              <button 
                type="button"
                @click="showDockerHelpBox = !showDockerHelpBox"
                class="text-[10px] text-[#130e30] underline font-bold cursor-pointer block pt-1"
              >
                {{ showDockerHelpBox ? 'Ocultar comando Docker' : 'Como rodar o WhatsApp real no Docker?' }}
              </button>

              <div v-if="showDockerHelpBox" class="p-2 rounded-xl bg-[#130e30] text-[#ffe228] font-mono text-[9px] leading-relaxed space-y-1">
                <p class="text-white/80">Copie e cole no PowerShell:</p>
                <div class="bg-black/50 p-1.5 rounded select-all break-all">
                  docker run -d --name evolution-api -p 8080:8080 -e AUTHENTICATION_API_KEY=ca6ece4fca2b05087e76dc38d2a21ee4f30e31636d31c2f4931759599f6c280f evoapicloud/evolution-api:latest
                </div>
              </div>
            </div>
          </div>

          <!-- Coluna Direita: Área do QR Code / Status da Conexão -->
          <div class="md:col-span-7 space-y-4">
            <div class="p-6 rounded-3xl bg-white border border-[#130e30]/15 shadow-sm flex flex-col items-center justify-center text-center space-y-4 min-h-[360px]">
              <!-- Caso 1: Conectado -->
              <div v-if="selectedWhatsappInstance?.status === 'connected'" class="space-y-4 py-4 w-full">
                <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-700 mx-auto flex items-center justify-center text-3xl">
                  ✓
                </div>
                <div>
                  <h4 class="text-base font-serif-brand font-bold text-emerald-800">
                    WhatsApp Conectado com Sucesso!
                  </h4>
                  <p class="text-xs text-[#5f5c6e] mt-1">
                    A instância <strong>{{ selectedWhatsappInstance.instance_name }}</strong> está pronta para envios.
                  </p>
                </div>

                <!-- Teste de Envio -->
                <div class="p-4 rounded-2xl bg-[#f9fbf2] border border-[#130e30]/10 text-left space-y-2.5 max-w-sm mx-auto">
                  <label class="block text-[11px] font-bold text-[#130e30]">
                    Enviar Mensagem de Teste (Digite seu celular com DDD)
                  </label>
                  <div class="flex gap-2">
                    <input 
                      v-model="whatsappTestPhone"
                      type="text"
                      placeholder="Ex: 11999998888"
                      class="flex-1 h-9 px-3 text-xs rounded-xl bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
                    />
                    <button 
                      type="button"
                      @click="sendWhatsappTestMessage"
                      :disabled="isSendingWhatsappTest || !whatsappTestPhone.trim()"
                      class="h-9 px-4 rounded-xl bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] font-bold text-xs cursor-pointer transition-all disabled:opacity-50"
                    >
                      {{ isSendingWhatsappTest ? 'Enviando...' : 'Testar' }}
                    </button>
                  </div>

                  <div v-if="whatsappTestResult" class="p-2 rounded-xl text-[10px] leading-tight" :class="whatsappTestResult.success ? 'bg-emerald-100 text-emerald-900 border border-emerald-300' : 'bg-rose-100 text-rose-900 border border-rose-300'">
                    {{ whatsappTestResult.message }}
                  </div>
                </div>

                <button 
                  type="button"
                  @click="disconnectWhatsappInstance(selectedWhatsappInstance)"
                  class="text-xs font-bold text-rose-600 hover:text-rose-800 underline cursor-pointer"
                >
                  Desconectar este aparelho
                </button>
              </div>

              <!-- Caso 2: Aparelho Não Conectado (Escolha entre QR Code e Código por Número) -->
              <div v-else class="w-full space-y-4">
                <!-- Seletor de Modo: QR Code vs Código por Número -->
                <div class="flex items-center gap-1.5 p-1 bg-[#eff2e5] rounded-2xl w-full max-w-sm mx-auto text-xs font-semibold">
                  <button 
                    type="button" 
                    @click="whatsappConnectMode = 'qr'" 
                    class="flex-1 py-1.5 rounded-xl transition-all cursor-pointer flex items-center justify-center gap-1.5"
                    :class="whatsappConnectMode === 'qr' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#5f5c6e] hover:text-[#130e30]'"
                  >
                    <span>📷 Ler QR Code</span>
                  </button>
                  <button 
                    type="button" 
                    @click="whatsappConnectMode = 'pairing_code'" 
                    class="flex-1 py-1.5 rounded-xl transition-all cursor-pointer flex items-center justify-center gap-1.5"
                    :class="whatsappConnectMode === 'pairing_code' ? 'bg-[#130e30] text-white shadow-xs' : 'text-[#5f5c6e] hover:text-[#130e30]'"
                  >
                    <span>🔢 Conectar por Número</span>
                  </button>
                </div>

                <!-- MODO A: CONEXÃO POR NÚMERO (PAIRING CODE) -->
                <div v-if="whatsappConnectMode === 'pairing_code'" class="space-y-4 py-2 w-full flex flex-col items-center">
                  <div>
                    <h4 class="text-sm font-serif-brand font-bold text-[#130e30]">
                      Conectar com Número de Telefone
                    </h4>
                    <p class="text-xs text-[#5f5c6e] mt-0.5">
                      Gere um código de 8 dígitos para digitar no WhatsApp sem usar a câmera.
                    </p>
                  </div>

                  <!-- Campo de Número -->
                  <div class="w-full max-w-sm space-y-2">
                    <label class="block text-[11px] font-bold text-[#130e30] text-left">
                      Digite seu número de WhatsApp com DDD
                    </label>
                    <div class="flex gap-2">
                      <input 
                        v-model="whatsappPairingPhone"
                        type="text"
                        placeholder="Ex: 11999998888"
                        class="flex-1 h-9 px-3 text-xs rounded-xl bg-[#f9fbf2] border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
                      />
                      <button 
                        type="button"
                        @click="generatePairingCode"
                        :disabled="isLoadingWhatsappQr || !whatsappPairingPhone.trim()"
                        class="h-9 px-4 rounded-xl bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] font-bold text-xs cursor-pointer transition-all disabled:opacity-50"
                      >
                        {{ isLoadingWhatsappQr ? 'Gerando...' : 'Gerar Código' }}
                      </button>
                    </div>
                  </div>

                  <!-- Código de 8 Dígitos Gerado -->
                  <div v-if="whatsappQrData?.pairing_code" class="w-full max-w-sm space-y-3">
                    <div class="p-4 rounded-2xl bg-[#eff2e5] border-2 border-[#130e30] text-center space-y-1 relative shadow-sm">
                      <p class="text-[10px] font-bold uppercase tracking-wider text-[#5f5c6e]">Seu Código de Pareamento</p>
                      <div class="text-3xl font-mono font-extrabold tracking-widest text-[#130e30] select-all">
                        {{ whatsappQrData.pairing_code }}
                      </div>
                      <p class="text-[10px] text-[#5f5c6e]">Código válido para digitar no seu WhatsApp</p>
                    </div>

                    <!-- Instruções -->
                    <div class="text-left bg-[#f9fbf2] p-3.5 rounded-2xl border border-[#130e30]/10 text-[11px] space-y-1.5">
                      <p class="font-bold text-[#130e30]">Passo a passo no seu celular:</p>
                      <ol class="list-decimal list-inside space-y-1 text-[#5f5c6e]">
                        <li>Abra o <strong>WhatsApp</strong> no smartphone</li>
                        <li>Toque nos <strong>três pontinhos</strong> (ou Configurações) > <strong>Aparelhos Conectados</strong></li>
                        <li>Toque em <strong>Conectar um aparelho</strong></li>
                        <li>Toque em <strong>Conectar com número de telefone</strong> (no rodapé da tela)</li>
                        <li>Digite o código de 8 dígitos <strong>{{ whatsappQrData.pairing_code }}</strong></li>
                      </ol>
                    </div>

                    <div v-if="isWhatsappPolling" class="flex items-center justify-center gap-2 text-xs font-semibold text-emerald-800">
                      <span class="animate-spin text-sm">⏳</span>
                      <span>Aguardando você digitar o código no celular...</span>
                    </div>
                  </div>
                </div>

                <!-- MODO B: CONEXÃO POR QR CODE (CÂMERA) -->
                <div v-else>
                  <!-- Exibindo QR Code -->
                  <div v-if="whatsappQrData?.base64" class="space-y-4 py-2 w-full flex flex-col items-center">
                    <div>
                      <h4 class="text-sm font-serif-brand font-bold text-[#130e30]">
                        Escaneie o QR Code com seu WhatsApp
                      </h4>
                      <p class="text-xs text-[#5f5c6e] mt-0.5">
                        Aguardando leitura do aparelho celular...
                      </p>
                    </div>

                    <div class="p-3 bg-white rounded-3xl border-2 border-[#130e30] shadow-md relative group">
                      <img 
                        :src="whatsappQrData.base64" 
                        alt="QR Code WhatsApp" 
                        class="w-56 h-56 object-contain rounded-2xl"
                      />
                      <div v-if="isWhatsappPolling" class="absolute -top-2 -right-2 flex h-5 w-5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-5 w-5 bg-emerald-600 text-white text-[10px] items-center justify-center font-bold">✓</span>
                      </div>
                    </div>

                    <div class="text-left bg-[#f9fbf2] p-3.5 rounded-2xl border border-[#130e30]/10 text-[11px] space-y-1 max-w-sm">
                      <p class="font-bold text-[#130e30]">Passo a passo no seu celular:</p>
                      <ol class="list-decimal list-inside space-y-0.5 text-[#5f5c6e]">
                        <li>Abra o WhatsApp no seu smartphone</li>
                        <li>Vá em <strong>Aparelhos Conectados</strong></li>
                        <li>Toque em <strong>Conectar um Aparelho</strong></li>
                        <li>Aponte a câmera para este QR Code</li>
                      </ol>
                    </div>

                    <button 
                      type="button"
                      @click="fetchWhatsappQr(selectedWhatsappInstance)"
                      :disabled="isLoadingWhatsappQr"
                      class="h-8 px-4 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-[#130e30] text-xs font-bold cursor-pointer transition-all"
                    >
                      🔄 Atualizar QR Code
                    </button>
                  </div>

                  <!-- Estado Inicial do QR Code -->
                  <div v-else class="space-y-4 py-8">
                    <div class="w-16 h-16 rounded-full bg-[#eff2e5] text-[#130e30] mx-auto flex items-center justify-center text-3xl shadow-2xs">
                      📷
                    </div>
                    <div>
                      <h4 class="text-sm font-serif-brand font-bold text-[#130e30]">
                        Nenhum QR Code Ativo no Momento
                      </h4>
                      <p class="text-xs text-[#5f5c6e] mt-1 max-w-xs mx-auto">
                        Clique no botão abaixo para gerar o QR Code oficial da sua instância.
                      </p>
                    </div>

                    <button 
                      type="button"
                      @click="fetchWhatsappQr(selectedWhatsappInstance)"
                      :disabled="isLoadingWhatsappQr || !selectedWhatsappInstance"
                      class="h-11 px-6 rounded-full bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] font-bold text-xs flex items-center gap-2 mx-auto cursor-pointer transition-all active:scale-95 disabled:opacity-50 shadow-sm"
                    >
                      <span v-if="!isLoadingWhatsappQr">📷 Gerar QR Code Agora</span>
                      <span v-else class="animate-spin text-xs">⏳ Gerando...</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Compor e Enviar E-mail via Gmail -->
    <div 
      v-if="showEmailComposerModal" 
      class="fixed inset-0 z-50 flex items-center justify-center bg-[#130e30]/40 backdrop-blur-sm p-4 overflow-y-auto"
      @click.self="showEmailComposerModal = false"
    >
      <div class="w-full max-w-2xl rounded-3xl bg-[#f9fbf2] border border-[#130e30]/15 p-6 shadow-2xl text-[#130e30]">
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 border-b border-[#130e30]/10 mb-4">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-full bg-[#130e30] text-[#ffe228] flex items-center justify-center font-bold text-base shadow-xs">
              ✉️
            </div>
            <div>
              <h3 class="text-base font-serif-brand font-bold text-[#130e30]">Compor E-mail (Gmail)</h3>
              <p class="text-[11px] text-[#5f5c6e]">Envio direto pela sua caixa de correio com registro na timeline</p>
            </div>
          </div>
          <button @click="showEmailComposerModal = false" class="w-8 h-8 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-[#130e30] flex items-center justify-center cursor-pointer">✕</button>
        </div>

        <form @submit.prevent="submitSendEmail" class="space-y-3.5">
          <!-- Linha Remetente e Destinatário -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">De (Conta Gmail) *</label>
              <div v-if="emailAccounts && emailAccounts.length > 0">
                <select 
                  v-model="emailForm.email_account_id" 
                  class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
                >
                  <option v-for="acc in emailAccounts" :key="acc.id" :value="acc.id">
                    {{ acc.email }} {{ acc.name ? `(${acc.name})` : '' }}
                  </option>
                </select>
              </div>
              <div v-else class="flex items-center justify-between h-9 px-3 rounded-full bg-amber-50 border border-amber-200 text-[11px] text-amber-800">
                <span>Nenhuma conta vinculada</span>
                <button type="button" @click="showGmailModal = true" class="underline font-bold">Conectar</button>
              </div>
            </div>

            <div>
              <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Para (Destinatário) *</label>
              <input 
                v-model="emailForm.to_email" 
                type="email" 
                required 
                placeholder="decisor@empresa.com.br"
                class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
              />
            </div>
          </div>

          <!-- Templates Rápidos B2B -->
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="block text-[11px] font-semibold text-[#5f5c6e]">Modelos Rápidos de Cold Mail</label>
              <span class="text-[10px] text-[#5f5c6e]">Clique para inserir</span>
            </div>
            <div class="flex gap-1.5 overflow-x-auto pb-1 text-[11px]">
              <button 
                type="button" 
                @click="scriptPickerTarget = 'email'; showScriptPickerModal = true" 
                class="px-3 py-1 rounded-full bg-[#130e30] hover:bg-[#20184f] text-[#ffe228] font-bold whitespace-nowrap cursor-pointer transition-all shadow-xs flex items-center gap-1 active:scale-95"
              >
                <span>⚡</span>
                <span>Scripts Salvos ({{ (messageTemplates || []).length }})</span>
              </button>
              <button 
                type="button" 
                @click="insertEmailTemplate('intro')" 
                class="px-3 py-1 rounded-full bg-[#eff2e5] hover:bg-white text-[#130e30] border border-[#130e30]/15 whitespace-nowrap cursor-pointer transition-colors"
              >
                + Apresentação SDR
              </button>
              <button 
                type="button" 
                @click="insertEmailTemplate('followup')" 
                class="px-3 py-1 rounded-full bg-[#eff2e5] hover:bg-white text-[#130e30] border border-[#130e30]/15 whitespace-nowrap cursor-pointer transition-colors"
              >
                + Follow-up 1
              </button>
              <button 
                type="button" 
                @click="insertEmailTemplate('demo')" 
                class="px-3 py-1 rounded-full bg-[#eff2e5] hover:bg-white text-[#130e30] border border-[#130e30]/15 whitespace-nowrap cursor-pointer transition-colors"
              >
                + Convite Demonstração
              </button>
            </div>
          </div>

          <!-- Assunto -->
          <div>
            <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Assunto do E-mail *</label>
            <input 
              v-model="emailForm.subject" 
              type="text" 
              required 
              placeholder="Ex: Oportunidade comercial para a [Empresa]"
              class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
            />
          </div>

          <!-- Corpo do E-mail -->
          <div>
            <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Mensagem *</label>
            <textarea 
              v-model="emailForm.body" 
              rows="6" 
              required 
              placeholder="Olá, tudo bem?..."
              class="w-full p-3 text-xs rounded-2xl bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30] resize-none leading-relaxed"
            ></textarea>
          </div>

          <!-- Botões -->
          <div class="pt-2 flex items-center justify-between">
            <span class="text-[11px] text-[#5f5c6e]">Ficará registrado no histórico da conta</span>
            <div class="flex items-center gap-2">
              <button 
                type="button" 
                @click="showEmailComposerModal = false"
                class="px-4 py-2 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-xs font-medium text-[#130e30] cursor-pointer transition-colors"
              >
                Cancelar
              </button>
              <button 
                type="submit" 
                :disabled="isSendingEmail || !emailForm.to_email || !emailForm.body.trim()"
                class="px-5 py-2 rounded-full bg-[#ffe228] hover:bg-[#fad81d] active:scale-[0.98] disabled:opacity-50 text-xs font-bold text-[#130e30] cursor-pointer transition-all flex items-center gap-1.5 shadow-sm"
              >
                <div v-if="isSendingEmail" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-[#130e30]"></div>
                <span>{{ isSendingEmail ? 'Enviando...' : '✉️ Enviar via Gmail' }}</span>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Criar Novo Script / Modelo de Mensagem (Sunlit Atelier Theme) -->
    <div 
      v-if="showNewTemplateModal" 
      class="fixed inset-0 z-50 flex items-center justify-center bg-[#130e30]/40 backdrop-blur-sm p-4 overflow-y-auto"
      @click.self="showNewTemplateModal = false"
    >
      <div class="w-full max-w-xl rounded-3xl bg-[#f9fbf2] border border-[#130e30]/15 p-6 shadow-2xl my-8 text-[#130e30]">
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 border-b border-[#130e30]/10 mb-4">
          <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-[#130e30] text-[#ffe228] flex items-center justify-center font-bold text-lg shadow-xs">
              📝
            </div>
            <div>
              <h3 class="text-base font-serif-brand font-bold text-[#130e30]">Novo Script / Modelo de Abordagem</h3>
              <p class="text-[11px] text-[#5f5c6e]">Crie roteiros padronizados com variáveis dinâmicas para o time de SDR</p>
            </div>
          </div>
          <button @click="showNewTemplateModal = false" class="w-8 h-8 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-[#130e30] flex items-center justify-center cursor-pointer">✕</button>
        </div>

        <form @submit.prevent="submitNewTemplate" class="space-y-4">
          <!-- Título do Script -->
          <div>
            <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Título do Script *</label>
            <input 
              v-model="newTemplateForm.title" 
              type="text" 
              required 
              placeholder="Ex: Abordagem Inicial - Gancho de Eficiência Operacional"
              class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
            />
          </div>

          <!-- Categoria e Canal em 2 Colunas -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Categoria de Vendas *</label>
              <select 
                v-model="newTemplateForm.category" 
                class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
              >
                <option value="approach">🚀 Abordagem Inicial</option>
                <option value="objection">🛡️ Quebra de Objeção</option>
                <option value="followup">🔄 Follow-up</option>
                <option value="meeting">📅 Confirmação de Reunião</option>
                <option value="closing">🎯 Fechamento</option>
              </select>
            </div>

            <div>
              <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Canal Recomendado *</label>
              <select 
                v-model="newTemplateForm.channel" 
                class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
              >
                <option value="all">🌐 Todos (WhatsApp & E-mail)</option>
                <option value="whatsapp">💬 WhatsApp</option>
                <option value="email">✉️ E-mail (Gmail)</option>
              </select>
            </div>
          </div>

          <!-- Tags Rápidas Clicáveis -->
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="block text-[11px] font-semibold text-[#5f5c6e]">Tags Dinâmicas (Clique para Inserir)</label>
              <span class="text-[10px] text-[#5f5c6e]">Substituição automática no envio</span>
            </div>
            <div class="flex flex-wrap gap-1.5 text-[10px]">
              <button 
                type="button" 
                @click="insertVariableIntoNewTemplate('{{decisor}}')"
                class="px-2.5 py-1 rounded-full bg-white border border-[#130e30]/15 hover:border-[#130e30] font-mono text-[#130e30] cursor-pointer transition-all"
              >
                + &#123;&#123;decisor&#125;&#125;
              </button>
              <button 
                type="button" 
                @click="insertVariableIntoNewTemplate('{{empresa}}')"
                class="px-2.5 py-1 rounded-full bg-white border border-[#130e30]/15 hover:border-[#130e30] font-mono text-[#130e30] cursor-pointer transition-all"
              >
                + &#123;&#123;empresa&#125;&#125;
              </button>
              <button 
                type="button" 
                @click="insertVariableIntoNewTemplate('{{cargo}}')"
                class="px-2.5 py-1 rounded-full bg-white border border-[#130e30]/15 hover:border-[#130e30] font-mono text-[#130e30] cursor-pointer transition-all"
              >
                + &#123;&#123;cargo&#125;&#125;
              </button>
              <button 
                type="button" 
                @click="insertVariableIntoNewTemplate('{{setor}}')"
                class="px-2.5 py-1 rounded-full bg-white border border-[#130e30]/15 hover:border-[#130e30] font-mono text-[#130e30] cursor-pointer transition-all"
              >
                + &#123;&#123;setor&#125;&#125;
              </button>
              <button 
                type="button" 
                @click="insertVariableIntoNewTemplate('{{sdr}}')"
                class="px-2.5 py-1 rounded-full bg-white border border-[#130e30]/15 hover:border-[#130e30] font-mono text-[#130e30] cursor-pointer transition-all"
              >
                + &#123;&#123;sdr&#125;&#125;
              </button>
            </div>
          </div>

          <!-- Conteúdo do Script -->
          <div>
            <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Conteúdo da Mensagem *</label>
            <textarea 
              v-model="newTemplateForm.content" 
              rows="6" 
              required 
              placeholder="Digite o texto do script. Para e-mails, você pode começar com 'Assunto: ...' na primeira linha seguido de duas quebras de linha."
              class="w-full p-3.5 text-xs rounded-2xl bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30] resize-y font-sans leading-relaxed"
            ></textarea>
          </div>

          <!-- Checkbox Favorito -->
          <div class="flex items-center gap-2">
            <input 
              v-model="newTemplateForm.is_favorite" 
              type="checkbox" 
              id="tpl_favorite"
              class="w-4 h-4 rounded text-[#130e30] focus:ring-0 cursor-pointer"
            />
            <label for="tpl_favorite" class="text-xs text-[#130e30] font-medium cursor-pointer">
              Fixar nos scripts favoritos (topo da lista)
            </label>
          </div>

          <!-- Rodapé do Modal com Ações -->
          <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#130e30]/10">
            <button 
              type="button" 
              @click="showNewTemplateModal = false" 
              class="px-4 py-2 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-xs font-medium text-[#130e30] cursor-pointer transition-colors"
            >
              Cancelar
            </button>
            <button 
              type="submit" 
              :disabled="isSavingTemplate || !newTemplateForm.title || !newTemplateForm.content"
              class="px-5 py-2 rounded-full bg-[#ffe228] hover:bg-[#fad81d] active:scale-[0.98] disabled:opacity-50 text-xs font-bold text-[#130e30] cursor-pointer transition-all flex items-center gap-1.5 shadow-sm"
            >
              <div v-if="isSavingTemplate" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-[#130e30]"></div>
              <span>{{ isSavingTemplate ? 'Salvando...' : 'Salvar Script' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Quick Script Picker Modal (Injeção com 1 Clique no Chat ou E-mail) -->
    <div 
      v-if="showScriptPickerModal" 
      class="fixed inset-0 z-50 flex items-center justify-center bg-[#130e30]/40 backdrop-blur-sm p-4 overflow-y-auto"
      @click.self="showScriptPickerModal = false"
    >
      <div class="w-full max-w-2xl rounded-3xl bg-[#f9fbf2] border border-[#130e30]/15 p-6 shadow-2xl my-8 text-[#130e30]">
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 border-b border-[#130e30]/10 mb-4">
          <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-[#130e30] text-[#ffe228] flex items-center justify-center font-bold text-lg shadow-xs">
              ⚡
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="text-base font-serif-brand font-bold text-[#130e30]">Inserir Script Rápido</h3>
                <span 
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                  :class="scriptPickerTarget === 'whatsapp' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800'"
                >
                  {{ scriptPickerTarget === 'whatsapp' ? '💬 Destino: WhatsApp' : '✉️ Destino: Gmail' }}
                </span>
              </div>
              <p class="text-[11px] text-[#5f5c6e]">Clique em qualquer script para preencher os dados do decisor e da empresa automaticamente</p>
            </div>
          </div>
          <button @click="showScriptPickerModal = false" class="w-8 h-8 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-[#130e30] flex items-center justify-center cursor-pointer">✕</button>
        </div>

        <!-- Filtros Rápidos de Categoria no Seletor -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-2 mb-3 text-xs">
          <button 
            type="button"
            @click="templateCategoryFilter = 'all'"
            class="h-7 px-3 rounded-full font-semibold transition-all cursor-pointer whitespace-nowrap"
            :class="templateCategoryFilter === 'all' ? 'bg-[#130e30] text-white shadow-2xs' : 'bg-white border border-[#130e30]/10 text-[#5f5c6e] hover:text-[#130e30]'"
          >
            Todos
          </button>
          <button 
            type="button"
            @click="templateCategoryFilter = 'approach'"
            class="h-7 px-3 rounded-full font-semibold transition-all cursor-pointer whitespace-nowrap"
            :class="templateCategoryFilter === 'approach' ? 'bg-[#130e30] text-white shadow-2xs' : 'bg-white border border-[#130e30]/10 text-[#5f5c6e] hover:text-[#130e30]'"
          >
            🚀 Abordagem
          </button>
          <button 
            type="button"
            @click="templateCategoryFilter = 'objection'"
            class="h-7 px-3 rounded-full font-semibold transition-all cursor-pointer whitespace-nowrap"
            :class="templateCategoryFilter === 'objection' ? 'bg-[#130e30] text-white shadow-2xs' : 'bg-white border border-[#130e30]/10 text-[#5f5c6e] hover:text-[#130e30]'"
          >
            🛡️ Objeção
          </button>
          <button 
            type="button"
            @click="templateCategoryFilter = 'followup'"
            class="h-7 px-3 rounded-full font-semibold transition-all cursor-pointer whitespace-nowrap"
            :class="templateCategoryFilter === 'followup' ? 'bg-[#130e30] text-white shadow-2xs' : 'bg-white border border-[#130e30]/10 text-[#5f5c6e] hover:text-[#130e30]'"
          >
            🔄 Follow-up
          </button>
          <button 
            type="button"
            @click="templateCategoryFilter = 'meeting'"
            class="h-7 px-3 rounded-full font-semibold transition-all cursor-pointer whitespace-nowrap"
            :class="templateCategoryFilter === 'meeting' ? 'bg-[#130e30] text-white shadow-2xs' : 'bg-white border border-[#130e30]/10 text-[#5f5c6e] hover:text-[#130e30]'"
          >
            📅 Reunião
          </button>
        </div>

        <!-- Lista de Scripts Selecionáveis -->
        <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
          <div 
            v-for="tpl in filteredMessageTemplates" 
            :key="tpl.id"
            @click="injectSelectedTemplate(tpl)"
            class="p-4 rounded-2xl bg-white border border-[#130e30]/10 hover:border-[#130e30]/30 hover:shadow-xs transition-all cursor-pointer space-y-2 group"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span v-if="tpl.is_favorite" class="text-amber-400 text-xs">★</span>
                <h4 class="text-xs font-serif-brand font-bold text-[#130e30] group-hover:text-emerald-700 transition-colors">
                  {{ tpl.title }}
                </h4>
              </div>
              <span 
                class="px-2 py-0.5 rounded-full text-[9px] font-bold"
                :class="tpl.category === 'approach' ? 'bg-amber-100 text-amber-900' : (tpl.category === 'objection' ? 'bg-rose-100 text-rose-900' : 'bg-sky-100 text-sky-900')"
              >
                {{ tpl.category }}
              </span>
            </div>

            <p class="text-xs text-[#5f5c6e] line-clamp-3 leading-relaxed whitespace-pre-line bg-[#f9fbf2] p-2.5 rounded-xl border border-[#130e30]/5">
              {{ tpl.content }}
            </p>

            <div class="flex items-center justify-between pt-1 text-[10px]">
              <span class="text-[#5f5c6e]">Canal: {{ tpl.channel === 'whatsapp' ? 'WhatsApp' : (tpl.channel === 'email' ? 'Gmail' : 'Qualquer') }}</span>
              <span class="text-xs font-bold text-[#130e30] group-hover:translate-x-1 transition-transform flex items-center gap-1">
                <span>Usar este script</span>
                <span>→</span>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ================= MODAIS DE GESTÃO MULTI-TENANT & ACESSOS ================= -->

    <!-- Modal 1: Novo Perfil de CRM (Tenant) com IA Integrada -->
    <div 
      v-if="showNewTenantModal" 
      class="fixed inset-0 z-50 flex items-center justify-center bg-[#130e30]/40 backdrop-blur-sm p-4 overflow-y-auto"
      @click.self="showNewTenantModal = false"
    >
      <div class="w-full max-w-2xl rounded-3xl bg-[#f9fbf2] border border-[#130e30]/15 p-6 shadow-2xl my-8 text-[#130e30]">
        <div class="flex items-center justify-between pb-3 border-b border-[#130e30]/10 mb-4">
          <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-[#130e30] text-[#ffe228] flex items-center justify-center font-bold text-lg shadow-xs">
              🏢
            </div>
            <div>
              <h3 class="text-base font-serif-brand font-bold text-[#130e30]">Novo Perfil de CRM & Operação B2B</h3>
              <p class="text-[11px] text-[#5f5c6e]">Crie uma base de dados autônoma com pipeline próprio e IA personalizada</p>
            </div>
          </div>
          <button @click="showNewTenantModal = false" class="w-8 h-8 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-[#130e30] flex items-center justify-center cursor-pointer">✕</button>
        </div>

        <form @submit.prevent="submitNewTenant" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Nome do CRM / Empresa *</label>
              <input 
                v-model="newTenantForm.name" 
                type="text" 
                required 
                placeholder="Ex: Consultoria Tech B2B"
                class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
              />
            </div>

            <div>
              <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Slug / Identificador Único</label>
              <input 
                v-model="newTenantForm.slug" 
                type="text" 
                placeholder="Ex: consultoria-tech (opcional)"
                class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30] font-mono"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">CNPJ / Documento Fiscal</label>
              <input 
                v-model="newTenantForm.document" 
                type="text" 
                placeholder="00.000.000/0001-00 (opcional)"
                class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
              />
            </div>

            <div>
              <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Descrição Breve da Operação</label>
              <input 
                v-model="newTenantForm.description" 
                type="text" 
                placeholder="Ex: Prospecção de SaaS para logística"
                class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
              />
            </div>
          </div>

          <!-- Seção de IA Exclusiva deste CRM -->
          <div class="p-4 rounded-2xl bg-[#eff2e5]/70 border border-[#130e30]/10 space-y-3">
            <div class="flex items-center gap-2">
              <span class="text-sm">🤖</span>
              <h4 class="text-xs font-serif-brand font-bold text-[#130e30]">Configuração de IA do Agente Comercial</h4>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#5f5c6e] mb-1">Nome do Agente SDR</label>
                <input 
                  v-model="newTenantForm.ai_name" 
                  type="text" 
                  placeholder="Ex: Gabriel - SDR Jarvis"
                  class="w-full h-8 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
                />
              </div>

              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#5f5c6e] mb-1">Modelo de IA Preferido</label>
                <select 
                  v-model="newTenantForm.ai_model" 
                  class="w-full h-8 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
                >
                  <option value="gpt-4o-mini">GPT-4o Mini (Rápido e Inteligente)</option>
                  <option value="gpt-4o">GPT-4o (Alta Precisão B2B)</option>
                  <option value="gemini-1.5-flash">Gemini 1.5 Flash (Ultrarrápido)</option>
                  <option value="claude-3-5-sonnet">Claude 3.5 Sonnet (Excelente Redação)</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-[#5f5c6e] mb-1">Persona & Tom de Voz da IA</label>
              <input 
                v-model="newTenantForm.ai_persona" 
                type="text" 
                placeholder="Ex: Consultor Comercial Sênior B2B, tom executivo e foco em ROI"
                class="w-full h-8 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
              />
            </div>

            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-[#5f5c6e] mb-1">Prompt / Diretriz Geral da Operação</label>
              <textarea 
                v-model="newTenantForm.ai_prompt" 
                rows="3" 
                placeholder="Instruções para a IA guiar as conversas e quebrar objeções específicas deste CRM..."
                class="w-full p-2.5 text-xs rounded-xl bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30] resize-y"
              ></textarea>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#130e30]/10">
            <button 
              type="button" 
              @click="showNewTenantModal = false" 
              class="px-4 py-2 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-xs font-medium text-[#130e30] cursor-pointer transition-colors"
            >
              Cancelar
            </button>
            <button 
              type="submit" 
              :disabled="isSavingAdmin || !newTenantForm.name.trim()"
              class="px-5 py-2 rounded-full bg-[#ffe228] hover:bg-[#fad81d] active:scale-[0.98] disabled:opacity-50 text-xs font-bold text-[#130e30] cursor-pointer transition-all flex items-center gap-1.5 shadow-sm"
            >
              <div v-if="isSavingAdmin" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-[#130e30]"></div>
              <span>{{ isSavingAdmin ? 'Criando...' : 'Criar Perfil de CRM' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal 2: Editar Perfil de CRM & IA -->
    <div 
      v-if="showEditTenantModal && editingTenantForm.id" 
      class="fixed inset-0 z-50 flex items-center justify-center bg-[#130e30]/40 backdrop-blur-sm p-4 overflow-y-auto"
      @click.self="showEditTenantModal = false"
    >
      <div class="w-full max-w-2xl rounded-3xl bg-[#f9fbf2] border border-[#130e30]/15 p-6 shadow-2xl my-8 text-[#130e30]">
        <div class="flex items-center justify-between pb-3 border-b border-[#130e30]/10 mb-4">
          <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-[#130e30] text-[#ffe228] flex items-center justify-center font-bold text-lg shadow-xs">
              ⚙️
            </div>
            <div>
              <h3 class="text-base font-serif-brand font-bold text-[#130e30]">Editar CRM: {{ editingTenantForm.name }}</h3>
              <p class="text-[11px] text-[#5f5c6e]">Atualize dados cadastrais, status operacional e diretrizes de IA</p>
            </div>
          </div>
          <button @click="showEditTenantModal = false" class="w-8 h-8 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-[#130e30] flex items-center justify-center cursor-pointer">✕</button>
        </div>

        <form @submit.prevent="submitEditTenant" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Nome do CRM *</label>
              <input 
                v-model="editingTenantForm.name" 
                type="text" 
                required 
                class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
              />
            </div>

            <div>
              <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Status da Operação</label>
              <select 
                v-model="editingTenantForm.status" 
                class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
              >
                <option value="active">Ativo (Operando)</option>
                <option value="suspended">Suspenso / Pausado</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Descrição</label>
            <input 
              v-model="editingTenantForm.description" 
              type="text" 
              class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
            />
          </div>

          <!-- Seção de IA -->
          <div class="p-4 rounded-2xl bg-[#eff2e5]/70 border border-[#130e30]/10 space-y-3">
            <div class="flex items-center gap-2">
              <span class="text-sm">🤖</span>
              <h4 class="text-xs font-serif-brand font-bold text-[#130e30]">Diretrizes de IA deste CRM</h4>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#5f5c6e] mb-1">Nome do Agente SDR</label>
                <input 
                  v-model="editingTenantForm.ai_name" 
                  type="text" 
                  class="w-full h-8 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
                />
              </div>

              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#5f5c6e] mb-1">Modelo de IA</label>
                <select 
                  v-model="editingTenantForm.ai_model" 
                  class="w-full h-8 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
                >
                  <option value="gpt-4o-mini">GPT-4o Mini</option>
                  <option value="gpt-4o">GPT-4o</option>
                  <option value="gemini-1.5-flash">Gemini 1.5 Flash</option>
                  <option value="claude-3-5-sonnet">Claude 3.5 Sonnet</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-[#5f5c6e] mb-1">Persona</label>
              <input 
                v-model="editingTenantForm.ai_persona" 
                type="text" 
                class="w-full h-8 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
              />
            </div>

            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-[#5f5c6e] mb-1">Prompt de Instrução Comercial</label>
              <textarea 
                v-model="editingTenantForm.ai_prompt" 
                rows="3" 
                class="w-full p-2.5 text-xs rounded-xl bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30] resize-y"
              ></textarea>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#130e30]/10">
            <button 
              type="button" 
              @click="showEditTenantModal = false" 
              class="px-4 py-2 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-xs font-medium text-[#130e30] cursor-pointer transition-colors"
            >
              Cancelar
            </button>
            <button 
              type="submit" 
              :disabled="isSavingAdmin"
              class="px-5 py-2 rounded-full bg-[#ffe228] hover:bg-[#fad81d] active:scale-[0.98] disabled:opacity-50 text-xs font-bold text-[#130e30] cursor-pointer transition-all flex items-center gap-1.5 shadow-sm"
            >
              <div v-if="isSavingAdmin" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-[#130e30]"></div>
              <span>Salvar Configurações</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal 3: Novo Usuário & Delegação de Acessos -->
    <div 
      v-if="showNewUserModal" 
      class="fixed inset-0 z-50 flex items-center justify-center bg-[#130e30]/40 backdrop-blur-sm p-4 overflow-y-auto"
      @click.self="showNewUserModal = false"
    >
      <div class="w-full max-w-lg rounded-3xl bg-[#f9fbf2] border border-[#130e30]/15 p-6 shadow-2xl my-8 text-[#130e30]">
        <div class="flex items-center justify-between pb-3 border-b border-[#130e30]/10 mb-4">
          <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-[#130e30] text-[#ffe228] flex items-center justify-center font-bold text-lg shadow-xs">
              👤
            </div>
            <div>
              <h3 class="text-base font-serif-brand font-bold text-[#130e30]">Convidar / Criar Usuário</h3>
              <p class="text-[11px] text-[#5f5c6e]">Defina o perfil de CRM vinculado e o nível de acesso comercial</p>
            </div>
          </div>
          <button @click="showNewUserModal = false" class="w-8 h-8 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-[#130e30] flex items-center justify-center cursor-pointer">✕</button>
        </div>

        <form @submit.prevent="submitNewUser" class="space-y-3.5">
          <div>
            <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Nome Completo *</label>
            <input 
              v-model="newUserForm.name" 
              type="text" 
              required 
              placeholder="Ex: Amanda Silva"
              class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
            />
          </div>

          <div>
            <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">E-mail de Acesso *</label>
            <input 
              v-model="newUserForm.email" 
              type="email" 
              required 
              placeholder="amanda.sdr@empresa.com"
              class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
            />
          </div>

          <div>
            <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Senha Provisória *</label>
            <input 
              v-model="newUserForm.password" 
              type="password" 
              required 
              minlength="6"
              placeholder="Mínimo 6 caracteres"
              class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Perfil de CRM Atribuído *</label>
              <select 
                v-if="canManageTenants"
                v-model="newUserForm.tenant_id" 
                required
                class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
              >
                <option v-for="t in allTenants" :key="t.id" :value="t.id">
                  {{ t.name }}
                </option>
              </select>
              <div 
                v-else
                class="w-full h-9 px-3 text-xs rounded-full bg-[#eff2e5] border border-[#130e30]/10 text-[#130e30] font-bold flex items-center truncate"
              >
                {{ activeTenant?.name || 'Sua Base CRM' }}
              </div>
            </div>

            <div>
              <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Papel / Nível *</label>
              <select 
                v-model="newUserForm.role" 
                class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
              >
                <option value="sdr">💼 SDR (Operador de Prospecção)</option>
                <option value="manager">👔 Gestor Comercial</option>
                <option v-if="canManageTenants" value="admin">🏢 Administrador do Tenant</option>
              </select>
            </div>
          </div>

          <!-- Ramal SIP FreePBX -->
          <div class="grid grid-cols-2 gap-3 p-3 rounded-2xl bg-[#eff2e5] border border-[#130e30]/10">
            <div>
              <label class="block text-[11px] font-semibold text-[#130e30] mb-1">📞 Ramal FreePBX (SIP)</label>
              <input 
                v-model="newUserForm.sip_extension" 
                type="text" 
                placeholder="Ex: 1001"
                class="w-full h-8 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
              />
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-[#130e30] mb-1">Senha do Ramal</label>
              <input 
                v-model="newUserForm.sip_secret" 
                type="password" 
                placeholder="Secret PJSIP"
                class="w-full h-8 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
              />
            </div>
          </div>

          <div v-if="isSuperAdmin" class="p-3 rounded-2xl bg-amber-50 border border-amber-200 flex items-center gap-2">
            <input 
              v-model="newUserForm.is_superadmin" 
              type="checkbox" 
              id="new_user_superadmin"
              class="w-4 h-4 rounded text-[#130e30] focus:ring-0 cursor-pointer"
            />
            <label for="new_user_superadmin" class="text-xs text-amber-900 font-medium cursor-pointer">
              👑 Conceder Acesso de Super Admin Global (Pode alternar e criar qualquer CRM)
            </label>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#130e30]/10">
            <button 
              type="button" 
              @click="showNewUserModal = false" 
              class="px-4 py-2 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-xs font-medium text-[#130e30] cursor-pointer transition-colors"
            >
              Cancelar
            </button>
            <button 
              type="submit" 
              :disabled="isSavingAdmin"
              class="px-5 py-2 rounded-full bg-[#ffe228] hover:bg-[#fad81d] active:scale-[0.98] disabled:opacity-50 text-xs font-bold text-[#130e30] cursor-pointer transition-all flex items-center gap-1.5 shadow-sm"
            >
              <div v-if="isSavingAdmin" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-[#130e30]"></div>
              <span>Cadastrar Usuário</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal 4: Editar Usuário & Permissões -->
    <div 
      v-if="showEditUserModal && editingUserForm.id" 
      class="fixed inset-0 z-50 flex items-center justify-center bg-[#130e30]/40 backdrop-blur-sm p-4 overflow-y-auto"
      @click.self="showEditUserModal = false"
    >
      <div class="w-full max-w-lg rounded-3xl bg-[#f9fbf2] border border-[#130e30]/15 p-6 shadow-2xl my-8 text-[#130e30]">
        <div class="flex items-center justify-between pb-3 border-b border-[#130e30]/10 mb-4">
          <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-[#130e30] text-[#ffe228] flex items-center justify-center font-bold text-lg shadow-xs">
              ⚙️
            </div>
            <div>
              <h3 class="text-base font-serif-brand font-bold text-[#130e30]">Editar: {{ editingUserForm.name }}</h3>
              <p class="text-[11px] text-[#5f5c6e]">Atualize papéis de acesso e o perfil de CRM vinculado</p>
            </div>
          </div>
          <button @click="showEditUserModal = false" class="w-8 h-8 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-[#130e30] flex items-center justify-center cursor-pointer">✕</button>
        </div>

        <form @submit.prevent="submitEditUser" class="space-y-3.5">
          <div>
            <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Nome Completo *</label>
            <input 
              v-model="editingUserForm.name" 
              type="text" 
              required 
              class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
            />
          </div>

          <div>
            <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">E-mail *</label>
            <input 
              v-model="editingUserForm.email" 
              type="email" 
              required 
              class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
            />
          </div>

          <div>
            <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Redefinir Senha (opcional)</label>
            <input 
              v-model="editingUserForm.password" 
              type="password" 
              minlength="6"
              placeholder="Deixe em branco para manter a atual"
              class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Perfil de CRM Atribuído *</label>
              <select 
                v-if="canManageTenants"
                v-model="editingUserForm.tenant_id" 
                required
                class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
              >
                <option v-for="t in allTenants" :key="t.id" :value="t.id">
                  {{ t.name }}
                </option>
              </select>
              <div 
                v-else
                class="w-full h-9 px-3 text-xs rounded-full bg-[#eff2e5] border border-[#130e30]/10 text-[#130e30] font-bold flex items-center truncate"
              >
                {{ activeTenant?.name || 'Sua Base CRM' }}
              </div>
            </div>

            <div>
              <label class="block text-[11px] font-semibold text-[#5f5c6e] mb-1">Papel / Nível *</label>
              <select 
                v-model="editingUserForm.role" 
                class="w-full h-9 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
              >
                <option value="sdr">💼 SDR (Operador de Prospecção)</option>
                <option value="manager">👔 Gestor Comercial</option>
                <option v-if="canManageTenants" value="admin">🏢 Administrador do Tenant</option>
              </select>
            </div>
          </div>

          <!-- Ramal SIP FreePBX -->
          <div class="grid grid-cols-2 gap-3 p-3 rounded-2xl bg-[#eff2e5] border border-[#130e30]/10">
            <div>
              <label class="block text-[11px] font-semibold text-[#130e30] mb-1">📞 Ramal FreePBX (SIP)</label>
              <input 
                v-model="editingUserForm.sip_extension" 
                type="text" 
                placeholder="Ex: 1001"
                class="w-full h-8 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
              />
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-[#130e30] mb-1">Redefinir Senha Ramal</label>
              <input 
                v-model="editingUserForm.sip_secret" 
                type="password" 
                placeholder="Deixe em branco p/ manter"
                class="w-full h-8 px-3 text-xs rounded-full bg-white border border-[#130e30]/20 text-[#130e30] focus:outline-none focus:border-[#130e30]"
              />
            </div>
          </div>

          <div class="flex items-center gap-4 pt-1">
            <div v-if="isSuperAdmin" class="flex items-center gap-2">
              <input 
                v-model="editingUserForm.is_superadmin" 
                type="checkbox" 
                id="edit_user_superadmin"
                class="w-4 h-4 rounded text-[#130e30] focus:ring-0 cursor-pointer"
              />
              <label for="edit_user_superadmin" class="text-xs text-[#130e30] font-medium cursor-pointer">
                👑 Super Admin
              </label>
            </div>

            <div class="flex items-center gap-2">
              <input 
                v-model="editingUserForm.is_active" 
                type="checkbox" 
                id="edit_user_active"
                class="w-4 h-4 rounded text-emerald-600 focus:ring-0 cursor-pointer"
              />
              <label for="edit_user_active" class="text-xs text-[#130e30] font-medium cursor-pointer">
                Usuário Ativo
              </label>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#130e30]/10">
            <button 
              type="button" 
              @click="showEditUserModal = false" 
              class="px-4 py-2 rounded-full bg-[#eff2e5] hover:bg-[#e4ebd7] text-xs font-medium text-[#130e30] cursor-pointer transition-colors"
            >
              Cancelar
            </button>
            <button 
              type="submit" 
              :disabled="isSavingAdmin"
              class="px-5 py-2 rounded-full bg-[#ffe228] hover:bg-[#fad81d] active:scale-[0.98] disabled:opacity-50 text-xs font-bold text-[#130e30] cursor-pointer transition-all flex items-center gap-1.5 shadow-sm"
            >
              <div v-if="isSavingAdmin" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-[#130e30]"></div>
              <span>Salvar Alterações</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Notification Toast (Clean High-Contrast Pill) -->
    <div 
      v-if="toastMessage" 
      class="fixed bottom-24 right-6 z-50 max-w-sm rounded-full px-5 py-3 text-xs shadow-2xl flex items-center justify-between gap-3 border transition-all animate-in slide-in-from-bottom"
      :class="toastType === 'success' 
        ? 'bg-[#130e30] border-[#130e30] text-white' 
        : 'bg-rose-700 border-rose-700 text-white'"
    >
      <div class="flex items-center gap-2">
        <span v-if="toastType === 'success'" class="text-[#ffe228] font-bold text-sm">✓</span>
        <span v-else class="text-white font-bold text-sm">⚠️</span>
        <p class="font-medium leading-tight">{{ toastMessage }}</p>
      </div>
      <button @click="toastMessage = null" class="text-white/60 hover:text-white text-xs cursor-pointer">✕</button>
    </div>

    <!-- ================= WEBRTC SOFTPHONE / DISCADOR FREEPBX ================= -->
    <WebphoneWidget :is-chat-open="!!selectedLead" />
  </div>
</template>

<script setup>
import { ref, computed, nextTick, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import WebphoneWidget from '@/Components/Voip/WebphoneWidget.vue'
import JarvisCopilotChat from '@/Components/Ai/JarvisCopilotChat.vue'
import AiTrainingLab from '@/Components/Ai/AiTrainingLab.vue'

const props = defineProps({
  pipeline: Object,
  stages: Array,
  companies: Array,
  leads: Array,
  instances: Array,
  emailAccounts: Array,
  hasGoogleConfig: Boolean,
  metrics: Object,
  dashboardMetrics: Object,
  users: Array,
  currentUser: Object,
  cadences: Array,
  messageTemplates: Array,
  activeTenant: Object,
  allTenants: Array,
  allUsers: Array,
  isSuperAdmin: Boolean,
  isManager: Boolean,
  canManageTenants: Boolean,
  canManageTeam: Boolean,
})

// Visualização ativa: 'kanban' | 'tasks' | 'cadences' | 'templates' | 'dashboard' | 'admin'
const currentView = ref('kanban')

// Estado do Painel Administrativo Multi-Tenant (Gestor inicia direto na aba de colaboradores)
const adminTab = ref(props.canManageTenants ? 'tenants' : 'users')
const showTenantDropdown = ref(false)
const showNewTenantModal = ref(false)
const showEditTenantModal = ref(false)
const showNewUserModal = ref(false)
const showEditUserModal = ref(false)
const showWhatsappConnectModal = ref(false)
const isSavingAdmin = ref(false)

// ================= ESTADO DO WHATSAPP MODAL & CONEXÃO REAL =================
const selectedWhatsappInstance = ref(null)
const whatsappConnectMode = ref('qr') // 'qr' | 'pairing_code'
const whatsappPairingPhone = ref('')
const whatsappQrData = ref(null)
const isLoadingWhatsappQr = ref(false)
const isWhatsappPolling = ref(false)
let whatsappPollTimer = null

const whatsappTestPhone = ref('')
const isSendingWhatsappTest = ref(false)
const whatsappTestResult = ref(null)

const isPingingGateway = ref(false)
const gatewayPingResult = ref(null)
const showDockerHelpBox = ref(false)

const showNewInstanceModalBox = ref(false)
const newInstanceForm = ref({ name: '', daily_limit: 60 })
const isCreatingWhatsappInstance = ref(false)

function openWhatsappModal(inst = null) {
  showWhatsappConnectModal.value = true
  const target = inst || activeInstance.value || (props.instances && props.instances[0]) || null
  selectedWhatsappInstance.value = target
  if (target && target.status !== 'connected') {
    fetchWhatsappQr(target)
  }
}

function closeWhatsappModal() {
  stopWhatsappPolling()
  showWhatsappConnectModal.value = false
}

function selectWhatsappInstance(inst) {
  selectedWhatsappInstance.value = inst
  whatsappQrData.value = null
  whatsappTestResult.value = null
  if (inst.status !== 'connected') {
    if (whatsappConnectMode.value === 'pairing_code' && whatsappPairingPhone.value.trim()) {
      fetchWhatsappQr(inst, whatsappPairingPhone.value)
    } else {
      fetchWhatsappQr(inst)
    }
  }
}

async function fetchWhatsappQr(inst, phone = null) {
  if (!inst) return
  selectedWhatsappInstance.value = inst
  isLoadingWhatsappQr.value = true
  whatsappQrData.value = null
  whatsappTestResult.value = null

  try {
    const url = phone 
      ? `/whatsapp/instances/${inst.id}/qr?phone=${encodeURIComponent(phone)}`
      : `/whatsapp/instances/${inst.id}/qr`
    const res = await axios.get(url)
    if (res.data?.success) {
      whatsappQrData.value = res.data
      inst.status = 'connecting'
      startWhatsappPolling(inst)
    }
  } catch (err) {
    showToast(err.response?.data?.message || 'Falha ao gerar QR Code / Código do WhatsApp.', 'error')
  } finally {
    isLoadingWhatsappQr.value = false
  }
}

function generatePairingCode() {
  if (!selectedWhatsappInstance.value) return
  if (!whatsappPairingPhone.value.trim()) {
    showToast('Por favor, informe seu número de WhatsApp com DDD.', 'error')
    return
  }
  fetchWhatsappQr(selectedWhatsappInstance.value, whatsappPairingPhone.value)
}

function startWhatsappPolling(inst) {
  stopWhatsappPolling()
  isWhatsappPolling.value = true
  whatsappPollTimer = setInterval(async () => {
    try {
      const res = await axios.get(`/whatsapp/instances/${inst.id}/status`)
      if (res.data?.is_connected) {
        inst.status = 'connected'
        stopWhatsappPolling()
        whatsappQrData.value = null
        showToast(`🎉 WhatsApp conectado na instância ${inst.instance_name}!`, 'success')
      }
    } catch (e) {}
  }, 3000)
}

function stopWhatsappPolling() {
  if (whatsappPollTimer) {
    clearInterval(whatsappPollTimer)
    whatsappPollTimer = null
  }
  isWhatsappPolling.value = false
}

async function disconnectWhatsappInstance(inst) {
  if (!confirm(`Deseja realmente desconectar o WhatsApp da instância "${inst.instance_name}"?`)) return
  try {
    const res = await axios.post(`/whatsapp/instances/${inst.id}/disconnect`)
    if (res.data?.success) {
      inst.status = 'disconnected'
      whatsappQrData.value = null
      stopWhatsappPolling()
      showToast('Instância desconectada com sucesso.', 'success')
    }
  } catch (err) {
    showToast('Falha ao desconectar instância.', 'error')
  }
}

async function sendWhatsappTestMessage() {
  if (!selectedWhatsappInstance.value || !whatsappTestPhone.value.trim()) return
  isSendingWhatsappTest.value = true
  whatsappTestResult.value = null

  try {
    const res = await axios.post('/whatsapp/instances/test-send', {
      instance_id: selectedWhatsappInstance.value.id,
      phone: whatsappTestPhone.value,
      message: '🚀 Olá! Esta é uma mensagem de teste enviada diretamente pelo seu SDR JARVIS.',
    })
    whatsappTestResult.value = {
      success: true,
      message: res.data?.message || 'Mensagem enviada com sucesso!',
    }
  } catch (err) {
    whatsappTestResult.value = {
      success: false,
      message: err.response?.data?.message || 'Erro ao enviar mensagem de teste.',
    }
  } finally {
    isSendingWhatsappTest.value = false
  }
}

async function testGatewayPing() {
  isPingingGateway.value = true
  gatewayPingResult.value = null
  try {
    const res = await axios.post('/whatsapp/gateway/ping')
    gatewayPingResult.value = res.data
  } catch (err) {
    gatewayPingResult.value = {
      online: false,
      message: 'Não foi possível se comunicar com o gateway de WhatsApp.',
    }
  } finally {
    isPingingGateway.value = false
  }
}

async function createNewWhatsappInstance() {
  if (!newInstanceForm.value.name.trim()) return
  isCreatingWhatsappInstance.value = true
  try {
    const res = await axios.post('/whatsapp/instances', {
      instance_name: newInstanceForm.value.name,
      daily_limit: newInstanceForm.value.daily_limit || 60,
    })
    if (res.data?.success) {
      if (props.instances) {
        props.instances.unshift(res.data.instance)
      }
      selectedWhatsappInstance.value = res.data.instance
      showNewInstanceModalBox.value = false
      newInstanceForm.value.name = ''
      showToast('Nova instância criada! Agora gere o QR Code para conectar.', 'success')
      fetchWhatsappQr(res.data.instance)
    }
  } catch (err) {
    showToast(err.response?.data?.message || 'Erro ao criar instância.', 'error')
  } finally {
    isCreatingWhatsappInstance.value = false
  }
}

const newTenantForm = ref({
  name: '',
  slug: '',
  description: '',
  document: '',
  ai_name: '',
  ai_persona: '',
  ai_model: 'gpt-4o-mini',
  ai_prompt: '',
  ai_api_key: '',
})

const editingTenantForm = ref({
  id: null,
  name: '',
  slug: '',
  description: '',
  document: '',
  status: 'active',
  ai_name: '',
  ai_persona: '',
  ai_model: 'gpt-4o-mini',
  ai_prompt: '',
  ai_api_key: '',
})

const newUserForm = ref({
  name: '',
  email: '',
  password: '',
  role: 'sdr',
  is_superadmin: false,
  tenant_id: null,
  sip_extension: '',
  sip_secret: '',
})

const editingUserForm = ref({
  id: null,
  name: '',
  email: '',
  password: '',
  role: 'sdr',
  is_superadmin: false,
  is_active: true,
  tenant_id: null,
  sip_extension: '',
  sip_secret: '',
})

// Smart Views do Funil Kanban
const activeSmartView = ref('all') // 'all' | 'cadence' | 'no_contact' | 'meeting' | 'pending_tasks' | 'my_accounts'

function isNoContact3Days(company) {
  const threeDaysAgo = new Date(Date.now() - 3 * 24 * 60 * 60 * 1000)
  
  // Verifica atividades recentes
  const latestActivity = (company.activities || [])[0]
  if (latestActivity && new Date(latestActivity.created_at) > threeDaysAgo) {
    return false
  }
  
  // Verifica mensagens recentes
  for (const c of company.contacts || []) {
    const latestMsg = (c.messages || [])[0]
    if (latestMsg && new Date(latestMsg.created_at) > threeDaysAgo) {
      return false
    }
  }

  // Se criada recentemente (< 3 dias), não conta como sem contato
  if (company.created_at && new Date(company.created_at) > threeDaysAgo) {
    return false
  }

  return true
}

const smartViewOptions = computed(() => [
  { id: 'all', label: 'Todas as Contas', icon: '🔥', count: (props.companies || []).length },
  { id: 'cadence', label: 'Em Cadência Ativa', icon: '⚡', count: (props.companies || []).filter(c => c.active_cadence && c.active_cadence.status === 'active').length },
  { id: 'no_contact', label: 'Sem Contato > 3 Dias', icon: '⚠️', count: (props.companies || []).filter(isNoContact3Days).length },
  { id: 'meeting', label: 'Reunião Agendada', icon: '📅', count: (props.companies || []).filter(c => !!c.meeting_at).length },
  { id: 'pending_tasks', label: 'Com Tarefas', icon: '📌', count: (props.companies || []).filter(c => (c.tasks || []).some(t => t.status === 'pending')).length },
  { id: 'my_accounts', label: 'Minhas Contas', icon: '👤', count: (props.companies || []).filter(c => Number(c.assigned_user_id) === Number(props.currentUser?.id)).length },
])

// Biblioteca de Scripts & Templates de Mensagens
const templateCategoryFilter = ref('all') // 'all' | 'approach' | 'objection' | 'followup' | 'meeting'
const templateChannelFilter = ref('all') // 'all' | 'whatsapp' | 'email'
const showNewTemplateModal = ref(false)
const showScriptPickerModal = ref(false)
const scriptPickerTarget = ref('whatsapp') // 'whatsapp' | 'email'
const isSavingTemplate = ref(false)

const newTemplateForm = ref({
  title: '',
  category: 'approach',
  channel: 'all',
  content: '',
  is_favorite: false,
})

const filteredMessageTemplates = computed(() => {
  let list = props.messageTemplates || []
  if (templateCategoryFilter.value !== 'all') {
    list = list.filter(t => t.category === templateCategoryFilter.value)
  }
  if (templateChannelFilter.value !== 'all') {
    list = list.filter(t => t.channel === 'all' || t.channel === templateChannelFilter.value)
  }
  return list
})

function openNewTemplateModal() {
  newTemplateForm.value = {
    title: '',
    category: 'approach',
    channel: 'all',
    content: '',
    is_favorite: false,
  }
  showNewTemplateModal.value = true
}

async function submitNewTemplate() {
  if (!newTemplateForm.value.title.trim() || !newTemplateForm.value.content.trim()) {
    alert('Preencha o título e o conteúdo do script.')
    return
  }
  isSavingTemplate.value = true
  try {
    const res = await axios.post('/templates', newTemplateForm.value)
    if (res.data?.success) {
      showNewTemplateModal.value = false
      router.reload({ only: ['messageTemplates'] })
      showToast('Script salvo com sucesso!')
    }
  } catch (err) {
    console.error('Erro ao salvar template:', err)
    alert('Erro ao salvar novo script.')
  } finally {
    isSavingTemplate.value = false
  }
}

async function toggleTemplateFavorite(template) {
  try {
    const res = await axios.patch(`/templates/${template.id}/favorite`)
    if (res.data?.success) {
      template.is_favorite = res.data.is_favorite
    }
  } catch (err) {
    console.error('Erro ao favoritar template:', err)
  }
}

async function deleteTemplate(templateId) {
  if (!confirm('Deseja excluir este modelo de mensagem?')) return
  try {
    const res = await axios.delete(`/templates/${templateId}`)
    if (res.data?.success) {
      router.reload({ only: ['messageTemplates'] })
      showToast('Script excluído.')
    }
  } catch (err) {
    console.error('Erro ao excluir template:', err)
  }
}

function injectTemplateIntoChat(template) {
  let text = template.content
  const leadName = selectedLead.value?.name ? selectedLead.value.name.split(' ')[0] : 'Decisor'
  const companyName = selectedLead.value?.company_name || editingCompany.value?.name || 'sua empresa'
  const jobTitle = selectedLead.value?.job_title || 'gestor'
  const segment = editingCompany.value?.segment || 'seu setor'
  const sdrName = props.currentUser?.name || 'nosso time'

  text = text.replace(/{{decisor}}/g, leadName)
             .replace(/{{empresa}}/g, companyName)
             .replace(/{{cargo}}/g, jobTitle)
             .replace(/{{setor}}/g, segment)
             .replace(/{{sdr}}/g, sdrName)
             .replace(/{nome}/g, leadName)
             .replace(/{empresa}/g, companyName)

  newMessageText.value = text
  showScriptPickerModal.value = false
  showToast('Script inserido no chat!')
}

function injectTemplateIntoEmail(template) {
  let text = template.content
  const leadName = emailForm.value.to_name ? emailForm.value.to_name.split(' ')[0] : 'Decisor'
  const companyName = emailForm.value.company_name || 'sua empresa'
  const sdrName = props.currentUser?.name || 'nosso time'

  text = text.replace(/{{decisor}}/g, leadName)
             .replace(/{{empresa}}/g, companyName)
             .replace(/{{cargo}}/g, 'gestor')
             .replace(/{{setor}}/g, 'seu setor')
             .replace(/{{sdr}}/g, sdrName)
             .replace(/{nome}/g, leadName)
             .replace(/{empresa}/g, companyName)

  if (text.startsWith('Assunto:')) {
    const parts = text.split('\n\n')
    const subjectLine = parts[0].replace('Assunto:', '').trim()
    emailForm.value.subject = subjectLine
    emailForm.value.body = parts.slice(1).join('\n\n')
  } else {
    emailForm.value.body = text
  }

  showScriptPickerModal.value = false
  showToast('Script inserido no e-mail!')
}

function copyTemplateText(text) {
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(text)
    showToast('Script copiado!')
  } else {
    const textarea = document.createElement('textarea')
    textarea.value = text
    document.body.appendChild(textarea)
    textarea.select()
    document.execCommand('copy')
    document.body.removeChild(textarea)
    showToast('Script copiado!')
  }
}

function insertVariableIntoNewTemplate(varTag) {
  newTemplateForm.value.content = (newTemplateForm.value.content || '') + ' ' + varTag + ' '
}

function injectSelectedTemplate(template) {
  if (scriptPickerTarget.value === 'whatsapp') {
    injectTemplateIntoChat(template)
  } else {
    injectTemplateIntoEmail(template)
  }
}

// Estado e Computeds de Cadências B2B
const cadenceList = computed(() => props.cadences || [])

const activeEnrollmentsTotal = computed(() => {
  return (props.companies || []).filter(c => c.active_cadence && c.active_cadence.status === 'active').length
})

const totalCadenceStepsConfigured = computed(() => {
  let count = 0
  for (const c of cadenceList.value) {
    count += (c.steps || []).length
  }
  return count
})

const selectedCadenceToEnroll = ref(null)
const isEnrollingCadence = ref(false)

async function enrollCompanyInCadence(companyId, cadenceId) {
  if (!cadenceId) return
  isEnrollingCadence.value = true
  try {
    const res = await axios.post(`/companies/${companyId}/cadence/enroll`, { cadence_id: cadenceId })
    if (res.data?.success) {
      router.reload({ only: ['companies', 'cadences'] })
    }
  } catch (err) {
    console.error('Erro ao matricular na cadência:', err)
    alert('Erro ao matricular conta na cadência.')
  } finally {
    isEnrollingCadence.value = false
  }
}

async function unenrollCompanyFromCadence(companyId) {
  if (!confirm('Deseja realmente interromper a cadência desta conta? As tarefas pendentes da régua serão canceladas.')) return
  isEnrollingCadence.value = true
  try {
    const res = await axios.post(`/companies/${companyId}/cadence/unenroll`)
    if (res.data?.success) {
      router.reload({ only: ['companies', 'cadences'] })
    }
  } catch (err) {
    console.error('Erro ao interromper cadência:', err)
  } finally {
    isEnrollingCadence.value = false
  }
}

const isProcessingCadences = ref(false)
async function processCadencesNow() {
  isProcessingCadences.value = true
  try {
    const res = await axios.post('/cadences/process-now')
    if (res.data?.success) {
      const executed = res.data.result?.executed?.length || 0
      const pending = res.data.result?.total_pending || 0
      alert(`⚡ Motor de cadências processado!\n- ${executed} tarefa(s) disparada(s)\n- ${pending} tarefa(s) pendente(s) avaliada(s)`)
      router.reload({ only: ['companies', 'cadences', 'tasks', 'metrics'] })
    }
  } catch (err) {
    console.error('Erro ao processar motor de cadências:', err)
    alert('Erro ao executar motor de cadências.')
  } finally {
    isProcessingCadences.value = false
  }
}

const isSimulatingReply = ref(false)
async function simulateLeadReply(companyId) {
  if (!confirm('Simular resposta inbound do lead desta empresa? Isso pausará automaticamente a cadência ativa e moverá a conta para a etapa de Respondeu no Kanban.')) return
  isSimulatingReply.value = true
  try {
    const res = await axios.post(`/companies/${companyId}/cadence/simulate-reply`)
    if (res.data?.success) {
      alert(`🎯 Resposta do lead simulada com sucesso!\nCadência pausada e conta movida para a coluna '${res.data.result?.stage_name || 'Respondeu'}'.`)
      router.reload({ only: ['companies', 'cadences', 'stages', 'tasks'] })
    }
  } catch (err) {
    console.error('Erro ao simular resposta do lead:', err)
    alert('Erro ao simular resposta do lead.')
  } finally {
    isSimulatingReply.value = false
  }
}

function triggerCall(phone, company = null, contact = null) {
  if (!phone) return
  window.dispatchEvent(new CustomEvent('jarvis:call-phone', {
    detail: {
      phone,
      companyId: company?.id || null,
      companyName: company?.name || '',
      leadId: contact?.id || null,
      leadName: contact?.name || 'Decisor',
    }
  }))
}

function formatStepDelayBadge(step) {
  const val = step.delay_value !== null && step.delay_value !== undefined ? step.delay_value : step.day_offset
  if (val === 0 || !val) {
    return 'Imediato (Hoje)'
  }
  const unit = step.delay_unit || 'days'
  if (unit === 'seconds') return `+${val}s`
  if (unit === 'minutes') return `+${val} min`
  if (unit === 'hours') return `+${val}h`
  return `+${val} dias`
}

// Criar nova cadência modal
const showNewCadenceModal = ref(false)
const isSavingCadence = ref(false)
const newCadenceForm = ref({
  name: '',
  description: '',
  target_segment: '',
  color: '#130e30',
  steps: [
    { step_number: 1, delay_value: 0, delay_unit: 'days', day_offset: 0, task_type: 'whatsapp', priority: 'high', title: '1º Toque WhatsApp: Gancho de Dor', instructions: '', template_content: 'Olá {{decisor}}, tudo bem? Notei que a {{empresa}} atua no setor {{setor}}...' },
    { step_number: 2, delay_value: 1, delay_unit: 'days', day_offset: 1, task_type: 'email', priority: 'medium', title: '1º E-mail: Apresentação & Proposta de Valor', instructions: '', template_content: 'Assunto: Oportunidade estratégica para {{empresa}}' },
    { step_number: 3, delay_value: 2, delay_unit: 'days', day_offset: 2, task_type: 'call', priority: 'urgent', title: 'Cold Call: 3 Minutos com Decisor', instructions: 'Ligar para o número do decisor ou recepção', template_content: '' },
  ]
})

function openNewCadenceModal() {
  newCadenceForm.value = {
    name: '',
    description: '',
    target_segment: '',
    color: '#130e30',
    steps: [
      { step_number: 1, delay_value: 0, delay_unit: 'days', day_offset: 0, task_type: 'whatsapp', priority: 'high', title: '1º Toque WhatsApp: Gancho de Dor', instructions: '', template_content: 'Olá {{decisor}}, tudo bem? Notei que a {{empresa}} atua no setor {{setor}}...' },
      { step_number: 2, delay_value: 1, delay_unit: 'days', day_offset: 1, task_type: 'email', priority: 'medium', title: '1º E-mail: Apresentação & Proposta de Valor', instructions: '', template_content: 'Assunto: Oportunidade estratégica para {{empresa}}' },
      { step_number: 3, delay_value: 2, delay_unit: 'days', day_offset: 2, task_type: 'call', priority: 'urgent', title: 'Cold Call: 3 Minutos com Decisor', instructions: 'Ligar para o número do decisor ou recepção', template_content: '' },
    ]
  }
  showNewCadenceModal.value = true
}

function addStepToNewCadence() {
  const nextNum = newCadenceForm.value.steps.length + 1
  newCadenceForm.value.steps.push({
    step_number: nextNum,
    delay_value: 2,
    delay_unit: 'days',
    day_offset: 2,
    task_type: 'whatsapp',
    priority: 'medium',
    title: `Passo ${nextNum}: Follow-up`,
    instructions: '',
    template_content: '',
  })
}

function removeStepFromNewCadence(idx) {
  if (newCadenceForm.value.steps.length <= 1) return
  newCadenceForm.value.steps.splice(idx, 1)
  newCadenceForm.value.steps.forEach((s, i) => { s.step_number = i + 1 })
}

async function submitNewCadence() {
  if (!newCadenceForm.value.name.trim()) {
    alert('Informe o nome da cadência.')
    return
  }
  isSavingCadence.value = true
  try {
    const res = await axios.post('/cadences', newCadenceForm.value)
    if (res.data?.success) {
      showNewCadenceModal.value = false
      router.reload({ only: ['cadences'] })
    }
  } catch (err) {
    console.error('Erro ao salvar cadência:', err)
    alert('Erro ao salvar nova cadência.')
  } finally {
    isSavingCadence.value = false
  }
}

async function deleteCadence(cadence) {
  if (!confirm(`Deseja realmente excluir a cadência "${cadence.name}"? Todas as etapas e contas vinculadas a ela serão removidas.`)) {
    return
  }
  try {
    const res = await axios.delete(`/cadences/${cadence.id}`)
    if (res.data?.success) {
      router.reload({ only: ['cadences'] })
    }
  } catch (err) {
    console.error('Erro ao excluir cadência:', err)
    alert('Erro ao excluir cadência: ' + (err.response?.data?.message || err.message))
  }
}

// Gestão Global de Tarefas State & Computeds
const taskStatusFilter = ref('pending') // 'pending' | 'overdue' | 'today' | 'upcoming' | 'completed' | 'all'
const taskTypeFilter = ref('all')
const taskSdrFilter = ref('all')

const allTasksList = computed(() => {
  const list = []
  for (const comp of props.companies || []) {
    for (const t of comp.tasks || []) {
      list.push({
        ...t,
        company_name: comp.name,
        company_segment: comp.segment,
        company_id: comp.id,
        company: comp,
      })
    }
  }
  return list
})

const pendingTasksCount = computed(() => {
  return allTasksList.value.filter(t => t.status === 'pending').length
})

const overdueTasksCount = computed(() => {
  const now = new Date()
  return allTasksList.value.filter(t => t.status === 'pending' && t.due_at && new Date(t.due_at) < now).length
})

const todayTasksCount = computed(() => {
  const todayStr = new Date().toISOString().slice(0, 10)
  return allTasksList.value.filter(t => t.status === 'pending' && t.due_at && t.due_at.slice(0, 10) === todayStr).length
})

const completedTasksCount = computed(() => {
  return allTasksList.value.filter(t => t.status === 'completed').length
})

const filteredTasksList = computed(() => {
  const now = new Date()
  const todayStr = now.toISOString().slice(0, 10)
  const q = (searchQuery.value || '').toLowerCase().trim()

  return allTasksList.value.filter(t => {
    // Filtro por status
    if (taskStatusFilter.value === 'pending' && t.status !== 'pending') return false
    if (taskStatusFilter.value === 'completed' && t.status !== 'completed') return false
    if (taskStatusFilter.value === 'overdue') {
      if (t.status !== 'pending' || !t.due_at || new Date(t.due_at) >= now) return false
    }
    if (taskStatusFilter.value === 'today') {
      if (t.status !== 'pending' || !t.due_at || t.due_at.slice(0, 10) !== todayStr) return false
    }
    if (taskStatusFilter.value === 'upcoming') {
      if (t.status !== 'pending' || !t.due_at || new Date(t.due_at) <= now) return false
    }

    // Filtro por tipo
    if (taskTypeFilter.value !== 'all' && t.type !== taskTypeFilter.value) return false

    // Filtro por SDR
    if (taskSdrFilter.value !== 'all' && String(t.user_id) !== String(taskSdrFilter.value)) return false

    // Busca textual
    if (q) {
      const matchTitle = t.title?.toLowerCase().includes(q)
      const matchDesc = t.description?.toLowerCase().includes(q)
      const matchComp = t.company_name?.toLowerCase().includes(q)
      const matchLead = t.lead?.name?.toLowerCase().includes(q)
      if (!matchTitle && !matchDesc && !matchComp && !matchLead) return false
    }

    return true
  }).sort((a, b) => {
    // Pendentes primeiro
    if (a.status === 'pending' && b.status !== 'pending') return -1
    if (a.status !== 'pending' && b.status === 'pending') return 1
    // Depois por data de vencimento (as mais próximas/atrasadas primeiro)
    if (a.due_at && b.due_at) {
      return new Date(a.due_at) - new Date(b.due_at)
    }
    if (a.due_at) return -1
    if (b.due_at) return 1
    return 0
  })
})

// Dashboard de Produtividade State
const dashboardPeriod = ref('all')
const activeDashboardData = ref(props.dashboardMetrics || {
  period: 'all',
  kpis: {
    total_meetings: 0,
    meeting_conversion_rate: 0,
    total_companies: 0,
    responded_companies: 0,
    response_rate: 0,
    total_calls: 0,
    connected_calls: 0,
    call_connection_rate: 0,
    whatsapp_outbound: 0,
    whatsapp_inbound: 0,
    whatsapp_response_rate: 0,
    emails_sent: 0,
    total_activities: 0,
  },
  call_outcomes: {
    connected: 0,
    meeting_scheduled: 0,
    no_answer: 0,
    voicemail: 0,
    gatekeeper: 0,
    wrong_number: 0,
  },
  stage_distribution: [],
  sdr_leaderboard: [],
})
const isLoadingDashboard = ref(false)

const periodOptions = [
  { label: 'Hoje', value: 'today' },
  { label: '7 Dias', value: '7days' },
  { label: '30 Dias', value: '30days' },
  { label: 'Geral', value: 'all' },
]

async function changeDashboardPeriod(period) {
  dashboardPeriod.value = period
  isLoadingDashboard.value = true
  try {
    const res = await axios.get('/dashboard/metrics', {
      params: { period }
    })
    activeDashboardData.value = res.data
  } catch (error) {
    console.error('Erro ao buscar métricas do dashboard:', error)
  } finally {
    isLoadingDashboard.value = false
  }
}

function getOutcomePercent(count) {
  const total = activeDashboardData.value?.kpis?.total_calls || 0
  if (total === 0) return 0
  return Math.round((count / total) * 100)
}

function getStagePercent(count) {
  const total = activeDashboardData.value?.kpis?.total_companies || 0
  if (total === 0) return 0
  return Math.round((count / total) * 100)
}

// Gmail & Email State
const showGmailModal = ref(false)
const showEmailComposerModal = ref(false)
const isSendingEmail = ref(false)
const isSavingGoogleCredentials = ref(false)

const emailForm = ref({
  company_id: null,
  lead_id: null,
  to_email: '',
  to_name: '',
  company_name: '',
  subject: '',
  body: '',
  email_account_id: null,
})

const googleCredentialsForm = ref({
  client_id: '',
  client_secret: '',
})

const activeEmailAccount = computed(() => {
  return (props.emailAccounts || []).find(a => a.status === 'connected') || (props.emailAccounts || [])[0] || null
})

// Metrics Bar Toggle
const showMetrics = ref(true)

// Search
const searchQuery = ref('')

// Drag & Drop State (Empresas)
const draggedCompany = ref(null)
const isDragOverStage = ref(null)

// Chat Drawer State
const selectedLead = ref(null)
const messages = ref([])
const isLoadingMessages = ref(false)
const isSending = ref(false)
const newMessageText = ref('')
const chatContainer = ref(null)
const showReplySimulator = ref(false)
const isSimulatingChatReply = ref(false)
const customSimulatedText = ref('')

// Company Modal State
const showCompanyModal = ref(false)
const isSavingCompany = ref(false)
const isDeletingCompany = ref(false)
const editingCompany = ref(null)
const activeCompanyTab = ref('activities') // 'activities' | 'contacts' | 'meeting' | 'tasks'
const showAddContactBox = ref(false)
const isAddingContact = ref(false)
const isAddingActivity = ref(false)
const isSchedulingMeeting = ref(false)
const isAddingTask = ref(false)

const newTaskForm = ref({
  title: '',
  description: '',
  type: 'followup',
  priority: 'medium',
  due_at: '',
  lead_id: null,
})

const scheduleMeetingForm = ref({
  meeting_at: '',
  meeting_link: '',
  lead_id: null,
  qualification_notes: '',
})

const newActivityForm = ref({
  type: 'call',
  call_outcome: 'connected',
  lead_id: null,
  description: '',
})

const companyForm = ref({
  id: null,
  name: '',
  document: '',
  segment: '',
  status: 'open',
  pipeline_stage_id: null,
  assigned_user_id: null,
  notes: '',
})

const newContactForm = ref({
  name: '',
  job_title: '',
  phone: '',
  email: '',
})

// New Company / Lead Modal State
const showNewLeadModal = ref(false)
const isSubmittingLead = ref(false)
const newLeadForm = ref({
  name: '',
  job_title: '',
  company_name: '',
  phone: '',
  email: '',
  pipeline_stage_id: props.stages?.[0]?.id || null,
  document: '',
  segment: '',
  custom_attributes: {},
})

// Enriquecimento Automático CNPJ State
const cnpjSearchQuery = ref('')
const isEnriching = ref(false)
const enrichedData = ref(null)
const isEnrichingCompany = ref(false)

// Import Leads Modal State
const showImportModal = ref(false)
const isImporting = ref(false)
const fileInput = ref(null)
const importForm = ref({
  file: null,
  pipeline_stage_id: props.stages?.[0]?.id || null,
})

// Toast State
const toastMessage = ref(null)
const toastType = ref('success')

// Active WhatsApp Instance
const activeInstance = computed(() => {
  return props.instances?.find(i => i.status === 'connected') || props.instances?.[0] || null
})

// Filtering & Segmentation State
const filterSdr = ref('all')
const filterSegment = ref('all')
const filterStatus = ref('all')

// Extract distinct non-empty segments dynamically from companies
const availableSegments = computed(() => {
  const segments = new Set()
  for (const c of props.companies || []) {
    if (c.segment && c.segment.trim()) {
      segments.add(c.segment.trim())
    }
  }
  return Array.from(segments).sort()
})

const hasActiveFilters = computed(() => {
  return filterSdr.value !== 'all' || 
         filterSegment.value !== 'all' || 
         filterStatus.value !== 'all' || 
         activeSmartView.value !== 'all' ||
         searchQuery.value.trim().length > 0
})

function clearFilters() {
  filterSdr.value = 'all'
  filterSegment.value = 'all'
  filterStatus.value = 'all'
  activeSmartView.value = 'all'
  searchQuery.value = ''
}

// Filter Companies by Stage, Filters and Search
function getCompaniesForStage(stageId) {
  const query = searchQuery.value.trim().toLowerCase()
  return (props.companies || []).filter(company => {
    // 1. Stage match
    if (company.pipeline_stage_id !== stageId) return false

    // 1.1 Smart View filter
    if (activeSmartView.value === 'cadence') {
      if (!company.active_cadence || company.active_cadence.status !== 'active') return false
    } else if (activeSmartView.value === 'no_contact') {
      if (!isNoContact3Days(company)) return false
    } else if (activeSmartView.value === 'meeting') {
      if (!company.meeting_at) return false
    } else if (activeSmartView.value === 'pending_tasks') {
      const hasPending = (company.tasks || []).some(t => t.status === 'pending')
      if (!hasPending) return false
    } else if (activeSmartView.value === 'my_accounts') {
      if (Number(company.assigned_user_id) !== Number(props.currentUser?.id)) return false
    }

    // 2. SDR filter
    if (filterSdr.value !== 'all') {
      if (filterSdr.value === 'unassigned') {
        if (company.assigned_user_id) return false
      } else if (Number(company.assigned_user_id) !== Number(filterSdr.value)) {
        return false
      }
    }

    // 3. Segment filter
    if (filterSegment.value !== 'all') {
      if ((company.segment || '').trim().toLowerCase() !== filterSegment.value.toLowerCase()) {
        return false
      }
    }

    // 4. Status filter
    if (filterStatus.value !== 'all') {
      if ((company.status || 'open') !== filterStatus.value) {
        return false
      }
    }

    // 5. Search text filter
    if (!query) return true
    
    const companyMatch = company.name?.toLowerCase().includes(query)
    const segmentMatch = company.segment?.toLowerCase().includes(query)
    const contactsMatch = (company.contacts || []).some(c => 
      c.name?.toLowerCase().includes(query) || 
      c.job_title?.toLowerCase().includes(query) || 
      c.phone?.includes(query)
    )

    return companyMatch || segmentMatch || contactsMatch
  })
}

const filteredTotalCompanies = computed(() => {
  let count = 0
  for (const stage of props.stages || []) {
    count += getCompaniesForStage(stage.id).length
  }
  return count
})

// Drag & Drop Handlers (Empresas)
function onDragStartCompany(company) {
  draggedCompany.value = company
}

function onDragOver(stageId) {
  isDragOverStage.value = stageId
}

function onDragLeave(stageId) {
  if (isDragOverStage.value === stageId) {
    isDragOverStage.value = null
  }
}

function onDrop(stageId) {
  isDragOverStage.value = null
  if (!draggedCompany.value || draggedCompany.value.pipeline_stage_id === stageId) {
    draggedCompany.value = null
    return
  }

  const companyToMove = draggedCompany.value
  draggedCompany.value = null

  // Optimistic UI update
  companyToMove.pipeline_stage_id = stageId

  // Backend update
  router.patch(`/companies/${companyToMove.id}/stage`, {
    pipeline_stage_id: stageId,
  }, {
    preserveScroll: true,
    preserveState: true,
  })
}

// Company Modal Handlers
function openCompanyDetails(company, defaultTab = 'activities') {
  editingCompany.value = company
  activeCompanyTab.value = defaultTab
  showAddContactBox.value = false
  companyForm.value = {
    id: company.id,
    name: company.name || '',
    document: company.document || '',
    segment: company.segment || '',
    status: company.status || 'open',
    pipeline_stage_id: company.pipeline_stage_id,
    assigned_user_id: company.assigned_user_id || null,
    notes: company.custom_attributes?.notes || '',
  }
  newContactForm.value = {
    name: '',
    job_title: '',
    phone: '',
    email: '',
  }
  newActivityForm.value = {
    type: 'call',
    call_outcome: 'connected',
    lead_id: company.contacts?.[0]?.id || null,
    description: '',
  }
  newTaskForm.value = {
    title: '',
    description: '',
    type: 'followup',
    priority: 'medium',
    due_at: '',
    lead_id: company.contacts?.[0]?.id || null,
  }
  initMeetingForm(company)
  showCompanyModal.value = true
}

function initMeetingForm(company, defaultLeadId = null) {
  let formattedDateTime = ''
  if (company.meeting_at) {
    const d = new Date(company.meeting_at)
    const pad = (n) => String(n).padStart(2, '0')
    formattedDateTime = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
  }

  scheduleMeetingForm.value = {
    meeting_at: formattedDateTime,
    meeting_link: company.meeting_link || '',
    lead_id: defaultLeadId || company.contacts?.[0]?.id || null,
    qualification_notes: company.custom_attributes?.qualification_notes || '',
  }
}

function appendPainTag(tag) {
  if (!scheduleMeetingForm.value.qualification_notes) {
    scheduleMeetingForm.value.qualification_notes = `• ${tag}`
  } else {
    scheduleMeetingForm.value.qualification_notes += `\n• ${tag}`
  }
}

function submitScheduleMeeting() {
  if (!editingCompany.value || !scheduleMeetingForm.value.meeting_at) {
    showToast('Por favor, informe a data e horário da reunião.', 'error')
    return
  }

  isSchedulingMeeting.value = true
  router.post(`/companies/${editingCompany.value.id}/schedule-meeting`, scheduleMeetingForm.value, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      isSchedulingMeeting.value = false
      showToast('Reunião agendada e empresa movida para Reunião Marcada!', 'success')
      activeCompanyTab.value = 'activities'
    },
    onError: () => {
      isSchedulingMeeting.value = false
      showToast('Erro ao agendar reunião. Verifique os campos.', 'error')
    },
  })
}

function submitAddActivity() {
  if (!editingCompany.value || !newActivityForm.value.description.trim()) return

  isAddingActivity.value = true
  router.post(`/companies/${editingCompany.value.id}/activities`, newActivityForm.value, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      isAddingActivity.value = false
      newActivityForm.value.description = ''
      showToast('Atividade registrada no histórico!', 'success')
    },
    onError: () => {
      isAddingActivity.value = false
      showToast('Erro ao registrar atividade.', 'error')
    },
  })
}

function deleteActivity(activityId) {
  if (!confirm('Deseja realmente excluir este registro de atividade?')) return

  router.delete(`/activities/${activityId}`, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      showToast('Registro de atividade removido!', 'success')
    },
    onError: () => {
      showToast('Erro ao remover atividade.', 'error')
    },
  })
}

function submitCompanyUpdate() {
  if (!editingCompany.value) return

  isSavingCompany.value = true
  router.put(`/companies/${editingCompany.value.id}`, companyForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      showCompanyModal.value = false
      isSavingCompany.value = false
      showToast('Empresa e contatos atualizados com sucesso!', 'success')
    },
    onError: () => {
      isSavingCompany.value = false
      showToast('Erro ao atualizar empresa.', 'error')
    },
  })
}

function deleteCompany(companyId) {
  if (!confirm('Tem certeza que deseja excluir esta empresa e todos os seus contatos? Esta ação não pode ser desfeita.')) {
    return
  }

  isDeletingCompany.value = true
  router.delete(`/companies/${companyId}`, {
    preserveScroll: true,
    onSuccess: () => {
      showCompanyModal.value = false
      isDeletingCompany.value = false
      showToast('Empresa e contatos excluídos com sucesso!', 'success')
    },
    onError: () => {
      isDeletingCompany.value = false
      showToast('Erro ao excluir empresa.', 'error')
    },
  })
}

function submitAddContact() {
  if (!editingCompany.value || !newContactForm.value.name || !newContactForm.value.phone) return

  isAddingContact.value = true
  router.post(`/companies/${editingCompany.value.id}/contacts`, newContactForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      isAddingContact.value = false
      showAddContactBox.value = false
      newContactForm.value = { name: '', job_title: '', phone: '', email: '' }
      showToast('Novo contato adicionado!', 'success')
    },
    onError: () => {
      isAddingContact.value = false
      showToast('Erro ao adicionar contato.', 'error')
    },
  })
}

function openChatFromCompany(contact) {
  showCompanyModal.value = false
  openChat(contact)
}

function openCompanyWithTab(company, tab = 'activities') {
  openCompanyDetails(company, tab)
}

// Métodos de Gestão de Tarefas e Lembretes
async function submitAddTask() {
  if (!editingCompany.value || !newTaskForm.value.title.trim()) return
  isAddingTask.value = true
  try {
    const res = await axios.post(`/companies/${editingCompany.value.id}/tasks`, newTaskForm.value)
    if (res.data && res.data.task) {
      if (!editingCompany.value.tasks) {
        editingCompany.value.tasks = []
      }
      editingCompany.value.tasks.unshift(res.data.task)

      // Atualiza também na lista principal de empresas (para refletir no card)
      const comp = (props.companies || []).find(c => c.id === editingCompany.value.id)
      if (comp) {
        if (!comp.tasks) comp.tasks = []
        comp.tasks.unshift(res.data.task)
      }

      showToast('Tarefa criada com sucesso!', 'success')
      newTaskForm.value = {
        title: '',
        description: '',
        type: 'followup',
        priority: 'medium',
        due_at: '',
        lead_id: editingCompany.value.contacts?.[0]?.id || null,
      }
    }
  } catch (error) {
    console.error('Erro ao adicionar tarefa:', error)
    showToast('Erro ao criar tarefa.', 'error')
  } finally {
    isAddingTask.value = false
  }
}

async function toggleTaskStatus(task) {
  try {
    const res = await axios.patch(`/tasks/${task.id}/toggle`)
    if (res.data && res.data.task) {
      task.status = res.data.task.status
      task.completed_at = res.data.task.completed_at

      // Atualiza na empresa correspondente
      if (editingCompany.value && editingCompany.value.tasks) {
        const idx = editingCompany.value.tasks.findIndex(t => t.id === task.id)
        if (idx !== -1) {
          editingCompany.value.tasks[idx] = res.data.task
        }
      }
      const comp = (props.companies || []).find(c => c.id === task.company_id)
      if (comp && comp.tasks) {
        const idx = comp.tasks.findIndex(t => t.id === task.id)
        if (idx !== -1) {
          comp.tasks[idx] = res.data.task
        }
      }
      showToast(res.data.message || 'Status da tarefa atualizado!', 'success')
    }
  } catch (error) {
    console.error('Erro ao alternar status da tarefa:', error)
    showToast('Erro ao atualizar tarefa.', 'error')
  }
}

async function deleteTask(task) {
  if (!confirm(`Deseja realmente excluir a tarefa "${task.title}"?`)) return
  try {
    await axios.delete(`/tasks/${task.id}`)
    if (editingCompany.value && editingCompany.value.tasks) {
      editingCompany.value.tasks = editingCompany.value.tasks.filter(t => t.id !== task.id)
    }
    const comp = (props.companies || []).find(c => c.id === task.company_id)
    if (comp && comp.tasks) {
      comp.tasks = comp.tasks.filter(t => t.id !== task.id)
    }
    showToast('Tarefa excluída com sucesso!', 'success')
  } catch (error) {
    console.error('Erro ao excluir tarefa:', error)
    showToast('Erro ao excluir tarefa.', 'error')
  }
}

function getCompanyTaskSummary(company) {
  const tasks = company.tasks || []
  const pending = tasks.filter(t => t.status === 'pending')
  if (!pending.length) return null

  const now = new Date()
  const todayStr = now.toISOString().slice(0, 10)

  // 1. Verifica tarefas atrasadas
  const overdue = pending.filter(t => t.due_at && new Date(t.due_at) < now)
  if (overdue.length > 0) {
    return {
      type: 'overdue',
      icon: '⚠️',
      label: `Atrasada: ${overdue[0].title}`,
      count: overdue.length,
      badgeClass: 'bg-rose-50 text-rose-800 border border-rose-200 cursor-pointer shadow-2xs',
    }
  }

  // 2. Verifica tarefas para hoje
  const todayTasks = pending.filter(t => t.due_at && t.due_at.slice(0, 10) === todayStr)
  if (todayTasks.length > 0) {
    return {
      type: 'today',
      icon: '⏰',
      label: `Hoje: ${todayTasks[0].title}`,
      count: todayTasks.length,
      badgeClass: 'bg-[#ffe228] text-[#130e30] border border-[#130e30]/15 shadow-2xs cursor-pointer',
    }
  }

  // 3. Próximas tarefas futuras
  return {
    type: 'upcoming',
    icon: '📌',
    label: pending[0].title,
    count: pending.length,
    badgeClass: 'bg-[#eff2e5] text-[#130e30] border border-[#130e30]/15 cursor-pointer',
  }
}

function isTaskOverdue(dateStr) {
  if (!dateStr) return false
  return new Date(dateStr) < new Date()
}

function isTaskToday(dateStr) {
  if (!dateStr) return false
  const d = new Date(dateStr)
  const now = new Date()
  return d.toDateString() === now.toDateString()
}

function formatTaskDue(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  const now = new Date()
  if (d.toDateString() === now.toDateString()) {
    return `Hoje às ${d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })}`
  }
  const tomorrow = new Date(now)
  tomorrow.setDate(tomorrow.getDate() + 1)
  if (d.toDateString() === tomorrow.toDateString()) {
    return `Amanhã às ${d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })}`
  }
  return d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' })
}

// Chat Drawer Handlers
let chatPollTimer = null

async function openChat(lead) {
  selectedLead.value = lead
  isLoadingMessages.value = true
  messages.value = []

  if (chatPollTimer) clearInterval(chatPollTimer)

  try {
    const res = await axios.get(`/leads/${lead.id}/messages`)
    messages.value = res.data.messages || []
    scrollToBottom()
  } catch (err) {
    console.error('Erro ao buscar mensagens do lead:', err)
  } finally {
    isLoadingMessages.value = false
  }

  // Polling a cada 3 segundos enquanto o chat estiver aberto
  chatPollTimer = setInterval(async () => {
    if (!selectedLead.value) {
      if (chatPollTimer) clearInterval(chatPollTimer)
      return
    }
    try {
      const res = await axios.get(`/leads/${selectedLead.value.id}/messages`)
      if (res.data?.messages && res.data.messages.length !== messages.value.length) {
        messages.value = res.data.messages
        scrollToBottom()
      }
    } catch (_) {}
  }, 3000)
}

function closeChat() {
  if (chatPollTimer) {
    clearInterval(chatPollTimer)
    chatPollTimer = null
  }
  selectedLead.value = null
  messages.value = []
  newMessageText.value = ''
  showReplySimulator.value = false
  customSimulatedText.value = ''
}

function insertTemplate(template) {
  newMessageText.value = template
}

function openMeetingSchedulerFromChat() {
  if (!selectedLead.value) return
  const companyId = selectedLead.value.company_id
  const company = (props.companies || []).find(c => c.id === companyId) || {
    id: companyId,
    name: selectedLead.value.company_name || 'Conta',
    contacts: [selectedLead.value],
    pipeline_stage_id: selectedLead.value.pipeline_stage_id,
  }

  openCompanyDetails(company)
  activeCompanyTab.value = 'meeting'
  scheduleMeetingForm.value.lead_id = selectedLead.value.id
}

function insertMeetingConfirmationTemplate() {
  if (!selectedLead.value) return

  const company = (props.companies || []).find(c => c.id === selectedLead.value.company_id)
  let dateTimeStr = '[DATA_HORA]'
  let linkStr = '[LINK_MEET]'

  if (company && company.meeting_at) {
    dateTimeStr = formatDateTimeWithHour(company.meeting_at)
  }
  if (company && company.meeting_link) {
    linkStr = company.meeting_link
  }

  newMessageText.value = `Perfeito, {nome}! Nossa reunião está confirmada para ${dateTimeStr}. Segue o link de acesso da sala: ${linkStr}. Até breve!`
}

// Gmail & Email Composer Handlers
function openEmailComposer(company, contact = null) {
  const targetLead = contact || (company.contacts || []).find(c => c.email) || (company.contacts || [])[0]
  emailForm.value = {
    company_id: company.id,
    lead_id: targetLead?.id || null,
    to_email: targetLead?.email || '',
    to_name: targetLead?.name || '',
    company_name: company.name || '',
    subject: `Oportunidade e Alinhamento Estratégico - ${company.name}`,
    body: `Olá ${targetLead?.name ? targetLead.name.split(' ')[0] : 'tudo bem'},\n\nAcompanho o crescimento da ${company.name} e identifiquei oportunidades estratégicas para acelerar a geração de leads qualificados.\n\nVocê teria 15 minutos nesta semana para uma rápida troca de ideias?\n\nQual o melhor dia e horário para você?\n\nAtenciosamente,\n${props.currentUser?.name || 'SDR JARVIS'}`,
    email_account_id: activeEmailAccount.value?.id || null,
  }
  showEmailComposerModal.value = true
}

function insertEmailTemplate(type) {
  const nome = emailForm.value.to_name ? emailForm.value.to_name.split(' ')[0] : '{nome}'
  const empresa = emailForm.value.company_name || '{empresa}'

  if (type === 'intro') {
    emailForm.value.subject = `Oportunidade e Otimização Comercial para ${empresa}`
    emailForm.value.body = `Olá ${nome},\n\nTudo bem?\n\nAcompanho o crescimento da ${empresa} e identifiquei oportunidades estratégicas para acelerar a geração de leads qualificados e otimizar o tempo da equipe comercial.\n\nVocê teria 15 minutos nesta semana para uma troca de ideias rápida e sem compromisso?\n\nQual o melhor dia e horário para você?\n\nAtenciosamente,\n${props.currentUser?.name || 'SDR JARVIS'}`
  } else if (type === 'followup') {
    emailForm.value.subject = `Re: Contato sobre ${empresa}`
    emailForm.value.body = `Olá ${nome},\n\nPassando apenas para saber se você conseguiu avaliar minha mensagem anterior.\n\nAinda faz sentido conversarmos brevemente esta semana sobre como acelerar a prospecção da ${empresa}?\n\nFico no seu aguardo!\n\nAbraços,\n${props.currentUser?.name || 'SDR JARVIS'}`
  } else if (type === 'demo') {
    emailForm.value.subject = `Demonstração rápida: SDR JARVIS para ${empresa}`
    emailForm.value.body = `Olá ${nome},\n\nPreparamos uma demonstração personalizada mostrando como empresas do seu segmento estão reduzindo o tempo de primeiro contato e aumentando a taxa de reuniões marcadas em mais de 3x.\n\nPodemos agendar uma call de 15 minutos? Se preferir, pode me sugerir um horário diretamente respondendo este e-mail.\n\nUm abraço,\n${props.currentUser?.name || 'SDR JARVIS'}`
  }
}

function submitSendEmail() {
  if (!emailForm.value.to_email || !emailForm.value.subject || !emailForm.value.body.trim()) {
    showToast('Preencha destinatário, assunto e mensagem do e-mail.', 'error')
    return
  }

  isSendingEmail.value = true
  router.post('/emails/send', emailForm.value, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      isSendingEmail.value = false
      showEmailComposerModal.value = false
      showToast('E-mail enviado e registrado no histórico da conta com sucesso!', 'success')
    },
    onError: (errors) => {
      isSendingEmail.value = false
      showToast(errors.email || 'Erro ao enviar e-mail. Verifique a conexão com o Gmail.', 'error')
    },
  })
}

function submitSaveGoogleCredentials() {
  if (!googleCredentialsForm.value.client_id || !googleCredentialsForm.value.client_secret) return

  isSavingGoogleCredentials.value = true
  router.post('/auth/google/credentials', googleCredentialsForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      isSavingGoogleCredentials.value = false
      showToast('Credenciais do Google salvas com sucesso! Agora clique em Conectar com o Google.', 'success')
    },
    onError: () => {
      isSavingGoogleCredentials.value = false
      showToast('Erro ao salvar credenciais.', 'error')
    },
  })
}

function disconnectGoogleAccount(accountId) {
  if (!confirm('Deseja realmente desconectar esta conta do Gmail?')) return

  router.post(`/auth/google/disconnect/${accountId}`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      showToast('Conta do Gmail desconectada.', 'success')
    },
  })
}

async function submitMessage() {
  if (!newMessageText.value.trim() || !selectedLead.value) return

  isSending.value = true
  const textToSend = newMessageText.value
  newMessageText.value = ''

  try {
    const res = await axios.post(`/leads/${selectedLead.value.id}/send-message`, {
      text: textToSend,
      whatsapp_instance_id: activeInstance.value?.id,
    })

    if (res.data?.success && res.data?.message) {
      messages.value.push(res.data.message)
      scrollToBottom()
      router.reload({ only: ['companies', 'leads'] })
    } else {
      const reloadRes = await axios.get(`/leads/${selectedLead.value.id}/messages`)
      messages.value = reloadRes.data.messages || []
      scrollToBottom()
    }
  } catch (err) {
    console.error('Erro ao enviar mensagem:', err)
    newMessageText.value = textToSend
    showToast(err.response?.data?.message || 'Erro ao enviar mensagem.', 'error')
  } finally {
    isSending.value = false
  }
}

async function toggleLeadAiAutonomous(lead) {
  if (!lead) return
  try {
    const res = await axios.post(`/ai/leads/${lead.id}/toggle-autonomous`)
    if (res.data?.success) {
      if (!lead.custom_attributes) lead.custom_attributes = {}
      lead.custom_attributes.ai_autonomous_paused = res.data.is_ai_paused
      showToast(res.data.message, 'success')
    }
  } catch (err) {
    console.error('Erro ao alternar IA autônoma:', err)
    showToast('Erro ao alternar modo autônomo do lead.', 'error')
  }
}

async function simulateLeadResponse(text) {
  if (!selectedLead.value || !text.trim() || isSimulatingChatReply.value) return
  isSimulatingChatReply.value = true

  try {
    const res = await axios.post(`/leads/${selectedLead.value.id}/simulate-reply`, {
      text: text,
    })

    if (res.data?.success && res.data?.message) {
      messages.value.push(res.data.message)
      scrollToBottom()

      if (res.data?.autonomous_result?.status === 'replied') {
        showToast(`🤖 O SDR JARVIS respondeu autonomamente para ${selectedLead.value.name}!`, 'success')
      } else if (res.data?.autonomous_result?.status === 'handoff_triggered') {
        showToast(`🚨 Transbordo acionado! A IA pausou para você atender diretamente.`, 'warning')
      } else {
        showToast(`🎯 Resposta de ${selectedLead.value.name} simulada! A cadência foi pausada.`, 'success')
      }

      // Recarrega as mensagens para buscar a resposta outbound gerada pela IA
      setTimeout(async () => {
        try {
          const reloadRes = await axios.get(`/leads/${selectedLead.value.id}/messages`)
          if (reloadRes.data?.messages) {
            messages.value = reloadRes.data.messages
            scrollToBottom()
          }
        } catch (e) {}
      }, 600)

      router.reload({ only: ['companies', 'leads', 'stages'] })
    }
  } catch (err) {
    console.error('Erro ao simular resposta do lead:', err)
    showToast(err.response?.data?.message || 'Erro ao simular resposta do lead.', 'error')
  } finally {
    isSimulatingChatReply.value = false
    customSimulatedText.value = ''
  }
}

function submitCustomSimulatedReply() {
  if (!customSimulatedText.value.trim()) return
  simulateLeadResponse(customSimulatedText.value)
}

function scrollToBottom() {
  nextTick(() => {
    if (chatContainer.value) {
      chatContainer.value.scrollTop = chatContainer.value.scrollHeight
    }
  })
}

// Formatters
function formatPhone(phone) {
  if (!phone) return ''
  const clean = phone.replace(/\D/g, '')
  if (clean.length === 13) {
    return `+${clean.slice(0, 2)} (${clean.slice(2, 4)}) ${clean.slice(4, 9)}-${clean.slice(9)}`
  }
  if (clean.length === 11) {
    return `(${clean.slice(0, 2)}) ${clean.slice(2, 7)}-${clean.slice(7)}`
  }
  if (clean.length === 10) {
    return `(${clean.slice(0, 2)}) ${clean.slice(2, 6)}-${clean.slice(6)}`
  }
  return phone
}

function formatTime(timestamp) {
  if (!timestamp) return ''
  const date = new Date(timestamp)
  return date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}

function formatDate(timestamp) {
  if (!timestamp) return ''
  const date = new Date(timestamp)
  return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

function formatDateTime(timestamp) {
  if (!timestamp) return ''
  const date = new Date(timestamp)
  return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

function formatDateTimeWithHour(timestamp) {
  if (!timestamp) return ''
  const date = new Date(timestamp)
  return `${date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' })} às ${date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })}`
}

function formatOutcomeBadge(outcome) {
  const map = {
    connected: { label: 'Falou c/ Decisor', class: 'bg-[#ffe228] text-[#130e30] border border-[#130e30]/20' },
    meeting_scheduled: { label: 'Reunião Agendada', class: 'bg-[#130e30] text-white' },
    no_answer: { label: 'Não Atendeu', class: 'bg-rose-100 text-rose-800 border border-rose-200' },
    voicemail: { label: 'Caixa Postal', class: 'bg-amber-100 text-amber-800 border border-amber-200' },
    gatekeeper: { label: 'Secretária / Barrou', class: 'bg-orange-100 text-orange-800 border border-orange-200' },
    wrong_number: { label: 'Número Incorreto', class: 'bg-slate-200 text-slate-700' },
  }
  return map[outcome] || { label: outcome, class: 'bg-[#eff2e5] text-[#130e30] border border-[#130e30]/10' }
}

// Watch companies prop to keep open modal up to date
watch(() => props.companies, (newCompanies) => {
  if (editingCompany.value) {
    const updated = (newCompanies || []).find(c => c.id === editingCompany.value.id)
    if (updated) {
      editingCompany.value = updated
    }
  }
}, { deep: true })

// New Lead Modal Handlers
function openNewLeadModal() {
  cnpjSearchQuery.value = ''
  enrichedData.value = null
  newLeadForm.value = {
    name: '',
    job_title: '',
    company_name: '',
    phone: '',
    email: '',
    pipeline_stage_id: props.stages?.[0]?.id || null,
    document: '',
    segment: '',
    custom_attributes: {},
  }
  showNewLeadModal.value = true
}

async function searchCnpjData() {
  const clean = cnpjSearchQuery.value.replace(/\D/g, '')
  if (clean.length < 14) {
    showToast('Informe um CNPJ válido com 14 dígitos.', 'error')
    return
  }

  isEnriching.value = true
  try {
    const res = await axios.post('/companies/enrich-cnpj', { cnpj: cnpjSearchQuery.value })
    if (res.data?.success) {
      enrichedData.value = res.data
      newLeadForm.value.company_name = res.data.name
      newLeadForm.value.document = res.data.cnpj
      newLeadForm.value.segment = res.data.segment
      if (res.data.phone && !newLeadForm.value.phone) {
        newLeadForm.value.phone = res.data.phone
      }
      if (res.data.email && !newLeadForm.value.email) {
        newLeadForm.value.email = res.data.email
      }
      newLeadForm.value.custom_attributes = {
        enrichment: res.data,
        capital_social: res.data.capital_social_formatted,
        address: res.data.full_address,
      }
      showToast('Dados da Receita Federal carregados com sucesso!', 'success')
    }
  } catch (err) {
    console.error('Erro ao consultar CNPJ:', err)
    showToast(err.response?.data?.message || 'CNPJ não encontrado na base pública.', 'error')
  } finally {
    isEnriching.value = false
  }
}

function selectPartnerAsLead(partner) {
  newLeadForm.value.name = partner.name
  newLeadForm.value.job_title = partner.job_title
  showToast(`Decisor ${partner.name} selecionado!`, 'success')
}

async function enrichExistingCompany() {
  if (!companyForm.value.document || !editingCompany.value) return
  isEnrichingCompany.value = true

  try {
    const res = await axios.post(`/companies/${editingCompany.value.id}/apply-enrichment`, { 
      cnpj: companyForm.value.document 
    })
    if (res.data?.success) {
      const updated = res.data.company
      companyForm.value.document = updated.document
      companyForm.value.segment = updated.segment
      if (updated.custom_attributes) {
        editingCompany.value.custom_attributes = updated.custom_attributes
      }
      router.reload({ only: ['companies', 'leads'] })
      showToast('Conta enriquecida com dados da Receita Federal!', 'success')
    }
  } catch (err) {
    console.error('Erro ao enriquecer empresa:', err)
    showToast(err.response?.data?.message || 'Falha ao consultar CNPJ.', 'error')
  } finally {
    isEnrichingCompany.value = false
  }
}

async function importPartnerToCompany(partner) {
  if (!editingCompany.value) return
  try {
    await axios.post(`/companies/${editingCompany.value.id}/contacts`, {
      name: partner.name,
      job_title: partner.job_title || 'Sócio / Decisor',
      phone: editingCompany.value.contacts?.[0]?.phone || '11999990000',
      email: '',
      is_primary: false,
    })
    router.reload({ only: ['companies', 'leads'] })
    showToast(`Contato ${partner.name} adicionado à conta!`, 'success')
  } catch (err) {
    console.error('Erro ao adicionar contato do QSA:', err)
    showToast('Erro ao adicionar contato.', 'error')
  }
}

function submitNewLead() {
  isSubmittingLead.value = true
  router.post('/leads', newLeadForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      showNewLeadModal.value = false
      isSubmittingLead.value = false
      showToast('Conta e contato cadastrados com sucesso!', 'success')
    },
    onError: () => {
      isSubmittingLead.value = false
      showToast('Erro ao cadastrar. Verifique os campos.', 'error')
    },
  })
}

// Import Leads Handlers
function openImportModal() {
  importForm.value = {
    file: null,
    pipeline_stage_id: props.stages?.[0]?.id || null,
  }
  showImportModal.value = true
}

function handleFileSelect(e) {
  const files = e.target.files
  if (files && files[0]) {
    importForm.value.file = files[0]
  }
}

function submitImport() {
  if (!importForm.value.file) return

  isImporting.value = true

  const formData = new FormData()
  formData.append('file', importForm.value.file)
  formData.append('pipeline_stage_id', importForm.value.pipeline_stage_id)

  router.post('/leads/import', formData, {
    preserveScroll: true,
    onSuccess: (page) => {
      showImportModal.value = false
      isImporting.value = false
      importForm.value.file = null

      const successMsg = page.props.flash?.success || 'Importação realizada com sucesso!'
      showToast(successMsg, 'success')
    },
    onError: (errors) => {
      isImporting.value = false
      const errorMsg = errors.file || 'Erro ao processar arquivo. Verifique o formato das colunas.'
      showToast(errorMsg, 'error')
    },
  })
}

function showToast(message, type = 'success') {
  toastMessage.value = message
  toastType.value = type
  setTimeout(() => {
    toastMessage.value = null
  }, 6000)
}

function downloadSampleCsv() {
  const csvContent = "empresa;nome;cargo;telefone;email;setor\n" +
    "Logística Express;Carlos Silveira;Diretor de Operações;5544998881122;carlos@logistica.com;Transportes\n" +
    "Logística Express;Mariana Duarte;Gerente de Compras;5544997773344;mariana@logistica.com;Transportes\n" +
    "Tech Inovação;Lucas Mendes;CEO;5511999887766;lucas@techinovacao.com.br;Tecnologia\n"

  const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement("a")
  link.setAttribute("href", url)
  link.setAttribute("download", "modelo_b2b_sdr_jarvis.csv")
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

// ================= MÉTODOS DE GESTÃO MULTI-TENANT & ACESSOS =================

function switchActiveTenant(tenantId) {
  if (!tenantId) return
  showTenantDropdown.value = false
  router.post(`/admin/tenants/switch/${tenantId}`, {}, {
    preserveScroll: true,
    onSuccess: (page) => {
      showToast(page.props.flash?.success || 'Perfil de CRM alternado com sucesso!')
    }
  })
}

function openNewTenantModal() {
  newTenantForm.value = {
    name: '',
    slug: '',
    description: '',
    document: '',
    ai_name: 'JARVIS SDR Agent',
    ai_persona: 'Consultor Comercial Sênior B2B',
    ai_model: 'gpt-4o-mini',
    ai_prompt: 'Você é o SDR da operação comercial. Seu objetivo é engajar decisores com alto padrão profissional e conduzir para uma demonstração.',
    ai_api_key: '',
  }
  showNewTenantModal.value = true
}

function openEditTenantModal(tenant) {
  editingTenantForm.value = {
    id: tenant.id,
    name: tenant.name,
    slug: tenant.slug || '',
    description: tenant.description || '',
    document: tenant.document || '',
    status: tenant.status || 'active',
    ai_name: tenant.ai_config?.ai_name || '',
    ai_persona: tenant.ai_config?.ai_persona || '',
    ai_model: tenant.ai_config?.ai_model || 'gpt-4o-mini',
    ai_prompt: tenant.ai_config?.ai_prompt || '',
    ai_api_key: '',
  }
  showEditTenantModal.value = true
}

function submitNewTenant() {
  if (!newTenantForm.value.name.trim()) return
  isSavingAdmin.value = true
  router.post('/admin/tenants', newTenantForm.value, {
    preserveScroll: true,
    onSuccess: (page) => {
      isSavingAdmin.value = false
      showNewTenantModal.value = false
      showToast(page.props.flash?.success || 'Novo Perfil de CRM criado!')
    },
    onError: () => {
      isSavingAdmin.value = false
      showToast('Erro ao criar Perfil de CRM.', 'error')
    }
  })
}

function submitEditTenant() {
  if (!editingTenantForm.value.name.trim()) return
  isSavingAdmin.value = true
  router.put(`/admin/tenants/${editingTenantForm.value.id}`, editingTenantForm.value, {
    preserveScroll: true,
    onSuccess: (page) => {
      isSavingAdmin.value = false
      showEditTenantModal.value = false
      showToast(page.props.flash?.success || 'Configurações de CRM salvas!')
    },
    onError: () => {
      isSavingAdmin.value = false
      showToast('Erro ao atualizar CRM.', 'error')
    }
  })
}

function deleteTenant(tenantId) {
  if (!confirm('Deseja realmente excluir este Perfil de CRM? Todas as contas, leads e dados vinculados serão excluídos permanentemente.')) return
  router.delete(`/admin/tenants/${tenantId}`, {
    preserveScroll: true,
    onSuccess: (page) => {
      showToast(page.props.flash?.success || 'Perfil de CRM excluído!')
    },
    onError: (page) => {
      showToast(page.props.flash?.error || 'Erro ao excluir CRM.', 'error')
    }
  })
}

function openNewUserModal() {
  newUserForm.value = {
    name: '',
    email: '',
    password: '',
    role: 'sdr',
    is_superadmin: false,
    tenant_id: props.activeTenant?.id || props.allTenants?.[0]?.id || 1,
    sip_extension: '',
    sip_secret: '',
  }
  showNewUserModal.value = true
}

function openEditUserModal(user) {
  if (!props.isSuperAdmin && (user.is_superadmin || user.role === 'admin')) {
    showToast('Você não tem permissão para editar contas de Administrador.', 'error')
    return
  }

  editingUserForm.value = {
    id: user.id,
    name: user.name,
    email: user.email,
    password: '',
    role: user.role || 'sdr',
    is_superadmin: !!user.is_superadmin,
    is_active: user.is_active !== false,
    tenant_id: user.tenant_id,
    sip_extension: user.sip_extension || '',
    sip_secret: '',
  }
  showEditUserModal.value = true
}

function submitNewUser() {
  if (!newUserForm.value.name.trim() || !newUserForm.value.email.trim() || !newUserForm.value.password) return
  isSavingAdmin.value = true
  router.post('/admin/users', newUserForm.value, {
    preserveScroll: true,
    onSuccess: (page) => {
      isSavingAdmin.value = false
      showNewUserModal.value = false
      showToast(page.props.flash?.success || 'Usuário criado com sucesso!')
    },
    onError: (errs) => {
      isSavingAdmin.value = false
      const msg = errs.email || 'Erro ao cadastrar usuário.'
      showToast(msg, 'error')
    }
  })
}

function submitEditUser() {
  if (!editingUserForm.value.name.trim() || !editingUserForm.value.email.trim()) return
  isSavingAdmin.value = true
  router.put(`/admin/users/${editingUserForm.value.id}`, editingUserForm.value, {
    preserveScroll: true,
    onSuccess: (page) => {
      isSavingAdmin.value = false
      showEditUserModal.value = false
      showToast(page.props.flash?.success || 'Permissões do usuário atualizadas!')
    },
    onError: (errs) => {
      isSavingAdmin.value = false
      const msg = errs.email || 'Erro ao atualizar usuário.'
      showToast(msg, 'error')
    }
  })
}

function deleteUser(userId) {
  const target = (props.allUsers || []).find(u => u.id === userId)
  if (!props.isSuperAdmin && target && (target.is_superadmin || target.role === 'admin')) {
    showToast('Gestores não têm permissão para remover contas de Administrador.', 'error')
    return
  }

  if (!confirm(`Deseja realmente remover o colaborador ${target?.name || ''} da sua equipe?`)) return
  router.delete(`/admin/users/${userId}`, {
    preserveScroll: true,
    onSuccess: (page) => {
      showToast(page.props.flash?.success || 'Colaborador removido da equipe.')
    },
    onError: (page) => {
      showToast(page.props.flash?.error || 'Erro ao remover colaborador.', 'error')
    }
  })
}

function handleLogout() {
  if (!confirm('Deseja realmente encerrar a sessão no JARVIS?')) return
  router.post('/logout')
}

// ================= JARVIS COPILOT (IA) HELPERS =================
function getCompanyAiInsight(company) {
  if (!company) return null
  const contactWithAi = (company.contacts || []).find(c => c.custom_attributes?.ai_analysis)
  if (!contactWithAi) return null

  const analysis = contactWithAi.custom_attributes.ai_analysis
  if (!analysis) return null

  let badgeClass = 'bg-[#eff2e5] border-[#130e30]/15 text-[#130e30]'
  let icon = '✨'
  if (analysis.sentiment === 'positive') {
    badgeClass = 'bg-amber-50 border-amber-300 text-amber-900'
    icon = '🔥'
  } else if (analysis.sentiment === 'objection') {
    badgeClass = 'bg-orange-50 border-orange-300 text-orange-900'
    icon = '⚠️'
  } else if (analysis.sentiment === 'negative') {
    badgeClass = 'bg-slate-100 border-slate-300 text-slate-700'
    icon = '🧊'
  }

  return {
    lead: contactWithAi,
    score: analysis.score || 50,
    label: analysis.sentiment_label || 'Analisado por IA',
    icon: icon,
    class: badgeClass,
  }
}

function handleAiStageAdvanced(payload) {
  router.reload({ only: ['companies', 'leads', 'stages'] })
  showToast(payload.message || 'Conta movida com sucesso!', 'success')
}

function handleAiNotify(payload) {
  showToast(payload.message, payload.type || 'success')
}
</script>
