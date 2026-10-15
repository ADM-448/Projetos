@extends('layouts.app')

@section('title', 'Detalhes da Avaliação')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<style>
    .avaliado-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px -5px rgba(0,0,0,0.06);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        overflow: hidden;
    }
    .avaliado-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px -8px rgba(0,0,0,0.12);
    }
    .avaliado-card-header {
        padding: 20px 24px 16px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 14px;
        background: linear-gradient(135deg, #fafafa, #ffffff);
    }
    .avaliado-avatar {
        width: 52px; height: 52px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6a4c7c, #8b5cf6);
        color: white;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.35rem;
        font-weight: 800;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(106, 76, 124, 0.3);
    }
    .avaliador-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border-radius: 12px;
        background: #f8fafc;
        margin-bottom: 8px;
        transition: all 0.25s ease;
        border: 1px solid transparent;
    }
    .avaliador-row:hover { 
        background: #f1f5f9; 
        transform: translateX(4px);
    }
    .avaliador-row.is-current {
        background: linear-gradient(to right, #fdf4ff, #faf5ff);
        border: 1px solid rgba(139, 92, 246, 0.3);
        box-shadow: 0 2px 8px rgba(139, 92, 246, 0.08);
    }
    .avaliador-row.is-current:hover {
        background: linear-gradient(to right, #fae8ff, #f3e8ff);
    }
    .badge-respondido {
        background: #dcfce7; color: #15803d;
        border: 1px solid #bbf7d0;
        padding: 4px 10px; border-radius: 20px;
        font-size: 0.75rem; font-weight: 700;
        display: flex; align-items: center; gap: 5px;
    }
    .badge-pendente {
        background: #fff7ed; color: #c2410c;
        border: 1px solid #fed7aa;
        padding: 4px 10px; border-radius: 20px;
        font-size: 0.75rem; font-weight: 700;
        display: flex; align-items: center; gap: 5px;
    }
    .tipo-badge {
        font-size: 0.68rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.5px;
        color: #94a3b8;
        background: #f1f5f9;
        padding: 2px 7px; border-radius: 6px;
    }
    .progress-ring {
        display: flex; align-items: center; gap: 8px;
    }
    .progress-bar-wrap {
        flex: 1; height: 6px;
        background: #e2e8f0; border-radius: 10px; overflow: hidden;
    }
    .progress-bar-fill {
        height: 100%; border-radius: 10px;
        background: linear-gradient(90deg, #6a4c7c, #8b5cf6);
        transition: width 0.6s ease;
    }
</style>

{{-- Header --}}
<div class="header-actions" style="margin-bottom: 28px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
            <h1 class="page-title" style="margin: 0;">{{ $avaliacao->titulo }}</h1>
            <span style="background: {{ $avaliacao->status->bg() }}; color: {{ $avaliacao->status->color() }}; border: 1px solid {{ $avaliacao->status->border() }}; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700;">
                {{ $avaliacao->status->label() }}
            </span>
            @if($avaliacao->recorrencia && $avaliacao->recorrencia !== 'none')
                <span style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; padding: 4px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 700;">
                    <i class="fa-solid fa-repeat"></i> {{ ucfirst($avaliacao->recorrencia) }}
                </span>
            @endif
        </div>
        <p style="color: #64748b; font-size: 0.9rem; margin: 0;">
            <i class="fa-regular fa-calendar" style="margin-right: 4px;"></i>
            {{ $avaliacao->data_inicio->format('d/m/Y') }} até {{ $avaliacao->data_fim ? $avaliacao->data_fim->format('d/m/Y') : 'Indeterminado' }}
            &nbsp;·&nbsp; {{ $avaliacao->grau }}° graus
            @if($avaliacao->template)
                &nbsp;·&nbsp; <i class="fa-solid fa-layer-group" style="color: #6a4c7c;"></i> {{ $avaliacao->template->titulo }}
            @endif
        </p>
    </div>
    <div style="display: flex; gap: 10px;">
        @can('update', $avaliacao)
            <a href="{{ route('avaliacoes.edit', $avaliacao->id) }}" class="btn-secondary" style="color: #2b6cb0; border-color: #bee3f8; background: #ebf8ff;">
                <i class="fa-solid fa-pen"></i> Editar
            </a>
        @endcan
        @can('delete', $avaliacao)
            <form action="{{ route('avaliacoes.destroy', $avaliacao->id) }}" method="POST" onsubmit="return confirm('Tem certeza?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn-secondary" style="color: #e53e3e; border-color: #fed7d7; background: #fff5f5;">
                    <i class="fa-solid fa-trash"></i> Excluir
                </button>
            </form>
        @endcan
        <a href="{{ route('avaliacoes.index') }}?tab=campanhas" class="btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

{{-- KPIs --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px;">
    <div style="background: white; border-radius: 16px; padding: 22px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px; border-left: 5px solid #3182ce;">
        <div style="background: rgba(49,130,206,0.1); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #3182ce; font-size: 1.3rem;">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <div style="font-size: 0.78rem; color: #888; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Participantes</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #2d3748; line-height: 1.1;">{{ $totalParticipantesUnicos }}</div>
        </div>
    </div>
    <div style="background: white; border-radius: 16px; padding: 22px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px; border-left: 5px solid #38a169;">
        <div style="background: rgba(56,161,105,0.1); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #38a169; font-size: 1.3rem;">
            <i class="fa-solid fa-check-double"></i>
        </div>
        <div>
            <div style="font-size: 0.78rem; color: #888; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Respondidas</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #2d3748; line-height: 1.1;">{{ $totalRespondidos }}</div>
        </div>
    </div>
    <div style="background: white; border-radius: 16px; padding: 22px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px; border-left: 5px solid #d69e2e;">
        <div style="background: rgba(214,158,46,0.1); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #d69e2e; font-size: 1.3rem;">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div>
            <div style="font-size: 0.78rem; color: #888; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Pendentes</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #2d3748; line-height: 1.1;">{{ $totalPendentes }}</div>
        </div>
    </div>
    <div style="background: white; border-radius: 16px; padding: 22px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px; border-left: 5px solid #6a4c7c;">
        <div style="background: rgba(106,76,124,0.1); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #6a4c7c; font-size: 1.3rem;">
            <i class="fa-solid fa-fire"></i>
        </div>
        <div style="flex:1;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span style="font-size: 0.78rem; color: #888; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Engajamento</span>
                <span style="color: #6a4c7c; font-weight: 800; font-size: 1.1rem;">{{ $engajamento }}%</span>
            </div>
            <div style="width: 100%; background: #e2e8f0; border-radius: 8px; height: 8px; overflow: hidden;">
                <div style="width: {{ $engajamento }}%; background: linear-gradient(90deg, #9b59b6, #6a4c7c); height: 100%; border-radius: 8px; transition: width 0.6s ease;"></div>
            </div>
        </div>
    </div>
</div>

{{-- Info da campanha --}}
@if($avaliacao->descricao || $avaliacao->competencias->count())
<div style="background: white; border-radius: 16px; padding: 22px 26px; margin-bottom: 28px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
    @if($avaliacao->descricao)
        <p style="color: #4a5568; margin: 0 0 14px 0; font-size: 0.95rem; line-height: 1.6;">{{ $avaliacao->descricao }}</p>
    @endif
    @if($avaliacao->competencias->count())
        <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
            <span style="font-size: 0.8rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-right: 4px;">Competências:</span>
            @foreach($avaliacao->competencias as $comp)
                <span style="background: rgba(106,76,124,0.08); color: #6a4c7c; border: 1px solid rgba(106,76,124,0.2); padding: 4px 12px; border-radius: 20px; font-size: 0.82rem; font-weight: 600;">
                    {{ $comp->nome }}
                </span>
            @endforeach
        </div>
    @endif
</div>
@endif

{{-- Grid de Avaliados --}}
@php
    $avaliacao->load(['fechamentos']);
    $participantesAgrupados = $avaliacao->participantes->groupBy('avaliado_id');
@endphp

<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
    <div style="background: rgba(106,76,124,0.1); width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #6a4c7c;">
        <i class="fa-solid fa-users-viewfinder"></i>
    </div>
    <h2 style="font-size: 1.2rem; font-weight: 800; color: #1e293b; margin: 0;">Participantes</h2>
    <span style="background: #f1f5f9; color: #64748b; padding: 3px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 700;">{{ $participantesAgrupados->count() }} avaliados</span>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; margin-bottom: 40px;">
    @forelse($participantesAgrupados as $avaliadoId => $participantesDoAvaliado)
        @php
            $avaliado = $participantesDoAvaliado->first()->avaliado;
            // Pegar as iniciais (Ex: Joao Silva => JS)
            $nomePartes = explode(' ', trim($avaliado->name));
            $iniciais = strtoupper(substr($nomePartes[0], 0, 1));
            if(count($nomePartes) > 1) {
                $iniciais .= strtoupper(substr(end($nomePartes), 0, 1));
            }
            
            $totalResps = $participantesDoAvaliado->count();
            $respondidos = $participantesDoAvaliado->where('status','respondido')->count();
            $percResp = $totalResps > 0 ? round($respondidos / $totalResps * 100) : 0;
            $fechamento = $avaliacao->fechamentos->where('avaliado_id', $avaliadoId)->first();
        @endphp
        <div class="avaliado-card">
            <div class="avaliado-card-header">
                <div class="avaliado-avatar">{{ $iniciais }}</div>
                <div style="flex:1; min-width: 0;">
                    <div style="font-weight: 800; color: #0f172a; font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $avaliado->name }}</div>
                    <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 2px;">{{ $avaliado->cargo->nome ?? 'Sem cargo' }}</div>
                    <div class="progress-ring" style="margin-top: 8px;">
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-fill" style="width: {{ $percResp }}%;"></div>
                        </div>
                        <span style="font-size: 0.78rem; font-weight: 700; color: {{ $percResp === 100 ? '#15803d' : '#64748b' }}; min-width: 36px;">{{ $percResp }}%</span>
                    </div>
                </div>
                @if($fechamento)
                    <div title="Mediação concluída" style="background: #dcfce7; color: #15803d; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fa-solid fa-check" style="font-size: 0.85rem;"></i>
                    </div>
                @endif
            </div>

            <div style="padding: 16px 20px;">
                {{-- Resumo dos avaliadores --}}
                @foreach($participantesDoAvaliado as $part)
                    @php
                        $isCurrentUser = auth()->id() == $part->avaliador_id;
                    @endphp
                    <div class="avaliador-row {{ $isCurrentUser ? 'is-current' : '' }}">
                        <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                            <div style="width: 34px; height: 34px; border-radius: 50%; background: {{ $isCurrentUser ? '#e879f9' : '#e2e8f0' }}; color: {{ $isCurrentUser ? 'white' : '#64748b' }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.85rem; box-shadow: {{ $isCurrentUser ? '0 2px 6px rgba(232, 121, 249, 0.4)' : 'none' }};">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div style="min-width: 0;">
                                <div style="font-weight: 700; color: {{ $isCurrentUser ? '#4c1d95' : '#334155' }}; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $part->avaliador->name }} {{ $isCurrentUser ? '(Você)' : '' }}
                                </div>
                                <span class="tipo-badge" style="{{ $isCurrentUser ? 'background: rgba(139, 92, 246, 0.1); color: #7c3aed;' : '' }}">{{ $part->tipo instanceof \App\Enums\TipoAvaliacao ? $part->tipo->value : $part->tipo }}</span>
                            </div>
                        </div>
                        <div style="flex-shrink: 0; margin-left: 8px; display: flex; align-items: center; gap: 8px;">
                            @if($part->status === 'respondido')
                                <span class="badge-respondido"><i class="fa-solid fa-check"></i> Respondido</span>
                            @else
                                @if($isCurrentUser && $avaliacao->status->value === 'andamento')
                                    <a href="{{ route('avaliacoes.responder', $part->id) }}" 
                                       style="background: linear-gradient(135deg, #a855f7, #7c3aed); color: white; padding: 6px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; text-decoration: none; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(124, 58, 237, 0.3); transition: all 0.2s;"
                                       onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 6px 14px rgba(124, 58, 237, 0.4)';"
                                       onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 10px rgba(124, 58, 237, 0.3)';">
                                        <i class="fa-solid fa-pen-to-square"></i> Avaliar
                                    </a>
                                @else
                                    <span class="badge-pendente"><i class="fa-regular fa-clock"></i> Pendente</span>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach

                {{-- Ações --}}
                <div style="display: flex; gap: 10px; margin-top: 16px;">
                    <a href="{{ route('avaliacoes.relatorios') }}?avaliacao_id={{ $avaliacao->id }}&colaborador_id={{ $avaliadoId }}&autoload=1"
                       style="flex:1; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 16px; background: linear-gradient(135deg, #6a4c7c, #8b5cf6); color: white; border-radius: 12px; font-size: 0.88rem; font-weight: 700; text-decoration: none; box-shadow: 0 4px 12px rgba(106,76,124,0.25); transition: all 0.2s;"
                       onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(106,76,124,0.35)';"
                       onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 12px rgba(106,76,124,0.25)';">
                        <i class="fa-solid fa-chart-pie"></i> Ver Relatório
                    </a>
                    
                    @can('mediar', $avaliacao)
                        <a href="{{ route('avaliacoes.mediar', [$avaliacao->id, $avaliadoId]) }}"
                           style="flex:1; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 16px; {{ $fechamento ? 'background: #f1f5f9; color: #475569;' : 'background: white; color: #6a4c7c; border: 1.5px solid #6a4c7c;' }} border-radius: 12px; font-size: 0.88rem; font-weight: 700; text-decoration: none; transition: all 0.2s;"
                           onmouseover="this.style.opacity='0.85';" onmouseout="this.style.opacity='1';">
                            <i class="fa-solid fa-gavel"></i> {{ $fechamento ? 'Reabrir' : 'Mediar' }}
                        </a>
                    @endcan
                    
                    @php
                        $meuParticipante = $participantesDoAvaliado->where('avaliador_id', auth()->id())->where('status','pendente')->first();
                    @endphp
                    @if($meuParticipante)
                        <a href="{{ route('avaliacoes.responder', $meuParticipante->id) }}"
                           title="Responder Avaliação"
                           style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 14px; background: #eff6ff; color: #1d4ed8; border: 1.5px solid #bfdbfe; border-radius: 12px; font-size: 0.88rem; font-weight: 700; text-decoration: none; transition: all 0.2s;">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; background: white; border-radius: 20px; border: 1px dashed #e2e8f0; color: #94a3b8;">
            <i class="fa-solid fa-users-slash" style="font-size: 3rem; margin-bottom: 16px; color: #e2e8f0;"></i>
            <p style="font-size: 1.05rem; font-weight: 600; margin: 0;">Nenhum participante configurado.</p>
        </div>
    @endforelse
</div>

@endsection

@push('scripts')
<script>
    // Se precisar de scripts adicionais
</script>
@endpush
