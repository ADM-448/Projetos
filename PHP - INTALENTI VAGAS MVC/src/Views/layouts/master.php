<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IntalenTI Vagas - <?= $title ?? 'Home' ?></title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <!-- BoxIcons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- Custom CSS (Migrado) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>lib/css/styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>lib/css/vagas.css">
</head>

<body class="<?= isset($page_theme) ? $page_theme : 'theme-public' ?>">

    <?php require_once __DIR__ . '/../components/header.php'; ?>

    <main class="container-fluid" style="margin-top: 80px; min-height: 80vh;">
        <!-- Injeção de Conteúdo da View -->
        <?php require $viewPath; ?>
    </main>

    <footer class="main-footer" style="padding: 20px; text-align: center; background: #eee; margin-top: 20px;">
        <p>&copy; <?= date('Y') ?> IntalenTI Vagas. Todos os direitos reservados.</p>
    </footer>

    <!-- Scripts Básicos -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>