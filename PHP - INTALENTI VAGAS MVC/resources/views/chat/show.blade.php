@extends('layouts.app')

@section('title', 'Mensagens - ' . $chat->vaga->titulo)

@push('styles')
<style>
    .chat-box-card {
        background: #ffffff;
        border: 1px solid var(--pl-border);
        border-radius: var(--pl-radius-lg);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 78vh;
        box-shadow: var(--pl-shadow-md);
    }

    .chat-messages-area {
        background-color: #f9fafb;
        flex-grow: 1;
        overflow-y: auto;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    /* Balões de Mensagem */
    .chat-bubble-me {
        background-color: var(--pl-green);
        color: #ffffff;
        border-radius: 18px 18px 4px 18px;
        padding: 12px 16px;
        max-width: 72%;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
    }

    .chat-bubble-other {
        background-color: #ffffff;
        color: var(--pl-text-main);
        border: 1px solid var(--pl-border);
        border-radius: 18px 18px 18px 4px;
        padding: 12px 16px;
        max-width: 72%;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-lg-4 py-4" style="max-width: 1100px;">
    
    <div class="chat-box-card">
        
        <!-- Cabeçalho do Chat -->
        <div class="p-3 bg-white border-bottom d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 44px; height: 44px; flex-shrink: 0;">
                    {{ strtoupper(substr($nomeInterlocutor, 0, 1)) }}
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">{{ $nomeInterlocutor }}</h6>
                    <small class="text-muted d-flex align-items-center gap-1">
                        <span>Referente à vaga:</span>
                        <strong class="text-dark">{{ $chat->vaga->titulo }}</strong>
                    </small>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('vagas.show', $chat->vaga_id) }}" class="btn btn-pl-secondary btn-sm" target="_blank" title="Ver detalhes da vaga">
                    <i class="fa-solid fa-briefcase me-1"></i> Ver Vaga
                </a>
                <a href="{{ url()->previous() }}" class="btn btn-pl-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Voltar
                </a>
            </div>
        </div>

        <!-- Área de Rolagem das Mensagens -->
        <div class="chat-messages-area" id="mensagensContainer">
            @forelse($chat->mensagens as $msg)
                @php $isMe = ($msg->remetente_id === Auth::id()); @endphp
                <div class="d-flex flex-column {{ $isMe ? 'align-items-end' : 'align-items-start' }} msg-item" data-id="{{ $msg->id }}">
                    <div class="{{ $isMe ? 'chat-bubble-me' : 'chat-bubble-other' }}">
                        <div class="small fw-semibold mb-1 opacity-75" style="font-size: 0.75rem;">
                            {{ $isMe ? 'Você' : $msg->remetente->name }}
                        </div>
                        <div style="white-space: pre-wrap; word-break: break-word; font-size: 0.92rem; line-height: 1.5;">{{ $msg->mensagem }}</div>
                        <div class="text-end small mt-1 opacity-75" style="font-size: 0.68rem;">{{ $msg->created_at->format('H:i') }}</div>
                    </div>
                </div>
            @empty
                @if(!$chatLiberado)
                    <div class="text-center my-auto p-4" id="waitingCompany">
                        <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 68px; height: 68px; background: #fef3c7; color: #d97706;">
                            <i class="fa-regular fa-clock fa-2x"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Aguardando contato da empresa</h5>
                        <p class="text-muted small mx-auto mb-0" style="max-width: 440px;">
                            O chat será liberado para você enviar mensagens assim que o recrutador ou empresa fizer o primeiro contato.
                        </p>
                    </div>
                @else
                    <div class="text-center my-auto text-muted small" id="noMessages">
                        <div class="mb-3">
                            <i class="fa-regular fa-comments fa-3x text-muted opacity-50"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Inicie a conversa!</h5>
                        <p class="text-muted small">Tire dúvidas, alinhe expectativas e agende entrevistas diretamente por aqui.</p>
                    </div>
                @endif
            @endforelse
        </div>

        <!-- Barra Inferior de Envio -->
        <div class="p-3 bg-white border-top" id="chatInputArea" style="{{ !$chatLiberado ? 'display: none !important;' : '' }}">
            <form id="chatForm" class="d-flex align-items-center gap-2">
                @csrf
                <input type="text" id="inputMensagem" class="form-control rounded-pill px-4 py-2 border" placeholder="Digite sua mensagem aqui..." required autocomplete="off" style="font-size: 0.9rem;">
                <button type="submit" class="btn btn-pl-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; flex-shrink: 0;" title="Enviar Mensagem">
                    <i class="fa-solid fa-paper-plane" style="font-size: 0.95rem;"></i>
                </button>
            </form>
        </div>

        <!-- Aviso para Candidato quando o chat está aguardando empresa -->
        <div class="p-3 bg-light border-top text-center text-muted small" id="chatBlockedNotice" style="{{ $chatLiberado ? 'display: none !important;' : '' }}">
            <i class="fa-solid fa-lock me-1 text-warning"></i> O envio de mensagens será liberado após o primeiro contato da empresa.
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('mensagensContainer');
        const form = document.getElementById('chatForm');
        const input = document.getElementById('inputMensagem');
        const inputArea = document.getElementById('chatInputArea');
        const blockedNotice = document.getElementById('chatBlockedNotice');
        const chatId = {{ $chat->id }};

        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        // Scroll para o fim automático
        function scrollToBottom() {
            container.scrollTop = container.scrollHeight;
        }
        scrollToBottom();

        // Envio via AJAX
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const texto = input.value.trim();
                if (!texto) return;

                input.value = '';

                fetch(`/chat/${chatId}/enviar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ mensagem: texto })
                })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) {
                        throw new Error(data.message || 'Erro ao enviar mensagem');
                    }
                    return data;
                })
                .then(data => {
                    if (data.success && data.mensagem) {
                        const noMsgs = document.getElementById('noMessages');
                        if (noMsgs) noMsgs.remove();
                        const waiting = document.getElementById('waitingCompany');
                        if (waiting) waiting.remove();
                        adicionarMensagem(data.mensagem);
                    }
                })
                .catch(err => {
                    alert(err.message || 'Não foi possível enviar a mensagem.');
                });
            });
        }

        // Adiciona mensagem ao DOM
        function adicionarMensagem(msg) {
            const isMe = Boolean(msg.e_meu);
            const div = document.createElement('div');
            div.className = `d-flex flex-column ${isMe ? 'align-items-end' : 'align-items-start'} msg-item`;
            div.dataset.id = msg.id;

            const remetenteNome = isMe ? 'Você' : (msg.remetente || 'Usuário');
            const textoCorpo = msg.texto || msg.mensagem || '';
            const hora = msg.hora || 'Agora';

            div.innerHTML = `
                <div class="${isMe ? 'chat-bubble-me' : 'chat-bubble-other'}">
                    <div class="small fw-semibold mb-1 opacity-75" style="font-size: 0.75rem;">
                        ${escapeHtml(remetenteNome)}
                    </div>
                    <div style="white-space: pre-wrap; word-break: break-word; font-size: 0.92rem; line-height: 1.5;">${escapeHtml(textoCorpo)}</div>
                    <div class="text-end small mt-1 opacity-75" style="font-size: 0.68rem;">${escapeHtml(hora)}</div>
                </div>
            `;
            container.appendChild(div);
            scrollToBottom();
        }

        // Polling para checar novas mensagens a cada 3 segundos
        setInterval(function () {
            const ultimos = document.querySelectorAll('.msg-item');
            let lastId = 0;
            if (ultimos.length > 0) {
                lastId = ultimos[ultimos.length - 1].dataset.id || 0;
            }

            fetch(`/chat/${chatId}/mensagens?last_id=${lastId}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.chat_liberado) {
                    if (inputArea) inputArea.style.setProperty('display', 'block', 'important');
                    if (blockedNotice) blockedNotice.style.setProperty('display', 'none', 'important');
                    const waiting = document.getElementById('waitingCompany');
                    if (waiting) waiting.remove();
                }

                const lista = data.mensagens || data;
                if (Array.isArray(lista) && lista.length > 0) {
                    const noMsgs = document.getElementById('noMessages');
                    if (noMsgs) noMsgs.remove();
                    const waiting = document.getElementById('waitingCompany');
                    if (waiting) waiting.remove();

                    lista.forEach(m => adicionarMensagem(m));
                }
            })
            .catch(err => console.error('Erro ao buscar mensagens:', err));
        }, 3000);
    });
</script>
@endpush
