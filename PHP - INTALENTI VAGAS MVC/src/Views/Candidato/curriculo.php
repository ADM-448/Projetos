<?php


$candidato_data = $candidato;
$experiencias = $candidato['experiencias'] ?? [];
$formacoes = $candidato['formacoes'] ?? [];
$cursos = $candidato['cursos'] ?? [];

// Helpers
function formatarCPF($cpf)
{
    if (!$cpf) return '';
    $cpfLimpo = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpfLimpo) != 11) return $cpf;
    return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpfLimpo);
}

function e($str)
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<style>
    .checkbox-group {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 20px;
        margin-top: 10px;
    }

    .checkbox-group label {
        display: flex;
        align-items: center;
        gap: 5px;
        font-weight: 400;
        cursor: pointer;
    }

    .idiomas-checkbox-group {
        display: block;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        padding: 15px;
        height: 180px;
        overflow-y: auto;
        background-color: var(--surface-color);
    }

    .idiomas-checkbox-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 400;
        cursor: pointer;
    }

    .idiomas-checkbox-group input {
        width: auto;
        margin-right: 8px;
        vertical-align: middle;
    }

    .file-upload-wrapper {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .half-width {
        width: 48%;
    }

    .item-actions {
        margin-top: 15px;
        text-align: right;
    }

    /* Espaçamento para botões de adicionar */
    #add-experiencia,
    #add-formacao,
    #add-curso {
        margin-top: 15px;
    }

    /* Estilos adicionais para harmonizar com o tema */
    fieldset {
        margin-bottom: 2rem;
    }

    /* Idiomas Cards & Modern UI */
    :root {
        --marsala: #955251;
        --marsala-light: #fbecec;
    }

    .idiomas-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: stretch;
        /* Cards mesma altura */
    }

    .language-card {
        background: #fff;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 15px;
        width: 180px;
        /* Um pouco mais largo para caber selects */
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 10px;
        cursor: pointer;
        position: relative;
        min-height: 100px;
    }

    .language-card:hover {
        border-color: #ced4da;
        transform: translateY(-2px);
    }

    .language-card.active {
        border-color: var(--marsala);
        background-color: var(--marsala-light);
        box-shadow: 0 4px 12px rgba(149, 82, 81, 0.2);
    }

    .language-card .card-remove-btn {
        position: absolute;
        top: 5px;
        right: 5px;
        color: #dc3545;
        cursor: pointer;
        opacity: 0.6;
        padding: 2px;
    }

    .language-card .card-remove-btn:hover {
        opacity: 1;
    }

    .lang-header {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        color: #495057;
        font-size: 0.95rem;
    }

    .language-card.active .lang-header {
        color: var(--marsala);
    }

    /* Checkmark simulado */
    .check-icon {
        width: 20px;
        height: 20px;
        border-radius: 5px;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 12px;
        transition: all 0.2s;
        flex-shrink: 0;
    }

    .language-card.active .check-icon {
        background: var(--marsala);
    }

    .lang-level select,
    .lang-name-select {
        width: 100%;
        font-size: 0.85rem;
        padding: 6px 24px 6px 8px;
        /* Extra padding right for arrow */
        border-radius: 6px;
        border: 1px solid #dee2e6;
        background-color: #fff;
        color: #495057;
        outline: none;

        /* Custom Chevron Arrow */
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23495057' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 6px center;
        background-size: 14px;
        cursor: pointer;
    }

    .lang-level select:focus,
    .lang-name-select:focus {
        border-color: var(--marsala);
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23955251' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    }

    .hidden {
        display: none;
    }

    .add-lang-card {
        background: #fff;
        border: 2px dashed #ced4da;
        border-radius: 12px;
        height: auto;
        min-height: 100px;
        width: 100px;
        /* Botão menor quadrado */
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 5px;
        cursor: pointer;
        color: #6c757d;
        font-weight: 600;
        transition: all 0.2s;
        text-align: center;
        font-size: 0.8rem;
    }

    .add-lang-card i {
        font-size: 24px;
    }

    .add-lang-card:hover {
        border-color: var(--marsala);
        color: var(--marsala);
        background: var(--marsala-light);
    }
</style>

<div class="container">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 style="color: var(--primary-color); margin-top: 0;">Meu Currículo</h1>
            <p class="subtitle" style="margin-bottom: 0;">Mantenha seus dados atualizados para encontrar as melhores vagas.</p>
        </div>
        <a href="<?= BASE_URL ?>vagas" class="btn btn-secondary" style="background-color: #6c757d; border: none; color: #fff; padding: 8px 20px; font-size: 0.95rem; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; height: fit-content;">
            <i class='bx bx-arrow-back'></i> Voltar
        </a>
    </div>

    <?php if (!empty($msg)) : ?>
        <div class="alert alert-info">
            <?= e($msg); ?>
        </div>
    <?php endif; ?>

    <div class="alert alert-warning" style="font-size: 0.9rem;">
        <i class='bx bx-info-circle'></i> Lembre-se de clicar em <strong>"Salvar Currículo"</strong> no final da página para gravar todas as suas alterações.
        Campos com * são obrigatórios.
    </div>

    <form action="<?= BASE_URL ?>curriculo/update" method="POST" enctype="multipart/form-data">

        <fieldset>
            <legend>Dados Pessoais:</legend>
            <div class="input-box">
                <label>Nome Completo *</label>
                <input type="text" name="nome_completo" required
                    value="<?= e($candidato_data['nome_completo'] ?? $candidato_data['name']) ?>">
            </div>
            <div class="input-group">
                <div class="input-box half-width">
                    <label>CPF</label>
                    <input type="text" disabled style="background-color: #eee;"
                        value="<?= formatarCPF(e(!empty($candidato_data['cpf']) ? $candidato_data['cpf'] : ($candidato_data['user_cpf'] ?? ''))) ?>">
                    <!-- Envia o CPF oculto para não perder o dado se o backend esperar -->
                    <input type="hidden" name="cpf" value="<?= e(!empty($candidato_data['cpf']) ? $candidato_data['cpf'] : ($candidato_data['user_cpf'] ?? '')) ?>">
                </div>

                <div class="input-box half-width">
                    <label>Data de Nascimento *</label>
                    <input type="date" name="data_nascimento"
                        value="<?= e($candidato_data['data_nascimento']) ?>"
                        min="1930-01-01" max="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            <div class="input-group">
                <div class="input-box half-width">
                    <label>Estado Civil *</label>
                    <select name="estado_civil" required>
                        <option value="">Selecione</option>
                        <?php
                        $opts = ['Solteiro(a)', 'Casado(a)', 'Divorciado(a)', 'Viúvo(a)'];
                        foreach ($opts as $opt): ?>
                            <option value="<?= $opt ?>" <?= (e($candidato_data['estado_civil']) == $opt) ? "selected" : "" ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-box half-width">
                    <label>Sexo *</label>
                    <select name="sexo" required>
                        <option value="">Selecione</option>
                        <option value="Masculino" <?= (e($candidato_data['sexo']) == "Masculino") ? "selected" : "" ?>>Masculino</option>
                        <option value="Feminino" <?= (e($candidato_data['sexo']) == "Feminino") ? "selected" : "" ?>>Feminino</option>
                        <option value="Outro" <?= (e($candidato_data['sexo']) == "Outro") ? "selected" : "" ?>>Prefiro não informar</option>
                    </select>
                </div>
            </div>

            <div class="input-group">
                <div class="input-box half-width">
                    <label for="nacionalidade">Nacionalidade *</label>
                    <select id="nacionalidade" required disabled style="background-color: #eee;">
                        <option value="Brasileiro(a)" <?= (e($candidato_data['nacionalidade']) == 'Brasileiro(a)' || empty($candidato_data['nacionalidade'])) ? 'selected' : '' ?>>Brasileiro(a)</option>
                        <option value="Estrangeiro(a)" <?= (e($candidato_data['nacionalidade']) == 'Estrangeiro(a)') ? 'selected' : '' ?>>Estrangeiro(a)</option>
                    </select>
                    <input type="hidden" name="nacionalidade" value="<?= e($candidato_data['nacionalidade']) ?: 'Brasileiro(a)' ?>">
                </div>

                <div class="input-box half-width" id="campo_rne" style="<?= (e($candidato_data['nacionalidade']) == 'Estrangeiro(a)') ? 'display: block;' : 'display: none;' ?>">
                    <label for="rne">RNE</label>
                    <input type="text" id="rne" name="rne" value="<?= e($candidato_data['rne']) ?>">
                </div>
            </div>

            <div class="input-box">
                <label>Idiomas *</label>
                <div id="idiomas-container" class="idiomas-grid">
                    <!-- Preset: Inglês -->
                    <div class="language-card" data-lang="Inglês">
                        <div class="lang-header">
                            <div class="check-icon"><i class='bx bx-check'></i></div>
                            <span class="lang-name">Inglês</span>
                        </div>
                        <div class="lang-level hidden">
                            <select class="form-control form-control-sm level-select">
                                <option value="Básico">Básico</option>
                                <option value="Intermediário">Intermediário</option>
                                <option value="Avançado">Avançado</option>
                                <option value="Fluente">Fluente</option>
                            </select>
                        </div>
                    </div>

                    <!-- Preset: Espanhol -->
                    <div class="language-card" data-lang="Espanhol">
                        <div class="lang-header">
                            <div class="check-icon"><i class='bx bx-check'></i></div>
                            <span class="lang-name">Espanhol</span>
                        </div>
                        <div class="lang-level hidden">
                            <select class="form-control form-control-sm level-select">
                                <option value="Básico">Básico</option>
                                <option value="Intermediário">Intermediário</option>
                                <option value="Avançado">Avançado</option>
                                <option value="Fluente">Fluente</option>
                            </select>
                        </div>
                    </div>

                    <!-- Preset: Português -->
                    <div class="language-card" data-lang="Português">
                        <div class="lang-header">
                            <div class="check-icon"><i class='bx bx-check'></i></div>
                            <span class="lang-name">Português</span>
                        </div>
                        <div class="lang-level hidden">
                            <select class="form-control form-control-sm level-select">
                                <option value="Básico">Básico</option>
                                <option value="Intermediário">Intermediário</option>
                                <option value="Avançado">Avançado</option>
                                <option value="Fluente">Fluente</option>
                            </select>
                        </div>
                    </div>

                    <!-- Dynamic Custom Cards will be inserted here by JS -->

                    <div class="add-lang-card" id="btn-add-outro-idioma">
                        <i class='bx bx-plus'></i>
                        <div>Outro Idioma</div>
                    </div>
                </div>

                <!-- Hidden inputs container for form submission -->
                <div id="idiomas-hidden-inputs"></div>

                <!-- Data passing for JS -->
                <input type="hidden" id="saved-idiomas" value="<?= e($candidato_data['idiomas'] ?? '') ?>">
            </div>

            <div class="input-group">
                <div class="input-box half-width">
                    <label>Escolaridade *</label>
                    <select name="escolaridade" required>
                        <option value="">Selecione</option>
                        <?php
                        $graus = ['Ensino Fundamental', 'Ensino Médio', 'Ensino Superior', 'Pós-graduação', 'Mestrado', 'Doutorado'];
                        foreach ($graus as $g): ?>
                            <option value="<?= $g ?>" <?= (e($candidato_data['escolaridade']) == $g) ? 'selected' : '' ?>><?= $g ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-box half-width">
                    <label>Carteira de Habilitação *</label>
                    <?php $h_salvas = array_map('trim', explode(',', e($candidato_data['carteira_habilitacao']))); ?>
                    <div class="checkbox-group">
                        <?php
                        $ph = ['Nenhuma', 'A', 'B', 'C', 'D', 'E'];
                        foreach ($ph as $h): ?>
                            <label>
                                <input type="checkbox" name="carteira_habilitacao[]" value="<?= $h ?>" <?= in_array($h, $h_salvas) ? 'checked' : '' ?> style="width: auto;">
                                <?= $h ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Contato e Endereço:</legend>
            <div class="input-group">
                <div class="input-box half-width">
                    <label>E-mail</label>
                    <input type="email" disabled style="background-color: #eee;" value="<?= e($candidato_data['email']) ?>">
                    <input type="hidden" name="email" value="<?= e($candidato_data['email']) ?>">
                </div>

                <div class="input-box half-width">
                    <label>Telefone Fixo (Opcional)</label>
                    <input type="tel" name="telefone" value="<?= e($candidato_data['telefone']) ?>">
                </div>
            </div>

            <div class="input-group">
                <div class="input-box half-width">
                    <label>WhatsApp (Celular)</label>
                    <input type="tel" name="whatsapp" value="<?= e($candidato_data['whatsapp'] ?? '') ?>" placeholder="(XX) 9XXXX-XXXX">
                </div>

                <div class="input-box half-width">
                    <label>CEP *</label>
                    <input type="text" name="cep_candidato" id="cep" value="<?= e($candidato_data['cep']) ?>" placeholder="00000-000" required>
                    <small id="cep-error" style="color: #dc3545; display: none; font-size: 0.85rem; margin-top: 4px;"></small>
                </div>
            </div>

            <div class="input-group">
                <div class="input-box half-width">
                    <label>Cidade *</label>
                    <input type="text" name="cidade" id="cidade" value="<?= e($candidato_data['cidade']) ?>" required>
                </div>
                <div class="input-box half-width">
                    <label>Estado *</label>
                    <input type="text" name="estado" id="estado" value="<?= e($candidato_data['estado']) ?>" required>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Sobre Mim: *</legend>
            <div class="input-box">
                <textarea name="sobre_mim" rows="5" placeholder="Fale um pouco sobre suas habilidades e objetivos." required><?= e($candidato_data['sobre_mim']) ?></textarea>
            </div>
        </fieldset>

        <fieldset>
            <legend>Resumo Profissional:</legend>
            <div class="input-box">
                <textarea name="resumo_profissional" rows="5" placeholder="Fale um pouco sobre sua trajetória no mercado de trabalho."><?= e($candidato_data['resumo_profissional']) ?></textarea>
            </div>
        </fieldset>

        <!-- Seção Dinâmica: Experiências -->
        <fieldset>
            <legend>Experiência Profissional:</legend>
            <div id="experiencias-wrapper">
                <?php foreach ($experiencias as $exp) : ?>
                    <div class="experiencia-item box">
                        <div class="input-box"><label>Cargo</label><input type="text" name="exp_cargo[]" value="<?= e($exp['cargo']) ?>" placeholder="Ex: Vendedor"></div>
                        <div class="input-box"><label>Empresa</label><input type="text" name="exp_empresa[]" value="<?= e($exp['empresa']) ?>" placeholder="Ex: Empresa X"></div>
                        <div class="input-box"><label>Descrição</label><textarea name="exp_descricao[]"><?= e($exp['descricao']) ?></textarea></div>
                        <div class="input-group">
                            <div class="input-box half-width"><label>Início</label><input type="date" class="data-inicio" name="exp_data_inicio[]" value="<?= e($exp['data_inicio']) ?>" max="<?= date('Y-m-d') ?>"></div>
                            <div class="input-box half-width"><label>Término</label><input type="date" class="data-fim" name="exp_data_fim[]" value="<?= e($exp['data_fim']) ?>" max="<?= date('Y-m-d') ?>"></div>
                        </div>
                        <div class="error-msg-date text-danger small" style="display:none; color:#dc3545; margin-top:5px; font-size:0.85rem;">A data de início não pode ser superior à data de término.</div>
                        <div class="input-box">
                            <label class="checkbox-label">
                                <input type="hidden" name="exp_emprego_atual[]" value="<?= !empty($exp['emprego_atual']) ? '1' : '0' ?>">
                                <input type="checkbox" class="check-emprego-atual" <?= !empty($exp['emprego_atual']) ? 'checked' : '' ?> style="width:auto;"> Emprego Atual
                            </label>
                        </div>
                        <div class="item-actions">
                            <button type="button" class="btn-remove btn btn-danger btn-sm">Remover</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="add-experiencia" class="btn btn-secondary">+ Adicionar Experiência</button>
        </fieldset>

        <!-- Seção Dinâmica: Formações -->
        <fieldset>
            <legend>Formação Acadêmica:</legend>
            <div id="formacoes-wrapper">
                <?php foreach ($formacoes as $form) : ?>
                    <div class="formacao-item box">
                        <div class="input-box">
                            <label>Escolaridade *</label>
                            <select name="form_grau[]" required>
                                <?php foreach ($graus as $g) : ?>
                                    <option value="<?= $g ?>" <?= (e($form['grau']) == $g) ? 'selected' : '' ?>><?= $g ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-box"><label>Curso *</label><input type="text" name="form_curso[]" value="<?= e($form['curso']) ?>" required></div>
                        <div class="input-group">
                            <div class="input-box half-width"><label>Instituição *</label><input type="text" name="form_instituicao[]" value="<?= e($form['instituicao']) ?>" required></div>
                            <div class="input-box half-width"><label>Localidade</label><input type="text" name="form_localidade[]" value="<?= e($form['localidade']) ?>"></div>
                        </div>
                        <div class="input-group">
                            <div class="input-box half-width"><label>Início</label><input type="date" class="data-inicio" name="form_data_inicio[]" value="<?= e($form['data_inicio']) ?>" max="<?= date('Y-m-d') ?>"></div>
                            <div class="input-box half-width"><label>Término</label><input type="date" class="data-fim" name="form_data_fim[]" value="<?= e($form['data_fim']) ?>" max="<?= date('Y-m-d') ?>"></div>
                        </div>
                        <div class="error-msg-date text-danger small" style="display:none; color:#dc3545; margin-top:5px; font-size:0.85rem;">A data de início não pode ser superior à data de término.</div>
                        <div class="input-box">
                            <label>Status</label>
                            <select name="form_status[]">
                                <option value="Cursando" <?= (e($form['status']) == "Cursando") ? "selected" : "" ?>>Cursando</option>
                                <option value="Concluído" <?= (e($form['status']) == "Concluído") ? "selected" : "" ?>>Concluído</option>
                                <option value="Trancado" <?= (e($form['status']) == "Trancado") ? "selected" : "" ?>>Trancado</option>
                            </select>
                        </div>
                        <div class="item-actions">
                            <button type="button" class="btn-remove btn btn-danger btn-sm">Remover</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="add-formacao" class="btn btn-secondary">+ Adicionar Formação</button>
        </fieldset>

        <!-- Seção Dinâmica: Cursos -->
        <fieldset>
            <legend>Cursos e Aperfeiçoamento:</legend>
            <div id="cursos-wrapper">
                <?php foreach ($cursos as $curso) : ?>
                    <div class="curso-item box">
                        <div class="input-box"><label>Nome do Curso</label><input type="text" name="curso_nome[]" value="<?= e($curso['nome_curso']) ?>"></div>
                        <div class="input-box"><label>Instituição</label><input type="text" name="curso_instituicao[]" value="<?= e($curso['instituicao']) ?>"></div>
                        <div class="input-box"><label>Localidade</label><input type="text" name="curso_localidade[]" value="<?= e($curso['localidade']) ?>"></div>
                        <div class="input-box"><label>Ano de Conclusão</label><input type="number" name="curso_ano_conclusao[]" value="<?= e($curso['ano_conclusao']) ?>"></div>
                        <div class="item-actions">
                            <button type="button" class="btn-remove btn btn-danger btn-sm">Remover</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="add-curso" class="btn btn-secondary">+ Adicionar Curso</button>
        </fieldset>

        <fieldset>
            <legend>Anexar Currículo (PDF ou DOCX)</legend>
            <div class="input-box file-upload-wrapper" style="position: relative;">
                <input type="file" id="curriculo-file" name="curriculo_file"
                    style="opacity: 0; position: absolute; top: 0; left: 0; width: 1px; height: 1px;"
                    accept=".pdf,.doc,.docx"
                    <?= empty($candidato_data['curriculo_path']) ? '' : '' ?>>
                <label for="curriculo-file" class="btn btn-secondary">
                    <i class='bx bx-upload'></i> Escolher Arquivo
                </label>
                <span id="file-chosen-text" style="color: var(--text-muted-color);">
                    <?= !empty($candidato_data['curriculo_path']) ? htmlspecialchars($candidato_data['curriculo_path']) : 'Nenhum arquivo selecionado' ?>
                </span>
            </div>

            <div id="curriculo-anexado-info" style="margin-top: 15px; display: <?= !empty($candidato_data['curriculo_path']) ? 'flex' : 'none' ?>; align-items: center; gap: 15px;">
                <a id="ver-curriculo-link" href="<?= BASE_URL ?>uploads/curriculos/<?= e($candidato_data['curriculo_path']) ?>" target="_blank" class="btn" style="border: 1px solid var(--border-color);">
                    <i class='bx bx-file'></i> Ver Currículo Atual
                </a>
                <button type="button" id="btn-remover-curriculo" class="btn btn-danger btn-sm">
                    <i class='bx bx-trash'></i> Remover
                </button>
            </div>
        </fieldset>

        <hr style="margin: 30px 0;">

        <button type="submit" class="btn btn-primary btn-block" style="padding: 15px; font-size: 1.2rem;">Salvar Currículo</button>

    </form>
</div>



<!-- Simple Address Fetch Script (ViaCEP) -->
<script>
    // Listener delegado para botões Remover (confirm() nativo - simples e confiável)
    document.addEventListener('click', function(e) {
        const btn = e.target.classList.contains('btn-remove') ? e.target : e.target.closest('.btn-remove');
        if (btn) {
            const item = btn.closest('.box');
            if (item && confirm('Deseja realmente remover este item do seus currículo?')) {
                item.remove();
            }
        }
    });

    document.getElementById('cep').addEventListener('blur', function() {
        const cepRaw = this.value.replace(/\D/g, '');

        if (cepRaw.length !== 8) {
            // CEP Incompleto
            // alert('CEP inválido: digite os 8 números.'); // Opcional: alerta chato
            this.setCustomValidity('CEP incompleto');
            this.reportValidity();
            return;
        } else {
            this.setCustomValidity('');
        }

        if (cepRaw.length === 8) {
            // Feedback de Carregando...
            document.body.style.cursor = 'wait';
            const errorSpan = document.getElementById('cep-error');
            errorSpan.style.display = 'none';

            fetch(`https://viacep.com.br/ws/${cepRaw}/json/`)
                .then(res => res.json())
                .then(data => {
                    document.body.style.cursor = 'default';
                    const cepField = document.getElementById('cep'); // Ensure we have the element reference

                    if (data.erro) {
                        // Alert visual in red text
                        errorSpan.textContent = 'CEP não encontrado.';
                        errorSpan.style.display = 'block';

                        // Block submit
                        cepField.setCustomValidity('CEP não encontrado. Corrija para salvar.');

                        document.getElementById('cidade').value = '';
                        document.getElementById('estado').value = '';
                        // document.getElementById('cep').value = ''; // Don't clear user input, just show error
                    } else {
                        document.getElementById('cidade').value = data.localidade;
                        document.getElementById('estado').value = data.uf;
                        errorSpan.style.display = 'none';

                        // Allow submit
                        cepField.setCustomValidity('');
                    }
                })
                .catch(() => {
                    document.body.style.cursor = 'default';
                    errorSpan.textContent = 'Erro ao buscar CEP.';
                    errorSpan.style.display = 'block';
                    // Optional: Block on connection error? Maybe not, allow manual entry?
                    // Let's block to be safe or warn. Better to let user fix connection or try again.
                    // For now, let's NOT block strict on connection error, but block on 'NotFound'.
                });
        }
    });

    // Clear error on input
    const cepInputEl = document.getElementById('cep');
    if (cepInputEl) {
        cepInputEl.addEventListener('input', function() {
            const errorSpan = document.getElementById('cep-error');
            if (errorSpan) errorSpan.style.display = 'none';

            // Clear validity to allow typing and eventually submitting if logic permits (or wait for blur)
            this.setCustomValidity('');
        });
    }

    // Dynamic Fields Script
    function createRemoveButton() {
        return `<div class="item-actions"><button type="button" class="btn-remove btn btn-danger btn-sm">Remover</button></div>`;
    }

    // Listener delegado para checkboxes "Emprego Atual"
    document.body.addEventListener('change', function(e) {
        if (e.target.classList.contains('check-emprego-atual')) {
            const checkbox = e.target;
            const box = checkbox.closest('.box');
            const dataFimInput = box.querySelector('.data-fim');

            if (dataFimInput) {
                if (checkbox.checked) {
                    dataFimInput.value = '';
                    dataFimInput.readOnly = true;
                    dataFimInput.style.backgroundColor = '#eee';
                } else {
                    dataFimInput.readOnly = false;
                    dataFimInput.style.backgroundColor = '';
                }
            }

            // Sync hidden field value (0 or 1)
            const hiddenInput = checkbox.parentElement.querySelector('input[type="hidden"]');
            if (hiddenInput) hiddenInput.value = checkbox.checked ? '1' : '0';
        }

        if (e.target.type === 'date') {
            validateDateRange(e.target);
        }
    });

    document.body.addEventListener('focusout', function(e) {
        if (e.target.type === 'date') {
            validateDateRange(e.target);
        }
    });

    function validateDateRange(input) {
        const box = input.closest('.box');
        if (!box) return;

        const dataInicio = box.querySelector('.data-inicio');
        const dataFim = box.querySelector('.data-fim');
        const errorMsg = box.querySelector('.error-msg-date');

        // Se não tiver mensagem de erro no HTML (caso legado/cache), cria dinamicamente
        if (!errorMsg && (dataInicio || dataFim)) {
            // Fallback: tenta inserir se não existir
            // Mas como alteramos o template, deve existir. Se não, não exibe nada ou loga erro.
            return;
        }

        if (dataInicio && dataFim) {
            // Remove validade customizada anterior
            dataInicio.setCustomValidity('');
            dataFim.setCustomValidity('');

            if (dataInicio.value && dataFim.value) {
                if (dataInicio.value > dataFim.value) {
                    // Inválido
                    if (errorMsg) errorMsg.style.display = 'block';
                    // Trava o submit
                    input.setCustomValidity('Data inválida');
                } else {
                    // Válido
                    if (errorMsg) errorMsg.style.display = 'none';
                    if (input.validity.customError && input.validationMessage === 'Data inválida') {
                        input.setCustomValidity('');
                    }
                }
            } else {
                // Se um dos dois estiver vazio, limpa erro (está digitando)
                if (errorMsg) errorMsg.style.display = 'none';
            }
        }
    }

    // Inicializa estado dos checkboxes "Emprego Atual"
    window.addEventListener('load', () => {
        document.querySelectorAll('.check-emprego-atual').forEach(chk => {
            if (chk.checked) chk.dispatchEvent(new Event('change', {
                bubbles: true
            }));
        });
    });

    document.getElementById('add-experiencia').addEventListener('click', function() {
        const html = `
    <div class="experiencia-item box">
        <div class="input-box"><label>Cargo *</label><input type="text" name="exp_cargo[]" placeholder="Ex: Vendedor" required></div>
        <div class="input-box"><label>Empresa *</label><input type="text" name="exp_empresa[]" placeholder="Ex: Empresa X" required></div>
        <div class="input-box"><label>Descrição</label><textarea name="exp_descricao[]"></textarea></div>
        <div class="input-group">
            <div class="input-box half-width"><label>Início *</label><input type="date" class="data-inicio" name="exp_data_inicio[]" required max="<?= date('Y-m-d') ?>"></div>
            <div class="input-box half-width"><label>Término</label><input type="date" class="data-fim" name="exp_data_fim[]" max="<?= date('Y-m-d') ?>"></div>
        </div>
        <div class="error-msg-date text-danger small" style="display:none; color:#dc3545; margin-top:5px; font-size:0.85rem;">A data de início não pode ser superior à data de término.</div>
        <div class="input-box">
            <label class="checkbox-label">
                <input type="hidden" name="exp_emprego_atual[]" value="0">
                <input type="checkbox" class="check-emprego-atual" style="width:auto;"> Emprego Atual
            </label>
        </div>
        ${createRemoveButton()}
    </div>`;
        document.getElementById('experiencias-wrapper').insertAdjacentHTML('beforeend', html);
    });

    document.getElementById('add-formacao').addEventListener('click', function() {
        const html = `
    <div class="formacao-item box">
        <div class="input-box">
            <label>Escolaridade *</label>
            <select name="form_grau[]" required>
                <option value="">Selecione...</option>
                <option value="Ensino Médio">Ensino Médio</option>
                <option value="Ensino Superior">Ensino Superior</option>
                <option value="Pós-graduação">Pós-graduação</option>
                <option value="Mestrado">Mestrado</option>
                <option value="Doutorado">Doutorado</option>
            </select>
        </div>
        <div class="input-box"><label>Curso *</label><input type="text" name="form_curso[]" required></div>
        <div class="input-group">
            <div class="input-box half-width"><label>Instituição *</label><input type="text" name="form_instituicao[]" required></div>
            <div class="input-box half-width"><label>Localidade</label><input type="text" name="form_localidade[]"></div>
        </div>
        <div class="input-group">
            <div class="input-box half-width"><label>Início</label><input type="date" class="data-inicio" name="form_data_inicio[]" max="<?= date('Y-m-d') ?>"></div>
            <div class="input-box half-width"><label>Término</label><input type="date" class="data-fim" name="form_data_fim[]" max="<?= date('Y-m-d') ?>"></div>
        </div>
        <div class="error-msg-date text-danger small" style="display:none; color:#dc3545; margin-top:5px; font-size:0.85rem;">A data de início não pode ser superior à data de término.</div>
        <div class="input-box">
             <label>Status</label>
             <select name="form_status[]">
                 <option value="Cursando">Cursando</option>
                 <option value="Concluído">Concluído</option>
                 <option value="Trancado">Trancado</option>
             </select>
        </div>
        ${createRemoveButton()}
    </div>`;
        document.getElementById('formacoes-wrapper').insertAdjacentHTML('beforeend', html);
    });

    document.getElementById('add-curso').addEventListener('click', function() {
        const html = `
    <div class="curso-item box">
        <div class="input-box"><label>Nome do Curso</label><input type="text" name="curso_nome[]"></div>
        <div class="input-box"><label>Instituição</label><input type="text" name="curso_instituicao[]"></div>
        <div class="input-box"><label>Localidade</label><input type="text" name="curso_localidade[]"></div>
         <div class="input-box"><label>Ano de Conclusão</label><input type="number" name="curso_ano_conclusao[]"></div>
        ${createRemoveButton()}
    </div>`;
        document.getElementById('cursos-wrapper').insertAdjacentHTML('beforeend', html);
    });

    // File Upload Logic
    document.getElementById('curriculo-file').addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            document.getElementById('file-chosen-text').textContent = e.target.files[0].name;
        }
    });

    document.getElementById('btn-remover-curriculo').addEventListener('click', function() {
        if (confirm('Remover currículo atual?')) {
            fetch('<?= BASE_URL ?>curriculo/removeFile', {
                    method: 'POST'
                })
                .then(res => {
                    if (!res.ok) throw new Error('Erro na requisição');
                    return res.json();
                })
                .then(data => {
                    if (data.status == 'success') {
                        document.getElementById('curriculo-anexado-info').style.display = 'none';
                        document.getElementById('file-chosen-text').textContent = 'Nenhum arquivo selecionado';
                        // document.getElementById('curriculo-file').required = true; // Removido
                        alert('Currículo removido!');
                    } else {
                        alert('Erro ao remover: ' + (data.message || 'Desconhecido'));
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Erro ao tentar remover o currículo.');
                });
        }
    });

    // Validar Idiomas e Upload (HTML5 Standard)
    window.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('form');
        const idiomasCheckboxes = document.querySelectorAll('input[name="idiomas[]"]');

        function validateIdiomas() {
            if (idiomasCheckboxes.length === 0) return;

            const anyChecked = Array.from(idiomasCheckboxes).some(cb => cb.checked);
            const firstCb = idiomasCheckboxes[0];
            if (firstCb) {
                if (!anyChecked) {
                    firstCb.setCustomValidity('Selecione pelo menos um idioma.');
                } else {
                    firstCb.setCustomValidity('');
                }
            }
        }

        if (form) {
            form.addEventListener('submit', function(e) {
                validateIdiomas(); // Force check
            });
        }

        if (idiomasCheckboxes.length > 0) {
            idiomasCheckboxes.forEach(cb => {
                cb.addEventListener('change', validateIdiomas);
            });
        }

        // --- CEP Mask & Validation ---
        const cepInput = document.getElementById('cep');
        if (cepInput) {
            cepInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, ''); // Remove non-numeric
                if (value.length > 5) {
                    value = value.substring(0, 5) + '-' + value.substring(5, 8);
                }
                e.target.value = value;
            });

            // Remove existing listener if any to avoid duplication logic or conflicts (existing listener attached in HTML previously?)
            // The existing View has: document.getElementById('cep').addEventListener('blur', function() { ... });
            // Let's keep existing logic but just add mask support.
        }
        const idiomasContainer = document.getElementById('idiomas-container');
        const savedIdiomasStr = document.getElementById('saved-idiomas').value;
        const hiddenInputsContainer = document.getElementById('idiomas-hidden-inputs');
        const btnAddOutro = document.getElementById('btn-add-outro-idioma');

        // Lista de idiomas completa disponivel no JS
        const listaIdiomasDisponiveis = [
            'Alemão', 'Chinês (Mandarim)', 'Francês', 'Italiano', 'Japonês', 'Libras', 'Russo', 'Árabe', 'Coreano', 'Holandês', 'Polonês'
        ];

        // Parse Saved Data
        const savedIdiomas = [];
        if (savedIdiomasStr) {
            const parts = savedIdiomasStr.split(',').map(s => s.trim());
            parts.forEach(p => {
                const match = p.match(/^(.*?)\s*\((.*?)\)$/);
                if (match) {
                    savedIdiomas.push({
                        name: match[1],
                        level: match[2]
                    });
                } else {
                    const name = p.replace(/\s*\(.*?\)$/, '');
                    savedIdiomas.push({
                        name: name,
                        level: 'Básico'
                    });
                }
            });
        }

        // Hydrate Presets (Static Cards)
        document.querySelectorAll('.language-card[data-lang]').forEach(card => {
            const langName = card.dataset.lang;
            const saved = savedIdiomas.find(i => i.name === langName);

            card.addEventListener('click', (e) => {
                if (e.target.tagName === 'SELECT') return;
                toggleCard(card);
                syncIdiomas();
            });

            const select = card.querySelector('select');
            select.addEventListener('change', syncIdiomas);

            if (saved) {
                toggleCard(card, true);
                select.value = saved.level;
            }
        });

        // Toggle Function for Presets
        function toggleCard(card, forceState = null) {
            const levelDiv = card.querySelector('.lang-level');
            const checkIcon = card.querySelector('.check-icon');

            let isActive = card.classList.contains('active');
            if (forceState !== null) isActive = !forceState;

            if (isActive) {
                card.classList.remove('active');
                levelDiv.classList.add('hidden');
                if (checkIcon) checkIcon.style.opacity = '0';
            } else {
                card.classList.add('active');
                levelDiv.classList.remove('hidden');
                if (checkIcon) checkIcon.style.opacity = '1';
            }
        }

        // Hydrate Custom (Dynamic Cards)
        savedIdiomas.forEach(item => {
            if (!['Inglês', 'Espanhol', 'Português'].includes(item.name)) {
                addCustomLanguageCard(item.name, item.level);
            }
        });

        btnAddOutro.addEventListener('click', () => {
            addCustomLanguageCard('', 'Básico');
        });

        function addCustomLanguageCard(selectedName = '', level = 'Básico') {
            const div = document.createElement('div');
            div.className = 'language-card active custom-card'; // Always active upon creation

            let optionsHtml = '<option value="">Selecione...</option>';
            listaIdiomasDisponiveis.forEach(lang => {
                const isSel = lang === selectedName ? 'selected' : '';
                optionsHtml += `<option value="${lang}" ${isSel}>${lang}</option>`;
            });

            if (selectedName && !listaIdiomasDisponiveis.includes(selectedName)) {
                optionsHtml += `<option value="${selectedName}" selected>${selectedName}</option>`;
            }

            div.innerHTML = `
            <div class="card-remove-btn"><i class='bx bx-x'></i></div>
            <div style="margin-bottom: 5px;">
                <select class="lang-name-select" style="font-weight:600;">
                    ${optionsHtml}
                </select>
            </div>
            <div class="lang-level">
                <select class="form-control form-control-sm level-select">
                    <option value="Básico" ${level === 'Básico' ? 'selected' : ''}>Básico</option>
                    <option value="Intermediário" ${level === 'Intermediário' ? 'selected' : ''}>Intermediário</option>
                    <option value="Avançado" ${level === 'Avançado' ? 'selected' : ''}>Avançado</option>
                    <option value="Fluente" ${level === 'Fluente' ? 'selected' : ''}>Fluente</option>
                </select>
            </div>
        `;

            // Insert before the Add Button
            idiomasContainer.insertBefore(div, btnAddOutro);

            // Listeners
            div.querySelector('.card-remove-btn').addEventListener('click', (e) => {
                e.stopPropagation(); // prevent triggering card click if we had one
                div.remove();
                syncIdiomas();
            });

            div.querySelector('.lang-name-select').addEventListener('change', syncIdiomas);
            div.querySelector('.level-select').addEventListener('change', syncIdiomas);

            syncIdiomas();
        }

        function syncIdiomas() {
            hiddenInputsContainer.innerHTML = '';

            // 1. Static Presets Check
            document.querySelectorAll('.language-card[data-lang].active').forEach(card => {
                const name = card.dataset.lang;
                const level = card.querySelector('.level-select').value;
                createHiddenInput(`${name} (${level})`);
            });

            // 2. Custom Dynamic Cards
            document.querySelectorAll('.language-card.custom-card').forEach(card => {
                const nameSelect = card.querySelector('.lang-name-select');
                const levelSelect = card.querySelector('.level-select');

                if (nameSelect && levelSelect) {
                    const name = nameSelect.value;
                    const level = levelSelect.value;
                    if (name) {
                        createHiddenInput(`${name} (${level})`);
                    }
                }
            });
        }

        function createHiddenInput(value) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'idiomas[]';
            input.value = value;
            hiddenInputsContainer.appendChild(input);
        }

        // Initial sync
        syncIdiomas();

        // Nacionalidade/RNE Toggle
        const nac = document.getElementById('nacionalidade');
        const rneBox = document.getElementById('campo_rne');
        if (nac && rneBox) {
            nac.addEventListener('change', function() {
                if (this.value === 'Estrangeiro(a)') {
                    rneBox.style.display = 'block';
                } else {
                    rneBox.style.display = 'none';
                }
            });
        }
    });
</script>