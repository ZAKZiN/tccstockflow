<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class FiadoController extends Controller {
    
    public function index() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/');
        }
        
        $db = Database::getConnection();
        
        $sql = "
            SELECT cr.id_conta, c.nome as cliente, cr.valor_total, cr.status, cr.criado_em, cr.id_venda 
            FROM contas_receber cr
            JOIN clientes c ON cr.id_cliente = c.id_cliente
            ORDER BY cr.status DESC, cr.criado_em DESC
        ";
        $stmt = $db->query($sql);
        $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('fiado/index', [
            'contas' => $contas
        ]);
    }
    
    public function pagar($id) {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/');
        }
        
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE contas_receber SET status = 'Pago', valor_pago = valor_total WHERE id_conta = ?");
        $stmt->execute([$id]);
        
        $this->redirect('/fiado');
    }
}
