@extends('layouts.app')
@section('hide_sidebar', '1')

@section('title', 'Crie sua Conta - IntalenTI Vagas')

@push('styles')
<style>
    .register-container {
        max-width: 820px;
        margin: 0 auto;
    }
    .pl-auth-card {
        background: #ffffff;
        border: 1px solid var(--pl-border);
        border-radius: var(--pl-radius-lg);
        padding: 36px 40px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }
    .tipo-card {
        border: 1.5px solid var(--pl-border);
        border-radius: 12px;
        padding: 18px 14px;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        height: 100%;
        user-select: none;
    }
    .tipo-card:hover {
        border-color: #cbd5e1;
        background: #fcfdfd;
        transform: translateY(-2px);
    }
    .tipo-card.active-tipo {
        border-color: var(--pl-green) !important;
        background: var(--pl-green-soft) !important;
        box-shadow: 0 0 0 2px rgba(0, 170, 69, 0.2);
    }
    .tipo-card-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: var(--pl-dark);
        font-size: 1.25rem;
        transition: all 0.2s ease;
    }
    .tipo-card.active-tipo .tipo-card-icon {
        background: var(--pl-green);
        color: #ffffff;
    }
    .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--pl-dark);
        margin-bottom: 6px;
    }
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid var(--pl-border);
        font-size: 0.9rem;
        padding: 9px 13px;
        color: var(--pl-dark);
        background-color: #ffffff;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--pl-green);
        box-shadow: 0 0 0 3px rgba(0, 170, 69, 0.15);
        outline: none;
    }
    .password-toggle-btn {
        background: none;
        border: none;
        cursor: pointer;
        color: #94a3b8;
        padding: 0 12px;
        display: flex;
        align-items: center;
        transition: color 0.15s ease;
    }
    .password-toggle-btn:hover {
        color: var(--pl-dark);
    }
</style>
@endpush

@section('content')
<div class="container py-4 register-container">
    <div class="pl-auth-card">
        <!-- Cabeçalho -->
        <div class="text-center mb-4">
            <span class="pl-portal-badge mb-2 d-inline-flex align-items-center">
                <i class="fa-solid fa-sparkles me-1"></i> Cadastro Gratuito
            </span>
            <h2 class="fw-bold mb-1" style="color: var(--pl-dark); letter-spacing: -0.02em;">Crie sua Conta no Portal</h2>
            <p class="text-muted small">Selecione seu perfil abaixo para personalizar sua experiência</p>
        </div>

        <!-- Seletor Inicial de Perfil -->
        <div class="row g-3 mb-4 justify-content-center" id="tipoSelector">
            <div class="col-12 col-md-4">
                <div class="tipo-card active-tipo" data-tipo="1">
                    <div class="tipo-card-icon">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="color: var(--pl-dark); font-size: 0.95rem;">Candidato</div>
                        <small class="text-muted" style="font-size: 0.78rem;">Buscar vagas e enviar currículo</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="tipo-card" data-tipo="2">
                    <div class="tipo-card-icon">
                        <i class="fa-solid fa-building"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="color: var(--pl-dark); font-size: 0.95rem;">Empresa</div>
                        <small class="text-muted" style="font-size: 0.78rem;">Publicar vagas e triar talentos</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="tipo-card" data-tipo="3">
                    <div class="tipo-card-icon">
                        <i class="fa-solid fa-landmark"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="color: var(--pl-dark); font-size: 0.95rem;">Prefeitura</div>
                        <small class="text-muted" style="font-size: 0.78rem;">Gestão de emprego municipal</small>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('register') }}" id="registerForm">
            @csrf
            <input type="hidden" name="tipo_usuario" id="tipo_usuario" value="{{ old('tipo_usuario', 1) }}">

            <!-- ============================================== -->
            <!-- CAMPOS EXCLUSIVOS: EMPRESA E PREFEITURA        -->
            <!-- ============================================== -->
            <div id="camposEmpresa" class="d-none">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="pl-portal-badge" style="background: var(--pl-green-soft); color: var(--pl-green);">
                        <i class="fa-solid fa-building" id="iconeTipoOrganizacao"></i>
                    </span>
                    <h6 class="fw-bold mb-0" id="tituloTipoOrganizacao" style="color: var(--pl-dark);">Dados da Organização</h6>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label for="cnpj" class="form-label" id="labelCnpj">CNPJ <span class="text-danger">*</span></label>
                        <input type="text" id="cnpj" name="cnpj" value="{{ old('cnpj') }}" class="form-control @error('cnpj') is-invalid @enderror" placeholder="00.000.000/0000-00" maxlength="18">
                        @error('cnpj')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="nome_empresa" class="form-label" id="labelNomeEmpresa">Nome Empresarial / Razão Social <span class="text-danger">*</span></label>
                        <input type="text" id="nome_empresa" name="nome_empresa" value="{{ old('nome_empresa') }}" class="form-control @error('nome_empresa') is-invalid @enderror" placeholder="Razão social completa">
                        @error('nome_empresa')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6" id="divNomeFantasia">
                        <label for="nome_fantasia" class="form-label">Nome Fantasia</label>
                        <input type="text" id="nome_fantasia" name="nome_fantasia" value="{{ old('nome_fantasia') }}" class="form-control" placeholder="Nome comercial ou de exibição">
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="cidade" class="form-label" id="labelCidade">Cidade <span class="text-danger d-none" id="reqCidade">*</span></label>
                        <input type="text" id="cidade" name="cidade" value="{{ old('cidade', 'Maringá') }}" class="form-control @error('cidade') is-invalid @enderror" placeholder="Maringá">
                        @error('cidade')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-2">
                        <label for="estado" class="form-label" id="labelEstado">UF <span class="text-danger d-none" id="reqEstado">*</span></label>
                        <input type="text" id="estado" name="estado" value="{{ old('estado', 'PR') }}" class="form-control @error('estado') is-invalid @enderror" maxlength="2" placeholder="PR">
                        @error('estado')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <hr class="my-4" style="border-color: #f1f5f9;">
            </div>

            <!-- ============================================== -->
            <!-- DADOS DO USUÁRIO / GESTOR / CANDIDATO          -->
            <!-- ============================================== -->
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="pl-portal-badge" style="background: var(--pl-green-soft); color: var(--pl-green);">
                    <i class="fa-solid fa-user"></i>
                </span>
                <h6 class="fw-bold mb-0" id="tituloDadosPessoais" style="color: var(--pl-dark);">Dados Pessoais do Candidato</h6>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                    <label for="name" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Ex: João Silva" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="seu@email.com" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Nacionalidade (Item legado MVC) -->
                <div class="col-12 col-md-4" id="divNacionalidade">
                    <label for="nacionalidade" class="form-label">Nacionalidade</label>
                    <select name="nacionalidade" id="nacionalidade" class="form-select">
                        <option value="Brasileiro(a)" {{ old('nacionalidade') != 'Estrangeiro(a)' ? 'selected' : '' }}>Brasileiro(a)</option>
                        <option value="Estrangeiro(a)" {{ old('nacionalidade') == 'Estrangeiro(a)' ? 'selected' : '' }}>Estrangeiro(a)</option>
                    </select>
                </div>

                <!-- CPF -->
                <div class="col-12 col-md-4" id="divCpf">
                    <label for="cpf" class="form-label">CPF <span class="text-danger" id="reqCpf">*</span></label>
                    <input type="text" id="cpf" name="cpf" value="{{ old('cpf') }}" class="form-control @error('cpf') is-invalid @enderror" placeholder="000.000.000-00" maxlength="14">
                    @error('cpf')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- RNE (Exibido para estrangeiros) -->
                <div class="col-12 col-md-4 d-none" id="divRne">
                    <label for="rne" class="form-label">RNE (Estrangeiro) <span class="text-danger">*</span></label>
                    <input type="text" id="rne" name="rne" value="{{ old('rne') }}" class="form-control @error('rne') is-invalid @enderror" placeholder="Número do RNE">
                    @error('rne')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Data Nascimento -->
                <div class="col-12 col-md-4" id="divDataNascimento">
                    <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                    <input type="date" id="data_nascimento" name="data_nascimento" max="{{ date('Y-m-d') }}" value="{{ old('data_nascimento') }}" class="form-control @error('data_nascimento') is-invalid @enderror">
                    @error('data_nascimento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Celular e Telefone -->
                <div class="col-12 col-md-6" id="divCelular">
                    <label for="celular" class="form-label">Celular / WhatsApp <span class="text-danger">*</span></label>
                    <input type="text" id="celular" name="celular" value="{{ old('celular') }}" class="form-control @error('celular') is-invalid @enderror" placeholder="(00) 90000-0000">
                    @error('celular')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6" id="divTelefone">
                    <label for="telefone" class="form-label">Telefone Fixo / Recado</label>
                    <input type="text" id="telefone" name="telefone" value="{{ old('telefone') }}" class="form-control" placeholder="(00) 0000-0000">
                </div>

                <!-- Senhas com toggle de visibilidade -->
                <div class="col-12 col-md-6">
                    <label for="password" class="form-label">Senha <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Mínimo 6 caracteres" required>
                        <button class="btn btn-outline-secondary password-toggle-btn border" type="button" onclick="togglePass('password', this)" title="Exibir/Ocultar Senha">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="password_confirmation" class="form-label">Confirmar Senha <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Repita a senha" required>
                        <button class="btn btn-outline-secondary password-toggle-btn border" type="button" onclick="togglePass('password_confirmation', this)" title="Exibir/Ocultar Senha">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="alert border-0 d-flex align-items-center gap-2 mt-4 rounded-3" id="avisoCurriculo" style="background: var(--pl-green-soft); color: var(--pl-green);">
                <i class="fa-solid fa-circle-info fs-5"></i>
                <small style="color: var(--pl-dark);">Ao concluir o cadastro como candidato, você poderá montar seu currículo completo com histórico, escolaridade e idiomas no próximo passo.</small>
            </div>

            <button type="submit" class="btn btn-pl-primary w-100 py-3 mt-3 fs-6">
                <i class="fa-solid fa-check-circle me-1"></i> Concluir Cadastro
            </button>
        </form>

        <div class="text-center mt-4 pt-3 border-top">
            <span class="text-muted small">Já possui uma conta?</span>
            <a href="{{ route('login') }}" class="fw-bold ms-1" style="color: var(--pl-green); text-decoration: none;">Faça login</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePass(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const tipoCards = document.querySelectorAll('.tipo-card');
        const inputTipo = document.getElementById('tipo_usuario');
        const camposEmpresa = document.getElementById('camposEmpresa');
        const divNacionalidade = document.getElementById('divNacionalidade');
        const selectNacionalidade = document.getElementById('nacionalidade');
        const divCpf = document.getElementById('divCpf');
        const divRne = document.getElementById('divRne');
        const divDataNasc = document.getElementById('divDataNascimento');
        const divCelular = document.getElementById('divCelular');
        const divTelefone = document.getElementById('divTelefone');
        const tituloDadosPessoais = document.getElementById('tituloDadosPessoais');
        const avisoCurriculo = document.getElementById('avisoCurriculo');
        const divNomeFantasia = document.getElementById('divNomeFantasia');
        const labelNomeEmpresa = document.getElementById('labelNomeEmpresa');
        const inputNomeEmpresa = document.getElementById('nome_empresa');
        const iconeTipoOrganizacao = document.getElementById('iconeTipoOrganizacao');
        const tituloTipoOrganizacao = document.getElementById('tituloTipoOrganizacao');
        const labelCnpj = document.getElementById('labelCnpj');
        const labelCidade = document.getElementById('labelCidade');
        const inputCidade = document.getElementById('cidade');
        const reqCidade = document.getElementById('reqCidade');
        const reqEstado = document.getElementById('reqEstado');

        function atualizarNacionalidade() {
            if (!selectNacionalidade) return;
            if (selectNacionalidade.value === 'Estrangeiro(a)') {
                divRne.classList.remove('d-none');
                document.getElementById('reqCpf').style.display = 'none';
            } else {
                divRne.classList.add('d-none');
                document.getElementById('reqCpf').style.display = 'inline';
            }
        }

        if (selectNacionalidade) {
            selectNacionalidade.addEventListener('change', atualizarNacionalidade);
            atualizarNacionalidade();
        }

        function atualizarTipo(tipo) {
            inputTipo.value = tipo;
            tipoCards.forEach(card => {
                if (card.dataset.tipo == tipo) {
                    card.classList.add('active-tipo');
                } else {
                    card.classList.remove('active-tipo');
                }
            });

            if (tipo == 1) {
                // Candidato
                camposEmpresa.classList.add('d-none');
                divNacionalidade.classList.remove('d-none');
                divCpf.classList.remove('d-none');
                divDataNasc.classList.remove('d-none');
                divCelular.classList.remove('d-none');
                divTelefone.classList.remove('d-none');
                avisoCurriculo.classList.remove('d-none');
                tituloDadosPessoais.textContent = 'Dados Pessoais do Candidato';
                atualizarNacionalidade();
            } else if (tipo == 2) {
                // Empresa
                camposEmpresa.classList.remove('d-none');
                divNacionalidade.classList.add('d-none');
                divCpf.classList.add('d-none');
                divRne.classList.add('d-none');
                divDataNasc.classList.add('d-none');
                divCelular.classList.add('d-none');
                divTelefone.classList.add('d-none');
                avisoCurriculo.classList.add('d-none');
                divNomeFantasia.classList.remove('d-none');

                if (iconeTipoOrganizacao) iconeTipoOrganizacao.className = 'fa-solid fa-building';
                if (tituloTipoOrganizacao) tituloTipoOrganizacao.textContent = 'Dados da Empresa';
                if (labelCnpj) labelCnpj.innerHTML = 'CNPJ da Empresa <span class="text-danger">*</span>';
                if (labelNomeEmpresa) labelNomeEmpresa.innerHTML = 'Razão Social <span class="text-danger">*</span>';
                if (inputNomeEmpresa) inputNomeEmpresa.placeholder = 'Razão social completa';
                if (reqCidade) reqCidade.classList.add('d-none');
                if (reqEstado) reqEstado.classList.add('d-none');
                if (inputCidade && inputCidade.value === '') inputCidade.value = 'Maringá';
                tituloDadosPessoais.textContent = 'Dados do Responsável / Recrutador RH';
            } else if (tipo == 3) {
                // Prefeitura
                camposEmpresa.classList.remove('d-none');
                divNacionalidade.classList.add('d-none');
                divCpf.classList.add('d-none');
                divRne.classList.add('d-none');
                divDataNasc.classList.add('d-none');
                divCelular.classList.add('d-none');
                divTelefone.classList.add('d-none');
                avisoCurriculo.classList.add('d-none');
                divNomeFantasia.classList.add('d-none');

                if (iconeTipoOrganizacao) iconeTipoOrganizacao.className = 'fa-solid fa-landmark';
                if (tituloTipoOrganizacao) tituloTipoOrganizacao.textContent = 'Dados da Prefeitura / Município';
                if (labelCnpj) labelCnpj.innerHTML = 'CNPJ do Município <span class="text-danger">*</span>';
                if (labelNomeEmpresa) labelNomeEmpresa.innerHTML = 'Nome da Prefeitura / Órgão Público <span class="text-danger">*</span>';
                if (inputNomeEmpresa) inputNomeEmpresa.placeholder = 'Ex: Prefeitura Municipal de Sarandi';
                if (reqCidade) reqCidade.classList.remove('d-none');
                if (reqEstado) reqEstado.classList.remove('d-none');
                if (inputCidade && inputCidade.placeholder) inputCidade.placeholder = 'Ex: Sarandi, Londrina, Maringá';
                tituloDadosPessoais.textContent = 'Dados do Gestor Público Municipal';
            }
        }

        tipoCards.forEach(card => {
            card.addEventListener('click', function () {
                atualizarTipo(this.dataset.tipo);
            });
        });

        // Sugestão automática de nome da prefeitura ao digitar a cidade
        if (inputCidade) {
            inputCidade.addEventListener('input', function () {
                if (inputTipo.value == 3) {
                    const c = this.value.trim();
                    if (c.length >= 3 && (!inputNomeEmpresa.value || inputNomeEmpresa.value.startsWith('Prefeitura Municipal de '))) {
                        inputNomeEmpresa.value = 'Prefeitura Municipal de ' + c.charAt(0).toUpperCase() + c.slice(1);
                    }
                }
            });
        }

        // Máscaras de entrada
        const inputCpf = document.getElementById('cpf');
        if (inputCpf) {
            inputCpf.addEventListener('input', function () {
                let v = this.value.replace(/\D/g, '');
                if (v.length > 11) v = v.substring(0, 11);
                if (v.length > 9) {
                    this.value = v.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/, '$1.$2.$3-$4');
                } else if (v.length > 6) {
                    this.value = v.replace(/(\d{3})(\d{3})(\d{1,3})/, '$1.$2.$3');
                } else if (v.length > 3) {
                    this.value = v.replace(/(\d{3})(\d{1,3})/, '$1.$2');
                } else {
                    this.value = v;
                }
            });
        }

        const inputCnpj = document.getElementById('cnpj');
        if (inputCnpj) {
            inputCnpj.addEventListener('input', function () {
                let v = this.value.replace(/\D/g, '');
                if (v.length > 14) v = v.substring(0, 14);
                if (v.length > 12) {
                    this.value = v.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{1,2})/, '$1.$2.$3/$4-$5');
                } else if (v.length > 8) {
                    this.value = v.replace(/(\d{2})(\d{3})(\d{3})(\d{1,4})/, '$1.$2.$3/$4');
                } else if (v.length > 5) {
                    this.value = v.replace(/(\d{2})(\d{3})(\d{1,3})/, '$1.$2.$3');
                } else if (v.length > 2) {
                    this.value = v.replace(/(\d{2})(\d{1,3})/, '$1.$2');
                } else {
                    this.value = v;
                }
            });
        }

        const inputCelular = document.getElementById('celular');
        if (inputCelular) {
            inputCelular.addEventListener('input', function () {
                let v = this.value.replace(/\D/g, '');
                if (v.length > 11) v = v.substring(0, 11);
                if (v.length > 10) {
                    this.value = v.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                } else if (v.length > 6) {
                    this.value = v.replace(/(\d{2})(\d{4})(\d{1,4})/, '($1) $2-$3');
                } else if (v.length > 2) {
                    this.value = v.replace(/(\d{2})(\d{1,5})/, '($1) $2');
                } else {
                    this.value = v;
                }
            });
        }

        // Inicializa com o tipo selecionado
        atualizarTipo(inputTipo.value || 1);
    });
</script>
@endpush
