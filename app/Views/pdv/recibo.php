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
        .no-print {
            text-align: center;
            margin-bottom: 15px;
        }
        .no-print button {
            padding: 5px 10px;
            margin: 0 5px;
            cursor: pointer;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 4px;
        }
        .no-print button.secondary {
            background: #4b5563;
        }
        @media print {
            @page { margin: 0; size: 80mm auto; }
            body { margin: 0; padding: 0; width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="if(new URLSearchParams(window.location.search).get('print') === '1') { window.print(); setTimeout(() => window.close(), 500); }">
    
    <div class="no-print">
        <button onclick="window.print()">🖨️ Imprimir</button>
        <button class="secondary" onclick="gerarPDF()">📄 Salvar PDF</button>
        <button style="background-color: #25D366;" onclick="window.open('https://wa.me/?text=<?= urlencode("Olá! Aqui está o comprovante da sua compra (Venda #" . str_pad($venda['id_venda'], 4, '0', STR_PAD_LEFT) . ") no valor de R$ " . number_format($venda['valor_total'], 2, ',', '.') . ". Obrigado pela preferência!") ?>', '_blank')">💬 Enviar no WhatsApp</button>
    </div>

    <div id="recibo-content">
    
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
            <?php 
                $subtotal = 0;
                foreach($itens as $item): 
                $totalItem = $item['quantidade'] * $item['preco_unitario'];
                $subtotal += $totalItem;
            ?>
                <div class="item">
                    <span class="item-name"><?= htmlspecialchars($item['nome_produto']) ?></span>
                    <span class="item-qty"><?= $item['quantidade'] ?></span>
                    <span class="item-price"><?= number_format($totalItem, 2, ',', '.') ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="totals">
            <?php if(isset($venda['desconto']) && $venda['desconto'] > 0): ?>
            <div class="item" style="margin-bottom: 2px;">
                <span class="item-name">Subtotal:</span>
                <span>R$ <?= number_format($subtotal, 2, ',', '.') ?></span>
            </div>
            <div class="item" style="color: #ef4444; margin-bottom: 2px;">
                <span class="item-name">Desconto:</span>
                <span>- R$ <?= number_format($venda['desconto'], 2, ',', '.') ?></span>
            </div>
            <?php endif; ?>
            <div class="total-line">
                <span>TOTAL:</span>
                <span>R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?></span>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Obrigado pela preferência!</p>
        <p>Volte Sempre</p>
    </div>
    
    </div> <!-- Fim recibo-content -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function gerarPDF() {
            const element = document.getElementById('recibo-content');
            const opt = {
                margin:       5,
                filename:     'Recibo_<?= $venda['id_venda'] ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'mm', format: [80, 200], orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>
</html>
