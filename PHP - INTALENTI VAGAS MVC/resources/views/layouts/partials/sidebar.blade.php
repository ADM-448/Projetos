<aside class="pl-app-sidebar d-none d-lg-flex" aria-label="Menu lateral">
    <nav class="d-flex flex-column gap-1">
            @guest
                <a href="{{ route('vagas.index') }}" class="pl-sidebar-link {{ request()->routeIs('vagas.index', 'vagas.show', 'home') && !in_array(request('tab'), ['applied', 'saved']) ? 'active' : '' }}">
                    <i class="fa-solid fa-briefcase"></i>
                    <span>Todas as Vagas</span>
                </a>
            @endguest

            @auth
                @if(Auth::user()->isCandidato())
                    <a href="{{ route('vagas.index') }}" class="pl-sidebar-link {{ request()->routeIs('vagas.index', 'vagas.show', 'home') && !in_array(request('tab'), ['applied', 'saved']) ? 'active' : '' }}">
                        <i class="fa-solid fa-briefcase"></i>
                        <span>Vagas</span>
                    </a>
                    <a href="{{ route('vagas.index', ['tab' => 'applied']) }}" class="pl-sidebar-link {{ request('tab') == 'applied' ? 'active' : '' }}">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Candidaturas</span>
                    </a>
                    <a href="{{ route('vagas.index', ['tab' => 'saved']) }}" class="pl-sidebar-link {{ request('tab') == 'saved' ? 'active' : '' }}">
                        <i class="fa-regular fa-bookmark"></i>
                        <span>Vagas Salvas</span>
                    </a>
                    <a href="{{ route('curriculo.edit') }}" class="pl-sidebar-link {{ request()->routeIs('curriculo.*') ? 'active' : '' }}">
                        <i class="fa-regular fa-id-card"></i>
                        <span>Meu Currículo</span>
                    </a>
                @elseif(Auth::user()->isEmpresa())
                    <a href="{{ route('dashboard') }}" class="pl-sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-gauge-high"></i>
                        <span>Painel Empresa</span>
                    </a>
                    <a href="{{ route('vagas.index') }}" class="pl-sidebar-link {{ request()->routeIs('vagas.index') ? 'active' : '' }}">
                        <i class="fa-solid fa-briefcase"></i>
                        <span>Minhas Vagas</span>
                    </a>
                    <a href="{{ route('vagas.create') }}" class="pl-sidebar-link {{ request()->routeIs('vagas.create', 'vagas.edit', 'vagas.candidatos') ? 'active' : '' }}">
                        <i class="fa-solid fa-plus-circle"></i>
                        <span>Publicar Vaga</span>
                    </a>
                    <a href="{{ route('empresa.equipe') }}" class="pl-sidebar-link {{ request()->routeIs('empresa.equipe') ? 'active' : '' }}">
                        <i class="fa-solid fa-users"></i>
                        <span>Equipe / Usuários</span>
                    </a>
                @elseif(Auth::user()->isSuperAdmin())
                    <a href="{{ route('admin.empresas') }}" class="pl-sidebar-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Gestão de Portais</span>
                        @php
                            $pendentesSideCount = \App\Models\Empresa::where('tipo', 'prefeitura')->where('status', false)->count();
                        @endphp
                        @if($pendentesSideCount > 0)
                            <span class="badge bg-warning text-dark rounded-pill ms-auto" style="font-size: 0.68rem;">{{ $pendentesSideCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('prefeitura.dashboard') }}" class="pl-sidebar-link {{ request()->routeIs('prefeitura.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-landmark"></i>
                        <span>Observatório</span>
                    </a>
                    <a href="{{ route('vagas.index') }}" class="pl-sidebar-link {{ request()->routeIs('vagas.index', 'vagas.show') ? 'active' : '' }}">
                        <i class="fa-solid fa-briefcase"></i>
                        <span>Todas as Vagas</span>
                    </a>
                @elseif(Auth::user()->isPrefeitura())
                    <a href="{{ route('prefeitura.dashboard') }}" class="pl-sidebar-link {{ request()->routeIs('prefeitura.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-landmark"></i>
                        <span>Observatório</span>
                    </a>
                    <a href="{{ route('vagas.index') }}" class="pl-sidebar-link {{ request()->routeIs('vagas.index', 'vagas.show') ? 'active' : '' }}">
                        <i class="fa-solid fa-briefcase"></i>
                        <span>Vagas do Município</span>
                    </a>
                @endif
            @endauth
        </nav>

    <div class="pt-3 border-top">
        @auth
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-success fw-bold" style="width: 36px; height: 36px;">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="overflow-hidden">
                    <div class="fw-bold text-truncate small">{{ Auth::user()->primeiro_nome }}</div>
                    <div class="text-muted text-truncate" style="font-size: 0.72rem;">
                        {{ Auth::user()->isCandidato() ? 'Candidato' : (Auth::user()->isSuperAdmin() ? 'Super Administrador' : (Auth::user()->isPrefeitura() ? 'Gestor Público (' . Auth::user()->getCidadePrefeitura() . ')' : 'Empresa / RH')) }}
                    </div>
                </div>
            </div>
        @else
            <div class="p-3 bg-light rounded-3 text-center mb-2">
                <p class="small text-muted mb-2">Acesse todas as vagas e candidate-se com 1 clique.</p>
                <a href="{{ route('login') }}" class="btn btn-pl-primary btn-sm w-100">Entrar / Criar Conta</a>
            </div>
        @endauth

        <div class="mt-3 text-muted" style="font-size: 0.72rem;">
            &copy; {{ date('Y') }} IntalenTI &bull; Peerlist Edition
        </div>
    </div>
</aside>
