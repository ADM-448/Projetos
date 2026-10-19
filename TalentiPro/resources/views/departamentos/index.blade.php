@extends('layouts.app')

@section('title', 'Departamentos')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
    <div class="header-actions">
        <div>
            <h1 class="page-title" style="margin-bottom: 5px;">Gestão de Departamentos</h1>
            <p style="color: #666; font-size: 0.95rem;">Organize a estrutura organizacional da sua empresa e defina os
                gestores responsáveis.</p>
        </div>

        @if(!auth()->user()->isConsultor())
        <div>
            <button onclick="openCreateModal()" class="btn-primary">
                <i class="fa-solid fa-plus"></i> Novo Departamento
            </button>
        </div>
        @endif
    </div>

    {{-- LISTAGEM DE DEPARTAMENTOS --}}
    <div class="list-container">
        @if($departamentos->isEmpty())
            <div style="text-align: center; padding: 40px 20px;">
                <div style="font-size: 3rem; color: #cbd5e1; margin-bottom: 15px;">
                    <i class="fa-solid fa-sitemap"></i>
                </div>
                <h3 style="font-size: 1.2rem; color: #475569; margin-bottom: 5px; font-weight: bold;">Nenhum departamento
                    cadastrado</h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">Cadastre os setores da empresa para poder
                    alocar cargos e colaboradores.</p>
                @if(!auth()->user()->isConsultor())
                <button onclick="openCreateModal()" class="btn-primary">
                    <i class="fa-solid fa-plus"></i> Cadastrar Primeiro
                </button>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="custom-table table-cards-mobile" style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background-color: #f8fafc; border-bottom: 2px solid #6a4c7c;">
                            <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: left;">Nome do Setor</th>
                            <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: left;">Descrição</th>
                            <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: left;">Gestor Responsável
                            </th>
                            <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: center;">Colaboradores</th>
                            @if(!auth()->user()->isConsultor())
                            <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: right;">Ações</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departamentos as $dept)
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px; font-weight: bold; color: #2d3748; text-align: left;" data-label="Nome do Setor">
                                    {{ $dept->nome }}
                                </td>
                                <td
                                    style="padding: 15px; color: #64748b; font-size: 0.9rem; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; text-align: left;" data-label="Descrição">
                                    {{ $dept->descricao ?? 'Sem descrição informada' }}
                                </td>
                                <td style="padding: 15px; color: #4a5568; text-align: left;" data-label="Gestor Responsável">
                                    @if($dept->gestor)
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <div
                                                style="background-color: #e8f8f5; color: #2ecc71; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.85rem;">
                                                {{ Str::upper(collect(explode(' ', $dept->gestor->name))->map(fn($w) => mb_substr($w, 0, 1))->take(2)->join('')) }}
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; font-size: 0.9rem;">{{ $dept->gestor->name }}</div>
                                                <div style="font-size: 0.75rem; color: #718096;">
                                                    {{ $dept->gestor->cargo->nome ?? 'Gestor' }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span style="color: #a0aec0; font-size: 0.85rem; font-style: italic;">
                                            <i class="fa-solid fa-user-slash"></i> Não definido
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 15px; text-align: center;" data-label="Colaboradores">
                                    <span
                                        style="background-color: #ebf5fb; color: #2980b9; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: bold;">
                                        {{ $dept->colaboradores_count }} {{ Str::plural('colaborador', $dept->colaboradores_count) }}
                                    </span>
                                </td>
                                @if(!auth()->user()->isConsultor())
                                <td style="padding: 15px; text-align: right;" data-label="Ações">
                                    <div style="display: inline-flex; gap: 10px;">
                                        <button
                                            type="button"
                                            onclick="openEditModal(this)"
                                            data-id="{{ $dept->id }}"
                                            data-nome="{{ $dept->nome }}"
                                            data-descricao="{{ $dept->descricao ?? '' }}"
                                            data-gestor-id="{{ $dept->gestor_id }}"
                                            style="background: none; border: none; color: #2980b9; cursor: pointer; font-weight: 600; font-size: 0.85rem;"
                                            title="Editar">
                                            <i class="fa-solid fa-pen-to-square"></i> Editar
                                        </button>
                                        <button type="button"
                                            onclick="confirmDelete(this)"
                                            data-id="{{ $dept->id }}"
                                            data-nome="{{ $dept->nome }}"
                                            style="background: none; border: none; color: #e74c3c; cursor: pointer; font-weight: 600; font-size: 0.85rem;"
                                            title="Excluir">
                                            <i class="fa-solid fa-trash"></i> Excluir
                                        </button>
                                    </div>
                                </td>
                                @endif
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
            style="max-width: 440px; margin-bottom: 0; padding: 35px 30px; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border-radius: 16px;">
            <button type="button" onclick="closeCreateModal()"
                style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.25rem; color: #b2bec3; cursor: pointer; transition: 0.2s;"
                onmouseover="this.style.color='#636e72'" onmouseout="this.style.color='#b2bec3'">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <h3
                style="font-size: 1.25rem; font-weight: bold; color: #6a4c7c; text-align: center; margin-bottom: 25px; margin-top: 0;">
                Novo Departamento</h3>

            <form action="{{ route('departamentos.store') }}" method="POST">
                @csrf

                <div class="form-group" style="margin-bottom: 20px; text-align: center;">
                    <label for="create_nome"
                        style="display: block; font-weight: 600; color: #555; margin-bottom: 8px; font-size: 14px; text-align: center;">Nome
                        do Departamento</label>
                    <input type="text" name="nome" id="create_nome" required placeholder="Ex: Financeiro"
                        style="text-align: center; padding: 12px 15px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="create_descricao"
                        style="display: block; font-weight: 600; color: #555; margin-bottom: 8px; font-size: 14px;">Descrição</label>
                    <textarea name="descricao" id="create_descricao" rows="2" placeholder="Descreva a função do setor..."
                        style="padding: 12px 15px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px; resize: none;"></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="create_gestor_id"
                        style="display: block; font-weight: 600; color: #555; margin-bottom: 8px; font-size: 14px;">Gestor
                        Responsável (Opcional)</label>
                    <select name="gestor_id" id="create_gestor_id"
                        style="padding: 12px 15px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                        <option value="">Selecione um gestor...</option>
                        @foreach($gestores as $gestor)
                            <option value="{{ $gestor->id }}">{{ $gestor->name }}</option>
                        @endforeach
                    </select>
                    <span style="font-size: 0.75rem; color: #aaa; margin-top: 6px; display: block;">Exibindo apenas usuários
                        com perfil Gestor ou Master.</span>
                </div>

                <div style="margin-top: 25px;">
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
            style="max-width: 440px; margin-bottom: 0; padding: 35px 30px; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border-radius: 16px;">
            <button type="button" onclick="closeEditModal()"
                style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.25rem; color: #b2bec3; cursor: pointer; transition: 0.2s;"
                onmouseover="this.style.color='#636e72'" onmouseout="this.style.color='#b2bec3'">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <h3
                style="font-size: 1.25rem; font-weight: bold; color: #6a4c7c; text-align: center; margin-bottom: 25px; margin-top: 0;">
                Editar Departamento</h3>

            <form id="editForm" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="edit_nome"
                        style="display: block; font-weight: 600; color: #555; margin-bottom: 8px; font-size: 14px;">Nome
                        do Departamento</label>
                    <input type="text" name="nome" id="edit_nome" required
                        style="padding: 12px 15px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="edit_descricao"
                        style="display: block; font-weight: 600; color: #555; margin-bottom: 8px; font-size: 14px;">Descrição</label>
                    <textarea name="descricao" id="edit_descricao" rows="2"
                        style="padding: 12px 15px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px; resize: none;"></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="edit_gestor_id"
                        style="display: block; font-weight: 600; color: #555; margin-bottom: 8px; font-size: 14px;">Gestor
                        Responsável (Opcional)</label>
                    <select name="gestor_id" id="edit_gestor_id"
                        style="padding: 12px 15px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                        <option value="">Selecione um gestor...</option>
                        @foreach($gestores as $gestor)
                            <option value="{{ $gestor->id }}">{{ $gestor->name }}</option>
                        @endforeach
                    </select>
                    <span style="font-size: 0.75rem; color: #aaa; margin-top: 6px; display: block;">Exibindo apenas usuários
                        com perfil Gestor ou Master.</span>
                </div>

                <div style="margin-top: 25px;">
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

            <div
                style="background-color: rgba(231, 76, 60, 0.1); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #e74c3c; font-size: 2rem;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>

            <h3 style="font-size: 1.25rem; font-weight: bold; color: #2d3748; margin-bottom: 10px;">Confirmar Exclusão</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 25px; line-height: 1.5; text-align: center;">
                Deseja realmente excluir o departamento <strong id="delete_dept_name" style="color: #2d3748;"></strong>?
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

            function closeCreateModal() {
                const modal = document.getElementById('createModal');
                modal.style.display = 'none';
            }

            // Controle do Modal de Edição
            function openEditModal(button) {
                const id = button.getAttribute('data-id');
                const nome = button.getAttribute('data-nome');
                const descricao = button.getAttribute('data-descricao');
                const gestorId = button.getAttribute('data-gestor-id');

                const modal = document.getElementById('editModal');
                const form = document.getElementById('editForm');

                // Define o action dinamicamente para o ID correto
                form.action = `/departamentos/${id}`;

                // Preenche os campos
                document.getElementById('edit_nome').value = nome;
                document.getElementById('edit_descricao').value = descricao;
                document.getElementById('edit_gestor_id').value = gestorId || '';

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
                const namePlaceholder = document.getElementById('delete_dept_name');

                form.action = `/departamentos/${id}`;
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