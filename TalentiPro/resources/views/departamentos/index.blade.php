@extends('layouts.admin')

@section('title', 'Departamentos')

@section('content')

@push('styles')
<style>
/* Reaproveitando estilos da tabela de colaboradores */
.table-list {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.table-list th {
    text-align: left;
    padding: 15px;
    background: #f8f9fa;
    color: #666;
    font-weight: 700;
    border-bottom: 2px solid #eee;
}

.table-list td {
    padding: 15px;
    border-bottom: 1px solid #eee;
    color: #333;
    vertical-align: middle;
}

.btn-icon {
    background: none;
    border: none;
    cursor: pointer;
    color: #777;
    transition: 0.2s;
    font-size: 1.1em;
    margin: 0 5px;
}

.btn-icon:hover {
    color: #6a4c7c;
    transform: scale(1.1);
}

/* Estilos do Modal */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: white;
    padding: 30px;
    border-radius: 10px;
    width: 500px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    position: relative;
}

.close-modal {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 25px;
    cursor: pointer;
    color: #999;
}

.modal-title {
    font-size: 1.2rem;
    color: #6a4c7c;
    margin-bottom: 20px;
    font-weight: bold;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.input-group {
    margin-bottom: 15px;
}

.input-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #444;
}

.input-group input,
.input-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    outline: none;
}

.btn-submit {
    width: 100%;
    background: #6a4c7c;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 5px;
    font-weight: bold;
    cursor: pointer;
    margin-top: 10px;
}

.btn-submit:hover {
    background: #563d66;
}
</style>
@endpush

<div class="header-actions">
    <h1 class="page-title">Departamentos</h1>
    {{-- Botão abre modal em modo CRIAR --}}
    <button onclick="abrirModalCriar()" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Novo Departamento
    </button>
</div>

<div class="card-container">
    @if($errors->any())
    <div class="alert alert-danger">
        {{ $errors->first() }}
    </div>
    @endif

    <table class="table-list">
        <thead>
            <tr>
                <th>Nome do Departamento</th>
                <th>Responsável (Gestor)</th>
                <th>Qtd. Colaboradores</th>
                <th style="text-align: right;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($departamentos as $dep)
            <tr>
                <td data-label="Departamento"><strong>{{ $dep->nome }}</strong></td>
                <td data-label="Líder/Gestor">
                    @if($dep->gestor)
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div
                            style="width:30px; height:30px; background:#ccc; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-size:12px;">
                            {{ substr($dep->gestor->name, 0, 1) }}
                        </div>
                        {{ $dep->gestor->name }}
                    </div>
                    @else
                    <span style="color:#999; font-style:italic;">Não definido</span>
                    @endif
                </td>
                <td data-label="Colaboradores">{{ $dep->users_count ?? 0 }} Colaboradores</td>
                <td data-label="Ações" style="text-align: right;">
                    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 15px;">
                        <button class="btn-icon" style="color:#3b5998; font-size:1.2em; transition: 0.2s; margin:0;" onmouseover="this.style.color='#2d4373'" onmouseout="this.style.color='#3b5998'" title="Editar" onclick="abrirModalEditar({{ $dep->id }}, '{{ $dep->nome }}', '{{ $dep->gestor_id }}')">
                            <i class="fa-solid fa-pen"></i>
                        </button>

                        <form action="{{ route('departamentos.destroy', $dep->id) }}" method="POST" style="margin: 0;" class="confirm-delete" data-confirm-message="Deseja excluir o departamento '{{ $dep->nome }}'?">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-icon" style="color:#e74c3c; font-size:1.2em; transition: 0.2s; margin:0;" onmouseover="this.style.color='#c0392b'" onmouseout="this.style.color='#e74c3c'" title="Excluir"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align:center; padding:30px; color:#777;">Nenhum departamento cadastrado.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- MODAL ÚNICO (CRIAR E EDITAR) --}}
<div id="modalDepartamento" class="modal-overlay">
    <div class="modal-content">
        <span class="close-modal" onclick="fecharModal()">&times;</span>

        <div class="modal-title" id="modalTitle">Novo Departamento</div>

        {{-- O action do form será definido via JS --}}
        <form id="formDepartamento" method="POST">
            @csrf
            <div id="methodSpoof"></div> {{-- Aqui vai o @method('PUT') via JS se for edição --}}

            <div class="input-group">
                <label>Nome do Departamento</label>
                <input type="text" name="nome" id="inputNome" required placeholder="Ex: Financeiro">
            </div>

            <div class="input-group">
                <label>Gestor Responsável</label>
                <select name="gestor_id" id="selectGestor">
                    <option value="">Selecione um gestor...</option>
                    @foreach($gestores as $g)
                    <option value="{{ $g->id }}">
                        {{ $g->name }} - {{ $g->cargo ? $g->cargo->nome : 'Sem Cargo' }}
                    </option>
                    @endforeach
                </select>
                <small style="color:#777; font-size:0.8em;">Exibindo apenas usuários com perfil Gestor.</small>
            </div>

            <button type="submit" class="btn-submit" id="btnSalvar">Salvar</button>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const modal = document.getElementById('modalDepartamento');
const form = document.getElementById('formDepartamento');
const title = document.getElementById('modalTitle');
const inputNome = document.getElementById('inputNome');
const selectGestor = document.getElementById('selectGestor');
const methodSpoof = document.getElementById('methodSpoof');

function abrirModalCriar() {
    modal.style.display = 'flex';
    title.innerText = 'Novo Departamento';
    
    // Pega o caminho atual de forma inteligente
    let currentPath = window.location.pathname;
    form.action = currentPath.endsWith('/') ? currentPath.slice(0, -1) : currentPath;
    
    methodSpoof.innerHTML = ''; 
    inputNome.value = '';
    selectGestor.value = '';
}

function abrirModalEditar(id, nome, gestorId) {
    modal.style.display = 'flex';
    title.innerText = 'Editar Departamento';
    
    // CORREÇÃO DEFINITIVA: Pega o caminho atual e acrescenta o ID
    let currentPath = window.location.pathname;
    if (!currentPath.endsWith('/')) {
        currentPath += '/';
    }
    form.action = currentPath + id;

    // Adiciona o input hidden para simular PUT
    methodSpoof.innerHTML = '<input type="hidden" name="_method" value="PUT">';

    inputNome.value = nome;
    selectGestor.value = gestorId || '';
}

function fecharModal() {
    modal.style.display = 'none';
}

// Fecha ao clicar fora
window.onclick = function(event) {
    if (event.target == modal) fecharModal();
}
</script>
@endpush
