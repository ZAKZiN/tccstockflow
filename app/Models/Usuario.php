<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Usuario {
    
    /**
     * Autentica o usuário pelo login e senha
     */
    public static function authenticate($login, $senha) {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT u.*, s.nome_setor 
                              FROM usuarios u 
                              LEFT JOIN setores s ON u.id_setor = s.id_setor 
                              WHERE u.login = :login");
        $stmt->bindParam(':login', $login);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user && password_verify($senha, $user['senha'])) {
            // Remove a senha do array por segurança antes de retornar
            unset($user['senha']);
            return $user;
        }
        
        return false;
    }
}
