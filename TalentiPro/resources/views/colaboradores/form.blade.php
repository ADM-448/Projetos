@extends('layouts.admin')

@section('title', isset($colaborador) ? 'Editar Colaborador' : 'Cadastrar Colaborador')

@section('content')

{{-- Estilos Específicos do Formulário --}}
@push('styles')
<style>
.form-card {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

h3 {
    color: #6a4c7c;
    margin-bottom: 20px;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
    font-size: 1.1rem;
}

.form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    flex: 1;
    display: flex;
    flex-direction: column;
}

label {
    font-weight: 600;
    font-size: 0.9rem;
    color: #444;
    margin-bottom: 8px;
}

input,
select {
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.95rem;
    outline: none;
    transition: border-color 0.3s;
}

input:focus,
select:focus {
    border-color: #6a4c7c;
}

.btn-save {
    background-color: #6a4c7c;
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
    font-size: 1rem;
    transition: background 0.3s;
}

.btn-save:hover {
    background-color: #563d66;
}

.btn-back {
    background-color: #95a5a6;
    color: white;
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 0.9rem;
}

.password-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.password-wrapper input {
    flex: 1;
    padding-right: 40px;
}

.toggle-password {
    position: absolute;
    right: 12px;
    cursor: pointer;
    color: #999;
    font-size: 1rem;
    transition: color 0.2s;
    background: none;
    border: none;
    padding: 0;
    line-height: 1;
}

.toggle-password:hover {
    color: #6a4c7c;
}

.whatsapp-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.whatsapp-wrapper input {
    flex: 1;
    padding-left: 40px;
}

.whatsapp-flag {
    position: absolute;
    left: 12px;
    color: #25d366;
    font-size: 1rem;
    pointer-events: none;
}

.header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
</style>
@endpush

<div class="header-actions">
    <h1 class="page-title">{{ isset($colaborador) ? 'EDITAR COLABORADOR' : 'NOVO COLABORADOR' }}</h1>
    <a href="{{ route('colaboradores.index') }}" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
</div>

{{-- Mostra erros de validação se houver --}}
@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="form-card">
    <form
        action="{{ isset($colaborador) ? route('colaboradores.update', $colaborador->id) : route('colaboradores.store') }}"
        method="POST">
        @csrf
        @if(isset($colaborador)) @method('PUT') @endif

        <h3>Dados Pessoais</h3>
        <div class="form-row">
            <div class="form-group">
                <label>Nome Completo *</label>
                <input type="text" name="name" value="{{ old('name', $colaborador->name ?? '') }}" required
                    placeholder="Ex: João Silva">
            </div>
            <div class="form-group">
                <label>CPF</label>
                <input type="text" name="cpf" value="{{ old('cpf', $colaborador->cpf ?? '') }}"
                    placeholder="000.000.000-00">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Data de Nascimento</label>
                <input type="date" name="data_nascimento"
                    value="{{ old('data_nascimento', isset($colaborador) && $colaborador->data_nascimento ? $colaborador->data_nascimento->format('Y-m-d') : '') }}">
            </div>
            <div class="form-group">
                <label>Gênero</label>
                <select name="genero">
                    <option value="">Selecione...</option>
                    <option value="M" {{ old('genero', $colaborador->genero ?? '') == 'M' ? 'selected' : '' }}>Masculino
                    </option>
                    <option value="F" {{ old('genero', $colaborador->genero ?? '') == 'F' ? 'selected' : '' }}>Feminino
                    </option>
                    <option value="O" {{ old('genero', $colaborador->genero ?? '') == 'O' ? 'selected' : '' }}>Outro
                    </option>
                </select>
            </div>
            <div class="form-group">
                <label>Escolaridade</label>
                <select name="escolaridade">
                    <option value="">Selecione...</option>
                    <option value="medio"
                        {{ old('escolaridade', $colaborador->escolaridade ?? '') == 'medio' ? 'selected' : '' }}>Ensino
                        Médio</option>
                    <option value="superior"
                        {{ old('escolaridade', $colaborador->escolaridade ?? '') == 'superior' ? 'selected' : '' }}>
                        Ensino Superior</option>
                    <option value="pos"
                        {{ old('escolaridade', $colaborador->escolaridade ?? '') == 'pos' ? 'selected' : '' }}>
                        Pós-Graduação</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>E-mail (Login) *</label>
                <input type="email" name="email" value="{{ old('email', $colaborador->email ?? '') }}" required
                    placeholder="email@empresa.com">
            </div>
            <div class="form-group">
                <label>Senha {{ isset($colaborador) ? '(Deixe em branco para manter)' : '*' }}</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="inputSenhaForm" {{ isset($colaborador) ? '' : 'required' }}
                        placeholder="********">
                    <button type="button" class="toggle-password" onclick="toggleSenhaForm()" title="Mostrar/ocultar senha">
                        <i class="fa-solid fa-eye" id="iconSenhaForm"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label><i class="fa-brands fa-whatsapp" style="color:#25d366;"></i> WhatsApp</label>
                <div class="whatsapp-wrapper">
                    <i class="fa-solid fa-phone whatsapp-flag"></i>
                    <input type="text" name="telefone" id="inputTelefoneForm"
                        value="{{ old('telefone', $colaborador->telefone ?? '') }}"
                        placeholder="(11) 9 9999-9999" maxlength="16">
                </div>
                <small style="color:#888; margin-top:4px;">Opcional — usado para notificações automáticas.</small>
            </div>
            <div class="form-group"></div>
        </div>

        <h3>Dados Contratuais</h3>
        <div class="form-row">
            <div class="form-group">
                <label>Departamento</label>
                <select name="departamento_id">
                    <option value="">Selecione...</option>
                    @foreach($departamentos as $dep)
                    <option value="{{ $dep->id }}"
                        {{ old('departamento_id', $colaborador->departamento_id ?? '') == $dep->id ? 'selected' : '' }}>
                        {{ $dep->nome }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Cargo</label>
                <select name="cargo_id">
                    <option value="">Selecione...</option>
                    @foreach($cargos as $cargo)
                    <option value="{{ $cargo->id }}"
                        {{ old('cargo_id', $colaborador->cargo_id ?? '') == $cargo->id ? 'selected' : '' }}>
                        {{ $cargo->nome }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Perfil de Acesso *</label>
                <select name="perfil_acesso" required>
                    <option value="colaborador"
                        {{ old('perfil_acesso', $colaborador->perfil_acesso ?? '') == 'colaborador' ? 'selected' : '' }}>
                        Colaborador</option>
                    <option value="gestor"
                        {{ old('perfil_acesso', $colaborador->perfil_acesso ?? '') == 'gestor' ? 'selected' : '' }}>
                        Gestor</option>
                    <option value="mediador"
                        {{ old('perfil_acesso', $colaborador->perfil_acesso ?? '') == 'mediador' ? 'selected' : '' }}>
                        Mediador</option>
                    <option value="master"
                        {{ in_array(old('perfil_acesso', $colaborador->perfil_acesso ?? ''), ['master','rh']) ? 'selected' : '' }}>
                        Master (Admin)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Gestor Responsável</label>
                <select name="gestor_id">
                    <option value="">Selecione...</option>
                    @foreach($gestores as $gestor)
                    <option value="{{ $gestor->id }}"
                        {{ old('gestor_id', $colaborador->gestor_id ?? '') == $gestor->id ? 'selected' : '' }}>
                        {{ $gestor->name }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Data de Admissão</label>
                <input type="date" name="data_admissao"
                    value="{{ old('data_admissao', isset($colaborador) && $colaborador->data_admissao ? $colaborador->data_admissao->format('Y-m-d') : '') }}">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="ativo" {{ old('status', $colaborador->status ?? '') == 'ativo' ? 'selected' : '' }}>
                        Ativo</option>
                    <option value="inativo"
                        {{ old('status', $colaborador->status ?? '') == 'inativo' ? 'selected' : '' }}>Inativo</option>
                    <option value="ferias"
                        {{ old('status', $colaborador->status ?? '') == 'ferias' ? 'selected' : '' }}>Férias</option>
                </select>
            </div>
        </div>

        <div style="text-align: right; margin-top: 20px;">
@push('scripts')
<script>
function toggleSenhaForm() {
    const input = document.getElementById('inputSenhaForm');
    const icon  = document.getElementById('iconSenhaForm');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Máscara de telefone: (11) 9 9999-9999
const telForm = document.getElementById('inputTelefoneForm');
if (telForm) {
    telForm.addEventListener('input', function(e) {
        let v = e.target.value.replace(/\D/g, '').slice(0, 11);
        if (v.length > 6) {
            v = v.replace(/(\d{2})(\d)(\d{4})(\d{0,4})/, '($1) $2 $3-$4');
        } else if (v.length > 2) {
            v = v.replace(/(\d{2})(\d*)/, '($1) $2');
        }
        e.target.value = v;
    });
}
</script>
@endpush

            <button type="submit" class="btn-save">
                <i class="fa-solid fa-check"></i> {{ isset($colaborador) ? 'Atualizar Dados' : 'Salvar Cadastro' }}
            </button>
        </div>
    </form>
</div>
@endsection