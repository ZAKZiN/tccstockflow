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
        $sql = "INSERT INTO produtos (nome_produto, codigo_barras, sku, id_categoria, preco_custo, preco_venda, quantidade_estoque, estoque_minimo, lote, data_validade) 
                VALUES (:nome_produto, :codigo_barras, :sku, :id_categoria, :preco_custo, :preco_venda, :quantidade_estoque, :estoque_minimo, :lote, :data_validade)";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':nome_produto', $dados['nome_produto']);
        $stmt->bindParam(':codigo_barras', $dados['codigo_barras']);
        $stmt->bindParam(':sku', $dados['sku']);
        $stmt->bindParam(':id_categoria', $dados['id_categoria']);
        $stmt->bindParam(':preco_custo', $dados['preco_custo']);
        $stmt->bindParam(':preco_venda', $dados['preco_venda']);
        $stmt->bindParam(':quantidade_estoque', $dados['quantidade_estoque']);
        $stmt->bindParam(':estoque_minimo', $dados['estoque_minimo']);
        $stmt->bindParam(':lote', $dados['lote']);
        $stmt->bindParam(':data_validade', $dados['data_validade']);
        
        return $stmt->execute();
    }
}
