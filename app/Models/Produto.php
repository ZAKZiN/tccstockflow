<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Produto {
    
    public static function getAll() {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM produtos ORDER BY nome_produto ASC");
        return $stmt->fetchAll();
    }
    
    public static function getCriticos() {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM produtos WHERE quantidade_estoque <= estoque_minimo ORDER BY quantidade_estoque ASC");
        return $stmt->fetchAll();
    }

    public static function create($dados) {
        $db = Database::getConnection();
        $sql = "INSERT INTO produtos (nome_produto, quantidade_estoque, estoque_minimo) 
                VALUES (:nome_produto, :quantidade_estoque, :estoque_minimo)";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':nome_produto', $dados['nome_produto']);
        $stmt->bindParam(':quantidade_estoque', $dados['quantidade_estoque']);
        $stmt->bindParam(':estoque_minimo', $dados['estoque_minimo']);
        
        return $stmt->execute();
    }
}
