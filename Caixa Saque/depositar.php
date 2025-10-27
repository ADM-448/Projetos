<?php
// depositar.php
session_start();
require 'db.php';

// 1. Verifica se o método é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 2. Valida e limpa os dados de entrada
    $nota = (int)($_POST['nota'] ?? 0);
    $quantidade = (int)($_POST['quantidade'] ?? 0);

    // Lista de notas válidas
    $notasValidas = [100, 50, 20, 10, 5];

    if ($quantidade <= 0) {
        $_SESSION['error'] = "A quantidade deve ser maior que zero.";
    } elseif (!in_array($nota, $notasValidas)) {
        $_SESSION['error'] = "Nota inválida selecionada.";
    } else {

        // 3. Tenta executar a atualização no banco
        try {

            $sql = "UPDATE caixa_notas SET quantidade = quantidade + :qtd WHERE nota = :nota";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                'qtd' => $quantidade,
                'nota' => $nota
            ]);

            // Verifica se alguma linha foi realmente atualizada
            if ($stmt->rowCount() > 0) {
                $_SESSION['message'] = "Sucesso! $quantidade nota(s) de R$ $nota foram adicionadas.";
            } else {
                $_SESSION['error'] = "Nota R$ $nota não encontrada no banco (isso não deveria acontecer).";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Erro ao depositar: " . $e->getMessage();
        }
    }
}

// 4. Redireciona de volta para a página inicial
header('Location: index.php');
exit;
