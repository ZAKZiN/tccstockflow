<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Produto;

class EstoqueController extends Controller {
    
    public function index() {
        if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_nivel'], ['Almoxarife', 'Administrador'])) {
            $this->redirect('/');
        }
        
        $produtos = Produto::getAll();
        
        $this->view('estoque/index', ['produtos' => $produtos]);
    }
}
