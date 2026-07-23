@extends(in_array(auth()->user()->perfil_acesso, ['admin', 'master']) ? 'layouts.admin' : 'layouts.gestor')
@section('title', 'Nova Reunião Inteligente (IA)')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .form-card { background: white; border-radius: 10px; padding: 40px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: 1px solid #e0e0e0; max-width: 900px; margin: 0 auto; margin-bottom: 20px; }
    .form-group { margin-bottom: 20px; }
    label { font-weight: 600; color: #444; margin-bottom: 8px; display: block; font-size: 14px; }
    
    input[type="text"], input[type="date"], select, textarea { 
        width: 100%; padding: 10px 12px; border-radius: 5px; border: 1px solid #ccc; 
        font-size: 14px; outline: none; background-color: #f9f9f9; transition: border-color 0.2s;
    }
    input[type="text"]:focus, input[type="date"]:focus, select:focus, textarea:focus { border-color: #3b5998; }
    
    .btn-action { background-color: #3b5998; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; font-weight: bold; transition:0.3s; display: inline-flex; align-items: center; gap: 8px; }
    .btn-action:hover { background-color: #2d4373; }
    .btn-action:disabled { opacity: 0.6; cursor: not-allowed; }
    
    .btn-success { background-color: #28a745; color: white; padding: 14px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; transition:0.3s; width: 100%; text-transform: uppercase; letter-spacing: 1px; }
    .btn-success:hover { background-color: #218838; }
    
    .btn-secondary { background-color: #6c757d; color: white; }
    .btn-secondary:hover { background-color: #5a6268; }

    .btn-back { background-color: #3b5998; color: white; padding: 8px 25px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; font-weight: bold; font-size: 14px; }
    
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; max-width: 900px; margin-left: auto; margin-right: auto; }
    .page-title { font-size: 24px; font-weight: bold; color: #333; margin: 0; }

    .step-title { font-size: 18px; color: #3b5998; margin-top: 0; margin-bottom: 25px; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; }

    .pdi-card { border: 1px solid #e0e0e0; padding: 25px; margin-bottom: 15px; border-radius: 8px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.02); position: relative; border-left: 4px solid #ffc107; }
    .remove-pdi { position: absolute; top: 15px; right: 15px; color: #dc3545; cursor: pointer; font-weight: bold; background: rgba(220,53,69,0.1); border: none; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
    .remove-pdi:hover { background: #dc3545; color: white; }

    .participants-box { max-height: 200px; overflow-y: auto; border: 1px solid #ccc; padding: 15px; border-radius: 5px; background: #f9f9f9; }
    .checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; margin-bottom: 10px; font-size: 14px; color: #444; }
    .checkbox-label input { width: auto; margin: 0; transform: scale(1.1); }

    .drive-box { background-color: #e3f2fd; border-left: 4px solid #2196f3; padding: 20px; border-radius: 4px; margin-bottom: 20px; }
    .drive-box label { color: #0d47a1; margin-bottom: 5px; font-size: 15px; }
    .drive-box p { font-size: 13px; color: #444; margin: 0 0 15px 0; }

    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    .spinner { margin: 30px auto; border: 4px solid #f3f3f3; border-top: 4px solid #3b5998; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; }
</style>
@endpush

@section('content')

<div class="page-content" style="padding: 40px; padding-top: 20px;">
    <div class="header-actions">
        <h1 class="page-title">Revisão e Geração da Reunião (IA)</h1>
        <a href="{{ route('reunioes.index') }}" class="btn-back">Voltar</a>
    </div>
    
    <!-- PASSO 1: Preparação da Transcrição / Gravação -->
    <div class="form-card" id="step1">
        <h3 class="step-title">1. Dados da Reunião</h3>
        
        <div class="form-group">
            <label>Título da Reunião</label>
            <input type="text" id="titulo" value="{{ $agendamento->titulo ?? '' }}" placeholder="Ex: Feedback Mensal - João Silva">
        </div>
        
        <div class="form-group">
            <label>Participantes Presentes</label>
            <div class="participants-box">
                @foreach($users as $u)
                    @if($u->id !== auth()->id())
                    <label class="checkbox-label">
                        <input type="checkbox" name="participantes" value="{{ $u->id }}" 
                            {{ isset($agendamento) && $agendamento->colaborador_id == $u->id ? 'checked' : '' }}> 
                        {{ $u->name }} ({{ $u->cargo->nome ?? 'Sem cargo' }})
                    </label>
                    @endif
                @endforeach
            </div>
        </div>

        <h3 class="step-title" style="margin-top: 40px;">2. Como deseja capturar a reunião?</h3>

        <!-- OPÇÃO A: GRAVAR ÁUDIO NATIVO -->
        <div class="drive-box" style="background-color: #fff3e0; border-left-color: #ff9800; margin-bottom: 30px;">
            <label style="color: #e65100;"><i class="fa-solid fa-microphone"></i> Gravar Reunião Agora (Áudio)</label>
            <p>Grave o áudio da sua reunião diretamente pelo navegador. Nós transcreveremos tudo usando Inteligência Artificial e salvaremos o arquivo de áudio automaticamente no seu Google Drive!</p>
            
            @if(!auth()->user()->google_access_token)
                <div style="padding: 15px; background: #ffebee; border: 1px solid #ffcdd2; border-radius: 5px; color: #c62828;">
                    <strong>Google Drive Não Vinculado!</strong><br>
                    Para usar a gravação nativa e salvar arquivos na sua nuvem, você precisa vincular sua conta do Google.
                    <br><br>
                    <a href="{{ route('google.redirect') }}" class="btn-action" style="background: #db4437;"><i class="fa-brands fa-google"></i> Vincular Google Drive</a>
                </div>
            @else
                <div style="padding: 15px; background: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 5px; margin-bottom: 15px; color: #2e7d32; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong><i class="fa-brands fa-google-drive"></i> Google Drive Vinculado</strong><br>
                        O áudio será salvo na sua conta automaticamente.
                    </div>
                    <form action="{{ route('google.unlink') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn-action btn-secondary" style="font-size: 11px; padding: 5px 10px;">Desvincular</button>
                    </form>
                </div>
                
                <div id="recorder-ui" style="display: flex; gap: 10px; align-items: center; background: white; padding: 20px; border-radius: 8px; border: 1px solid #ccc; flex-wrap: wrap;">
                    <div style="width: 100%; border-bottom: 1px dashed #ddd; padding-bottom: 15px; margin-bottom: 5px;">
                        <h4 style="margin: 0 0 10px 0; color: #555;">Opção 1: Gravar com o Microfone</h4>
                        <button type="button" id="btnStartRecord" class="btn-action" style="background: #dc3545;"><i class="fa-solid fa-circle"></i> Iniciar Gravação</button>
                        <button type="button" id="btnStopRecord" class="btn-action" style="background: #6c757d; display: none;"><i class="fa-solid fa-square"></i> Parar Gravação</button>
                        <span id="recordingTimer" style="font-size: 18px; font-weight: bold; color: #dc3545; display: none; font-family: monospace; margin-left: 10px;">00:00</span>
                        <audio id="audioPreview" controls style="display: none; height: 40px; margin-top: 10px;"></audio>
                    </div>

                    <div style="width: 100%; padding-top: 5px;">
                        <h4 style="margin: 0 0 10px 0; color: #555;">Opção 2: Fazer Upload de Arquivo (Áudio ou Vídeo)</h4>
                        <input type="file" id="audioUploadFile" accept="audio/*,video/*" style="border: 1px solid #ccc; padding: 5px; border-radius: 5px;">
                        <button type="button" id="btnUploadAudio" class="btn-action" style="background: #17a2b8; margin-top: 10px;"><i class="fa-solid fa-cloud-arrow-up"></i> Processar Arquivo</button>
                    </div>
                </div>
                <input type="hidden" id="audioBlobInfo" value="">
            @endif
        </div>

        <!-- OPÇÃO B: GOOGLE DOCS -->
        <div class="drive-box">
            <label><i class="fa-solid fa-file-lines"></i> Já tenho a transcrição (Google Docs / Texto)</label>
            <p>Se a reunião foi feita no Google Meet e ele já gerou o documento de transcrição, cole o link abaixo. Ou cole o texto manualmente na caixa de texto.</p>
            <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                <input type="text" id="drive_url" placeholder="Cole o link do Google Docs aqui..." style="flex: 1; border-color: #90caf9;">
                <button type="button" class="btn-action" style="background: #2196f3;" onclick="extrairDrive()" id="btnExtrair">Extrair Texto</button>
            </div>
            <textarea id="transcricaoTexto" rows="6" placeholder="Aguardando texto da transcrição ou cole manualmente aqui...">{{ $transcricao ?? '' }}</textarea>
        </div>

        <input type="hidden" id="agendamento_id" value="{{ $agendamento->id ?? '' }}">

        <div style="display: flex; gap: 15px; margin-top: 30px;">
            <button type="button" class="btn-action" onclick="processarIA()" id="btnGerarIA"><i class="fa-solid fa-wand-magic-sparkles"></i> Gerar Resumo e PDIs com IA</button>
            <button type="button" class="btn-action btn-secondary" onclick="pularIA()">Pular IA (Preencher Manualmente)</button>
        </div>
    </div>

    <!-- PASSO 2: Processando IA -->
    <div class="form-card" id="step2" style="display: none; text-align: center; padding: 60px 20px;">
        <h3 class="step-title" style="border: none; justify-content: center;">2. Processando Inteligência Artificial...</h3>
        <p style="color: #666; font-size: 15px;">Enviando dados para o Google Gemini. A IA está lendo a transcrição, resumindo o que foi discutido e separando as tarefas e prazos automaticamente.</p>
        <div class="spinner"></div>
    </div>

    <!-- PASSO 3: Revisão Final -->
    <div class="form-card" id="step3" style="display: none;">
        <h3 class="step-title">3. Revisão Final</h3>
        
        <div class="form-group">
            <label>Resumo da Reunião (Editável)</label>
            <textarea id="resumo" style="height: 120px; line-height: 1.5;"></textarea>
        </div>

        <div class="form-group" style="margin-top: 30px;">
            <label style="font-size: 16px; color: #3b5998; border-bottom: 1px solid #eee; padding-bottom: 8px; margin-bottom: 15px;">Ações Estratégicas / PDIs Gerados</label>
            <div id="pdisContainer"></div>
            <button class="btn-action btn-secondary" style="font-size: 12px; padding: 8px 15px; margin-top: 10px;" onclick="addEmptyPdi()">+ Adicionar Tarefa Manualmente</button>
        </div>

        <hr style="margin: 40px 0; border: 0; border-top: 1px solid #eaeaea;">
        <button class="btn-success" onclick="salvarTudo()"><i class="fa-solid fa-check"></i> Aprovar e Salvar Reunião</button>
    </div>
</div>

<script>
    let generatedPdis = [];
    let participantes = [];

    async function extrairDrive() {
        const url = document.getElementById('drive_url').value;
        if(!url) return alert('Cole o link do Google Docs.');
        
        const btn = document.getElementById('btnExtrair');
        const oldText = btn.innerText;
        btn.innerText = 'Extraindo...';
        btn.disabled = true;

        try {
            const res = await fetch('{{ route("reunioes.fetch_transcript") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ drive_url: url })
            });
            const data = await res.json();
            
            if(data.success) {
                document.getElementById('transcricaoTexto').value = data.texto;
                alert('Transcrição extraída com sucesso!');
            } else {
                alert('Erro ao extrair: ' + data.error);
            }
        } catch(e) {
            alert('Erro de rede ao conectar com o Google Drive.');
        } finally {
            btn.innerText = oldText;
            btn.disabled = false;
        }
    }

    async function processarIA() {
        const titulo = document.getElementById('titulo').value;
        const cbParticipantes = document.querySelectorAll('input[name="participantes"]:checked');
        const transcricao = document.getElementById('transcricaoTexto').value;
        
        if(!titulo) return alert('Digite um título para a reunião.');
        if(cbParticipantes.length === 0) return alert('Selecione pelo menos um participante.');
        if(transcricao.trim() === '') return alert('A transcrição está vazia. Digite algo ou clique em "Pular IA" se quiser fazer tudo manual.');

        participantes = Array.from(cbParticipantes).map(cb => cb.value);

        document.getElementById('step1').style.display = 'none';
        document.getElementById('step2').style.display = 'block';

        try {
            const res = await fetch('{{ route("reunioes.processar") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    transcricao: transcricao,
                    participantes: participantes
                })
            });
            
            const data = await res.json();
            
            if(data.error) throw new Error(data.error);
            
            document.getElementById('step2').style.display = 'none';
            document.getElementById('step3').style.display = 'block';
            
            document.getElementById('resumo').value = data.resumo || '';
            
            generatedPdis = data.pdis || [];
            renderPdis();
            
        } catch(err) {
            alert('Erro ao processar IA: ' + err.message);
            pularIA();
        }
    }

    function pularIA() {
        const cbParticipantes = document.querySelectorAll('input[name="participantes"]:checked');
        if(cbParticipantes.length === 0) return alert('Selecione pelo menos um participante.');
        participantes = Array.from(cbParticipantes).map(cb => cb.value);

        document.getElementById('step1').style.display = 'none';
        document.getElementById('step2').style.display = 'none';
        document.getElementById('step3').style.display = 'block';
        renderPdis();
    }

    function renderPdis() {
        const container = document.getElementById('pdisContainer');
        container.innerHTML = '';
        
        if(generatedPdis.length === 0) {
            container.innerHTML = '<p>Nenhuma tarefa gerada.</p>';
            return;
        }

        let optionsHtml = '';
        document.querySelectorAll('input[name="participantes"]:checked').forEach(cb => {
            optionsHtml += `<option value="${cb.value}">${cb.nextSibling.textContent.trim()}</option>`;
        });

        generatedPdis.forEach((pdi, index) => {
            const html = `
                <div class="pdi-card" id="pdi-${index}">
                    <button type="button" class="remove-pdi" onclick="removerPdi(${index})" title="Remover Tarefa"><i class="fa-solid fa-xmark"></i></button>
                    
                    <div class="form-row" style="display: flex; gap: 20px; margin-bottom: 15px;">
                        <div class="form-group" style="flex: 1; margin-bottom: 0;">
                            <label>Colaborador Responsável</label>
                            <select class="form-control pdi-colaborador">
                                ${optionsHtml}
                            </select>
                        </div>
                        <div class="form-group" style="flex: 1; margin-bottom: 0;">
                            <label>Prazo</label>
                            <input type="date" class="form-control pdi-prazo" value="${pdi.prazo || ''}">
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Título da Tarefa / Ação</label>
                        <input type="text" class="form-control pdi-titulo" value="${pdi.titulo || ''}" placeholder="Ex: Treinamento de Comunicação">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Descrição</label>
                        <textarea class="form-control pdi-descricao" rows="3" placeholder="Descreva o que deve ser feito...">${pdi.descricao || ''}</textarea>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            if(pdi.colaborador_id) {
                document.querySelector(`#pdi-${index} .pdi-colaborador`).value = pdi.colaborador_id;
            }
        });
    }

    function removerPdi(index) {
        generatedPdis.splice(index, 1);
        renderPdis();
    }
    
    function addEmptyPdi() {
        generatedPdis.push({
            colaborador_id: participantes[0] || '',
            titulo: '',
            descricao: '',
            prazo: ''
        });
        renderPdis();
    }

    async function salvarTudo() {
        const formData = new FormData();
        formData.append('titulo', document.getElementById('titulo').value);
        formData.append('resumo', document.getElementById('resumo').value);
        formData.append('transcricao', document.getElementById('transcricaoTexto').value);
        formData.append('agendamento_id', document.getElementById('agendamento_id').value);
        
        participantes.forEach(p => formData.append('participantes[]', p));
        
        const pdiCards = document.querySelectorAll('.pdi-card');
        pdiCards.forEach((card, index) => {
            formData.append(`pdis[${index}][colaborador_id]`, card.querySelector('.pdi-colaborador').value);
            formData.append(`pdis[${index}][titulo]`, card.querySelector('.pdi-titulo').value);
            formData.append(`pdis[${index}][descricao]`, card.querySelector('.pdi-descricao').value);
            formData.append(`pdis[${index}][prazo]`, card.querySelector('.pdi-prazo').value);
        });

        formData.append('_token', '{{ csrf_token() }}');

        try {
            const res = await fetch('{{ route("reunioes.store") }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json'
                },
                body: formData
            });
            const data = await res.json();
            
            if(res.ok && data.success) {
                window.location.href = data.redirect;
            } else {
                console.error("Erro do servidor:", data);
                alert('Erro ao salvar: ' + (data.message || JSON.stringify(data.errors) || 'Desconhecido'));
            }
        } catch(err) {
            console.error("Erro do JS:", err);
            alert('Erro de conexão ao salvar. Verifique o console.');
        }
    }

    // LÓGICA DE GRAVAÇÃO DE ÁUDIO (MediaRecorder)
    let mediaRecorder;
    let audioChunks = [];
    let recordInterval;
    let secondsRecorded = 0;
    
    const btnStartRecord = document.getElementById('btnStartRecord');
    const btnStopRecord = document.getElementById('btnStopRecord');
    const recordingTimer = document.getElementById('recordingTimer');
    const audioPreview = document.getElementById('audioPreview');

    if (btnStartRecord) {
        btnStartRecord.addEventListener('click', async () => {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                mediaRecorder = new MediaRecorder(stream);
                audioChunks = [];
                
                mediaRecorder.ondataavailable = event => {
                    if (event.data.size > 0) audioChunks.push(event.data);
                };
                
                mediaRecorder.onstop = async () => {
                    const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                    const audioUrl = URL.createObjectURL(audioBlob);
                    audioPreview.src = audioUrl;
                    audioPreview.style.display = 'block';
                    
                    // Fazer upload automático
                    await uploadAudio(audioBlob);
                };
                
                mediaRecorder.start();
                
                btnStartRecord.style.display = 'none';
                btnStopRecord.style.display = 'inline-block';
                recordingTimer.style.display = 'inline-block';
                
                secondsRecorded = 0;
                recordingTimer.textContent = '00:00';
                recordingTimer.style.color = '#dc3545';
                recordInterval = setInterval(() => {
                    secondsRecorded++;
                    const m = String(Math.floor(secondsRecorded / 60)).padStart(2, '0');
                    const s = String(secondsRecorded % 60).padStart(2, '0');
                    recordingTimer.textContent = `${m}:${s}`;
                }, 1000);
                
            } catch (err) {
                alert("Erro ao acessar microfone. Verifique as permissões do navegador.");
            }
        });
        
        btnStopRecord.addEventListener('click', () => {
            mediaRecorder.stop();
            mediaRecorder.stream.getTracks().forEach(track => track.stop());
            clearInterval(recordInterval);
            
            btnStartRecord.style.display = 'none'; 
            btnStopRecord.style.display = 'none';
            recordingTimer.style.color = '#28a745'; // verde de concluído
        });
    }

    const btnUploadAudio = document.getElementById('btnUploadAudio');
    const audioUploadFile = document.getElementById('audioUploadFile');

    if (btnUploadAudio && audioUploadFile) {
        btnUploadAudio.addEventListener('click', async () => {
            if (audioUploadFile.files.length === 0) {
                alert('Por favor, selecione um arquivo de áudio ou vídeo primeiro!');
                return;
            }
            const file = audioUploadFile.files[0];
            await uploadAudio(file);
        });
    }

    async function uploadAudio(audioBlob) {
        const transcricaoField = document.getElementById('transcricaoTexto');
        const btnGerarIA = document.getElementById('btnGerarIA');
        
        transcricaoField.value = "⏳ Fazendo upload do áudio para o seu Google Drive e gerando a transcrição via IA... Aguarde (pode demorar alguns minutos dependendo do tamanho da reunião)...";
        btnGerarIA.disabled = true;

        const formData = new FormData();
        formData.append('audio', audioBlob, 'reuniao.webm');
        formData.append('titulo', document.getElementById('titulo').value || 'Reunião de Feedback');
        formData.append('_token', '{{ csrf_token() }}');

        try {
            const response = await fetch('{{ route("reunioes.upload_audio") }}', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                transcricaoField.value = data.transcricao;
                Swal.fire({
                    title: 'Mágica feita! 🪄',
                    text: 'Arquivo salvo no seu Google Drive e transcrito com sucesso! Agora você pode Gerar o Resumo com IA.',
                    icon: 'success',
                    confirmButtonColor: '#3b5998'
                });
                btnGerarIA.disabled = false;
            } else {
                transcricaoField.value = "";
                btnGerarIA.disabled = false;
                Swal.fire({
                    title: 'Ops! Algo deu errado',
                    text: data.error,
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            }
        } catch (e) {
            console.error(e);
            Swal.fire({
                title: 'Erro!',
                text: 'Erro de conexão ao enviar áudio para o servidor.',
                icon: 'error'
            });
            transcricaoField.value = "";
            btnGerarIA.disabled = false;
        }
    }
</script>
@endsection
