<?php

include_once("config/url.php");
include_once("config/process.php");

//limpa a mensagem
if (isset($_SESSION['msg'])) {
    $printmsg = $_SESSION['msg'];
    $_SESSION['msg'] = '';
}


?>


<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda de Contatos</title>
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.3/css/bootstrap.min.css"
        integrity="sha512-oc9+XSs1H243/FRN9Rw62Fn8EtxjEYWHXRvjS43YtueEewbS6ObfXcJNyohjHqVKFPoXXUxwc+q1K7Dee6vv9g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- CSS -->
    <link rel="stylesheet" href="<?= $BASE_URL ?>css/styles.css">
</head>
<header>
    <!-- cabeçalho/icone img -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <a class="navbar-brand" href="<?= $BASE_URL ?>index.php">
            <img src="<?= $BASE_URL ?>img/logo.svg" alt="Agenda">
        </a>
        <div>
            <div class="navbar-nav">
                <!-- Esse href está criando um link dinâmico para a página inicial do site. -->
                <a class="nav-link active" id="home-link" href="<?= $BASE_URL ?>index.php">Agenda</a>

                <a class="nav-link active" href="<?= $BASE_URL ?>create.php">Adicionar Contato</a>
            </div>
        </div>
    </nav>

</header>