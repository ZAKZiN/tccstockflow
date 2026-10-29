<?php include __DIR__ . '/layouts/header.php'; ?>

            <header class="topbar animate-fade-up">
                <h2>Visão Geral do Negócio</h2>
                <div class="user-profile">
                    <div class="user-info">
                        <strong><?= htmlspecialchars($_SESSION['usuario_nome']) ?></strong>
                        <span><?= htmlspecialchars($_SESSION['usuario_nivel']) ?><?= !empty($_SESSION['usuario_setor']) ? ' • ' . htmlspecialchars($_SESSION['usuario_setor']) : '' ?></span>
                    </div>
                    <div class="avatar">
                        <?= htmlspecialchars(mb_strtoupper(mb_substr($_SESSION['usuario_nome'], 0, 1, 'UTF-8'), 'UTF-8')) ?>
                    </div>
                </div>
            </header>

            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-error animate-fade-up">
                    <i class="ph ph-warning-circle"></i> <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <?php if(!empty($stats['vencendo'])): ?>
                <div class="alert alert-error animate-fade-up" style="display: flex; flex-direction: column; align-items: flex-start; gap: 0.5rem; margin-bottom: 1.5rem; border-left: 4px solid var(--danger);">
                    <div style="font-weight: 600; font-size: 1.05rem; color: var(--danger);"><i class="ph ph-warning"></i> Atenção: Prevenção de Perdas!</div>
                    <div style="color: var(--text-primary);">Você possui <?= count($stats['vencendo']) ?> produto(s) vencido(s) ou vencendo em até 7 dias.</div>
                    <ul style="margin-left: 1.5rem; margin-top: 0.25rem; margin-bottom: 0; list-style-type: disc; color: var(--text-secondary);">
                        <?php foreach($stats['vencendo'] as $vp): ?>
                            <li>
                                <strong style="color: var(--text-primary);"><?= htmlspecialchars($vp['nome_produto']) ?></strong> — 
                                Validade: <?= date('d/m/Y', strtotime($vp['data_validade'])) ?> 
                                (<?= rtrim(rtrim(number_format((float)$vp['quantidade_estoque'], 3, ',', '.'), '0'), ',') ?> em estoque)
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if(!empty($stats['estoque_critico_list'])): ?>
                <div class="alert alert-warning animate-fade-up" style="display: flex; flex-direction: column; align-items: flex-start; gap: 0.5rem; margin-bottom: 1.5rem; border-left: 4px solid var(--warning);">
                    <div style="font-weight: 600; font-size: 1.05rem; color: var(--warning);"><i class="ph ph-warning-circle"></i> Atenção: Estoque Crítico!</div>
                    <div style="color: var(--text-primary);">Você possui <?= count($stats['estoque_critico_list']) ?> produto(s) no estoque mínimo ou abaixo.</div>
                    <ul style="margin-left: 1.5rem; margin-top: 0.25rem; margin-bottom: 0; list-style-type: disc; color: var(--text-secondary);">
                        <?php foreach($stats['estoque_critico_list'] as $ec): ?>
                            <li>
                                <strong style="color: var(--text-primary);"><?= htmlspecialchars($ec['nome_produto']) ?></strong> — 
                                Atual: <strong style="color: var(--danger);"><?= rtrim(rtrim(number_format((float)$ec['quantidade_estoque'], 3, ',', '.'), '0'), ',') ?></strong> 
                                (Mínimo: <?= rtrim(rtrim(number_format((float)$ec['estoque_minimo'], 3, ',', '.'), '0'), ',') ?>)
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card glass-panel animate-fade-up delay-100">
                    <div class="stat-icon icon-blue">
                        <i class="ph ph-currency-circle-dollar"></i>
                    </div>
                    <div class="stat-info">
                        <h3>R$ <?= number_format($stats['faturamento_hoje'], 2, ',', '.') ?></h3>
                        <p>Faturamento de Hoje</p>
                    </div>
                </div>
                
                <?php 
                    $margem = $stats['faturamento_hoje'] > 0
                        ? ($stats['lucro_hoje'] / $stats['faturamento_hoje']) * 100
                        : 0;
                    $corLucro = $stats['lucro_hoje'] >= 0 ? 'var(--success)' : 'var(--danger)';
                ?>
                <div class="stat-card glass-panel animate-fade-up delay-150" style="border-left: 4px solid <?= $corLucro ?>;">
                    <div class="stat-icon" style="color: <?= $corLucro ?>; background: var(--success-bg);">
                        <i class="ph ph-trend-up"></i>
                    </div>
                    <div class="stat-info">
                        <h3>R$ <?= number_format($stats['lucro_hoje'], 2, ',', '.') ?></h3>
                        <p>Lucro Líquido Hoje <span style="font-size: 0.8rem; color: <?= $corLucro ?>;">(<?= number_format($margem, 1, ',', '.') ?>%)</span></p>
                    </div>
                </div>
                
                <div class="stat-card glass-panel animate-fade-up delay-200">
                    <div class="stat-icon icon-yellow">
                        <i class="ph ph-receipt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= (int)$stats['vendas_hoje'] ?></h3>
                        <p>Vendas Realizadas Hoje</p>
                    </div>
                </div>
                
                <div class="stat-card glass-panel animate-fade-up delay-300">
                    <div class="stat-icon icon-red">
                        <i class="ph ph-warning-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= (int)$stats['estoque_critico'] ?></h3>
                        <p>Itens em Estoque Crítico</p>
                    </div>
                </div>
            </div>
            
            <!-- Analytics Charts -->
            <div class="charts-grid animate-fade-up delay-400">
                <div class="chart-container">
                    <div class="chart-header"><i class="ph ph-chart-line"></i> Evolução do Faturamento Mensal (R$)</div>
                    <canvas id="lineChart" height="110"></canvas>
                </div>
                <div class="chart-container">
                    <div class="chart-header"><i class="ph ph-chart-donut"></i> Top 5 Produtos Vendidos</div>
                    <canvas id="doughnutChart" height="200"></canvas>
                </div>
            </div>

            <!-- Tabela de Produtos com Mais Saídas -->
            <div class="glass-panel animate-fade-up delay-500" style="padding: 1.5rem; margin-bottom: 2rem;">
                <div class="chart-header" style="margin-bottom: 1.25rem;"><i class="ph ph-trophy"></i> Ranking: Produtos com Mais Saídas (Top 10)</div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Cód.</th>
                                <th>Produto</th>
                                <th style="text-align: center;">Qtd Vendida</th>
                                <th style="text-align: right;">Valor Gerado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($stats['top_produtos_list'])): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-secondary);">Nenhuma venda registrada ainda.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($stats['top_produtos_list'] as $tp): ?>
                                    <tr>
                                        <td style="color: var(--text-secondary);">#<?= str_pad((string)$tp['id_produto'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td style="font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars($tp['nome_produto']) ?></td>
                                        <td style="text-align: center; font-weight: 600; font-size: 1.05rem;"><?= rtrim(rtrim(number_format((float)$tp['qtd_vendida'], 3, ',', '.'), '0'), ',') ?></td>
                                        <td style="text-align: right; color: var(--success); font-weight: 600;">R$ <?= number_format((float)$tp['valor_gerado'], 2, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const isDark = document.body.classList.contains('dark-theme');
        const gridColor = isDark ? '#334155' : '#e5e7eb';
        const labelColor = isDark ? '#94a3b8' : '#4b5563';

        const ctxLine = document.getElementById('lineChart').getContext('2d');
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                datasets: [{
                    label: 'Faturamento (R$)',
                    data: <?= $stats['chart_mensal'] ?>,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.12)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: labelColor } },
                    x: { grid: { display: false }, ticks: { color: labelColor } }
                }
            }
        });

        const ctxDoughnut = document.getElementById('doughnutChart').getContext('2d');
        new Chart(ctxDoughnut, {
            type: 'doughnut',
            data: {
                labels: <?= $stats['chart_top_labels'] ?>,
                datasets: [{
                    data: <?= $stats['chart_top_data'] ?>,
                    backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                cutout: '68%',
                plugins: {
                    legend: { position: 'bottom', labels: { color: labelColor } }
                }
            }
        });
    </script>

<?php include __DIR__ . '/layouts/footer.php'; ?>
