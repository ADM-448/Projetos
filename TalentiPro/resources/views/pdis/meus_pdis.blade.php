@extends(in_array(auth()->user()->perfil_acesso, ['admin', 'master']) ? 'layouts.admin' : (auth()->user()->perfil_acesso === 'gestor' ? 'layouts.gestor' : 'layouts.colaborador'))

@section('title', 'Meu PDI')

@push('styles')
<style>
    .pdi-list-container {
        display: flex; flex-direction: column; gap: 20px; width: 100%; max-width: 900px; margin: 0 auto;
    }
    .pdi-item { 
        background: white; border-radius: 10px; padding: 25px; 
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03); border: 1px solid #e0e0e0;
    }
    .pdi-main-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; gap: 10px; }
    
    .progress-bg {
        flex: 1; height: 20px; background: #eee; border-radius: 10px; overflow: hidden; position: relative; margin: 0 20px;
    }
    .progress-fill {
        height: 100%; background: #27ae60; display: flex; align-items: center; justify-content: center;
        color: white; font-size: 10px; font-weight: bold; transition: width 0.3s;
    }
    .pdi-buttons-row { display: flex; justify-content: center; gap: 20px; margin-top: 10px; }
    .btn-pdi-blue {
        background-color: #3b5998; color: white; padding: 8px 30px; border-radius: 5px;
        border: none; font-weight: bold; cursor: pointer; text-decoration: none; font-size: 14px;
    }
    .btn-pdi-blue:hover { background-color: #2d4373; }

    /* Modal */
    .modal-overlay {
        display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(0, 0, 0, 0.5); z-index: 2000;
        justify-content: center; align-items: center; padding: 20px;
    }
    .modal-content {
        background-color: white; padding: 30px; border-radius: 10px;
        width: 800px; max-width: 95%; position: relative; animation: slideDown 0.3s;
        max-height: 90vh; overflow-y: auto;
    }
    .close-modal { position: absolute; top: 20px; right: 20px; font-size: 20px; cursor: pointer; color: #999; }
    
    .checklist-item {
        display: flex; align-items: center; gap: 10px; padding: 10px; border-bottom: 1px solid #f0f0f0;
    }
    .checklist-item input[type="checkbox"] { transform: scale(1.3); cursor: pointer; }
    .checklist-item.done span { text-decoration: line-through; color: #999; }
    
    @keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>
@endpush

@section('content')
<div class="page-content">
    <h1 style="margin-bottom: 20px; color: #333; text-align: center;">Meu Plano de Desenvolvimento Individual</h1>

    @if(session('success'))
        <div style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 5px; max-width: 900px; margin: 0 auto 20px auto;">
            {{ session('success') }}
        </div>
    @endif

    <div class="pdi-list-container">
        @forelse($pdis as $pdi)
            <div class="pdi-item">
                <div class="pdi-main-row">
                    <div style="font-weight: bold; min-width: 150px;">{{ $pdi->titulo }}</div>
                    
                    <div style="font-size: 13px; color: #666; min-width: 100px;">
                        Prazo: {{ $pdi->data_fim ? \Carbon\Carbon::parse($pdi->data_fim)->format('d/m/Y') : ($pdi->prazo ? \Carbon\Carbon::parse($pdi->prazo)->format('d/m/Y') : 'Vazio') }}
                    </div>

                    <div class="progress-bg">
                        <div class="progress-fill" style="width: {{ $pdi->progresso }}%;" id="prog-bar-{{ $pdi->id }}">
                            {{ $pdi->progresso }}%
                        </div>
                    </div>
                </div>
                
                <div class="pdi-buttons-row">
                    <button class="btn-pdi-blue" onclick="abrirModalPdi({{ json_encode($pdi) }}, {{ json_encode($pdi->acoes) }})">
                        Ver Detalhes & Ações
                    </button>
                </div>
            </div>
        @empty
            <p style="text-align: center; color: #666;">Você não possui nenhum PDI ativo.</p>
        @endforelse
    </div>
</div>

{{-- MODAL DETALHES --}}
<div id="modalPdi" class="modal-overlay">
    <div class="modal-content">
        <i class="fa-solid fa-xmark close-modal" onclick="fecharModal()"></i>
        
        <h2 id="modalTitle" style="margin-bottom: 10px; color: #333;">Título do PDI</h2>
        
        <div style="display: flex; gap: 20px; margin-bottom: 15px; font-size: 14px; color: #555; background: #f9f9f9; padding: 10px; border-radius: 5px;">
             <div><i class="fa-regular fa-calendar"></i> <strong>Início:</strong> <span id="modalDataInicio">-</span></div>
             <div><i class="fa-solid fa-flag-checkered"></i> <strong>Término:</strong> <span id="modalDataFim">-</span></div>
        </div>

        <h3 style="font-size: 16px; margin-bottom: 5px; color: #6a4c7c;">Descrição</h3>
        <p id="modalDesc" style="color: #666; margin-bottom: 20px; line-height: 1.5;">Descrição...</p>
        
        <h3 id="acoesTitle" style="font-size: 16px; margin-bottom: 10px; color: #6a4c7c;">Etapas / Ações</h3>
        <div id="acoesList" style="margin-bottom: 20px; border: 1px solid #eee; border-radius: 5px;">
            <!-- Renderizado via JS -->
        </div>

        <h3 style="font-size: 16px; margin-bottom: 10px; color: #6a4c7c;">Observações / Comentários</h3>
        <div id="obsListContainer" style="max-height: 150px; overflow-y: auto; margin-bottom: 10px; border: 1px solid #eee; padding: 10px; border-radius: 5px; background: #fafafa;">
            <!-- Renderizado via JS -->
        </div>
        <div style="display: flex; gap: 10px; margin-bottom: 30px;">
            <textarea id="newObsColab" placeholder="Adicione um comentário, dúvida ou feedback..." rows="2" style="flex: 1; border-radius: 5px; padding: 10px; border: 1px solid #ccc; font-family: inherit; font-size: 13px;"></textarea>
            <button class="btn-pdi-blue" onclick="salvarObsColab()" style="height: auto; white-space: nowrap;"><i class="fa-solid fa-paper-plane"></i> Enviar</button>
        </div>

        <h3 style="font-size: 16px; margin-bottom: 10px; color: #6a4c7c;">Comprovação de Conclusão</h3>
        <p style="font-size: 13px; color: #777; margin-bottom: 10px;">
            Envie um arquivo (PDF ou Imagem) para comprovar a realização deste PDI, se necessário.
        </p>

        <form id="formUpload" action="" method="POST" enctype="multipart/form-data" 
              style="background: #fdfdfd; padding: 20px; border: 2px dashed #3b5998; border-radius: 10px; text-align: center;">
            @csrf
            
            <div id="currentFileArea" style="margin-bottom: 15px; display: none;">
                <div style="font-size: 14px; margin-bottom: 5px; color: #555;">Documento atual:</div>
                <a href="#" id="linkFile" target="_blank" style="display: inline-block; padding: 6px 15px; background: #eef2ff; color: #3b5998; border-radius: 20px; font-weight: bold; text-decoration: none;">
                    <i class="fa-solid fa-file-pdf"></i> Visualizar Arquivo
                </a>
            </div>

            <div style="font-size: 14px; color: #666; margin-bottom: 15px; font-weight: bold;" id="uploadLabel">
                Clique abaixo para enviar o comprovante
            </div>

            <div style="display: flex; justify-content: center; gap: 10px; align-items: center; flex-direction: column;">
                <input type="file" name="comprovante" accept=".pdf,.jpg,.jpeg,.png" required style="border: 1px solid #ddd; padding: 8px; border-radius: 5px; background: white; width: 100%; max-width: 300px;">
                <button type="submit" class="btn-pdi-blue" style="width: 100%; max-width: 300px; padding: 10px; margin-top: 5px;">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Enviar Comprovante
                </button>
            </div>
            <small style="display:block; margin-top:10px; color:#999; font-size:11px;">(Formatos: PDF, JPG, PNG - Max: 10MB)</small>
        </form>

    </div>
</div>

@endsection

@push('scripts')
<script>
    function fecharModal() {
        document.getElementById('modalPdi').style.display = 'none';
    }

    function formatDate(dateStr) {
        if(!dateStr) return '-';
        const [y, m, d] = dateStr.split('-');
        return `${d}/${m}/${y}`;
    }

    let currentPdiId = null;

    function abrirModalPdi(pdi, acoes) {
        currentPdiId = pdi.id;
        document.getElementById('modalPdi').style.display = 'flex';
        document.getElementById('modalTitle').innerText = pdi.titulo;
        
        document.getElementById('modalDataInicio').innerText = formatDate(pdi.data_inicio);
        document.getElementById('modalDataFim').innerText = formatDate(pdi.data_fim || pdi.prazo);

        document.getElementById('modalDesc').innerText = pdi.descricao || 'Sem descrição.';
        
        // Renderizar Ações
        const list = document.getElementById('acoesList');
        list.innerHTML = '';
        
        if (acoes.length === 0) {
            document.getElementById('acoesTitle').style.display = 'none';
            list.style.display = 'none';
        } else {
            document.getElementById('acoesTitle').style.display = 'block';
            list.style.display = 'block';
            acoes.forEach(acao => {
                const isDone = acao.status === 'concluido';
                const div = document.createElement('div');
                div.className = 'checklist-item' + (isDone ? ' done' : '');
                div.id = `acao-row-${acao.id}`;
                
                div.innerHTML = `
                    <input type="checkbox" ${isDone ? 'checked' : ''} onchange="toggleAcao(${acao.id}, ${pdi.id})">
                    <span>${acao.titulo}</span>
                `;
                list.appendChild(div);
            });
        }

        // Renderizar Comentários
        const obsList = document.getElementById('obsListContainer');
        obsList.innerHTML = '';
        if (pdi.comentarios && pdi.comentarios.length > 0) {
            pdi.comentarios.forEach(c => {
                const userName = c.user ? c.user.name : 'Desconhecido';
                const date = new Date(c.created_at).toLocaleDateString('pt-BR');
                obsList.innerHTML += `
                    <div style="margin-bottom:10px; padding:10px; background:#fff; border-radius:5px; font-size:13px; border: 1px solid #eaeaea;">
                        <div style="font-weight:bold; margin-bottom:5px; color:#3b5998;">
                            <i class="fa-solid fa-user-circle"></i> ${userName} <span style="color:#999; font-size:11px; font-weight:normal; float:right;">${date}</span>
                        </div>
                        <div style="color:#555;">${c.comentario}</div>
                    </div>
                `;
            });
            // Auto scroll to bottom
            obsList.scrollTop = obsList.scrollHeight;
        } else {
            obsList.innerHTML = '<p style="color:#999; font-size:13px; text-align:center;">Nenhum comentário ainda.</p>';
        }

        // Limpa textarea
        document.getElementById('newObsColab').value = '';

        // Configurar Upload
        const form = document.getElementById('formUpload');
        // Rota: POST /pdis/{id}/comprovante
        // Vou criar essa rota. Preciso usar URL helper do Blade no JS?
        // Sim, ou gerar dinamicamente.
        let uploadUrl = "{{ route('pdis.upload', ':id') }}";
        uploadUrl = uploadUrl.replace(':id', pdi.id);
        form.action = uploadUrl;

        // Mostrar arquivo atual se existir
        const fileArea = document.getElementById('currentFileArea');
        const linkFile = document.getElementById('linkFile');
        
        if (pdi.comprovante) {
            fileArea.style.display = 'block';
            // Usa rota de download do Laravel para forçar o download do arquivo
            let downloadUrl = "{{ route('pdis.download', ':id') }}";
            linkFile.href = downloadUrl.replace(':id', pdi.id);
        } else {
            fileArea.style.display = 'none';
        }
    }

    // Toggle Action AJAX
    async function toggleAcao(acaoId, pdiId) {
        try {
            // Rota: POST /pdis/acao/{id}/toggle
            // Já existe no web.php
            let url = "{{ route('pdis.acao.toggle', ':id') }}";
            url = url.replace(':id', acaoId);

            const resp = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await resp.json();
            
            if (data.success) {
                // Atualizar visual do modal
                const row = document.getElementById(`acao-row-${acaoId}`);
                const checkbox = row.querySelector('input');
                if (checkbox.checked) {
                    row.classList.add('done');
                } else {
                    row.classList.remove('done');
                }
                
                // Atualizar barra de progresso na lista principal (se o PDI ID bater)
                const bar = document.getElementById(`prog-bar-${pdiId}`);
                if (bar) {
                    bar.style.width = data.progresso + '%';
                    bar.innerText = data.progresso + '%';
                }

            } else {
                alert('Erro ao atualizar ação.');
            }

        } catch (error) {
            console.error(error);
            alert('Erro de conexão.');
        }
    }

    window.onclick = function (event) {
        if (event.target == document.getElementById('modalPdi')) {
            fecharModal();
        }
    }

    async function salvarObsColab() {
        if(!currentPdiId) return;
        const txt = document.getElementById('newObsColab').value.trim();
        if(!txt) {
            alert('Digite um comentário antes de enviar.');
            return;
        }

        try {
            let url = "{{ url('/pdis') }}/" + currentPdiId + "/comentar";
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ comentario: txt })
            });

            const data = await res.json();
            if(data.success) {
                // Atualiza a página para carregar o novo comentário
                location.reload(); 
            } else {
                alert('Erro ao salvar comentário.');
            }
        } catch(e) { 
            console.error(e); 
            alert('Falha na comunicação com o servidor.');
        }
    }
</script>
@endpush
