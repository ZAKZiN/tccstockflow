<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockFlow - Estoque</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .app-layout { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background-color: var(--bg-secondary); border-right: 1px solid var(--border-color); padding: 1.5rem; display: flex; flex-direction: column; }
        .sidebar-brand { font-size: 1.25rem; font-weight: 700; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.5rem; color: var(--text-primary); }
        .sidebar-menu { list-style: none; display: flex; flex-direction: column; gap: 0.5rem; }
        .sidebar-menu a { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 8px; color: var(--text-secondary); text-decoration: none; transition: var(--transition); }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: rgba(59, 130, 246, 0.1); color: var(--accent-color); }
        .main-content { flex: 1; padding: 2rem; overflow-y: auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        
        .table-container { overflow-x: auto; margin-top: 1.5rem; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 1rem; border-bottom: 1px solid var(--border-color); }
        th { color: var(--text-secondary); font-weight: 600; font-size: 0.875rem; }
        tr:hover { background-color: rgba(255,255,255,0.02); }
        
        .status-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 8px; }
        .status-ok { background-color: var(--success); }
        .status-alert { background-color: var(--warning); }
        .status-critical { background-color: var(--danger); }
    </style>
</head>
<body>

    <div class="app-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <i class="ph ph-package"></i> StockFlow
            </div>
            <ul class="sidebar-menu">
                <li><a href="/dashboard"><i class="ph ph-squares-four"></i> Dashboard</a></li>
                <li><a href="/requisicoes"><i class="ph ph-file-text"></i> Requisições</a></li>
                <li><a href="/estoque" class="active"><i class="ph ph-archive"></i> Estoque</a></li>
                <li><a href="/logout"><i class="ph ph-sign-out"></i> Sair</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <h2>Controle de Estoque</h2>
                <button class="btn btn-primary"><i class="ph ph-plus"></i> Cadastrar Produto</button>
            </header>

            <div class="glass-panel" style="padding: 1.5rem;">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Produto</th>
                                <th>Qtd. Atual</th>
                                <th>Estoque Mínimo</th>
                                <th>Status</th>
                                <th>Última Atualização</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($produtos)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                        Nenhum produto cadastrado no estoque.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($produtos as $prod): ?>
                                    <tr>
                                        <td>#<?= str_pad($prod['id_produto'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td><strong><?= htmlspecialchars($prod['nome_produto']) ?></strong></td>
                                        <td><?= $prod['quantidade_estoque'] ?></td>
                                        <td><?= $prod['estoque_minimo'] ?></td>
                                        <td>
                                            <?php 
                                                if ($prod['quantidade_estoque'] <= 0) {
                                                    echo '<span class="status-dot status-critical"></span>Esgotado';
                                                } elseif ($prod['quantidade_estoque'] <= $prod['estoque_minimo']) {
                                                    echo '<span class="status-dot status-alert"></span>Crítico';
                                                } else {
                                                    echo '<span class="status-dot status-ok"></span>Regular';
                                                }
                                            ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($prod['atualizado_em'])) ?></td>
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
