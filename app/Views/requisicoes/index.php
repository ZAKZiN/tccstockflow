<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockFlow - Requisições</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>

    <div class="app-layout">
        <aside class="sidebar">
            <div class="sidebar-brand animate-fade-up">
                <i class="ph ph-package"></i> StockFlow
            </div>
            <ul class="sidebar-menu">
                <li class="animate-fade-up delay-100"><a href="/dashboard"><i class="ph ph-chart-pie-slice"></i> Dashboard</a></li>
                <li class="animate-fade-up delay-200"><a href="/requisicoes" class="active"><i class="ph ph-file-text"></i> Requisições</a></li>
                <?php if(in_array($_SESSION['usuario_nivel'], ['Almoxarife', 'Administrador'])): ?>
                    <li class="animate-fade-up delay-300"><a href="/estoque"><i class="ph ph-archive"></i> Estoque</a></li>
                    <li class="animate-fade-up delay-400"><a href="/fornecedores"><i class="ph ph-truck"></i> Fornecedores</a></li>
                    <li class="animate-fade-up delay-400"><a href="/compras"><i class="ph ph-shopping-cart"></i> Compras</a></li>
                <?php endif; ?>
                <li class="animate-fade-up delay-400"><a href="/logout"><i class="ph ph-sign-out"></i> Sair</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="topbar animate-fade-up">
                <h2>Gestão de Requisições</h2>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-primary" onclick="window.print()" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-subtle);"><i class="ph ph-printer"></i> Imprimir Relatório</button>
                    <a href="?export=csv" class="btn" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-subtle);"><i class="ph ph-file-csv"></i> Exportar CSV</a>
                    <a href="/requisicoes/nova" class="btn btn-primary animate-fade-up delay-100"><i class="ph ph-plus"></i> Nova Requisição</a>
                </div>
            </header>

            <div class="glass-panel animate-fade-up delay-200" style="padding: 1.5rem;">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Cód.</th>
                                <th>Material</th>
                                <th>Qtd</th>
                                <th>Solicitante</th>
                                <th>Setor</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($requisicoes)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                        Nenhuma requisição encontrada.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($requisicoes as $req): ?>
                                    <tr>
                                        <td>#<?= str_pad($req['id_requisicao'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td style="font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars($req['material']) ?></td>
                                        <td><?= $req['quantidade'] ?></td>
                                        <td><?= htmlspecialchars($req['solicitante']) ?></td>
                                        <td><?= htmlspecialchars($req['nome_setor']) ?></td>
                                        <td>
                                            <?php 
                                                $s = $req['status'];
                                                $badgeClass = 'badge-warning';
                                                if(strpos($s, 'Aprovado') !== false || strpos($s, 'Efetuada') !== false || strpos($s, 'Despachado') !== false) $badgeClass = 'badge-success';
                                                if($s == 'Recusado') $badgeClass = 'badge-danger';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= $s ?></span>
                                        </td>
                                        <td style="color: var(--text-secondary);"><?= date('d/m/Y', strtotime($req['data_solicitacao'])) ?></td>
                                        <td>
                                            <?php if($req['status'] === 'Pendente Coordenador' && $_SESSION['usuario_nivel'] === 'Coordenador'): ?>
                                                <a href="/requisicoes/aprovar/<?= $req['id_requisicao'] ?>" class="btn" style="background-color: var(--success-bg); color: var(--success); padding: 0.25rem 0.5rem; font-size: 0.75rem;">Aprovar</a>
                                                <a href="/requisicoes/recusar/<?= $req['id_requisicao'] ?>" class="btn" style="background-color: var(--danger-bg); color: var(--danger); padding: 0.25rem 0.5rem; font-size: 0.75rem;">Recusar</a>
                                            <?php endif; ?>
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
    <script src="/js/notifications.js"></script>
    <script>
        // Data para a impressão
        const dateStr = new Date().toLocaleString('pt-BR');
        document.querySelector('.topbar h2').setAttribute('data-date', dateStr);
    </script>
</body>
</html>
