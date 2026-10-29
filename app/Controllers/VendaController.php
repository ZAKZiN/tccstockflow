<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\AuditHelper;
use App\Helpers\CryptHelper;
use PDO;

class VendaController extends Controller {
    
    public function index() {
        $db = Database::getConnection();
        
        $stmtCat = $db->query("SELECT * FROM categorias ORDER BY nome_categoria");
        $categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

        $stmtCli = $db->query("SELECT * FROM clientes ORDER BY CASE WHEN id_cliente = 1 THEN 0 ELSE 1 END, nome ASC");
        $clientes = $stmtCli->fetchAll(PDO::FETCH_ASSOC);
        foreach ($clientes as &$cli) {
            $cli['telefone'] = CryptHelper::decrypt($cli['telefone'] ?? '');
        }
        unset($cli);

        $stmtProd = $db->query("
            SELECT id_produto, nome_produto, codigo_barras, id_categoria, preco_venda, quantidade_estoque 
            FROM produtos 
            WHERE quantidade_estoque > 0
            ORDER BY nome_produto ASC
        ");
        $produtos = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

        $this->view('pdv/index', [
            'categorias' => $categorias,
            'clientes' => $clientes,
            'produtos_json' => json_encode($produtos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
        ]);
    }
    
    public function finalizar() {
        header('Content-Type: application/json');

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data || !isset($data['carrinho']) || !is_array($data['carrinho']) || count($data['carrinho']) === 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Carrinho vazio.']);
            return;
        }

        $idCliente = (int)($data['id_cliente'] ?? 1);
        $metodoPagamento = trim((string)($data['metodo_pagamento'] ?? 'Dinheiro'));
        $desconto = round(floatval($data['desconto'] ?? 0), 2);
        $valorDinheiroMisto = isset($data['valor_dinheiro']) ? round(floatval($data['valor_dinheiro']), 2) : null;
        $carrinho = $data['carrinho'];

        if ($desconto < 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'O desconto não pode ser negativo.']);
            return;
        }

        if ($metodoPagamento === 'Fiado (Caderninho)' && $idCliente <= 1) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Selecione ou cadastre um cliente nominal para registrar venda no Fiado.']);
            return;
        }

        $db = Database::getConnection();
        
        try {
            $db->beginTransaction();
            
            $idUsuario = (int)$_SESSION['usuario_id'];
            $agora = date('Y-m-d H:i:s');
            
            $stmtCaixa = $db->prepare("SELECT id_caixa FROM caixas WHERE id_usuario = ? AND status = 'Aberto'");
            $stmtCaixa->execute([$idUsuario]);
            $caixa = $stmtCaixa->fetch(PDO::FETCH_ASSOC);
            
            if (!$caixa) {
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => 'Você precisa abrir o caixa (Turno) antes de realizar vendas.']);
                return;
            }
            $idCaixa = (int)$caixa['id_caixa'];

            // Consolida quantidades por produto e busca preço real no servidor (anti-tampering)
            $stmtCheckEstoque = $db->prepare("SELECT id_produto, nome_produto, preco_venda, quantidade_estoque FROM produtos WHERE id_produto = ?");
            $itensValidados = [];
            $valorTotalBruto = 0.0;

            foreach ($carrinho as $item) {
                $idProd = (int)($item['id'] ?? 0);
                $qtd = round(floatval($item['quantidade'] ?? 0), 3);

                if ($idProd <= 0 || $qtd <= 0) {
                    throw new \Exception("Quantidade inválida informada no carrinho.");
                }

                if (!isset($itensValidados[$idProd])) {
                    $stmtCheckEstoque->execute([$idProd]);
                    $prodData = $stmtCheckEstoque->fetch(PDO::FETCH_ASSOC);
                    if (!$prodData) {
                        throw new \Exception("Produto #{$idProd} não encontrado no sistema.");
                    }
                    $itensValidados[$idProd] = [
                        'id' => (int)$prodData['id_produto'],
                        'nome' => $prodData['nome_produto'],
                        'preco' => round((float)$prodData['preco_venda'], 2),
                        'estoque_disponivel' => (float)$prodData['quantidade_estoque'],
                        'quantidade' => 0.0
                    ];
                }

                $itensValidados[$idProd]['quantidade'] = round($itensValidados[$idProd]['quantidade'] + $qtd, 3);
                if ($itensValidados[$idProd]['quantidade'] > $itensValidados[$idProd]['estoque_disponivel']) {
                    $disp = rtrim(rtrim(number_format($itensValidados[$idProd]['estoque_disponivel'], 3, ',', '.'), '0'), ',');
                    throw new \Exception("Estoque insuficiente para '{$itensValidados[$idProd]['nome']}'. (Disponível: {$disp})");
                }
            }

            foreach ($itensValidados as $iv) {
                $valorTotalBruto += ($iv['preco'] * $iv['quantidade']);
            }
            $valorTotalBruto = round($valorTotalBruto, 2);
            $descontoAplicado = min($desconto, $valorTotalBruto);
            $valorTotalLiquido = round(max(0, $valorTotalBruto - $descontoAplicado), 2);

            // Inserir Venda
            $stmtVenda = $db->prepare("INSERT INTO vendas (id_cliente, valor_total, desconto, metodo_pagamento, status, data_venda) VALUES (?, ?, ?, ?, 'Concluída', ?)");
            $stmtVenda->execute([$idCliente, $valorTotalLiquido, $descontoAplicado, $metodoPagamento, $agora]);
            $idVenda = (int)$db->lastInsertId();

            // Processar Itens e Baixar Estoque
            $stmtItem = $db->prepare("INSERT INTO vendas_itens (id_venda, id_produto, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
            $stmtUpdateEstoque = $db->prepare("UPDATE produtos SET quantidade_estoque = ROUND(quantidade_estoque - ?, 3) WHERE id_produto = ?");
            $stmtMovimentacao = $db->prepare("INSERT INTO movimentacoes_estoque (id_produto, id_usuario, tipo, quantidade, observacao, data_movimentacao) VALUES (?, ?, 'Saída', ?, ?, ?)");

            foreach ($itensValidados as $iv) {
                $stmtItem->execute([$idVenda, $iv['id'], $iv['quantidade'], $iv['preco']]);
                $stmtUpdateEstoque->execute([$iv['quantidade'], $iv['id']]);
                
                $obs = "Venda PDV #" . str_pad((string)$idVenda, 4, '0', STR_PAD_LEFT);
                $stmtMovimentacao->execute([$iv['id'], $idUsuario, $iv['quantidade'], $obs, $agora]);
            }

            // Movimentação Financeira
            if ($metodoPagamento === 'Fiado (Caderninho)') {
                $vencimento = date('Y-m-d', strtotime('+30 days'));
                $stmtFiado = $db->prepare("INSERT INTO contas_receber (id_venda, id_cliente, valor_total, status, data_vencimento, criado_em) VALUES (?, ?, ?, 'Pendente', ?, ?)");
                $stmtFiado->execute([$idVenda, $idCliente, $valorTotalLiquido, $vencimento, $agora]);
            } elseif (str_starts_with($metodoPagamento, 'Misto') && $valorDinheiroMisto !== null) {
                $dinheiroLiquido = min($valorTotalLiquido, max(0, $valorDinheiroMisto));
                $eletronicoLiquido = round($valorTotalLiquido - $dinheiroLiquido, 2);
                $stmtMovCaixa = $db->prepare("INSERT INTO caixa_movimentacoes (id_caixa, tipo, valor, metodo_pagamento, descricao, data_movimentacao) VALUES (?, 'Venda', ?, ?, ?, ?)");
                if ($dinheiroLiquido > 0) {
                    $stmtMovCaixa->execute([$idCaixa, $dinheiroLiquido, 'Dinheiro', "Venda #{$idVenda} (Parte em Dinheiro)", $agora]);
                }
                if ($eletronicoLiquido > 0) {
                    $stmtMovCaixa->execute([$idCaixa, $eletronicoLiquido, 'Cartão/Pix', "Venda #{$idVenda} (Parte Eletrônica)", $agora]);
                }
            } else {
                $metodoCaixa = ($metodoPagamento === 'Dinheiro') ? 'Dinheiro' : $metodoPagamento;
                $stmtMovCaixa = $db->prepare("INSERT INTO caixa_movimentacoes (id_caixa, tipo, valor, metodo_pagamento, descricao, data_movimentacao) VALUES (?, 'Venda', ?, ?, ?, ?)");
                $stmtMovCaixa->execute([$idCaixa, $valorTotalLiquido, $metodoCaixa, "Venda #{$idVenda} - {$metodoPagamento}", $agora]);
            }
            
            $db->commit();
            
            echo json_encode(['success' => true, 'id_venda' => $idVenda, 'total' => $valorTotalLiquido]);
        } catch (\Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    public function recibo($id) {
        $db = Database::getConnection();
        
        $stmtVenda = $db->prepare("
            SELECT v.*, c.nome as cliente_nome, c.telefone as cliente_telefone
            FROM vendas v 
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            WHERE v.id_venda = ?
        ");
        $stmtVenda->execute([(int)$id]);
        $venda = $stmtVenda->fetch(PDO::FETCH_ASSOC);
        
        if (!$venda) {
            http_response_code(404);
            die("Venda não encontrada.");
        }
        $venda['cliente_telefone'] = CryptHelper::decrypt($venda['cliente_telefone'] ?? '');
        
        $stmtItens = $db->prepare("
            SELECT vi.*, p.nome_produto, p.codigo_barras 
            FROM vendas_itens vi 
            JOIN produtos p ON vi.id_produto = p.id_produto 
            WHERE vi.id_venda = ?
        ");
        $stmtItens->execute([(int)$id]);
        $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('pdv/recibo', ['venda' => $venda, 'itens' => $itens]);
    }
}
