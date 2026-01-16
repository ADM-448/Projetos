<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - IntalenTI</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>lib/css/styles.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

    <main class="container">
        <form action="<?= BASE_URL ?>auth/sendResetLink" method="POST">

            <h1>Recuperação de Senha</h1>
            <p class="subtitle">Informe seu e-mail para receber um link de redefinição.</p>

            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="mensagem-erro" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px;">
                    <p><?= $_SESSION['flash_error']; ?></p>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_success'])): ?>
                <div class="mensagem-sucesso" style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px;">
                    <p><?= $_SESSION['flash_success']; ?></p>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <div class="input-box">
                <label for="email">E-mail Cadastrado</label>
                <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
                <i class='bx bx-envelope'></i>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Enviar Link</button>

            <div style="text-align: center; margin-top: 15px;">
                <p><a href="<?= BASE_URL ?>auth/login">Voltar para Login</a></p>
            </div>
        </form>
    </main>
</body>

</html>