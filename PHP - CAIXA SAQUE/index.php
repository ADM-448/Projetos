<?php
// index.php
session_start();
require 'db.php';

// Busca o estado atual do caixa para exibir na tela
try {
    $stmt = $pdo->query("SELECT nota, quantidade FROM caixa_notas ORDER BY nota DESC");
    $estoque = $stmt->fetchAll();
} catch (Exception $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

// Pega mensagens da sessão (se existirem) e depois as limpa
$message = $_SESSION['message'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['message'], $_SESSION['error']);

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caixa Eletrônico</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container">
        <h1>Simulador de Caixa Eletrônico</h1>

        <form action="sacar.php" method="POST" class="form-saque">
            <label for="valor">Valor do Saque (R$):</label>
            <input type="number" id="valor" name="valor" min="5" step="5" placeholder="Ex: 135" required>
            <button type="submit">Sacar Dinheiro</button>
        </form>
        <hr class="divisor">

        <form action="depositar.php" method="POST" class="form-saque form-deposito">
            <h2>Abastecer Caixa</h2>
            <div class="form-grupo">
                <label for="nota">Nota:</label>
                <select id="nota" name="nota" required>
                    <?php foreach ($estoque as $item): ?>
                        <option value="<?php echo $item['nota']; ?>">
                            R$ <?php echo $item['nota']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-grupo">
                <label for="quantidade">Quantidade:</label>
                <input type="number" id="quantidade" name="quantidade" min="1" placeholder="Ex: 50" required>
            </div>

            <button type="submit">Depositar Notas</button>
        </form>

        <?php if ($message): ?>
            <div class="mensagem sucesso"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mensagem erro"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="estoque">
            <h2>Estoque Atual do Caixa</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nota (R$)</th>
                        <th>Quantidade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($estoque as $item): ?>
                        <tr>
                            <td>R$ <?php echo $item['nota']; ?></td>
                            <td><?php echo $item['quantidade']; ?> unid.</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>