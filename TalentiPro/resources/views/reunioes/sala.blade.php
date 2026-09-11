@extends('layouts.app')

@section('title', 'Sala de Feedback - ' . $reuniao->titulo)
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-microphone-lines" style="color: var(--primary-color);"></i> Sala de Feedback</h1>
        <p style="color: #666;">Reunião com: {{ auth()->id() === $reuniao->gestor_id ? $reuniao->participantes->pluck('name')->join(', ') : $reuniao->gestor->name }}</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        @if($reuniao->google_meet_link)
            <a href="{{ $reuniao->google_meet_link }}" target="_blank" class="btn-primary" style="background-color: #059669; text-decoration: none;">
                <i class="fa-solid fa-video"></i> Abrir Google Meet
            </a>
        @endif
        <a href="{{ route('reunioes.index') }}" class="btn-secondary" style="text-decoration: none;">Voltar</a>
    </div>
</div>

{{-- ============================================================ --}}
{{-- BLOCO DO COLABORADOR: Aguardando o Gestor                    --}}
{{-- ============================================================ --}}
@if(auth()->id() !== $reuniao->gestor_id)
<div class="card" style="padding: 40px; text-align: center; max-width: 700px; margin: 0 auto;">
    @if($reuniao->google_meet_link)
        {{-- Reunião via Google Meet: direciona o convidado para a sala --}}
        <i class="fa-brands fa-google fa-3x" style="color: #4285F4; margin-bottom: 20px;"></i>
        <h2 style="color: #334155; margin-bottom: 10px;">Reunião pelo Google Meet</h2>
        <p style="color: #64748b; margin-bottom: 25px;">O seu gestor agendou esta reunião pelo Google Meet. Clique no botão abaixo para entrar na sala virtual.</p>
        <a href="{{ $reuniao->google_meet_link }}" target="_blank" class="btn-primary" style="background: #4285F4; text-decoration: none; padding: 14px 30px; border-radius: 8px; font-size: 1rem; font-weight: bold;">
            <i class="fa-solid fa-video"></i> Entrar no Google Meet
        </a>
    @else
        {{-- Reunião presencial / gravação local: apenas lembrete --}}
        <i class="fa-solid fa-handshake fa-4x" style="color: #a78bfa; margin-bottom: 20px;"></i>
        <h2 style="color: #334155; margin-bottom: 10px;">Reunião Presencial</h2>
        <p style="color: #64748b; margin-bottom: 8px;">Esta reunião está agendada para ocorrer presencialmente ou no local combinado com seu gestor.</p>
        <p style="color: #94a3b8; font-size: 0.9rem;">Quando encerrada, o relatório ficará disponível aqui automaticamente.</p>
        <div style="margin-top: 25px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; text-align: left;">
            <p style="margin: 0; font-weight: 600; color: #475569;"><i class="fa-solid fa-calendar-days" style="color: var(--primary-color);"></i> {{ $reuniao->data_hora->format('d/m/Y \à\s H:i') }}</p>
            <p style="margin: 6px 0 0; color: #64748b; font-size: 0.9rem;">Reunião com: {{ $reuniao->gestor->name }}</p>
        </div>
    @endif
</div>

{{-- ============================================================ --}}
{{-- BLOCO DO GESTOR: Fluxo completo em etapas                   --}}
{{-- ============================================================ --}}
@else
<div id="sala-wrapper" style="max-width: 900px; margin: 0 auto;">

    {{-- INDICADOR DE ETAPAS --}}
    <div style="display: flex; align-items: center; margin-bottom: 30px; gap: 0;">
        @foreach([
            ['1', 'Gravação'],
            ['2', 'Transcrição'],
            ['3', 'Análise IA'],
            ['4', 'Revisão']
        ] as $i => $etapa)
        <div class="etapa-indicator" id="etapa-label-{{ $etapa[0] }}" style="display: flex; align-items: center; flex: 1;">
            <div style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; border-radius: 6px; font-weight: 600; font-size: 0.9rem; color: {{ $i === 0 ? '#fff' : '#94a3b8' }}; background: {{ $i === 0 ? 'var(--primary-color)' : '#f1f5f9' }}; transition: all 0.3s;">
                <span style="width: 22px; height: 22px; border-radius: 50%; background: {{ $i === 0 ? 'rgba(255,255,255,0.3)' : '#e2e8f0' }}; display: flex; align-items: center; justify-content: center; font-size: 0.75rem;">{{ $etapa[0] }}</span>
                {{ $etapa[1] }}
            </div>
            @if($i < 3)<div style="flex: 1; height: 2px; background: #e2e8f0;"></div>@endif
        </div>
        @endforeach
    </div>

    {{-- ============================================================ --}}
    {{-- ETAPA 1: Gravação de Áudio                                  --}}
    {{-- ============================================================ --}}
    <div id="etapa-1" class="etapa-panel">
        <div class="card" style="padding: 35px; text-align: center;">

            @if($podeUsarIa)
            {{-- Modo com IA --}}
            <h2 style="color: #334155; margin-bottom: 10px;"><i class="fa-solid fa-wand-magic-sparkles" style="color: var(--primary-color);"></i> Assistente de IA</h2>
            <p style="color: #64748b; margin-bottom: 30px; max-width: 550px; margin-left: auto; margin-right: auto;">
                Clique em gravar quando a reunião começar. Ao parar, o áudio será enviado para a Inteligência Artificial para transcrição automática.
            </p>

            <div id="recording-controls">
                <button id="btn-start" onclick="iniciarGravacao()" class="btn-primary" style="background: #ef4444; font-size: 1.1rem; padding: 16px 35px; border-radius: 50px; border: none; cursor: pointer;">
                    <i class="fa-solid fa-circle"></i> Iniciar Gravação
                </button>
                <button id="btn-stop" onclick="pararGravacao()" style="display: none; background: #475569; color: white; font-size: 1.1rem; padding: 16px 35px; border-radius: 50px; border: none; cursor: pointer;">
                    <i class="fa-solid fa-square"></i> Parar e Enviar
                </button>
            </div>

            <div id="cronometro" style="display: none; margin-top: 20px; font-size: 2rem; font-weight: bold; color: #ef4444; font-family: monospace;" aria-live="polite">00:00</div>

            @endif

            <div style="margin: 40px 0; position: relative; text-align: center;">
                <hr style="border: none; border-top: 1px dashed #cbd5e1; margin: 0;">
                <span style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%); background: white; padding: 0 15px; color: #94a3b8; font-weight: 600; font-size: 0.85rem; text-transform: uppercase;">OU</span>
            </div>

            {{-- Modo manual sempre disponível --}}
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 30px; border-radius: 8px; text-align: left;">
                <h3 style="color: #334155; margin-top: 0;"><i class="fa-solid fa-pen-to-square"></i> Registro Manual da Reunião</h3>
                <p style="color: #64748b; font-size: 0.95rem;">
                    @if($podeUsarIa)
                        Prefere não usar a gravação? Realize a reunião normalmente e registre a ata abaixo manualmente.
                    @else
                        A gravação com IA não está disponível para sua conta. Realize a reunião normalmente e registre a ata abaixo.
                    @endif
                </p>
                <form action="{{ route('reunioes.salvar_manual', $reuniao->id) }}" method="POST">
                    @csrf
                    <label for="resumo" style="display: block; font-weight: 600; margin-bottom: 8px; color: #475569;">Ata / Resumo da Reunião *</label>
                    <textarea id="resumo" name="resumo" rows="10" required
                        placeholder="Digite os pontos discutidos, feedbacks e próximos passos..."
                        style="width: 100%; padding: 15px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit; font-size: 1rem; resize: vertical; box-sizing: border-box;"></textarea>
                    <button type="submit" class="btn-primary" style="margin-top: 15px; background: var(--primary-color); border: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; cursor: pointer;">
                        <i class="fa-solid fa-check"></i> Salvar Relatório Manualmente
                    </button>
                </form>
            </div>

        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- ETAPA 2: Processando / Transcrição                          --}}
    {{-- ============================================================ --}}
    <div id="etapa-2" class="etapa-panel" style="display: none;">
        <div class="card" style="padding: 35px; text-align: center;">
            <div id="spinner-transcricao">
                <i class="fa-solid fa-circle-notch fa-spin fa-3x" style="color: var(--primary-color); margin-bottom: 20px;"></i>
                <h3 style="color: #334155;">Transcrevendo o áudio...</h3>
                <p style="color: #64748b;">A IA está ouvindo e convertendo a fala em texto. Isso pode levar alguns instantes.</p>
            </div>

            <div id="resultado-transcricao" style="display: none; text-align: left;">
                <h3 style="color: #334155; margin-bottom: 15px;"><i class="fa-solid fa-quote-left" style="color: var(--primary-color);"></i> Transcrição (editável)</h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 10px;">Revise e corrija o texto abaixo antes de prosseguir.</p>
                <textarea id="transcricao-texto" rows="12"
                    style="width: 100%; padding: 15px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem; resize: vertical; box-sizing: border-box; font-family: monospace; color: #334155; line-height: 1.6;"></textarea>

                <div style="margin-top: 15px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 8px; color: #475569;">Participantes desta reunião</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        @foreach($participantes as $p)
                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; background: #f8fafc; padding: 8px 14px; border-radius: 20px; border: 1px solid #e2e8f0;">
                            <input type="checkbox" name="participante_check" value="{{ $p->id }}" checked style="cursor: pointer;">
                            {{ $p->name }}
                        </label>
                        @endforeach
                    </div>
                </div>

                <button onclick="analisarComIa()" class="btn-primary" style="margin-top: 20px; background: var(--primary-color); border: none; padding: 14px 30px; border-radius: 6px; font-size: 1rem; font-weight: bold; cursor: pointer; width: 100%;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Analisar Reunião com IA →
                </button>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- ETAPA 3: Analisando com IA                                  --}}
    {{-- ============================================================ --}}
    <div id="etapa-3" class="etapa-panel" style="display: none;">
        <div class="card" style="padding: 35px; text-align: center;">
            <i class="fa-solid fa-brain fa-3x fa-bounce" style="color: var(--primary-color); margin-bottom: 20px;"></i>
            <h3 style="color: #334155;">Analisando a reunião...</h3>
            <p style="color: #64748b;">Gerando resumo executivo e sugestões de PDI personalizadas.</p>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- ETAPA 4: Revisão e Aprovação                                --}}
    {{-- ============================================================ --}}
    <div id="etapa-4" class="etapa-panel" style="display: none;">

        <div class="card" style="padding: 30px; margin-bottom: 20px;">
            <h3 style="color: #334155; margin-top: 0;"><i class="fa-solid fa-wand-magic-sparkles" style="color: var(--primary-color);"></i> Resumo Executivo <span style="font-size: 0.8rem; color: #94a3b8; font-weight: normal;">(editável)</span></h3>
            <textarea id="resumo-ia" rows="8"
                style="width: 100%; padding: 15px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem; resize: vertical; box-sizing: border-box; line-height: 1.6;"></textarea>

            <div style="margin-top: 15px;">
                <label style="font-weight: 600; color: #475569;">Sentimento Geral da Reunião:</label>
                <select id="sentimento-select" style="margin-left: 10px; padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    <option value="Positivo">😊 Positivo</option>
                    <option value="Neutro" selected>😐 Neutro</option>
                    <option value="Negativo">😟 Negativo</option>
                </select>
            </div>
        </div>

        <div class="card" style="padding: 30px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0; color: #334155;"><i class="fa-solid fa-list-check" style="color: var(--primary-color);"></i> PDIs Sugeridos pela IA</h3>
                <button onclick="adicionarPdi()" style="background: #f1f5f9; border: 1px solid #e2e8f0; padding: 8px 14px; border-radius: 6px; cursor: pointer; font-size: 0.9rem;">
                    <i class="fa-solid fa-plus"></i> Adicionar PDI
                </button>
            </div>
            <div id="pdis-container"></div>
            <p id="sem-pdis" style="color: #94a3b8; text-align: center; font-style: italic; display: none;">Nenhum PDI sugerido. Clique em "Adicionar PDI" para criar manualmente.</p>
        </div>

            <p style="color: #64748b; margin-bottom: 20px; font-size: 0.95rem; text-align: left; background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px 16px; border-radius: 6px;">
                <i class="fa-solid fa-circle-info" style="color: var(--primary-color);"></i>
                <strong>Como funciona:</strong> A IA gerou sugestões de PDI acima. Revise cada uma, edite o que precisar e <strong>desmarque</strong> as que não deseja criar. Somente os itens <span style="color: #059669;">✔ Aprovados</span> serão efetivamente criados no sistema ao salvar.
            </p>
            <button onclick="salvarTudo()" id="btn-salvar-final" class="btn-primary" style="background: var(--primary-color); border: none; padding: 16px 40px; border-radius: 8px; font-size: 1.1rem; font-weight: bold; cursor: pointer; width: 100%; max-width: 400px;">
                <i class="fa-solid fa-floppy-disk"></i> Salvar e Criar PDIs
            </button>
        </div>

    </div>

    {{-- Mensagens de erro global --}}
    <div id="erro-global" style="display: none; margin-top: 20px; background: #fef2f2; color: #991b1b; padding: 15px 20px; border-radius: 8px; border-left: 4px solid #ef4444;"></div>

</div>
@endif

@endsection

@if(auth()->id() === $reuniao->gestor_id && $podeUsarIa)
@push('scripts')
<script>
const REUNIAO_ID   = {{ $reuniao->id }};
const CSRF_TOKEN   = '{{ csrf_token() }}';
const URL_UPLOAD   = '{{ route('reunioes.upload_audio', $reuniao->id) }}';
const URL_PROCESSAR = '{{ route('reunioes.processar_transcricao', $reuniao->id) }}';
const URL_SALVAR   = '{{ route('reunioes.salvar_com_ia', $reuniao->id) }}';

// Lista de participantes disponíveis para seleção manual de PDI
const PARTICIPANTES_LISTA = @json($participantes->map(fn($p) => ['id' => $p->id, 'name' => $p->name]));

let mediaRecorder, audioChunks = [], cronometroInterval, segundos = 0;
let transcricaoOriginalData = '';

// ==================== GRAVAÇÃO ====================

async function iniciarGravacao() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        audioChunks = [];
        mediaRecorder = new MediaRecorder(stream, { mimeType: 'audio/webm' });

        mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
        mediaRecorder.onstop = () => {
            stream.getTracks().forEach(t => t.stop());
            const blob = new Blob(audioChunks, { type: 'audio/webm' });
            enviarAudio(blob);
        };

        mediaRecorder.start();
        document.getElementById('btn-start').style.display = 'none';
        document.getElementById('btn-stop').style.display = 'inline-block';
        document.getElementById('cronometro').style.display = 'block';
        segundos = 0;
        cronometroInterval = setInterval(() => {
            segundos++;
            const m = String(Math.floor(segundos / 60)).padStart(2, '0');
            const s = String(segundos % 60).padStart(2, '0');
            document.getElementById('cronometro').textContent = `${m}:${s}`;
        }, 1000);
    } catch (e) {
        mostrarErro('Não foi possível acessar o microfone. Verifique as permissões do navegador.');
    }
}

function pararGravacao() {
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        clearInterval(cronometroInterval);
        mediaRecorder.stop();
        irParaEtapa(2);
    }
}

// ==================== ETAPA 2: UPLOAD E TRANSCRIÇÃO ====================

async function enviarAudio(blob) {
    const formData = new FormData();
    formData.append('audio', blob, 'reuniao.webm');
    formData.append('_token', CSRF_TOKEN);

    try {
        const resp = await fetch(URL_UPLOAD, { method: 'POST', body: formData });
        const data = await resp.json();

        if (!resp.ok || !data.success) {
            irParaEtapa(1);
            mostrarErro(data.error || 'Erro ao processar o áudio.');
            return;
        }

        document.getElementById('spinner-transcricao').style.display = 'none';
        document.getElementById('transcricao-texto').value = data.transcricao;
        transcricaoOriginalData = data.transcricao;
        document.getElementById('resultado-transcricao').style.display = 'block';

    } catch (e) {
        irParaEtapa(1);
        mostrarErro('Falha de conexão ao enviar o áudio.');
    }
}

// ==================== ETAPA 3: ANÁLISE COM IA ====================

async function analisarComIa() {
    const transcricao = document.getElementById('transcricao-texto').value.trim();
    if (!transcricao) {
        alert('A transcrição está vazia. Não é possível analisar.');
        return;
    }

    const checks = Array.from(document.querySelectorAll('input[name="participante_check"]:checked'));
    if (!checks.length) {
        alert('Selecione ao menos um participante.');
        return;
    }

    irParaEtapa(3);

    const participantes = checks.map(c => c.value);
    const body = new FormData();
    body.append('_token', CSRF_TOKEN);
    body.append('transcricao', transcricao);
    participantes.forEach(id => body.append('participantes[]', id));

    try {
        const resp = await fetch(URL_PROCESSAR, { method: 'POST', body });
        const data = await resp.json();

        if (!resp.ok || !data.success) {
            irParaEtapa(2);
            mostrarErro(data.error || 'Erro na análise com IA.');
            return;
        }

        renderizarEtapa4(data);

    } catch (e) {
        irParaEtapa(2);
        mostrarErro('Falha de conexão ao analisar a reunião.');
    }
}

// ==================== ETAPA 4: REVISÃO ====================

let pdisData = [];
let resumoOriginalData = '';

function renderizarEtapa4(data) {
    document.getElementById('resumo-ia').value = data.resumo || '';
    resumoOriginalData = data.resumo || '';
    const sel = document.getElementById('sentimento-select');
    if (data.sentimento && sel) sel.value = data.sentimento;

    pdisData = data.pdis || [];
    renderizarPdis();
    irParaEtapa(4);
}

function renderizarPdis() {
    const container = document.getElementById('pdis-container');
    const semPdis   = document.getElementById('sem-pdis');
    container.innerHTML = '';

    if (!pdisData.length) {
        semPdis.style.display = 'block';
        return;
    }
    semPdis.style.display = 'none';

    pdisData.forEach((pdi, idx) => {
        container.insertAdjacentHTML('beforeend', `
            <div id="pdi-card-${idx}" style="border: 1px solid #e2e8f0; border-left: 4px solid var(--primary-color); border-radius: 8px; padding: 20px; margin-bottom: 15px; background: #fafafa; position: relative; transition: opacity 0.2s;">

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <label id="pdi-aprovado-label-${idx}" style="display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none; font-weight: 600; color: #334155;">
                        <input type="checkbox" id="pdi-aprovado-${idx}" checked
                            onchange="togglePdiAprovado(${idx})"
                            style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--primary-color);">
                        <span id="pdi-aprovado-texto-${idx}" style="color: #059669; font-size: 0.85rem;">✔ Aprovado — será criado no sistema</span>
                    </label>
                    <button onclick="removerPdi(${idx})" style="background: #fef2f2; border: none; color: #ef4444; width: 30px; height: 30px; border-radius: 50%; cursor: pointer; font-size: 0.85rem;" title="Remover sugestão">✕</button>
                </div>

                <div id="pdi-body-${idx}">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <label style="font-size: 0.8rem; color: #94a3b8; font-weight: 600;">COLABORADOR</label>
                            <select name="pdi_colaborador_${idx}" id="pdi-colab-${idx}" onchange="updatePdiField(${idx}, 'colaborador_id', this.value)" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.9rem; font-weight: 600; color: #334155;">
                                ${PARTICIPANTES_LISTA.map(p => `<option value="${p.id}" ${p.id == pdi.colaborador_id ? 'selected' : ''}>${escapeHtml(p.name)}</option>`).join('')}
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 0.8rem; color: #94a3b8; font-weight: 600;">PRAZO</label>
                            <input type="date" id="pdi-prazo-${idx}" value="${pdi.prazo || ''}" onchange="updatePdiField(${idx}, 'prazo', this.value)" style="width: 100%; padding: 6px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.9rem;">
                        </div>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label style="font-size: 0.8rem; color: #94a3b8; font-weight: 600;">TÍTULO</label>
                        <input type="text" id="pdi-titulo-${idx}" value="${escapeHtml(pdi.titulo || '')}" oninput="updatePdiField(${idx}, 'titulo', this.value)" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.95rem; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="font-size: 0.8rem; color: #94a3b8; font-weight: 600;">DESCRIÇÃO</label>
                        <textarea id="pdi-desc-${idx}" rows="3" oninput="updatePdiField(${idx}, 'descricao', this.value)" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.9rem; resize: vertical; box-sizing: border-box;">${escapeHtml(pdi.descricao || '')}</textarea>
                    </div>
                    <div style="margin-top: 15px; border-top: 1px dashed #e2e8f0; padding-top: 15px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <label style="font-size: 0.85rem; color: #475569; font-weight: 600;"><i class="fa-solid fa-list-check"></i> Metas / Etapas (Ações)</label>
                            <button onclick="adicionarAcaoPdi(${idx})" style="background: #f1f5f9; border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 0.75rem;"><i class="fa-solid fa-plus"></i> Adicionar Etapa</button>
                        </div>
                        <div id="pdi-acoes-container-${idx}" style="display: flex; flex-direction: column; gap: 10px;">
                            ${(pdi.acoes || []).map((acao, aIdx) => `
                                <div style="display: grid; grid-template-columns: 1fr auto; gap: 10px; background: #fff; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; align-items: start;">
                                    <div style="display: flex; flex-direction: column; gap: 8px;">
                                        <input type="text" id="pdi-${idx}-acao-titulo-${aIdx}" value="${escapeHtml(acao.titulo || '')}" oninput="updateAcaoField(${idx}, ${aIdx}, 'titulo', this.value)" placeholder="Título da etapa" style="width: 100%; padding: 6px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.85rem;">
                                        <input type="text" id="pdi-${idx}-acao-desc-${aIdx}" value="${escapeHtml(acao.descricao || '')}" oninput="updateAcaoField(${idx}, ${aIdx}, 'descricao', this.value)" placeholder="Descrição (Opcional)" style="width: 100%; padding: 6px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.85rem;">
                                    </div>
                                    <button onclick="removerAcaoPdi(${idx}, ${aIdx})" style="background: #fef2f2; border: none; color: #ef4444; width: 26px; height: 26px; border-radius: 4px; cursor: pointer; font-size: 0.8rem;" title="Remover etapa"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            `).join('')}
                            ${!(pdi.acoes && pdi.acoes.length) ? '<p style="font-size: 0.8rem; color: #94a3b8; font-style: italic; margin: 0;">Nenhuma etapa cadastrada. O PDI ficará com 0% se não tiver etapas.</p>' : ''}
                        </div>
                    </div>
                </div>
            </div>
        `);
    });
}

function togglePdiAprovado(idx) {
    const checked  = document.getElementById(`pdi-aprovado-${idx}`).checked;
    const card     = document.getElementById(`pdi-card-${idx}`);
    const body     = document.getElementById(`pdi-body-${idx}`);
    const texto    = document.getElementById(`pdi-aprovado-texto-${idx}`);

    if (checked) {
        card.style.opacity = '1';
        card.style.borderLeftColor = 'var(--primary-color)';
        body.style.display = 'block';
        texto.style.color = '#059669';
        texto.textContent = '✔ Aprovado — será criado no sistema';
    } else {
        card.style.opacity = '0.45';
        card.style.borderLeftColor = '#e2e8f0';
        body.style.display = 'none';
        texto.style.color = '#94a3b8';
        texto.textContent = '✗ Não aprovado — não será criado';
    }
}

function updatePdiField(idx, field, value) {
    if (pdisData[idx]) {
        pdisData[idx][field] = value;
    }
}

function updateAcaoField(pdiIdx, acaoIdx, field, value) {
    if (pdisData[pdiIdx] && pdisData[pdiIdx].acoes && pdisData[pdiIdx].acoes[acaoIdx]) {
        pdisData[pdiIdx].acoes[acaoIdx][field] = value;
    }
}

function adicionarAcaoPdi(idx) {
    if (pdisData[idx]) {
        if (!pdisData[idx].acoes) pdisData[idx].acoes = [];
        pdisData[idx].acoes.push({ titulo: '', descricao: '' });
        renderizarPdis();
    }
}

function removerAcaoPdi(pdiIdx, acaoIdx) {
    if (pdisData[pdiIdx] && pdisData[pdiIdx].acoes) {
        pdisData[pdiIdx].acoes.splice(acaoIdx, 1);
        renderizarPdis();
    }
}

function removerPdi(idx) {
    showGlobalModal({
        title: 'Excluir PDI sugerido',
        message: 'Tem certeza que deseja remover esta sugestão de PDI?',
        type: 'danger',
        showCancel: true,
        confirmText: 'OK',
        cancelText: 'Cancelar',
        onConfirm: () => {
            pdisData.splice(idx, 1);
            renderizarPdis();
        }
    });
}


function adicionarPdi() {
    // Pré-seleciona o primeiro participante disponível
    const primeiro = PARTICIPANTES_LISTA[0] || {};
    pdisData.push({
        colaborador_id:   primeiro.id   || '',
        colaborador_nome: primeiro.name || 'Selecione',
        titulo: '',
        descricao: '',
        prazo: '',
        acoes: []
    });
    renderizarPdis();
}

// ==================== SALVAR TUDO ====================

async function salvarTudo() {
    const btn = document.getElementById('btn-salvar-final');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Salvando...';

    const transcricao = document.getElementById('transcricao-texto').value;
    const resumo      = document.getElementById('resumo-ia').value.trim();
    const sentimento  = document.getElementById('sentimento-select').value;

    if (!resumo) {
        mostrarErro('O resumo não pode estar vazio.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Salvar e Criar PDIs';
        return;
    }

    // Coleta participantes selecionados na etapa 2
    const participantes = Array.from(document.querySelectorAll('input[name="participante_check"]:checked')).map(c => c.value);

    // Coleta apenas os PDIs marcados como "Aprovado" pelo RH
    const pdisEditados = pdisData
        .map((pdi, idx) => {
            const aprovado = document.getElementById(`pdi-aprovado-${idx}`)?.checked ?? true;
            return {
                aprovado:       aprovado,
                colaborador_id: pdi.colaborador_id,
                titulo:         pdi.titulo,
                descricao:      pdi.descricao,
                prazo:          pdi.prazo || null,
                acoes:          (pdi.acoes || []).filter(a => a.titulo.trim() !== '')
            };
        })
        .filter(p => p.aprovado && p.titulo && p.colaborador_id);

    const body = new FormData();
    body.append('_token', CSRF_TOKEN);
    body.append('transcricao', transcricao);
    body.append('transcricao_original', transcricaoOriginalData);
    body.append('resumo', resumo);
    body.append('resumo_original', resumoOriginalData);
    body.append('sentimento', sentimento);
    participantes.forEach(id => body.append('participantes[]', id));
    pdisEditados.forEach((pdi, i) => {
        body.append(`pdis[${i}][colaborador_id]`, pdi.colaborador_id);
        body.append(`pdis[${i}][titulo]`, pdi.titulo);
        body.append(`pdis[${i}][descricao]`, pdi.descricao);
        if (pdi.prazo) body.append(`pdis[${i}][prazo]`, pdi.prazo);
        if (pdi.acoes) {
            pdi.acoes.forEach((acao, a) => {
                body.append(`pdis[${i}][acoes][${a}][titulo]`, acao.titulo);
                body.append(`pdis[${i}][acoes][${a}][descricao]`, acao.descricao || '');
            });
        }
    });

    try {
        const resp = await fetch(URL_SALVAR, { method: 'POST', body });
        const data = await resp.json();

        if (!resp.ok || !data.success) {
            mostrarErro(data.error || 'Erro ao salvar.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Salvar e Criar PDIs';
            return;
        }

        // Sucesso! Redireciona
        window.location.href = data.redirect;

    } catch (e) {
        mostrarErro('Falha de conexão ao salvar. Tente novamente.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Salvar e Criar PDIs';
    }
}

// ==================== UTILITÁRIOS ====================

function irParaEtapa(num) {
    document.querySelectorAll('.etapa-panel').forEach(p => p.style.display = 'none');
    document.getElementById(`etapa-${num}`).style.display = 'block';

    // Atualiza indicadores visuais
    for (let i = 1; i <= 4; i++) {
        const label = document.getElementById(`etapa-label-${i}`);
        if (!label) continue;
        const inner = label.querySelector('div');
        const circle = label.querySelector('span');
        if (i === num) {
            inner.style.background = 'var(--primary-color)';
            inner.style.color = '#fff';
            circle.style.background = 'rgba(255,255,255,0.3)';
        } else if (i < num) {
            inner.style.background = '#dcfce7';
            inner.style.color = '#15803d';
            circle.style.background = '#bbf7d0';
        } else {
            inner.style.background = '#f1f5f9';
            inner.style.color = '#94a3b8';
            circle.style.background = '#e2e8f0';
        }
    }

    document.getElementById('erro-global').style.display = 'none';
}

function mostrarErro(msg) {
    const el = document.getElementById('erro-global');
    el.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + escapeHtml(msg);
    el.style.display = 'block';
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function escapeHtml(text) {
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
</script>
@endpush
@endif
