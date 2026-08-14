<?php include __DIR__ . '/layouts/header.php'; ?>

            <header class="topbar animate-fade-up">
                <h2>Visão Geral do Negócio</h2>
                <div class="user-profile">
                    <div class="user-info">
                        <strong><?= htmlspecialchars($_SESSION['usuario_nome']) ?></strong>
                        <span><?= htmlspecialchars($_SESSION['usuario_nivel']) ?> - <?= htmlspecialchars($_SESSION['usuario_setor'] ?? '') ?></span>
                    </div>
                    <div class="avatar">
                        <?= substr(htmlspecialchars($_SESSION['usuario_nome']), 0, 1) ?>
                    </div>
                </div>
            </header>

            <?php if(!empty($stats['vencendo'])): ?>
                <div class="alert alert-error animate-fade-up" style="display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1.5rem; background-color: var(--danger-bg); border-left: 4px solid var(--danger); padding: 1rem; border-radius: 8px;">
                    <div style="font-weight: 600; font-size: 1.1rem; color: var(--danger);"><i class="ph ph-warning"></i> Atenção: Prevenção de Perdas!</div>
                    <div style="color: var(--text-primary);">Você possui <?= count($stats['vencendo']) ?> produto(s) vencendo em 7 dias ou menos. Sugestão: Faça uma promoção!</div>
                    <ul style="margin-left: 1.5rem; margin-top: 0.5rem; list-style-type: disc; color: var(--text-secondary);">
                        <?php foreach($stats['vencendo'] as $vp): ?>
                            <li>
                                <strong style="color: var(--text-primary);"><?= htmlspecialchars($vp['nome_produto']) ?></strong> - 
                                Vence em: <?= date('d/m/Y', strtotime($vp['data_validade'])) ?> 
                                (<?= $vp['quantidade_estoque'] ?> unid. em estoque)
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
                
                <div class="stat-card glass-panel animate-fade-up delay-200">
                    <div class="stat-icon icon-yellow">
                        <i class="ph ph-receipt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['vendas_hoje'] ?></h3>
                        <p>Vendas Realizadas Hoje</p>
                    </div>
                </div>
                
                <div class="stat-card glass-panel animate-fade-up delay-300">
                    <div class="stat-icon icon-red">
                        <i class="ph ph-warning-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['estoque_critico'] ?></h3>
                        <p>Itens em Estoque Crítico</p>
                    </div>
                </div>
            </div>
            
            <!-- Analytics Charts -->
            <div class="charts-grid animate-fade-up delay-400">
                <div class="chart-container">
                    <div class="chart-header">Evolução do Faturamento Mensal (R$)</div>
                    <canvas id="lineChart" height="100"></canvas>
                </div>
                <div class="chart-container">
                    <div class="chart-header">Gráfico de Produtos Mais Vendidos</div>
                    <canvas id="doughnutChart" height="200"></canvas>
                </div>
            </div>

            <!-- Tabela de Produtos com Mais Saídas -->
            <div class="glass-panel animate-fade-up delay-500" style="padding: 1.5rem; margin-bottom: 2rem;">
                <div class="chart-header" style="margin-bottom: 1.5rem;"><i class="ph ph-trend-up"></i> Ranking: Produtos com Mais Saídas (Top 10)</div>
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
                                        <td style="color: var(--text-secondary);">#<?= str_pad($tp['id_produto'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td style="font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars($tp['nome_produto']) ?></td>
                                        <td style="text-align: center; font-weight: 600; font-size: 1.1rem;"><?= $tp['qtd_vendida'] ?></td>
                                        <td style="text-align: right; color: #10b981; font-weight: 600;">R$ <?= number_format($tp['valor_gerado'], 2, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Line Chart (Requisitions per Month)
        const ctxLine = document.getElementById('lineChart').getContext('2d');
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                datasets: [{
                    label: 'Faturamento (R$)',
                    data: <?= $stats['chart_mensal'] ?>,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#e5e7eb' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Doughnut Chart (Top Products)
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
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>

<?php include __DIR__ . '/layouts/footer.php'; ?>
