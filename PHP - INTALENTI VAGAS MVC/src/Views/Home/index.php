<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IntalenTi Vagas - MVC</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            color: #333;
        }

        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        .user-card {
            background: #f9f9f9;
            border-left: 5px solid #3498db;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .badge {
            background: #2c3e50;
            color: #fff;
            padding: 2px 8px;
            font-size: 0.8em;
            border-radius: 12px;
            margin-left: 10px;
        }

        .status-ok {
            color: green;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h1>🚀 IntalenTi Vagas Refatorado</h1>
    <p>Olá, <strong><?= $nome ?></strong>!</p>
    <p>Status do Sistema: <span class="status-ok">✔ MVC Ativo</span> | <span class="status-ok">✔ Banco Conectado</span></p>

    <h3>📋 Usuários Recuperados do Banco (Teste):</h3>

    <?php if (!empty($usuarios)): ?>
        <?php foreach ($usuarios as $user): ?>
            <div class="user-card">
                <strong>#<?= $user['id'] ?></strong>
                <span style="font-size: 1.1em; margin-left: 10px;"><?= htmlspecialchars($user['name']) ?></span>
                <br>
                <small>✉ <?= htmlspecialchars($user['email']) ?></small>
                <span class="badge">Nível <?= $user['tipo_usuario'] ?></span>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="padding: 20px; background: #ffeebb; border-radius: 5px;">
            ⚠ Nenhum usuário encontrado. Verifique se o banco de dados `intale75_multi` foi importado corretamento no phpMyAdmin.
        </div>
    <?php endif; ?>

    <hr>
    <p><small>Ambiente pronto para migrar para Cloudways.</small></p>
</body>

</html>