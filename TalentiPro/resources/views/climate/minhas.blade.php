@extends('layouts.app')

@section('title', 'Minhas Pesquisas de Clima')

@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<style>
.climate-item-card {
    background: white;
    border-radius: 16px;
    border: 1px solid #f0f2f5;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    padding: 24px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}
</style>

<div class="header-actions" style="margin-bottom: 25px;">
    <div>
        <h1 class="page-title" style="margin-bottom: 8px;">
            <i class="fa-solid fa-heart-pulse" style="color: #ec4899;"></i> Pesquisa de Clima Organizacional
        </h1>
        <p style="color: #64748b; font-size: 0.95rem;">
            Sua opinião é fundamental para evoluirmos nosso ambiente de trabalho. <strong>Todas as respostas são 100% anônimas.</strong>
        </p>
    </div>
</div>

@if(session('success'))
    <div style="background: #f0fff4; border: 1px solid #c6f6d5; color: #276749; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 600;">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
@endif

@if(session('info'))
    <div style="background: #ebf8ff; border: 1px solid #bee3f8; color: #2b6cb0; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 600;">
        <i class="fa-solid fa-circle-info"></i> {{ session('info') }}
    </div>
@endif

@if(session('error'))
    <div style="background: #fff5f5; border: 1px solid #feb2b2; color: #c53030; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 600;">
        <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
    </div>
@endif

<div style="display: flex; flex-direction: column; gap: 15px;">
    @forelse($participacoes as $p)
        @php $s = $p->survey; @endphp
        <div class="climate-item-card">
            <div style="flex: 1; min-width: 250px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                    <h3 style="font-size: 1.15rem; font-weight: 800; color: #1e293b; margin: 0;">{{ $s->titulo }}</h3>
                    <span style="background: #fdf2f8; color: #db2777; border: 1px solid #fbcfe8; padding: 3px 10px; border-radius: 9999px; font-size: 0.75rem; font-weight: 700;">
                        <i class="fa-solid fa-user-shield"></i> 100% Anônima
                    </span>
                </div>

                <p style="color: #64748b; font-size: 0.9rem; margin: 0 0 10px 0; line-height: 1.5;">
                    {{ $s->descricao ?? 'Pesquisa de clima interna para avaliação contínua da satisfação da equipe.' }}
                </p>

                <div style="font-size: 0.8rem; color: #94a3b8; font-weight: 600;">
                    <i class="fa-solid fa-calendar-day"></i> Ativa até {{ $s->data_fim ? $s->data_fim->format('d/m/Y') : 'prazo indeterminado' }}
                </div>
            </div>

            <div>
                @if($p->status === 'respondido')
                    <span style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 12px; background: #dcfce7; color: #166534; font-weight: 700; font-size: 0.9rem; border: 1px solid #bbf7d0;">
                        <i class="fa-solid fa-circle-check"></i> Resposta Enviada
                    </span>
                @else
                    <a href="{{ route('clima.responder', $s->id) }}" 
                       class="btn-primary" 
                       style="padding: 12px 24px; border-radius: 12px; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); box-shadow: 0 4px 14px rgba(236, 72, 153, 0.35); color: white;">
                        <i class="fa-solid fa-heart-pulse"></i> Responder Pesquisa Anônima
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div style="background: white; border-radius: 16px; padding: 60px 20px; text-align: center; color: #a0aec0; border: 1px solid #f0f2f5;">
            <i class="fa-solid fa-heart-circle-check" style="font-size: 3rem; color: #cbd5e0; margin-bottom: 15px; display: block;"></i>
            <h3 style="font-size: 1.2rem; font-weight: 800; color: #334155; margin-bottom: 6px;">Nenhuma pesquisa ativa pendente no momento</h3>
            <p style="color: #64748b; font-size: 0.9rem; max-width: 450px; margin: 0 auto;">
                Quando uma nova pesquisa de clima for disponibilizada pelo RH, ela aparecerá aqui para você responder com total anonimato.
            </p>
        </div>
    @endforelse
</div>
@endsection
