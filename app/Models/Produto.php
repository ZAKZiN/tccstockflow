<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Produto {
    
    public static function getAll() {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT p.*, c.nome_categoria 
            FROM produtos p
            LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
            ORDER BY p.nome_produto ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function getCriticos() {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM produtos WHERE quantidade_estoque <= estoque_minimo ORDER BY quantidade_estoque ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($dados) {
        $db = Database::getConnection();
        $sql = "INSERT INTO produtos (nome_produto, codigo_barras, sku, id_categoria, preco_custo, preco_venda, quantidade_estoque, estoque_minimo, lote, data_validade, atualizado_em) 
                VALUES (:nome_produto, :codigo_barras, :sku, :id_categoria, :preco_custo, :preco_venda, :quantidade_estoque, :estoque_minimo, :lote, :data_validade, :atualizado_em)";
        
        $stmt = $db->prepare($sql);
        if ($stmt->execute([
            ':nome_produto' => $dados['nome_produto'],
            ':codigo_barras' => $dados['codigo_barras'],
            ':sku' => $dados['sku'],
            ':id_categoria' => $dados['id_categoria'],
            ':preco_custo' => $dados['preco_custo'],
            ':preco_venda' => $dados['preco_venda'],
            ':quantidade_estoque' => $dados['quantidade_estoque'],
            ':estoque_minimo' => $dados['estoque_minimo'],
            ':lote' => $dados['lote'],
            ':data_validade' => $dados['data_validade'],
            ':atualizado_em' => date('Y-m-d H:i:s')
        ])) {
            return (int)$db->lastInsertId();
        }
        return false;
    }

    public static function update($idProduto, $dados) {
        $db = Database::getConnection();
        $sql = "UPDATE produtos 
                SET nome_produto = :nome_produto,
                    codigo_barras = :codigo_barras,
                    id_categoria = :id_categoria,
                    preco_custo = :preco_custo,
                    preco_venda = :preco_venda,
                    estoque_minimo = :estoque_minimo,
                    lote = :lote,
                    data_validade = :data_validade,
                    atualizado_em = :atualizado_em
                WHERE id_produto = :id_produto";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':nome_produto' => $dados['nome_produto'],
            ':codigo_barras' => $dados['codigo_barras'],
            ':id_categoria' => $dados['id_categoria'],
            ':preco_custo' => $dados['preco_custo'],
            ':preco_venda' => $dados['preco_venda'],
            ':estoque_minimo' => $dados['estoque_minimo'],
            ':lote' => $dados['lote'],
            ':data_validade' => $dados['data_validade'],
            ':atualizado_em' => date('Y-m-d H:i:s'),
            ':id_produto' => $idProduto
        ]);
    }
}
