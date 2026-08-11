<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Notificacao {
    
    public static function create($titulo, $mensagem, $id_usuario = null, $nivel_destino = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO notificacoes (titulo, mensagem, id_usuario, nivel_destino) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$titulo, $mensagem, $id_usuario, $nivel_destino]);
    }
    
    public static function getUnreadForUser($id_usuario, $nivel_acesso) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT * FROM notificacoes 
            WHERE lida = FALSE 
            AND (id_usuario = ? OR nivel_destino = ?)
            ORDER BY criado_em DESC LIMIT 10
        ");
        $stmt->execute([$id_usuario, $nivel_acesso]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function markAsRead($id_notificacao, $id_usuario, $nivel_acesso) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE notificacoes SET lida = TRUE 
            WHERE id_notificacao = ? AND (id_usuario = ? OR nivel_destino = ?)
        ");
        return $stmt->execute([$id_notificacao, $id_usuario, $nivel_acesso]);
    }
}
