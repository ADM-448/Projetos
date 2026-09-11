@extends('layouts.app')

@section('title', 'Detalhes da Avaliação')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title">{{ $avaliacao->titulo }}</h1>
        <p style="color: #666; font-size: 0.95rem;">
            Período: {{ $avaliacao->data_inicio->format('d/m/Y') }} até {{ $avaliacao->data_fim->format('d/m/Y') }}
            &bull;
            Status: 
            @if($avaliacao->status->value === 'rascunho')
                <span style="color: #4a5568; font-weight: bold;">Rascunho</span>
            @elseif($avaliacao->status->value === 'andamento')
                <span style="color: #22543d; font-weight: bold;">Em Andamento</span>
            @else
                <span style="color: #2b6cb0; font-weight: bold;">Concluída</span>
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
            <form action="{{ route('avaliacoes.destroy', $avaliacao->id) }}" method="POST" data-confirm="Tem certeza que deseja excluir esta avaliação? Esta ação não pode ser desfeita e excluirá todos os vínculos e respostas." data-confirm-title="Confirmar Exclusão" data-confirm-type="danger">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-secondary" style="color: #e53e3e; border-color: #fed7d7; background: #fff5f5;">
                    <i class="fa-solid fa-trash"></i> Excluir
                </button>
            </form>
        @endcan
        <a href="{{ route('avaliacoes.index') }}" class="btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

{{-- KPIs de Acompanhamento --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; padding: 20px 20px 0;">
    {{-- Total de Participantes --}}
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; border-left: 5px solid #3182ce;">
        <div style="background: rgba(49, 130, 206, 0.1); width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #3182ce; font-size: 1.5rem;">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <div style="font-size: 0.85rem; color: #888; font-weight: 600; text-transform: uppercase;">Total Participantes</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #2d3748; line-height: 1.2;">{{ $totalParticipantesUnicos }}</div>
        </div>
    </div>

    {{-- Respostas Concluídas --}}
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; border-left: 5px solid #38a169;">
        <div style="background: rgba(56, 161, 105, 0.1); width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #38a169; font-size: 1.5rem;">
            <i class="fa-solid fa-check-double"></i>
        </div>
        <div>
            <div style="font-size: 0.85rem; color: #888; font-weight: 600; text-transform: uppercase;">Respondidas</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #2d3748; line-height: 1.2;">{{ $totalRespondidos }}</div>
        </div>
    </div>

    {{-- Pendentes --}}
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; border-left: 5px solid #d69e2e;">
        <div style="background: rgba(214, 158, 46, 0.1); width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #d69e2e; font-size: 1.5rem;">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div>
            <div style="font-size: 0.85rem; color: #888; font-weight: 600; text-transform: uppercase;">Pendentes</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #2d3748; line-height: 1.2;">{{ $totalPendentes }}</div>
        </div>
    </div>

    {{-- Engajamento --}}
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; border-left: 5px solid #6a4c7c;">
        <div style="background: rgba(106, 76, 124, 0.1); width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #6a4c7c; font-size: 1.5rem;">
            <i class="fa-solid fa-fire"></i>
        </div>
        <div style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span style="font-size: 0.85rem; color: #888; font-weight: 600; text-transform: uppercase;">Engajamento</span>
                <span style="color: #6a4c7c; font-weight: 800; font-size: 1.1rem;">{{ $engajamento }}%</span>
            </div>
            <div style="width: 100%; background: #e2e8f0; border-radius: 8px; height: 8px; overflow: hidden;">
                <div style="width: {{ $engajamento }}%; background: linear-gradient(90deg, #9b59b6, #6a4c7c); height: 100%; border-radius: 8px; transition: width 0.5s ease-in-out;"></div>
            </div>
        </div>
    </div>
</div>

<div class="table-container" style="padding: 20px;">
    
    <div style="margin-bottom: 25px;">
        <h3 style="font-size: 1.1rem; color: #2d3748; margin-bottom: 10px;">Descrição da Campanha</h3>
        <p style="color: #4a5568; line-height: 1.5;">{{ $avaliacao->descricao ?: 'Nenhuma descrição informada.' }}</p>
    </div>

    <div style="margin-bottom: 25px;">
        <h3 style="font-size: 1.1rem; color: #2d3748; margin-bottom: 10px;">Competências Avaliadas</h3>
        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
            @forelse($avaliacao->competencias as $comp)
                <span style="background: var(--primary-color); color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.85rem;">
                    {{ $comp->nome }}
                </span>
            @empty
                <span style="color: #718096; font-style: italic;">Nenhuma competência vinculada.</span>
            @endforelse
        </div>
    </div>

    <div>
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #2d3748; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-users-viewfinder" style="color: #6a4c7c;"></i> Participantes da Avaliação
        </h3>
        
        @php
            $participantesAgrupados = $avaliacao->participantes->groupBy('avaliado_id');
        @endphp

        <div style="display: flex; flex-direction: column; gap: 20px;">
            @forelse($participantesAgrupados as $avaliadoId => $participantesDoAvaliado)
                @php
                    $avaliado = $participantesDoAvaliado->first()->avaliado;
                @endphp
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                    <div style="background: #f8fafc; padding: 20px 25px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(106, 76, 124, 0.1); color: #6a4c7c; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>
                            <div>
                                <h4 style="font-size: 1.15rem; color: #1a202c; margin: 0; font-weight: 800;">{{ $avaliado->name }}</h4>
                                <span style="font-size: 0.85rem; color: #718096; font-weight: 600;">Avaliado &bull; {{ $participantesDoAvaliado->count() }} Avaliadores</span>
                            </div>
                        </div>
                    </div>
                    <div style="padding: 0;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tbody>
                                @foreach($participantesDoAvaliado as $part)
                                    <tr style="border-bottom: 1px solid #edf2f7; transition: background 0.2s;" onmouseover="this.style.background='#fbfcfd'" onmouseout="this.style.background='transparent'">
                                        <td style="padding: 15px 25px;">
                                            <div style="display: flex; align-items: center; gap: 12px;">
                                                <div style="width: 36px; height: 36px; border-radius: 50%; background: #edf2f7; display: flex; align-items: center; justify-content: center; color: #a0aec0;">
                                                    <i class="fa-solid fa-user"></i>
                                                </div>
                                                <div>
                                                    <div style="font-weight: 700; color: #2d3748; font-size: 0.95rem;">{{ $part->avaliador->name }}</div>
                                                    <div style="font-size: 0.75rem; color: #a0aec0; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">{{ $part->tipo }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="padding: 15px 25px; text-align: center; width: 160px;">
                                            @if($part->status === 'pendente')
                                                <span style="background: #fff5f5; color: #e53e3e; border: 1px solid #fed7d7; padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; display: inline-flex; align-items: center; gap: 6px;">
                                                    <i class="fa-regular fa-clock"></i> Pendente
                                                </span>
                                            @else
                                                <span style="background: #f0fff4; color: #38a169; border: 1px solid #c6f6d5; padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; display: inline-flex; align-items: center; gap: 6px;">
                                                    <i class="fa-solid fa-check"></i> Respondido
                                                </span>
                                            @endif
                                        </td>
                                        <td style="padding: 15px 25px; text-align: right; width: 140px;">
                                            @if($part->status === 'pendente' && $part->avaliador_id === auth()->id())
                                                <a href="{{ route('avaliacoes.responder', $part->id) }}" class="btn-primary" style="padding: 8px 16px; font-size: 0.85rem; text-decoration: none; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); background: linear-gradient(90deg, #6a4c7c, #4a3556); border: none;">
                                                    <i class="fa-solid fa-pen"></i> Responder
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 40px; background: white; border-radius: 12px; border: 1px dashed #e2e8f0; color: #a0aec0; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                    <i class="fa-solid fa-users-slash" style="font-size: 3rem; margin-bottom: 15px; color: #e2e8f0;"></i>
                    <p style="font-size: 1.1rem; color: #718096; font-weight: 600;">Nenhum participante configurado nesta avaliação.</p>
                </div>
            @endforelse
        </div>
    </div>

    @php
        $avaliadosUnicos = $avaliacao->participantes->pluck('avaliado')->unique('id');
        $podeMediar = auth()->user()->can('mediar', $avaliacao);
    @endphp

    @if($podeMediar)
        <div style="margin-top: 50px; border-top: 2px dashed #e2e8f0; padding-top: 40px; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px;">
                <div>
                    <h3 style="font-size: 1.35rem; color: #2d3748; margin-bottom: 5px; font-weight: 800; display: flex; align-items: center; gap: 10px;">
                        <div style="background: rgba(106, 76, 124, 0.1); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #6a4c7c;">
                            <i class="fa-solid fa-gavel"></i>
                        </div>
                        Painel de Mediação
                    </h3>
                    <p style="color: #718096; font-size: 0.95rem; margin: 0;">Como Mediador, você deve consolidar as notas e registrar o feedback final para os colaboradores abaixo.</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
                @forelse($avaliadosUnicos as $colab)
                    @php
                        // Obter participantes associados a esse avaliado nesta avaliação
                        $partDesteAvaliado = $avaliacao->participantes->where('avaliado_id', $colab->id);
                        $totalVias = $partDesteAvaliado->count();
                        $respondidasVias = $partDesteAvaliado->where('status', 'respondido')->count();
                        
                        // Verificar se já tem fechamento
                        $fechamentoObj = $avaliacao->fechamentos->where('avaliado_id', $colab->id)->first();
                    @endphp
                    
                    <div style="background: white; border-radius: 16px; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); border: 1px solid #edf2f7; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 20px rgba(0,0,0,0.06)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.03)';">
                        
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 46px; height: 46px; border-radius: 50%; background: #f7fafc; border: 2px solid #e2e8f0; display: flex; align-items: center; justify-content: center; color: #a0aec0; font-size: 1.2rem;">
                                        <i class="fa-solid fa-user-tie"></i>
                                    </div>
                                    <div>
                                        <h4 style="font-size: 1.1rem; font-weight: 800; color: #1a202c; margin: 0;">{{ $colab->name }}</h4>
                                        <div style="font-size: 0.8rem; color: #718096; font-weight: 500; margin-top: 2px;">{{ $colab->cargo->nome ?? 'Sem Cargo' }}</div>
                                    </div>
                                </div>
                                @if($fechamentoObj)
                                    <div style="background: #c6f6d5; color: #22543d; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;" title="Mediação Concluída">
                                        <i class="fa-solid fa-check"></i>
                                    </div>
                                @else
                                    <div style="background: #feebc8; color: #dd6b20; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;" title="Aguardando Mediação">
                                        <i class="fa-regular fa-clock"></i>
                                    </div>
                                @endif
                            </div>

                            <div style="background: #f8fafc; border-radius: 10px; padding: 12px 15px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-size: 0.75rem; color: #718096; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Respostas</div>
                                    <div style="font-size: 1.1rem; font-weight: 800; color: #2d3748;">{{ $respondidasVias }} <span style="font-size: 0.85rem; color: #a0aec0; font-weight: 600;">/ {{ $totalVias }}</span></div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 0.75rem; color: #718096; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Nota Final</div>
                                    <div style="font-size: 1.25rem; font-weight: 900; color: {{ $fechamentoObj ? '#6a4c7c' : '#a0aec0' }};">
                                        {{ $fechamentoObj ? number_format($fechamentoObj->nota_final, 2) : '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('avaliacoes.mediar', [$avaliacao->id, $colab->id]) }}" class="btn-primary" style="width: 100%; display: flex; justify-content: center; align-items: center; gap: 8px; padding: 10px; border-radius: 10px; font-weight: 700; font-size: 0.9rem; {{ $fechamentoObj ? 'background: #edf2f7; color: #4a5568; border: 1px solid #e2e8f0;' : 'background: linear-gradient(90deg, #6a4c7c, #4a3556); border: none;' }}">
                            @if($fechamentoObj)
                                <i class="fa-solid fa-pen-to-square"></i> Reabrir / Editar
                            @else
                                <i class="fa-solid fa-gavel"></i> Mediar & Fechar
                            @endif
                        </a>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: #fafbfc; border-radius: 16px; border: 1px dashed #cbd5e0; color: #a0aec0;">
                        <i class="fa-solid fa-users-slash" style="font-size: 2.5rem; margin-bottom: 15px;"></i>
                        <p style="font-size: 1.05rem; font-weight: 600; color: #718096; margin: 0;">Nenhum colaborador avaliado na campanha.</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>
@endsection
