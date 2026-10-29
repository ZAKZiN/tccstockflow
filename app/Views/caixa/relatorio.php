<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Fechamento Z - Caixa #<?= (int)$caixa['id_caixa'] ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Courier New', Courier, monospace; }
        body { width: 80mm; padding: 5mm; font-size: 12px; color: #000; background-color: #fff; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 10px; border-bottom: 1px dashed #000; padding-bottom: 6px; }
        .header h1 { font-size: 16px; text-transform: uppercase; }
        .content { margin-bottom: 10px; }
        .item { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .totals { border-top: 1px dashed #000; padding-top: 6px; margin-top: 6px; }
        .total-line { display: flex; justify-content: space-between; font-weight: bold; font-size: 13px; margin-top: 3px; }
        .footer { text-align: center; margin-top: 15px; border-top: 1px dashed #000; padding-top: 10px; font-size: 10px; }
        .no-print { text-align: center; margin-bottom: 12px; }
        .no-print button { padding: 6px 12px; background: #2563eb; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        @media print {
            body { margin: 0; padding: 2mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="window.print();">
    <div class="no-print">
        <button onclick="window.print()">Imprimir Relatório Z</button>
    </div>

    <div class="header">
        <h1>STOCKFLOW PDV</h1>
        <p>RELATÓRIO Z - FECHAMENTO DE TURNO</p>
        <p>Abertura: <?= !empty($caixa['data_abertura']) ? date('d/m/Y H:i', strtotime($caixa['data_abertura'])) : '-' ?></p>
        <p>Fechamento: <?= !empty($caixa['data_fechamento']) ? date('d/m/Y H:i', strtotime($caixa['data_fechamento'])) : 'Em aberto' ?></p>
        <p>Caixa: #<?= (int)$caixa['id_caixa'] ?> - <?= htmlspecialchars($caixa['nome']) ?></p>
    </div>

    <div class="content">
        <h3 style="text-align: center; margin-bottom: 5px;">MOVIMENTAÇÕES</h3>
        <div style="border-bottom: 1px dashed #000; padding-bottom: 4px; margin-bottom: 6px;">
            <?php 
            $totaisPorTipo = [];
            foreach ($movs as $m): 
                $tipo = $m['tipo'];
                if (!isset($totaisPorTipo[$tipo])) $totaisPorTipo[$tipo] = 0;
                $totaisPorTipo[$tipo] += (float)$m['valor'];
            ?>
                <div class="item" style="font-size: 11px;">
                    <span style="width:65%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        [<?= htmlspecialchars($tipo) ?>] <?= htmlspecialchars($m['descricao']) ?>
                    </span>
                    <span style="width:35%; text-align:right;">
                        <?= $tipo === 'Sangria' ? '-' : '+' ?> R$ <?= number_format((float)$m['valor'], 2, ',', '.') ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <h3 style="text-align: center; margin: 8px 0 5px;">RESUMO POR OPERAÇÃO</h3>
        <?php foreach ($totaisPorTipo as $tipo => $valor): ?>
            <div class="item">
                <span>Total <?= htmlspecialchars($tipo) ?>:</span>
                <span><?= $tipo === 'Sangria' ? '- ' : '' ?>R$ <?= number_format($valor, 2, ',', '.') ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="totals">
        <div class="item">
            <span>Dinheiro em Gaveta:</span>
            <span>R$ <?= number_format((float)($resumo['saldo_gaveta'] ?? 0), 2, ',', '.') ?></span>
        </div>
        <div class="item">
            <span>Eletrônico (Pix/Cartão):</span>
            <span>R$ <?= number_format((float)($resumo['total_eletronico'] ?? 0), 2, ',', '.') ?></span>
        </div>
        <div class="total-line">
            <span>SALDO TOTAL DO TURNO:</span>
            <span>R$ <?= number_format((float)($caixa['saldo_final'] ?? ($resumo['saldo_total'] ?? 0)), 2, ',', '.') ?></span>
        </div>
    </div>

    <div class="footer">
        <p>Conferência do Operador</p>
        <p style="margin-top: 22px;">___________________________</p>
        <p><?= htmlspecialchars($caixa['nome']) ?></p>
    </div>
</body>
</html>
