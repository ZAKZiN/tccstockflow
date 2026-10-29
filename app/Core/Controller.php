<?php

namespace App\Core;

class Controller {
    
    /**
     * Renderiza uma view passando dados para ela
     */
    protected function view($view, $data = []) {
        extract($data);
        
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            http_response_code(500);
            die("Erro interno: View não encontrada.");
        }
    }
    
    /**
     * Redireciona para uma rota específica
     */
    protected function redirect($url) {
        header("Location: " . $url);
        exit;
    }

    /**
     * Cibersegurança: Previne CSV Formula Injection (OWASP) ao exportar planilhas para Excel
     */
    protected function sanitizeCsvRow(array $row): array {
        return array_map(function ($cell) {
            if ($cell === null || is_int($cell) || is_float($cell) || is_numeric($cell)) {
                return $cell;
            }
            $str = (string)$cell;
            if ($str !== '' && preg_match('/^[=\+\-@\t\r]/', $str)) {
                return "'" . $str;
            }
            return $str;
        }, $row);
    }
}
