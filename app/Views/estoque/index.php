<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockFlow - Estoque</title>
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
                <li class="animate-fade-up delay-100"><a href="/dashboard"><i class="ph ph-squares-four"></i> Dashboard</a></li>
                <li class="animate-fade-up delay-200"><a href="/requisicoes"><i class="ph ph-file-text"></i> Requisições</a></li>
                <li class="animate-fade-up delay-300"><a href="/estoque" class="active"><i class="ph ph-archive"></i> Estoque</a></li>
                <li class="animate-fade-up delay-400"><a href="/logout"><i class="ph ph-sign-out"></i> Sair</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="topbar animate-fade-up">
                <h2>Controle de Estoque</h2>
                <div style="display: flex; gap: 1rem;">
                    <a href="?export=csv" class="btn" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-subtle);"><i class="ph ph-file-csv"></i> Exportar CSV</a>
                    <button class="btn btn-primary"><i class="ph ph-plus"></i> Cadastrar Produto</button>
                </div>
            </header>

            <div class="glass-panel animate-fade-up delay-200" style="padding: 1.5rem;">
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
                                    <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                        Nenhum produto cadastrado no estoque.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($produtos as $prod): ?>
                                    <tr>
                                        <td>#<?= str_pad($prod['id_produto'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td style="font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars($prod['nome_produto']) ?></td>
                                        <td><?= $prod['quantidade_estoque'] ?></td>
                                        <td><?= $prod['estoque_minimo'] ?></td>
                                        <td>
                                            <?php 
                                                if ($prod['quantidade_estoque'] <= 0) {
                                                    echo '<span class="badge badge-danger">Esgotado</span>';
                                                } elseif ($prod['quantidade_estoque'] <= $prod['estoque_minimo']) {
                                                    echo '<span class="badge badge-warning">Crítico</span>';
                                                } else {
                                                    echo '<span class="badge badge-success">Regular</span>';
                                                }
                                            ?>
                                        </td>
                                        <td style="color: var(--text-secondary);"><?= date('d/m/Y H:i', strtotime($prod['atualizado_em'])) ?></td>
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
