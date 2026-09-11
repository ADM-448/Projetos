<aside class="sidebar" style="overflow-y: auto; display: flex; flex-direction: column;">
    <div
        style="padding: 20px; text-align: center; border-bottom: 1px solid rgba(255, 255, 255, 0.1); margin-bottom: 20px; flex-shrink: 0;">
        <span style="font-size: 1.5rem; font-weight: 800; letter-spacing: 1px; color: white;">
            <i class="fa-solid fa-brain" style="color: #f39c12; margin-right: 8px;"></i>TalentiPro
        </span>
        @if(auth()->user()->empresa)
            <div style="font-size: 0.8rem; color: rgba(255, 255, 255, 0.7); margin-top: 5px; font-weight: 500;">
                {{ auth()->user()->empresa->nome }}
            </div>
        @else
            <div style="font-size: 0.8rem; color: #f39c12; margin-top: 5px; font-weight: 600; text-transform: uppercase;">
                Painel Global SaaS
            </div>
        @endif
    </div>

    <ul class="menu-list" style="flex-grow: 1; overflow-y: auto;">
        @if(auth()->user()->perfil_acesso->value === 'admin')
            {{-- MENU DO ADMINISTRADOR SAAS --}}
            <li class="menu-header">Administração SaaS</li>

            <li>
                <a href="{{ route('admin.dashboard') }}"
                    class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <div><i class="fa-solid fa-chart-line"></i> Painel Geral</div>
                </a>
            </li>

            <li>
                <a class="menu-link">
                    <div><i class="fa-solid fa-building"></i> Empresas (Tenants)</div>
                    <span class="badge"
                        style="background-color: #f39c12; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; font-weight: bold;">SaaS</span>
                </a>
            </li>

            <li>
                <a class="menu-link">
                    <div><i class="fa-solid fa-users-gear"></i> Usuários Globais</div>
                </a>
            </li>

            <li>
                <a class="menu-link">
                    <div><i class="fa-solid fa-receipt"></i> Planos & Cobrança</div>
                </a>
            </li>

            <li>
                <a class="menu-link">
                    <div><i class="fa-solid fa-sliders"></i> Configurações</div>
                </a>
            </li>

            <li>
                <a href="{{ route('whatsapp.config') }}"
                    class="menu-link {{ request()->routeIs('whatsapp.*') ? 'active' : '' }}">
                    <div><i class="fa-brands fa-whatsapp" style="color: #25d366;"></i> WhatsApp API</div>
                </a>
            </li>
        @else
            {{-- MENU DO TENANT (MASTER, GESTOR, COLABORADOR) --}}

            <li class="menu-header">PESSOAL</li>

            @php
                $isMasterOrGestor = in_array(auth()->user()->perfil_acesso->value, ['master', 'gestor']);
                $isActivePessoal = request()->routeIs('dashboard') || request()->routeIs('avaliacoes.minhas') || request()->routeIs('pdis.*') || request()->routeIs('avaliacoes.relatorios.meus') || request()->routeIs('disc.take') || request()->routeIs('clima.minhas') || request()->routeIs('solicitacoes.*') || request()->routeIs('profile.edit');
                $itemStyle = $isMasterOrGestor ? 'padding: 8px 15px; font-size: 0.9rem;' : '';
            @endphp

            @if($isMasterOrGestor)
                <li style="margin-bottom: 5px;">
                    <a class="menu-link {{ $isActivePessoal ? 'active' : '' }}"
                        style="justify-content: space-between; cursor: pointer;" onclick="toggleSubmenu('submenuPessoal')">
                        <div><i class="fa-solid fa-user-astronaut"></i> Meu Painel</div>
                        <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem;"></i>
                    </a>
                    <ul class="submenu" id="submenuPessoal"
                        style="display: {{ $isActivePessoal ? 'block' : 'none' }}; padding-left: 25px; list-style: none; margin-top: 5px;">
            @endif

                    <li>
                        <a href="{{ route('dashboard') }}"
                            class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                            style="{{ $itemStyle }}">
                            <div><i class="fa-solid fa-house"></i> Home</div>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('avaliacoes.minhas', ['tab' => 'autoavaliacao']) }}"
                            class="menu-link {{ request()->routeIs('avaliacoes.minhas') && (!request()->has('tab') || request('tab') == 'autoavaliacao') ? 'active' : '' }}"
                            style="{{ $itemStyle }}">
                            <div><i class="fa-solid fa-clipboard-check"></i> Minhas Avaliações</div>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('pdis.index', ['tab' => 'meus']) }}"
                            class="menu-link {{ request()->routeIs('pdis.*') ? 'active' : '' }}" style="{{ $itemStyle }}">
                            <div><i class="fa-solid fa-medal"></i> Meu PDI</div>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('avaliacoes.relatorios.meus') }}"
                            class="menu-link {{ request()->routeIs('avaliacoes.relatorios.meus') ? 'active' : '' }}"
                            style="{{ $itemStyle }}">
                            <div><i class="fa-solid fa-chart-pie"></i> Meus Resultados</div>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('disc.take') }}"
                            class="menu-link {{ (request()->routeIs('disc.take') || request()->routeIs('disc.store') || request()->routeIs('disc.result')) ? 'active' : '' }}"
                            style="{{ $itemStyle }}">
                            <div><i class="fa-solid fa-puzzle-piece"></i> Teste DISC</div>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('clima.minhas') }}"
                            class="menu-link {{ request()->routeIs('clima.*') ? 'active' : '' }}" style="{{ $itemStyle }}">
                            <div><i class="fa-solid fa-heart-pulse" style="color: #ec4899;"></i> Pesquisa de Clima</div>
                            @php
                                $climaPendentes = \App\Models\ClimateSurveyParticipation::where('user_id', auth()->id())
                                    ->where('status', 'pendente')
                                    ->whereHas('survey', fn($q) => $q->where('status', 'ativa'))
                                    ->count();
                            @endphp
                            @if($climaPendentes > 0)
                                <span
                                    style="background:#ec4899; color:white; padding:2px 7px; border-radius:10px; font-size:0.72rem; font-weight:700;">{{ $climaPendentes }}</span>
                            @endif
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('solicitacoes.index') }}"
                            class="menu-link {{ request()->routeIs('solicitacoes.*') ? 'active' : '' }}"
                            style="{{ $itemStyle }}">
                            <div><i class="fa-solid fa-inbox"></i> Minhas Solicitações</div>
                            @php $pendentes = \App\Models\Solicitacao::contarPendentes(userId: auth()->id()); @endphp
                            @if($pendentes > 0)
                                <span
                                    style="background:#ef4444; color:white; padding:2px 7px; border-radius:10px; font-size:0.72rem; font-weight:700;">{{ $pendentes }}</span>
                            @endif
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('profile.edit') }}"
                            class="menu-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}"
                            style="{{ $itemStyle }}">
                            <div><i class="fa-solid fa-user"></i> Meus Dados</div>
                        </a>
                    </li>

                    @if($isMasterOrGestor)
                            </ul>
                        </li>
                    @endif

            @if(in_array(auth()->user()->perfil_acesso->value, ['master', 'gestor']))
                <li class="menu-header" style="margin-top: 20px;">SEÇÃO CADASTROS</li>

                {{-- Dropdown: Cadastros --}}
                <li style="margin-bottom: 5px;">
                    <a class="menu-link" style="justify-content: space-between; cursor: pointer;"
                        onclick="toggleSubmenu('submenuCadastros')">
                        <div><i class="fa-solid fa-users-viewfinder"></i> Cadastros</div>
                        <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem;"></i>
                    </a>
                    <ul class="submenu" id="submenuCadastros"
                        style="display: {{ request()->routeIs('departamentos.*') || request()->routeIs('cargos.*') || request()->routeIs('colaboradores.*') || request()->routeIs('competencias.*') ? 'block' : 'none' }}; padding-left: 25px; list-style: none; margin-top: 5px;">
                        <li><a href="{{ route('departamentos.index') }}"
                                class="menu-link {{ request()->routeIs('departamentos.*') ? 'active' : '' }}"
                                style="padding: 8px 15px; font-size: 0.9rem;">Departamentos</a></li>
                        <li><a href="{{ route('cargos.index') }}"
                                class="menu-link {{ request()->routeIs('cargos.*') ? 'active' : '' }}"
                                style="padding: 8px 15px; font-size: 0.9rem;">Cargos</a></li>
                        <li><a href="{{ route('colaboradores.index') }}"
                                class="menu-link {{ request()->routeIs('colaboradores.*') ? 'active' : '' }}"
                                style="padding: 8px 15px; font-size: 0.9rem;">Colaboradores</a></li>
                        <li><a href="{{ route('competencias.index') }}"
                                class="menu-link {{ request()->routeIs('competencias.*') ? 'active' : '' }}"
                                style="padding: 8px 15px; font-size: 0.9rem;">Competências</a></li>
                    </ul>
                </li>

                <li>
                    <a href="{{ route('solicitacoes.index') }}"
                        class="menu-link {{ request()->routeIs('solicitacoes.*') ? 'active' : '' }}">
                        <div><i class="fa-solid fa-inbox"></i> Solicitações</div>
                        @php $solPendentes = \App\Models\Solicitacao::contarPendentes(gestorId: auth()->id()); @endphp
                        @if($solPendentes > 0)
                            <span
                                style="background:#ef4444; color:white; padding:2px 7px; border-radius:10px; font-size:0.72rem; font-weight:700;">{{ $solPendentes }}</span>
                        @endif
                    </a>
                </li>

                <li class="menu-header" style="margin-top: 20px;">RH ORGANIZACIONAL</li>

                <li>
                    <a href="{{ route('reunioes.index') }}"
                        class="menu-link {{ request()->routeIs('reunioes.*') ? 'active' : '' }}">
                        <div><i class="fa-solid fa-headset"></i> Reuniões Feedback (IA)</div>
                    </a>
                </li>

                {{-- Dropdown: Avaliações da Equipe --}}
                <li style="margin-bottom: 5px;">
                    <a class="menu-link" style="justify-content: space-between; cursor: pointer;"
                        onclick="toggleSubmenu('submenuGestaoAvaliacoes')">
                        <div><i class="fa-solid fa-star"></i> Avaliações da Equipe</div>
                        <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem;"></i>
                    </a>
                    <ul class="submenu" id="submenuGestaoAvaliacoes"
                        style="display: {{ request()->routeIs('avaliacoes.*') && !request()->routeIs('avaliacoes.relatorios.meus') ? 'block' : 'none' }}; padding-left: 25px; list-style: none; margin-top: 5px;">
                        <li><a href="{{ route('avaliacoes.index') }}"
                                class="menu-link {{ request()->routeIs('avaliacoes.index') ? 'active' : '' }}"
                                style="padding: 8px 15px; font-size: 0.9rem;">Criar Avaliações</a></li>
                        <li><a href="{{ route('avaliacoes.minhas', ['tab' => 'avaliador']) }}"
                                class="menu-link {{ request()->routeIs('avaliacoes.minhas') && request('tab') == 'avaliador' ? 'active' : '' }}"
                                style="padding: 8px 15px; font-size: 0.9rem;">Avaliar Equipe</a></li>
                        <li><a href="{{ route('avaliacoes.minhas', ['tab' => 'mediador']) }}"
                                class="menu-link {{ request()->routeIs('avaliacoes.minhas') && request('tab') == 'mediador' ? 'active' : '' }}"
                                style="padding: 8px 15px; font-size: 0.9rem;">Mediar Avaliações</a></li>
                        <li><a href="{{ route('avaliacoes.relatorios') }}"
                                class="menu-link {{ request()->routeIs('avaliacoes.relatorios') ? 'active' : '' }}"
                                style="padding: 8px 15px; font-size: 0.9rem;">Relatórios da Equipe</a></li>
                    </ul>
                </li>



                <li>
                    <a href="{{ route('pdis.index', ['tab' => 'equipe']) }}"
                        class="menu-link {{ request()->routeIs('pdis.*') ? 'active' : '' }}">
                        <div><i class="fa-solid fa-route"></i> PDIs da Equipe</div>
                    </a>
                </li>

                <li>
                    <a href="{{ route('disc.equipe') }}"
                        class="menu-link {{ request()->routeIs('disc.equipe') ? 'active' : '' }}">
                        <div><i class="fa-solid fa-puzzle-piece"></i> Teste DISC da Equipe</div>
                    </a>
                </li>

                <li>
                    <a href="{{ route('gestao.clima.index') }}"
                        class="menu-link {{ request()->routeIs('gestao.clima.*') ? 'active' : '' }}">
                        <div><i class="fa-solid fa-heart-pulse" style="color: #ec4899;"></i> Pesquisa de Clima (RH)</div>
                    </a>
                </li>

                {{-- Dropdown: Configurações --}}
                <li style="margin-bottom: 5px;">
                    <a class="menu-link" style="justify-content: space-between; cursor: pointer;"
                        onclick="toggleSubmenu('submenuConfig')">
                        <div><i class="fa-solid fa-gear"></i> Configurações</div>
                        <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem;"></i>
                    </a>
                    <ul class="submenu" id="submenuConfig"
                        style="display: {{ request()->routeIs('whatsapp.*') ? 'block' : 'none' }}; padding-left: 25px; list-style: none; margin-top: 5px;">

                        @if(auth()->user()->perfil_acesso->value === 'master' && auth()->user()->empresa?->whatsapp_ativo)
                            <li>
                                <a href="{{ route('whatsapp.config') }}"
                                    class="menu-link {{ request()->routeIs('whatsapp.*') ? 'active' : '' }}"
                                    style="padding: 8px 15px; font-size: 0.9rem; justify-content: flex-start; gap: 8px;">
                                    <i class="fa-brands fa-whatsapp" style="color: #25d366; width: 20px; text-align: center;"></i>
                                    WhatsApp API
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
        @endif
    </ul>

    {{-- Sessão inferior com botão de sair --}}
    <div style="padding: 20px; padding-bottom: 70px; border-top: 1px solid rgba(255, 255, 255, 0.1); flex-shrink: 0;">
        <a class="menu-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
            style="border-radius: 8px; background-color: rgba(231, 76, 60, 0.15); color: #ff7675; transition: 0.3s;">
            <div><i class="fa-solid fa-right-from-bracket"></i> Sair do Sistema</div>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
        <script>
            function toggleSubmenu(id) {
                const el = document.getElementById(id);
                if (el.style.display === 'none' || el.style.display === '') {
                    el.style.display = 'block';
                } else {
                    el.style.display = 'none';
                }
            }
        </script>
    </div>
</aside>