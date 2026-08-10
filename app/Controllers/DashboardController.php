<?php

namespace App\Controllers;

use App\Core\Controller;

class DashboardController extends Controller {
    
    public function index() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/');
        }
        
        // Aqui buscaríamos do model os totais para os cards
        $stats = [
            'total_requisicoes' => 0, // Placeholder
            'requisicoes_pendentes' => 0,
            'estoque_critico' => 0
        ];
        
        $this->view('dashboard', ['stats' => $stats]);
    }
}
