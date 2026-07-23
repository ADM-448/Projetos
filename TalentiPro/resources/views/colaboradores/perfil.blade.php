@extends(in_array(auth()->user()->perfil_acesso, ['admin', 'master', 'rh']) ? 'layouts.admin' : (auth()->user()->perfil_acesso === 'gestor' ? 'layouts.gestor' : 'layouts.colaborador'))
@section('title', 'Minha Conta')
@section('content')



<div class="page-content">
    
    <div class="profile-container">
        
        <h1 class="page-title-profile">Informações do Colaborador</h1>

        {{-- Feedback --}}
        @if(session('success'))
        <div style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 5px; width:100%;">
            {{ session('success') }}
        </div>
        @endif

        {{-- Formulário --}}
        {{-- Atenção: A rota de update é colaboradores.update, mas isso geralmente exige permissão.
             Se for para o próprio usuário se atualizar, a rota pode ser diferente ou a mesma validando permissions.
             Vou assumir que o usuário pode editar o próprio perfil.
             Se não houver rota específica 'update-profile', usaremos a 'colaboradores.update' passando o ID.
         --}}
        <form class="form-card" action="{{ route('colaboradores.update', $colaborador->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            {{-- Dados Pessoais --}}
            <div class="form-section-title">Dados Pessoais</div>

            <div class="form-row">
                <div class="form-group">
                    <label>Nome do colaborador</label>
                    <input type="text" name="name" value="{{ old('name', $colaborador->name) }}" placeholder="Nome completo" required>
                </div>
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email" value="{{ old('email', $colaborador->email) }}" placeholder="email@empresa.com" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>CPF</label>
                    <input type="text" name="cpf" value="{{ old('cpf', $colaborador->cpf) }}" placeholder="000.000.000-00">
                </div>
                <div class="form-group">
                    <label>Senha (Deixe em branco para manter)</label>
                    <div style="position:relative; display:flex; align-items:center;">
                        <input type="password" name="password" id="inputSenhaPerfil" placeholder="Nova senha" style="flex:1; padding-right:40px;">
                        <button type="button" onclick="toggleSenhaPerfil()" title="Mostrar/ocultar senha"
                            style="position:absolute; right:10px; background:none; border:none; cursor:pointer; color:#999; font-size:1rem; padding:0; line-height:1;">
                            <i class="fa-solid fa-eye" id="iconSenhaPerfil"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fa-brands fa-whatsapp" style="color:#25d366;"></i> WhatsApp</label>
                    <div style="position:relative; display:flex; align-items:center;">
                        <i class="fa-solid fa-phone" style="position:absolute; left:12px; color:#25d366; font-size:1rem; pointer-events:none;"></i>
                        <input type="text" name="telefone" id="inputTelefonePerfil"
                            value="{{ old('telefone', $colaborador->telefone ? preg_replace('/^(\d{2})(\d)(\d{4})(\d{4})$/', '($1) $2 $3-$4', $colaborador->telefone) : '') }}"
                            placeholder="(11) 9 9999-9999" maxlength="16"
                            style="flex:1; padding-left:40px;">
                    </div>
                    <small style="color:#888; margin-top:4px;">Opcional — para notificações automáticas via WhatsApp.</small>
                </div>
                <div class="form-group"></div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Data de Nascimento</label>
                    <input type="date" name="data_nascimento" value="{{ old('data_nascimento', $colaborador->data_nascimento) }}">
                </div>
                <div class="form-group">
                    <label>Gênero</label>
                    <select name="genero">
                        <option value="">Selecione</option>
                        <option value="M" {{ old('genero', $colaborador->genero) == 'M' ? 'selected' : '' }}>Masculino</option>
                        <option value="F" {{ old('genero', $colaborador->genero) == 'F' ? 'selected' : '' }}>Feminino</option>
                        <option value="O" {{ old('genero', $colaborador->genero) == 'O' ? 'selected' : '' }}>Outro</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Escolaridade</label>
                    <select name="escolaridade">
                        <option value="">Selecione</option>
                        <option value="medio" {{ old('escolaridade', $colaborador->escolaridade) == 'medio' ? 'selected' : '' }}>Ensino Médio</option>
                        <option value="superior" {{ old('escolaridade', $colaborador->escolaridade) == 'superior' ? 'selected' : '' }}>Ensino Superior</option>
                        <option value="pos" {{ old('escolaridade', $colaborador->escolaridade) == 'pos' ? 'selected' : '' }}>Pós-Graduação</option>
                    </select>
                </div>
                <div class="form-group"></div>
            </div>

            {{-- Dados Profissionais --}}
            <div class="form-section-title" style="margin-top: 30px;">Dados Profissionais</div>
            
            @php $canEditProfessional = in_array(auth()->user()->perfil_acesso, ['admin', 'master']); @endphp

            <div class="form-row">
                <div class="form-group">
                    <label>Data de Admissão</label>
                    <input type="date" name="data_admissao" value="{{ $colaborador->data_admissao }}" {{ $canEditProfessional ? '' : 'disabled' }}>
                    @if(!$canEditProfessional) <input type="hidden" name="data_admissao" value="{{ $colaborador->data_admissao }}"> @endif
                </div>
                <div class="form-group">
                    <label>Vínculo Empregativo</label>
                    @php
                        $allOptions = ['CLT', 'PJ', 'TEMPORARIO', 'TERCEIRIZADO', 'ESTAGIO', 'APRENDIZ'];
                        $options = ($colaborador->perfil_acesso === 'master') ? ['CLT', 'PJ', 'TERCEIRIZADO'] : $allOptions;
                    @endphp
                    <select name="vinculo" {{ $canEditProfessional ? '' : 'disabled' }}>
                        <option value="">Selecione</option>
                        @foreach($options as $opt)
                            <option value="{{ $opt }}" {{ (old('vinculo', strtoupper($colaborador->vinculo ?? ''))) == $opt ? 'selected' : '' }}>
                                {{ $opt }}
                            </option>
                        @endforeach
                    </select>
                    @if(!$canEditProfessional) <input type="hidden" name="vinculo" value="{{ $colaborador->vinculo }}"> @endif
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Departamento</label>
                    <select name="departamento_id" {{ $canEditProfessional ? '' : 'disabled' }}>
                        <option value="">Selecione</option>
                        @foreach($departamentos as $dept)
                            <option value="{{ $dept->id }}" {{ old('departamento_id', $colaborador->departamento_id) == $dept->id ? 'selected' : '' }}>
                                {{ $dept->nome }}
                            </option>
                        @endforeach
                    </select>
                    @if(!$canEditProfessional) <input type="hidden" name="departamento_id" value="{{ $colaborador->departamento_id }}"> @endif
                </div>
                <div class="form-group">
                    <label>Cargo</label>
                    <select name="cargo_id" {{ $canEditProfessional ? '' : 'disabled' }}>
                         <option value="">Selecione</option>
                        @foreach($cargos as $c)
                            <option value="{{ $c->id }}" {{ old('cargo_id', $colaborador->cargo_id) == $c->id ? 'selected' : '' }}>
                                {{ $c->nome }}
                            </option>
                        @endforeach
                    </select>
                    @if(!$canEditProfessional) <input type="hidden" name="cargo_id" value="{{ $colaborador->cargo_id }}"> @endif
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Superior Direto</label>
                    <select name="gestor_id" {{ $canEditProfessional ? '' : 'disabled' }}>
                        <option value="">Selecione</option>
                        @foreach($gestores as $g)
                            <option value="{{ $g->id }}" {{ old('gestor_id', $colaborador->gestor_id) == $g->id ? 'selected' : '' }}>
                                {{ $g->name }}
                            </option>
                        @endforeach
                    </select>
                    @if(!$canEditProfessional) <input type="hidden" name="gestor_id" value="{{ $colaborador->gestor_id }}"> @endif
                </div>
                <div class="form-group">
                    <label>Perfil de Acesso</label>
                    <input type="text" value="{{ ucfirst($colaborador->perfil_acesso) }}" disabled>
                    {{-- Hidden input para manter valor --}}
                    <input type="hidden" name="perfil_acesso" value="{{ $colaborador->perfil_acesso }}">
                </div>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn-save">Salvar</button>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleSenhaPerfil() {
    const input = document.getElementById('inputSenhaPerfil');
    const icon  = document.getElementById('iconSenhaPerfil');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Máscara de telefone
const telPerfil = document.getElementById('inputTelefonePerfil');
if (telPerfil) {
    telPerfil.addEventListener('input', function(e) {
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