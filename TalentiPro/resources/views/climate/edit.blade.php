@extends('layouts.app')

@section('title', 'Editar Pesquisa de Clima (Rascunho)')

@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
    <style>
        .header-questionario {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #edf2f7;
            padding-bottom: 12px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .grid-pergunta {
            display: grid;
            grid-template-columns: 1fr 250px;
            gap: 15px;
            align-items: start;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 20px;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        @media (max-width: 640px) {
            .grid-pergunta {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>

    <div class="header-actions" style="margin-bottom: 25px;">
        <div>
            <h1 class="page-title" style="margin-bottom: 8px;">
                <i class="fa-solid fa-pen" style="color: #ec4899;"></i> Editar Pesquisa de Clima (Rascunho)
            </h1>
            <p style="color: #64748b; font-size: 0.95rem;">
                Configure o título, período de vigência e selecione as perguntas da avaliação.
            </p>
        </div>
    </div>

    @if($errors->any())
        <div
            style="background: #fff5f5; border: 1px solid #feb2b2; color: #c53030; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 600;">
            <i class="fa-solid fa-circle-exclamation"></i> Verifique os erros no formulário abaixo:
            <ul style="margin: 8px 0 0 20px; font-weight: normal;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('gestao.clima.update', $survey->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div
            style="background: white; border-radius: 16px; padding: 30px; border: 1px solid #f0f2f5; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 25px;">
            <h3
                style="font-size: 1.1rem; font-weight: 800; color: #1e293b; margin-bottom: 20px; border-bottom: 1px solid #edf2f7; padding-bottom: 10px;">
                <i class="fa-solid fa-sliders" style="color: #6a4c7c; margin-right: 8px;"></i> Informações Gerais
            </h3>

            <div style="display: flex; flex-direction: column; gap: 18px;">
                <div>
                    <label style="display: block; font-weight: 700; color: #334155; font-size: 0.9rem; margin-bottom: 6px;">
                        Título da Pesquisa <span style="color: #e53e3e;">*</span>
                    </label>
                    <input type="text" name="titulo"
                        value="{{ old('titulo', $survey->titulo) }}"
                        placeholder="Ex: Pesquisa de Clima Organizacional 2026"
                        style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e0; border-radius: 10px; font-size: 0.95rem; outline: none; box-sizing: border-box;"
                        required>
                </div>

                <div>
                    <label style="display: block; font-weight: 700; color: #334155; font-size: 0.9rem; margin-bottom: 6px;">
                        Descrição / Instruções aos Colaboradores
                    </label>
                    <textarea name="descricao" rows="3"
                        placeholder="Ex: Sua opinião é fundamental para construirmos um ambiente de trabalho cada vez melhor. Esta pesquisa é 100% anônima e leva apenas 3 minutos."
                        style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e0; border-radius: 10px; font-size: 0.95rem; outline: none; resize: vertical; box-sizing: border-box;">{{ old('descricao', $survey->descricao) }}</textarea>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
                    <div>
                        <label
                            style="display: block; font-weight: 700; color: #334155; font-size: 0.9rem; margin-bottom: 6px;">
                            Data de Início
                        </label>
                        <input type="date" name="data_inicio" value="{{ old('data_inicio', $survey->data_inicio ? \Carbon\Carbon::parse($survey->data_inicio)->format('Y-m-d') : '') }}"
                            style="width: 100%; padding: 11px 14px; border: 1px solid #cbd5e0; border-radius: 10px; font-size: 0.9rem; outline: none; box-sizing: border-box;">
                    </div>

                    <div>
                        <label
                            style="display: block; font-weight: 700; color: #334155; font-size: 0.9rem; margin-bottom: 6px;">
                            Data de Encerramento (Opcional)
                        </label>
                        <input type="date" name="data_fim" value="{{ old('data_fim', $survey->data_fim ? \Carbon\Carbon::parse($survey->data_fim)->format('Y-m-d') : '') }}"
                            style="width: 100%; padding: 11px 14px; border: 1px solid #cbd5e0; border-radius: 10px; font-size: 0.9rem; outline: none; box-sizing: border-box;">
                    </div>
                </div>
            </div>
        </div>

        {{-- PÚBLICO ALVO E PERGUNTAS DINÂMICAS --}}
        <div
            style="background: white; border-radius: 16px; padding: 30px; border: 1px solid #f0f2f5; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 25px;">
            <h3
                style="font-size: 1.1rem; font-weight: 800; color: #1e293b; margin-bottom: 20px; border-bottom: 1px solid #edf2f7; padding-bottom: 10px;">
                <i class="fa-solid fa-users-viewfinder" style="color: #ec4899; margin-right: 8px;"></i> Público Alvo
            </h3>

            <div style="margin-bottom: 30px;">
                <label style="display: block; font-weight: 700; color: #334155; font-size: 0.9rem; margin-bottom: 6px;">
                    Direcionar pesquisa para: <span style="color: #e53e3e;">*</span>
                </label>
                <select name="departamento_id"
                    style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e0; border-radius: 10px; font-size: 0.95rem; outline: none; box-sizing: border-box;">
                    <option value="">Toda a Empresa</option>
                    @foreach($departamentos as $dep)
                        <option value="{{ $dep->id }}" {{ old('departamento_id', $survey->departamento_id) == $dep->id ? 'selected' : '' }}>
                            Setor: {{ $dep->nome }}
                        </option>
                    @endforeach
                </select>
                <small style="color: #718096; display: block; margin-top: 5px;">Se deixar vazio, todos os colaboradores da
                    empresa receberão a pesquisa.</small>
            </div>

            <div class="header-questionario">
                <div>
                    <h3 style="font-size: 1.1rem; font-weight: 800; color: #1e293b; margin: 0 0 4px 0;">
                        <i class="fa-solid fa-clipboard-question" style="color: #6a4c7c; margin-right: 8px;"></i>
                        Questionário
                    </h3>
                    <p style="color: #64748b; font-size: 0.88rem; margin: 0;">
                        Crie as perguntas da sua pesquisa. Você pode escolher entre respostas de nota (1 a 10) ou texto
                        livre.
                    </p>
                </div>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: nowrap;">
                    @if(isset($templates) && $templates->count() > 0)
                        <div class="custom-multi-select" style="position: relative; width: 280px; flex-shrink: 0;">
                            <div id="multiSelectToggle" onclick="toggleMultiSelect()" style="padding: 10px 14px; border-radius: 8px; border: 1px solid #cbd5e0; background-color: #f8fafc; color: #334155; font-weight: 600; font-size: 0.9rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); height: 42px; box-sizing: border-box;" onmouseover="this.style.borderColor='#cbd5e0'" onmouseout="this.style.borderColor=document.getElementById('multiSelectDropdown').style.display==='block'?'#ec4899':'#cbd5e0'">
                                <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">-- Importar Perguntas Salvas --</span>
                                <i class="fa-solid fa-chevron-down" style="color: #94a3b8; font-size: 0.8rem; margin-left: 8px;"></i>
                            </div>
                            <div id="multiSelectDropdown" style="display: none; position: absolute; top: calc(100% + 5px); left: 0; width: 100%; min-width: 280px; background: white; border: 1px solid #cbd5e0; border-radius: 8px; max-height: 250px; overflow-y: auto; z-index: 50; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                                @foreach($templates as $tpl)
                                    <div id="template-item-{{ $tpl->id }}" style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                        <label style="display: flex; align-items: flex-start; padding: 10px 12px; cursor: pointer; margin: 0; flex: 1;">
                                            <input type="checkbox" class="template-checkbox" value="{{ $tpl->id }}" 
                                                data-texto="{{ $tpl->texto_pergunta }}" 
                                                data-tipo="{{ $tpl->tipo_resposta }}" 
                                                data-dimensao="{{ $tpl->dimensao }}" 
                                                style="margin-top: 2px; margin-right: 10px; cursor: pointer;">
                                            <span style="font-size: 0.85rem; color: #475569; line-height: 1.4;">
                                                {{ $tpl->is_global ? '[Global] ' : '' }}{{ $tpl->texto_pergunta }}
                                            </span>
                                        </label>
                                        @if(!$tpl->is_global)
                                            <button type="button" onclick="excluirTemplate({{ $tpl->id }}, event)" style="background: transparent; border: none; color: #ef4444; padding: 10px; margin-right: 5px; cursor: pointer; border-radius: 6px; transition: all 0.2s;" title="Excluir Template" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='transparent'">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <button type="button" onclick="importarTemplate()"
                            style="background: #3b82f6; color: white; border: none; padding: 0 16px; border-radius: 8px; font-weight: 700; cursor: pointer; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3); transition: all 0.2s; height: 42px; display: inline-flex; align-items: center; justify-content: center;"
                            onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='translateY(0)';"
                            title="Importar perguntas selecionadas">
                            <i class="fa-solid fa-download"></i>
                        </button>
                    @endif

                    <button type="button" onclick="adicionarPergunta()"
                        style="background: #10b981; color: white; border: none; padding: 0 18px; border-radius: 8px; font-weight: 700; cursor: pointer; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3); transition: all 0.2s; height: 42px; display: inline-flex; align-items: center; justify-content: center;"
                        onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='translateY(0)';">
                        <i class="fa-solid fa-plus" style="margin-right: 6px;"></i> Adicionar Manual
                    </button>
                </div>
            </div>

            <div id="perguntas-container" style="display: flex; flex-direction: column; gap: 15px;">
                <!-- Perguntas inseridas via JS -->
            </div>

            <div
                style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #edf2f7; display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" id="salvar_banco_perguntas" name="salvar_banco_perguntas" value="1"
                    style="width: 18px; height: 18px; cursor: pointer;">
                <label for="salvar_banco_perguntas" style="font-weight: 600; color: #4a5568; cursor: pointer;">
                    Salvar as Novas perguntas como Rascunho para usar futuramente
                </label>
            </div>
        </div>

        {{-- BOTÕES DE AÇÃO --}}
        <div class="form-actions">
            <a href="{{ route('gestao.clima.index') }}" class="btn-action"
                style="padding: 12px 24px; border-radius: 10px; border: 1px solid #cbd5e0; background: white; color: #4a5568; font-weight: 600; text-decoration: none;">
                Cancelar
            </a>

            <button type="submit" name="salvar_rascunho" value="1" class="btn-action"
                style="padding: 12px 24px; border-radius: 10px; border: 1px solid #6a4c7c; background: transparent; color: #6a4c7c; font-weight: 700; cursor: pointer;">
                <i class="fa-solid fa-save" style="margin-right: 8px;"></i> Salvar Rascunho
            </button>

            <button type="submit" name="publicar_agora" value="1" class="btn-primary btn-action"
                style="padding: 12px 28px; border-radius: 10px; font-weight: 800; gap: 8px; background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); border: none; box-shadow: 0 4px 14px rgba(236, 72, 153, 0.35); color: white; cursor: pointer;">
                <i class="fa-solid fa-paper-plane"></i> Salvar e Publicar Agora
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            let perguntaIndex = 0;

            function adicionarPergunta(texto = '', tipo = 'escala_1_10', dimensao = '') {
                const container = document.getElementById('perguntas-container');

                const html = `
                    <div class="pergunta-item" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 55px 20px 20px; position: relative;">
                        <input type="hidden" name="perguntas[${perguntaIndex}][id]" value="">
                        <button type="button" onclick="this.parentElement.remove()" style="position: absolute; top: 20px; right: 15px; background: #fee2e2; color: #ef4444; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='#fca5a5'; this.style.color='white';" onmouseout="this.style.background='#fee2e2'; this.style.color='#ef4444';" title="Remover Pergunta">
                            <i class="fa-solid fa-times"></i>
                        </button>

                        <div class="grid-pergunta">
                            <div>
                                <label style="display: block; font-weight: 700; color: #475569; font-size: 0.85rem; margin-bottom: 4px;">Texto da Pergunta <span style="color: #e53e3e;">*</span></label>
                                <input type="text" name="perguntas[${perguntaIndex}][texto_pergunta]" value="${texto}" required placeholder="Ex: Qual sua satisfação com o ambiente de trabalho?" style="width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 8px; box-sizing: border-box;">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 700; color: #475569; font-size: 0.85rem; margin-bottom: 4px;">Tipo de Resposta <span style="color: #e53e3e;">*</span></label>
                                <select name="perguntas[${perguntaIndex}][tipo_resposta]" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 8px; box-sizing: border-box;">
                                    <option value="escala_1_10" ${tipo === 'escala_1_10' ? 'selected' : ''}>Nota (1 a 10)</option>
                                    <option value="texto_livre" ${tipo === 'texto_livre' ? 'selected' : ''}>Texto Livre</option>
                                </select>
                            </div>
                        </div>

                        <div style="margin-top: 10px;">
                            <label style="display: block; font-weight: 700; color: #475569; font-size: 0.85rem; margin-bottom: 4px;">Categoria / Tema (Opcional)</label>
                            <input type="text" name="perguntas[${perguntaIndex}][dimensao]" value="${dimensao}" placeholder="Ex: Ambiente de Trabalho, Liderança, etc." style="width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 8px; box-sizing: border-box;">
                        </div>
                    </div>
                `;

                container.insertAdjacentHTML('beforeend', html);
                perguntaIndex++;
            }

            function toggleMultiSelect() {
                const dropdown = document.getElementById('multiSelectDropdown');
                const toggle = document.getElementById('multiSelectToggle');
                const isHidden = dropdown.style.display === 'none';
                
                dropdown.style.display = isHidden ? 'block' : 'none';
                toggle.style.borderColor = isHidden ? '#ec4899' : '#cbd5e0';
                toggle.style.boxShadow = isHidden ? '0 0 0 3px rgba(236,72,153,0.1)' : 'inset 0 2px 4px rgba(0,0,0,0.02)';
            }

            document.addEventListener('click', function(event) {
                const container = document.querySelector('.custom-multi-select');
                if (container && !container.contains(event.target)) {
                    const dropdown = document.getElementById('multiSelectDropdown');
                    const toggle = document.getElementById('multiSelectToggle');
                    if (dropdown && dropdown.style.display === 'block') {
                        dropdown.style.display = 'none';
                        toggle.style.borderColor = '#cbd5e0';
                        toggle.style.boxShadow = 'inset 0 2px 4px rgba(0,0,0,0.02)';
                    }
                }
            });

            function importarTemplate() {
                const checkboxes = document.querySelectorAll('.template-checkbox:checked');
                if (checkboxes.length === 0) {
                    showAlert('Por favor, selecione pelo menos uma pergunta para importar.', 'Atenção', 'info');
                    return;
                }

                checkboxes.forEach(cb => {
                    adicionarPergunta(cb.dataset.texto, cb.dataset.tipo, cb.dataset.dimensao);
                    cb.checked = false; // desmarca após importar
                });

                const dropdown = document.getElementById('multiSelectDropdown');
                const toggle = document.getElementById('multiSelectToggle');
                if (dropdown) {
                    dropdown.style.display = 'none';
                    toggle.style.borderColor = '#cbd5e0';
                    toggle.style.boxShadow = 'inset 0 2px 4px rgba(0,0,0,0.02)';
                }
            }

            function excluirTemplate(id, event) {
                event.stopPropagation(); // impede de fechar o dropdown ao clicar na lixeira

                showConfirm('Deseja excluir esta pergunta dos rascunhos salvos?', function() {
                    fetch(`/gestao/clima/templates/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const item = document.getElementById(`template-item-${id}`);
                            if (item) item.remove();
                        } else {
                            showAlert(data.error || 'Erro ao excluir.', 'Erro', 'error');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        showAlert('Erro na requisição ao excluir.', 'Erro', 'error');
                    });
                }, 'Excluir Rascunho', 'danger');
            }

            // Adiciona uma pergunta inicial ao carregar
            document.addEventListener('DOMContentLoaded', () => {
                const perguntasAtuais = @json($survey->perguntas);
                if (perguntasAtuais && perguntasAtuais.length > 0) {
                    perguntasAtuais.forEach(p => {
                        adicionarPergunta(p.texto_pergunta, p.tipo_resposta, p.dimensao);
                    });
                } else {
                    adicionarPergunta();
                }
            });
        </script>
    @endpush

@endsection