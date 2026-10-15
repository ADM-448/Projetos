<header class="w-full flex justify-center pt-8 pb-4 sticky top-0 z-40 bg-canvas/80 backdrop-blur-md">
    <div x-data="{ 
        canScrollLeft: false,
        canScrollRight: false,
        hasScroll: false,
        init() {
            this.$nextTick(() => { this.checkScroll(); });
            window.addEventListener('resize', () => this.checkScroll());
        },
        checkScroll() {
            const el = this.$refs.navContainer;
            if(!el) return;
            this.hasScroll = el.scrollWidth > el.clientWidth;
            this.canScrollLeft = el.scrollLeft > 0;
            this.canScrollRight = Math.ceil(el.scrollLeft) < (el.scrollWidth - el.clientWidth - 1);
        },
        scrollLeftNav() {
            if(!this.canScrollLeft) return;
            this.$refs.navContainer.scrollBy({ left: -200, behavior: 'smooth' });
            setTimeout(() => this.checkScroll(), 350);
        },
        scrollRightNav() {
            if(!this.canScrollRight) return;
            this.$refs.navContainer.scrollBy({ left: 200, behavior: 'smooth' });
            setTimeout(() => this.checkScroll(), 350);
        },
        centerTab(button) {
            const container = this.$refs.navContainer;
            if(!container || !button) return;
            const scrollLeft = button.offsetLeft - (container.clientWidth / 2) + (button.clientWidth / 2);
            container.scroll({ left: scrollLeft, behavior: 'smooth' });
            setTimeout(() => this.checkScroll(), 350);
        }
    }" class="flex items-center gap-2 sm:gap-4 w-full max-w-[1200px] mx-4 transition-all">

        <!-- Left Arrow (Outside) -->
        <button x-show="hasScroll" style="display: none;" @click="scrollLeftNav()" class="shrink-0 p-1 transition-all"
            :class="canScrollLeft ? 'opacity-100 text-muted hover:text-neon cursor-pointer' : 'opacity-30 text-muted cursor-default'">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>

        <!-- Pill Container Principal -->
        <div
            class="dark:bg-black bg-surface border border-border-tron rounded-nav-pills px-2 sm:px-6 shadow-none flex items-center justify-between flex-1 overflow-hidden gap-2 sm:gap-6">

            <!-- Logo Text / Brand & Theme Toggle -->
            <div class="shrink-0 ml-2 flex items-center gap-4 py-2">
                <!-- Theme Toggle Button -->
                <button @click="theme = theme === 'dark' ? 'light' : 'dark'; $nextTick(() => window.dispatchEvent(new Event('theme-changed')))" 
                        class="p-2 rounded-full bg-surface border border-border-tron text-muted hover:text-main transition-colors focus:outline-none">
                <!-- Ícone Lua Minguante (aparece no claro, para escurecer) -->
                    <svg x-show="theme === 'light'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                    <!-- Ícone Sol Laranja (aparece no escuro, para clarear) -->
                    <svg x-show="theme === 'dark'" class="w-5 h-5 text-neon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </button>

                <!-- Logo Dinâmica -->
                <img :src="theme === 'dark' ? '/img/logoBranca.jpeg' : '/img/logoPreta.png'" alt="IntalenTI BI" class="h-10 w-auto rounded-sm object-contain">
            </div>

            <!-- Navegação Scrollable -->
            <nav x-ref="navContainer" @scroll.debounce.50ms="checkScroll()"
                class="relative flex-1 flex items-center gap-2 sm:gap-4 text-sm whitespace-nowrap font-sans overflow-x-auto hide-scrollbar scroll-smooth px-4 py-4"
                :class="hasScroll ? 'justify-start' : 'md:justify-center'">
                <template x-for="tab in tabs" :key="tab.id">
                    <button @click="currentTab = tab.id; centerTab($event.currentTarget)" class="px-4 py-2 rounded-full transition-all duration-300"
                        :class="currentTab === tab.id ? 'text-neon font-medium shadow-neon bg-panel' : 'text-muted hover:text-main hover:bg-panel/50'">
                        <span x-text="tab.name"></span>
                    </button>
                </template>
            </nav>

            <!-- Utilitários (Import/Export) -->
            <div
                class="shrink-0 flex items-center gap-3 sm:gap-4 text-sm font-sans px-2 sm:px-3 border-l border-border-tron ml-1 py-2">
                <!-- Relatório Executivo PDF (Plano de Cargos & Salários) -->
                <a href="{{ route('plano-cargos.exportar-pdf') }}" target="_blank"
                    class="bg-panel border border-blue-500/80 text-blue-400 hover:bg-blue-600 hover:text-white transition-all px-3 py-1.5 rounded-md font-medium flex items-center gap-1.5 shadow-[0_0_8px_rgba(59,130,246,0.2)]"
                    title="Baixar Relatório Executivo em PDF com orientações de Dissídio e Governança">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <span class="hidden sm:inline">Relatório PDF</span>
                </a>

                <!-- Baixar Modelo -->
                <a href="{{ route('template.download') }}"
                    class="bg-panel border border-neon text-neon hover:bg-neon hover:text-white transition-all px-3 py-1.5 rounded-md font-medium flex items-center gap-1 shadow-[0_0_8px_rgba(255,85,0,0.2)]"
                    title="Baixar planilha modelo">
                    <svg class="w-4 h-4 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    <span class="hidden sm:inline">Baixar Modelo</span>
                </a>

                <!-- Nova Base (Import) -->
                <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data" x-ref="importForm"
                    class="m-0 hidden">
                    @csrf
                    <input type="file" name="arquivo" x-ref="fileInput" class="hidden" accept=".xlsx,.xls,.csv"
                        @change="$refs.importForm.submit()">
                </form>
                <button type="button" @click="$refs.fileInput.click()"
                    class="bg-panel border border-neon text-neon hover:bg-neon hover:text-white transition-all px-3 py-1.5 rounded-md font-medium flex items-center gap-1 shadow-[0_0_8px_rgba(255,85,0,0.2)]"
                    title="Subir nova base">
                    <svg class="w-4 h-4 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                        </path>
                    </svg>
                    <span class="hidden sm:inline whitespace-nowrap">Nova Base</span>
                </button>
            </div>
        </div>

        <!-- Right Arrow (Outside) -->
        <button x-show="hasScroll" style="display: none;" @click="scrollRightNav()" class="shrink-0 p-1 transition-all"
            :class="canScrollRight ? 'opacity-100 text-muted hover:text-neon cursor-pointer' : 'opacity-30 text-muted cursor-default'">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>

    </div>
</header>

<style>
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>