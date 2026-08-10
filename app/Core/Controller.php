<?php

namespace App\Core;

class Controller {
    
    /**
     * Renderiza uma view passando dados para ela
     * 
     * @param string $view Caminho da view a partir da pasta Views (ex: 'auth/login')
     * @param array $data Array associativo com dados a serem passados para a view
     */
    protected function view($view, $data = []) {
        // Extrai as variáveis do array para ficarem acessíveis na view
        extract($data);
        
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View não encontrada: " . $viewFile);
        }
    }
    
    /**
     * Redireciona para uma rota específica
     */
    protected function redirect($url) {
        header("Location: " . $url);
        exit;
    }
}
