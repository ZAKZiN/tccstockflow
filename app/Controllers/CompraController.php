<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class CompraController extends Controller {
    
    public function index() {
        if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_nivel'], ['Almoxarife', 'Administrador'])) {
            $this->redirect('/dashboard');
        }
        
        $db = Database::getConnection();
        
        // POST to create a new Order
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_requisicao = $_POST['id_requisicao'] ?? null;
            $id_fornecedor = $_POST['id_fornecedor'] ?? null;
            $valor_total = $_POST['valor_total'] ?? 0;
            
            if ($id_requisicao && $id_fornecedor) {
                // Insert Compra
                $stmt = $db->prepare("INSERT INTO compras (id_requisicao, id_fornecedor, valor_total) VALUES (?, ?, ?)");
                $stmt->execute([$id_requisicao, $id_fornecedor, $valor_total]);
                
                // Update Requisicao Status to 'Compra Efetuada'
                $stmtReq = $db->prepare("UPDATE requisicoes SET status = 'Compra Efetuada' WHERE id_requisicao = ?");
                $stmtReq->execute([$id_requisicao]);
            }
            
            $this->redirect('/compras');
        }
        
        // Fetch Compras
        $stmt = $db->query("
            SELECT c.id_compra, c.valor_total, c.data_compra, r.material, r.quantidade, f.nome_fantasia 
            FROM compras c 
            JOIN requisicoes r ON c.id_requisicao = r.id_requisicao 
            LEFT JOIN fornecedores f ON c.id_fornecedor = f.id_fornecedor
            ORDER BY c.data_compra DESC
        ");
        $compras = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // For the Modal
        $stmtReq = $db->query("SELECT id_requisicao, material FROM requisicoes WHERE status LIKE 'Pendente%' OR status = 'Aprovado'");
        $requisicoes = $stmtReq->fetchAll(PDO::FETCH_ASSOC);
        
        $stmtForn = $db->query("SELECT id_fornecedor, nome_fantasia FROM fornecedores");
        $fornecedores = $stmtForn->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('compras/index', [
            'compras' => $compras,
            'requisicoes' => $requisicoes,
            'fornecedores' => $fornecedores
        ]);
    }
}
