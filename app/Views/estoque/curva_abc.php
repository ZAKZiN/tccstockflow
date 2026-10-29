<?php include __DIR__ . '/../layouts/header.php'; ?>

<header class="topbar animate-fade-up">
    <h2><i class="ph ph-chart-line-up"></i> Análise Curva ABC (Pareto)</h2>
</header>

<div class="glass-panel animate-fade-up delay-100" style="padding: 1.5rem; margin-bottom: 2rem;">
    <p style="color: var(--text-secondary); line-height: 1.6; margin-bottom: 1.25rem;">
        A <strong>Curva ABC</strong> classifica os itens conforme sua representatividade no faturamento acumulado (<strong style="color: var(--text-primary);">R$ <?= number_format((float)$receitaGeral, 2, ',', '.') ?></strong>):<br>
        <span class="badge badge-success">Curva A (até 80%)</span> Produtos vitais de alto giro/receita •
        <span class="badge badge-warning">Curva B (80% a 95%)</span> Importância intermediária •
        <span class="badge badge-danger">Curva C (95% a 100%)</span> Menor impacto financeiro relativo.
    </p>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Classe</th>
                    <th>Produto</th>
                    <th>Qtd Vendida</th>
                    <th>Receita Gerada</th>
                    <th>% da Receita</th>
                    <th>% Acumulado</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($produtos)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2.5rem; color: var(--text-secondary);">Nenhuma venda concluída registrada para calcular a Curva ABC.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($produtos as $p): ?>
                        <?php
                            if ($p['curva'] === 'A') { $badge = 'badge-success'; $barColor = 'var(--success)'; }
                            elseif ($p['curva'] === 'B') { $badge = 'badge-warning'; $barColor = 'var(--warning)'; }
                            else { $badge = 'badge-danger'; $barColor = 'var(--danger)'; }
                        ?>
                        <tr>
                            <td>
                                <span class="badge <?= $badge ?>" style="font-size: 0.9rem; padding: 0.35em 0.8em;">
                                    <?= htmlspecialchars($p['curva']) ?>
                                </span>
                            </td>
                            <td style="font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($p['nome_produto']) ?></td>
                            <td><?= rtrim(rtrim(number_format((float)$p['total_vendido'], 3, ',', '.'), '0'), ',') ?></td>
                            <td style="font-weight: 600; color: var(--text-primary);">R$ <?= number_format((float)$p['receita_total'], 2, ',', '.') ?></td>
                            <td>
                                <div class="d-flex align-items-center" style="min-width: 140px;">
                                    <span class="me-2"><?= number_format((float)$p['percentual'], 2, ',', '.') ?>%</span>
                                    <div class="progress" style="height: 6px; width: 90px; flex-grow: 1; background-color: var(--bg-secondary);">
                                        <div class="progress-bar" role="progressbar" style="width: <?= min(100, (float)$p['percentual']) ?>%; background-color: <?= $barColor ?>;"></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= number_format((float)$p['percentual_acumulado'], 2, ',', '.') ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
