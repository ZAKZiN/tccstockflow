<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockFlow - Dashboard</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .chart-container {
            padding: 1.5rem;
            border-radius: var(--border-radius);
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            box-shadow: var(--shadow-soft);
        }
        .chart-header {
            margin-bottom: 1rem;
            font-weight: 600;
            color: var(--text-primary);
        }
    </style>
</head>
<body>

    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand animate-fade-up">
                <i class="ph ph-package"></i> StockFlow
            </div>
            
            <ul class="sidebar-menu">
                <li class="animate-fade-up delay-100"><a href="/dashboard" class="active"><i class="ph ph-chart-pie-slice"></i> Dashboard</a></li>
                <li class="animate-fade-up delay-200"><a href="/requisicoes"><i class="ph ph-file-text"></i> Requisições</a></li>
                <?php if(in_array($_SESSION['usuario_nivel'], ['Almoxarife', 'Administrador'])): ?>
                    <li class="animate-fade-up delay-300"><a href="/estoque"><i class="ph ph-archive"></i> Estoque</a></li>
                <?php endif; ?>
                <li class="animate-fade-up delay-400"><a href="/logout"><i class="ph ph-sign-out"></i> Sair</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="topbar animate-fade-up">
                <h2>Visão Geral</h2>
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

            <div class="stats-grid">
                <div class="stat-card glass-panel animate-fade-up delay-100">
                    <div class="stat-icon icon-blue">
                        <i class="ph ph-file-text"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['total_requisicoes'] ?></h3>
                        <p>Total de Requisições</p>
                    </div>
                </div>
                
                <div class="stat-card glass-panel animate-fade-up delay-200">
                    <div class="stat-icon icon-yellow">
                        <i class="ph ph-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['requisicoes_pendentes'] ?></h3>
                        <p>Pendentes de Aprovação</p>
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
                    <div class="chart-header">Evolução de Requisições (Ano Atual)</div>
                    <canvas id="lineChart" height="100"></canvas>
                </div>
                <div class="chart-container">
                    <div class="chart-header">Demandas por Setor</div>
                    <canvas id="doughnutChart" height="200"></canvas>
                </div>
            </div>
            
        </main>
    </div>

    <script>
        // Line Chart (Requisitions per Month)
        const ctxLine = document.getElementById('lineChart').getContext('2d');
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                datasets: [{
                    label: 'Requisições',
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

        // Doughnut Chart (Requisitions per Sector)
        const ctxDoughnut = document.getElementById('doughnutChart').getContext('2d');
        new Chart(ctxDoughnut, {
            type: 'doughnut',
            data: {
                labels: <?= $stats['chart_setores_labels'] ?>,
                datasets: [{
                    data: <?= $stats['chart_setores_data'] ?>,
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
</body>
</html>
