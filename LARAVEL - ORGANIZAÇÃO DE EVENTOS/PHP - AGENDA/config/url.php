<?php

// Cria a URL base completa do projeto para que os links funcionem em qualquer servidor.
$BASE_URL = "http://" . // Protocolo (https://)
    $_SERVER["SERVER_NAME"] . // Domínio (ex: "localhost")
    dirname($_SERVER['REQUEST_URI'] . '?') . // Pasta do projeto (ex: "/Agenda")
    '/'; // Barra final da URL