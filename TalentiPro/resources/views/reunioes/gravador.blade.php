<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gravando reunião — TalentiPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #2d1f3a 0%, #5a3c6c 100%); color: #fff;
        }
        .box { width: 100%; max-width: 340px; padding: 22px; text-align: center; }
        .titulo { font-size: .8rem; opacity: .75; margin: 0 0 4px; text-transform: uppercase; letter-spacing: .08em; }
        .reuniao { font-size: .95rem; font-weight: 600; margin: 0 0 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .status { display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 1rem; }
        .dot { width: 12px; height: 12px; border-radius: 50%; background: #ef4444; animation: pulse 1.2s infinite; }
        .dot.pausado { background: #f59e0b; animation: none; }
        .dot.parado { background: #94a3b8; animation: none; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(239,68,68,.7); } 70% { box-shadow: 0 0 0 10px rgba(239,68,68,0); } 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0); } }
        .tempo { font-family: ui-monospace, Consolas, monospace; font-size: 2.6rem; font-weight: 700; margin: 12px 0 16px; letter-spacing: .04em; }
        .acoes { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
        button {
            border: none; cursor: pointer; font-weight: 700; font-size: .95rem; padding: 12px 20px;
            border-radius: 999px; color: #fff; transition: transform .15s, opacity .15s;
        }
        button:hover { transform: translateY(-1px); }
        button:disabled { opacity: .5; cursor: not-allowed; transform: none; }
        .btn-parar { background: #ef4444; }
        .btn-pausar { background: rgba(255,255,255,.18); }
        .btn-sec { background: rgba(255,255,255,.18); }
        .btn-ok { background: #059669; }
        .msg { margin-top: 14px; font-size: .82rem; line-height: 1.45; opacity: .9; }
        .erro { background: rgba(239,68,68,.25); border: 1px solid rgba(239,68,68,.6); padding: 10px 12px; border-radius: 10px; margin-top: 14px; font-size: .82rem; text-align: left; }
        .oculto { display: none !important; }
        .vu { height: 6px; background: rgba(255,255,255,.18); border-radius: 99px; overflow: hidden; margin: 0 auto 6px; max-width: 220px; }
        .vu > span { display: block; height: 100%; width: 0; background: #34d399; transition: width .1s; }
    </style>
</head>
<body>
<main class="box" id="app">
    <p class="titulo"><i class="fa-solid fa-microphone-lines"></i> Sala de Feedback</p>
    <p class="reuniao" title="{{ $reuniao->titulo }}">{{ $reuniao->titulo }}</p>

    {{-- Estado: gravando --}}
    <section id="vista-gravando" class="oculto">
        <div class="status"><span class="dot" id="dot"></span><span id="status-texto">Gravando</span></div>
        <div class="tempo" id="tempo" aria-live="polite">00:00</div>
        <div class="vu" aria-hidden="true"><span id="vu"></span></div>
        <div id="vu-aviso" style="font-size: .75rem; opacity: .85; margin: 4px auto 12px; max-width: 260px;">Aguardando captação de voz...</div>
        <div class="acoes">
            <button id="btn-pausar" class="btn-pausar" type="button"><i class="fa-solid fa-pause"></i> Pausar</button>
            <button id="btn-parar" class="btn-parar" type="button"><i class="fa-solid fa-square"></i> Parar e revisar</button>
        </div>
        <p class="msg">Pode fechar ou navegar na janela principal do sistema. <strong>Mantenha este popup aberto</strong> (pode minimizá-lo).</p>
    </section>

    {{-- Estado: enviando / transcrevendo --}}
    <section id="vista-enviando" class="oculto">
        <div class="status"><i class="fa-solid fa-circle-notch fa-spin"></i> <span id="enviando-texto">Enviando e transcrevendo…</span></div>
        <p class="msg">Isso pode levar alguns instantes. Você será levado automaticamente para a tela de revisão da transcrição.</p>
    </section>

    {{-- Estado: concluído --}}
    <section id="vista-pronto" class="oculto">
        <div class="status"><i class="fa-solid fa-circle-check" style="color:#34d399"></i> Transcrição pronta</div>
        <p class="msg">Abrindo a tela de revisão…</p>
        <div class="acoes" style="margin-top:12px">
            <button id="btn-abrir-revisao" class="btn-ok" type="button"><i class="fa-solid fa-arrow-right"></i> Abrir revisão</button>
        </div>
    </section>

    {{-- Estado: recuperação de áudio órfão --}}
    <section id="vista-recuperar" class="oculto">
        <div class="status"><i class="fa-solid fa-life-ring" style="color:#fbbf24"></i> Áudio recuperável</div>
        <p class="msg">Encontramos uma gravação anterior que não foi enviada (<span id="rec-info"></span>).</p>
        <div class="acoes" style="margin-top:12px">
            <button id="btn-baixar-recuperado" class="btn-sec" type="button" style="background:#4f46e5; width:100%; margin-bottom:6px;"><i class="fa-solid fa-download"></i> Baixar cópia do áudio (.webm)</button>
            <button id="btn-enviar-recuperado" class="btn-ok" type="button">Enviar áudio recuperado</button>
            <button id="btn-descartar" class="btn-sec" type="button">Descartar</button>
        </div>
    </section>

    {{-- Estado: iniciar (permissão negada ou aguardando) --}}
    <section id="vista-iniciar" class="oculto">
        <div class="acoes">
            <button id="btn-iniciar" class="btn-parar" type="button"><i class="fa-solid fa-circle"></i> Iniciar gravação</button>
        </div>
    </section>

    <div id="erro" class="erro oculto" role="alert"></div>
    <div class="acoes oculto" id="acoes-erro" style="margin-top:10px">
        <button id="btn-baixar-erro" class="btn-sec" type="button" style="background:#4f46e5; width:100%; margin-bottom:6px;"><i class="fa-solid fa-download"></i> Baixar cópia do áudio (.webm)</button>
        <button id="btn-tentar-novamente" class="btn-sec" type="button"><i class="fa-solid fa-rotate-right"></i> Tentar novamente</button>
    </div>
</main>

<script>
(() => {
    const REUNIAO_ID   = {{ $reuniao->id }};
    const CSRF         = document.querySelector('meta[name="csrf-token"]').content;
    const URL_INICIAR  = @json(route('reunioes.gravacao.iniciar', $reuniao->id));
    const URL_HEART    = @json(route('reunioes.gravacao.heartbeat', $reuniao->id));
    const URL_UPLOAD   = @json(route('reunioes.upload_audio', $reuniao->id));
    const URL_CANCELAR = @json(route('reunioes.gravacao.cancelar', $reuniao->id));
    const URL_SALA     = @json(route('reunioes.sala', $reuniao->id) . '?etapa=transcricao');

    const canal = ('BroadcastChannel' in window) ? new BroadcastChannel('talenti-gravacao') : null;
    const $ = id => document.getElementById(id);

    let rec = null, stream = null, audioCtx = null, analyser = null, vuRaf = null;
    let seq = 0, wakeLock = null, estado = 'ocioso'; // ocioso|gravando|pausado|enviando|pronto
    let acumulado = 0, retomadoEm = 0; // ms
    let ultimoBlob = null;

    // ---------- UI ----------
    function vista(nome) {
        ['gravando','enviando','pronto','recuperar','iniciar'].forEach(v => $('vista-' + v).classList.toggle('oculto', v !== nome));
    }
    function erro(msg, retry = false) {
        $('erro').textContent = msg; $('erro').classList.remove('oculto');
        $('acoes-erro').classList.toggle('oculto', !retry);
    }
    function limparErro() { $('erro').classList.add('oculto'); $('acoes-erro').classList.add('oculto'); }
    function fmt(s) { return String(Math.floor(s/60)).padStart(2,'0') + ':' + String(s%60).padStart(2,'0'); }
    function segundos() { return Math.floor((acumulado + (estado === 'gravando' ? Date.now() - retomadoEm : 0)) / 1000); }
    function emitir(msg) { canal && canal.postMessage({ reuniaoId: REUNIAO_ID, ...msg }); }

    // ---------- IndexedDB (chunks de áudio para recuperação) ----------
    const idb = {
        db: null,
        open() {
            return new Promise((res, rej) => {
                if (this.db && this.db.objectStoreNames.contains('chunks')) return res(this.db);
                const r = indexedDB.open('talenti-gravador', 2);
                r.onupgradeneeded = () => {
                    const db = r.result;
                    if (!db.objectStoreNames.contains('chunks')) {
                        db.createObjectStore('chunks', { keyPath: 'k' });
                    }
                };
                r.onsuccess = () => { this.db = r.result; res(this.db); };
                r.onerror = () => rej(r.error);
            });
        },
        async put(i, blob) {
            try {
                const db = await this.open();
                db.transaction('chunks', 'readwrite').objectStore('chunks')
                  .put({ k: `${REUNIAO_ID}:${String(i).padStart(6,'0')}`, blob });
            } catch (e) { /* IndexedDB indisponível: segue só em memória */ }
        },
        async all() {
            try {
                const db = await this.open();
                return await new Promise((res, rej) => {
                    const q = db.transaction('chunks').objectStore('chunks')
                        .getAll(IDBKeyRange.bound(`${REUNIAO_ID}:`, `${REUNIAO_ID}:\uffff`));
                    q.onsuccess = () => res(q.result.map(x => x.blob));
                    q.onerror = () => rej(q.error);
                });
            } catch (e) { return []; }
        },
        async clear() {
            try {
                const db = await this.open();
                db.transaction('chunks', 'readwrite').objectStore('chunks')
                  .delete(IDBKeyRange.bound(`${REUNIAO_ID}:`, `${REUNIAO_ID}:\uffff`));
            } catch (e) {}
        }
    };

    // ---------- Heartbeat/tick em Web Worker (não sofre throttling em aba minimizada) ----------
    let worker = null;
    function iniciarRelogio() {
        const src = 'setInterval(()=>postMessage(1),1000)';
        worker = new Worker(URL.createObjectURL(new Blob([src], { type: 'text/javascript' })));
        let n = 0;
        worker.onmessage = () => {
            n++;
            const s = segundos();
            $('tempo').textContent = fmt(s);
            document.title = (estado === 'pausado' ? '⏸ ' : '🔴 ') + fmt(s) + ' — Gravando reunião';
            emitir({ type: 'tick', seconds: s, pausado: estado === 'pausado' });
            if (n % 3 === 0) post(URL_HEART).catch(() => {});
        };
    }
    function pararRelogio() { worker && worker.terminate(); worker = null; }

    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: body || new FormData()
        });
    }

    let memoriaChunks = [];
    let vozDetectadaAlgumaVez = false;

    // ---------- Medidor de volume ----------
    function iniciarVu() {
        try {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const src = audioCtx.createMediaStreamSource(stream);
            analyser = audioCtx.createAnalyser(); analyser.fftSize = 256;
            src.connect(analyser);
            const buf = new Uint8Array(analyser.frequencyBinCount);
            let framesSilencio = 0;
            const avisoEl = $('vu-aviso');

            const loop = () => {
                analyser.getByteFrequencyData(buf);
                const media = buf.reduce((a, b) => a + b, 0) / buf.length;
                const pct = Math.min(100, media * 2.2);
                $('vu').style.width = pct + '%';

                if (pct > 8) {
                    vozDetectadaAlgumaVez = true;
                    framesSilencio = 0;
                    if (avisoEl) {
                        avisoEl.innerHTML = '<span style="color:#34d399;"><i class="fa-solid fa-waveform-lines"></i> Voz/áudio detectado com sucesso!</span>';
                    }
                } else {
                    framesSilencio++;
                    if (framesSilencio > 150 && avisoEl) { // ~3 segundos de silêncio
                        if (vozDetectadaAlgumaVez) {
                            avisoEl.innerHTML = '<span style="opacity:0.75;">Microfone pronto (aguardando fala)...</span>';
                        } else {
                            avisoEl.innerHTML = '<span style="color:#fbbf24;"><i class="fa-solid fa-triangle-exclamation"></i> Microfone muito baixo ou silencioso. Fale mais perto ou aumente o volume.</span>';
                        }
                    }
                }

                vuRaf = requestAnimationFrame(loop);
            };
            loop();
        } catch (e) {}
    }

    // ---------- Gravação ----------
    async function iniciar() {
        limparErro();
        memoriaChunks = [];
        vozDetectadaAlgumaVez = false;

        const audioConstraints = {
            echoCancellation: true,
            noiseSuppression: false, // Desligado para não podar fala sussurrada ou distante
            autoGainControl: true    // Aumenta o volume automaticamente para não ficar inaudível!
        };

        try {
            stream = await navigator.mediaDevices.getUserMedia({ audio: audioConstraints });
        } catch (e) {
            try {
                // Fallback para constraints básicas caso o hardware do dispositivo seja restrito
                stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            } catch (err) {
                vista('iniciar');
                erro('Não foi possível acessar o microfone. Libere a permissão do navegador e clique em "Iniciar gravação".');
                return;
            }
        }

        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().catch(() => {});
        }

        await idb.clear();
        seq = 0;
        const mime = MediaRecorder.isTypeSupported('audio/webm;codecs=opus') ? 'audio/webm;codecs=opus' : 'audio/webm';
        
        try {
            rec = new MediaRecorder(stream, { mimeType: mime, audioBitsPerSecond: 96000 });
        } catch (e) {
            rec = new MediaRecorder(stream);
        }

        rec.ondataavailable = e => {
            if (e.data && e.data.size > 0) {
                memoriaChunks.push(e.data);
                idb.put(seq++, e.data);
            }
        };
        rec.onstop = finalizar;

        try {
            const r = await post(URL_INICIAR);
            if (!r.ok) throw new Error();
        } catch (e) {
            stream.getTracks().forEach(t => t.stop());
            vista('iniciar');
            erro('Não foi possível registrar o início da gravação. Verifique sua conexão e tente novamente.');
            return;
        }

        rec.start(3000); // Salva chunks a cada 3s
        acumulado = 0; retomadoEm = Date.now(); estado = 'gravando';
        $('dot').className = 'dot'; $('status-texto').textContent = 'Gravando';
        vista('gravando');
        iniciarVu();
        iniciarRelogio();
        emitir({ type: 'iniciou' });
        try { wakeLock = await navigator.wakeLock?.request('screen'); } catch (e) {}
    }

    function pausarOuRetomar() {
        if (!rec) return;
        if (estado === 'gravando') {
            rec.pause(); acumulado += Date.now() - retomadoEm; estado = 'pausado';
            $('dot').className = 'dot pausado'; $('status-texto').textContent = 'Pausado';
            $('btn-pausar').innerHTML = '<i class="fa-solid fa-play"></i> Retomar';
        } else if (estado === 'pausado') {
            rec.resume(); retomadoEm = Date.now(); estado = 'gravando';
            $('dot').className = 'dot'; $('status-texto').textContent = 'Gravando';
            $('btn-pausar').innerHTML = '<i class="fa-solid fa-pause"></i> Pausar';
        }
    }

    function parar() {
        if (!rec || rec.state === 'inactive') return;
        if (estado === 'gravando') acumulado += Date.now() - retomadoEm;
        estado = 'enviando';
        rec.stop(); // dispara finalizar()
    }

    async function finalizar() {
        stream && stream.getTracks().forEach(t => t.stop());
        pararRelogio();
        vuRaf && cancelAnimationFrame(vuRaf);
        audioCtx && audioCtx.close().catch(() => {});
        wakeLock && wakeLock.release().catch(() => {});
        document.title = 'Enviando… — TalentiPro';
        emitir({ type: 'parou' });

        // Pequena espera para o último chunk emitido pelo stop ser recebido
        await new Promise(r => setTimeout(r, 400));
        
        if (memoriaChunks.length > 0) {
            ultimoBlob = new Blob(memoriaChunks, { type: 'audio/webm' });
        } else {
            const blobs = await idb.all();
            ultimoBlob = new Blob(blobs, { type: 'audio/webm' });
        }

        await enviar();
    }

    async function enviar() {
        limparErro();
        vista('enviando');
        estado = 'enviando';
        emitir({ type: 'enviando' });
        const fd = new FormData();
        fd.append('audio', ultimoBlob, 'reuniao.webm');
        try {
            const resp = await post(URL_UPLOAD, fd);
            const data = await resp.json().catch(() => ({}));
            if (!resp.ok || !data.success) {
                vista('enviando');
                $('enviando-texto').textContent = 'Falha no envio';
                erro(data.error || 'Erro ao enviar/transcrever o áudio. O áudio está salvo neste navegador.', true);
                emitir({ type: 'erro', msg: data.error || 'Falha ao transcrever' });
                return;
            }
            await idb.clear();
            ultimoBlob = null;
            concluir();
        } catch (e) {
            $('enviando-texto').textContent = 'Falha de conexão';
            erro('Falha de conexão ao enviar o áudio. O áudio está salvo neste navegador — tente novamente.', true);
        }
    }

    // ---------- Conclusão: leva o usuário para a tela de revisão ----------
    function concluir() {
        estado = 'pronto';
        vista('pronto');
        document.title = '✅ Transcrição pronta — TalentiPro';

        let alguemRespondeu = false;
        const onAck = ev => { if (ev.data && ev.data.type === 'ack_transcrita' && ev.data.reuniaoId === REUNIAO_ID) alguemRespondeu = true; };
        canal && canal.addEventListener('message', onAck);
        emitir({ type: 'transcrita', url: URL_SALA });

        // Se nenhuma aba do sistema estiver aberta, este próprio popup vira a tela de revisão
        setTimeout(() => {
            canal && canal.removeEventListener('message', onAck);
            if (!alguemRespondeu) { window.location.href = URL_SALA; }
            else { setTimeout(() => window.close(), 1500); }
        }, 1500);
    }

    // ---------- Recuperação de áudio órfão ----------
    async function verificarOrfao() {
        const blobs = await idb.all();
        if (!blobs.length) return false;
        ultimoBlob = new Blob(blobs, { type: 'audio/webm' });
        $('rec-info').textContent = (ultimoBlob.size / 1048576).toFixed(1) + ' MB';
        vista('recuperar');
        return true;
    }

    async function baixarAudio() {
        let blob = ultimoBlob;
        if (!blob) {
            const blobs = await idb.all();
            if (blobs.length > 0) {
                blob = new Blob(blobs, { type: 'audio/webm' });
            }
        }
        if (!blob || blob.size === 0) {
            alert('Nenhum áudio encontrado na memória deste navegador.');
            return;
        }
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `reuniao_${REUNIAO_ID}_gravacao_completa.webm`;
        document.body.appendChild(a);
        a.click();
        setTimeout(() => URL.revokeObjectURL(url), 15000);
    }

    // ---------- Eventos ----------
    $('btn-parar').onclick = parar;
    $('btn-pausar').onclick = pausarOuRetomar;
    $('btn-iniciar').onclick = iniciar;
    $('btn-tentar-novamente').onclick = () => ultimoBlob ? enviar() : iniciar();
    $('btn-abrir-revisao').onclick = () => { window.location.href = URL_SALA; };
    $('btn-enviar-recuperado').onclick = enviar;
    $('btn-baixar-recuperado').onclick = baixarAudio;
    $('btn-baixar-erro').onclick = baixarAudio;
    $('btn-descartar').onclick = async () => {
        await idb.clear(); ultimoBlob = null;
        try { await post(URL_CANCELAR); } catch (e) {}
        iniciar();
    };

    canal && canal.addEventListener('message', ev => {
        const m = ev.data || {};
        if (m.reuniaoId !== REUNIAO_ID) return;
        if (m.type === 'solicitar_parar') parar();
        if (m.type === 'ping' && (estado === 'gravando' || estado === 'pausado')) {
            emitir({ type: 'tick', seconds: segundos(), pausado: estado === 'pausado' });
        }
    });

    window.addEventListener('beforeunload', e => {
        if (estado === 'gravando' || estado === 'pausado') { e.preventDefault(); e.returnValue = ''; }
    });

    // ---------- Início ----------
    (async () => {
        if (!navigator.mediaDevices || !window.MediaRecorder) {
            vista('iniciar'); $('btn-iniciar').disabled = true;
            erro('Este navegador não suporta gravação de áudio. Use Chrome, Edge ou Firefox atualizados.');
            return;
        }
        if (await verificarOrfao()) return;
        iniciar();
    })();
})();
</script>
</body>
</html>
