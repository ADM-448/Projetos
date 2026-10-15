@extends('layouts.app')

@section('title', 'Competências e Skills')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title">Competências</h1>
        <p style="color: #666; font-size: 0.95rem;">Gerencie as competências e suas respectivas skills de avaliação.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button onclick="abrirModalImportar()" class="btn-secondary" style="background-color: #27ae60; color: white; border-color: #27ae60;">
            <i class="fa-solid fa-file-excel"></i> Importar via Planilha
        </button>
        <button class="btn-primary" onclick="abrirModalNova()">
            <i class="fa-solid fa-plus"></i> Nova Competência
        </button>
    </div>
</div>

@if (session('success'))
    <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="unified-card">
    <div class="table-responsive">
        <table class="custom-table table-cards-mobile" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background-color: #f8fafc; border-bottom: 2px solid #6a4c7c;">
                    <th style="width: 25%; padding: 12px 15px; font-weight: bold; color: #333; text-align: left;">Competência</th>
                    <th style="width: 30%; padding: 12px 15px; font-weight: bold; color: #333; text-align: left;">Descrição</th>
                    <th style="width: 30%; padding: 12px 15px; font-weight: bold; color: #333; text-align: left;">Skills</th>
                    <th style="width: 15%; padding: 12px 15px; font-weight: bold; color: #333; text-align: right;">Ações</th>
                </tr>
            </thead>
        <tbody>
            @forelse($competencias as $competencia)
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 15px; font-weight: bold; color: #2d3748; text-align: left;" data-label="Competência">{{ $competencia->nome }}</td>
                <td style="padding: 15px; color: #64748b; font-size: 0.9rem; text-align: left;" data-label="Descrição">{{ $competencia->descricao ?? '-' }}</td>
                <td style="padding: 15px; text-align: left;" data-label="Skills">
                    <div style="display: flex; gap: 5px; flex-wrap: wrap; justify-content: flex-end; width: 100%;">
                        @if($competencia->skills->count() > 0)
                            @foreach($competencia->skills->take(2) as $skill)
                                <span style="background: #edf2f7; color: #4a5568; border: 1px solid #cbd5e1; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 500; white-space: normal; word-break: break-word; display: inline-block; line-height: 1.4; text-align: right; max-width: 100%;">
                                    {{ $skill->nome }}
                                </span>
                            @endforeach
                            @if($competencia->skills->count() > 2)
                                <span style="background: #6a4c7c; color: white; padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; cursor: help;" title="E mais {{ $competencia->skills->count() - 2 }} skills">
                                    +{{ $competencia->skills->count() - 2 }}
                                </span>
                            @endif
                        @else
                            <span style="color: #999; font-size: 0.85rem; font-style: italic;">Nenhuma skill</span>
                        @endif
                    </div>
                </td>
                <td style="padding: 15px; text-align: right;" data-label="Ações">
                    <div style="display: inline-flex; gap: 8px;">
                        <button onclick="abrirModalEditar({{ $competencia->id }}, '{{ addslashes($competencia->nome) }}', '{{ addslashes($competencia->descricao) }}', {{ json_encode($competencia->skills) }})" class="btn-secondary" style="padding: 8px 12px; font-size: 0.9rem;" title="Editar">
                            <i class="fa-solid fa-pen"></i>
                        </button>

                        <button class="btn-secondary" style="padding: 8px 12px; font-size: 0.9rem; color: #e53e3e;" onclick="abrirModalExcluir({{ $competencia->id }}, '{{ addslashes($competencia->nome) }}')" title="Excluir">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align:center; padding: 30px; color:#666;">
                    Nenhuma competência cadastrada. <br>
                    <button class="btn-primary" style="margin-top: 15px;" onclick="abrirModalNova()">Criar a primeira</button>
                </td>
            </tr>
            @endforelse
        </tbody>
        </table>
    </div>
</div>

<!-- Modal Nova / Editar Competencia -->
<div id="modalCompetencia" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div class="modal-box" style="background: white; border-radius: 12px; padding: 30px; width: 100%; max-width: 550px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 id="modalCompetenciaTitle" style="margin: 0; font-size: 1.25rem;">Nova Competência</h2>
            <button type="button" onclick="fecharModalCompetencia()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #666;">&times;</button>
        </div>
        
        <form id="formCompetencia" action="{{ route('competencias.store') }}" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formCompetenciaMethod" value="POST">
            
            <div class="form-group">
                <label class="form-label" for="nome">Nome da Competência <span style="color: red;">*</span></label>
                <input type="text" id="nome" name="nome" class="form-control" required placeholder="Ex: Liderança">
            </div>

            <div class="form-group">
                <label class="form-label" for="descricao">Descrição</label>
                <textarea id="descricao" name="descricao" class="form-control" rows="2" placeholder="Descreva brevemente esta competência..."></textarea>
            </div>

            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

            <div class="form-group">
                <label class="form-label">Skills Relacionadas</label>
                <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                    <input type="text" id="novaSkillTemp" class="form-control" placeholder="Digite uma skill (Ex: Escuta ativa)">
                    <button type="button" class="btn-secondary" id="btnAdicionarSkill">Adicionar</button>
                </div>
                
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; min-height: 80px;">
                    <ul id="skillsListDOM" style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                        <!-- Skills inseridas dinamicamente -->
                    </ul>
                    <div id="skillsEmptyMsg" style="text-align: center; color: #999; font-size: 0.85rem; margin-top: 10px;">
                        Nenhuma skill adicionada. Adicione skills acima.
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 25px;">
                <button type="button" class="btn-secondary" onclick="fecharModalCompetencia()">Cancelar</button>
                <button type="submit" class="btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Confirmação Exclusão -->
<div id="modalExcluir" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div class="modal-box" style="background: white; border-radius: 12px; padding: 30px; width: 100%; max-width: 450px; text-align: center;">
        <div style="color: #e53e3e; font-size: 3rem; margin-bottom: 15px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h2 style="margin-bottom: 10px;">Excluir Competência?</h2>
        <p style="color: #666; margin-bottom: 25px;">
            Você está prestes a excluir a competência <strong id="deleteTargetName"></strong>.<br>
            <strong>Atenção:</strong> Todas as skills vinculadas também serão apagadas. Esta ação não pode ser desfeita.
        </p>

        <form id="formExcluir" method="POST" action="">
            @csrf
            @method('DELETE')
            <div style="display: flex; justify-content: center; gap: 10px;">
                <button type="button" class="btn-secondary" onclick="fecharModalExcluir()">Cancelar</button>
                <button type="submit" class="btn-primary" style="background-color: #e53e3e; border-color: #e53e3e;">Sim, Excluir</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Importar -->
<div id="modalImportar" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div class="modal-box" style="background: white; border-radius: 12px; padding: 30px; width: 100%; max-width: 480px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0; font-size: 1.25rem;">Importar Competências</h2>
            <button type="button" onclick="fecharModalImportar()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #666;">&times;</button>
        </div>
        
        <p style="color: #555; font-size: 14px; margin-bottom: 20px;">
            Envie um arquivo Excel (.xlsx) ou CSV com as colunas: <strong>Nome, Descrição, Skills</strong>. 
            Separe as skills por caractere pipe ( | ).<br><br>
            <a href="{{ asset('modelos/modelo_importacao_competencia.csv') }}" download class="btn-secondary" style="font-size: 0.85rem; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
                <i class="fa-solid fa-download"></i> Baixar Arquivo Modelo (CSV)
            </a>
        </p>

        <form action="{{ route('competencias.importar') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="arquivo_competencias" class="form-label">Arquivo <span class="text-danger">*</span></label>
                <input type="file" name="arquivo" id="arquivo_competencias" required accept=".xlsx,.xls,.csv" class="form-control">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-secondary" onclick="fecharModalImportar()">Cancelar</button>
                <button type="submit" class="btn-primary" style="background-color: #27ae60; border-color: #27ae60;">Importar Planilha</button>
            </div>
        </form>
    </div>
</div>

<script>
    let tempSkills = [];

    function renderSkillsDOM() {
        const list = document.getElementById('skillsListDOM');
        const emptyMsg = document.getElementById('skillsEmptyMsg');
        
        list.innerHTML = '';
        
        if(tempSkills.length === 0) {
            emptyMsg.style.display = 'block';
        } else {
            emptyMsg.style.display = 'none';
            tempSkills.forEach((skillName, index) => {
                const li = document.createElement('li');
                li.style.cssText = 'display: flex; justify-content: space-between; align-items: flex-start; background: white; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.9rem; gap: 10px;';
                
                li.innerHTML = `
                    <div style="flex: 1; word-break: break-word;">
                        <i class="fa-solid fa-check" style="color: var(--primary-color); margin-right: 8px;"></i>
                        <span id="skill-text-${index}" style="line-height: 1.4;">${skillName}</span>
                    </div>
                    <input type="hidden" name="skills[]" value="${skillName.replace(/"/g, '&quot;')}">
                    <div style="display: flex; gap: 12px; padding-top: 2px;">
                        <button type="button" class="btn-edit-skill" data-index="${index}" style="background: none; border: none; color: #3182ce; cursor: pointer; padding: 0;" title="Editar Skill">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button type="button" class="btn-remove-skill" data-index="${index}" style="background: none; border: none; color: #e53e3e; cursor: pointer; padding: 0;" title="Remover Skill">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                `;
                list.appendChild(li);
            });

            // Bind events for edit buttons
            document.querySelectorAll('.btn-edit-skill').forEach(btn => {
                btn.addEventListener('click', function() {
                    const idx = parseInt(this.getAttribute('data-index'));
                    editarSkillDOM(idx);
                });
            });

            // Bind events for remove buttons
            document.querySelectorAll('.btn-remove-skill').forEach(btn => {
                btn.addEventListener('click', function() {
                    const idx = parseInt(this.getAttribute('data-index'));
                    removerSkillDOM(idx);
                });
            });
        }
    }

    async function editarSkillDOM(index) {
        if(index >= 0 && index < tempSkills.length) {
            const currentVal = tempSkills[index];
            if (typeof Swal !== 'undefined') {
                const { value: newSkill } = await Swal.fire({
                    title: 'Editar Skill',
                    input: 'textarea',
                    inputValue: currentVal,
                    showCancelButton: true,
                    confirmButtonColor: '#4f46e5',
                    cancelButtonColor: '#a0aec0',
                    confirmButtonText: 'Salvar',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        input: 'swal2-textarea-custom' // To adjust height if needed
                    },
                    inputValidator: (value) => {
                        if (!value.trim()) {
                            return 'A skill não pode estar vazia!';
                        }
                    }
                });
                
                if (newSkill && newSkill.trim() !== currentVal) {
                    tempSkills[index] = newSkill.trim();
                    renderSkillsDOM();
                }
            } else {
                const newSkill = prompt('Editar Skill:', currentVal);
                if (newSkill !== null && newSkill.trim() !== '') {
                    tempSkills[index] = newSkill.trim();
                    renderSkillsDOM();
                }
            }
        }
    }

    function addSkillDOM() {
        const input = document.getElementById('novaSkillTemp');
        const skillName = input.value.trim();
        
        if(skillName) {
            const skillExists = tempSkills.some(s => s.toLowerCase() === skillName.toLowerCase());
            if(!skillExists) {
                tempSkills.push(skillName);
                renderSkillsDOM();
            }
        }
        input.value = '';
        input.focus();
    }

    function removerSkillDOM(index) {
        if(index >= 0 && index < tempSkills.length) {
            tempSkills.splice(index, 1);
            renderSkillsDOM();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const inputSkill = document.getElementById('novaSkillTemp');
        const btnAdd = document.getElementById('btnAdicionarSkill');

        if(inputSkill) {
            inputSkill.addEventListener('keydown', function(e) {
                if(e.key === 'Enter') {
                    e.preventDefault();
                    addSkillDOM();
                }
            });
        }

        if(btnAdd) {
            btnAdd.addEventListener('click', function(e) {
                e.preventDefault();
                addSkillDOM();
            });
        }
    });

    function abrirModalNova() {
        document.getElementById('modalCompetenciaTitle').innerText = 'Nova Competência';
        document.getElementById('formCompetencia').action = "{{ route('competencias.store') }}";
        document.getElementById('formCompetenciaMethod').value = 'POST';
        document.getElementById('nome').value = '';
        document.getElementById('descricao').value = '';
        
        tempSkills = [];
        renderSkillsDOM();
        
        document.getElementById('modalCompetencia').style.display = 'flex';
    }

    function abrirModalEditar(id, nome, descricao, skillsRaw) {
        document.getElementById('modalCompetenciaTitle').innerText = 'Editar Competência';
        document.getElementById('formCompetencia').action = `/competencias/${id}`;
        document.getElementById('formCompetenciaMethod').value = 'PUT';
        document.getElementById('nome').value = nome;
        document.getElementById('descricao').value = descricao;
        
        tempSkills = [];
        if(skillsRaw && Array.isArray(skillsRaw)) {
            skillsRaw.forEach(sk => tempSkills.push(sk.nome));
        }
        renderSkillsDOM();

        document.getElementById('modalCompetencia').style.display = 'flex';
    }

    function fecharModalCompetencia() {
        document.getElementById('modalCompetencia').style.display = 'none';
    }

    // Modal Excluir
    function abrirModalExcluir(id, nome) {
        document.getElementById('deleteTargetName').innerText = nome;
        document.getElementById('formExcluir').action = `/competencias/${id}`;
        document.getElementById('modalExcluir').style.display = 'flex';
    }

    function fecharModalExcluir() {
        document.getElementById('modalExcluir').style.display = 'none';
    }

    function abrirModalImportar() {
        document.getElementById('modalImportar').style.display = 'flex';
    }

    function fecharModalImportar() {
        document.getElementById('modalImportar').style.display = 'none';
    }

    // Abre modal automaticamente via parâmetro na URL (Ex: para atalhos)
    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('action') === 'create') {
            abrirModalNova();
        }
    });
</script>
@endsection
