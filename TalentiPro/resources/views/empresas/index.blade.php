@extends('layouts.admin')

@section('title', 'Gestão de Empresas')

@section('content')
    <div class="header-actions">
        <h1 class="page-title">Gestão de Empresas</h1>
        <button class="btn-primary" onclick="abrirModalCriar()">
            <i class="fa-solid fa-plus"></i> Nova Empresa
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            @foreach($errors->all() as $error)
                <p style="margin: 0;">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <table class="colab-list" style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <thead>
            <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 15px; text-align: left;">Nome</th>
                <th style="padding: 15px; text-align: left;">CNPJ</th>
                <th style="padding: 15px; text-align: left;">Email</th>
                <th style="padding: 15px; text-align: left;">Telefone</th>
                <th style="padding: 15px; text-align: center;">Usuários</th>
                <th style="padding: 15px; text-align: center;">Status</th>
                <th style="padding: 15px; text-align: center;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($empresas as $empresa)
                <tr style="border-bottom: 1px solid #dee2e6;">
                    <td style="padding: 15px;">
                        <strong>{{ $empresa->nome }}</strong>
                    </td>
                    <td style="padding: 15px;">{{ $empresa->cnpj ?? '-' }}</td>
                    <td style="padding: 15px;">
                        @if($empresa->users->isNotEmpty())
                            {{ $empresa->users->first()->email }}
                        @else
                            <span style="color: #999;">Sem Master</span>
                        @endif
                    </td>
                    <td style="padding: 15px;">{{ $empresa->telefone ?? '-' }}</td>
                    <td style="padding: 15px; text-align: center;">
                        <span style="background-color: #e3f2fd; color: #1976d2; padding: 5px 10px; border-radius: 12px; font-size: 12px;">
                            {{ $empresa->users_count }} usuários
                        </span>
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        @if($empresa->status === 'ativa')
                            <span style="background-color: #d4edda; color: #155724; padding: 5px 15px; border-radius: 12px; font-size: 12px;">
                                <i class="fa-solid fa-check-circle"></i> Ativada
                            </span>
                        @else
                            <span style="background-color: #f8d7da; color: #721c24; padding: 5px 15px; border-radius: 12px; font-size: 12px;">
                                <i class="fa-solid fa-times-circle"></i> Suspensa
                            </span>
                        @endif
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        <a href="{{ route('whatsapp.config', ['empresa_id' => $empresa->id]) }}" class="btn-icon" style="background: #25d366; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; margin-right: 5px; display: inline-block; text-decoration: none; vertical-align: middle;" title="Configurar WhatsApp">
                            <i class="fa-brands fa-whatsapp"></i>
                        </a>

                        <button onclick="editarEmpresa({{ json_encode($empresa) }})" class="btn-icon" style="background: #3498db; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; margin-right: 5px; vertical-align: middle;" title="Editar">
                            <i class="fa-solid fa-edit"></i>
                        </button>
                        
                        <form action="{{ route('empresas.toggle', $empresa) }}" method="POST" style="display: inline; vertical-align: middle;">
                            @csrf
                            <button type="submit" class="btn-icon" style="background: {{ $empresa->status === 'ativa' ? '#f39c12' : '#27ae60' }}; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; margin-right: 5px;" title="{{ $empresa->status === 'ativa' ? 'Suspender' : 'Ativar' }}">
                                <i class="fa-solid fa-{{ $empresa->status === 'ativa' ? 'ban' : 'check' }}"></i>
                            </button>
                        </form>

                        <form action="{{ route('empresas.destroy', $empresa) }}" method="POST" style="display: inline; vertical-align: middle;" class="confirm-delete" data-confirm-message="⚠️ ATENÇÃO! Você está prestes a deletar a empresa {{ $empresa->nome }}. {{ $empresa->users_count }} usuário(s) vinculado(s) também será(ão) DELETADO(S) permanentemente! Deseja continuar?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-icon" style="background: #e74c3c; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer;" title="Excluir">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="padding: 40px; text-align: center; color: #999;">
                        <i class="fa-solid fa-building" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                        Nenhuma empresa cadastrada
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>


{{-- MODAL CRIAR/EDITAR --}}
<div id="modalEmpresa" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: white; padding: 30px; border-radius: 8px; width: 90%; max-width: 500px; position: relative;">
        <span class="close-modal" onclick="fecharModal()" style="position: absolute; top: 15px; right: 20px; font-size: 28px; cursor: pointer; color: #999;">&times;</span>
        <h2 id="modalTitle" style="margin-bottom: 20px;">Nova Empresa</h2>
        
        <form id="formEmpresa" action="{{ route('empresas.store') }}" method="POST">
            @csrf
            <input type="hidden" id="formMethod" name="_method" value="POST">
            
            <div class="modal-group" style="margin-bottom: 20px;">
                <label class="modal-label" style="display: block; margin-bottom: 5px; font-weight: 600;">Nome da Empresa *</label>
                <input type="text" name="nome" id="inputNome" class="modal-input" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" required>
            </div>

            <div class="modal-group" style="margin-bottom: 20px;">
                <label class="modal-label" style="display: block; margin-bottom: 5px; font-weight: 600;">CNPJ</label>
                <input type="text" name="cnpj" id="inputCnpj" class="modal-input" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" placeholder="00.000.000/0000-00">
            </div>

            <hr style="margin: 25px 0; border: none; border-top: 2px solid #e0e0e0;">
            <h3 style="margin-bottom: 15px; color: #555; font-size: 16px;">👤 Usuário Master (Administrador)</h3>

            <div class="modal-group" style="margin-bottom: 20px;">
                <label class="modal-label" style="display: block; margin-bottom: 5px; font-weight: 600;">Nome do Master *</label>
                <input type="text" name="master_name" id="inputMasterName" class="modal-input" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" required>
            </div>

            <div class="modal-group" style="margin-bottom: 20px;">
                <label class="modal-label" style="display: block; margin-bottom: 5px; font-weight: 600;">Email (Login) *</label>
                <input type="email" name="master_email" id="inputMasterEmail" class="modal-input" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" placeholder="master@empresa.com" required>
            </div>

            <div class="modal-group" style="margin-bottom: 20px;">
                <label class="modal-label" style="display: block; margin-bottom: 5px; font-weight: 600;">Senha *</label>
                <div style="position: relative;">
                    <input type="password" name="master_password" id="inputMasterPassword" class="modal-input" style="width: 100%; padding: 10px 40px 10px 10px; border: 1px solid #ddd; border-radius: 4px;" placeholder="Mínimo 8 caracteres" required minlength="8">
                    <button type="button" onclick="togglePassword()" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666; font-size: 18px;">
                        <i id="togglePasswordIcon" class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-modal-save" style="width: 100%; padding: 12px; background-color: #27ae60; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer;">
                Criar Empresa e Master
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function abrirModalCriar() {
        document.getElementById('modalEmpresa').style.display = 'flex';
        document.getElementById('modalTitle').innerText = 'Nova Empresa';
        document.getElementById('formEmpresa').action = '{{ route("empresas.store") }}';
        document.getElementById('formMethod').value = 'POST';
        
        // Reset inputs
        document.getElementById('inputNome').value = '';
        document.getElementById('inputCnpj').value = '';
        document.getElementById('inputMasterName').value = '';
        document.getElementById('inputMasterEmail').value = '';
        document.getElementById('inputMasterPassword').value = '';
        
        // Show master fields
        document.querySelectorAll('.modal-group').forEach(el => el.style.display = 'block');
        document.querySelector('hr').style.display = 'block';
        document.querySelector('h3').style.display = 'block';
    }

    function editarEmpresa(empresa) {
        document.getElementById('modalEmpresa').style.display = 'flex';
        document.getElementById('modalTitle').innerText = 'Editar Empresa';
        
        let url = '{{ route("empresas.update", ":id") }}';
        url = url.replace(':id', empresa.id);
        
        document.getElementById('formEmpresa').action = url;
        document.getElementById('formMethod').value = 'PUT';

        document.getElementById('inputNome').value = empresa.nome;
        document.getElementById('inputCnpj').value = empresa.cnpj || '';
    }

    function fecharModal() {
        document.getElementById('modalEmpresa').style.display = 'none';
    }

    window.onclick = function (event) {
        if (event.target.id === 'modalEmpresa') {
            fecharModal();
        }
    }

    // Máscara de CNPJ com limite de 14 dígitos
    document.getElementById('inputCnpj').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
        
        // Limita a 14 dígitos
        if (value.length > 14) {
            value = value.substring(0, 14);
        }
        
        // Aplica a máscara: 00.000.000/0000-00
        if (value.length > 12) {
            value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
        } else if (value.length > 8) {
            value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{1,4})/, '$1.$2.$3/$4');
        } else if (value.length > 5) {
            value = value.replace(/^(\d{2})(\d{3})(\d{1,3})/, '$1.$2.$3');
        } else if (value.length > 2) {
            value = value.replace(/^(\d{2})(\d{1,3})/, '$1.$2');
        }
        
        e.target.value = value;
    });

    // Validação matemática do CNPJ
    function validarCNPJ(cnpj) {
        cnpj = cnpj.replace(/\D/g, ''); // Remove formatação
        
        if (cnpj.length !== 14) return false;
        
        // Elimina CNPJs inválidos conhecidos
        if (/^(\d)\1+$/.test(cnpj)) return false;
        
        // Valida DVs (Dígitos Verificadores)
        let tamanho = cnpj.length - 2;
        let numeros = cnpj.substring(0, tamanho);
        let digitos = cnpj.substring(tamanho);
        let soma = 0;
        let pos = tamanho - 7;
        
        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) pos = 9;
        }
        
        let resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        if (resultado != digitos.charAt(0)) return false;
        
        tamanho = tamanho + 1;
        numeros = cnpj.substring(0, tamanho);
        soma = 0;
        pos = tamanho - 7;
        
        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) pos = 9;
        }
        
        resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        if (resultado != digitos.charAt(1)) return false;
        
        return true;
    }

    // Adiciona validação ao submeter o formulário
    document.getElementById('formEmpresa').addEventListener('submit', function(e) {
        const cnpjInput = document.getElementById('inputCnpj');
        const cnpjValue = cnpjInput.value;
        
        // Se CNPJ foi preenchido, valida
        if (cnpjValue && cnpjValue.trim() !== '') {
            if (!validarCNPJ(cnpjValue)) {
                e.preventDefault();
                alert('❌ CNPJ inválido! Por favor, verifique os dígitos informados.');
                cnpjInput.focus();
                return false;
            }
        }
    });

    // Função para mostrar/ocultar senha
    function togglePassword() {
        const passwordInput = document.getElementById('inputMasterPassword');
        const toggleIcon = document.getElementById('togglePasswordIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }
</script>
@endpush
@endsection
