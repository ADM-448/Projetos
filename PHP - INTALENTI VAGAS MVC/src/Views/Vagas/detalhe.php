<?php
// Lógica de visualização (Helpers)
$v = $vaga;

// Formatação de Salário
$salario_formatado = '';
$classe_salario = '';

if (!empty($v['salario_a_combinar'])) {
    $salario_formatado = 'Salário: A combinar';
} elseif (!empty($v['faixa_salarial_min']) && !empty($v['faixa_salarial_max'])) {
    $min = number_format($v['faixa_salarial_min'], 2, ',', '.');
    $max = number_format($v['faixa_salarial_max'], 2, ',', '.');
    $salario_formatado = "R$ $min - R$ $max";
    $classe_salario = 'salary-value';
} elseif (!empty($v['faixa_salarial_min'])) {
    $min = number_format($v['faixa_salarial_min'], 2, ',', '.');
    $salario_formatado = "A partir de R$ $min";
    $classe_salario = 'salary-value';
} elseif (!empty($v['faixa_salarial_max'])) {
    $max = number_format($v['faixa_salarial_max'], 2, ',', '.');
    $salario_formatado = "Até R$ $max";
    $classe_salario = 'salary-value';
}

$back_link = BASE_URL . "vagas"; // Simplificado por enquanto, depois podemos restaurar filtros da sessão
?>

<div class="page-container">
    <main class="job-content">
        <div class="job-header">

            <div class="job-title-bar">
                <h1><?= htmlspecialchars($v['titulo']) ?></h1>

                <div class="job-extras-wrapper">
                    <?php if (!$v['confidencial'] && !empty($v['logo_path'])): ?>
                        <div class="job-logo">
                            <img src="<?= BASE_URL ?>uploads/logos/<?= htmlspecialchars($v['logo_path']) ?>"
                                alt="Logo <?= htmlspecialchars($v['nome_fantasia'] ?? $v['nome_empresa']) ?>">
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="header-meta">
                <span>
                    <i class='bx bx-buildings'></i>
                    <?= $v['confidencial'] ? 'Empresa Confidencial' : htmlspecialchars($v['nome_fantasia'] ?? $v['nome_empresa']) ?>
                </span>
                <span>
                    <i class='bx bx-map'></i>
                    <?= htmlspecialchars($v['localizacao']) ?>
                </span>
                <?php if (!empty($v['created_at'])): ?>
                    <span>
                        <i class='bx bx-calendar'></i>
                        Postada em: <?= date('d/m/Y', strtotime($v['created_at'])) ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($v['ramo_atividade'])): ?>
                    <span>
                        <i class='bx bx-briefcase'></i>
                        <?= htmlspecialchars($v['ramo_atividade']) ?>
                    </span>
                <?php endif; ?>

                <?php
                $mapa_experiencia = [
                    'com' => 'Exige Experiência',
                    'sem' => 'Não exige Experiência',
                    'indiferente' => 'Experiência Indiferente'
                ];
                if (isset($v['experiencia']) && array_key_exists($v['experiencia'], $mapa_experiencia)) {
                    $texto_experiencia = $mapa_experiencia[$v['experiencia']];
                ?>
                    <span>
                        <i class='bx bx-badge-check'></i>
                        <?= htmlspecialchars($texto_experiencia) ?>
                    </span>
                <?php } ?>

                <?php if (!empty($salario_formatado)): ?>
                    <span class="<?= $classe_salario ?>">
                        <i class='bx bx-dollar'></i>
                        <?= $salario_formatado ?>
                    </span>
                <?php endif; ?>
            </div>

        </div>

        <section class="job-section">
            <h2>Descrição da Vaga</h2>
            <p><?= nl2br(htmlspecialchars($v['descricao'])) ?></p>
        </section>

        <section class="job-section">
            <h2>Requisitos e Qualificações</h2>
            <ul>
                <?php
                if (!empty($v['requisitos'])) {
                    $requisitos = explode("\n", trim($v['requisitos']));
                    foreach ($requisitos as $req) {
                        if (trim($req)) echo "<li><i class='bx bx-check'></i> " . htmlspecialchars(trim($req)) . "</li>";
                    }
                } else {
                    echo "<li>Não informado.</li>";
                }
                ?>
            </ul>
        </section>

        <section class="job-section">
            <h2>Benefícios</h2>
            <ul>
                <?php
                if (!empty($v['beneficios'])) {
                    $beneficios = explode("\n", trim($v['beneficios']));
                    foreach ($beneficios as $ben) {
                        if (trim($ben)) echo "<li><i class='bx bx-check'></i> " . htmlspecialchars(trim($ben)) . "</li>";
                    }
                } else {
                    echo "<li>Não informado.</li>";
                }
                ?>
            </ul>
        </section>
    </main>

    <aside class="actions-sidebar">
        <div class="summary-card">
            <h3>Resumo da Vaga</h3>

            <?php if (!empty($msg)): ?>
                <div style="color: #28a745; text-align: center; margin-bottom: 15px;"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>

            <?php if (!empty($msg_error)): ?>
                <div style="color: #dc3545; text-align: center; margin-bottom: 15px;"><?= htmlspecialchars($msg_error) ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_id']) && ($_SESSION['tipo_usuario'] ?? $_SESSION['user_type']) == 1): // Se for candidato 
            ?>



                <?php if ($ja_candidatado): ?>
                    <p style="text-align: center; margin-bottom: 10px; color: #28a745;">
                        <strong><i class='bx bx-check-double'></i> Candidatura Enviada</strong>
                    </p>

                    <!-- Botão de Chat (Condicional) -->
                    <?php if (!empty($chat_id) && !empty($chat_tem_mensagens)): ?>
                        <a href="<?= BASE_URL ?>chat/index/<?= $chat_id ?>" class="btn w-100 mb-2 fw-bold text-decoration-none" style="background-color: #581d22; color: #fff; padding: 10px; border-radius: 5px; display: inline-block;">
                            <i class='bx bx-message-square-dots'></i> Falar com Recrutador
                        </a>
                    <?php endif; ?>

                    <button type="button" id="open-cancel-modal-btn" class="btn btn-danger btn-block"><i class='bx bx-x-circle'></i> Cancelar Candidatura</button>

                <?php else: ?>
                    <?php if (!$perfil_completo): ?>
                        <button type="button" class="btn btn-primary btn-block" onclick="openModal('perfil-incompleto-modal')">
                            <i class='bx bx-rocket'></i> Candidatar-se
                        </button>
                    <?php elseif (count($perguntas) > 0): ?>
                        <button type="button" id="open-modal-btn" class="btn btn-primary btn-block">
                            <i class='bx bx-rocket'></i> Candidatar-se
                        </button>
                    <?php else: ?>
                        <form action="<?= BASE_URL ?>candidatura/aplicar" method="POST">
                            <input type="hidden" name="vaga_id" value="<?= $v['id'] ?>">
                            <button type="submit" class="btn btn-primary btn-block"><i class='bx bx-rocket'></i> Candidatar-se</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>

            <?php elseif (isset($_SESSION['user_id']) && ($_SESSION['tipo_usuario'] ?? $_SESSION['user_type']) != 1): ?>
                <p style="text-align:center; color: #ccc;">Apenas candidatos podem se inscrever nas vagas.</p>
            <?php else: ?>
                <p style="text-align:center; color: #ccc;">Faça login como candidato para se inscrever.</p>
                <a href="<?= BASE_URL ?>auth/login" class="btn btn-primary btn-block">Login</a>
            <?php endif; ?>

            <a href="<?= $back_link ?>" class="btn btn-secondary btn-sm btn-block" style="background-color: #6c757d; border: none; color: #fff; padding: 10px; font-weight: 500; margin-top: 10px; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 5px;">
                <i class='bx bx-arrow-back'></i> Voltar
            </a>
        </div>
    </aside>
</div>

<!-- Modal Questionário -->
<?php if (isset($perguntas) && count($perguntas) > 0 && !$ja_candidatado): ?>
    <div id="questionario-modal" class="modal-overlay">
        <div class="modal-content">
            <button class="modal-close-btn" id="close-modal-btn">&times;</button>
            <form action="<?= BASE_URL ?>candidatura/aplicar" method="POST">
                <input type="hidden" name="vaga_id" value="<?= $v['id'] ?>">
                <input type="hidden" name="tem_questionario" value="1">

                <h3>Enviar Currículo</h3>
                <p class="subtitle" style="margin-top:-1rem; color: #666;">Responda às perguntas abaixo para continuar.</p>

                <?php foreach ($perguntas as $index => $pergunta): ?>
                    <div class="questionario-item" data-eliminatoria="<?= $pergunta['eliminatoria'] ?>">
                        <input type="hidden" name="pergunta_id[]" value="<?= $pergunta['id'] ?>">
                        <p><strong><?= htmlspecialchars($pergunta['texto_pergunta']) ?></strong></p>
                        <div class="respostas-group">
                            <label><input type="radio" name="respostas[<?= $index ?>]" value="Sim" required> Sim</label>
                            <label><input type="radio" name="respostas[<?= $index ?>]" value="Não" required> Não</label>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div id="eliminatoria-aviso" style="display:none; padding: 10px 15px; background-color: #ffebee; border: 1px solid #dc3545; color: #dc3545; border-radius: var(--border-radius); margin-bottom: 15px;">
                    <strong>Atenção:</strong> você respondeu ‘Não’ a um requisito obrigatório para esta vaga. Por esse motivo, não será possível prosseguir com a sua candidatura.
                </div>

                <button type="submit" class="btn btn-primary btn-block">Enviar Currículo <i class='bx bx-send'></i></button>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- Modal Cancelamento -->
<?php if ($ja_candidatado): ?>
    <div id="cancelar-modal" class="modal-overlay">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <button class="modal-close-btn" id="close-cancel-btn">&times;</button>
            <div style="margin-bottom: 20px;">
                <i class='bx bx-error-circle' style='font-size: 4rem; color: #dc3545;'></i>
            </div>
            <h3>Cancelar Candidatura?</h3>
            <p class="subtitle" style="margin-bottom: 20px;">Tem certeza que deseja cancelar sua candidatura para esta vaga?</p>

            <form action="<?= BASE_URL ?>candidatura/cancelar" method="POST">
                <input type="hidden" name="vaga_id" value="<?= $v['id'] ?>">
                <div style="display: flex; gap: 10px; justify-content: center;">
                    <button type="button" class="btn btn-secondary" id="cancel-cancel-btn">Não, voltar</button>
                    <button type="submit" class="btn btn-danger">Sim, cancelar</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- Modal Perfil Incompleto -->
<div id="perfil-incompleto-modal" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px; text-align: center;">
        <button class="modal-close-btn" onclick="closeModal('perfil-incompleto-modal')">&times;</button>
        <div style="margin-bottom: 20px;">
            <i class='bx bx-id-card' style='font-size: 4rem; color: #f0ad4e;'></i>
        </div>
        <h3 style="color: #333;">Complete seu Perfil</h3>
        <p class="subtitle" style="margin-bottom: 25px; color: #666;">
            Para se candidatar a esta vaga, você precisa preencher as informações obrigatórias do seu currículo (Dados Pessoais e Sobre Mim).
        </p>

        <div style="display: flex; gap: 10px; justify-content: center;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('perfil-incompleto-modal')">Agora não</button>
            <a href="<?= BASE_URL ?>curriculo" class="btn btn-primary" style="background-color: #f0ad4e; border-color: #f0ad4e; color: #fff;">
                Ir para Meu Currículo
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        // --- Modal Helper Functions ---
        window.closeModal = function(id) {
            const m = document.getElementById(id);
            if (m) {
                m.classList.remove('active');
                setTimeout(() => m.style.display = 'none', 300);
            }
        }

        window.openModal = function(id) {
            const m = document.getElementById(id);
            if (m) {
                m.style.display = 'flex';
                setTimeout(() => m.classList.add('active'), 10);
            }
        }

        // --- Questionário Logic ---
        const modal = document.getElementById('questionario-modal');
        if (modal) {
            const openBtn = document.getElementById('open-modal-btn');
            const closeBtn = document.getElementById('close-modal-btn');

            if (openBtn) openBtn.addEventListener('click', () => openModal('questionario-modal'));
            if (closeBtn) closeBtn.addEventListener('click', () => closeModal('questionario-modal'));

            // Validation Logic (Questionario)
            const submitBtn = modal.querySelector('button[type="submit"]');
            const radios = modal.querySelectorAll('input[type="radio"]');
            const avisoDiv = document.getElementById('eliminatoria-aviso');

            function validarRespostas() {
                let desclassificado = false;
                const perguntasEliminatorias = modal.querySelectorAll('.questionario-item[data-eliminatoria="1"]');
                for (const item of perguntasEliminatorias) {
                    const radioNao = item.querySelector('input[value="Não"]');
                    if (radioNao && radioNao.checked) {
                        desclassificado = true;
                        break;
                    }
                }
                if (desclassificado) {
                    avisoDiv.style.display = 'block';
                    submitBtn.disabled = true;
                } else {
                    avisoDiv.style.display = 'none';
                    submitBtn.disabled = false;
                }
            }
            radios.forEach(radio => radio.addEventListener('change', validarRespostas));
        }

        // --- Cancelamento Logic ---
        const cancelModal = document.getElementById('cancelar-modal');
        if (cancelModal) {
            const openCancelBtn = document.getElementById('open-cancel-modal-btn');
            const closeCancelBtn = document.getElementById('close-cancel-btn');
            const btnNo = document.getElementById('cancel-cancel-btn');

            if (openCancelBtn) openCancelBtn.addEventListener('click', () => openModal('cancelar-modal'));
            if (closeCancelBtn) closeCancelBtn.addEventListener('click', () => closeModal('cancelar-modal'));
            if (btnNo) btnNo.addEventListener('click', () => closeModal('cancelar-modal'));
        }
    });
</script>