@extends('layouts.app')

@section('title', 'Detalhes do PDI: ' . $pdi->titulo)
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<style>
    :root {
        --color-primary: #4f46e5;
        --color-primary-hover: #3730a3;
        --color-accent: #6d28d9;
        --color-slate-50: #f8fafc;
        --color-slate-100: #f1f5f9;
        --color-slate-500: #64748b;
        --color-slate-900: #0f172a;
        --radius-card: 16px;
        --radius-element: 10px;
        --radius-pill: 99px;
        --shadow-card: 0 4px 16px rgba(0,0,0,.08);
        --shadow-hover: 0 10px 40px rgba(0,0,0,.14);
        --border-soft: 1px solid #e9eaf0;
        --font-main: 'Inter', system-ui, sans-serif;
    }

    .pdi-page {
        font-family: var(--font-main);
        background: var(--color-slate-50);
        min-height: 100vh;
        padding: 24px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 32px;
    }

    .card-panel {
        background: #fff;
        border-radius: var(--radius-card);
        padding: 28px 32px;
        box-shadow: var(--shadow-card);
        border: var(--border-soft);
    }

    .panel-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--color-slate-900);
        margin: 0 0 20px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .info-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: var(--radius-pill);
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .acao-card {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        padding: 18px;
        border: var(--border-soft);
        border-radius: var(--radius-element);
        transition: all 0.3s ease;
    }

    .acao-card:hover {
        box-shadow: var(--shadow-card);
    }

    .acao-card.concluido {
        background: rgba(16, 185, 129, 0.04);
        border-color: rgba(16, 185, 129, 0.3);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: var(--radius-pill);
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        border: none;
        font-family: var(--font-main);
    }

    .btn-primary {
        background: var(--color-primary);
        color: #fff;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
    }

    .btn-primary:hover {
        background: var(--color-primary-hover);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
    }

    .btn-secondary {
        background: var(--color-slate-50);
        color: var(--color-slate-900);
        border: var(--border-soft);
    }

    .btn-secondary:hover {
        background: var(--color-slate-100);
        transform: translateY(-1px);
    }

    /* Chat */
    .chat-bubble-self {
        background: rgba(79, 70, 229, 0.08);
        border: 1px solid rgba(79, 70, 229, 0.15);
    }

    .chat-bubble-other {
        background: var(--color-slate-50);
        border: var(--border-soft);
    }

    /* ── Responsividade Mobile ── */
    @media (max-width: 768px) {
        .pdi-page {
            padding: 12px 16px;
        }

        .page-header {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
            margin-bottom: 20px;
        }

        .page-header h1 {
            font-size: 1.35rem !important;
        }

        /* Form-row principal: coluna única */
        .form-row {
            flex-direction: column !important;
            gap: 16px !important;
        }

        /* Coluna principal ocupa 100% */
        .form-row > div[style*="flex: 2"],
        .form-row > div[style*="flex: 1"] {
            flex: 1 1 100% !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        /* Cards com padding compacto */
        .card-panel {
            padding: 16px !important;
            box-sizing: border-box;
            width: 100%;
            max-width: 100%;
        }

        /* Acao card em coluna no mobile */
        .acao-card {
            flex-direction: column;
            gap: 10px;
        }

        /* Linha de datas em coluna */
        .mobile-center-text {
            flex-direction: column !important;
            gap: 10px !important;
        }

        /* Header do PDI em coluna */
        .pdi-header-mobile {
            flex-direction: column !important;
        }

        /* Garantir box-sizing em todos containers */
        .pdi-page *,
        .pdi-page *::before,
        .pdi-page *::after {
            box-sizing: border-box;
        }
    }
</style>

<div class="pdi-page">
    <div class="page-header">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--color-slate-900); margin: 0 0 4px 0; letter-spacing: -0.02em; font-family: var(--font-main);">
                Plano de Desenvolvimento Individual
            </h1>
            <p style="color: var(--color-slate-500); font-size: 0.95rem; margin: 0; font-family: var(--font-main);">
                Visualize o progresso das metas e faça alinhamentos.
            </p>
        </div>
        <a href="{{ route('pdis.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Voltar para PDIs
        </a>
    </div>

    <div class="form-row" style="gap: 28px; margin-bottom: 50px; font-family: var(--font-main);">

        <!-- COLUNA PRINCIPAL -->
        <div style="flex: 2; display: flex; flex-direction: column; gap: 24px; min-width: 0;">

            <!-- CARD DE DETALHES -->
            <div class="card-panel">
                <div class="pdi-header-mobile" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; gap: 16px; flex-wrap: wrap;">
                    <div style="flex: 1;">
                        <span class="info-badge" style="background: rgba(79, 70, 229, 0.1); color: var(--color-primary); margin-bottom: 10px;">
                            {{ $pdi->categoria ?? 'Comportamental' }}
                        </span>
                        <h2 style="margin: 10px 0 6px 0; color: var(--color-slate-900); font-size: 1.5rem; font-weight: 800;">{{ $pdi->titulo }}</h2>
                        <p style="margin: 0; color: var(--color-slate-500); font-size: 0.9rem;">
                            Colaborador: <strong style="color: var(--color-slate-900);">{{ $pdi->colaborador->name }}</strong> &middot;
                            Responsável: <strong style="color: var(--color-slate-900);">{{ $pdi->gestor->name ?? 'RH' }}</strong>
                        </p>
                    </div>
                    <div id="pdi-status-badge">
                        @include('pdis.partials.badge-status', ['status' => $pdi->status->value])
                    </div>
                </div>

                @if($pdi->descricao)
                    <div style="background: var(--color-slate-50); padding: 16px 20px; border-radius: var(--radius-element); border: var(--border-soft); color: var(--color-slate-900); line-height: 1.7; margin-bottom: 20px;">
                        <strong style="color: var(--color-slate-500); font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.05em;">Objetivo do Plano</strong>
                        <p style="margin: 6px 0 0 0;">{{ $pdi->descricao }}</p>
                    </div>
                @endif

                <div class="form-row mobile-center-text" style="border-top: var(--border-soft); padding-top: 16px; font-size: 0.9rem; color: var(--color-slate-500); margin-bottom: 0; gap: 20px; flex-wrap: wrap;">
                    <div style="flex: 1; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-calendar-check" style="color: var(--color-primary);"></i>
                        Início: <strong style="color: var(--color-slate-900);">{{ $pdi->data_inicio ? $pdi->data_inicio->format('d/m/Y') : 'Não informada' }}</strong>
                    </div>
                    <div style="flex: 1; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-calendar-xmark" style="color: #ef4444;"></i>
                        Prazo: <strong style="color: var(--color-slate-900);">{{ $pdi->data_fim ? $pdi->data_fim->format('d/m/Y') : 'Sem prazo' }}</strong>
                    </div>
                    <div style="flex: 1; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-flag-checkered" style="color: #059669;"></i>
                        Conclusão: <strong id="pdi-conclusao-date" style="color: var(--color-slate-900);">{{ $pdi->data_conclusao ? $pdi->data_conclusao->format('d/m/Y') : 'Pendente' }}</strong>
                    </div>
                </div>
            </div>

            <!-- CARD DE METAS -->
            <div class="card-panel">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;" class="mobile-center-flex">
                    <h3 class="panel-title" style="margin: 0;">
                        <i class="fa-solid fa-list-check" style="color: var(--color-primary);"></i> Metas e Etapas
                    </h3>
                    <span id="progresso-percentual-kpi" style="font-size: 1.3rem; font-weight: 800; color: var(--color-primary);">{{ $pdi->progresso }}%</span>
                </div>

                @php
                    $pdiJaIniciou = !$pdi->data_inicio || $pdi->data_inicio->startOfDay()->lte(now()->startOfDay());
                    $prazoEncerrado = $pdi->data_fim && $pdi->data_fim->endOfDay()->isPast();
                @endphp

                @if(!$pdiJaIniciou)
                    <div style="background: rgba(79, 70, 229, 0.06); color: var(--color-primary); padding: 14px 18px; border-radius: var(--radius-element); font-size: 0.9rem; margin-bottom: 20px; border-left: 4px solid var(--color-primary);">
                        <i class="fa-solid fa-clock" style="margin-right: 6px;"></i> Este PDI está agendado para começar em <strong>{{ $pdi->data_inicio->format('d/m/Y') }}</strong>. As metas estarão disponíveis a partir desta data.
                    </div>
                @elseif($prazoEncerrado)
                    <div style="background: rgba(239, 68, 68, 0.06); color: #dc2626; padding: 14px 18px; border-radius: var(--radius-element); font-size: 0.9rem; margin-bottom: 20px; border-left: 4px solid #dc2626;">
                        <i class="fa-solid fa-calendar-xmark" style="margin-right: 6px;"></i> O prazo deste PDI encerrou em <strong>{{ $pdi->data_fim->format('d/m/Y') }}</strong>. Não é mais possível alterar o status das metas.
                    </div>
                @endif

                <!-- Barra de Progresso -->
                <div style="background: #e9eaf0; border-radius: var(--radius-pill); height: 14px; overflow: hidden; position: relative; margin-bottom: 28px;">
                    <div id="progresso-barra-kpi" style="background: {{ $prazoEncerrado && $pdi->progresso < 100 ? '#ef4444' : 'var(--color-primary)' }}; height: 100%; border-radius: var(--radius-pill); width: {{ $pdi->progresso }}%; transition: width 0.5s ease;"></div>
                </div>

                <!-- Lista de Metas -->
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach($pdi->acoes as $acao)
                        <div class="acao-card {{ $acao->status === 'concluido' ? 'concluido' : '' }}">
                            <div style="padding-top: 2px;">
                                @if(auth()->user()->can('toggleAcao', $pdi) && $pdiJaIniciou && !$prazoEncerrado)
                                    <input type="checkbox"
                                           class="acao-checkbox"
                                           data-id="{{ $acao->id }}"
                                           {{ $acao->status === 'concluido' ? 'checked' : '' }}
                                           style="width: 20px; height: 20px; cursor: pointer; accent-color: var(--color-primary);">
                                @else
                                    <input type="checkbox"
                                           disabled
                                           {{ $acao->status === 'concluido' ? 'checked' : '' }}
                                           style="width: 20px; height: 20px; accent-color: var(--color-primary); cursor: not-allowed; opacity: 0.7;">
                                @endif
                            </div>
                            <div style="flex-grow: 1;">
                                <h4 class="acao-titulo" style="margin: 0; font-size: 1rem; color: var(--color-slate-900); font-weight: 700; text-decoration: {{ $acao->status === 'concluido' ? 'line-through' : 'none' }}; opacity: {{ $acao->status === 'concluido' ? 0.6 : 1 }}; transition: all 0.2s;">
                                    {{ $acao->titulo }}
                                </h4>
                                @if($acao->descricao)
                                    <p class="acao-descricao" style="margin: 5px 0 0 0; font-size: 0.9rem; color: var(--color-slate-500); text-decoration: {{ $acao->status === 'concluido' ? 'line-through' : 'none' }}; opacity: {{ $acao->status === 'concluido' ? 0.5 : 1 }}; transition: all 0.2s;">
                                        {{ $acao->descricao }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- CARD DE COMPROVANTE -->
            <div class="card-panel" id="comprovante-section">
                <h3 class="panel-title mobile-center-text">
                    <i class="fa-solid fa-file-shield" style="color: var(--color-primary);"></i> Evidências e Comprovantes
                </h3>

                @if($pdi->comprovante)
                    <div class="mobile-center-flex" style="display: flex; align-items: center; justify-content: space-between; background: var(--color-slate-50); padding: 18px 20px; border-radius: var(--radius-element); border: var(--border-soft); margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <i class="fa-solid fa-file-lines" style="font-size: 2rem; color: var(--color-primary);"></i>
                            <div>
                                <span style="font-weight: 700; color: var(--color-slate-900); display: block; font-size: 0.95rem;">Comprovante Enviado</span>
                                <span style="font-size: 0.8rem; color: var(--color-slate-500);">O arquivo serve como atestado da conclusão do plano.</span>
                            </div>
                        </div>
                        <a href="{{ route('pdis.comprovante.download', $pdi->id) }}" class="btn btn-primary" style="padding: 8px 18px; font-size: 0.9rem;">
                            <i class="fa-solid fa-download"></i> Baixar Arquivo
                        </a>
                    </div>
                @endif

                @if(auth()->user()->can('toggleAcao', $pdi) && !$prazoEncerrado)
                    <div id="upload-container" style="display: {{ $pdi->progresso === 100 ? 'block' : 'none' }};">
                        <form action="{{ route('pdis.comprovante.upload', $pdi->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <label style="font-weight: 700; margin-bottom: 10px; display: block; color: var(--color-slate-900);">
                                {{ $pdi->comprovante ? 'Substituir Comprovante' : 'Enviar Comprovante de Conclusão' }}
                            </label>
                            <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                                <input type="file" name="comprovante" class="form-control" style="padding: 10px; border-radius: var(--radius-element); border: var(--border-soft); flex: 1;" required>
                                <button type="submit" class="btn btn-secondary" style="white-space: nowrap;">
                                    <i class="fa-solid fa-upload"></i> Enviar Comprovante
                                </button>
                            </div>
                            <small style="color: var(--color-slate-500); display: block; margin-top: 8px; font-size: 0.8rem;">Formatos: PDF, JPG, PNG, ZIP. Máximo de 5MB.</small>
                        </form>
                    </div>

                    <div id="comprovante-pendente-msg" style="display: {{ $pdi->progresso === 100 ? 'none' : 'flex' }}; color: var(--color-slate-500); align-items: center; gap: 10px; padding: 16px; background: var(--color-slate-50); border-radius: var(--radius-element); border: 1px dashed #cbd5e1;">
                        <i class="fa-solid fa-triangle-exclamation" style="color: #d97706;"></i>
                        O envio de comprovante estará disponível assim que o progresso atingir 100%.
                    </div>
                @endif
            </div>
        </div>

        <!-- COLUNA LATERAL: CHAT -->
        <div class="chat-sidebar" style="flex: 1; position: sticky; top: 20px; align-self: start; min-width: 0; font-family: var(--font-main);">
            <div class="card-panel" style="padding: 20px; display: flex; flex-direction: column; height: calc(100vh - 120px); min-height: 400px; max-height: 700px; margin-bottom: 0;">
                <h3 class="panel-title mobile-center-text" style="border-bottom: var(--border-soft); padding-bottom: 14px; flex-shrink: 0;">
                    <i class="fa-solid fa-comments" style="color: var(--color-primary);"></i> Alinhamentos & Notas
                </h3>

                <!-- Feed de Comentários -->
                <div id="chat-messages" style="flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; padding-right: 4px; min-height: 0;">
                    @forelse($pdi->comentarios as $coment)
                        @php
                            $isSelf = $coment->user_id === auth()->id();
                        @endphp
                        <div class="chat-message-wrapper" style="display: flex; flex-direction: column; align-items: {{ $isSelf ? 'flex-end' : 'flex-start' }};">
                            <div style="max-width: 85%; padding: 10px 14px; border-radius: 14px; border-bottom-{{ $isSelf ? 'right' : 'left' }}-radius: 4px;" class="{{ $isSelf ? 'chat-bubble-self' : 'chat-bubble-other' }}">
                                <span style="font-size: 0.75rem; font-weight: 700; color: {{ $isSelf ? 'var(--color-primary)' : 'var(--color-slate-500)' }}; display: block; margin-bottom: 4px;">
                                    {{ $coment->user->name }}
                                </span>
                                <p style="margin: 0; color: var(--color-slate-900); font-size: 0.92rem; word-break: break-word;">{{ $coment->comentario }}</p>
                            </div>
                            <span style="font-size: 0.7rem; color: #a0aec0; margin-top: 4px; padding: 0 5px;">
                                {{ $coment->created_at->format('d/m H:i') }}
                            </span>
                        </div>
                    @empty
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; flex: 1; color: var(--color-slate-500); text-align: center; padding: 30px 0;">
                            <i class="fa-solid fa-message" style="font-size: 2rem; margin-bottom: 12px; color: #cbd5e1;"></i>
                            <p style="margin: 0; font-size: 0.85rem;">Sem comentários no histórico.<br>Inicie um alinhamento abaixo.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Formulário de Envio -->
                <div style="border-top: var(--border-soft); padding-top: 14px; margin-top: 14px; flex-shrink: 0;">
                    <form action="{{ route('pdis.comentarios.store', $pdi->id) }}" method="POST">
                        @csrf
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="text" name="comentario" class="form-control" placeholder="Escreva uma mensagem..." required style="border-radius: var(--radius-pill); padding: 10px 16px; flex: 1; border: var(--border-soft); font-family: var(--font-main);">
                            <button type="submit" class="btn btn-primary" style="border-radius: 50%; width: 42px; height: 42px; padding: 0; flex-shrink: 0;">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chat = document.getElementById('chat-messages');
        if (chat) {
            chat.scrollTop = chat.scrollHeight;
        }

        document.querySelectorAll('.acao-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const acaoId = this.getAttribute('data-id');
                const isChecked = this.checked;
                const status = isChecked ? 'concluido' : 'pendente';
                const rowCard = this.closest('.acao-card');
                const tituloElement = rowCard.querySelector('.acao-titulo');
                const descElement = rowCard.querySelector('.acao-descricao');

                if (isChecked) {
                    rowCard.classList.add('concluido');
                    if (tituloElement) { tituloElement.style.textDecoration = 'line-through'; tituloElement.style.opacity = '0.6'; }
                    if (descElement) { descElement.style.textDecoration = 'line-through'; descElement.style.opacity = '0.5'; }
                } else {
                    rowCard.classList.remove('concluido');
                    if (tituloElement) { tituloElement.style.textDecoration = 'none'; tituloElement.style.opacity = '1'; }
                    if (descElement) { descElement.style.textDecoration = 'none'; descElement.style.opacity = '1'; }
                }

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
                        const percentual = data.progresso;
                        document.getElementById('progresso-percentual-kpi').textContent = percentual + '%';
                        document.getElementById('progresso-barra-kpi').style.width = percentual + '%';

                        const conclusaoDateEl = document.getElementById('pdi-conclusao-date');
                        if (conclusaoDateEl) {
                            conclusaoDateEl.textContent = data.data_conclusao ? data.data_conclusao : 'Pendente';
                        }

                        atualizarStatusBadge(data.status_pdi, data.status_pdi_label);

                        const uploadContainer = document.getElementById('upload-container');
                        const pendenteMsg = document.getElementById('comprovante-pendente-msg');
                        if (percentual === 100) {
                            if (uploadContainer) uploadContainer.style.display = 'block';
                            if (pendenteMsg) pendenteMsg.style.display = 'none';
                        } else {
                            if (uploadContainer && !'{{ $pdi->comprovante }}') uploadContainer.style.display = 'none';
                            if (pendenteMsg) pendenteMsg.style.display = 'flex';
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

    function atualizarStatusBadge(status, label) {
        const container = document.getElementById('pdi-status-badge');
        if (!container) return;

        let bg = '#edf2f7'; let color = '#4a5568';
        switch(status) {
            case 'nao_iniciado': bg = '#edf2f7'; color = '#4a5568'; break;
            case 'em_andamento': bg = 'rgba(245, 158, 11, 0.15)'; color = '#b45309'; break;
            case 'concluido': bg = 'rgba(16, 185, 129, 0.15)'; color = '#065f46'; break;
            case 'atrasado': bg = 'rgba(239, 68, 68, 0.15)'; color = '#991b1b'; break;
            case 'cancelado': bg = '#e2e8f0'; color = '#4a5568'; break;
        }

        container.innerHTML = `
            <span style="background: ${bg}; color: ${color}; padding: 6px 14px; font-size: 0.85rem; font-weight: 700; border-radius: 99px; display: inline-block;">
                ${label}
            </span>
        `;
    }
</script>
@endpush
