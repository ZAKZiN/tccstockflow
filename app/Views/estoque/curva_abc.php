<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom animate-fade-up">
    <h1 class="h2">Análise Curva ABC</h1>
</div>

<div class="glass-panel animate-fade-up delay-100" style="padding: 1.5rem; margin-bottom: 2rem;">
    <p class="text-muted">
        A <strong>Curva ABC</strong> classifica seus produtos com base no impacto real no faturamento total (R$ <?= number_format($receitaGeral, 2, ',', '.') ?>).<br>
        <span class="badge" style="background-color: var(--success); color: white;">Curva A (80%)</span> Itens mais importantes. Não deixe faltar no estoque.<br>
        <span class="badge" style="background-color: var(--warning); color: black;">Curva B (15%)</span> Itens de média importância.<br>
        <span class="badge" style="background-color: var(--danger); color: white;">Curva C (5%)</span> Itens que geram pouco faturamento relativo.
    </p>

    <div class="table-responsive mt-4">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Classificação</th>
                    <th>Produto</th>
                    <th>Qtd Vendida</th>
                    <th>Receita Total</th>
                    <th>% da Receita</th>
                    <th>% Acumulado</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($produtos)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4">Nenhuma venda registrada para calcular a curva ABC.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($produtos as $p): ?>
                        <?php
                            $bg = '';
                            $color = '';
                            if ($p['curva'] === 'A') { $bg = 'var(--success-bg)'; $color = 'var(--success)'; }
                            elseif ($p['curva'] === 'B') { $bg = 'var(--warning-bg)'; $color = 'var(--warning)'; }
                            else { $bg = 'var(--danger-bg)'; $color = 'var(--danger)'; }
                        ?>
                        <tr>
                            <td>
                                <span class="badge" style="background-color: <?= $bg ?>; color: <?= $color ?>; font-size: 1rem; padding: 0.4em 0.8em;">
                                    <?= $p['curva'] ?>
                                </span>
                            </td>
                            <td class="fw-bold" style="color: var(--text-primary);"><?= htmlspecialchars($p['nome_produto']) ?></td>
                            <td><?= $p['total_vendido'] ?></td>
                            <td class="fw-bold" style="color: var(--text-primary);">R$ <?= number_format($p['receita_total'], 2, ',', '.') ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="me-2"><?= number_format($p['percentual'], 2, ',', '.') ?>%</span>
                                    <div class="progress" style="height: 6px; width: 100px; flex-grow: 1;">
                                        <div class="progress-bar" role="progressbar" style="width: <?= $p['percentual'] ?>%; background-color: <?= $color ?>;"></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= number_format($p['percentual_acumulado'], 2, ',', '.') ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
