{{-- Modal de Revisão do PDI Gerado por IA --}}
<div id="modalPdiIa" style="display: none; position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.5); align-items: center; justify-content: center; z-index: 9999; backdrop-filter: blur(4px);">
    <div style="background: white; border-radius: 12px; width: 90%; max-width: 800px; max-height: 90vh; overflow-y: auto; padding: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px;">
            <h2 style="margin: 0; color: #2d3748; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-wand-magic-sparkles" style="color: #6b46c1;"></i> Revisar PDI Sugerido
            </h2>
            <button type="button" onclick="fecharModalPdiIa()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #a0aec0;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="formPdiIa" action="{{ route('pdis.store') }}" method="POST">
            @csrf
            <input type="hidden" name="tipo_criacao" value="individual">
            <input type="hidden" name="gerado_ia" value="1">
            {{-- colaborador_id será setado via JS --}}
            <input type="hidden" name="colaborador_id" id="ai_colaborador_id">

            <div class="form-row">
                <div class="form-group" style="flex: 2;">
                    <label class="form-label" for="ai_titulo">Título do PDI <span style="color: red;">*</span></label>
                    <input type="text" id="ai_titulo" name="titulo" class="form-control" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label class="form-label" for="ai_categoria">Categoria</label>
                    <select id="ai_categoria" name="categoria" class="form-control">
                        <option value="Técnico">Técnico (Hard Skills)</option>
                        <option value="Comportamental">Comportamental (Soft Skills)</option>
                        <option value="Liderança">Liderança / Gestão</option>
                        <option value="Outros">Outros</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="ai_data_inicio">Data de Início</label>
                    <input type="date" id="ai_data_inicio" name="data_inicio" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="ai_data_fim">Data Limite de Término</label>
                    <input type="date" id="ai_data_fim" name="data_fim" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="ai_descricao">Objetivo Geral do PDI</label>
                <textarea id="ai_descricao" name="descricao" class="form-control" rows="3"></textarea>
            </div>

            <div style="border-top: 1px solid #e2e8f0; margin-top: 20px; padding-top: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 style="margin: 0; color: #4a5568; font-size: 1.1rem;">Ações e Metas Específicas <span style="color: red;">*</span></h3>
                    <button type="button" class="btn-secondary" onclick="adicionarAcaoIa()" style="padding: 6px 12px; font-size: 0.85rem;">
                        <i class="fa-solid fa-plus"></i> Adicionar
                    </button>
                </div>

                <div id="ai_acoes_container" style="display: flex; flex-direction: column; gap: 10px;">
                    {{-- Ações serão injetadas via JS --}}
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px;">
                <button type="button" onclick="fecharModalPdiIa()" class="btn-secondary">Cancelar</button>
                <button type="submit" class="btn-primary" style="background: #6b46c1; border: none; padding: 12px 25px;">
                    <i class="fa-solid fa-floppy-disk"></i> Salvar PDI
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let aiAcaoIndex = 0;

    function abrirModalPdiIa(dados, colaboradorId, titleDefault = null) {
        document.getElementById('modalPdiIa').style.display = 'flex';
        
        document.getElementById('ai_colaborador_id').value = colaboradorId;
        document.getElementById('ai_titulo').value = dados.titulo || titleDefault || 'Plano de Ação Sugerido';
        document.getElementById('ai_descricao').value = dados.descricao || '';
        document.getElementById('ai_data_fim').value = dados.prazo || '';
        
        const acoesContainer = document.getElementById('ai_acoes_container');
        acoesContainer.innerHTML = '';
        aiAcaoIndex = 0;
        
        if (dados.acoes && Array.isArray(dados.acoes) && dados.acoes.length > 0) {
            dados.acoes.forEach(acao => {
                adicionarAcaoIa(acao.titulo, acao.descricao);
            });
        } else {
            adicionarAcaoIa('', '');
        }
    }

    function fecharModalPdiIa() {
        document.getElementById('modalPdiIa').style.display = 'none';
    }

    function adicionarAcaoIa(titulo = '', descricao = '') {
        const container = document.getElementById('ai_acoes_container');
        const div = document.createElement('div');
        div.className = 'form-row';
        div.style.background = '#f7fafc';
        div.style.padding = '15px';
        div.style.borderRadius = '8px';
        div.style.border = '1px solid #e2e8f0';
        div.style.alignItems = 'flex-start';
        div.style.marginBottom = '0';
        
        div.innerHTML = `
            <div style="display: flex; flex-direction: column; gap: 10px; flex: 1;">
                <div>
                    <label class="form-label" style="font-size: 0.85rem; font-weight: bold;">Título da Ação/Meta <span style="color: red;">*</span></label>
                    <input type="text" name="acoes[${aiAcaoIndex}][titulo]" class="form-control" value="${titulo.replace(/"/g, '&quot;')}" required>
                </div>
                <div>
                    <label class="form-label" style="font-size: 0.85rem;">Como realizar / Critério de sucesso</label>
                    <textarea name="acoes[${aiAcaoIndex}][descricao]" class="form-control" rows="2">${descricao.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</textarea>
                </div>
            </div>
            <button type="button" class="btn-danger" style="margin-left: 15px; padding: 10px;" onclick="this.parentElement.remove()">
                <i class="fa-solid fa-trash"></i>
            </button>
        `;
        
        container.appendChild(div);
        aiAcaoIndex++;
    }
</script>
