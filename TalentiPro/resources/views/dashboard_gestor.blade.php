@extends('layouts.gestor')
@section('title', 'Dashboard')

@section('content')
<div class="dashboard-container">
    
    {{-- SEÇÃO PESSOAL --}}
    <h2 class="section-title"><i class="fa-solid fa-user-circle"></i> Minha Jornada</h2>
    <div class="cards-grid">
        <!-- Minhas Pendências de Avaliação -->
        <div class="card-status">
            <div class="icon-area icon-orange">
                <i class="fa-solid fa-clipboard-question"></i>
            </div>
            <div class="info-area">
                <h3>Minhas Avaliações</h3>
                <div class="number">{{ $myPendingCount ?? 0 }}</div>
                <p>Pendentes de Resposta</p>
                <a href="{{ route('avaliacoes.pendentes') }}" class="card-link">Responder Agora <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- Meu PDI -->
        <div class="card-status">
            <div class="icon-area icon-blue">
                <i class="fa-solid fa-medal"></i>
            </div>
            <div class="info-area">
                <h3>Meu PDI</h3>
                <div class="number">{{ $myPdiProgress ?? 0 }}%</div>
                <p>Concluído</p>
                <a href="{{ route('pdis.meus') }}" class="card-link">Ver detalhes <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
    </div>


    {{-- SEÇÃO GESTÃO --}}
    <h2 class="section-title" style="margin-top: 40px; border-left-color: #ffcc00;"><i class="fa-solid fa-users-gear"></i> Gestão da Equipe</h2>
    <div class="cards-grid">
        
        <!-- Avaliações do Setor -->
        <div class="card-status">
            <div class="icon-area icon-green">
                <i class="fa-solid fa-star"></i>
            </div>
            <div class="info-area">
                <h3>Avaliações da Equipe</h3>
                {{-- Exemplo: Quantas avaliações eu criei e estão ativas --}}
                <div class="number">{{ $teamActiveEvaluations ?? 0 }}</div>
                <p>Avaliações Ativas</p>
                <a href="{{ route('avaliacoes.index') }}" class="card-link">Gerenciar <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- PDIs do Setor -->
        <div class="card-status">
            <div class="icon-area icon-purple">
                <i class="fa-solid fa-rocket"></i>
            </div>
            <div class="info-area">
                <h3>PDIs da Equipe</h3>
                <div class="number">{{ $teamActivePdis ?? 0 }}</div>
                <p>Em Andamento</p>
                <a href="{{ route('pdis.index') }}" class="card-link">Acompanhar <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>

    </div>

    {{-- SEÇÃO MEDIAÇÃO (Se houver) --}}
    @php
        $isMediator = auth()->user()->perfil_acesso == 'mediador' || 
                      \App\Models\AvaliacaoParticipante::where('avaliador_id', auth()->id())
                        ->where('tipo', 'mediador')
                        ->exists();
    @endphp

    @if($isMediator)
    <h2 class="section-title" style="margin-top: 40px; border-left-color: #27ae60;"><i class="fa-solid fa-scale-balanced"></i> Mediação</h2>
    <div class="cards-grid">
        <div class="card-status">
            <div class="icon-area" style="background: linear-gradient(135deg, #2ecc71, #27ae60); box-shadow: 0 4px 10px rgba(46, 204, 113, 0.3);">
                <i class="fa-solid fa-handshake-angle"></i>
            </div>
            <div class="info-area">
                <h3>Mediação</h3>
                <div class="number">{{ $mediationCount }}</div>
                <p>Casos de Conflito</p>
                <a href="{{ route('avaliacoes.mediar') }}" class="card-link" style="color: #27ae60;">Resolver <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
    .section-title { font-size: 1.2rem; margin-bottom: 20px; color: #555; background: white; padding: 10px 20px; border-radius: 8px; border-left: 5px solid #3b5998; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .cards-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
    
    .card-status { background: white; border-radius: 10px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; border: 1px solid #eee; transition: transform 0.2s; }
    .card-status:hover { transform: translateY(-5px); }

    .icon-area { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; color: white; flex-shrink: 0; }
    .icon-orange { background: linear-gradient(135deg, #f39c12, #e67e22); box-shadow: 0 4px 10px rgba(243, 156, 18, 0.3); }
    .icon-blue { background: linear-gradient(135deg, #3498db, #2980b9); box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3); }
    .icon-green { background: linear-gradient(135deg, #2ecc71, #27ae60); box-shadow: 0 4px 10px rgba(46, 204, 113, 0.3); }
    .icon-purple { background: linear-gradient(135deg, #9b59b6, #8e44ad); box-shadow: 0 4px 10px rgba(155, 89, 182, 0.3); }

    .info-area h3 { margin: 0; font-size: 14px; color: #777; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .info-area .number { font-size: 28px; font-weight: bold; color: #333; margin: 5px 0; }
    .info-area p { margin: 0 0 10px 0; font-size: 12px; color: #999; }
    .card-link { font-size: 13px; font-weight: bold; color: #3b5998; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
    .card-link:hover { text-decoration: underline; }
</style>
@endpush
@endsection
