<?php
// sacar.php
session_start();
require 'db.php';


function calcularSaque(int $valorSaque, array &$caixa): array
{
    $saldoTotalCaixa = 0;
    foreach ($caixa as $nota => $quantidade) {
        $saldoTotalCaixa += $nota * $quantidade;
    }

    if ($valorSaque > $saldoTotalCaixa) {
        throw new Exception("Saldo insuficiente no caixa para este valor.");
    }

    $valorRestante = $valorSaque;
    $notasParaEntregar = [];


    $denominacoes = array_keys($caixa);

    foreach ($denominacoes as $nota) {
        if ($valorRestante >= $nota) {
            $notasNecessarias = (int)($valorRestante / $nota);
            $notasDisponiveis = $caixa[$nota];
            $notasADar = min($notasNecessarias, $notasDisponiveis);

            if ($notasADar > 0) {
                $notasParaEntregar[$nota] = $notasADar;
                $valorRestante -= $notasADar * $nota;
                // Atualiza o estoque 'caixa' (que é uma referência)
                $caixa[$nota] -= $notasADar;
            }
        }
    }

    if ($valorRestante > 0) {
        throw new Exception("Não foi possível compor o valor de R$ $valorSaque com as notas disponíveis.");
    }

    return $notasParaEntregar;
}

// --- Início da Execução ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valorSaque = (int)$_POST['valor'];

    if ($valorSaque <= 0) {
        $_SESSION['error'] = "Por favor, insira um valor válido.";
        header('Location: index.php');
        exit;
    }

    try {
        // 1. INICIAR A TRANSAÇÃO
        $pdo->beginTransaction();

        // 2. BUSCAR O ESTOQUE ATUAL DE NOTAS (e travar as linhas para update)
        // "FOR UPDATE" impede que outra transação leia/altere essas linhas
        // até que a nossa termine (evita saques simultâneos darem errado).
        $stmt = $pdo->query("SELECT nota, quantidade FROM caixa_notas WHERE quantidade > 0 ORDER BY nota DESC FOR UPDATE");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Converte o resultado do BD para o formato que a função espera: [nota => quantidade]
        // Ex: [100 => 10, 50 => 20, ...]
        $caixa = array_column($rows, 'quantidade', 'nota');

        // 3. CALCULAR O SAQUE
        // A função 'calcularSaque' vai modificar o array $caixa por referência.
        $notasEntregues = calcularSaque($valorSaque, $caixa);

        // 4. ATUALIZAR O BANCO DE DADOS COM O NOVO ESTOQUE
        $updateStmt = $pdo->prepare("UPDATE caixa_notas SET quantidade = :qtd WHERE nota = :nota");

        foreach ($caixa as $nota => $novaQuantidade) {
            $updateStmt->execute(['qtd' => $novaQuantidade, 'nota' => $nota]);
        }

        // 5. SE TUDO DEU CERTO, CONFIRMAR A TRANSAÇÃO
        $pdo->commit();

        // Formata a mensagem de sucesso
        $msgSucesso = "Saque de R$ $valorSaque APROVADO: ";
        foreach ($notasEntregues as $nota => $qtd) {
            $msgSucesso .= "[$qtd x R$ $nota] ";
        }
        $_SESSION['message'] = $msgSucesso;
    } catch (Exception $e) {
        // 6. SE ALGO DEU ERRADO, REVERTER TUDO
        $pdo->rollBack();
        $_SESSION['error'] = "Saque REJEITADO: " . $e->getMessage();
    }
}

// 7. Redirecionar de volta para a página inicial
header('Location: index.php');
exit;
