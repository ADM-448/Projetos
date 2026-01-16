<div class="chat-wrapper-container">
    <div class="chat-wrapper">
        <div class="chat-header">
            <div class="chat-header-info">
                <h3><?= htmlspecialchars($nome_outro) ?></h3>
                <span><?= htmlspecialchars($titulo_topo) ?></span>
            </div>
            <a href="<?= $back_url ?>" class="btn btn-secondary btn-sm">Voltar</a>
        </div>

        <div class="messages-area" id="messagesArea">
            <div style="text-align:center; color:#888; padding:20px;" id="loadingMsg">
                <i class='bx bx-loader-alt bx-spin' style="font-size:2rem;"></i><br>
                Conectando...
            </div>
        </div>

        <div class="chat-input-area">
            <textarea id="msgInput" placeholder="Escreva sua mensagem..." onkeypress="handleEnter(event)"></textarea>
            <button class="btn-send" onclick="enviarMensagem()">
                <i class='bx bxs-send'></i>
            </button>
        </div>
    </div>
</div>

<style>
    /* Estilos copiados e adaptados para garantir funcionamento dentro do Layout Master */
    .chat-wrapper-container {
        height: calc(100vh - 100px);
        /* Ajuste para caber com header do site */
        display: flex;
        flex-direction: column;
        background-color: #dadde1;
    }

    .chat-wrapper {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        width: 95%;
        max-width: 1000px;
        margin: 20px auto;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        border: 1px solid #ccc;
        overflow: hidden;
        position: relative;
    }

    .chat-header {
        padding: 15px 20px;
        background: #f0f2f5;
        border-bottom: 1px solid #ddd;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
        z-index: 10;
    }

    .chat-header-info h3 {
        margin: 0;
        font-size: 1.1rem;
        color: #333;
    }

    .chat-header-info span {
        font-size: 0.85rem;
        color: #666;
    }

    .messages-area {
        flex-grow: 1;
        padding: 20px;
        overflow-y: auto;
        background-color: #e5ddd5;
        background-image: radial-gradient(#cfd5db 1px, transparent 1px);
        /* background-size: 20px 20px; */
        /* Comentado pois as vezes buga path relativo */
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .message {
        max-width: 80%;
        display: flex;
        align-items: center;
        gap: 8px;
        background: transparent;
        padding: 0;
        box-shadow: none;
    }

    .msg-content-wrapper {
        padding: 10px 14px;
        border-radius: 8px;
        position: relative;
        font-size: 0.95rem;
        line-height: 1.4;
        word-wrap: break-word;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
    }

    .message.sent {
        align-self: flex-end;
        flex-direction: row-reverse;
    }

    .message.sent .msg-content-wrapper {
        background-color: #581c21;
        /* Cor da marca */
        color: #fff;
        border-top-right-radius: 0;
    }

    .message.received {
        align-self: flex-start;
        flex-direction: row;
    }

    .message.received .msg-content-wrapper {
        background-color: #fff;
        color: #333;
        border-top-left-radius: 0;
    }

    .msg-time {
        font-size: 0.7rem;
        display: block;
        text-align: right;
        margin-top: 4px;
        opacity: 0.7;
    }

    .btn-delete {
        background: none;
        border: none;
        color: #999;
        cursor: pointer;
        font-size: 1.2rem;
        padding: 5px;
        display: flex;
        align-items: center;
        transition: color 0.2s, transform 0.2s;
        opacity: 1;
    }

    .btn-delete:hover {
        color: #c0392b;
        transform: scale(1.1);
    }

    .chat-input-area {
        padding: 15px;
        background: #f0f2f5;
        border-top: 1px solid #ddd;
        display: flex;
        gap: 10px;
        align-items: center;
        flex-shrink: 0;
    }

    .chat-input-area textarea {
        flex-grow: 1;
        border: 1px solid #fff;
        padding: 12px 15px;
        border-radius: 20px;
        resize: none;
        height: 50px;
        outline: none;
        font-family: inherit;
        background: #fff;
    }

    .btn-send {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: none;
        background: #581c21;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.2s;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .btn-send:active {
        transform: scale(0.95);
    }

    .btn-send i {
        font-size: 1.4rem;
        margin-left: 3px;
    }

    @media (max-width: 768px) {
        .chat-wrapper {
            width: 100%;
            max-width: 100%;
            margin: 0;
            border-radius: 0;
            border: none;
            box-shadow: none;
            position: fixed;
            top: 60px;
            left: 0;
            right: 0;
            bottom: 0;
            height: auto;
            z-index: 50;
        }
    }
</style>

<!-- Modal Confirmar Exclusão -->
<div id="delete-msg-modal" class="modal-overlay" style="z-index: 9999;">
    <div class="modal-content" style="max-width: 400px; text-align: center;">
        <button class="modal-close-btn" onclick="closeDeleteModal()">&times;</button>
        <div style="margin-bottom: 20px;">
            <i class='bx bx-error-circle' style='font-size: 4rem; color: #dc3545;'></i>
        </div>
        <h3 style="color: #333;">Apagar Mensagem?</h3>
        <p class="subtitle" style="margin-bottom: 20px;">Tem certeza que deseja apagar esta mensagem para todos?</p>

        <div style="display: flex; gap: 10px; justify-content: center;">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Não, voltar</button>
            <button type="button" class="btn btn-danger" id="confirm-delete-btn">Sim, apagar</button>
        </div>
    </div>
</div>

<script>
    const chatId = <?= $chat_id ?>;
    const userId = <?= $user_id ?>;
    const candidatoUserId = <?= $candidato_user_id ?>;

    const messagesArea = document.getElementById('messagesArea');
    const msgInput = document.getElementById('msgInput');
    const loadingMsg = document.getElementById('loadingMsg');

    let lastMsgId = 0;
    let isFirstLoad = true;
    let msgToDeleteId = null; // Store ID for deletion

    // URLs adaptadas para MVC
    const URL_GET_MESSAGES = '<?= BASE_URL ?>chat/getMessages';
    const URL_SEND_MESSAGE = '<?= BASE_URL ?>chat/send';
    const URL_DELETE_MESSAGE = '<?= BASE_URL ?>chat/delete';

    // --- Modal Functions ---
    function apagarMensagem(id) {
        msgToDeleteId = id;
        const modal = document.getElementById('delete-msg-modal');
        modal.style.display = 'flex';
        // Pequeno delay para animação CSS funcionar (se tiver transition)
        setTimeout(() => modal.classList.add('active'), 10);
    }

    function closeDeleteModal() {
        const modal = document.getElementById('delete-msg-modal');
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
        msgToDeleteId = null;
    }

    // Bind confirmation click
    document.getElementById('confirm-delete-btn').addEventListener('click', function() {
        if (msgToDeleteId) {
            confirmarExclusao(msgToDeleteId);
        }
    });

    function confirmarExclusao(id) {
        const formData = new FormData();
        formData.append('mensagem_id', id);

        // Desativa botão para evitar duplo clique
        const btn = document.getElementById('confirm-delete-btn');
        const originalText = btn.innerText;
        btn.innerText = 'Apagando...';
        btn.disabled = true;

        fetch(URL_DELETE_MESSAGE, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.innerText = originalText;
                btn.disabled = false;
                closeDeleteModal(); // Fecha modal

                if (data.status === 'success') {
                    const el = document.getElementById(`msg-${id}`);
                    if (el) el.remove();
                } else {
                    alert('Erro ao apagar: ' + (data.message || 'Desconhecido'));
                }
            })
            .catch(err => {
                btn.innerText = originalText;
                btn.disabled = false;
                closeDeleteModal();
                console.error(err);
            });
    }


    function carregarMensagens() {
        fetch(`${URL_GET_MESSAGES}?chat_id=${chatId}&last_id=${lastMsgId}`)
            .then(response => {
                if (!response.ok) throw new Error('Erro na rede');
                return response.json();
            })
            .then(data => {
                if (data.status === 'error') {
                    console.error(data.message);
                    return;
                }

                if (isFirstLoad) {
                    if (loadingMsg) loadingMsg.remove();
                    isFirstLoad = false;
                }

                if (data.mensagens && data.mensagens.length > 0) {
                    // SE TEM MENSAGENS: Libera o chat para todos
                    document.querySelector('.chat-input-area').style.display = 'flex';

                    const ultimoIdDoArray = data.mensagens[data.mensagens.length - 1].id;
                    if (ultimoIdDoArray > lastMsgId) {
                        lastMsgId = ultimoIdDoArray;
                        renderizar(data.mensagens);
                        rolarParaBaixo();
                    }
                } else if (lastMsgId === 0) {
                    // CHAT VAZIO
                    if (userId == candidatoUserId) {
                        // Se sou candidato e está vazio: BLOQUEIA
                        messagesArea.innerHTML = '<div style="text-align:center; color:#888; margin-top:50px;"><i class="bx bx-time-five" style="font-size:3rem;"></i><br>Aguardando contato da empresa.<br><small>O chat será liberado assim que o recrutador enviar a primeira mensagem.</small></div>';
                        document.querySelector('.chat-input-area').style.display = 'none';
                    } else {
                        // Se sou RH: LIBERA
                        messagesArea.innerHTML = '<div style="text-align:center; color:#999; margin-top:50px;"><i class="bx bx-message-rounded-dots" style="font-size:3rem;"></i><br>Inicie a conversa!</div>';
                        document.querySelector('.chat-input-area').style.display = 'flex';
                    }
                }
            })
            .catch(err => {
                console.error('Erro:', err);
            });
    }

    function renderizar(msgs) {
        if (messagesArea.innerHTML.includes('Inicie a conversa')) messagesArea.innerHTML = '';

        msgs.forEach(msg => {
            if (document.getElementById(`msg-${msg.id}`)) return;

            // LÓGICA DE QUEM ENVIOU (Compatível com equipe)
            let tipo = 'received';

            if (userId == candidatoUserId) {
                // Se eu sou o candidato, só é 'sent' se for meu ID
                if (msg.remetente_id == userId) tipo = 'sent';
            } else {
                // Se eu sou RH/Admin, é 'sent' se NÃO foi o candidato que mandou (ou seja, foi alguém da equipe)
                if (msg.remetente_id != candidatoUserId) tipo = 'sent';
            }

            const div = document.createElement('div');
            div.className = `message ${tipo}`;
            div.id = `msg-${msg.id}`;

            let deleteBtn = '';
            if (msg.remetente_id == userId) {
                // Atualizado para chamar a nova função que abre o modal
                deleteBtn = `<button class="btn-delete" onclick="apagarMensagem(${msg.id})" title="Apagar">
                                <i class='bx bx-trash'></i>
                             </button>`;
            }

            // Garante que não quebre se tiver quebra de linha
            const textoMensagem = msg.mensagem ? msg.mensagem.replace(/\n/g, '<br>') : '';

            div.innerHTML = `
                ${deleteBtn}
                <div class="msg-content-wrapper">
                    ${textoMensagem}
                    <span class="msg-time">${msg.hora_formatada}</span>
                </div>
            `;

            messagesArea.appendChild(div);
        });
    }

    function rolarParaBaixo() {
        messagesArea.scrollTop = messagesArea.scrollHeight;
    }

    function enviarMensagem() {
        const texto = msgInput.value.trim();
        if (!texto) return;

        const formData = new FormData();
        formData.append('chat_id', chatId);
        formData.append('mensagem', texto);

        msgInput.value = '';
        msgInput.focus();

        fetch(URL_SEND_MESSAGE, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    carregarMensagens();
                } else {
                    // alert('Erro ao enviar: ' + (data.message || 'Desconhecido'));
                }
            });
    }

    function handleEnter(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            enviarMensagem();
        }
    }

    carregarMensagens();
    setInterval(carregarMensagens, 3000);
</script>