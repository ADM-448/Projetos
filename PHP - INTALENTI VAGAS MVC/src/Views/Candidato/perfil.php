<?php
// Função helper local para escapar HTML (igual ao original)
function e($str)
{
    return htmlspecialchars($str ?? 'Não informado', ENT_QUOTES, 'UTF-8');
}
?>

<!-- Container principal centralizado e com largura limitada -->
<div class="container main-content-container py-4" style="max-width: 1200px;">



    <!-- Wrapper Flexível -->
    <div class="page-content-wrapper" style="display: flex; gap: 25px; align-items: flex-start;">

        <!-- --- COLUNA ESQUERDA (Conteúdo) --- -->
        <main class="job-content" style="flex: 1; background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 40px; border: 1px solid #e0e0e0;">
            <div class="job-header" style="border-bottom: 1px solid #eee; padding-bottom: 25px; margin-bottom: 30px;">
                <h1 style="font-size: 2.2rem; margin: 0 0 15px 0; color: #2c3e50; font-weight: 700;"><?= e($candidato['nome_completo'] ?? $candidato['name']) ?></h1>

                <div class="header-meta" style="display: flex; flex-wrap: wrap; gap: 25px; font-size: 0.95rem; color: #666;">
                    <span style="display: flex; align-items: center; gap: 8px;"><i class='bx bx-envelope fs-5' style="color: #666;"></i> <?= e($candidato['email']) ?></span>

                    <?php if ($whatsapp_link): ?>
                        <span style="display: flex; align-items: center; gap: 8px;"><i class='bx bxl-whatsapp fs-5' style="color: #666;"></i> <?= e($candidato['whatsapp']) ?></span>
                    <?php endif; ?>

                    <?php if (!empty($candidato['telefone'])): ?>
                        <span style="display: flex; align-items: center; gap: 8px;"><i class='bx bx-phone fs-5' style="color: #666;"></i> <?= e($candidato['telefone']) ?></span>
                    <?php endif; ?>

                    <span style="display: flex; align-items: center; gap: 8px;"><i class='bx bx-map fs-5' style="color: #666;"></i> <?= e($candidato['cidade']) ?> - <?= e($candidato['estado']) ?></span>
                </div>
            </div>

            <section class="job-section mb-5">
                <h2 style="font-size: 1.6rem; color: #212529; font-weight: 700; margin-bottom: 20px;">Dados Pessoais</h2>
                <div style="font-size: 1rem; color: #444; line-height: 1.9;">
                    <p style="margin:0;"><strong>Data de Nascimento:</strong> <?= !empty($candidato['data_nascimento']) ? date('d/m/Y', strtotime($candidato['data_nascimento'])) : 'Não informado' ?></p>
                    <p style="margin:0;"><strong>Estado Civil:</strong> <?= e($candidato['estado_civil']) ?></p>
                    <p style="margin:0;"><strong>Sexo:</strong> <?= e($candidato['sexo']) == 'Outro' ? 'Prefiro não informar' : e($candidato['sexo']) ?></p>
                    <p style="margin:0;"><strong>Escolaridade (Nível Máximo):</strong> <?= e($candidato['escolaridade']) ?></p>
                    <p style="margin:0;"><strong>Habilitação:</strong> <?= e($candidato['carteira_habilitacao']) ?></p>
                    <p style="margin:0;"><strong>Nacionalidade:</strong> <?= e($candidato['nacionalidade']) ?></p>

                    <?php if (!empty($candidato['idiomas'])): ?>
                        <p style="margin-top: 8px;"><strong>Idiomas:</strong><br><span style="color: #666;"><?= e($candidato['idiomas']) ?></span></p>
                    <?php endif; ?>
                </div>
            </section>

            <?php if (!empty($candidato['resumo_profissional'])): ?>
                <section class="job-section mb-5">
                    <h2 style="font-size: 1.6rem; color: #212529; font-weight: 700; margin-bottom: 20px;">Resumo Profissional</h2>
                    <div style="color: #555; line-height: 1.7; white-space: pre-wrap; font-size: 1rem;"><?= e($candidato['resumo_profissional']) ?></div>
                </section>
            <?php endif; ?>

            <?php if (!empty($candidato['sobre_mim'])): ?>
                <section class="job-section mb-5">
                    <h2 style="font-size: 1.6rem; color: #212529; font-weight: 700; margin-bottom: 20px;">Sobre Mim</h2>
                    <div style="color: #555; line-height: 1.7; white-space: pre-wrap; font-size: 1rem;"><?= e($candidato['sobre_mim']) ?></div>
                </section>
            <?php endif; ?>

            <?php if (!empty($questionario_respostas)): ?>
                <section class="job-section mb-5">
                    <h2 style="font-size: 1.6rem; color: #212529; font-weight: 700; margin-bottom: 20px;">Questionário da Vaga</h2>
                    <?php foreach ($questionario_respostas as $item): ?>
                        <div style="margin-bottom: 1.5rem; border-left: 4px solid #e9ecef; padding-left: 18px;">
                            <p style="margin:0; font-weight: 600; color: #495057; font-size: 1.05rem;"><?= e($item['texto_pergunta']) ?></p>
                            <p style="margin-top: 5px; color: #666;"><strong>Resposta:</strong> <?= e($item['resposta']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

            <section class="job-section mb-5">
                <h2 style="font-size: 1.6rem; color: #212529; font-weight: 700; margin-bottom: 25px;">Experiência Profissional</h2>
                <?php if (empty($experiencias)): ?>
                    <p class="text-muted">Nenhuma experiência cadastrada.</p>
                <?php else: ?>
                    <?php foreach ($experiencias as $exp): ?>
                        <div style="margin-bottom: 2.5rem; padding-left: 20px; border-left: 3px solid #6c757d;">
                            <h4 style="margin:0; font-size: 1.2em; font-weight: 700; color: #333;">
                                <?= e($exp['cargo']) ?>
                                <?php if ($exp['emprego_atual']): ?>
                                    <strong class="text-success ms-2" style="font-size: 0.85em;">(Atual)</strong>
                                <?php endif; ?>
                            </h4>
                            <p style="margin: 4px 0 8px 0; color:#555; font-weight: 500;"><?= e($exp['empresa']) ?></p>
                            <p style="margin:0 0 12px 0; font-size: 0.95em; color: #777;">
                                <?= !empty($exp['data_inicio']) ? date('m/Y', strtotime($exp['data_inicio'])) : '?' ?> a
                                <?php if ($exp['emprego_atual']): ?>
                                    Presente
                                <?php elseif (!empty($exp['data_fim'])): ?>
                                    <?= date('m/Y', strtotime($exp['data_fim'])) ?>
                                <?php else: ?>
                                    <strong class="text-success">Em andamento</strong>
                                <?php endif; ?>
                            </p>
                            <p style="margin:0; color: #555; line-height: 1.6;"><?= nl2br(e($exp['descricao'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <section class="job-section mb-5">
                <h2 style="font-size: 1.6rem; color: #212529; font-weight: 700; margin-bottom: 25px;">Formação Acadêmica</h2>
                <?php if (empty($formacoes)): ?>
                    <p class="text-muted">Nenhuma formação cadastrada.</p>
                <?php else: ?>
                    <?php foreach ($formacoes as $form): ?>
                        <div style="margin-bottom: 2rem;">
                            <p style="margin:0; font-weight: 700; color: #444; font-size: 1.1rem;">
                                <?= e($form['grau']) ?> - <?= e($form['curso']) ?>
                            </p>
                            <p style="margin: 2px 0; color:#666; font-size: 1rem;"><?= e($form['instituicao']) ?>
                                <?php if (!empty($form['localidade'])): ?>(<?= e($form['localidade']) ?>)<?php endif; ?>
                            </p>
                            <p style="margin:0; font-size:0.95em; color:#888;">
                                <?= e($form['status']) ?>
                                (<?= !empty($form['data_inicio']) ? date('Y', strtotime($form['data_inicio'])) : '?' ?> -
                                <?php if (empty($form['data_fim'])): ?>
                                    <strong class="text-success">Em andamento</strong>
                                <?php else: ?>
                                    <?= date('Y', strtotime($form['data_fim'])) ?>
                                    <?php endif; ?>)
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <?php if (!empty($cursos)): ?>
                <section class="job-section mb-4">
                    <h2 style="font-size: 1.6rem; color: #212529; font-weight: 700; margin-bottom: 20px;">Cursos e Aperfeiçoamento</h2>
                    <?php foreach ($cursos as $curso): ?>
                        <div style="margin-bottom: 1.2rem;">
                            <p style="margin:0; font-weight: 700; color: #333; font-size: 1.05rem;"><?= e($curso['nome_curso']) ?></p>
                            <p style="margin:0; color: #666;"><?= e($curso['instituicao']) ?> - <?= e($curso['ano_conclusao']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

        </main>

        <!-- --- COLUNA DIREITA (Sidebar) --- -->
        <aside class="actions-sidebar" style="width: 300px; flex-shrink: 0;">
            <div class="summary-card" style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #e0e0e0; text-align: center;">

                <h3 style="text-align: center; margin-top: 0; color: #000; font-weight: 700; font-size: 1.3rem; margin-bottom: 20px;">Ações</h3>

                <div class="summary-item" style="margin-bottom: 25px; font-size: 0.95rem;">
                    Status Atual:
                    <strong style="color: #34bc07; font-size: 1.1em; display: inline-block; margin-left: 5px;">
                        <?= isset($candidatura['status']) ? e($candidatura['status']) : 'Visualizada' ?>
                    </strong>
                </div>

                <!-- Flash Messages -->
                <?php if (isset($_SESSION['flash_success'])): ?>
                    <div class="alert alert-success" style="padding: 10px; border-radius: 5px; font-size: 0.85rem; margin-bottom: 15px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb;">
                        <?= $_SESSION['flash_success'] ?>
                    </div>
                    <?php unset($_SESSION['flash_success']); ?>
                <?php endif; ?>

                <!-- Formulário de Mudar Status -->
                <form action="<?= BASE_URL ?>candidatura/status" method="POST" style="margin-bottom: 20px;">
                    <input type="hidden" name="candidatura_id" value="<?= $candidatura['id'] ?? '' ?>">

                    <div class="input-box mb-3" style="text-align: left;">
                        <label for="novo_status" style="display: block; margin-bottom: 8px; color: #555; font-size: 0.9rem;">Alterar Status para:</label>
                        <select name="novo_status" id="novo_status" class="form-control form-select" required style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ced4da; background-color: #f8f9fa;">
                            <?php
                            $status_options = ['Visualizada', 'Em Processo', 'Aprovado', 'Rejeitado'];
                            foreach ($status_options as $status): ?>
                                <option value="<?= $status ?>" <?= (isset($candidatura['status']) && $candidatura['status'] == $status) ? 'selected' : '' ?>>
                                    <?= $status ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Botão Verde Atualizar -->
                    <button type="submit" class="btn w-100 fw-bold py-2" style="background-color: #198754; color: #fff; border: none; border-radius: 6px; font-size: 0.95rem;">
                        <i class='bx bx-refresh me-1'></i> Atualizar Status
                    </button>
                </form>

                <div class="d-grid gap-2">
                    <?php if (!empty($candidatura['chat_id'])): ?>
                        <!-- Botão Marrom Ver Conversa -->
                        <a href="<?= BASE_URL ?>chat/index/<?= $candidatura['chat_id'] ?>" class="btn w-100 fw-bold py-2 text-decoration-none" style="background-color: #581c21; color: #fff; border-radius: 6px; font-size: 0.95rem;">
                            <i class='bx bx-message-square-dots me-1'></i> Ver Conversa
                        </a>
                    <?php else: ?>
                        <form action="<?= BASE_URL ?>chat/create" method="POST" style="display:block; width:100%;">
                            <input type="hidden" name="candidatura_id" value="<?= $candidatura['id'] ?? '' ?>">
                            <button type="submit" class="btn w-100 fw-bold py-2" style="background-color: #581c21; color: #fff; border: none; border-radius: 6px; font-size: 0.95rem;">
                                <i class='bx bx-message-square-dots me-1'></i> Iniciar Conversa
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if ($whatsapp_link): ?>
                        <!-- Botão Verde WhatsApp -->
                        <a href="<?= htmlspecialchars($whatsapp_link); ?>" target="_blank" class="btn w-100 fw-bold py-2 text-decoration-none" style="background-color: #25D366; color: #fff; border-radius: 6px; font-size: 0.95rem;">
                            <i class='bx bxl-whatsapp me-1'></i> Conversar no WhatsApp
                        </a>
                    <?php endif; ?>
                </div>

                <hr style="border-color: #e9ecef; margin: 25px 0;">

                <?php if (!empty($candidato['curriculo_path'])): ?>
                    <div style="margin-bottom: 15px;">
                        <a href="<?= BASE_URL ?>uploads/curriculos/<?= $candidato['curriculo_path'] ?>" target="_blank" class="text-decoration-none text-secondary d-flex align-items-center justify-content-center gap-2" style="font-size: 0.9rem;">
                            <i class='bx bx-file-blank fs-5'></i> Ver Currículo Anexado
                        </a>
                    </div>
                <?php endif; ?>

                <div style="margin-top: 10px;">
                    <a href="<?= BASE_URL ?>vagas/candidatos/<?= $vaga_id ?>" class="text-decoration-none text-muted small">
                        ← Voltar para a lista
                    </a>
                </div>

            </div>
        </aside>
    </div>
</div>

<!-- Estilos Adicionais para garantir layout -->
<style>
    /* Garante que o container siga largura padrão do Bootstrap se disponível, mas com limite customizado */
    .main-content-container {
        width: 100%;
        padding-right: 15px;
        padding-left: 15px;
        margin-right: auto;
        margin-left: auto;
    }

    @media (max-width: 900px) {
        .page-content-wrapper {
            flex-direction: column;
        }

        .actions-sidebar {
            width: 100% !important;
        }
    }

    .btn:hover {
        opacity: 0.92;
        transition: opacity 0.2s;
    }

    /* Ajuste fino em inputs */
    .form-control:focus {
        border-color: #8ed6a6;
        box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
    }
</style>