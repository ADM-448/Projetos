<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps({
    demoAccounts: {
        type: Array,
        default: () => []
    },
    errors: {
        type: Object,
        default: () => ({})
    }
})

const showPassword = ref(false)

const form = useForm({
    email: 'admin@jarvis.com',
    password: 'password',
    remember: true,
})

const selectDemoAccount = (account) => {
    form.email = account.email
    form.password = 'password'
}

const submit = () => {
    form.post('/login', {
        onFinish: () => {
            // Mantém o formulário responsivo
        }
    })
}
</script>

<template>
    <Head title="Login - SDR JARVIS" />

    <div class="min-h-screen bg-[#f9fbf2] text-[#130e30] flex flex-col justify-center items-center px-4 py-12 relative selection:bg-[#ffe228] selection:text-[#130e30]">
        <div class="w-full max-w-md relative z-10">
            <!-- Header com Logo Oficial (DESIGN.md - Cockpit Editorial) -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center gap-3.5 mb-3">
                    <div class="w-14 h-14 rounded-2xl bg-white border border-[#130e30]/10 p-1.5 shadow-xs flex items-center justify-center overflow-hidden">
                        <img src="/img/logo.jpeg" alt="SDR JARVIS Logo" class="w-full h-full object-contain" />
                    </div>
                    <div class="text-left">
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-serif-brand font-bold tracking-tight text-[#130e30] leading-none">
                                SDR JARVIS
                            </h1>
                            <span class="text-[9px] uppercase font-bold tracking-widest bg-[#eff2e5] text-[#130e30] border border-[#130e30]/15 px-2 py-0.5 rounded-full">
                                B2B
                            </span>
                        </div>
                        <p class="text-xs text-[#5f5c6e] font-medium tracking-wide uppercase mt-1">
                            Sales Intelligence Cockpit
                        </p>
                    </div>
                </div>
                <p class="text-xs text-[#5f5c6e] max-w-xs mx-auto">
                    Acesse sua operação para coordenar cadências, contas e IAs de prospecção.
                </p>
            </div>

            <!-- Card de Autenticação (Card White com Ink Border) -->
            <div class="bg-white border border-[#130e30]/10 rounded-3xl p-7 shadow-sm">
                <!-- Alerta de Erro -->
                <div v-if="form.errors.email || form.errors.password" class="mb-5 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="font-medium">{{ form.errors.email || form.errors.password }}</span>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Campo E-mail -->
                    <div>
                        <label class="block text-[11px] font-bold text-[#5f5c6e] uppercase tracking-wider mb-1.5">
                            E-mail Profissional
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#5f5c6e]">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input 
                                v-model="form.email" 
                                type="email" 
                                required
                                placeholder="exemplo@jarvis.com"
                                class="w-full bg-[#f9fbf2] border border-[#130e30]/15 rounded-2xl pl-10 pr-4 py-2.5 text-xs text-[#130e30] placeholder-slate-400 focus:outline-none focus:border-[#130e30] focus:ring-1 focus:ring-[#130e30] transition-all"
                            />
                        </div>
                    </div>

                    <!-- Campo Senha -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[11px] font-bold text-[#5f5c6e] uppercase tracking-wider">
                                Senha de Acesso
                            </label>
                            <span class="text-[11px] text-[#5f5c6e]">padrão: <code class="font-mono text-[#130e30] bg-[#eff2e5] px-1.5 py-0.5 rounded">password</code></span>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#5f5c6e]">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input 
                                v-model="form.password" 
                                :type="showPassword ? 'text' : 'password'" 
                                required
                                placeholder="••••••••"
                                class="w-full bg-[#f9fbf2] border border-[#130e30]/15 rounded-2xl pl-10 pr-11 py-2.5 text-xs text-[#130e30] placeholder-slate-400 focus:outline-none focus:border-[#130e30] focus:ring-1 focus:ring-[#130e30] transition-all"
                            />
                            <button 
                                type="button" 
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#5f5c6e] hover:text-[#130e30] transition-colors"
                            >
                                <svg v-if="!showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Lembrar-me -->
                    <div class="flex items-center justify-between pt-0.5">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input 
                                v-model="form.remember" 
                                type="checkbox" 
                                class="w-4 h-4 rounded border-[#130e30]/20 text-[#130e30] focus:ring-0 focus:ring-offset-0 cursor-pointer"
                            />
                            <span class="text-xs text-[#5f5c6e]">Manter conectado neste dispositivo</span>
                        </label>
                    </div>

                    <!-- Botão de Ação Primária (Hi-Yellow Pill) -->
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full mt-2 h-11 px-4 rounded-full bg-[#ffe228] hover:bg-[#fad81d] text-[#130e30] font-bold text-xs tracking-wide shadow-xs hover:brightness-105 active:scale-[0.98] transition-all flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer"
                    >
                        <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span v-else>Entrar no SDR JARVIS</span>
                    </button>
                </form>

                <!-- Divisor de Acesso Rápido -->
                <div class="mt-7 pt-6 border-t border-[#130e30]/10">
                    <p class="text-[10px] font-bold text-[#5f5c6e] uppercase tracking-wider text-center mb-3">
                        ⚡ Acesso Rápido por Perfil (1 Clique)
                    </p>

                    <div class="space-y-2">
                        <button 
                            v-for="acc in demoAccounts" 
                            :key="acc.email"
                            type="button"
                            @click="selectDemoAccount(acc)"
                            :class="[
                                'w-full text-left p-3 rounded-2xl border transition-all flex items-center justify-between cursor-pointer group',
                                form.email === acc.email 
                                    ? 'bg-[#eff2e5] border-[#130e30] shadow-2xs' 
                                    : 'bg-[#f9fbf2] border-[#130e30]/10 hover:border-[#130e30]/30 hover:bg-[#eff2e5]/60'
                            ]"
                        >
                            <div class="min-w-0 pr-2">
                                <div class="text-xs font-bold text-[#130e30] flex items-center gap-1.5">
                                    {{ acc.role_label }}
                                </div>
                                <div class="text-[11px] text-[#5f5c6e] truncate mt-0.5">
                                    {{ acc.email }}
                                </div>
                            </div>
                            <span 
                                :class="[
                                    'text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0 border transition-all',
                                    form.email === acc.email 
                                        ? 'bg-[#130e30] text-[#ffe228] border-[#130e30]' 
                                        : 'bg-white text-[#5f5c6e] border-[#130e30]/10 group-hover:border-[#130e30]/20'
                                ]"
                            >
                                {{ form.email === acc.email ? 'Selecionado' : 'Usar' }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <p class="text-center text-[11px] text-[#5f5c6e] mt-6">
                SDR JARVIS &bull; Cockpit de Prospecção &amp; Inteligência Comercial
            </p>
        </div>
    </div>
</template>
