<?php
require __DIR__ . '/../vendor/autoload.php';

use src\Config\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

echo "Iniciando otimizacao de indices de performance (Multi-Tenancy & Filtros)...\n";

// 1. Tabela Candidaturas - Verificação de duplicidade e busca rápida
echo "1. Adicionando index composto em 'candidaturas' (vaga_id, candidato_id)...\n";
try {
    $pdo->exec("ALTER TABLE candidaturas ADD INDEX idx_vaga_candidato (vaga_id, candidato_id)");
    echo "[OK] Index 'idx_vaga_candidato' criado com sucesso.\n";
} catch (PDOException $e) {
    // 42000 is common for duplicates, checking message content too for safety
    if (strpos($e->getMessage(), 'Duplicate key name') !== false || $e->getCode() == '42000') {
        echo "[INFO] Index 'idx_vaga_candidato' ja existe.\n";
    } else {
        echo "[ERRO] Falha ao criar index em candidaturas: " . $e->getMessage() . "\n";
    }
}

// 2. Tabela Vagas - Otimização Multi-Tenant + Localização (Muito usado nos filtros)
echo "2. Adicionando index composto em 'vagas' (portal_id, localizacao)...\n";
try {
    $pdo->exec("ALTER TABLE vagas ADD INDEX idx_portal_local (portal_id, localizacao)");
    echo "[OK] Index 'idx_portal_local' criado com sucesso.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate key name') !== false || $e->getCode() == '42000') {
        echo "[INFO] Index 'idx_portal_local' ja existe.\n";
    } else {
        echo "[ERRO] Falha ao criar index em vagas (idx_portal_local): " . $e->getMessage() . "\n";
    }
}

// 3. Tabela Vagas - Otimização Multi-Tenant + Data (Para mostrar 'Vagas Recentes' rapidamente)
echo "3. Adicionando index composto em 'vagas' (portal_id, created_at)...\n";
try {
    $pdo->exec("ALTER TABLE vagas ADD INDEX idx_portal_created (portal_id, created_at)");
    echo "[OK] Index 'idx_portal_created' criado com sucesso.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate key name') !== false || $e->getCode() == '42000') {
        echo "[INFO] Index 'idx_portal_created' ja existe.\n";
    } else {
        echo "[ERRO] Falha ao criar index em vagas (idx_portal_created): " . $e->getMessage() . "\n";
    }
}

echo "Otimizacao de banco concluida!\n";
