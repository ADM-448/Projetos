<?php

namespace src\Core;

class Core
{

    public function run()
    {
        $url = '/';
        if (isset($_GET['url'])) {
            $url .= $_GET['url'];
        }

        $params = array();

        if (!empty($url) && $url != '/') {
            $url = explode('/', $url);
            array_shift($url); // Remove o primeiro slash vazio

            $currentController = ucfirst($url[0]) . 'Controller';
            array_shift($url);

            if (isset($url[0]) && !empty($url[0])) {
                $currentAction = $url[0];
                array_shift($url);
            } else {
                $currentAction = 'index';
            }

            if (count($url) > 0) {
                $params = $url;
            }
        } else {
            $currentController = 'HomeController';
            $currentAction = 'index';
        }

        // Namespace dos Controllers
        $prefix = 'src\\Controllers\\';

        // Verifica se o Controller existe, senão usa o NotFoundController ou HomeController
        if (!file_exists(__DIR__ . '/../Controllers/' . $currentController . '.php') || !method_exists($prefix . $currentController, $currentAction)) {
            $currentController = 'NotFoundController';
            $currentAction = 'index';
            // Se NotFound não existir, pode dar erro fatal, ideal criar um NotFoundController básico.
        }

        $newController = $prefix . $currentController;

        // Verifica se a classe existe através do autoloader antes de instanciar
        if (!class_exists($newController)) {
            // Fallback final
            echo "Erro 404: Página não encontrada (Controller '$currentController' inválido).";
            return;
        }

        $c = new $newController();

        // Chama a action passando os parametros
        call_user_func_array(array($c, $currentAction), $params);
    }
}
