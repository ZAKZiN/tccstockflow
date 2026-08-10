<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockFlow - Requisições</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .table-container { overflow-x: auto; margin-top: 1.5rem; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 1rem; border-bottom: 1px solid var(--border-color); }
        th { color: var(--text-secondary); font-weight: 600; font-size: 0.875rem; }
        tr:hover { background-color: rgba(255,255,255,0.02); }
        .badge { padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .badge-pendente { background-color: rgba(245, 158, 11, 0.2); color: var(--warning); }
        .badge-aprovado { background-color: rgba(16, 185, 129, 0.2); color: var(--success); }
        .badge-recusado { background-color: rgba(239, 68, 68, 0.2); color: var(--danger); }
        .action-btn { background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 1.25rem; transition: var(--transition); margin-right: 0.5rem; }
        .action-btn:hover { color: var(--accent-color); }
        .action-btn.approve:hover { color: var(--success); }
        .action-btn.reject:hover { color: var(--danger); }
        /* Re-using layout from dashboard */
        /* Normally we would extract the layout to a base file and inject the content, 
           but for simplicity we include the sidebar here as well */
        .app-layout { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background-color: var(--bg-secondary); border-right: 1px solid var(--border-color); padding: 1.5rem; display: flex; flex-direction: column; }
        .sidebar-brand { font-size: 1.25rem; font-weight: 700; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.5rem; color: var(--text-primary); }
        .sidebar-menu { list-style: none; display: flex; flex-direction: column; gap: 0.5rem; }
        .sidebar-menu a { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 8px; color: var(--text-secondary); text-decoration: none; transition: var(--transition); }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: rgba(59, 130, 246, 0.1); color: var(--accent-color); }
        .main-content { flex: 1; padding: 2rem; overflow-y: auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
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
                <li><a href="/dashboard"><i class="ph ph-squares-four"></i> Dashboard</a></li>
                <li><a href="/requisicoes" class="active"><i class="ph ph-file-text"></i> Requisições</a></li>
                <?php if(in_array($_SESSION['usuario_nivel'], ['Almoxarife', 'Administrador'])): ?>
                    <li><a href="/estoque"><i class="ph ph-archive"></i> Estoque</a></li>
                <?php endif; ?>
                <li><a href="/logout"><i class="ph ph-sign-out"></i> Sair</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="topbar">
                <h2>Gestão de Requisições</h2>
                <a href="/requisicoes/nova" class="btn btn-primary"><i class="ph ph-plus"></i> Nova Requisição</a>
            </header>

            <div class="glass-panel" style="padding: 1.5rem;">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Material</th>
                                <th>Qtd</th>
                                <th>Solicitante</th>
                                <th>Setor</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($requisicoes)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                        Nenhuma requisição encontrada.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($requisicoes as $req): ?>
                                    <tr>
                                        <td>#<?= str_pad($req['id_requisicao'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td><strong><?= htmlspecialchars($req['material']) ?></strong></td>
                                        <td><?= $req['quantidade'] ?></td>
                                        <td><?= htmlspecialchars($req['solicitante']) ?></td>
                                        <td><?= htmlspecialchars($req['nome_setor']) ?></td>
                                        <td>
                                            <?php
                                                $badgeClass = 'badge-pendente';
                                                if(str_contains($req['status'], 'Efetuada') || str_contains($req['status'], 'Despachado')) $badgeClass = 'badge-aprovado';
                                                if($req['status'] === 'Recusado') $badgeClass = 'badge-recusado';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= $req['status'] ?></span>
                                        </td>
                                        <td>
                                            <!-- Regras de Ação -->
                                            <?php if($_SESSION['usuario_nivel'] === 'Coordenador' && $req['status'] === 'Pendente Coordenador'): ?>
                                                <a href="/requisicoes/aprovar/<?= $req['id_requisicao'] ?>" class="action-btn approve" title="Aprovar"><i class="ph ph-check-circle"></i></a>
                                                <a href="/requisicoes/recusar/<?= $req['id_requisicao'] ?>" class="action-btn reject" title="Recusar"><i class="ph ph-x-circle"></i></a>
                                            <?php endif; ?>
                                            
                                            <!-- Apenas visualização genérica -->
                                            <button class="action-btn" title="Ver Detalhes"><i class="ph ph-eye"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
