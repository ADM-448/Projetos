<?php

$host =  '127.0.0.1';
$port = 3307; // Especifique a sua porta

$user = 'root';
$pass = '';
$database = 'agenda';

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$database", $user, $pass);
    //mostrar erros
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { //erro na conexão
    $error = $e->getMessage();
    echo "Erro: $error";
}
