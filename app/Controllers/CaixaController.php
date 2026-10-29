<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\AuditHelper;
use PDO;

class CaixaController extends Controller {
    
    /**
     * Calcula o saldo físico em dinheiro na gaveta e o total eletrônico do caixa
     */
    private function calcularResumoCaixa(PDO $db, int $idCaixa): array {
        $stmtMov = $db->prepare("SELECT * FROM caixa_movimentacoes WHERE id_caixa = ? ORDER BY id_movimentacao DESC");
        $stmtMov->execute([$idCaixa]);
        $movimentacoes = $stmtMov->fetchAll(PDO::FETCH_ASSOC);

        $saldoGaveta = 0.0;
        $totalEletronico = 0.0;

        foreach ($movimentacoes as $mov) {
            $valor = (float)$mov['valor'];
            $tipo = $mov['tipo'];
            $metodo = $mov['metodo_pagamento'] ?? 'Dinheiro';

            if ($tipo === 'Sangria') {
                $saldoGaveta -= $valor;
            } elseif (in_array($tipo, ['Abertura', 'Suprimento', 'Recebimento Fiado'], true)) {
                $saldoGaveta += $valor;
            } elseif ($tipo === 'Venda') {
                if ($metodo === 'Dinheiro') {
                    $saldoGaveta += $valor;
                } else {
                    $totalEletronico += $valor;
                }
            } else {
                $saldoGaveta += $valor;
            }
        }

        return [
            'movimentacoes' => $movimentacoes,
            'saldo_gaveta' => round($saldoGaveta, 2),
            'total_eletronico' => round($totalEletronico, 2),
            'saldo_total' => round($saldoGaveta + $totalEletronico, 2)
        ];
    }

    public function index() {
        $db = Database::getConnection();
        
        $idUsuario = $_SESSION['usuario_id'];
        $stmt = $db->prepare("SELECT * FROM caixas WHERE id_usuario = ? AND status = 'Aberto' ORDER BY id_caixa DESC LIMIT 1");
        $stmt->execute([$idUsuario]);
        $caixaAberto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $movimentacoes = [];
        if ($caixaAberto) {
            $resumo = $this->calcularResumoCaixa($db, (int)$caixaAberto['id_caixa']);
            $movimentacoes = $resumo['movimentacoes'];
            $caixaAberto['saldo_atual'] = $resumo['saldo_gaveta'];
            $caixaAberto['total_eletronico'] = $resumo['total_eletronico'];
            $caixaAberto['saldo_geral'] = $resumo['saldo_total'];
        }
        
        $this->view('caixa/index', [
            'caixaAberto' => $caixaAberto,
            'movimentacoes' => $movimentacoes
        ]);
    }
    
    public function abrir() {
        header('Content-Type: application/json');

        $saldoInicial = str_replace(',', '.', $_POST['saldo_inicial'] ?? '0');
        $saldoInicial = round(floatval($saldoInicial), 2);
        $idUsuario = $_SESSION['usuario_id'];

        if ($saldoInicial < 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'O fundo de troco inicial não pode ser negativo.']);
            return;
        }
        
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT id_caixa FROM caixas WHERE id_usuario = ? AND status = 'Aberto'");
        $stmt->execute([$idUsuario]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Você já possui um caixa aberto!']);
            return;
        }
        
        try {
            $agora = date('Y-m-d H:i:s');
            $db->beginTransaction();
            $stmtInsert = $db->prepare("INSERT INTO caixas (id_usuario, data_abertura, saldo_inicial, status) VALUES (?, ?, ?, 'Aberto')");
            $stmtInsert->execute([$idUsuario, $agora, $saldoInicial]);
            $idCaixa = (int)$db->lastInsertId();
            
            $stmtMov = $db->prepare("INSERT INTO caixa_movimentacoes (id_caixa, tipo, valor, metodo_pagamento, descricao, data_movimentacao) VALUES (?, 'Abertura', ?, 'Dinheiro', 'Fundo de Troco Inicial', ?)");
            $stmtMov->execute([$idCaixa, $saldoInicial, $agora]);
            
            $db->commit();
            AuditHelper::log('caixas', $idCaixa, 'ABRIR_CAIXA', 'Caixa aberto com fundo inicial de R$ ' . number_format($saldoInicial, 2, ',', '.'));
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('Erro ao abrir caixa: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro interno ao abrir caixa.']);
        }
    }
    
    public function fechar() {
        header('Content-Type: application/json');

        $idCaixa = (int)($_POST['id_caixa'] ?? 0);
        $idUsuario = (int)$_SESSION['usuario_id'];
        $nivel = $_SESSION['usuario_nivel'] ?? '';
        
        $db = Database::getConnection();

        // Cibersegurança (Anti-IDOR): garante que o caixa está aberto e pertence ao operador (ou Admin/Gerente)
        if (in_array($nivel, ['Administrador', 'Gerente'], true)) {
            $stmtCheck = $db->prepare("SELECT * FROM caixas WHERE id_caixa = ? AND status = 'Aberto'");
            $stmtCheck->execute([$idCaixa]);
        } else {
            $stmtCheck = $db->prepare("SELECT * FROM caixas WHERE id_caixa = ? AND id_usuario = ? AND status = 'Aberto'");
            $stmtCheck->execute([$idCaixa, $idUsuario]);
        }

        if (!$stmtCheck->fetch(PDO::FETCH_ASSOC)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Caixa não encontrado, já encerrado ou pertencente a outro operador.']);
            return;
        }
        
        $resumo = $this->calcularResumoCaixa($db, $idCaixa);
        $saldoFinal = $resumo['saldo_total'];
        
        $stmt = $db->prepare("UPDATE caixas SET status = 'Fechado', data_fechamento = ?, saldo_final = ? WHERE id_caixa = ?");
        if ($stmt->execute([date('Y-m-d H:i:s'), $saldoFinal, $idCaixa])) {
            AuditHelper::log('caixas', $idCaixa, 'FECHAR_CAIXA', 'Caixa encerrado com saldo total de R$ ' . number_format($saldoFinal, 2, ',', '.') . ' (Gaveta: R$ ' . number_format($resumo['saldo_gaveta'], 2, ',', '.') . ')');
            echo json_encode(['success' => true, 'id_caixa' => $idCaixa]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao fechar caixa.']);
        }
    }
    
    public function relatorio($id) {
        $idCaixa = (int)$id;
        $idUsuario = (int)$_SESSION['usuario_id'];
        $nivel = $_SESSION['usuario_nivel'] ?? '';

        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT c.*, u.nome FROM caixas c JOIN usuarios u ON c.id_usuario = u.id_usuario WHERE c.id_caixa = ?");
        $stmt->execute([$idCaixa]);
        $caixa = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$caixa) {
            http_response_code(404);
            die("Caixa não encontrado.");
        }

        if (!in_array($nivel, ['Administrador', 'Gerente'], true) && (int)$caixa['id_usuario'] !== $idUsuario) {
            http_response_code(403);
            die("Acesso negado a este relatório de caixa.");
        }
        
        $stmtMov = $db->prepare("SELECT * FROM caixa_movimentacoes WHERE id_caixa = ? ORDER BY id_movimentacao ASC");
        $stmtMov->execute([$idCaixa]);
        $movs = $stmtMov->fetchAll(PDO::FETCH_ASSOC);

        $resumo = $this->calcularResumoCaixa($db, $idCaixa);
        
        $this->view('caixa/relatorio', [
            'caixa' => $caixa,
            'movs' => $movs,
            'resumo' => $resumo
        ]);
    }
    
    public function lancar() {
        header('Content-Type: application/json');

        $idCaixa = (int)($_POST['id_caixa'] ?? 0);
        $tipo = $_POST['tipo'] ?? '';
        $valor = round(floatval(str_replace(',', '.', $_POST['valor'] ?? '0')), 2);
        $descricao = trim($_POST['descricao'] ?? '');
        $idUsuario = (int)$_SESSION['usuario_id'];
        $nivel = $_SESSION['usuario_nivel'] ?? '';
        
        if ($valor <= 0 || !in_array($tipo, ['Sangria', 'Suprimento'], true) || $descricao === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Informe um valor positivo e uma descrição válida.']);
            return;
        }
        
        $db = Database::getConnection();

        if (in_array($nivel, ['Administrador', 'Gerente'], true)) {
            $stmtCheck = $db->prepare("SELECT id_caixa FROM caixas WHERE id_caixa = ? AND status = 'Aberto'");
            $stmtCheck->execute([$idCaixa]);
        } else {
            $stmtCheck = $db->prepare("SELECT id_caixa FROM caixas WHERE id_caixa = ? AND id_usuario = ? AND status = 'Aberto'");
            $stmtCheck->execute([$idCaixa, $idUsuario]);
        }

        if (!$stmtCheck->fetch()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Operação não permitida neste caixa.']);
            return;
        }

        if ($tipo === 'Sangria') {
            $resumo = $this->calcularResumoCaixa($db, $idCaixa);
            if ($valor > $resumo['saldo_gaveta']) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Saldo físico insuficiente na gaveta para esta sangria (Disponível: R$ ' . number_format($resumo['saldo_gaveta'], 2, ',', '.') . ').'
                ]);
                return;
            }
        }
        
        $stmtMov = $db->prepare("INSERT INTO caixa_movimentacoes (id_caixa, tipo, valor, metodo_pagamento, descricao, data_movimentacao) VALUES (?, ?, ?, 'Dinheiro', ?, ?)");
        if ($stmtMov->execute([$idCaixa, $tipo, $valor, $descricao, date('Y-m-d H:i:s')])) {
            AuditHelper::log('caixa_movimentacoes', $idCaixa, strtoupper($tipo), "{$tipo} de R$ " . number_format($valor, 2, ',', '.') . " - Motivo: {$descricao}");
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao lançar movimentação.']);
        }
    }
}
