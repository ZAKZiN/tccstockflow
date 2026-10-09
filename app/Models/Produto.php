<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Produto {
    
    public static function getAll() {
        $db = Database::getConnection();
        $idEmpresa = $_SESSION['empresa_id'];
        $stmt = $db->prepare("SELECT * FROM produtos WHERE id_empresa = ? ORDER BY nome_produto ASC");
        $stmt->execute([$idEmpresa]);
        return $stmt->fetchAll();
    }
    
    public static function getCriticos() {
        $db = Database::getConnection();
        $idEmpresa = $_SESSION['empresa_id'];
        $stmt = $db->prepare("SELECT * FROM produtos WHERE id_empresa = ? AND quantidade_estoque <= COALESCE(estoque_minimo, 0) ORDER BY quantidade_estoque ASC");
        $stmt->execute([$idEmpresa]);
        return $stmt->fetchAll();
    }

    public static function create($dados) {
        $db = Database::getConnection();
        $idEmpresa = $_SESSION['empresa_id'];
        $sql = "INSERT INTO produtos (id_empresa, nome_produto, codigo_barras, sku, id_categoria, preco_custo, preco_venda, quantidade_estoque, estoque_minimo, lote, data_validade) 
                VALUES (:id_empresa, :nome_produto, :codigo_barras, :sku, :id_categoria, :preco_custo, :preco_venda, :quantidade_estoque, :estoque_minimo, :lote, :data_validade)";
        
        $stmt = $db->prepare($sql);
        if ($stmt->execute([
            ':id_empresa' => $idEmpresa,
            ':nome_produto' => $dados['nome_produto'],
            ':codigo_barras' => $dados['codigo_barras'],
            ':sku' => $dados['sku'],
            ':id_categoria' => $dados['id_categoria'],
            ':preco_custo' => $dados['preco_custo'],
            ':preco_venda' => $dados['preco_venda'],
            ':quantidade_estoque' => $dados['quantidade_estoque'],
            ':estoque_minimo' => $dados['estoque_minimo'],
            ':lote' => $dados['lote'],
            ':data_validade' => $dados['data_validade']
        ])) {
            return $db->lastInsertId();
        }
        return false;
    }
}
