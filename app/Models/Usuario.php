<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Usuario {
    
    /**
     * Autentica o usuário pelo login e senha
     */
    public static function authenticate($codigo_acesso, $login, $senha) {
        $db = Database::getConnection();
        
        // 1) Busca a empresa pelo codigo_acesso
        $stmtEmp = $db->prepare("SELECT id_empresa FROM empresas WHERE codigo_acesso = :codigo_acesso AND status_assinatura = 'ativo'");
        $stmtEmp->bindParam(':codigo_acesso', $codigo_acesso);
        $stmtEmp->execute();
        $empresa = $stmtEmp->fetch();
        
        if (!$empresa) {
            return false; // Empresa não existe ou inativa
        }
        
        $stmt = $db->prepare("SELECT u.*, s.nome_setor 
                              FROM usuarios u 
                              LEFT JOIN setores s ON u.id_setor = s.id_setor 
                              WHERE u.login = :login AND u.id_empresa = :id_empresa");
        $stmt->bindParam(':login', $login);
        $stmt->bindParam(':id_empresa', $empresa['id_empresa']);
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
