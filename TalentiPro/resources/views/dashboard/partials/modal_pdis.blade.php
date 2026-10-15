@if($pdis->isEmpty())
    <div style="text-align: center; color: #a0aec0; padding: 40px 0; z-index: 2;">
        <i class="fa-solid fa-ghost" style="font-size: 3rem; color: #e2e8f0; margin-bottom: 15px;"></i>
        <p style="margin: 0;">Nenhum PDI pendente no momento.</p>
    </div>
@else
    <div class="timeline-container" style="display: flex; flex-direction: column; gap: 15px; padding-right: 10px;">
        @foreach($pdis as $pdi)
            <div style="background: white; border: 1px solid #edf2f7; border-radius: 12px; padding: 16px 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px; margin-bottom: 12px;">
                    <div style="min-width: 0;">
                        <h4 style="font-size: 1.05rem; font-weight: 700; color: #2d3748; margin: 0 0 5px 0; word-break: break-word;">
                            <i class="fa-solid fa-bullseye" style="color: #6a4c7c; margin-right: 8px;"></i>
                            {{ $pdi->colaborador->name ?? 'Usuário Removido' }}
                        </h4>
                        <p style="font-size: 0.85rem; color: #718096; margin: 0; padding-left: 25px; word-break: break-word;">
                            {{ $pdi->titulo }}
                        </p>
                    </div>
                    <span style="display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; font-size: 0.75rem; color: #a0aec0; font-weight: 600; background: #f7fafc; padding: 4px 8px; border-radius: 6px;">
                        Prazo: {{ $pdi->data_fim->format('d/m/Y') }}
                    </span>
                </div>
                
                <div style="padding-left: 25px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <span style="font-size: 0.8rem; font-weight: 600; color: #4a5568;">Progresso</span>
                        <span style="font-size: 0.8rem; font-weight: 700; color: {{ $pdi->progresso == 100 ? '#48bb78' : '#6a4c7c' }};">{{ $pdi->progresso }}%</span>
                    </div>
                    <div style="width: 100%; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                        <div style="width: {{ $pdi->progresso }}%; height: 100%; background: {{ $pdi->progresso == 100 ? '#48bb78' : 'linear-gradient(90deg, #6a4c7c, #9b59b6)' }}; border-radius: 4px; transition: width 0.5s ease;"></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
