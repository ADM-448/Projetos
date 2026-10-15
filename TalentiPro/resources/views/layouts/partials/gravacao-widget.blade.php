{{--
    Widget global de gravação de reunião.
    Mostra uma barra flutuante em qualquer tela enquanto há gravação em andamento
    (ou transcrição pronta para revisar) e redireciona para a tela de revisão
    assim que o popup gravador terminar a transcrição.
--}}
<div id="gravacao-widget" role="status" aria-live="polite" style="display:none; position:fixed; right:20px; bottom:20px; z-index:9990; max-width:360px; background:linear-gradient(135deg,#2d1f3a,#5a3c6c); color:#fff; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,.35); padding:14px 16px; font-family:inherit; padding-right:32px;">
    <button type="button" id="gw-btn-fechar" onclick="fecharWidgetManual()" title="Dispensar aviso" aria-label="Fechar" style="position:absolute; top:8px; right:8px; background:none; border:none; color:rgba(255,255,255,.6); font-size:1.1rem; cursor:pointer; padding:4px 7px; border-radius:50%; line-height:1; transition:all .15s;">
        <i class="fa-solid fa-xmark"></i>
    </button>
    <div style="display:flex; align-items:center; gap:10px;">
        <span id="gw-dot" style="width:12px; height:12px; border-radius:50%; background:#ef4444; flex-shrink:0; animation:gwPulse 1.2s infinite;"></span>
        <div style="min-width:0; flex:1;">
            <div id="gw-titulo" style="font-weight:700; font-size:.9rem;">Gravando reunião</div>
            <div id="gw-sub" style="font-size:.75rem; opacity:.8; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"></div>
        </div>
        <span id="gw-tempo" style="font-family:ui-monospace,Consolas,monospace; font-weight:700; font-size:1.05rem;">00:00</span>
    </div>
    <div id="gw-acoes" style="display:flex; gap:8px; margin-top:12px; flex-wrap:wrap;"></div>
</div>
<style>
    @keyframes gwPulse { 0%{box-shadow:0 0 0 0 rgba(239,68,68,.7)} 70%{box-shadow:0 0 0 10px rgba(239,68,68,0)} 100%{box-shadow:0 0 0 0 rgba(239,68,68,0)} }
    #gravacao-widget button:not(#gw-btn-fechar), #gravacao-widget a { border:none; cursor:pointer; font-weight:700; font-size:.8rem; padding:8px 14px; border-radius:999px; color:#fff; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
    #gravacao-widget .gw-parar { background:#ef4444; }
    #gravacao-widget .gw-sec { background:rgba(255,255,255,.18); }
    #gravacao-widget .gw-ok { background:#059669; }
    #gw-btn-fechar:hover { color:#fff !important; background:rgba(255,255,255,.25) !important; }
    @media (max-width: 480px) { #gravacao-widget { left:12px; right:12px; bottom:12px; max-width:none; } }
</style>
<script>
(function () {
    const URL_ATIVA = @json(route('reunioes.gravacao.ativa'));
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const canal = ('BroadcastChannel' in window) ? new BroadcastChannel('talenti-gravacao') : null;
    const w = document.getElementById('gravacao-widget');
    const $ = id => document.getElementById(id);
    const tituloOriginal = document.title;

    let info = null;          // payload da gravação ativa
    let segundosBase = 0, base = 0, pausado = false, ultimoTick = 0;
    let pollTimer = null, relogioTimer = null, flashTimer = null;

    const fmt = s => String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');
    const naSala = () => info && location.pathname.replace(/\/$/, '').endsWith('/reunioes/' + info.reuniao_id + '/sala');

    function btn(cls, html, onclick) {
        const b = document.createElement('button');
        b.type = 'button'; b.className = cls; b.innerHTML = html; b.onclick = onclick;
        return b;
    }

    function abrirGravador() {
        const f = 'width=380,height=330,menubar=no,toolbar=no,location=no,status=no,resizable=yes';
        const nome = 'talenti-gravador-' + info.reuniao_id;
        // window.open('') não navega um popup existente (evita reiniciar uma gravação em andamento)
        const p = window.open('', nome, f);
        if (!p) return;
        let href = 'about:blank';
        try { href = p.location.href; } catch (e) {}
        if (href === 'about:blank') p.location.href = info.url_gravador;
        p.focus();
    }

    function solicitarParar() {
        canal && canal.postMessage({ type: 'solicitar_parar', reuniaoId: info.reuniao_id });
        // Se o popup não existir mais, reabre para que ele assuma o envio do áudio
        setTimeout(() => { if (info && info.status === 'gravando' && Date.now() - ultimoTick > 2500) abrirGravador(); }, 1500);
    }

    window.fecharWidgetManual = function() {
        if (info) {
            sessionStorage.setItem('gw_fechado_' + info.reuniao_id + '_' + info.status, '1');
        }
        w.style.display = 'none';
    };

    function render() {
        if (!info) { w.style.display = 'none'; return; }

        const chaveFechado = 'gw_fechado_' + info.reuniao_id + '_' + info.status;
        if (sessionStorage.getItem(chaveFechado) === '1') {
            w.style.display = 'none';
            return;
        }

        const acoes = $('gw-acoes');
        acoes.innerHTML = '';
        $('gw-sub').textContent = info.titulo || '';
        const dot = $('gw-dot');

        if (info.status === 'gravando' && info.interrompida) {
            dot.style.background = '#f59e0b'; dot.style.animation = 'none';
            $('gw-titulo').textContent = 'Gravação interrompida';
            $('gw-tempo').textContent = '';
            $('gw-sub').textContent = 'O popup foi fechado. Recupere o áudio gravado.';
            acoes.append(btn('gw-ok', '<i class="fa-solid fa-life-ring"></i> Recuperar áudio', abrirGravador));
        } else if (info.status === 'gravando') {
            dot.style.background = pausado ? '#f59e0b' : '#ef4444';
            dot.style.animation = pausado ? 'none' : '';
            $('gw-titulo').textContent = pausado ? 'Gravação pausada' : 'Gravando reunião';
            acoes.append(btn('gw-parar', '<i class="fa-solid fa-square"></i> Parar e revisar', solicitarParar));
            acoes.append(btn('gw-sec', '<i class="fa-solid fa-window-restore"></i> Abrir gravador', abrirGravador));
        } else if (info.status === 'enviando') {
            dot.style.background = '#a78bfa'; dot.style.animation = 'none';
            $('gw-titulo').innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Transcrevendo áudio…';
            $('gw-tempo').textContent = '';
        } else if (info.status === 'transcrita') {
            dot.style.background = '#34d399'; dot.style.animation = 'none';
            $('gw-titulo').textContent = 'Transcrição pronta para revisar';
            $('gw-tempo').textContent = '';
            if (!naSala()) {
                const a = document.createElement('a');
                a.href = info.url_sala; a.className = 'gw-ok';
                a.innerHTML = '<i class="fa-solid fa-arrow-right"></i> Revisar agora';
                acoes.append(a);
            }
        }
        w.style.display = (info.status === 'transcrita' && naSala()) ? 'none' : 'block';
    }

    function atualizarRelogio() {
        if (!info || info.status !== 'gravando' || info.interrompida) return;
        const s = pausado ? segundosBase : segundosBase + Math.floor((Date.now() - base) / 1000);
        $('gw-tempo').textContent = fmt(s);
    }

    function aplicar(payload) {
        info = payload && payload.ativa ? payload : null;
        if (info) {
            segundosBase = info.segundos || 0; base = Date.now();
            iniciarTimers();
        } else {
            pararTimers();
        }
        render(); atualizarRelogio();
    }

    async function consultar() {
        try {
            const r = await fetch(URL_ATIVA, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, credentials: 'same-origin' });
            if (!r.ok) return;
            aplicar(await r.json());
        } catch (e) {}
    }

    function iniciarTimers() {
        if (!pollTimer) pollTimer = setInterval(consultar, 6000);
        if (!relogioTimer) relogioTimer = setInterval(atualizarRelogio, 1000);
    }
    function pararTimers() {
        clearInterval(pollTimer); clearInterval(relogioTimer); pollTimer = relogioTimer = null;
    }

    // ---- Transcrição pronta: leva o usuário para a tela de revisão ----
    function irParaRevisao(url) {
        // Se a aba está em segundo plano/minimizada, notifica; ao clicar, foca esta aba
        if (document.hidden) {
            if ('Notification' in window && Notification.permission === 'granted') {
                try {
                    const n = new Notification('Transcrição pronta', {
                        body: 'Clique para revisar a transcrição da reunião.',
                        tag: 'talenti-transcricao', requireInteraction: true
                    });
                    n.onclick = () => { window.focus(); n.close(); };
                } catch (e) {}
            }
            let alt = false;
            clearInterval(flashTimer);
            flashTimer = setInterval(() => {
                document.title = (alt = !alt) ? '🔴 Transcrição pronta!' : tituloOriginal;
            }, 1000);
            document.addEventListener('visibilitychange', function limpar() {
                if (!document.hidden) {
                    clearInterval(flashTimer); document.title = tituloOriginal;
                    document.removeEventListener('visibilitychange', limpar);
                }
            });
        }
        window.location.href = url;
    }

    canal && canal.addEventListener('message', ev => {
        const m = ev.data || {};
        if (!m.type) return;
        if (m.type === 'iniciou') { consultar(); }
        else if (m.type === 'tick' && info && m.reuniaoId === info.reuniao_id) {
            ultimoTick = Date.now(); segundosBase = m.seconds; base = Date.now(); pausado = !!m.pausado;
            if (info.interrompida || info.status !== 'gravando') { info.interrompida = false; info.status = 'gravando'; render(); }
            atualizarRelogio();
        }
        else if (m.type === 'tick' && !info) { consultar(); }
        else if (m.type === 'parou' || m.type === 'enviando') { if (info) { info.status = 'enviando'; render(); } else { consultar(); } }
        else if (m.type === 'erro') { consultar(); }
        else if (m.type === 'transcrita') {
            canal.postMessage({ type: 'ack_transcrita', reuniaoId: m.reuniaoId });
            if (info) { info.status = 'transcrita'; }
            irParaRevisao(m.url);
        }
    });

    // Responde pings caso o popup esteja ativo ao carregar uma nova página
    consultar().then(() => { canal && info && canal.postMessage({ type: 'ping', reuniaoId: info.reuniao_id }); });
})();
</script>
