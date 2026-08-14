<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Fechamento Z - Caixa #<?= $caixa['id_caixa'] ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Courier New', Courier, monospace; }
        body { width: 80mm; padding: 5mm; font-size: 12px; color: #000; background-color: #fff; }
        .header { text-align: center; margin-bottom: 10px; border-bottom: 1px dashed #000; padding-bottom: 5px; }
        .header h1 { font-size: 16px; text-transform: uppercase; }
        .content { margin-bottom: 10px; }
        .item { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .totals { border-top: 1px dashed #000; padding-top: 5px; margin-top: 5px; }
        .total-line { display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; }
        .footer { text-align: center; margin-top: 15px; border-top: 1px dashed #000; padding-top: 10px; font-size: 10px; }
        @media print { body { margin: 0; padding: 0; } }
    </style>
</head>
<body onload="window.print(); setTimeout(() => window.close(), 500);">
    
    <div class="header">
        <h1>STOCKFLOW PDV</h1>
        <p>RELATÓRIO Z - FECHAMENTO</p>
        <p>Abertura: <?= date('d/m/Y H:i', strtotime($caixa['criado_em'])) ?></p>
        <p>Fechamento: <?= date('d/m/Y H:i', strtotime($caixa['data_fechamento'])) ?></p>
        <p>Caixa: #<?= $caixa['id_caixa'] ?> - <?= htmlspecialchars($caixa['nome']) ?></p>
    </div>

    <div class="content">
        <h3 style="text-align: center; margin-bottom: 5px;">MOVIMENTAÇÕES</h3>
        <div style="border-bottom: 1px dashed #000; padding-bottom: 3px; margin-bottom: 5px;">
            <?php 
            $totaisPorTipo = [];
            foreach($movs as $m): 
                $tipo = $m['tipo'];
                if(!isset($totaisPorTipo[$tipo])) $totaisPorTipo[$tipo] = 0;
                $totaisPorTipo[$tipo] += $m['valor'];
            ?>
                <div class="item" style="font-size: 11px;">
                    <span style="width:65%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        [<?= $tipo ?>] <?= htmlspecialchars($m['descricao']) ?>
                    </span>
                    <span style="width:35%; text-align:right;">
                        <?= $tipo == 'Sangria' ? '-' : '' ?> R$ <?= number_format($m['valor'], 2, ',', '.') ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <h3 style="text-align: center; margin: 10px 0 5px;">RESUMO</h3>
        <?php foreach($totaisPorTipo as $tipo => $valor): ?>
            <div class="item">
                <span>Total <?= $tipo ?>:</span>
                <span>R$ <?= number_format($valor, 2, ',', '.') ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="totals">
        <div class="total-line">
            <span>SALDO FINAL R$</span>
            <span><?= number_format($caixa['saldo_final'], 2, ',', '.') ?></span>
        </div>
    </div>

    <div class="footer">
        <p>Assinatura do Operador</p>
        <p style="margin-top: 20px;">___________________________</p>
        <p><?= htmlspecialchars($caixa['nome']) ?></p>
    </div>
</body>
</html>
