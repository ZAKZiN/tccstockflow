<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Requisicao {
    
    public static function getAll() {
        $db = Database::getConnection();
        // Base query
        $sql = "SELECT r.*, s.nome_setor 
                FROM requisicoes r 
                LEFT JOIN setores s ON r.id_setor = s.id_setor 
                ORDER BY r.data_solicitacao DESC";
        
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }
    
    public static function getBySetor($id_setor) {
        $db = Database::getConnection();
        $sql = "SELECT r.*, s.nome_setor 
                FROM requisicoes r 
                LEFT JOIN setores s ON r.id_setor = s.id_setor 
                WHERE r.id_setor = :id_setor
                ORDER BY r.data_solicitacao DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id_setor', $id_setor);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public static function create($dados) {
        $db = Database::getConnection();
        $sql = "INSERT INTO requisicoes (solicitante, id_setor, material, quantidade, prioridade, justificativa) 
                VALUES (:solicitante, :id_setor, :material, :quantidade, :prioridade, :justificativa)";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':solicitante', $dados['solicitante']);
        $stmt->bindParam(':id_setor', $dados['id_setor']);
        $stmt->bindParam(':material', $dados['material']);
        $stmt->bindParam(':quantidade', $dados['quantidade']);
        $stmt->bindParam(':prioridade', $dados['prioridade']);
        $stmt->bindParam(':justificativa', $dados['justificativa']);
        
        return $stmt->execute();
    }
    
    public static function updateStatus($id_requisicao, $status) {
        $db = Database::getConnection();
        $sql = "UPDATE requisicoes SET status = :status WHERE id_requisicao = :id_requisicao";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id_requisicao', $id_requisicao);
        
        return $stmt->execute();
    }
}
