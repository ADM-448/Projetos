@extends('layouts.app')

@section('title', 'Gestão - Pesquisas de Clima')

@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<style>
.climate-card {
    background: white;
    border-radius: 16px;
    border: 1px solid #f0f2f5;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    padding: 24px;
    margin-bottom: 20px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.climate-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.06);
}

.status-badge {
    padding: 5px 14px;
    border-radius: 9999px;
    font-size: 0.78rem;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    letter-spacing: 0.3px;
}
.status-ativa { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.status-rascunho { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.status-encerrada { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

.btn-create-survey {
    background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
    color: white !important;
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 14px rgba(236, 72, 153, 0.35);
    transition: all 0.2s;
}
.btn-create-survey:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(236, 72, 153, 0.5);
}
</style>

<div class="header-actions" style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div>
        <h1 class="page-title" style="margin-bottom: 8px;">
            <i class="fa-solid fa-heart-pulse" style="color: #ec4899;"></i> Pesquisas de Clima Organizacional
        </h1>
        <p style="color: #64748b; font-size: 0.95rem;">
            Gerencie pesquisas de satisfação, engajamento e clima da empresa com 100% de anonimato para a equipe.
        </p>
    </div>

    @if(in_array(auth()->user()->perfil_acesso->value, ['master', 'admin']))
        <a href="{{ route('gestao.clima.create') }}" class="btn-create-survey">
            <i class="fa-solid fa-plus"></i> Nova Pesquisa de Clima
        </a>
    @endif
</div>

@if(session('success'))
    <div style="background: #f0fff4; border: 1px solid #c6f6d5; color: #276749; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 600;">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background: #fff5f5; border: 1px solid #feb2b2; color: #c53030; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 600;">
        <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
    </div>
@endif

<div style="display: flex; flex-direction: column; gap: 15px;">
    @forelse($surveys as $s)
        <div class="climate-card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px; margin-bottom: 15px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 6px;">
                        <h2 style="font-size: 1.25rem; font-weight: 800; color: #1e293b; margin: 0;">{{ $s->titulo }}</h2>
                        <span class="status-badge status-{{ $s->status }}">
                            @if($s->status === 'ativa')
                                <i class="fa-solid fa-wifi"></i> Ativa
                            @elseif($s->status === 'encerrada')
                                <i class="fa-solid fa-lock"></i> Encerrada
                            @else
                                <i class="fa-solid fa-pen-ruler"></i> Rascunho
                            @endif
                        </span>
                        <span style="background: #fdf2f8; color: #db2777; border: 1px solid #fbcfe8; padding: 3px 10px; border-radius: 9999px; font-size: 0.75rem; font-weight: 700;">
                            <i class="fa-solid fa-user-shield"></i> 100% Anônima
                        </span>
                    </div>
                    @if($s->descricao)
                        <p style="color: #64748b; font-size: 0.9rem; margin: 0 0 10px 0; line-height: 1.5;">{{ $s->descricao }}</p>
                    @endif
                </div>

                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    @if($s->status === 'rascunho' && in_array(auth()->user()->perfil_acesso->value, ['master', 'admin']))
                        <a href="{{ route('gestao.clima.edit', $s->id) }}" 
                           style="padding: 10px 18px; border-radius: 10px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; background: #3b82f6; color: white; border: none; box-shadow: 0 4px 14px rgba(59, 130, 246, 0.35);">
                            <i class="fa-solid fa-pen"></i> Editar Rascunho
                        </a>
                    @else
                        <a href="{{ route('gestao.clima.relatorio', $s->id) }}" 
                           class="btn-primary" 
                           style="padding: 10px 18px; border-radius: 10px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-chart-line"></i> Dashboard & Relatório
                        </a>
                    @endif

                    @if($s->status === 'ativa' && in_array(auth()->user()->perfil_acesso->value, ['master', 'admin']))
                        <form action="{{ route('gestao.clima.encerrar', $s->id) }}" method="POST" data-confirm="Deseja realmente encerrar esta pesquisa de clima?" data-confirm-title="Encerrar Pesquisa" data-confirm-type="danger" style="margin:0;">
                            @csrf
                            <button type="submit" style="padding: 10px 16px; border-radius: 10px; border: 1px solid #fecaca; background: #fff5f5; color: #c53030; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-stop-circle"></i> Encerrar
                            </button>
                        </form>
                    @endif

                    @if(in_array(auth()->user()->perfil_acesso->value, ['master', 'admin']))
                        <form action="{{ route('gestao.clima.destroy', $s->id) }}" method="POST" data-confirm="ATENÇÃO: Deseja realmente excluir esta pesquisa e todos os dados vinculados a ela? Esta ação é irreversível." data-confirm-title="Excluir Pesquisa" data-confirm-type="danger" style="margin:0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="padding: 10px 14px; border-radius: 10px; border: 1px solid #fecaca; background: #fff5f5; color: #c53030; font-weight: 600; cursor: pointer; transition: all 0.2s;" title="Excluir Pesquisa" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fff5f5'">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- MÉTRICAS RESUMIDAS DA PESQUISA --}}
            <div style="display: flex; gap: 20px; flex-wrap: wrap; background: #fafbfc; border-radius: 12px; padding: 14px 18px; border: 1px solid #edf2f7; align-items: center; justify-content: space-between;">
                <div style="display: flex; gap: 25px; flex-wrap: wrap;">
                    @php
                        $realParticipantes = $s->participacoes_count;
                        if ($s->status === 'rascunho') {
                            $query = \App\Models\User::where('empresa_id', $s->empresa_id)->where('status', 'ativo');
                            if ($s->departamento_id) {
                                $query->where('departamento_id', $s->departamento_id);
                            }
                            $realParticipantes = $query->count();
                        }
                        
                        $respondidosCount = $s->participacoes->where('status', 'respondido')->count();
                        $pctAdesao = $realParticipantes > 0 ? round(($respondidosCount / $realParticipantes) * 100) : 0;
                    @endphp

                    <div>
                        <span style="font-size: 0.78rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Participantes:</span>
                        <span style="background: #f1f5f9; color: #1e293b; padding: 4px 12px; border-radius: 9999px; font-size: 0.85rem; font-weight: 700; margin-left: 6px;">
                            <i class="fa-solid fa-users" style="color: #94a3b8; margin-right: 4px;"></i> {{ $realParticipantes }} vínculos
                        </span>
                    </div>

                    <div>
                        <span style="font-size: 0.78rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Taxa de Adesão:</span>
                        <span style="font-size: 0.95rem; font-weight: 800; color: #166534; margin-left: 6px;">
                            {{ $respondidosCount }} / {{ $realParticipantes }} ({{ $pctAdesao }}%)
                        </span>
                    </div>

                    @if($s->data_inicio)
                        <div>
                            <span style="font-size: 0.78rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Período:</span>
                            <span style="font-size: 0.88rem; font-weight: 700; color: #334155; margin-left: 6px;">
                                {{ $s->data_inicio->format('d/m/Y') }} {{ $s->data_fim ? 'até ' . $s->data_fim->format('d/m/Y') : '' }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div style="background: white; border-radius: 16px; padding: 60px 20px; text-align: center; color: #a0aec0; border: 1px solid #f0f2f5;">
            <i class="fa-solid fa-heart-pulse" style="font-size: 3rem; color: #fbcfe8; margin-bottom: 15px; display: block;"></i>
            <h3 style="font-size: 1.2rem; font-weight: 800; color: #334155; margin-bottom: 6px;">Nenhuma Pesquisa de Clima cadastrada</h3>
            <p style="color: #64748b; font-size: 0.9rem; max-width: 450px; margin: 0 auto 20px auto;">
                Crie sua primeira pesquisa de clima para ouvir sua equipe e tomar decisões estratégicas baseadas em dados.
            </p>
            @if(in_array(auth()->user()->perfil_acesso->value, ['master', 'admin']))
                <a href="{{ route('gestao.clima.create') }}" class="btn-create-survey">
                    <i class="fa-solid fa-plus"></i> Criar Primeira Pesquisa
                </a>
            @endif
        </div>
    @endforelse
</div>
@endsection
