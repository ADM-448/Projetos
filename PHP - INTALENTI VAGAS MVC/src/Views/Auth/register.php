<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Crie sua Conta</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>lib/css/styles.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .campo-error-validacao {
            color: var(--danger-color);
            font-size: 0.85em;
            font-weight: 500;
            margin-top: 5px;
            display: block;
        }

        input.invalido {
            border-color: var(--danger-color) !important;
            box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.2) !important;
        }

        /* Estilos dos Botões de Tipo de Cadastro */
        .cadastro-options {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            justify-content: center;
        }

        .cadastro-btn {
            flex: 1;
            padding: 20px 10px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            background: #fff;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            font-weight: 600;
            color: #555;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .cadastro-btn:hover {
            border-color: var(--primary-color, #007bff);
            background: #f8f9fa;
            transform: translateY(-2px);
        }

        .cadastro-btn.active {
            border-color: var(--primary-color, #007bff);
            background: rgba(0, 123, 255, 0.05);
            color: var(--primary-color, #007bff);
            box-shadow: 0 4px 10px rgba(0, 123, 255, 0.15);
        }

        .cadastro-btn i {
            font-size: 28px;
            margin-bottom: 5px;
        }

        /* --- Custom Searchable Select (Municípios) --- */
        .custom-select-wrapper {
            position: relative;
            display: none;
            /* Oculto por padrão */
        }

        .select-trigger {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 12px 15px;
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            font-family: inherit;
            color: #555;
            user-select: none;
        }

        .select-trigger::after {
            content: '\25BC';
            font-size: 0.8em;
            color: #999;
        }

        .select-dropdown {
            position: absolute;
            top: 105%;
            left: 0;
            width: 100%;
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            z-index: 100;
            display: none;
            overflow: hidden;
        }

        .select-dropdown.open {
            display: block;
        }

        .select-search {
            padding: 10px;
            border-bottom: 1px solid #eee;
            background: #FAFAFA;
        }

        .select-search input {
            width: 100%;
            padding: 8px 10px 8px 35px;
            /* space for icon */
            border: 1px solid #ddd;
            border-radius: 4px;
            outline: none;
            font-size: 0.9em;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' style='fill:rgba(0,0,0,0.4);transform:;'%3E%3Cpath d='M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 10px center;
        }

        .select-options {
            max-height: 250px;
            overflow-y: auto;
        }

        .select-option {
            padding: 10px 15px;
            cursor: pointer;
            transition: background 0.2s;
            border-bottom: 1px solid #f9f9f9;
        }

        .select-option:hover {
            background-color: #f1f1f1;
            color: var(--primary-color, #581d22);
        }

        .select-option.selected {
            background-color: #e9ecef;
            font-weight: 600;
        }

        .select-option.no-result {
            padding: 15px;
            text-align: center;
            color: #999;
            cursor: default;
        }
    </style>
</head>

<body>
    <main class="container">
        <form id="form-cadastro" action="<?= BASE_URL ?>auth/store" method="POST" enctype="multipart/form-data">

            <h1>Crie sua Conta</h1>
            <p class="subtitle">Comece sua jornada em nossa plataforma.</p>

            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px;">
                    <?= htmlspecialchars($_SESSION['flash_error']) ?>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <!-- Seleção de Tipo de Conta (Botões) -->
            <label style="display:block; margin-bottom:15px; font-weight:600; font-size: 1.1em; color: #333;">Selecione o tipo de conta:</label>

            <div class="cadastro-options">
                <div class="cadastro-btn" onclick="selectTipo('candidato', this)">
                    <i class='bx bx-user'></i>
                    <span>Candidato</span>
                </div>
                <div class="cadastro-btn" onclick="selectTipo('empresa', this)">
                    <i class='bx bx-briefcase'></i>
                    <span>Empresa</span>
                </div>
                <div class="cadastro-btn" onclick="selectTipo('prefeitura', this)">
                    <i class='bx bxs-institution'></i>
                    <span>Prefeitura</span>
                </div>
            </div>

            <!-- Input Oculto para manter a lógica original -->
            <input type="hidden" name="tipo_cadastro" id="tipo_cadastro">

            <div id="campos_candidato" style="display: none;">
                <fieldset>
                    <legend>Dados do Candidato</legend>
                    <div class="input-box">
                        <label for="cand_name">Nome Completo</label>
                        <input type="text" id="cand_name" name="cand_name" placeholder="Seu nome completo">
                    </div>
                    <div class="input-box">
                        <label for="cand_email">Email</label>
                        <input type="email" id="cand_email" name="cand_email" placeholder="Seu melhor e-mail">
                    </div>
                    <div class="input-box">
                        <label for="cpf">CPF</label>
                        <input type="text" name="cpf" id="cpf" placeholder="Digite seu CPF (apenas números)">
                        <span id="cpf-error" class="campo-error-validacao"></span>
                    </div>
                    <div class="input-box">
                        <label for="cand_data_nascimento">Data de Nascimento</label>
                        <input type="date" id="cand_data_nascimento" name="cand_data_nascimento">
                    </div>
                    <div class="input-group">
                        <div class="input-box half-width">
                            <label for="cand_telefone">Telefone Fixo</label>
                            <input type="tel" id="cand_telefone" name="cand_telefone" placeholder="(XX) XXXX-XXXX">
                        </div>
                        <div class="input-box half-width">
                            <label for="cand_whatsapp">Celular / WhatsApp</label>
                            <input type="tel" id="cand_whatsapp" name="cand_whatsapp" placeholder="(XX) 9XXXX-XXXX">
                        </div>
                    </div>
                    <div class="input-box">
                        <label for="nacionalidade">Nacionalidade</label>
                        <select name="nacionalidade" id="nacionalidade">
                            <option value="Brasileiro(a)" selected>Brasileiro(a)</option>
                            <option value="Estrangeiro(a)">Estrangeiro(a)</option>
                        </select>
                    </div>
                    <div class="input-box" id="campo_rne" style="display: none;">
                        <label for="rne">RNE (Registro Nacional de Estrangeiro)</label>
                        <input type="text" id="rne" name="rne" placeholder="Digite seu número RNE">
                    </div>

                    <div class="input-group">
                        <div class="input-box half-width">
                            <label for="cand_password">Senha</label>
                            <div style="position: relative;">
                                <input type="password" id="cand_password" name="cand_password" placeholder="Crie uma senha" style="padding-right: 40px;">
                                <i class='bx bx-show' onclick="togglePasswordVisibility('cand_password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6c757d;"></i>
                            </div>
                        </div>
                        <div class="input-box half-width">
                            <label for="cand_confirm_password">Confirmar Senha</label>
                            <div style="position: relative;">
                                <input type="password" id="cand_confirm_password" name="cand_confirm_password" placeholder="Confirme sua senha" style="padding-right: 40px;">
                                <i class='bx bx-show' onclick="togglePasswordVisibility('cand_confirm_password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6c757d;"></i>
                            </div>
                            <span id="cand_pass_error" class="campo-error-validacao" style="display:none;">As senhas não são iguais</span>
                        </div>
                    </div>
                </fieldset>
            </div>

            <div id="campos_empregador" style="display: none;">
                <fieldset>
                    <legend>Dados do Empregador</legend>
                    <div class="input-box">
                        <label for="cnpj">CNPJ</label>
                        <input type="text" id="cnpj" name="emp_cnpj" placeholder="CNPJ da organização (apenas números)">
                        <span id="cnpj-error" class="campo-error-validacao"></span>
                    </div>
                    <div class="input-box">
                        <label for="emp_nome_empresa">Nome Empresarial</label>
                        <!-- Input nativo (usado para Empresa Privada e para submeter o valor) -->
                        <input type="text" id="emp_nome_empresa" name="emp_nome_empresa"
                            placeholder="Nome Empresarial da sua organização">

                        <!-- Custom Select Wrapper (Apenas para Prefeitura) -->
                        <div class="custom-select-wrapper" id="municipio-select-wrapper">
                            <div class="select-trigger" id="municipio-trigger">
                                <span>Selecione o Município...</span>
                            </div>
                            <div class="select-dropdown" id="municipio-dropdown">
                                <div class="select-search">
                                    <input type="text" id="municipio-search" placeholder="Pesquisar cidade..." autocomplete="off">
                                </div>
                                <div class="select-options" id="municipio-options-list">
                                    <div class="select-option no-result">Carregando...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="input-box">
                        <label for="emp_logo">Logo da Empresa (Opcional)</label>
                        <input type="file" id="emp_logo" name="emp_logo" accept="image/png, image/jpeg, image/webp">
                        <small>Isso será exibido nas vagas. Recomendado: .png com fundo transparente.</small>
                    </div>
                    <!-- Hidden input moved up for context if desired, or keep at bottom. Keeping it here is fine, but I'll only remove the CNPJ block from here -->
                    <input type="hidden" id="emp_tipo" name="emp_tipo">

                    <!-- Datalist para Municípios (Preenchido via API) -->
                    <datalist id="lista-municipios-pr"></datalist>
                </fieldset>
                <fieldset>
                    <legend>Endereço</legend>
                    <div class="input-group">
                        <div class="input-box" style="flex: 2;">
                            <label for="emp_cep">CEP</label>
                            <input type="text" id="emp_cep" name="emp_cep" placeholder="Digite o CEP" onblur="fetchAddress(this.value)">
                        </div>
                        <div class="input-box" style="flex: 6;">
                            <label for="emp_logradouro">Logradouro (Rua, Av.)</label>
                            <input type="text" id="emp_logradouro" name="emp_logradouro" placeholder="Preenchido automaticamente">
                        </div>
                        <div class="input-box" style="flex: 1;">
                            <label for="emp_numero">Número</label>
                            <input type="text" id="emp_numero" name="emp_numero" placeholder="Nº">
                        </div>
                    </div>
                    <div class="input-group">
                        <div class="input-box" style="flex: 3;">
                            <label for="emp_complemento">Complemento</label>
                            <input type="text" id="emp_complemento" name="emp_complemento" placeholder="Apto, Bloco, etc.">
                        </div>
                        <div class="input-box" style="flex: 3;">
                            <label for="emp_bairro">Bairro</label>
                            <input type="text" id="emp_bairro" name="emp_bairro" placeholder="Preenchido automaticamente">
                        </div>
                        <div class="input-box" style="flex: 3;">
                            <label for="emp_cidade">Cidade</label>
                            <input type="text" id="emp_cidade" name="emp_cidade" placeholder="Preenchido automaticamente">
                        </div>
                        <div class="input-box" style="flex: 1; min-width: 60px;">
                            <label for="emp_estado">UF</label>
                            <input type="text" id="emp_estado" name="emp_estado" placeholder="UF">
                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    <legend>Código de Convite</legend>
                    <div class="input-box">
                        <label for="codigo_convite">Código de Convite (Opcional)</label>
                        <input type="text" id="codigo_convite" name="codigo_convite" placeholder="Se você foi convidado, insira o código aqui" autocomplete="off">
                        <small>Se você é o primeiro a cadastrar esta empresa, deixe este campo em branco.</small>
                    </div>
                </fieldset>
                <fieldset>
                    <legend>Dados do Administrador da Conta (RH)</legend>
                    <div class="input-box">
                        <label for="rh_name">Seu Nome Completo</label>
                        <input type="text" id="rh_name" name="rh_name" placeholder="Seu nome">
                    </div>
                    <div class="input-box">
                        <label for="rh_email">Seu Email de Acesso</label>
                        <input type="email" id="rh_email" name="rh_email" placeholder="Seu e-mail profissional">
                    </div>
                    <div class="input-group">
                        <div class="input-box half-width">
                            <label for="rh_password">Sua Senha de Acesso</label>
                            <div style="position: relative;">
                                <input type="password" id="rh_password" name="rh_password" placeholder="Crie uma senha" style="padding-right: 40px;">
                                <i class='bx bx-show' onclick="togglePasswordVisibility('rh_password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6c757d;"></i>
                            </div>
                        </div>
                        <div class="input-box half-width">
                            <label for="rh_confirm_password">Confirmar Senha</label>
                            <div style="position: relative;">
                                <input type="password" id="rh_confirm_password" name="rh_confirm_password" placeholder="Confirme sua senha" style="padding-right: 40px;">
                                <i class='bx bx-show' onclick="togglePasswordVisibility('rh_confirm_password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6c757d;"></i>
                            </div>
                            <span id="rh_pass_error" class="campo-error-validacao" style="display:none;">As senhas não são iguais</span>
                        </div>
                    </div>
                </fieldset>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Criar Conta</button>

            <div style="text-align: center; margin-top: 15px;">
                <p>Já tem uma conta? <a href="<?= BASE_URL ?>auth/login">Faça Login</a></p>
            </div>
        </form>
    </main>

    <script>
        /**
         * Função para selecionar o tipo de cadastro via botões
         */
        function selectTipo(tipo, element) {
            // Atualiza visual dos botões
            document.querySelectorAll('.cadastro-btn').forEach(btn => btn.classList.remove('active'));
            element.classList.add('active');

            const tipoInput = document.getElementById('tipo_cadastro');
            const empTipoSelect = document.getElementById('emp_tipo');

            // Define valores baseados na escolha
            const nomeEmpresaInput = document.getElementById('emp_nome_empresa');
            const municipioWrapper = document.getElementById('municipio-select-wrapper');

            if (tipo === 'candidato') {
                tipoInput.value = 'candidato';

                // Reset visual inputs
                if (nomeEmpresaInput) {
                    nomeEmpresaInput.style.display = 'block';
                    nomeEmpresaInput.placeholder = "Nome Empresarial da sua organização";
                    nomeEmpresaInput.value = "";
                }
                if (municipioWrapper) municipioWrapper.style.display = 'none';

            } else if (tipo === 'empresa') {
                tipoInput.value = 'empregador';
                if (empTipoSelect) empTipoSelect.value = 'empresa';

                // Modo Empresa -> Input Texto Normal
                if (nomeEmpresaInput) {
                    nomeEmpresaInput.style.display = 'block';
                    nomeEmpresaInput.placeholder = "Nome Empresarial da sua organização";
                    nomeEmpresaInput.value = "";
                    // Remove requirement managed by JS validation, but visibility helps
                }
                if (municipioWrapper) municipioWrapper.style.display = 'none';

            } else if (tipo === 'prefeitura') {
                tipoInput.value = 'empregador';
                if (empTipoSelect) empTipoSelect.value = 'prefeitura';

                // Modo Prefeitura -> Custom Select
                if (nomeEmpresaInput) {
                    nomeEmpresaInput.style.display = 'none'; // Esconde input original
                    nomeEmpresaInput.value = ""; // Limpa valor
                }
                if (municipioWrapper) municipioWrapper.style.display = 'block';
            }


            // Dispara evento 'change' manualmente para ativar os listeners existentes
            const event = new Event('change');
            tipoInput.dispatchEvent(event);
        }

        // Global Array para cache
        let allMunicipios = [];

        async function carregarMunicipiosPR() {
            try {
                const response = await fetch('https://brasilapi.com.br/api/ibge/municipios/v1/PR');
                const municipios = await response.json();

                municipios.sort((a, b) => a.nome.localeCompare(b.nome));
                allMunicipios = municipios; // Salva em memória

                renderMunicipiosList(allMunicipios);

            } catch (error) {
                console.error("Erro ao carregar municípios:", error);
                const list = document.getElementById('municipio-options-list');
                list.innerHTML = '<div class="select-option no-result">Erro ao carregar.</div>';
            }
        }

        function renderMunicipiosList(lista) {
            const listContainer = document.getElementById('municipio-options-list');
            listContainer.innerHTML = '';

            if (lista.length === 0) {
                listContainer.innerHTML = '<div class="select-option no-result">Nenhuma cidade encontrada.</div>';
                return;
            }

            lista.forEach(city => {
                const div = document.createElement('div');
                div.classList.add('select-option');
                div.textContent = "Município de " + city.nome;
                div.dataset.value = "Município de " + city.nome;

                div.addEventListener('click', function() {
                    selectMunicipio(this.dataset.value);
                });

                listContainer.appendChild(div);
            });
        }

        // Selecionar Município (Click)
        function selectMunicipio(valor) {
            const triggerText = document.querySelector('#municipio-trigger span');
            const hiddenInput = document.getElementById('emp_nome_empresa');
            const dropdown = document.getElementById('municipio-dropdown');

            triggerText.textContent = valor;
            hiddenInput.value = valor;

            // Fecha dropdown
            dropdown.classList.remove('open');
            // Remove error class if exists
            hiddenInput.classList.remove('invalido');

            // Trigger validação manual se necessário
            const event = new Event('input');
            hiddenInput.dispatchEvent(event);
        }

        // Lógica de UI do Custom Select
        document.addEventListener('DOMContentLoaded', () => {
            carregarMunicipiosPR();

            const trigger = document.getElementById('municipio-trigger');
            const dropdown = document.getElementById('municipio-dropdown');
            const searchInput = document.getElementById('municipio-search');

            // Toggle Dropdown
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdown.classList.toggle('open');
                if (dropdown.classList.contains('open')) {
                    searchInput.focus();
                    searchInput.value = '';
                    renderMunicipiosList(allMunicipios); // Reset list
                }
            });

            // Search Filter logic
            searchInput.addEventListener('input', function() {
                const term = this.value.toLowerCase();
                const filtered = allMunicipios.filter(c => c.nome.toLowerCase().includes(term));
                renderMunicipiosList(filtered);
            });

            // Fechar ao clicar fora
            document.addEventListener('click', (e) => {
                if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.remove('open');
                }
            });
        });


        /**
         * Ouvinte para submissão do formulário
         * Verifica o campo oculto 'tipo_cadastro' e previne envio se vazio
         */
        document.getElementById('form-cadastro').addEventListener('submit', function(e) {
            const tipoInput = document.getElementById('tipo_cadastro');
            if (!tipoInput.value) {
                e.preventDefault();
                alert("Por favor, selecione o tipo de conta: Candidato, Empresa ou Prefeitura.");
                return;
            }
        });

        /**
         * Funções de validação de CPF e CNPJ
         */
        function validaCPF(cpf) {
            cpf = cpf.replace(/[^\d]+/g, '');
            if (cpf.length !== 11) return false;
            if (/^(\d)\1{10}$/.test(cpf)) return false;
            let soma = 0,
                resto;
            for (let i = 1; i <= 9; i++) soma += parseInt(cpf.substring(i - 1, i)) * (11 - i);
            resto = (soma * 10) % 11;
            if (resto === 10 || resto === 11) resto = 0;
            if (resto !== parseInt(cpf.substring(9, 10))) return false;
            soma = 0;
            for (let i = 1; i <= 10; i++) soma += parseInt(cpf.substring(i - 1, i)) * (12 - i);
            resto = (soma * 10) % 11;
            if (resto === 10 || resto === 11) resto = 0;
            if (resto !== parseInt(cpf.substring(10, 11))) return false;
            return true;
        }

        function validaCNPJ(cnpj) {
            cnpj = cnpj.replace(/[^\d]+/g, '');
            if (cnpj == '') return false;
            if (cnpj.length != 14) return false;
            if (/^(\d)\1+$/.test(cnpj)) return false;
            let tamanho = cnpj.length - 2
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

        /**
         * Função para buscar endereço via CEP (Brasil API)
         */
        async function fetchAddress(cep) {
            try {
                document.getElementById('emp_logradouro').value = 'Buscando...';
                document.getElementById('emp_bairro').value = 'Buscando...';
                document.getElementById('emp_cidade').value = 'Buscando...';
                document.getElementById('emp_estado').value = '...';
                const response = await fetch(`https://brasilapi.com.br/api/cep/v1/${cep}`);
                if (!response.ok) throw new Error('CEP não encontrado.');
                const data = await response.json();
                document.getElementById('emp_logradouro').value = data.street;
                document.getElementById('emp_bairro').value = data.neighborhood;
                document.getElementById('emp_cidade').value = data.city;
                document.getElementById('emp_estado').value = data.state;
                document.getElementById('emp_numero').focus();
            } catch (error) {
                alert('Não foi possível encontrar o CEP. Verifique o número e tente novamente.');
                document.getElementById('emp_logradouro').value = '';
                document.getElementById('emp_bairro').value = '';
                document.getElementById('emp_cidade').value = '';
                document.getElementById('emp_estado').value = '';
            }
        }

        function togglePasswordVisibility(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bx-show');
                icon.classList.add('bx-hide');
            } else {
                input.type = 'password';
                icon.classList.remove('bx-hide');
                icon.classList.add('bx-show');
            }
        }

        /**
         * Listener Principal DOMContentLoaded
         */
        document.addEventListener('DOMContentLoaded', () => {

            // --- Elementos Globais ---
            const form = document.getElementById('form-cadastro');
            const tipoCadastroSelect = document.getElementById('tipo_cadastro');
            const camposCandidatoDiv = document.getElementById('campos_candidato');
            const camposEmpregadorDiv = document.getElementById('campos_empregador');
            const submitButton = document.querySelector('#form-cadastro button[type="submit"]');

            // --- Elementos Candidato ---
            const candNameInput = document.getElementById('cand_name');
            const candEmailInput = document.getElementById('cand_email');
            const cpfInput = document.getElementById('cpf');
            const cpfErrorSpan = document.getElementById('cpf-error');
            // Novos campos
            const candDataNascInput = document.getElementById('cand_data_nascimento');
            const candWhatsappInput = document.getElementById('cand_whatsapp');
            const candTelefoneInput = document.getElementById('cand_telefone'); // Opcional

            const candPassInput = document.getElementById('cand_password');
            const candConfirmPassInput = document.getElementById('cand_confirm_password');
            const nacionalidadeSelect = document.getElementById('nacionalidade');
            const campoRneDiv = document.getElementById('campo_rne');
            const rneInput = document.getElementById('rne');
            const candInputs = camposCandidatoDiv.querySelectorAll('input, select, textarea');

            // --- Elementos Empregador ---
            const empNomeInput = document.getElementById('emp_nome_empresa');
            const cnpjInput = document.getElementById('cnpj');
            const cnpjErrorSpan = document.getElementById('cnpj-error');
            const cepInput = document.getElementById('emp_cep');
            const rhNameInput = document.getElementById('rh_name');
            const rhEmailInput = document.getElementById('rh_email');
            const rhPassInput = document.getElementById('rh_password');
            const rhConfirmPassInput = document.getElementById('rh_confirm_password');
            const empInputs = camposEmpregadorDiv.querySelectorAll('input, select, textarea');

            // ----- NOVO CAMPO DE CONVITE -----
            const codigoConviteInput = document.getElementById('codigo_convite');
            const camposEmpresaFieldsets = [ // Fieldsets para desabilitar com convite
                document.querySelector('input[name="emp_nome_empresa"]').closest('fieldset'),
                document.querySelector('input[name="emp_cep"]').closest('fieldset')
            ];
            // ---------------------------------

            // --- Função para limpar erros ---
            function limparErros(container) {
                const inputsInvalidos = container.querySelectorAll('input.invalido');
                inputsInvalidos.forEach(input => {
                    input.classList.remove('invalido');
                    input.setCustomValidity(''); // Limpa validade customizada
                    const errorSpan = document.getElementById(input.id + '-error');
                    if (errorSpan) errorSpan.textContent = '';
                });
            }

            // --- Validação de Senhas (HTML5 Custom Validity) ---
            function validarSenhas() {
                const tipoSelecionado = tipoCadastroSelect.value;
                let passInput, confirmInput;

                if (tipoSelecionado === 'candidato') {
                    passInput = candPassInput;
                    confirmInput = candConfirmPassInput;
                } else if (tipoSelecionado === 'empregador') {
                    passInput = rhPassInput;
                    confirmInput = rhConfirmPassInput;
                } else {
                    return;
                }

                if (passInput && confirmInput) {
                    if (confirmInput.value && passInput.value !== confirmInput.value) {
                        confirmInput.setCustomValidity("As senhas não conferem.");
                        // Apenas visual extra
                        const errorId = tipoSelecionado === 'candidato' ? 'cand_pass_error' : 'rh_pass_error';
                        const errorSpan = document.getElementById(errorId);
                        if (errorSpan) errorSpan.style.display = 'block';
                    } else {
                        confirmInput.setCustomValidity("");
                        const errorId = tipoSelecionado === 'candidato' ? 'cand_pass_error' : 'rh_pass_error';
                        const errorSpan = document.getElementById(errorId);
                        if (errorSpan) errorSpan.style.display = 'none';
                    }
                }
            }


            // --- 1. Lógica para mostrar/esconder campos principais ---
            if (tipoCadastroSelect) {
                tipoCadastroSelect.addEventListener('change', function() {
                    const tipo = this.value;

                    // Zera o 'required' de todos
                    candInputs.forEach(input => {
                        input.required = false;
                        input.setCustomValidity('');
                    });
                    empInputs.forEach(input => {
                        input.required = false;
                        input.setCustomValidity('');
                    });

                    if (tipo === 'candidato') {
                        camposCandidatoDiv.style.display = 'block';
                        camposEmpregadorDiv.style.display = 'none';
                        limparErros(camposEmpregadorDiv);
                        // Define campos requeridos para candidato
                        candNameInput.required = true;
                        candEmailInput.required = true;
                        cpfInput.required = true;
                        candDataNascInput.required = true;
                        candWhatsappInput.required = true;
                        candPassInput.required = true;
                        candConfirmPassInput.required = true;
                        nacionalidadeSelect.required = true;
                        toggleRneField();

                    } else if (tipo === 'empregador') {
                        camposCandidatoDiv.style.display = 'none';
                        camposEmpregadorDiv.style.display = 'block';
                        limparErros(camposCandidatoDiv);
                        // Define campos requeridos para empregador
                        empNomeInput.required = true;
                        cnpjInput.required = true;
                        rhNameInput.required = true;
                        rhEmailInput.required = true;
                        rhPassInput.required = true;
                        rhConfirmPassInput.required = true;
                    } else {
                        camposCandidatoDiv.style.display = 'none';
                        camposEmpregadorDiv.style.display = 'none';
                        limparErros(camposCandidatoDiv);
                        limparErros(camposEmpregadorDiv);
                    }
                    // submitButton.disabled = !isFormValid; // REMOVIDO
                });
            }

            // --- 2. Validação CPF (blur e input) ---
            if (cpfInput && cpfErrorSpan) {
                function validarCPFInput() {
                    if (cpfInput.value.length === 0 && cpfInput.required) {
                        // Deixa o HTML5 cuidar do 'required'
                        cpfErrorSpan.textContent = '';
                        cpfInput.classList.remove('invalido');
                        cpfInput.setCustomValidity('');
                    } else if (cpfInput.value.length > 0 && !validaCPF(cpfInput.value)) {
                        cpfErrorSpan.textContent = 'CPF inválido.';
                        cpfInput.classList.add('invalido');
                        cpfInput.setCustomValidity("CPF inválido."); // Bloqueia submit
                    } else {
                        cpfErrorSpan.textContent = '';
                        cpfInput.classList.remove('invalido');
                        cpfInput.setCustomValidity(""); // Libera
                    }
                }
                cpfInput.addEventListener('blur', validarCPFInput);
                cpfInput.addEventListener('input', validarCPFInput);
            }

            // --- 3. Validação CNPJ (blur e input) ---
            if (cnpjInput && cnpjErrorSpan) {
                function validarCNPJInput() {
                    if (cnpjInput.value.length === 0 && cnpjInput.required) {
                        // HTML5 handles empty
                        cnpjErrorSpan.textContent = '';
                        cnpjInput.classList.remove('invalido');
                        cnpjInput.setCustomValidity('');
                    } else if (cnpjInput.value.length > 0 && !validaCNPJ(cnpjInput.value)) {
                        cnpjErrorSpan.textContent = 'CNPJ inválido.';
                        cnpjInput.classList.add('invalido');
                        cnpjInput.setCustomValidity("CNPJ inválido."); // Bloqueia submit
                    } else {
                        cnpjErrorSpan.textContent = '';
                        cnpjInput.classList.remove('invalido');
                        cnpjInput.setCustomValidity(""); // Libera
                    }
                }
                cnpjInput.addEventListener('blur', validarCNPJInput);
                cnpjInput.addEventListener('input', validarCNPJInput);
            }

            // --- 4. Lógica para buscar CEP ---
            if (cepInput) {
                cepInput.addEventListener('blur', function() {
                    const cepValue = this.value.replace(/\D/g, '');
                    if (cepValue.length === 8) {
                        fetchAddress(cepValue);
                    }
                });
            }

            // --- 5. Lógica para mostrar/esconder RNE ---
            function toggleRneField() {
                if (nacionalidadeSelect.value === 'Estrangeiro(a)') {
                    campoRneDiv.style.display = 'block';
                    rneInput.required = true;
                } else {
                    campoRneDiv.style.display = 'none';
                    rneInput.required = false;
                    rneInput.value = '';
                    rneInput.classList.remove('invalido');
                    rneInput.setCustomValidity('');
                }
            }
            if (nacionalidadeSelect && campoRneDiv && rneInput) {
                nacionalidadeSelect.addEventListener('change', toggleRneField);
            }

            // --- 6. Listeners para SENHAS ---
            [candPassInput, candConfirmPassInput, rhPassInput, rhConfirmPassInput].forEach(inp => {
                if (inp) inp.addEventListener('input', validarSenhas);
            });


            // --- 7. Lógica do Código de Convite ---
            if (codigoConviteInput) {
                codigoConviteInput.addEventListener('input', function() {
                    const hasCode = this.value.trim().length > 0;

                    camposEmpresaFieldsets.forEach(fieldset => {
                        if (hasCode) {
                            fieldset.style.opacity = '0.3';
                            fieldset.style.pointerEvents = 'none';
                            // Remove obrigatoriedade dos inputs internos visualmente e logicamente
                            fieldset.querySelectorAll('input, select').forEach(inp => {
                                inp.dataset.requiredOriginal = inp.required; // Salva estado
                                inp.required = false;
                                inp.disabled = true; // Garante que não foca
                            });
                        } else {
                            fieldset.style.opacity = '1';
                            fieldset.style.pointerEvents = 'auto';
                            // Restaura
                            fieldset.querySelectorAll('input, select').forEach(inp => {
                                if (inp.dataset.requiredOriginal === "true") inp.required = true;
                                inp.disabled = false;
                            });
                        }
                    });
                });
            }

        }); // Fim do DOMContentLoaded principal
    </script>
</body>

</html>