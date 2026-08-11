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
        
        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=estoque_export.csv');
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF"); // BOM for Excel
            fputcsv($output, ['ID', 'Produto', 'Qtd Atual', 'Estoque Minimo', 'Atualizado Em'], ';');
            foreach ($produtos as $p) {
                fputcsv($output, [$p['id_produto'], $p['nome_produto'], $p['quantidade_estoque'], $p['estoque_minimo'], date('d/m/Y H:i', strtotime($p['atualizado_em']))], ';');
            }
            fclose($output);
            exit;
        }
        
        $this->view('estoque/index', ['produtos' => $produtos]);
    }
}
