<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\AuditHelper;
use App\Helpers\CryptHelper;
use PDO;

class FiadoController extends Controller {
    
    public function index() {
        $db = Database::getConnection();
        
        $sql = "
            SELECT cr.id_conta, c.nome as cliente, c.telefone, cr.valor_total, cr.status, cr.criado_em, cr.data_vencimento, cr.id_venda 
            FROM contas_receber cr
            JOIN clientes c ON cr.id_cliente = c.id_cliente
            ORDER BY CASE WHEN cr.status = 'Pendente' THEN 0 ELSE 1 END ASC, cr.criado_em DESC
        ";
        $stmt = $db->query($sql);
        $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($contas as &$conta) {
            $conta['telefone'] = CryptHelper::decrypt($conta['telefone'] ?? '');
        }
        unset($conta);

        $this->view('fiado/index', [
            'contas' => $contas
        ]);
    }
    
    public function pagar($id) {
        $idConta = (int)$id;
        $idUsuario = (int)$_SESSION['usuario_id'];
        $db = Database::getConnection();
        
        try {
            $db->beginTransaction();

            $stmtConta = $db->prepare("
                SELECT cr.*, c.nome as cliente_nome 
                FROM contas_receber cr
                JOIN clientes c ON cr.id_cliente = c.id_cliente
                WHERE cr.id_conta = ?
            ");
            $stmtConta->execute([$idConta]);
            $conta = $stmtConta->fetch(PDO::FETCH_ASSOC);

            if (!$conta) {
                $db->rollBack();
                $this->redirect('/fiado?error=' . urlencode('Conta a receber não encontrada.'));
            }

            if ($conta['status'] === 'Pago') {
                $db->rollBack();
                $this->redirect('/fiado?error=' . urlencode('Esta conta já foi baixada anteriormente.'));
            }

            $stmt = $db->prepare("UPDATE contas_receber SET status = 'Pago', valor_pago = valor_total WHERE id_conta = ?");
            $stmt->execute([$idConta]);

            // Se o operador tiver um caixa aberto, registra a entrada em dinheiro do recebimento
            $stmtCaixa = $db->prepare("SELECT id_caixa FROM caixas WHERE id_usuario = ? AND status = 'Aberto' ORDER BY id_caixa DESC LIMIT 1");
            $stmtCaixa->execute([$idUsuario]);
            $caixaAberto = $stmtCaixa->fetch(PDO::FETCH_ASSOC);

            if ($caixaAberto) {
                $stmtMov = $db->prepare("
                    INSERT INTO caixa_movimentacoes (id_caixa, tipo, valor, metodo_pagamento, descricao, data_movimentacao)
                    VALUES (?, 'Recebimento Fiado', ?, 'Dinheiro', ?, ?)
                ");
                $stmtMov->execute([
                    (int)$caixaAberto['id_caixa'],
                    (float)$conta['valor_total'],
                    "Baixa Fiado #{$idConta} - {$conta['cliente_nome']} (Venda #{$conta['id_venda']})",
                    date('Y-m-d H:i:s')
                ]);
            }

            $db->commit();
            AuditHelper::log('contas_receber', $idConta, 'BAIXA_FIADO', "Conta #{$idConta} de '{$conta['cliente_nome']}' quitada no valor de R$ " . number_format((float)$conta['valor_total'], 2, ',', '.'));

            $msgSucesso = $caixaAberto
                ? 'Conta recebida e lançada no caixa atual com sucesso!'
                : 'Conta baixada como paga! (Nenhum turno de caixa aberto no momento).';
            $this->redirect('/fiado?success=' . urlencode($msgSucesso));
        } catch (\Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('Erro ao quitar fiado: ' . $e->getMessage());
            $this->redirect('/fiado?error=' . urlencode('Erro ao processar recebimento da conta.'));
        }
    }
}
