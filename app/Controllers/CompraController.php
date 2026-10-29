<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\AuditHelper;
use App\Models\Requisicao;
use PDO;

class CompraController extends Controller {
    
    public function index() {
        $db = Database::getConnection();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_requisicao = (int)($_POST['id_requisicao'] ?? 0);
            $id_fornecedor = (int)($_POST['id_fornecedor'] ?? 0);
            $valor_total = round(floatval(str_replace(',', '.', $_POST['valor_total'] ?? '0')), 2);
            
            if ($id_requisicao <= 0 || $id_fornecedor <= 0 || $valor_total <= 0) {
                $this->redirect('/compras?error=' . urlencode('Selecione uma requisição aprovada, o fornecedor e um valor válido.'));
            }

            try {
                $agora = date('Y-m-d H:i:s');
                $db->beginTransaction();

                // Garante que apenas requisições aprovadas pelo Coordenador possam gerar Ordem de Compra
                $stmtCheckReq = $db->prepare("SELECT * FROM requisicoes WHERE id_requisicao = ? AND status = 'Pendente Almoxarifado'");
                $stmtCheckReq->execute([$id_requisicao]);
                $req = $stmtCheckReq->fetch(PDO::FETCH_ASSOC);

                if (!$req) {
                    throw new \Exception('A requisição selecionada precisa estar aprovada (Pendente Almoxarifado) e ainda não comprada.');
                }

                $stmt = $db->prepare("INSERT INTO compras (id_requisicao, id_fornecedor, valor_total, data_compra) VALUES (?, ?, ?, ?)");
                $stmt->execute([$id_requisicao, $id_fornecedor, $valor_total, $agora]);
                $idCompra = (int)$db->lastInsertId();
                
                $stmtReq = $db->prepare("UPDATE requisicoes SET status = 'Compra Efetuada', atualizado_em = ? WHERE id_requisicao = ?");
                $stmtReq->execute([$agora, $id_requisicao]);
                Requisicao::registrarHistorico($id_requisicao, "Ordem de Compra #{$idCompra} registrada (R$ " . number_format($valor_total, 2, ',', '.') . ")", $_SESSION['usuario_nome']);

                // Integração Estoque/Kardex: se o material corresponder a um produto cadastrado, dá entrada automática no saldo!
                $stmtProd = $db->prepare("SELECT id_produto, quantidade_estoque FROM produtos WHERE nome_produto = ? COLLATE NOCASE");
                $stmtProd->execute([trim($req['material'])]);
                $prod = $stmtProd->fetch(PDO::FETCH_ASSOC);

                if ($prod) {
                    $novaQtd = round((float)$prod['quantidade_estoque'] + (float)$req['quantidade'], 3);
                    $custoUnitario = (float)$req['quantidade'] > 0 ? round($valor_total / (float)$req['quantidade'], 2) : 0;

                    $stmtUpProd = $db->prepare("UPDATE produtos SET quantidade_estoque = ?, preco_custo = CASE WHEN ? > 0 THEN ? ELSE preco_custo END, atualizado_em = ? WHERE id_produto = ?");
                    $stmtUpProd->execute([$novaQtd, $custoUnitario, $custoUnitario, $agora, $prod['id_produto']]);

                    $stmtMov = $db->prepare("INSERT INTO movimentacoes_estoque (id_produto, id_usuario, tipo, quantidade, observacao, data_movimentacao) VALUES (?, ?, 'Entrada', ?, ?, ?)");
                    $stmtMov->execute([
                        $prod['id_produto'],
                        $_SESSION['usuario_id'],
                        $req['quantidade'],
                        "Entrada automática via Ordem de Compra #{$idCompra} (Req #{$id_requisicao})",
                        $agora
                    ]);
                }

                $db->commit();
                AuditHelper::log('compras', $idCompra, 'REGISTRAR_COMPRA', "OC #{$idCompra} vinculada à Req #{$id_requisicao} no valor de R$ " . number_format($valor_total, 2, ',', '.'));
                $this->redirect('/compras?success=' . urlencode('Ordem de compra registrada com sucesso!'));
            } catch (\Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                $this->redirect('/compras?error=' . urlencode($e->getMessage()));
            }
        }
        
        $stmt = $db->query("
            SELECT c.id_compra, c.valor_total, c.data_compra, r.material, r.quantidade, f.nome_fantasia 
            FROM compras c 
            JOIN requisicoes r ON c.id_requisicao = r.id_requisicao 
            LEFT JOIN fornecedores f ON c.id_fornecedor = f.id_fornecedor
            ORDER BY c.id_compra DESC
        ");
        $compras = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Lista apenas requisições já aprovadas pela Coordenação
        $stmtReq = $db->query("SELECT id_requisicao, material, quantidade, status FROM requisicoes WHERE status = 'Pendente Almoxarifado' ORDER BY id_requisicao DESC");
        $requisicoes = $stmtReq->fetchAll(PDO::FETCH_ASSOC);
        
        $stmtForn = $db->query("SELECT id_fornecedor, nome_fantasia FROM fornecedores ORDER BY nome_fantasia ASC");
        $fornecedores = $stmtForn->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('compras/index', [
            'compras' => $compras,
            'requisicoes' => $requisicoes,
            'fornecedores' => $fornecedores
        ]);
    }
}
