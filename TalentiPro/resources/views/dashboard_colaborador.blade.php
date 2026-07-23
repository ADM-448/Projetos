@extends('layouts.colaborador')
@section('title', 'Home')



@section('content')
<div class="page-content">
    <h1 class="welcome-title">Dashboard</h1>
    <p class="welcome-subtitle">Visão geral das suas pendências e atividades.</p>

    <div class="cards-grid">

        {{-- CARD 1: Minhas Pendências --}}
        <a href="{{ route('avaliacoes.pendentes') }}" class="dashboard-card">
            <i class="fa-solid fa-user card-bg-icon"></i>
            <div>
                <div class="card-title">Minhas Pendências</div>
                <div class="card-info">{{ $pendenciasCount }} Avaliação(ões)</div>
                <div class="card-sub">{{ $pendenciasCount > 0 ? 'Aguardando resposta' : 'Tudo em dia!' }}</div>
            </div>
            <div class="card-link">Responder <i class="fa-solid fa-arrow-right"></i></div>
        </a>

        {{-- CARD 2: Meu PDI --}}
        <a href="{{ route('pdis.meus') }}" class="dashboard-card">
            <i class="fa-solid fa-medal card-bg-icon"></i>
            <div>
                <div class="card-title">Meu PDI</div>
                <div class="card-info">{{ $pdiProgress }}%</div>
                <div class="card-sub">Progresso Geral</div>
            </div>
            <div class="card-link">Atualizar <i class="fa-solid fa-arrow-right"></i></div>
        </a>

        {{-- CARD 3: Minha Equipe (Só Gestor/Master) --}}
        @if(auth()->user()->perfil_acesso == 'gestor' || auth()->user()->perfil_acesso == 'master' || auth()->user()->perfil_acesso == 'admin')
        <a href="#" class="dashboard-card" style="border-left: 5px solid #ffcc00;">
            <i class="fa-solid fa-users card-bg-icon"></i>
            <div>
                <div class="card-title">Minha Equipe</div>
                <div class="card-info">-</div> {{-- Placeholder --}}
                <div class="card-sub">Colaboradores</div>
            </div>
            <div class="card-link" style="color: #d4a017;">Gerenciar <i class="fa-solid fa-arrow-right"></i></div>
        </a>
        @endif

        {{-- CARD 4: Mediação (Só Mediador) --}}
        @if($isMediator ?? (auth()->user()->perfil_acesso == 'mediador' || auth()->user()->perfil_acesso == 'master' || auth()->user()->perfil_acesso == 'admin'))
        <a href="{{ route('avaliacoes.mediar') }}" class="dashboard-card" style="border-left: 5px solid #27ae60;">
            <i class="fa-solid fa-scale-balanced card-bg-icon"></i>
            <div>
                <div class="card-title">Mediação</div>
                <div class="card-info">{{ $mediationCount }}</div>
                <div class="card-sub">Casos de conflito</div>
            </div>
            <div class="card-link" style="color: #27ae60;">Resolver <i class="fa-solid fa-arrow-right"></i></div>
        </a>
        @endif

    </div>
</div>
@endsection
