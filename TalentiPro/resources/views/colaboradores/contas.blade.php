@extends('layouts.admin')
@section('title', 'Gerenciar Contas')
@section('content')

@push('styles')
<style>
    /* Estilos do contas.html */
    .header-actions {
        display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;
    }
    .page-title {
        font-size: 24px; color: #333; font-weight: bold;
    }
    .btn-add-account {
        background-color: #3b5998; color: white; padding: 10px 25px; border: none; border-radius: 5px;
        cursor: pointer; font-weight: bold; display: flex; align-items: center; gap: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .btn-add-account:hover { background-color: #2d4373; }

    /* Tabela */
    .table-container {
        background: white; border-radius: 10px; padding: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: 1px solid #e0e0e0;
        overflow-x: auto;
    }
    .accounts-table { width: 100%; border-collapse: collapse; }
    .accounts-table th {
        text-align: left; padding: 15px; border-bottom: 2px solid #eee;
        color: #333; font-weight: bold; background-color: #f9f9f9;
    }
    .accounts-table td {
        padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; color: #555;
    }

    /* Badges */
    .access-badge {
        padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; text-transform: uppercase;
    }
    .badge-master { background-color: #6a4c7c; color: white; }
    .badge-admin { background-color: #3b5998; color: white; }
    .badge-colaborador { background-color: #eee; color: #555; } /* Ajustado nome da classe */
    .badge-mediador { background-color: #27ae60; color: white; }
    .badge-gestor { background-color: #e67e22; color: white; }
    .badge-rh { background-color: #3498db; color: white; }

    .action-icon {
        cursor: pointer; font-size: 16px; margin: 0 5px; color: #555; transition: 0.2s;
    }
    .action-icon:hover { color: #3b5998; transform: scale(1.1); }

    /* Modal */
    .modal-overlay {
        display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(0, 0, 0, 0.5); z-index: 2000;
        justify-content: center; align-items: center;
    }
    .modal-content {
        background-color: white; padding: 40px; border-radius: 10px;
        width: 800px; max-width: 95%; position: relative; animation: fadeIn 0.3s;
    }
    .modal-row { display: flex; gap: 30px; margin-bottom: 20px; }
    .modal-group { flex: 1; display: flex; flex-direction: column; }
    .modal-label { font-weight: bold; margin-bottom: 8px; color: #444; font-size: 14px; }
    .modal-input, .modal-select {
        padding: 10px; border: 1px solid #999; border-radius: 5px; font-size: 14px; outline: none; width: 100%;
    }
    .modal-input:focus, .modal-select:focus { border-color: #3b5998; }
    
    .radio-group { display: flex; gap: 20px; align-items: center; margin-top: 5px; }
    .radio-item { display: flex; align-items: center; gap: 5px; cursor: pointer; }
    
    .btn-modal-save {
        background-color: #3b5998; color: white; padding: 12px 0; border: none; width: 100%;
        border-radius: 5px; font-weight: bold; margin-top: 20px; cursor: pointer; font-size: 16px;
    }
    .close-modal {
        position: absolute; top: 15px; right: 20px; font-size: 24px; cursor: pointer; color: #999;
    }
    
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

    /* Olho na senha e WhatsApp */
    .input-icon-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }
    .input-icon-wrapper .modal-input { flex: 1; }
    .input-icon-wrapper .modal-input.has-left-icon { padding-left: 38px; }
    .input-icon-wrapper .modal-input.has-right-icon { padding-right: 40px; }
    .icon-left {
        position: absolute;
        left: 11px;
        color: #25d366;
        font-size: 15px;
        pointer-events: none;
    }
    .icon-right {
        position: absolute;
        right: 11px;
        cursor: pointer;
        color: #aaa;
        font-size: 15px;
        background: none;
        border: none;
        padding: 0;
        transition: color .2s;
    }
    .icon-right:hover { color: #3b5998; }

    /* Badge WhatsApp na tabela */
    .wpp-link {
        display: inline-flex; align-items: center; gap: 5px;
        color: #25d366; font-weight: 600; font-size: 13px;
        text-decoration: none;
    }
    .wpp-link:hover { color: #128c4e; }
    .wpp-none { color: #ccc; font-size: 13px; }
</style>
@endpush

<div class="page-content">

    {{-- Feedback Messages --}}
    @if(session('success'))
    <div style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 5px;">
        {{ session('success') }}
    </div>
    @endif
    
    @if($errors->any())
    <div style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 5px;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="header-actions">
        <h1 class="page-title">Contas</h1>
        <div style="display: flex; gap: 10px;">
            <button class="btn-add-account" onclick="abrirModalImportar()" style="background-color: #27ae60;">
                <i class="fa-solid fa-file-csv"></i> Importar
            </button>
            <button class="btn-add-account" onclick="abrirModalCriar()">
                <i class="fa-solid fa-plus"></i> Conta
            </button>
        </div>
    </div>

    <div class="table-container">
        <table class="accounts-table">
            <thead>
                <tr>
                    <th width="25%">Nome</th>
                    <th width="22%">E-mail</th>
                    <th width="13%">CPF</th>
                    <th width="15%">WhatsApp</th>
                    <th width="13%">Tipo de acesso</th>
                    <th width="6%" style="text-align: center;">Editar</th>
                    <th width="6%" style="text-align: center;">Excluir</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td data-label="Nome"><strong>{{ $user->name }}</strong></td>
                    <td data-label="E-mail">{{ $user->email }}</td>
                    <td data-label="CPF">{{ $user->cpf ?? '-' }}</td>
                    <td data-label="WhatsApp">
                        @if($user->telefone)
                            @php
                                $numWpp = preg_replace('/\D/', '', $user->telefone);
                                // Garante DDI 55 para links
                                $wppLink = strlen($numWpp) <= 11 ? '55' . $numWpp : $numWpp;
                            @endphp
                            <a href="https://wa.me/{{ $wppLink }}" target="_blank" class="wpp-link">
                                <i class="fa-brands fa-whatsapp"></i> {{ $user->telefone }}
                            </a>
                        @else
                            <span class="wpp-none"><i class="fa-solid fa-minus"></i></span>
                        @endif
                    </td>
                    <td data-label="Acesso">
                        @php
                            $badgeClass = 'badge-colaborador';
                            switch($user->perfil_acesso) {
                                case 'master': $badgeClass = 'badge-master'; break;
                                case 'admin': $badgeClass = 'badge-admin'; break;
                                 case 'mediador': $badgeClass = 'badge-mediador'; break;
                                case 'gestor': $badgeClass = 'badge-gestor'; break;
                                case 'rh': $badgeClass = 'badge-rh'; break;
                            }
                        @endphp
                        <span class="access-badge {{ $badgeClass }}">{{ ucfirst($user->perfil_acesso) }}</span>
                    </td>
                    <td data-label="Editar" style="text-align: center;">
                        <i class="fa-solid fa-pen action-icon" onclick="editarConta({{ json_encode($user) }})"></i>
                    </td>
                    <td data-label="Excluir" style="text-align: center;">
                        <form action="{{ route('colaboradores.destroy', $user->id) }}" method="POST" class="confirm-delete" data-confirm-message="Deseja excluir o usuário {{ $user->name }}?" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background:none; border:none; padding:0;">
                                <i class="fa-solid fa-trash action-icon" style="color:#c0392b;"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="margin-top: 20px;">
            {{ $users->links() }}
        </div>
    </div>
</div>

{{-- MODAL CONTA --}}
<div id="modalConta" class="modal-overlay">
    <div class="modal-content">
        <span class="close-modal" onclick="fecharModal()">&times;</span>
        <h2 id="modalTitle" style="margin-bottom:20px;">Nova Conta</h2>

        <form id="formConta" action="{{ route('colaboradores.store') }}" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            
            <div class="modal-row">
                <div class="modal-group">
                    <label class="modal-label">Nome</label>
                    <input type="text" name="name" id="inputName" class="modal-input" required>
                </div>
                <div class="modal-group">
                    <label class="modal-label">E-mail</label>
                    <input type="email" name="email" id="inputEmail" class="modal-input" placeholder="email@empresa.com" required>
                </div>
            </div>

            <div class="modal-row">
                <div class="modal-group">
                    <label class="modal-label">Senha</label>
                    <div class="input-icon-wrapper">
                        <input type="password" name="password" id="inputPassword" class="modal-input has-right-icon" placeholder="****">
                        <button type="button" class="icon-right" onclick="toggleSenhaModal()" title="Mostrar/ocultar senha">
                            <i class="fa-solid fa-eye" id="iconSenhaModal"></i>
                        </button>
                    </div>
                    <small style="color:#666;" id="passwordHelp">Obrigatório para novos usuários.</small>
                </div>
                <div class="modal-group">
                    <label class="modal-label">CPF</label>
                    <input type="text" name="cpf" id="inputCpf" class="modal-input" placeholder="000.000.000-00">
                </div>
            </div>

            <div class="modal-row">
                <div class="modal-group">
                    <label class="modal-label"><i class="fa-brands fa-whatsapp" style="color:#25d366;"></i> WhatsApp</label>
                    <div class="input-icon-wrapper">
                        <i class="fa-solid fa-phone icon-left"></i>
                        <input type="text" name="telefone" id="inputTelefone" class="modal-input has-left-icon" placeholder="(11) 9 9999-9999" maxlength="16">
                    </div>
                    <small style="color:#888;">Opcional — para notificações automáticas.</small>
                </div>
                <div class="modal-group">
                    <label class="modal-label">Nível de acesso</label>
                    <select name="perfil_acesso" id="selectPerfil" class="modal-select" required>
                        <option value="">Selecione um nível</option>
                        <option value="master">Master</option>
                        <option value="colaborador">Colaborador</option>
                        <option value="mediador">Mediador</option>
                        <option value="gestor">Gestor</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-modal-save">Salvar</button>
        </form>
    </div>
</div>

{{-- MODAL IMPORTAR --}}
<div id="modalImportar" class="modal-overlay">
    <div class="modal-content" style="width: 900px;">
        <span class="close-modal" onclick="fecharModalImportar()">&times;</span>
        <h2 style="margin-bottom:20px;">Importar Usuários em Massa</h2>
        
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #ddd; font-size: 14px; color: #444;">
            <p><strong>Como funciona:</strong></p>
            <ul style="margin-left: 20px; list-style-type: disc; margin-bottom: 15px;">
                <li>O sistema usa o <strong>E-mail</strong> para identificar o usuário. Se o e-mail já existir, os dados serão atualizados.</li>
                <li>Se o <strong>Departamento</strong> ou <strong>Cargo</strong> não existirem, eles serão criados automaticamente.</li>
                <li>Senha padrão para novos usuários (se deixar em branco): <code>mudar123</code>.</li>
            </ul>

            <p><strong>Níveis de Acesso (Preencha na coluna 'perfil'):</strong></p>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-top: 5px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="access-badge badge-master">MASTER</span> 
                    <span>Acesso total ao sistema.</span>
                </div>
                <!-- Admin removido: Apenas o Super Admin pode existir -->
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="access-badge badge-gestor">GESTOR</span> 
                    <span>Visualiza equipe e aprova.</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="access-badge badge-mediador">MEDIADOR</span> 
                    <span>Mediação de Avaliações.</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="access-badge badge-colaborador">COLABORADOR</span> 
                    <span>Acesso restrito (padrão).</span>
                </div>
            </div>
            <p style="margin-top:10px; font-size: 12px; color: #666; font-style: italic;">* Se o perfil não for informado corretamente, será definido como 'colaborador'. O perfil 'Admin' não pode ser importado.</p>
        </div>

        <p style="margin-bottom: 10px; color: #333;">
            1. Baixe a planilha modelo abaixo.<br>
            2. Preencha os dados seguindo as colunas.<br>
            3. <strong>IMPORTANTE:</strong> Salve como <strong>CSV (separado por ponto-e-vírgula)</strong>.<br>
            <small style="color: #e74c3c;">⚠️ Arquivos Excel (.xlsx, .xls) não são suportados. Use apenas CSV.</small>
        </p>

        <button type="button" class="btn-add-account" style="background-color: #7f8c8d; margin-bottom: 20px;" onclick="baixarModelo()">
            <i class="fa-solid fa-download"></i> Baixar Planilha Modelo
        </button>

        <form action="{{ route('colaboradores.importar') }}" method="POST" enctype="multipart/form-data" style="border-top: 1px solid #eee; padding-top: 20px;">
            @csrf
            <div class="modal-group" style="margin-bottom: 20px;">
                <label class="modal-label">Selecione o arquivo CSV</label>
                <input type="file" name="arquivo" accept=".csv,.txt" class="modal-input" required>
                <small style="color: #666; display: block; margin-top: 5px;">Apenas arquivos .csv ou .txt</small>
            </div>

            <button type="submit" class="btn-modal-save" style="background-color: #27ae60;">
                <i class="fa-solid fa-upload"></i> Importar Dados
            </button>
        </form>
    </div>
    </div>
</div>

{{-- MODAL ERROS DE IMPORTAÇÃO --}}
<div id="modalImportErrors" class="modal-overlay">
    <div class="modal-content" style="width: 600px; border-top: 5px solid #f39c12;">
        <span class="close-modal" onclick="fecharModalImportErrors()">&times;</span>
        <h2 style="margin-bottom:20px; color: #e67e22;">
            <i class="fa-solid fa-triangle-exclamation"></i> Atenção na Importação
        </h2>
        
        <p style="margin-bottom: 15px;">A importação foi concluída, mas <strong>alguns usuários foram pulados</strong> porque já possuem cadastro no sistema (E-mail ou CPF duplicado globalmente).</p>

        <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; border: 1px solid #ffeeba; max-height: 300px; overflow-y: auto;">
            <ul id="listaErrosImportacao" style="margin-left: 20px; list-style-type: disc;">
                <!-- Itens serão inseridos via JS -->
            </ul>
        </div>

        <button onclick="fecharModalImportErrors()" class="btn-modal-save" style="background-color: #f39c12; margin-top: 20px;">
            Entendido
        </button>
    </div>
</div>

@push('scripts')
<script>
    function fecharModal() {
        document.getElementById('modalConta').style.display = 'none';
    }

    function abrirModalImportar() {
        document.getElementById('modalImportar').style.display = 'flex';
    }
    
    function fecharModalImportar() {
        document.getElementById('modalImportar').style.display = 'none';
    }

    function baixarModelo() {
        const headers = ['nome', 'email', 'cpf', 'telefone', 'perfil', 'cargo', 'departamento', 'senha'];
        const rows = [
            ['Exemplo Silva', 'exemplo@empresa.com', '000.000.000-00', '(11) 9 9999-9999', 'colaborador', 'Analista', 'TI', '123456']
        ];
        
        let csvContent = "data:text/csv;charset=utf-8," 
            + headers.join(";") + "\n" 
            + rows.map(e => e.join(";")).join("\n");
            
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "modelo_importacao.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function abrirModalCriar() {
        document.getElementById('modalConta').style.display = 'flex';
        document.getElementById('modalTitle').innerText = 'Nova Conta';
        document.getElementById('formConta').action = '{{ route("colaboradores.store") }}';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('passwordHelp').innerText = 'Obrigatório.';
        
        // Reset inputs
        document.getElementById('inputName').value = '';
        document.getElementById('inputEmail').value = '';
        document.getElementById('inputPassword').value = '';
        document.getElementById('inputCpf').value = '';
        document.getElementById('inputTelefone').value = '';
        document.getElementById('selectPerfil').value = '';
        // Garante que senha volta para tipo password
        document.getElementById('inputPassword').type = 'password';
        document.getElementById('iconSenhaModal').classList.replace('fa-eye-slash','fa-eye');
    }

    function editarConta(user) {
        document.getElementById('modalConta').style.display = 'flex';
        document.getElementById('modalTitle').innerText = 'Editar Conta';
        
        let url = '{{ route("colaboradores.update", ":id") }}';
        url = url.replace(':id', user.id);
        
        document.getElementById('formConta').action = url;
        document.getElementById('formMethod').value = 'PUT'; 
        
        document.getElementById('passwordHelp').innerText = 'Deixe em branco para manter a atual.';

        document.getElementById('inputName').value = user.name;
        document.getElementById('inputEmail').value = user.email;
        document.getElementById('inputPassword').value = ''; 
        document.getElementById('inputCpf').value = user.cpf || '';
        document.getElementById('inputTelefone').value = user.telefone || '';
        document.getElementById('selectPerfil').value = user.perfil_acesso;
        // Garante que senha volta para tipo password
        document.getElementById('inputPassword').type = 'password';
        document.getElementById('iconSenhaModal').classList.replace('fa-eye-slash','fa-eye');
    }

    function fecharModalImportErrors() {
        document.getElementById('modalImportErrors').style.display = 'none';
    }

    // Verifica se há erros de importação na sessão e abre o modal
    document.addEventListener('DOMContentLoaded', function() {
        // Máscara de CPF
        const inputCpf = document.getElementById('inputCpf');
        if (inputCpf) {
            inputCpf.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, ''); // Remove tudo o que não é dígito
                if (value.length > 11) value = value.slice(0, 11); // Limita a 11 dígitos
                
                // Formata o CPF (XXX.XXX.XXX-XX)
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                
                e.target.value = value;
            });
        }

        @if(session('import_errors'))
            const erros = @json(session('import_errors'));
            const lista = document.getElementById('listaErrosImportacao');
            
            erros.forEach(erro => {
                const li = document.createElement('li');
                li.style.marginBottom = "5px";
                li.innerHTML = erro;
                lista.appendChild(li);
            });
            
            document.getElementById('modalImportErrors').style.display = 'flex';
        @endif

        // Máscara CPF no modal
        const inputCpf = document.getElementById('inputCpf');
        if (inputCpf) {
            inputCpf.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 11) value = value.slice(0, 11);
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                e.target.value = value;
            });
        }

        // Máscara telefone no modal
        const inputTel = document.getElementById('inputTelefone');
        if (inputTel) {
            inputTel.addEventListener('input', function(e) {
                let v = e.target.value.replace(/\D/g, '').slice(0, 11);
                if (v.length > 6) {
                    v = v.replace(/(\d{2})(\d)(\d{4})(\d{0,4})/, '($1) $2 $3-$4');
                } else if (v.length > 2) {
                    v = v.replace(/(\d{2})(\d*)/, '($1) $2');
                }
                e.target.value = v;
            });
        }
    });

    function toggleSenhaModal() {
        const input = document.getElementById('inputPassword');
        const icon  = document.getElementById('iconSenhaModal');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    window.onclick = function (event) {
        if (event.target == document.getElementById('modalConta')) {
            fecharModal();
        }
        if (event.target == document.getElementById('modalImportar')) {
            fecharModalImportar();
        }
        if (event.target == document.getElementById('modalImportErrors')) {
            fecharModalImportErrors();
        }
    }
</script>
@endpush

@endsection
