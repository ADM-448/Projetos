<?php

namespace src\Controllers;

use src\Core\Controller;
use src\Models\User;

class HomeController extends Controller
{

    public function index()
    {
        // Redireciona para o login (comportamento padrão desejado)
        header('Location: ' . BASE_URL . 'auth/login');
        exit;
    }
}
