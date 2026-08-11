<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockFlow - Ordens de Compra</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .full-width { grid-column: 1 / -1; }
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
                <li class="animate-fade-up delay-100"><a href="/dashboard"><i class="ph ph-chart-pie-slice"></i> Dashboard</a></li>
                <li class="animate-fade-up delay-200"><a href="/requisicoes"><i class="ph ph-file-text"></i> Requisições</a></li>
                <?php if(in_array($_SESSION['usuario_nivel'], ['Almoxarife', 'Administrador'])): ?>
                    <li class="animate-fade-up delay-300"><a href="/estoque"><i class="ph ph-archive"></i> Estoque</a></li>
                    <li class="animate-fade-up delay-400"><a href="/fornecedores"><i class="ph ph-truck"></i> Fornecedores</a></li>
                    <li class="animate-fade-up delay-400"><a href="/compras" class="active"><i class="ph ph-shopping-cart"></i> Compras</a></li>
                <?php endif; ?>
                <li class="animate-fade-up delay-400"><a href="/logout"><i class="ph ph-sign-out"></i> Sair</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="topbar animate-fade-up">
                <h2>Ordens de Compra</h2>
                <button class="btn btn-primary animate-fade-up delay-100" onclick="document.getElementById('modal-novo').style.display='block'"><i class="ph ph-plus"></i> Registrar Compra</button>
            </header>

            <div class="glass-panel animate-fade-up delay-200" style="padding: 1.5rem;">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>OC #</th>
                                <th>Material (Req)</th>
                                <th>Qtd</th>
                                <th>Fornecedor</th>
                                <th>Valor Total</th>
                                <th>Data da Compra</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($compras)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                        Nenhuma ordem de compra registrada.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($compras as $comp): ?>
                                    <tr>
                                        <td>#<?= str_pad($comp['id_compra'], 5, '0', STR_PAD_LEFT) ?></td>
                                        <td style="font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars($comp['material']) ?></td>
                                        <td><?= $comp['quantidade'] ?></td>
                                        <td><?= htmlspecialchars($comp['nome_fantasia']) ?></td>
                                        <td style="font-weight: 600;">R$ <?= number_format($comp['valor_total'], 2, ',', '.') ?></td>
                                        <td style="color: var(--text-secondary);"><?= date('d/m/Y H:i', strtotime($comp['data_compra'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Nova Compra -->
    <div id="modal-novo" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:50; align-items:center; justify-content:center;">
        <div class="glass-panel" style="width: 100%; max-width: 600px; padding: 2rem; margin: 5rem auto; background: var(--bg-card);">
            <div style="display:flex; justify-content:space-between; margin-bottom:1.5rem;">
                <h3 style="font-size: 1.25rem; font-weight:600;">Registrar Ordem de Compra</h3>
                <button onclick="document.getElementById('modal-novo').style.display='none'" style="background:none; border:none; cursor:pointer; font-size:1.5rem;"><i class="ph ph-x"></i></button>
            </div>
            
            <form action="/compras" method="POST">
                <div class="form-grid">
                    <div class="input-group full-width">
                        <label>Vincular Requisição</label>
                        <select name="id_requisicao" required>
                            <option value="">Selecione uma requisição aprovada/pendente...</option>
                            <?php foreach($requisicoes as $r): ?>
                                <option value="<?= $r['id_requisicao'] ?>">#<?= $r['id_requisicao'] ?> - <?= htmlspecialchars($r['material']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group full-width">
                        <label>Fornecedor</label>
                        <select name="id_fornecedor" required>
                            <option value="">Selecione o fornecedor...</option>
                            <?php foreach($fornecedores as $f): ?>
                                <option value="<?= $f['id_fornecedor'] ?>"><?= htmlspecialchars($f['nome_fantasia']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group full-width">
                        <label>Valor Total (R$)</label>
                        <input type="number" step="0.01" name="valor_total" placeholder="0.00" required>
                    </div>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                    <button type="button" class="btn" onclick="document.getElementById('modal-novo').style.display='none'" style="background-color: var(--bg-base);">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar Compra</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        const modal = document.getElementById('modal-novo');
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'style' && modal.style.display === 'block') {
                    modal.style.display = 'flex';
                }
            });
        });
        observer.observe(modal, { attributes: true });
    </script>
</body>
</html>
