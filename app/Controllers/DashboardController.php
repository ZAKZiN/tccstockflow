<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class DashboardController extends Controller {
    
    public function index() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/');
        }
        
        $db = Database::getConnection();
        
        // Cards Stats
        $stmtTotal = $db->query("SELECT COUNT(*) FROM requisicoes");
        $totalReq = $stmtTotal->fetchColumn();
        
        $stmtPendentes = $db->query("SELECT COUNT(*) FROM requisicoes WHERE status LIKE 'Pendente%'");
        $pendentes = $stmtPendentes->fetchColumn();
        
        $stmtEstoque = $db->query("SELECT COUNT(*) FROM produtos WHERE quantidade_estoque <= estoque_minimo");
        $estoqueCritico = $stmtEstoque->fetchColumn();
        
        // Chart 1: Requisitions per Month (Current Year)
        $stmtChart1 = $db->query("
            SELECT EXTRACT(MONTH FROM data_solicitacao) as mes, COUNT(*) as total 
            FROM requisicoes 
            WHERE EXTRACT(YEAR FROM data_solicitacao) = EXTRACT(YEAR FROM CURRENT_DATE) 
            GROUP BY mes ORDER BY mes
        ");
        
        $mesesData = array_fill(1, 12, 0);
        while($row = $stmtChart1->fetch(PDO::FETCH_ASSOC)) {
            $mesesData[(int)$row['mes']] = (int)$row['total'];
        }
        $chartMensal = array_values($mesesData);
        
        // Chart 2: Requisitions per Sector
        $stmtChart2 = $db->query("
            SELECT s.nome_setor, COUNT(r.id_requisicao) as total 
            FROM setores s 
            LEFT JOIN requisicoes r ON s.id_setor = r.id_setor 
            GROUP BY s.nome_setor
        ");
        
        $setoresNomes = [];
        $setoresTotais = [];
        while($row = $stmtChart2->fetch(PDO::FETCH_ASSOC)) {
            $setoresNomes[] = $row['nome_setor'];
            $setoresTotais[] = (int)$row['total'];
        }
        
        $stats = [
            'total_requisicoes' => $totalReq,
            'requisicoes_pendentes' => $pendentes,
            'estoque_critico' => $estoqueCritico,
            'chart_mensal' => json_encode($chartMensal),
            'chart_setores_labels' => json_encode($setoresNomes),
            'chart_setores_data' => json_encode($setoresTotais)
        ];
        
        $this->view('dashboard', ['stats' => $stats]);
    }
}
