<?php
require __DIR__ . '/../vendor/autoload.php';

use src\Config\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

echo "Iniciando migracao de indices Full-Text...\n";

// Vagas
echo "Adicionando indice Full-Text na tabela 'vagas'...\n";
try {
    // Check if index exists usually requires querying information_schema, but trying to create and catching duplicate error is simpler for scripts
    $pdo->exec("ALTER TABLE vagas ADD FULLTEXT INDEX idx_fulltext_vagas (titulo, descricao)");
    echo "[OK] Index 'idx_fulltext_vagas' adicionado.\n";
} catch (PDOException $e) {
    // Code 42000 is SQL syntax error or access violation, usually thrown for duplicates too depending on driver
    // SQLSTATE[42000]: Syntax error or access violation: 1061 Duplicate key name 
    if (strpos($e->getMessage(), 'Duplicate key name') !== false || $e->getCode() == '42000') {
        echo "[INFO] Index 'idx_fulltext_vagas' ja existe.\n";
    } else {
        echo "[ERRO] Falha ao adicionar index em vagas: " . $e->getMessage() . "\n";
    }
}

// Empresas
echo "Adicionando indice Full-Text na tabela 'empresas'...\n";
try {
    $pdo->exec("ALTER TABLE empresas ADD FULLTEXT INDEX idx_fulltext_empresa (nome_empresa)");
    echo "[OK] Index 'idx_fulltext_empresa' adicionado.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate key name') !== false || $e->getCode() == '42000') {
        echo "[INFO] Index 'idx_fulltext_empresa' ja existe.\n";
    } else {
        echo "[ERRO] Falha ao adicionar index em empresas: " . $e->getMessage() . "\n";
    }
}
