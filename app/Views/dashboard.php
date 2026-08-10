<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockFlow - Dashboard</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        /* Layout Layout Styles for Dashboard */
        .app-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 260px;
            background-color: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-brand {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-primary);
        }
        
        .sidebar-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: var(--transition);
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: rgba(59, 130, 246, 0.1);
            color: var(--accent-color);
        }
        
        .sidebar-menu a i { font-size: 1.25rem; }
        
        .main-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }
        
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .avatar {
            width: 40px;
            height: 40px;
            background-color: var(--accent-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* Dashboard Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }
        
        .icon-blue { background-color: rgba(59, 130, 246, 0.2); color: var(--accent-color); }
        .icon-yellow { background-color: rgba(245, 158, 11, 0.2); color: var(--warning); }
        .icon-red { background-color: rgba(239, 68, 68, 0.2); color: var(--danger); }
        
        .stat-info h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .stat-info p {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
    </style>
</head>
<body>

    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <i class="ph ph-package"></i> StockFlow
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="/dashboard" class="active"><i class="ph ph-squares-four"></i> Dashboard</a></li>
                <li><a href="/requisicoes"><i class="ph ph-file-text"></i> Requisições</a></li>
                <?php if(in_array($_SESSION['usuario_nivel'], ['Almoxarife', 'Administrador'])): ?>
                    <li><a href="/estoque"><i class="ph ph-archive"></i> Estoque</a></li>
                <?php endif; ?>
                <li><a href="/logout"><i class="ph ph-sign-out"></i> Sair</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="topbar">
                <h2>Visão Geral</h2>
                <div class="user-profile">
                    <div class="user-info" style="text-align: right;">
                        <strong><?= htmlspecialchars($_SESSION['usuario_nome']) ?></strong>
                        <div style="font-size: 0.75rem; color: var(--text-secondary);"><?= htmlspecialchars($_SESSION['usuario_nivel']) ?> - <?= htmlspecialchars($_SESSION['usuario_setor'] ?? '') ?></div>
                    </div>
                    <div class="avatar">
                        <?= substr(htmlspecialchars($_SESSION['usuario_nome']), 0, 1) ?>
                    </div>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card glass-panel">
                    <div class="stat-icon icon-blue">
                        <i class="ph ph-file-text"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['total_requisicoes'] ?></h3>
                        <p>Total de Requisições</p>
                    </div>
                </div>
                
                <div class="stat-card glass-panel">
                    <div class="stat-icon icon-yellow">
                        <i class="ph ph-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['requisicoes_pendentes'] ?></h3>
                        <p>Pendentes de Aprovação</p>
                    </div>
                </div>
                
                <div class="stat-card glass-panel">
                    <div class="stat-icon icon-red">
                        <i class="ph ph-warning-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['estoque_critico'] ?></h3>
                        <p>Itens em Estoque Crítico</p>
                    </div>
                </div>
            </div>
            
            <div class="glass-panel" style="padding: 1.5rem;">
                <h3 style="margin-bottom: 1rem;">Bem-vindo(a) ao StockFlow</h3>
                <p style="color: var(--text-secondary); line-height: 1.6;">
                    Utilize o menu lateral para navegar pelas funcionalidades. Este painel permite o acompanhamento em tempo real das requisições e níveis de estoque da instituição.
                </p>
            </div>
        </main>
    </div>

</body>
</html>
