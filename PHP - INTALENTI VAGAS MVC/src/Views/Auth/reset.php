<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - IntalenTI</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>lib/css/styles.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

    <main class="container">
        <form action="<?= BASE_URL ?>auth/updatePassword" method="POST">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <h1>Nova Senha</h1>
            <p class="subtitle">Crie uma nova senha segura para sua conta.</p>

            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="mensagem-erro" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px;">
                    <p><?= $_SESSION['flash_error']; ?></p>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <div class="input-box">
                <label for="password">Nova Senha</label>
                <div style="position: relative;">
                    <input type="password" name="password" id="password" placeholder="Nova senha" required style="padding-right: 40px;">
                    <i class='bx bx-show toggle-password' onclick="togglePasswordVisibility('password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 1.2rem; color: #6c757d;"></i>
                </div>
            </div>

            <div class="input-box">
                <label for="confirm_password">Confirmar Nova Senha</label>
                <div style="position: relative;">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirme a nova senha" required style="padding-right: 40px;">
                    <i class='bx bx-show toggle-password' onclick="togglePasswordVisibility('confirm_password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 1.2rem; color: #6c757d;"></i>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Atualizar Senha</button>
        </form>
    </main>

    <script>
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
    </script>
</body>

</html>