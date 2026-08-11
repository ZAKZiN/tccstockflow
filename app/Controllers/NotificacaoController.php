<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Notificacao;

class NotificacaoController extends Controller {
    
    public function getUnread() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['usuario_id'])) {
            echo json_encode([]);
            return;
        }
        
        $notificacoes = Notificacao::getUnreadForUser($_SESSION['usuario_id'], $_SESSION['usuario_nivel']);
        echo json_encode($notificacoes);
    }
    
    public function markAsRead($id) {
        if (isset($_SESSION['usuario_id'])) {
            Notificacao::markAsRead($id, $_SESSION['usuario_id'], $_SESSION['usuario_nivel']);
        }
        echo json_encode(['success' => true]);
    }
}
