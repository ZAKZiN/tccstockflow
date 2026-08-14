<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Recibo #<?= str_pad($venda['id_venda'], 4, '0', STR_PAD_LEFT) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace;
        }
        body {
            width: 80mm;
            padding: 5mm;
            font-size: 12px;
            color: #000;
            background-color: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }
        .header h1 {
            font-size: 16px;
            text-transform: uppercase;
        }
        .content {
            margin-bottom: 10px;
        }
        .item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .item-name {
            width: 50%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .item-qty {
            width: 15%;
            text-align: center;
        }
        .item-price {
            width: 35%;
            text-align: right;
        }
        .totals {
            border-top: 1px dashed #000;
            padding-top: 5px;
            margin-top: 5px;
        }
        .total-line {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            margin-top: 15px;
            border-top: 1px dashed #000;
            padding-top: 10px;
            font-size: 10px;
        }
        @media print {
            body { margin: 0; padding: 0; }
        }
    </style>
</head>
<body onload="window.print(); setTimeout(() => window.close(), 500);">
    
    <div class="header">
        <h1>STOCKFLOW PDV</h1>
        <p>Documento Não Fiscal</p>
        <p>Data: <?= date('d/m/Y H:i', strtotime($venda['data_venda'])) ?></p>
        <p>Venda #<?= str_pad($venda['id_venda'], 4, '0', STR_PAD_LEFT) ?></p>
    </div>

    <div class="content">
        <p><strong>Cliente:</strong> <?= htmlspecialchars($venda['cliente_nome'] ?? 'Consumidor Final') ?></p>
        <p><strong>Pagamento:</strong> <?= htmlspecialchars($venda['metodo_pagamento']) ?></p>
        
        <div style="margin-top: 10px; border-bottom: 1px dashed #000; padding-bottom: 3px;">
            <div class="item" style="font-weight: bold;">
                <span class="item-name">Descrição</span>
                <span class="item-qty">Qtd</span>
                <span class="item-price">Total</span>
            </div>
        </div>
        
        <div style="margin-top: 5px;">
            <?php foreach($itens as $i): ?>
                <div class="item">
                    <span class="item-name"><?= htmlspecialchars($i['nome_produto']) ?></span>
                    <span class="item-qty">x<?= floatval($i['quantidade']) ?></span>
                    <span class="item-price">R$ <?= number_format($i['quantidade'] * $i['preco_unitario'], 2, ',', '.') ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="totals">
        <div class="total-line">
            <span>TOTAL R$</span>
            <span><?= number_format($venda['valor_total'], 2, ',', '.') ?></span>
        </div>
    </div>

    <div class="footer">
        <p>Obrigado pela preferência!</p>
        <p>Volte Sempre</p>
    </div>
</body>
</html>
