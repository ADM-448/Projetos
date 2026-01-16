<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>lib/css/styles.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

    <?php
    // Em MVC, normalmente o header é um partial. Se existir, incluímos.
    // Se não, deixamos sem ou criamos um link de volta.
    // Vou assumir que o estilo principal resolve o layout.
    ?>

    <main class="container">
        <form action="<?= BASE_URL ?>auth/loginAction" method="POST">

            <?php if (isset($_SESSION['flash'])): ?>
                <div class="mensagem-erro" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                    <p><?= $_SESSION['flash']; ?></p>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_success'])): ?>
                <div class="mensagem-sucesso" style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                    <p><?= $_SESSION['flash_success']; ?></p>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <h1>Login</h1>

            <!-- No MVC o controlador sabe o que fazer, não precisamos do hidden input type=login necessariamente, 
                 mas se o seu back usar, podemos manter ou remover. O AuthController::loginAction não usa 'type'. -->

            <div class="input-box">
                <label>E-mail</label>
                <input type="email" name="email" placeholder="E-mail" required>
                <!-- Icon via CSS ou HTML? O código original usa <i> depois -->
                <i class='bx bx-user'></i>
            </div>

            <div class="input-box">
                <label>Senha</label>
                <div style="position: relative;">
                    <input type="password" name="password" id="password" placeholder="Senha" required style="padding-right: 40px;">
                    <i class='bx bx-show toggle-password' onclick="togglePasswordVisibility('password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 1.2rem; color: #6c757d;"></i>
                </div>
            </div>

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

            <div style="text-align: right; margin-bottom: 15px;">
                <a href="<?= BASE_URL ?>auth/forgot" style="font-size: 0.9rem; color: var(--primary-color);">Esqueceu sua senha?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Login</button>

            <div class="register-link">
                <p>Ainda não possui uma conta? <a href="<?= BASE_URL ?>auth/register">Cadastre-se</a></p>
            </div>
        </form>
    </main>
</body>

</html>