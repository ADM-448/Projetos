<?php

namespace src\Core;

class Controller
{

    /**
     * Renderiza uma view simples (sem template mestre)
     */
    protected function render($viewName, $viewData = [])
    {
        extract($viewData);
        $viewPath = __DIR__ . '/../Views/' . $viewName . '.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo "View '$viewName' não encontrada.";
        }
    }

    /**
     * Renderiza uma view dentro do Layout Master
     */
    protected function renderTemplate($viewName, $viewData = [])
    {
        extract($viewData);

        // Caminho da view filha
        $viewPath = __DIR__ . '/../Views/' . $viewName . '.php';

        // Carrega o layout pai (que vai incluir a filha usando $viewPath)
        require __DIR__ . '/../Views/layouts/master.php';
    }
}
