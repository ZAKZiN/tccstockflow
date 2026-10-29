<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class AuditoriaController extends Controller {
    
    public function index() {
        $db = Database::getConnection();
        
        $stmt = $db->query("SELECT * FROM audit_logs ORDER BY id_audit DESC LIMIT 150");
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('auditoria/index', [
            'logs' => $logs
        ]);
    }
}
