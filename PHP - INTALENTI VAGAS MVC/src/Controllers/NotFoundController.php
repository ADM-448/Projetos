<?php

namespace src\Controllers;

use src\Core\Controller;

class NotFoundController extends Controller
{

    public function index()
    {
        echo "<h1>404 - Página não encontrada</h1>";
    }
}
