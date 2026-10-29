<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Requisicao {
    
    public static function getAll() {
        $db = Database::getConnection();
        $sql = "SELECT r.*, s.nome_setor 
                FROM requisicoes r 
                LEFT JOIN setores s ON r.id_setor = s.id_setor 
                ORDER BY r.data_solicitacao DESC, r.id_requisicao DESC";
        
        $stmt = $db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function getBySetor($id_setor, $solicitanteFallback = null) {
        $db = Database::getConnection();
        if (!empty($id_setor)) {
            $sql = "SELECT r.*, s.nome_setor 
                    FROM requisicoes r 
                    LEFT JOIN setores s ON r.id_setor = s.id_setor 
                    WHERE r.id_setor = :id_setor
                    ORDER BY r.data_solicitacao DESC, r.id_requisicao DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id_setor' => $id_setor]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $sql = "SELECT r.*, s.nome_setor 
                FROM requisicoes r 
                LEFT JOIN setores s ON r.id_setor = s.id_setor 
                WHERE r.solicitante = :solicitante
                ORDER BY r.data_solicitacao DESC, r.id_requisicao DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([':solicitante' => (string)$solicitanteFallback]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find($id_requisicao) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM requisicoes WHERE id_requisicao = ?");
        $stmt->execute([$id_requisicao]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($dados) {
        $db = Database::getConnection();
        $agora = date('Y-m-d H:i:s');
        $sql = "INSERT INTO requisicoes (solicitante, id_setor, material, quantidade, prioridade, justificativa, data_solicitacao, atualizado_em) 
                VALUES (:solicitante, :id_setor, :material, :quantidade, :prioridade, :justificativa, :data_solicitacao, :atualizado_em)";
        
        $stmt = $db->prepare($sql);
        $ok = $stmt->execute([
            ':solicitante' => $dados['solicitante'],
            ':id_setor' => $dados['id_setor'],
            ':material' => $dados['material'],
            ':quantidade' => $dados['quantidade'],
            ':prioridade' => $dados['prioridade'],
            ':justificativa' => $dados['justificativa'],
            ':data_solicitacao' => $agora,
            ':atualizado_em' => $agora
        ]);

        if ($ok) {
            $idReq = (int)$db->lastInsertId();
            self::registrarHistorico($idReq, 'Requisição criada', $dados['solicitante']);
            return $idReq;
        }
        return false;
    }
    
    public static function updateStatus($id_requisicao, $status, $responsavel = 'Sistema') {
        $db = Database::getConnection();
        $sql = "UPDATE requisicoes SET status = :status, atualizado_em = :atualizado_em WHERE id_requisicao = :id_requisicao";
        
        $stmt = $db->prepare($sql);
        $ok = $stmt->execute([
            ':status' => $status,
            ':atualizado_em' => date('Y-m-d H:i:s'),
            ':id_requisicao' => $id_requisicao
        ]);
        if ($ok) {
            self::registrarHistorico((int)$id_requisicao, "Status alterado para: {$status}", $responsavel);
        }
        return $ok;
    }

    public static function registrarHistorico(int $idRequisicao, string $acao, string $responsavel): void {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO historico_requisicoes (id_requisicao, acao, responsavel, data_acao) VALUES (?, ?, ?, ?)");
        $stmt->execute([$idRequisicao, $acao, $responsavel, date('Y-m-d H:i:s')]);
    }
}
