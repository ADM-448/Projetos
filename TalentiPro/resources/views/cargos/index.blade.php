@extends('layouts.app')

@section('title', 'Cargos')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
    <div class="header-actions">
        <div>
            <h1 class="page-title" style="margin-bottom: 5px;">Gestão de Cargos</h1>
            <p style="color: #666; font-size: 0.95rem;">Defina a estrutura de cargos da empresa, associe-os a departamentos e defina a hierarquia de liderança.</p>
        </div>

        <div>
            <button onclick="openCreateModal()" class="btn-primary">
                <i class="fa-solid fa-plus"></i> Novo Cargo
            </button>
        </div>
    </div>

    {{-- LISTAGEM DE CARGOS --}}
    <div class="list-container">
        @if($cargos->isEmpty())
            <div style="text-align: center; padding: 40px 20px;">
                <div style="font-size: 3rem; color: #cbd5e1; margin-bottom: 15px;">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                <h3 style="font-size: 1.2rem; color: #475569; margin-bottom: 5px; font-weight: bold;">Nenhum cargo cadastrado</h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">Cadastre os cargos da empresa para alocar seus colaboradores.</p>
                <button onclick="openCreateModal()" class="btn-primary">
                    <i class="fa-solid fa-plus"></i> Cadastrar Primeiro
                </button>
            </div>
        @else
            <div class="table-responsive">
                <table class="custom-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background-color: #f8fafc; border-bottom: 2px solid #6a4c7c;">
                            <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: left;">Nome do Cargo</th>
                            <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: left;">Departamento</th>
                            <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: center;">CBO</th>
                            <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: center;">Senioridade</th>
                            <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: left;">Líder Superior</th>
                            <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: center;">Colaboradores</th>
                            <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: right;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cargos as $cargo)
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px; font-weight: bold; color: #2d3748; text-align: left;" data-label="Nome do Cargo">
                                    <div>{{ $cargo->nome }}</div>
                                    @if($cargo->descricao)
                                        <div style="font-weight: normal; font-size: 0.8rem; color: #888; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $cargo->descricao }}">
                                            {{ \Illuminate\Support\Str::limit($cargo->descricao, 80) }}
                                        </div>
                                    @endif
                                </td>
                                <td style="padding: 15px; color: #4a5568; text-align: left;" data-label="Departamento">
                                    @if($cargo->departamento)
                                        <span style="background-color: #f3e5f5; color: #8e24aa; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                                            {{ $cargo->departamento->nome }}
                                        </span>
                                    @else
                                        <span style="color: #a0aec0; font-size: 0.85rem; font-style: italic;">
                                            Não associado
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 15px; text-align: center; color: #64748b; font-size: 0.9rem;" data-label="CBO">
                                    {{ $cargo->cbo ?? '-' }}
                                </td>
                                <td style="padding: 15px; text-align: center;" data-label="Senioridade">
                                    @if($cargo->senioridade)
                                        <span style="background-color: #fdf2e9; color: #d35400; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                                            {{ $cargo->senioridade }}
                                        </span>
                                    @else
                                        <span style="color: #a0aec0; font-size: 0.85rem; font-style: italic;">
                                            -
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 15px; color: #4a5568; text-align: left;" data-label="Líder Superior">
                                    @if($cargo->cargoSuperior)
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <i class="fa-solid fa-turn-up" style="color: #3498db; font-size: 0.8rem; transform: rotate(90deg);"></i>
                                            <span style="font-weight: 600; font-size: 0.9rem;">{{ $cargo->cargoSuperior->nome }}</span>
                                        </div>
                                    @else
                                        <span style="color: #a0aec0; font-size: 0.85rem; font-style: italic;">
                                            Sem líder
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 15px; text-align: center;" data-label="Colaboradores">
                                    <span style="background-color: #ebf5fb; color: #2980b9; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: bold;">
                                        {{ $cargo->colaboradores_count }} {{ Str::plural('colaborador', $cargo->colaboradores_count) }}
                                    </span>
                                </td>
                                <td style="padding: 15px; text-align: right;" data-label="Ações">
                                    <div style="display: inline-flex; gap: 10px;">
                                        <button
                                            type="button"
                                            onclick="openEditModal(this)"
                                            data-id="{{ $cargo->id }}"
                                            data-nome="{{ $cargo->nome }}"
                                            data-descricao="{{ $cargo->descricao ?? '' }}"
                                            data-cbo="{{ $cargo->cbo ?? '' }}"
                                            data-senioridade="{{ $cargo->senioridade ?? '' }}"
                                            data-departamento-id="{{ $cargo->departamento_id ?? '' }}"
                                            data-cargo-superior-id="{{ $cargo->cargo_superior_id ?? '' }}"
                                            style="background: none; border: none; color: #2980b9; cursor: pointer; font-weight: 600; font-size: 0.85rem;"
                                            title="Editar">
                                            <i class="fa-solid fa-pen-to-square"></i> Editar
                                        </button>

                                        <button 
                                            type="button" 
                                            onclick="confirmDelete(this)"
                                            data-id="{{ $cargo->id }}"
                                            data-nome="{{ $cargo->nome }}"
                                            style="background: none; border: none; color: #e74c3c; cursor: pointer; font-weight: 600; font-size: 0.85rem;"
                                            title="Excluir">
                                            <i class="fa-solid fa-trash"></i> Excluir
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- MODAL DE CRIAÇÃO --}}
    <div id="createModal"
        style="display: none; position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 1000; padding: 20px;">
        <div class="form-card"
            style="max-width: 460px; margin-bottom: 0; padding: 30px 25px; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border-radius: 16px;">
            <button type="button" onclick="closeCreateModal()"
                style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.25rem; color: #b2bec3; cursor: pointer; transition: 0.2s;"
                onmouseover="this.style.color='#636e72'" onmouseout="this.style.color='#b2bec3'">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <h3 style="font-size: 1.25rem; font-weight: bold; color: #6a4c7c; text-align: center; margin-bottom: 20px; margin-top: 0;">
                Novo Cargo</h3>

            <form action="{{ route('cargos.store') }}" method="POST">
                @csrf

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="create_nome" style="font-weight: 600; color: #555; margin-bottom: 6px; font-size: 13px;">Nome do Cargo <span class="text-danger">*</span></label>
                    <input type="text" name="nome" id="create_nome" required placeholder="Ex: Desenvolvedor Full Stack"
                        style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="create_descricao" style="font-weight: 600; color: #555; margin-bottom: 6px; font-size: 13px;">Descrição</label>
                    <textarea name="descricao" id="create_descricao" rows="2" placeholder="Descreva as responsabilidades deste cargo..."
                        style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px; resize: none;"></textarea>
                </div>

                <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="create_cbo" style="font-weight: 600; color: #555; margin-bottom: 6px; font-size: 13px;">CBO (Opcional)</label>
                        <input type="text" name="cbo" id="create_cbo" placeholder="Ex: 2124-05"
                            style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="create_senioridade" style="font-weight: 600; color: #555; margin-bottom: 6px; font-size: 13px;">Senioridade</label>
                        <select name="senioridade" id="create_senioridade"
                            style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                            <option value="">Selecione...</option>
                            <option value="Estágio">Estágio</option>
                            <option value="Júnior">Júnior</option>
                            <option value="Pleno">Pleno</option>
                            <option value="Sênior">Sênior</option>
                            <option value="Especialista">Especialista</option>
                            <option value="Coordenação">Coordenação</option>
                            <option value="Gerência">Gerência</option>
                            <option value="Diretoria">Diretoria</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="create_departamento_id" style="font-weight: 600; color: #555; margin-bottom: 6px; font-size: 13px;">Departamento</label>
                    <select name="departamento_id" id="create_departamento_id"
                        style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                        <option value="">Selecione um departamento...</option>
                        @foreach($departamentos as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="create_cargo_superior_id" style="font-weight: 600; color: #555; margin-bottom: 6px; font-size: 13px;">Reporta-se a (Líder / Cargo Superior)</label>
                    <select name="cargo_superior_id" id="create_cargo_superior_id"
                        style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                        <option value="">Nenhum cargo superior...</option>
                        @foreach($cargosExistentes as $cExistente)
                            <option value="{{ $cExistente->id }}">{{ $cExistente->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <button type="submit" class="btn-primary"
                        style="width: 100%; padding: 12px; font-size: 15px; font-weight: bold; border-radius: 8px;">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DE EDIÇÃO --}}
    <div id="editModal"
        style="display: none; position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 1000; padding: 20px;">
        <div class="form-card"
            style="max-width: 460px; margin-bottom: 0; padding: 30px 25px; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border-radius: 16px;">
            <button type="button" onclick="closeEditModal()"
                style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.25rem; color: #b2bec3; cursor: pointer; transition: 0.2s;"
                onmouseover="this.style.color='#636e72'" onmouseout="this.style.color='#b2bec3'">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <h3 style="font-size: 1.25rem; font-weight: bold; color: #6a4c7c; text-align: center; margin-bottom: 20px; margin-top: 0;">
                Editar Cargo</h3>

            <form id="editForm" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="edit_nome" style="font-weight: 600; color: #555; margin-bottom: 6px; font-size: 13px;">Nome do Cargo <span class="text-danger">*</span></label>
                    <input type="text" name="nome" id="edit_nome" required
                        style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="edit_descricao" style="font-weight: 600; color: #555; margin-bottom: 6px; font-size: 13px;">Descrição</label>
                    <textarea name="descricao" id="edit_descricao" rows="2"
                        style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px; resize: none;"></textarea>
                </div>

                <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="edit_cbo" style="font-weight: 600; color: #555; margin-bottom: 6px; font-size: 13px;">CBO (Opcional)</label>
                        <input type="text" name="cbo" id="edit_cbo"
                            style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="edit_senioridade" style="font-weight: 600; color: #555; margin-bottom: 6px; font-size: 13px;">Senioridade</label>
                        <select name="senioridade" id="edit_senioridade"
                            style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                            <option value="">Selecione...</option>
                            <option value="Estágio">Estágio</option>
                            <option value="Júnior">Júnior</option>
                            <option value="Pleno">Pleno</option>
                            <option value="Sênior">Sênior</option>
                            <option value="Especialista">Especialista</option>
                            <option value="Coordenação">Coordenação</option>
                            <option value="Gerência">Gerência</option>
                            <option value="Diretoria">Diretoria</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="edit_departamento_id" style="font-weight: 600; color: #555; margin-bottom: 6px; font-size: 13px;">Departamento</label>
                    <select name="departamento_id" id="edit_departamento_id"
                        style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                        <option value="">Selecione um departamento...</option>
                        @foreach($departamentos as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="edit_cargo_superior_id" style="font-weight: 600; color: #555; margin-bottom: 6px; font-size: 13px;">Reporta-se a (Líder / Cargo Superior)</label>
                    <select name="cargo_superior_id" id="edit_cargo_superior_id"
                        style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                        <option value="">Nenhum cargo superior...</option>
                        @foreach($cargosExistentes as $cExistente)
                            <option value="{{ $cExistente->id }}" id="opt_superior_{{ $cExistente->id }}">{{ $cExistente->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <button type="submit" class="btn-primary"
                        style="width: 100%; padding: 12px; font-size: 15px; font-weight: bold; border-radius: 8px;">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DE CONFIRMAÇÃO DE DELEÇÃO --}}
    <div id="deleteModal"
        style="display: none; position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 1000; padding: 20px;">
        <div class="form-card"
            style="max-width: 400px; margin-bottom: 0; padding: 35px 30px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border-radius: 16px; position: relative;">
            <button type="button" onclick="closeDeleteModal()"
                style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.25rem; color: #b2bec3; cursor: pointer; transition: 0.2s;"
                onmouseover="this.style.color='#636e72'" onmouseout="this.style.color='#b2bec3'">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div style="background-color: rgba(231, 76, 60, 0.1); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #e74c3c; font-size: 2rem;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>

            <h3 style="font-size: 1.25rem; font-weight: bold; color: #2d3748; margin-bottom: 10px;">Confirmar Exclusão</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 25px; line-height: 1.5; text-align: center;">
                Deseja realmente excluir o cargo <strong id="delete_cargo_name" style="color: #2d3748;"></strong>?
                Essa ação não poderá ser desfeita.
            </p>

            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')

                <div style="display: flex; gap: 15px; justify-content: center;">
                    <button type="button" onclick="closeDeleteModal()" class="btn-secondary"
                        style="flex: 1; padding: 12px; font-weight: bold; border-radius: 8px; border: 1px solid #ccc; font-size: 14px; justify-content: center;">Cancelar</button>
                    <button type="submit" class="btn-danger"
                        style="flex: 1; padding: 12px; font-weight: bold; border-radius: 8px; font-size: 14px;">Excluir</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            // Controle do Modal de Criação
            function openCreateModal() {
                const modal = document.getElementById('createModal');
                modal.style.display = 'flex';
            }

            // Fechar modal de criação
            function closeCreateModal() {
                const modal = document.getElementById('createModal');
                modal.style.display = 'none';
            }

            // Controle do Modal de Edição
            function openEditModal(button) {
                const id = button.getAttribute('data-id');
                const nome = button.getAttribute('data-nome');
                const descricao = button.getAttribute('data-descricao');
                const cbo = button.getAttribute('data-cbo');
                const senioridade = button.getAttribute('data-senioridade');
                const departamentoId = button.getAttribute('data-departamento-id');
                const cargoSuperiorId = button.getAttribute('data-cargo-superior-id');

                const modal = document.getElementById('editModal');
                const form = document.getElementById('editForm');

                // Define o action dinamicamente para o ID correto
                form.action = `/cargos/${id}`;

                // Preenche os campos
                document.getElementById('edit_nome').value = nome;
                document.getElementById('edit_descricao').value = descricao;
                document.getElementById('edit_cbo').value = cbo;
                document.getElementById('edit_senioridade').value = senioridade;
                document.getElementById('edit_departamento_id').value = departamentoId;
                
                // Ocultar a si mesmo do select de cargo superior para evitar recursividade circular
                const selectSuperior = document.getElementById('edit_cargo_superior_id');
                
                // Primeiro, mostra todas as opções
                for (let i = 0; i < selectSuperior.options.length; i++) {
                    selectSuperior.options[i].style.display = 'block';
                    selectSuperior.options[i].disabled = false;
                }
                
                // Desabilita/Oculta a opção que corresponde ao próprio cargo
                const selfOption = document.getElementById(`opt_superior_${id}`);
                if (selfOption) {
                    selfOption.style.display = 'none';
                    selfOption.disabled = true;
                }

                selectSuperior.value = cargoSuperiorId || '';

                modal.style.display = 'flex';
            }

            // Fechar modal de edição
            function closeEditModal() {
                const modal = document.getElementById('editModal');
                modal.style.display = 'none';
            }

            // Controle do Modal de Confirmação de Deleção
            function confirmDelete(button) {
                const id = button.getAttribute('data-id');
                const nome = button.getAttribute('data-nome');

                const modal = document.getElementById('deleteModal');
                const form = document.getElementById('deleteForm');
                const namePlaceholder = document.getElementById('delete_cargo_name');

                form.action = `/cargos/${id}`;
                namePlaceholder.textContent = nome;

                modal.style.display = 'flex';
            }

            function closeDeleteModal() {
                const modal = document.getElementById('deleteModal');
                modal.style.display = 'none';
            }

            // Fechar modais ao clicar fora
            window.onclick = function (event) {
                const createModal = document.getElementById('createModal');
                const editModal = document.getElementById('editModal');
                const deleteModal = document.getElementById('deleteModal');

                if (event.target === createModal) {
                    closeCreateModal();
                }
                if (event.target === editModal) {
                    closeEditModal();
                }
                if (event.target === deleteModal) {
                    closeDeleteModal();
                }
            }
        </script>
    @endpush
@endsection
