<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class AuditoriaController extends Controller {
    
    public function index() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/');
        }
        
        $db = Database::getConnection();
        
        $stmt = $db->query("SELECT * FROM audit_logs ORDER BY criado_em DESC LIMIT 100");
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('auditoria/index', [
            'logs' => $logs
        ]);
    }
}
