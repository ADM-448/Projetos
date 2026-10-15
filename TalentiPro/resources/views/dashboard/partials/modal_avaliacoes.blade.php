@if($avaliacoes->isEmpty())
    <div style="text-align: center; color: #a0aec0; padding: 40px 0; z-index: 2;">
        <i class="fa-solid fa-ghost" style="font-size: 3rem; color: #e2e8f0; margin-bottom: 15px;"></i>
        <p style="margin: 0;">Nenhuma avaliação ativa no momento.</p>
    </div>
@else
    <div class="timeline-container" style="display: flex; flex-direction: column; gap: 15px; padding-right: 10px;">
        @foreach($avaliacoes as $avaliacao)
            <div style="background: white; border: 1px solid #edf2f7; border-radius: 12px; padding: 16px 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <h4 style="font-size: 1.1rem; font-weight: 700; color: #2d3748; margin: 0 0 10px 0; display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                    <div><i class="fa-solid fa-star-half-stroke" style="color: #f39c12; margin-right: 8px;"></i> {{ $avaliacao->titulo }}</div>
                    <span style="display: inline-flex; align-items: center; white-space: nowrap; font-size: 0.75rem; color: {{ $avaliacao->status->color() }}; font-weight: 600; background: {{ $avaliacao->status->bg() }}; border: 1px solid {{ $avaliacao->status->border() }}; padding: 4px 8px; border-radius: 6px;">
                        {{ $avaliacao->status->label() }}
                    </span>
                    @if($avaliacao->data_fim)
                        <span style="display: inline-flex; align-items: center; white-space: nowrap; font-size: 0.75rem; color: #a0aec0; font-weight: 600; background: #f7fafc; padding: 4px 8px; border-radius: 6px;">
                            Até {{ $avaliacao->data_fim->format('d/m/Y') }}
                        </span>
                    @else
                        <span style="display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; font-size: 0.75rem; color: #059669; font-weight: 600; background: #ecfdf5; padding: 4px 8px; border-radius: 6px;">
                            <i class="fa-solid fa-repeat"></i> Recorrente
                        </span>
                    @endif
                </h4>
                
                @php
                    // Agrupa participantes pelo avaliador (quem tem que responder)
                    $avaliadores = $avaliacao->participantes->groupBy('avaliador_id');
                @endphp
                
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    @foreach($avaliadores as $avaliadorId => $participantes)
                        @php
                            $avaliador = $participantes->first()->avaliador;
                            $total = $participantes->count();
                            $concluidos = $participantes->where('status', 'respondido')->count();
                            $isFinalizado = ($total > 0 && $total === $concluidos);
                        @endphp
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; background: #f8fafc; padding: 10px 15px; border-radius: 8px;">
                            <div style="font-size: 0.95rem; font-weight: 600; color: #4a5568; display: flex; align-items: center; min-width: 0; word-break: break-word;">
                                <i class="fa-solid fa-user" style="color: #cbd5e0; margin-right: 8px; flex-shrink: 0;"></i>
                                <span style="min-width: 0;">{{ $avaliador->name ?? 'Usuário Removido' }}</span>
                            </div>
                            <div>
                                @if($isFinalizado)
                                    <span style="display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; background: #c6f6d5; color: #22543d; font-size: 0.75rem; font-weight: 700; padding: 4px 10px; border-radius: 9999px;">
                                        <i class="fa-solid fa-check"></i> Finalizado
                                    </span>
                                @else
                                    <span style="display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; background: #feebc8; color: #c05621; font-size: 0.75rem; font-weight: 700; padding: 4px 10px; border-radius: 9999px;">
                                        <i class="fa-solid fa-clock"></i> Pendente
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endif
