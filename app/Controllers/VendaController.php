<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Produto;
use PDO;

class VendaController extends Controller {
    
    public function index() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/');
        }
        
        $db = Database::getConnection();
        
        // Buscar todas as categorias
        $stmtCat = $db->query("SELECT * FROM categorias ORDER BY nome_categoria");
        $categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

        // Buscar todos os clientes
        $stmtCli = $db->query("SELECT * FROM clientes ORDER BY nome");
        $clientes = $stmtCli->fetchAll(PDO::FETCH_ASSOC);

        // Buscar todos os produtos para a busca
        $stmtProd = $db->query("SELECT id_produto, nome_produto, codigo_barras, preco_venda, quantidade_estoque FROM produtos WHERE quantidade_estoque > 0");
        $produtos = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

        $this->view('pdv/index', [
            'categorias' => $categorias,
            'clientes' => $clientes,
            'produtos_json' => json_encode($produtos)
        ]);
    }
    
    public function finalizar() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data || !isset($data['carrinho']) || count($data['carrinho']) === 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Carrinho vazio']);
            return;
        }

        $input = $data;

        $idCliente = $input['id_cliente'] ?? 1; // 1 = Cliente Padrão
        $metodoPagamento = $input['metodo_pagamento'] ?? 'Dinheiro';
        $desconto = floatval($input['desconto'] ?? 0);
        $carrinho = $input['carrinho'];

        $db = Database::getConnection();
        
        try {
            $db->beginTransaction();
            
            $idUsuario = $_SESSION['usuario_id'];
            
            // Verificar Caixa Aberto
            $stmtCaixa = $db->prepare("SELECT id_caixa FROM caixas WHERE id_usuario = ? AND status = 'Aberto'");
            $stmtCaixa->execute([$idUsuario]);
            $caixa = $stmtCaixa->fetch(PDO::FETCH_ASSOC);
            
            if (!$caixa) {
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => 'Você precisa abrir o caixa (Turno) antes de realizar vendas.']);
                return;
            }
            $idCaixa = $caixa['id_caixa'];
            
            $valorTotalBruto = 0;
            foreach ($carrinho as $item) {
                $valorTotalBruto += ($item['preco'] * $item['quantidade']);
            }
            $valorTotalLiquido = max(0, $valorTotalBruto - $desconto);

            // Inserir Venda
            $stmtVenda = $db->prepare("INSERT INTO vendas (id_cliente, valor_total, desconto, metodo_pagamento) VALUES (?, ?, ?, ?)");
            $stmtVenda->execute([$idCliente, $valorTotalLiquido, $desconto, $metodoPagamento]);
            $idVenda = $db->lastInsertId();

            // Processar Itens
            $stmtItem = $db->prepare("INSERT INTO vendas_itens (id_venda, id_produto, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
            $stmtUpdateEstoque = $db->prepare("UPDATE produtos SET quantidade_estoque = quantidade_estoque - ? WHERE id_produto = ?");
            $stmtMovimentacao = $db->prepare("INSERT INTO movimentacoes_estoque (id_produto, id_usuario, tipo, quantidade, observacao) VALUES (?, ?, 'Saída', ?, ?)");

            $stmtCheckEstoque = $db->prepare("SELECT nome_produto, quantidade_estoque FROM produtos WHERE id_produto = ?");

            $idUsuario = $_SESSION['usuario_id'];

            foreach ($carrinho as $item) {
                $stmtCheckEstoque->execute([$item['id']]);
                $prodData = $stmtCheckEstoque->fetch(PDO::FETCH_ASSOC);
                if ($prodData && $prodData['quantidade_estoque'] < $item['quantidade']) {
                    throw new \Exception("Estoque insuficiente para vender '{$prodData['nome_produto']}'. (Atual: {$prodData['quantidade_estoque']})");
                }

                $stmtItem->execute([$idVenda, $item['id'], $item['quantidade'], $item['preco']]);
                $stmtUpdateEstoque->execute([$item['quantidade'], $item['id']]);
                
                // Registrar Kardex
                $obs = "Venda PDV #" . str_pad($idVenda, 4, '0', STR_PAD_LEFT);
                $stmtMovimentacao->execute([$item['id'], $idUsuario, $item['quantidade'], $obs]);
            }

            // Caixa: Se não for fiado, entra o dinheiro
            if ($metodoPagamento !== 'Fiado (Caderninho)') {
                $stmtMovCaixa = $db->prepare("INSERT INTO caixa_movimentacoes (id_caixa, tipo, valor, descricao) VALUES (?, 'Venda', ?, ?)");
                $stmtMovCaixa->execute([$idCaixa, $valorTotalLiquido, "Venda #$idVenda - $metodoPagamento"]);
            } else {
                // Registrar Conta a Receber
                $stmtFiado = $db->prepare("INSERT INTO contas_receber (id_venda, id_cliente, valor_total) VALUES (?, ?, ?)");
                $stmtFiado->execute([$idVenda, $idCliente, $valorTotalLiquido]);
            }
            
            $db->commit();
            
            echo json_encode(['success' => true, 'id_venda' => $idVenda, 'total' => $valorTotalLiquido]);
        } catch (\Exception $e) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    public function recibo($id) {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/');
        }
        
        $db = Database::getConnection();
        
        // Na nossa tabela vendas NÃO tem id_usuario, então ajustamos a query
        $stmtVenda = $db->prepare("SELECT v.*, c.nome as cliente_nome 
            FROM vendas v 
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            WHERE v.id_venda = ?");
        $stmtVenda->execute([$id]);
        $venda = $stmtVenda->fetch(PDO::FETCH_ASSOC);
        
        if (!$venda) die("Venda não encontrada");
        
        $stmtItens = $db->prepare("SELECT vi.*, p.nome_produto, p.codigo_barras 
            FROM vendas_itens vi 
            JOIN produtos p ON vi.id_produto = p.id_produto 
            WHERE vi.id_venda = ?");
        $stmtItens->execute([$id]);
        $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('pdv/recibo', ['venda' => $venda, 'itens' => $itens]);
    }
}
