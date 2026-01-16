<style>
    /* Estilos auxiliares */
    .input-with-prefix {
        display: flex;
        align-items: center;
        border: 1px solid #ccc;
        border-radius: 6px;
        background-color: #f9f9f9;
    }

    .input-with-prefix span {
        padding: 0 15px;
        background-color: #eee;
        border-right: 1px solid #ccc;
        color: #666;
        font-weight: 500;
    }

    .input-with-prefix input {
        border: none;
        background-color: transparent;
        width: 100%;
        outline: none;
        padding: 10px;
    }

    .input-with-prefix input:focus {
        box-shadow: none;
        border-color: transparent;
    }

    .input-with-prefix:focus-within {
        border-color: #581d22;
        box-shadow: 0 0 0 3px rgba(88, 29, 34, 0.1);
    }

    .text-marsala {
        color: #581d22 !important;
    }

    .checkbox-group-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
        margin-top: 10px;
    }

    .checkbox-group-grid label {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-weight: normal;
        font-size: 0.95rem;
        cursor: pointer;
    }

    .radio-group {
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
    }

    .radio-group label {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: normal;
        cursor: pointer;
    }

    input[type="checkbox"],
    input[type="radio"] {
        width: auto !important;
        flex-shrink: 0;
        margin-top: 3px;
        cursor: pointer;
    }

    #aviso-experiencia {
        display: none;
        color: #721c24;
        background-color: #f8d7da;
        padding: 10px;
        border-radius: 6px;
        border: 1px solid #f5c6cb;
        margin-top: 5px;
        font-size: 0.9rem;
    }

    .localizacao-group {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 1rem;
    }

    @media (max-width: 768px) {
        .localizacao-group {
            grid-template-columns: 1fr;
        }
    }

    /* --- Custom Multi-Select (Tipo de Contrato) --- */
    .multiselect-wrapper {
        position: relative;
        user-select: none;
    }

    .multiselect-trigger {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 6px;
        cursor: pointer;
        min-height: 42px;
    }

    .multiselect-trigger::after {
        content: '\25BC';
        font-size: 0.8em;
        color: #666;
        margin-left: 10px;
    }

    .multiselect-dropdown {
        position: absolute;
        top: 105%;
        left: 0;
        width: 100%;
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 100;
        display: none;
        max-height: 300px;
        overflow-y: auto;
        padding: 5px 0;
    }

    .multiselect-dropdown.open {
        display: block;
    }

    .multiselect-option {
        padding: 8px 15px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: background 0.2s;
    }

    .multiselect-option:hover {
        background-color: #f5f5f5;
    }

    .multiselect-option input[type="checkbox"] {
        margin: 0;
    }

    .multiselect-option label {
        cursor: pointer;
        width: 100%;
        margin: 0;
        font-weight: normal;
    }
</style>

<main class="page-container-view">
    <div class="page-content-wrapper" style="max-width: 900px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">

        <form action="<?= BASE_URL ?>vagas/store" method="POST">
            <!-- CSRF Token se tivesse, por enquanto sem -->
            <!-- <input type="hidden" name="type" value="criar_vaga"> --> <!-- Não precisa mais, rota define ação -->

            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger mb-4"><?= $_SESSION['flash_error'];
                                                        unset($_SESSION['flash_error']); ?></div>
            <?php endif; ?>

            <h1 class="text-marsala" style="margin-bottom: 5px;">Cadastrar Nova Vaga</h1>
            <p class="subtitle" style="color: #666; margin-bottom: 25px;">Preencha os detalhes abaixo para encontrar o candidato ideal.</p>

            <fieldset style="border: 1px solid #eee; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                <legend class="text-marsala" style="width: auto; padding: 0 10px; font-weight: 600;">Informações da Vaga</legend>

                <div class="input-box mb-3">
                    <label for="empresa_id">Publicar em nome de:</label>
                    <select name="empresa_id" id="empresa_id" class="form-control" required style="width: 100%; padding: 10px;">
                        <option value="" disabled selected>Selecione a empresa</option>
                        <?php foreach ($empresas as $empresa): ?>
                            <option value="<?= $empresa['id'] ?>"><?= htmlspecialchars($empresa['nome_empresa']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="input-box mb-3">
                    <label style="display: flex; align-items: center; gap: 10px; font-weight: normal; cursor: pointer;">
                        <input type="hidden" name="confidencial" value="0">
                        <input type="checkbox" name="confidencial" value="1">
                        Marcar como vaga confidencial (não exibir o nome da empresa)
                    </label>
                </div>

                <div class="input-box mb-3">
                    <label for="titulo-vaga">Título da Vaga</label>
                    <input type="text" id="titulo-vaga" name="titulo" class="form-control" placeholder="Ex: Desenvolvedor PHP Pleno" required>
                </div>

                <div class="input-box mb-3">
                    <label for="modalidade">Modalidade</label>
                    <select id="modalidade" name="modalidade" class="form-control" required>
                        <option value="" disabled selected>Selecione a modalidade</option>
                        <option value="Presencial">Presencial</option>
                        <option value="Híbrido">Híbrido</option>
                        <option value="Remoto">Remoto</option>
                    </select>
                </div>

                <div class="input-box mb-4">
                    <label>Tipo de Contrato</label>

                    <div class="multiselect-wrapper" id="contrato-multiselect">
                        <div class="multiselect-trigger" id="contrato-trigger">
                            <span id="contrato-text">Selecione as opções...</span>
                        </div>
                        <div class="multiselect-dropdown">
                            <?php
                            // Fallback caso a variável não venha do controller
                            if (empty($tipos_contrato)) {
                                $tipos_contrato = [
                                    'Efetivo' => 'Efetivo (CLT)',
                                    'PJ' => 'Pessoa Jurídica (PJ)',
                                    'Jovem Aprendiz' => 'Jovem Aprendiz',
                                    'Estágio' => 'Estágio',
                                    '1º Emprego' => '1º Emprego',
                                    'Trainee' => 'Trainee',
                                    'Temporário' => 'Temporário',
                                    'Freelancer' => 'Freelancer',
                                    'Terceiro' => 'Terceiro',
                                    'Banco de Talentos' => 'Banco de Talentos',
                                    'Voluntário' => 'Voluntário',
                                    'Sócio' => 'Sócio',
                                    'Summer job' => 'Summer job',
                                    'Intermitente' => 'Intermitente'
                                ];
                            }

                            $ids_js_contrato = [
                                'Estágio' => 'id="chkEstagio"',
                                'Jovem Aprendiz' => 'id="chkJovemAprendiz"',
                                '1º Emprego' => 'id="chkPrimeiroEmprego"',
                            ];
                            foreach ($tipos_contrato as $valor => $descricao):
                                $id_js = $ids_js_contrato[$valor] ?? '';
                            ?>
                                <div class="multiselect-option">
                                    <input type="checkbox" name="tipo_contrato[]" value="<?= htmlspecialchars($valor) ?>" <?= $id_js ?>>
                                    <label><?= htmlspecialchars($descricao) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="input-box mb-3">
                    <label>Localização</label>
                    <div class="localizacao-group">
                        <div class="input-box" style="margin: 0;">
                            <label for="vaga_cep" style="font-size: 0.9em; color:#666;">CEP (Para Mapa)</label>
                            <input type="text" id="vaga_cep" name="vaga_cep" class="form-control" placeholder="00000-000"
                                onblur="buscarEndereco(this.value)"
                                onkeyup="mascaraCep(this)"
                                maxlength="9" required>
                        </div>
                        <div class="input-box" style="margin: 0;">
                            <label for="localizacao_final" style="font-size: 0.9em; color:#666;">Cidade, UF (Exibição)</label>
                            <input type="text" id="localizacao_final" name="localizacao" class="form-control"
                                placeholder="Digite ou aguarde o CEP..." required>
                        </div>
                    </div>
                    <p class="subtitle" style="margin-top: 5px; font-size: 0.85rem; color: #888;">
                        O CEP é usado para calcular distâncias. O Texto da Localização é o que o candidato vê.
                    </p>
                </div>

                <div class="input-box mb-3">
                    <label for="ramo_atividade">Ramo de Atividade</label>
                    <select name="ramo_atividade" id="ramo_atividade" class="form-control" required>
                        <option value="">Selecione o ramo da vaga...</option>
                        <?php foreach ($ramos_de_atividade as $ramo): ?>
                            <option value="<?= htmlspecialchars($ramo) ?>"><?= htmlspecialchars($ramo) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </fieldset>

            <fieldset style="border: 1px solid #eee; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                <legend class="text-marsala" style="width: auto; padding: 0 10px; font-weight: 600;">Descrição e Requisitos</legend>

                <div class="input-box mb-3">
                    <label>Experiência Exigida</label>
                    <p id="aviso-experiencia">
                        <b>Atenção:</b> Vagas de "Estágio", "Jovem Aprendiz" e "1º Emprego" geralmente não exigem experiência.
                    </p>
                    <div class="radio-group">
                        <label><input type="radio" name="experiencia" value="com" id="radioComExperiencia"> Desejo candidato <b>com experiência</b>.</label>
                        <label><input type="radio" name="experiencia" value="sem" id="radioSemExperiencia"> Desejo candidato <b>sem experiência</b>.</label>
                        <label><input type="radio" name="experiencia" value="indiferente" id="radioIndiferente" checked> Indiferente.</label>
                    </div>
                </div>

                <div class="input-box mb-3">
                    <label for="descricao-vaga">Descrição da Vaga</label>
                    <textarea id="descricao-vaga" name="descricao" class="form-control" rows="5" placeholder="Descreva as principais atividades e responsabilidades..." style="width: 100%;"></textarea>
                </div>

                <div class="input-box mb-3">
                    <label for="requisitos">Requisitos e Qualificações</label>
                    <textarea id="requisitos" name="requisitos" class="form-control" rows="5" placeholder="Liste as habilidades técnicas e comportamentais necessárias..." style="width: 100%;"></textarea>
                </div>
            </fieldset>

            <fieldset style="border: 1px solid #eee; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                <legend class="text-marsala" style="width: auto; padding: 0 10px; font-weight: 600;">Salário e Benefícios</legend>

                <div class="input-box mb-3">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label style="margin-bottom: 0;">Faixa Salarial</label>
                        <label style="display: flex; align-items: center; gap: 10px; font-weight: normal; margin-bottom: 0; cursor: pointer;">
                            <input type="checkbox" id="salario-a-combinar" name="salario_a_combinar" value="1">
                            A Combinar
                        </label>
                    </div>
                    <div class="input-group" style="display: flex; gap: 20px;">
                        <div class="input-box" style="flex: 1; margin-bottom: 0;">
                            <label for="faixa_salarial_min" style="font-size: 0.9em;">Mínimo *</label>
                            <div class="input-with-prefix">
                                <span>R$</span>
                                <input type="text" id="faixa_salarial_min" name="faixa_salarial_min" placeholder="0,00" oninput="mascaraMoeda(event)" onblur="validarSalario()">
                            </div>
                        </div>
                        <div class="input-box" style="flex: 1; margin-bottom: 0;">
                            <label for="faixa_salarial_max" style="font-size: 0.9em;">Máximo</label>
                            <div class="input-with-prefix">
                                <span>R$</span>
                                <input type="text" id="faixa_salarial_max" name="faixa_salarial_max" placeholder="0,00" oninput="mascaraMoeda(event)" onblur="validarSalario()">
                            </div>
                        </div>
                    </div>
                    <div id="erro-salario" class="text-danger small" style="display: none; margin-top: 4px;">
                        <i class='bx bx-error-circle'></i> O salário mínimo não pode ser maior que o salário máximo.
                    </div>
                </div>

                <div class="input-box mb-3">
                    <label for="beneficios">Benefícios</label>
                    <textarea id="beneficios" name="beneficios" class="form-control" rows="4" placeholder="Vale Transporte, Vale Refeição, Plano de Saúde..." style="width: 100%;"></textarea>
                </div>
            </fieldset>

            <fieldset style="border: 1px solid #eee; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                <legend class="text-marsala" style="width: auto; padding: 0 10px; font-weight: 600;">Questionário da Vaga (Opcional)</legend>
                <p class="subtitle" style="margin-top: -5px; margin-bottom: 1.5rem; color: #666; font-size: 0.9rem;">Crie perguntas objetivas para triagem.</p>

                <div id="perguntas-wrapper"></div>

                <button type="button" id="add-pergunta" class="btn btn-secondary" style="margin-top: 10px;">
                    <i class='bx bx-plus'></i> Adicionar Pergunta
                </button>
            </fieldset>

            <button type="submit" class="btn btn-primary w-100 py-3" style="font-size: 1.1rem; font-weight: bold;">Publicar Vaga</button>
        </form>
    </div>
</main>

<script>
    // --- FUNÇÃO PARA BUSCAR ENDEREÇO ---
    // --- FUNÇÃO PARA BUSCAR ENDEREÇO (ASYNC/AWAIT - ROBUSTA) ---
    async function buscarEndereco(cep) {
        let cepLimpo = cep.replace(/\D/g, '');
        const inputLocal = document.getElementById('localizacao_final');

        if (cepLimpo.length !== 8) return;

        inputLocal.value = "";
        inputLocal.placeholder = "Pesquisando...";

        try {
            // 1. ViaCEP
            const resVia = await fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`);
            if (resVia.ok) {
                const data = await resVia.json();
                if (!data.erro) {
                    inputLocal.value = `${data.localidade} - ${data.uf}`;
                    inputLocal.disabled = false;
                    return; // Encontrou!
                }
            }
        } catch (e) {
            console.warn("ViaCEP falhou:", e);
        }

        try {
            // 2. BrasilAPI (Fallback)
            const resBra = await fetch(`https://brasilapi.com.br/api/cep/v1/${cepLimpo}`);
            if (resBra.ok) {
                const data = await resBra.json();
                inputLocal.value = `${data.city} - ${data.state}`;
                inputLocal.disabled = false;
                return; // Encontrou!
            }
        } catch (e) {
            console.warn("BrasilAPI falhou:", e);
        }

        // Falha Total
        inputLocal.disabled = false;
        inputLocal.placeholder = "Não encontrado. Digite a cidade manualmente.";
        inputLocal.focus();
    }

    // --- JS DO FORMULÁRIO ---
    document.addEventListener('DOMContentLoaded', function() {

        // --- MULTI-SELECT LÓGICA ---
        const msWrapper = document.getElementById('contrato-multiselect');
        if (msWrapper) {
            const trigger = msWrapper.querySelector('.multiselect-trigger');
            const dropdown = msWrapper.querySelector('.multiselect-dropdown');
            const checkboxes = msWrapper.querySelectorAll('input[type="checkbox"]');
            const resultText = document.getElementById('contrato-text');

            // Abrir/Fechar
            trigger.addEventListener('click', (e) => {
                e.stopPropagation(); // Impede fechar imediato
                dropdown.classList.toggle('open');
            });

            // Fechar ao clicar fora
            document.addEventListener('click', (e) => {
                if (!msWrapper.contains(e.target)) {
                    dropdown.classList.remove('open');
                }
            });

            // Atualizar Texto e Click no Label
            function updateLabel() {
                const selected = [];
                checkboxes.forEach(cb => {
                    if (cb.checked) selected.push(cb.nextElementSibling.textContent.trim());
                });

                if (selected.length === 0) {
                    resultText.textContent = "Selecione as opções...";
                    resultText.style.color = "#888";
                } else {
                    resultText.textContent = selected.join(', ');
                    resultText.style.color = "#333";
                }
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateLabel);
                // Permitir clicar na div inteira da opção
                cb.closest('.multiselect-option').addEventListener('click', function(e) {
                    if (e.target !== cb && e.target.tagName !== 'LABEL') {
                        cb.checked = !cb.checked;
                        cb.dispatchEvent(new Event('change'));
                    }
                });
            });

            updateLabel(); // Inicial
        }


        // SALÁRIO
        const salarioMinInput = document.getElementById('faixa_salarial_min');
        const salarioMaxInput = document.getElementById('faixa_salarial_max');
        const salarioCheckbox = document.getElementById('salario-a-combinar');

        if (salarioMinInput && salarioMaxInput && salarioCheckbox) {
            function toggleSalarioInput() {
                const isChecked = salarioCheckbox.checked;
                salarioMinInput.disabled = isChecked;
                salarioMaxInput.disabled = isChecked;
                if (isChecked) {
                    salarioMinInput.value = '';
                    salarioMaxInput.value = '';
                }
            }
            salarioCheckbox.addEventListener('change', toggleSalarioInput);
            toggleSalarioInput();
        }

        // EXPERIÊNCIA
        const chkEstagio = document.getElementById('chkEstagio');
        const chkJovemAprendiz = document.getElementById('chkJovemAprendiz');
        const chkPrimeiroEmprego = document.getElementById('chkPrimeiroEmprego');
        const radioComExperiencia = document.getElementById('radioComExperiencia');
        const radioIndiferente = document.getElementById('radioIndiferente');
        const avisoExperiencia = document.getElementById('aviso-experiencia');
        const tiposVagaProibidos = [chkEstagio, chkJovemAprendiz, chkPrimeiroEmprego];

        function validarExperiencia() {
            if (!radioComExperiencia || !avisoExperiencia || !radioIndiferente) return;
            const proibeExperiencia = tiposVagaProibidos.some(chk => chk && chk.checked);
            if (proibeExperiencia) {
                radioComExperiencia.disabled = true;
                avisoExperiencia.style.display = 'block';
                if (radioComExperiencia.checked) {
                    radioIndiferente.checked = true;
                }
            } else {
                radioComExperiencia.disabled = false;
                avisoExperiencia.style.display = 'none';
            }
        }
        tiposVagaProibidos.forEach(chk => {
            if (chk) chk.addEventListener('change', validarExperiencia);
        });
        validarExperiencia();

        // PERGUNTAS
        const addPerguntaBtn = document.getElementById("add-pergunta");
        if (addPerguntaBtn) {
            addPerguntaBtn.addEventListener("click", () => {
                const wrapper = document.getElementById('perguntas-wrapper');
                const index = Date.now();
                const template = `
                <div class="pergunta-item" style="margin-bottom: 1.5rem; background: #f9f9f9; padding: 15px; border-radius: 6px; border: 1px solid #eee;">
                    <div style="display: flex; gap: 10px; align-items: start; margin-bottom: 10px;">
                        <div style="flex-grow: 1;">
                            <label style="font-size: 0.9rem; margin-bottom: 5px; display: block;">Texto da Pergunta:</label>
                            <input type="text" name="perguntas[${index}][texto]" class="form-control" required placeholder="Ex: Tem disponibilidade para viagens?">
                        </div>
                        <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.pergunta-item').remove()" style="margin-top: 25px;"><i class='bx bx-trash'></i></button>
                    </div>
                    <label style="display: flex; align-items: center; gap: 10px; font-weight: normal; font-size: 0.9rem; cursor: pointer;">
                        <input type="checkbox" name="perguntas[${index}][eliminatoria]" value="1">
                        Esta é uma pergunta eliminatória (Exige "Sim")
                    </label>
                </div>`;
                wrapper.insertAdjacentHTML("beforeend", template);
            });
        }
    });

    // MÁSCARA CEP
    function mascaraCep(input) {
        let value = input.value.replace(/\D/g, "");
        if (value.length > 5) {
            value = value.substring(0, 5) + "-" + value.substring(5, 8);
        }
        input.value = value;
    }

    // --- MÁSCARA MOEDA ---
    function mascaraMoeda(event) {
        const input = event.target;
        let value = input.value.replace(/\D/g, '');

        if (value === '') {
            input.value = '';
            return;
        }

        // Converte para decimal
        value = (parseInt(value) / 100).toFixed(2) + '';

        // Troca ponto por vírgula
        value = value.replace('.', ',');

        // Adiciona ponto de milhar
        value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');

        input.value = value;
    }

    function validarSalario() {
        let minInput = document.getElementById('faixa_salarial_min');
        let maxInput = document.getElementById('faixa_salarial_max');
        let erroDiv = document.getElementById('erro-salario');
        let submitBtn = document.querySelector('button[type="submit"]');

        // Remove pontos e troca vírgula por ponto para converter float
        let min = parseFloat(minInput.value.replace(/\./g, '').replace(',', '.')) || 0;
        let max = parseFloat(maxInput.value.replace(/\./g, '').replace(',', '.')) || 0;

        if (max > 0 && min > max) {
            erroDiv.style.display = 'block';
            minInput.parentElement.style.borderColor = '#dc3545';
            maxInput.parentElement.style.borderColor = '#dc3545';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.5';
                submitBtn.style.cursor = 'not-allowed';
            }
        } else {
            erroDiv.style.display = 'none';
            minInput.parentElement.style.borderColor = '#ccc';
            maxInput.parentElement.style.borderColor = '#ccc';

            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
            }
        }
    }
</script>