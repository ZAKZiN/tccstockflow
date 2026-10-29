<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Notificacao {
    
    public static function create($titulo, $mensagem, $id_usuario = null, $nivel_destino = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO notificacoes (titulo, mensagem, id_usuario, nivel_destino, lida, criado_em) VALUES (?, ?, ?, ?, 0, ?)");
        return $stmt->execute([
            mb_substr(trim((string)$titulo), 0, 160),
            trim((string)$mensagem),
            $id_usuario,
            $nivel_destino,
            date('Y-m-d H:i:s')
        ]);
    }
    
    public static function getUnreadForUser($id_usuario, $nivel_acesso) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT * FROM notificacoes 
            WHERE lida = 0 
            AND (id_usuario = ? OR nivel_destino = ?)
            ORDER BY id_notificacao DESC LIMIT 10
        ");
        $stmt->execute([$id_usuario, $nivel_acesso]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function markAsRead($id_notificacao, $id_usuario, $nivel_acesso) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE notificacoes SET lida = 1 
            WHERE id_notificacao = ? AND (id_usuario = ? OR nivel_destino = ?)
        ");
        return $stmt->execute([$id_notificacao, $id_usuario, $nivel_acesso]);
    }
}
