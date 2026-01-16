<?php
session_start();

// O Composer gera o autoload na pasta vendor na RAIZ do projeto (um nível acima de public)
require __DIR__ . '/../vendor/autoload.php';

// Carrega variáveis de ambiente do arquivo .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

use src\Core\Core;
use src\Config\Database;

// --- MULTI-TENANCY: DETECCÃO DE PORTAL ---
$host = $_SERVER['HTTP_HOST'];
// Remover porta se houver (ex: localhost:8080)
$host = explode(':', $host)[0];

$portalId = null;

try {
    $db = Database::getInstance();
    $portal = $db->selectOne("SELECT id, nome_empresa FROM empresas WHERE tipo = 'prefeitura' AND dominio = :dom", ['dom' => $host]);

    if ($portal) {
        $portalId = $portal['id'];
    }
} catch (Exception $e) {
    // Erro de conexão, não há mto o que fazer
}

// Lógica de Fallback / Segurança
if (!$portalId) {
    // Ambientes Locais: Permite fallback para desenvolvimento
    if ($host === 'localhost' || $host === '127.0.0.1') {
        $portalId = 48; // ID padrão para DEV (Prefeitura Exemplo)
    } else {
        // PRODUÇÃO: Se não achou domínio, bloqueia.
        // Isso impede vazamento de dados de um portal padrão em URL desconhecida.
        http_response_code(404);
        echo "<h1>404 - Portal não encontrado</h1>";
        echo "<p>O domínio <strong>" . htmlspecialchars($host) . "</strong> não está configurado em nossa base.</p>";
        exit;
    }
}

define('PORTAL_ATUAL', $portalId);
// -----------------------------------------

// Definição da URL Base (Dinâmica para suportar múltiplos domínios)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";

// Detecção robusta para servidor embutido (php -S) ou porta customizada
if (php_sapi_name() === 'cli-server' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '8000')) {
    // Garante que usa a raiz atual como base
    define('BASE_URL', "http://" . $_SERVER['HTTP_HOST'] . "/");
} elseif ($host === 'localhost' || $host === '127.0.0.1') {
    // Se for localhost e pasta (XAMPP padrão na porta 80), mantém hardcoded
    // IMPORTANTE: Se você mudou a pasta, atualize aqui ou configure um VirtualHost
    define('BASE_URL', 'http://localhost/Projetos%20PHP/IntalenTi%20Vagas%20Antigravity/IntalenTIVagasMVC/public/');
} else {
    // Para produção/hosts reais (ex: meusite.com)
    define('BASE_URL', $protocol . "://" . $_SERVER['HTTP_HOST'] . "/");

    // --- HOSTGATOR / PRODUÇÃO ---
    // Caso a URL fique incorreta (ex: faltando /vagas/ ou /public/), force a URL aqui:
    // define('BASE_URL', 'https://intalenti.com.br/vagas/public/'); 
    // Ou se usar a reescrita do root e quiser ocultar o public:
    // define('BASE_URL', 'https://intalenti.com.br/vagas/');
}

$core = new Core();
$core->run();
