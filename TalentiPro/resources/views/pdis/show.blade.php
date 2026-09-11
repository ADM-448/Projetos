@extends('layouts.app')

@section('title', 'Detalhes do PDI: ' . $pdi->titulo)
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title mobile-center-text">Plano de Desenvolvimento Individual</h1>
        <p class="mobile-center-text" style="color: #666; font-size: 0.95rem;">Visualize o progresso das metas e faça alinhamentos.</p>
    </div>
    <div>
        <a href="{{ route('pdis.index') }}" class="btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="form-row" style="gap: 30px; margin-bottom: 50px;">
    
    <!-- COLUNA PRINCIPAL: METAS E DETALHES -->
    <div style="flex: 2; display: flex; flex-direction: column; gap: 25px; min-width: 0;">
        
        <!-- CARD DE DETALHES -->
        <div class="card" style="position: relative;">
            <div class="pdi-header-mobile" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                <div class="pdi-header-text-mobile">
                    <span class="badge" style="background-color: #ebf8ff; color: #2b6cb0; padding: 4px 10px; font-size: 0.8rem; font-weight: bold; border-radius: 4px; text-transform: uppercase;">
                        {{ $pdi->categoria ?? 'Comportamental' }}
                    </span>
                    <h2 style="margin: 8px 0 5px 0; color: #2d3748; font-size: 1.5rem;">{{ $pdi->titulo }}</h2>
                    <p style="margin: 0; color: #718096; font-size: 0.9rem;">
                        Colaborador: <strong>{{ $pdi->colaborador->name }}</strong> | 
                        Responsável: <strong>{{ $pdi->gestor->name ?? 'RH' }}</strong>
                    </p>
                </div>
                <div id="pdi-status-badge">
                    @include('pdis.partials.badge-status', ['status' => $pdi->status->value])
                </div>
            </div>

            @if($pdi->descricao)
                <div class="mobile-center-text" style="background: #f7fafc; padding: 15px; border-radius: 8px; border: 1px solid #edf2f7; color: #4a5568; line-height: 1.6; margin-bottom: 20px;">
                    <strong>Objetivo do Plano:</strong>
                    <p style="margin: 5px 0 0 0;">{{ $pdi->descricao }}</p>
                </div>
            @endif

            <div class="form-row mobile-center-text" style="border-top: 1px solid #edf2f7; padding-top: 15px; font-size: 0.9rem; color: #718096; margin-bottom: 0;">
                <div style="flex: 1;">
                    <i class="fa-solid fa-calendar-check" style="color: #6a4c7c;"></i> Início: 
                    <strong>{{ $pdi->data_inicio ? $pdi->data_inicio->format('d/m/Y') : 'Não informada' }}</strong>
                </div>
                <div style="flex: 1;">
                    <i class="fa-solid fa-calendar-times" style="color: #e74c3c;"></i> Prazo Final: 
                    <strong>{{ $pdi->data_fim ? $pdi->data_fim->format('d/m/Y') : 'Sem prazo' }}</strong>
                </div>
                <div style="flex: 1;">
                    <i class="fa-solid fa-flag-checkered" style="color: #38a169;"></i> Conclusão: 
                    <strong id="pdi-conclusao-date">{{ $pdi->data_conclusao ? $pdi->data_conclusao->format('d/m/Y') : 'Pendente' }}</strong>
                </div>
            </div>
        </div>

        <!-- CARD DE PROGRESSO E AÇÕES -->
        <div class="card">
            <h3 class="mobile-center-flex" style="margin: 0 0 15px 0; color: #2d3748; display: flex; align-items: center; justify-content: space-between;">
                <span><i class="fa-solid fa-list-check" style="color: #6a4c7c;"></i> Metas e Etapas</span>
                <span id="progresso-percentual-kpi" style="font-size: 1.2rem; font-weight: bold; color: #6a4c7c;">{{ $pdi->progresso }}%</span>
            </h3>

            @php
                $pdiJaIniciou = !$pdi->data_inicio || $pdi->data_inicio->startOfDay()->lte(now()->startOfDay());
                $prazoEncerrado = $pdi->data_fim && $pdi->data_fim->endOfDay()->isPast();
            @endphp

            @if(!$pdiJaIniciou)
                <div style="background-color: #ebf4ff; color: #3182ce; padding: 12px 15px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 20px; border-left: 4px solid #3182ce;">
                    <i class="fa-solid fa-clock" style="margin-right: 5px;"></i> Este PDI está agendado para começar no dia <strong>{{ $pdi->data_inicio->format('d/m/Y') }}</strong>. As metas estarão disponíveis para preenchimento a partir desta data.
                </div>
            @elseif($prazoEncerrado)
                <div style="background-color: #fff5f5; color: #c53030; padding: 12px 15px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 20px; border-left: 4px solid #c53030;">
                    <i class="fa-solid fa-calendar-xmark" style="margin-right: 5px;"></i> O prazo deste PDI encerrou no dia <strong>{{ $pdi->data_fim->format('d/m/Y') }}</strong>. Não é mais possível alterar o status das metas ou enviar comprovantes.
                </div>
            @endif

            <!-- Barra de Progresso Grande -->
            <div style="background-color: #edf2f7; border-radius: 9999px; height: 16px; overflow: hidden; position: relative; margin-bottom: 25px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);">
                <div id="progresso-barra-kpi" style="background-color: {{ $prazoEncerrado && $pdi->progresso < 100 ? '#e53e3e' : '#6a4c7c' }}; height: 100%; border-radius: 9999px; width: {{ $pdi->progresso }}%; transition: width 0.4s ease-in-out;"></div>
            </div>

            <!-- Lista de Metas (Ações) -->
            <div style="display: flex; flex-direction: column; gap: 15px;">
                @foreach($pdi->acoes as $acao)
                    <div class="acao-card" style="display: flex; align-items: start; gap: 15px; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px; transition: all 0.2s; background: {{ $acao->status === 'concluido' ? '#f0fff4' : 'white' }}; border-color: {{ $acao->status === 'concluido' ? '#c6f6d5' : '#e2e8f0' }};">
                        
                        <div style="padding-top: 2px;">
                            @if(auth()->user()->can('toggleAcao', $pdi) && $pdiJaIniciou && !$prazoEncerrado)
                                <input type="checkbox" 
                                       class="acao-checkbox" 
                                       data-id="{{ $acao->id }}" 
                                       {{ $acao->status === 'concluido' ? 'checked' : '' }} 
                                       style="width: 20px; height: 20px; cursor: pointer; accent-color: #38a169;">
                            @else
                                <input type="checkbox" 
                                       disabled 
                                       {{ $acao->status === 'concluido' ? 'checked' : '' }} 
                                       style="width: 20px; height: 20px; accent-color: #38a169; cursor: not-allowed;">
                            @endif
                        </div>

                        <div style="flex-grow: 1;">
                            <h4 class="acao-titulo" style="margin: 0; font-size: 1.05rem; color: #2d3748; text-decoration: {{ $acao->status === 'concluido' ? 'line-through' : 'none' }}; opacity: {{ $acao->status === 'concluido' ? 0.7 : 1 }}; transition: all 0.2s;">
                                {{ $acao->titulo }}
                            </h4>
                            @if($acao->descricao)
                                <p class="acao-descricao" style="margin: 5px 0 0 0; font-size: 0.9rem; color: #718096; text-decoration: {{ $acao->status === 'concluido' ? 'line-through' : 'none' }}; opacity: {{ $acao->status === 'concluido' ? 0.6 : 1 }}; transition: all 0.2s;">
                                    {{ $acao->descricao }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- CARD DE COMPROVANTE -->
        <div class="card" id="comprovante-section">
            <h3 class="mobile-center-text" style="margin: 0 0 15px 0; color: #2d3748;">
                <i class="fa-solid fa-file-shield" style="color: #6a4c7c;"></i> Evidências e Comprovantes
            </h3>

            @if($pdi->comprovante)
                <div class="mobile-center-flex" style="display: flex; align-items: center; justify-content: space-between; background: #f7fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div class="mobile-center-flex" style="display: flex; align-items: center; gap: 12px;">
                        <i class="fa-solid fa-file-lines" style="font-size: 2rem; color: #6a4c7c;"></i>
                        <div class="mobile-center-text">
                            <span style="font-weight: bold; color: #2d3748; display: block; font-size: 0.95rem;">Comprovante Enviado</span>
                            <span style="font-size: 0.8rem; color: #718096;">O arquivo serve como atestado da conclusão do plano.</span>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('pdis.comprovante.download', $pdi->id) }}" class="btn-primary" style="padding: 8px 18px; font-size: 0.9rem;">
                            <i class="fa-solid fa-download"></i> Baixar Arquivo
                        </a>
                    </div>
                </div>
            @endif

            <!-- Formulário de Upload (Habilitado apenas para o dono do PDI) -->
            @if(auth()->user()->can('toggleAcao', $pdi) && !$prazoEncerrado)
                <div id="upload-container" style="display: {{ $pdi->progresso === 100 ? 'block' : 'none' }}; margin-top: {{ $pdi->comprovante ? '20px' : '0' }};">
                    <form action="{{ route('pdis.comprovante.upload', $pdi->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label class="form-label" style="font-weight: bold; margin-bottom: 8px; display: block;">
                            {{ $pdi->comprovante ? 'Substituir Comprovante' : 'Enviar Comprovante de Conclusão' }}
                        </label>
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <input type="file" name="comprovante" class="form-control" style="padding: 7px;" required>
                            <button type="submit" class="btn-secondary" style="white-space: nowrap; padding: 10px 20px;">
                                <i class="fa-solid fa-upload"></i> Enviar
                            </button>
                        </div>
                        <small style="color: #718096; display: block; margin-top: 5px;">Formatos permitidos: PDF, JPG, JPEG, PNG, ZIP. Máximo de 5MB.</small>
                    </form>
                </div>

                <div id="comprovante-pendente-msg" style="display: {{ $pdi->progresso === 100 ? 'none' : 'block' }}; color: #a0aec0; text-align: center; padding: 15px;">
                    <i class="fa-solid fa-triangle-exclamation" style="margin-right: 5px;"></i>
                    O envio de comprovante estará disponível assim que o progresso do PDI atingir 100%.
                </div>
            @endif
        </div>

    </div>

    <!-- COLUNA LATERAL: CHAT DE INTERAÇÃO -->
    <div class="chat-sidebar" style="flex: 1; position: sticky; top: 20px; align-self: start; min-width: 0;">
        <div class="form-card" style="padding: 20px; display: flex; flex-direction: column; height: calc(100vh - 120px); min-height: 400px; max-height: 700px; margin-bottom: 0;">
            <h3 class="mobile-center-text" style="margin: 0 0 15px 0; color: #2d3748; border-bottom: 1px solid #edf2f7; padding-bottom: 10px; flex-shrink: 0;">
                <i class="fa-solid fa-comments" style="color: #6a4c7c;"></i> Alinhamentos &amp; Notas
            </h3>

            <!-- Feed de Comentários (Scrollable) -->
            <div id="chat-messages" style="flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; padding-right: 4px; min-height: 0;">
                @forelse($pdi->comentarios as $coment)
                    @php
                        $isSelf = $coment->user_id === auth()->id();
                    @endphp
                    <div class="chat-message-wrapper" style="display: flex; flex-direction: column; align-items: {{ $isSelf ? 'flex-end' : 'flex-start' }};">
                        <div style="max-width: 85%; background: {{ $isSelf ? '#ebf4ff' : '#f7fafc' }}; border: 1px solid {{ $isSelf ? '#bee3f8' : '#e2e8f0' }}; padding: 10px 14px; border-radius: 12px; border-bottom-{{ $isSelf ? 'right' : 'left' }}-radius: 2px;">
                            <span style="font-size: 0.75rem; font-weight: bold; color: #4a5568; display: block; margin-bottom: 4px;">
                                {{ $coment->user->name }}
                            </span>
                            <p style="margin: 0; color: #2d3748; font-size: 0.92rem; word-break: break-word;">{{ $coment->comentario }}</p>
                        </div>
                        <span style="font-size: 0.7rem; color: #a0aec0; margin-top: 3px; padding: 0 5px;">
                            {{ $coment->created_at->format('d/m H:i') }}
                        </span>
                    </div>
                @empty
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; flex: 1; color: #a0aec0; text-align: center;">
                        <i class="fa-solid fa-message" style="font-size: 2rem; margin-bottom: 10px;"></i>
                        <p style="margin: 0; font-size: 0.85rem;">Sem comentários no histórico.<br>Inicie um alinhamento abaixo.</p>
                    </div>
                @endforelse
            </div>

            <!-- Formulário de Envio -->
            <div style="border-top: 1px solid #edf2f7; padding-top: 15px; margin-top: 15px; flex-shrink: 0;">
                <form action="{{ route('pdis.comentarios.store', $pdi->id) }}" method="POST">
                    @csrf
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="text" name="comentario" class="form-control" placeholder="Escreva uma mensagem..." required style="border-radius: 20px; padding: 10px 15px; flex: 1;">
                        <button type="submit" class="btn-primary" style="border-radius: 50%; width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; padding: 0; flex-shrink: 0; border: none; cursor: pointer;">
                            <i class="fa-solid fa-paper-plane" style="font-size: 1rem;"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Rolar o chat para o fim automaticamente
        const chat = document.getElementById('chat-messages');
        if (chat) {
            chat.scrollTop = chat.scrollHeight;
        }

        // Listener dos checkboxes de ação para envio AJAX
        document.querySelectorAll('.acao-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const acaoId = this.getAttribute('data-id');
                const isChecked = this.checked;
                const status = isChecked ? 'concluido' : 'pendente';
                const rowCard = this.closest('.acao-card');
                const tituloElement = rowCard.querySelector('.acao-titulo');
                const descElement = rowCard.querySelector('.acao-descricao');

                // Transição visual temporária imediata (efeito de resposta instantânea)
                if (isChecked) {
                    rowCard.style.backgroundColor = '#f0fff4';
                    rowCard.style.borderColor = '#c6f6d5';
                    if (tituloElement) tituloElement.style.textDecoration = 'line-through';
                    if (tituloElement) tituloElement.style.opacity = '0.7';
                    if (descElement) descElement.style.textDecoration = 'line-through';
                    if (descElement) descElement.style.opacity = '0.6';
                } else {
                    rowCard.style.backgroundColor = 'white';
                    rowCard.style.borderColor = '#e2e8f0';
                    if (tituloElement) tituloElement.style.textDecoration = 'none';
                    if (tituloElement) tituloElement.style.opacity = '1';
                    if (descElement) descElement.style.textDecoration = 'none';
                    if (descElement) descElement.style.opacity = '1';
                }

                // Chamada AJAX
                fetch(`/pdi-acoes/${acaoId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ status: status })
                })
                .then(async response => {
                    if (!response.ok) {
                        const err = await response.text();
                        throw new Error(`HTTP ${response.status}: ${err.substring(0, 100)}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Atualizar barra de progresso e texto
                        const percentual = data.progresso;
                        document.getElementById('progresso-percentual-kpi').textContent = percentual + '%';
                        document.getElementById('progresso-barra-kpi').style.width = percentual + '%';

                        // Atualizar data de conclusão
                        const conclusaoDateEl = document.getElementById('pdi-conclusao-date');
                        if (conclusaoDateEl) {
                            conclusaoDateEl.textContent = data.data_conclusao ? data.data_conclusao : 'Pendente';
                        }

                        // Atualizar badge do status
                        atualizarStatusBadge(data.status_pdi, data.status_pdi_label);

                        // Habilitar/desabilitar comprovantes
                        const uploadContainer = document.getElementById('upload-container');
                        const pendenteMsg = document.getElementById('comprovante-pendente-msg');
                        if (percentual === 100) {
                            if (uploadContainer) uploadContainer.style.display = 'block';
                            if (pendenteMsg) pendenteMsg.style.display = 'none';
                        } else {
                            if (uploadContainer && !'{{ $pdi->comprovante }}') uploadContainer.style.display = 'none';
                            if (pendenteMsg) pendenteMsg.style.display = 'block';
                        }
                    } else {
                        alert('A resposta do servidor indicou falha.');
                        window.location.reload();
                    }
                })
                .catch(error => {
                    alert('Erro na requisição: ' + error.message);
                    window.location.reload();
                });
            });
        });
    });

    /** Atualiza o HTML do badge de status dinamicamente */
    function atualizarStatusBadge(status, label) {
        const container = document.getElementById('pdi-status-badge');
        if (!container) return;

        let bg = '#edf2f7';
        let color = '#4a5568';

        switch(status) {
            case 'nao_iniciado':
                bg = '#edf2f7';
                color = '#4a5568';
                break;
            case 'em_andamento':
                bg = '#fefcbf';
                color = '#b7791f';
                break;
            case 'concluido':
                bg = '#c6f6d5';
                color = '#22543d';
                break;
            case 'atrasado':
                bg = '#fed7d7';
                color = '#9b2c2c';
                break;
            case 'cancelado':
                bg = '#e2e8f0';
                color = '#4a5568';
                break;
        }

        container.innerHTML = `
            <span class="badge" style="background-color: ${bg}; color: ${color}; padding: 6px 12px; font-size: 0.85rem; font-weight: bold; border-radius: 6px; display: inline-block;">
                ${label}
            </span>
        `;
    }
</script>
@endpush
