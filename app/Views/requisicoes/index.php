<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockFlow - Requisições</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .action-btn { background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 1.25rem; transition: var(--transition); margin-right: 0.5rem; }
        .action-btn:hover { color: var(--accent-color); }
        .action-btn.approve:hover { color: var(--success); }
        .action-btn.reject:hover { color: var(--danger); }
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
                <li class="animate-fade-up delay-100"><a href="/dashboard"><i class="ph ph-squares-four"></i> Dashboard</a></li>
                <li class="animate-fade-up delay-200"><a href="/requisicoes" class="active"><i class="ph ph-file-text"></i> Requisições</a></li>
                <?php if(in_array($_SESSION['usuario_nivel'], ['Almoxarife', 'Administrador'])): ?>
                    <li class="animate-fade-up delay-300"><a href="/estoque"><i class="ph ph-archive"></i> Estoque</a></li>
                <?php endif; ?>
                <li class="animate-fade-up delay-400"><a href="/logout"><i class="ph ph-sign-out"></i> Sair</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="topbar animate-fade-up">
                <h2>Gestão de Requisições</h2>
                <div style="display: flex; gap: 1rem;">
                    <a href="?export=csv" class="btn" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-subtle);"><i class="ph ph-file-csv"></i> Exportar CSV</a>
                    <a href="/requisicoes/nova" class="btn btn-primary animate-fade-up delay-100"><i class="ph ph-plus"></i> Nova Requisição</a>
                </div>
            </header>

            <div class="glass-panel animate-fade-up delay-200" style="padding: 1.5rem;">
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
                                    <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                        Nenhuma requisição encontrada.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($requisicoes as $req): ?>
                                    <tr>
                                        <td>#<?= str_pad($req['id_requisicao'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td style="font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars($req['material']) ?></td>
                                        <td><?= $req['quantidade'] ?></td>
                                        <td style="color: var(--text-secondary);"><?= htmlspecialchars($req['solicitante']) ?></td>
                                        <td><span class="badge" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);"><?= htmlspecialchars($req['nome_setor']) ?></span></td>
                                        <td>
                                            <?php
                                                $badgeClass = 'badge-warning';
                                                if(str_contains($req['status'], 'Efetuada') || str_contains($req['status'], 'Despachado')) $badgeClass = 'badge-success';
                                                if($req['status'] === 'Recusado') $badgeClass = 'badge-danger';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= $req['status'] ?></span>
                                        </td>
                                        <td>
                                            <?php if($_SESSION['usuario_nivel'] === 'Coordenador' && $req['status'] === 'Pendente Coordenador'): ?>
                                                <a href="/requisicoes/aprovar/<?= $req['id_requisicao'] ?>" class="action-btn approve" title="Aprovar"><i class="ph ph-check-circle"></i></a>
                                                <a href="/requisicoes/recusar/<?= $req['id_requisicao'] ?>" class="action-btn reject" title="Recusar"><i class="ph ph-x-circle"></i></a>
                                            <?php endif; ?>
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
