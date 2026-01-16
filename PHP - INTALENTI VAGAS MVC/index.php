<?php
// Redireciona o acesso da raiz do projeto para a pasta public/auth/login

// --- LOCAL (Atual) ---
header('Location: public/auth/login');
exit;
// Redireciona o acesso da raiz do projeto para a pasta public

// --- HOSTGATOR / PRODUÇÃO (Recomendado) ---
// <?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// // --------------------------------

// require_once __DIR__ . '/public/index.php';
