<?php

namespace App\Controllers;

use App\Core\Database;
use PDO;

class CaixaController {
    
    public function index() {
        $db = Database::getConnection();
        
        // Verifica se há caixa aberto para o usuário atual
        $idUsuario = $_SESSION['usuario_id'];
        $stmt = $db->prepare("SELECT * FROM caixas WHERE id_usuario = ? AND status = 'Aberto' ORDER BY id_caixa DESC LIMIT 1");
        $stmt->execute([$idUsuario]);
        $caixaAberto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $movimentacoes = [];
        if ($caixaAberto) {
            $stmtMov = $db->prepare("SELECT * FROM caixa_movimentacoes WHERE id_caixa = ? ORDER BY id_movimentacao DESC");
            $stmtMov->execute([$caixaAberto['id_caixa']]);
            $movimentacoes = $stmtMov->fetchAll(PDO::FETCH_ASSOC);
            
            // Recalcula o saldo final no momento atual
            $saldo = $caixaAberto['saldo_inicial'];
            foreach($movimentacoes as $mov) {
                if ($mov['tipo'] == 'Sangria') $saldo -= $mov['valor'];
                else $saldo += $mov['valor'];
            }
            $caixaAberto['saldo_atual'] = $saldo;
        }
        
        require __DIR__ . '/../Views/caixa/index.php';
    }
    
    public function abrir() {
        $saldoInicial = str_replace(',', '.', $_POST['saldo_inicial'] ?? '0');
        $saldoInicial = floatval($saldoInicial);
        $idUsuario = $_SESSION['usuario_id'];
        
        $db = Database::getConnection();
        
        // Verifica novamente para não abrir 2 caixas
        $stmt = $db->prepare("SELECT id_caixa FROM caixas WHERE id_usuario = ? AND status = 'Aberto'");
        $stmt->execute([$idUsuario]);
        
        header('Content-Type: application/json');
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Você já possui um caixa aberto!']);
            return;
        }
        
        try {
            $db->beginTransaction();
            $stmtInsert = $db->prepare("INSERT INTO caixas (id_usuario, saldo_inicial, status) VALUES (?, ?, 'Aberto')");
            $stmtInsert->execute([$idUsuario, $saldoInicial]);
            $idCaixa = $db->lastInsertId();
            
            $stmtMov = $db->prepare("INSERT INTO caixa_movimentacoes (id_caixa, tipo, valor, descricao) VALUES (?, 'Abertura', ?, 'Fundo de Troco Inicial')");
            $stmtMov->execute([$idCaixa, $saldoInicial]);
            
            $db->commit();
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            $db->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao abrir caixa.']);
        }
    }
    
    public function fechar() {
        $idCaixa = $_POST['id_caixa'] ?? 0;
        
        $db = Database::getConnection();
        
        // Calcula saldo atual
        $stmtMov = $db->prepare("SELECT tipo, valor FROM caixa_movimentacoes WHERE id_caixa = ?");
        $stmtMov->execute([$idCaixa]);
        $movs = $stmtMov->fetchAll(PDO::FETCH_ASSOC);
        
        $saldoFinal = 0;
        foreach($movs as $mov) {
            if ($mov['tipo'] == 'Sangria') $saldoFinal -= $mov['valor'];
            else $saldoFinal += $mov['valor'];
        }
        
        header('Content-Type: application/json');
        
        $stmt = $db->prepare("UPDATE caixas SET status = 'Fechado', data_fechamento = CURRENT_TIMESTAMP, saldo_final = ? WHERE id_caixa = ?");
        if ($stmt->execute([$saldoFinal, $idCaixa])) {
            echo json_encode(['success' => true, 'id_caixa' => $idCaixa]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao fechar caixa.']);
        }
    }
    
    public function relatorio($id) {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /');
            exit;
        }
        
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT c.*, u.nome FROM caixas c JOIN usuarios u ON c.id_usuario = u.id_usuario WHERE c.id_caixa = ?");
        $stmt->execute([$id]);
        $caixa = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$caixa) {
            die("Caixa não encontrado.");
        }
        
        $stmtMov = $db->prepare("SELECT * FROM caixa_movimentacoes WHERE id_caixa = ? ORDER BY id_movimentacao ASC");
        $stmtMov->execute([$id]);
        $movs = $stmtMov->fetchAll(PDO::FETCH_ASSOC);
        
        require __DIR__ . '/../Views/caixa/relatorio.php';
    }
    
    public function lancar() {
        $idCaixa = $_POST['id_caixa'] ?? 0;
        $tipo = $_POST['tipo'] ?? ''; // Sangria ou Suprimento
        $valor = str_replace(',', '.', $_POST['valor'] ?? '0');
        $valor = floatval($valor);
        $descricao = $_POST['descricao'] ?? '';
        
        header('Content-Type: application/json');
        
        if ($valor <= 0 || !in_array($tipo, ['Sangria', 'Suprimento'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
            return;
        }
        
        $db = \App\Core\Database::getConnection();
        
        $stmtMov = $db->prepare("INSERT INTO caixa_movimentacoes (id_caixa, tipo, valor, descricao) VALUES (?, ?, ?, ?)");
        if ($stmtMov->execute([$idCaixa, $tipo, $valor, $descricao])) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao lançar movimentação.']);
        }
    }
}
