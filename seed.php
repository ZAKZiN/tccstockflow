<?php

$dbPath = __DIR__ . '/database.sqlite';
$db = new PDO('sqlite:' . $dbPath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $db->beginTransaction();

    // Inserir Produtos
    $produtos = [
        ['Coca-Cola 2L', '7894900011517', 'BEB-001', 1, 6.00, 10.50, 50, 10, 'L123A', date('Y-m-d', strtotime('+6 months'))],
        ['Heineken Long Neck 330ml', '7897893798413', 'BEB-002', 1, 4.50, 7.50, 120, 24, 'L456B', date('Y-m-d', strtotime('+3 months'))],
        ['Arroz Camil 5kg', '7896001201083', 'MER-001', 2, 22.00, 30.90, 40, 15, 'L789C', date('Y-m-d', strtotime('+1 year'))],
        ['Feijão Carioca Kicaldo 1kg', '7891234567890', 'MER-002', 2, 6.50, 9.90, 60, 20, 'L101D', date('Y-m-d', strtotime('+8 months'))],
        ['Sabonete Dove', '7894561234567', 'HIG-001', 3, 2.80, 4.50, 100, 30, 'L202E', date('Y-m-d', strtotime('+2 years'))],
        ['Detergente Ypê 500ml', '7898989898989', 'HIG-002', 3, 1.80, 2.99, 80, 20, 'L303F', date('Y-m-d', strtotime('+1 year'))],
        ['Café Pilão 500g', '7890123456789', 'MER-003', 2, 14.00, 18.50, 5, 10, 'L404G', date('Y-m-d', strtotime('+5 days'))], // Estoque Crítico
    ];

    $stmtProd = $db->prepare("INSERT OR IGNORE INTO produtos (nome_produto, codigo_barras, sku, id_categoria, preco_custo, preco_venda, quantidade_estoque, estoque_minimo, lote, data_validade) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($produtos as $p) {
        $stmtProd->execute($p);
    }

    // Criar Cliente Padrão 2 e 3
    $db->exec("INSERT OR IGNORE INTO clientes (nome, telefone) VALUES ('Maria (Fiado)', '11999999999')");
    $db->exec("INSERT OR IGNORE INTO clientes (nome, telefone) VALUES ('João (Empresa)', '11988888888')");

    // Lançar Vendas (Simular Faturamento de Hoje)
    $stmtVenda = $db->prepare("INSERT INTO vendas (id_cliente, valor_total, metodo_pagamento, data_venda) VALUES (?, ?, ?, ?)");
    $stmtItem = $db->prepare("INSERT INTO vendas_itens (id_venda, id_produto, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
    
    // Venda 1 (Hoje - Dinheiro)
    $stmtVenda->execute([1, 28.50, 'Dinheiro', date('Y-m-d H:i:s')]);
    $idVenda1 = $db->lastInsertId();
    $stmtItem->execute([$idVenda1, 1, 2, 10.50]); // 2 Coca-Colas
    $stmtItem->execute([$idVenda1, 2, 1, 7.50]); // 1 Heineken

    // Venda 2 (Hoje - Cartão)
    $stmtVenda->execute([3, 40.80, 'Cartão de Crédito', date('Y-m-d H:i:s')]);
    $idVenda2 = $db->lastInsertId();
    $stmtItem->execute([$idVenda2, 3, 1, 30.90]); // 1 Arroz
    $stmtItem->execute([$idVenda2, 4, 1, 9.90]); // 1 Feijão

    // Venda 3 (Fiado - Maria)
    $stmtVenda->execute([2, 22.50, 'Fiado (Caderninho)', date('Y-m-d H:i:s')]);
    $idVenda3 = $db->lastInsertId();
    $stmtItem->execute([$idVenda3, 5, 5, 4.50]); // 5 Sabonetes
    
    // Lançar o Fiado em contas_receber
    $db->exec("INSERT INTO contas_receber (id_venda, id_cliente, valor_total, status) VALUES ($idVenda3, 2, 22.50, 'Pendente')");

    // Movimentações Iniciais no Kardex para todos os produtos (Entrada)
    $stmtMov = $db->prepare("INSERT INTO movimentacoes_estoque (id_produto, id_usuario, tipo, quantidade, observacao) VALUES (?, 1, 'Entrada', ?, 'Estoque Inicial')");
    for ($i = 1; $i <= count($produtos); $i++) {
        $stmtMov->execute([$i, $produtos[$i-1][6]]);
    }
    
    // Movimentações de Saída para as Vendas geradas acima
    $stmtMovSaida = $db->prepare("INSERT INTO movimentacoes_estoque (id_produto, id_usuario, tipo, quantidade, observacao) VALUES (?, 1, 'Saída', ?, ?)");
    $stmtMovSaida->execute([1, 2, 'Venda PDV #'.str_pad($idVenda1, 4, '0', STR_PAD_LEFT)]);
    $stmtMovSaida->execute([2, 1, 'Venda PDV #'.str_pad($idVenda1, 4, '0', STR_PAD_LEFT)]);
    $stmtMovSaida->execute([3, 1, 'Venda PDV #'.str_pad($idVenda2, 4, '0', STR_PAD_LEFT)]);
    $stmtMovSaida->execute([4, 1, 'Venda PDV #'.str_pad($idVenda2, 4, '0', STR_PAD_LEFT)]);
    $stmtMovSaida->execute([5, 5, 'Venda PDV #'.str_pad($idVenda3, 4, '0', STR_PAD_LEFT)]);

    $db->commit();
    echo "Seed executado com sucesso! O sistema agora tem produtos reais, gráficos populados e fiados ativos.";
} catch (Exception $e) {
    $db->rollBack();
    echo "Erro: " . $e->getMessage();
}
