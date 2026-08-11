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
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand animate-fade-up">
                <i class="ph ph-package"></i> StockFlow
            </div>
            
            <ul class="sidebar-menu">
                <li class="animate-fade-up delay-100"><a href="/dashboard"><i class="ph ph-chart-pie-slice"></i> Dashboard</a></li>
                <li class="animate-fade-up delay-200"><a href="/requisicoes"><i class="ph ph-file-text"></i> Requisições</a></li>
                <?php if(in_array($_SESSION['usuario_nivel'], ['Almoxarife', 'Administrador'])): ?>
                    <li class="animate-fade-up delay-300"><a href="/estoque" class="active"><i class="ph ph-archive"></i> Estoque</a></li>
                    <li class="animate-fade-up delay-400"><a href="/fornecedores"><i class="ph ph-truck"></i> Fornecedores</a></li>
                    <li class="animate-fade-up delay-400"><a href="/compras"><i class="ph ph-shopping-cart"></i> Compras</a></li>
                <?php endif; ?>
                <li class="animate-fade-up delay-400"><a href="/logout"><i class="ph ph-sign-out"></i> Sair</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="topbar animate-fade-up">
                <h2>Controle de Estoque</h2>
                <div style="display: flex; gap: 1rem;">
                    <a href="?export=csv" class="btn" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-subtle);"><i class="ph ph-file-csv"></i> Exportar CSV</a>
                    <button class="btn btn-primary" onclick="window.print()"><i class="ph ph-printer"></i> Imprimir Relatório</button>
                    <button class="btn btn-primary"><i class="ph ph-plus"></i> Cadastrar Produto</button>
                </div>
            </header>

            <div class="glass-panel animate-fade-up delay-200" style="padding: 1.5rem;">
                
                <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; max-width: 500px;">
                    <div class="input-icon-wrapper" style="flex:1;">
                        <i class="ph ph-magnifying-glass"></i>
                        <input type="text" id="searchInput" placeholder="Buscar por Nome ou ID...">
                    </div>
                    <button class="btn" style="background-color:var(--accent-light); color:var(--accent-color);" onclick="startScanner()"><i class="ph ph-barcode"></i> Escanear</button>
                </div>

                <div class="table-container">
                    <table id="estoqueTable">
                        <thead>
                            <tr>
                                <th>Cód.</th>
                                <th>Produto</th>
                                <th>Qtd Atual</th>
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
                                    <tr class="produto-row">
                                        <td class="prod-id">#<?= str_pad($prod['id_produto'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td class="prod-nome" style="font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars($prod['nome_produto']) ?></td>
                                        <td style="font-size: 1.125rem; font-weight: 600;"><?= $prod['quantidade_estoque'] ?></td>
                                        <td style="color: var(--text-secondary);"><?= $prod['estoque_minimo'] ?></td>
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

    <!-- Modal do Scanner -->
    <div id="scanner-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:999; flex-direction:column; align-items:center; justify-content:center;">
        <div style="background:var(--bg-card); padding:1rem; border-radius:var(--border-radius); width:90%; max-width:500px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:1rem;">
                <h3 style="font-size: 1.25rem; font-weight:600;">Escanear Código</h3>
                <button onclick="stopScanner()" style="background:none; border:none; cursor:pointer; font-size:1.5rem;"><i class="ph ph-x"></i></button>
            </div>
            <div id="reader" style="width:100%;"></div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        // Filtro da tabela
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            document.querySelectorAll('.produto-row').forEach(row => {
                const id = row.querySelector('.prod-id').innerText.toLowerCase();
                const nome = row.querySelector('.prod-nome').innerText.toLowerCase();
                if (id.includes(term) || nome.includes(term)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Lógica do Scanner
        let html5QrcodeScanner = null;

        function startScanner() {
            document.getElementById('scanner-modal').style.display = 'flex';
            html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: {width: 250, height: 250} }, false);
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        }

        function stopScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear();
            }
            document.getElementById('scanner-modal').style.display = 'none';
        }

        function onScanSuccess(decodedText, decodedResult) {
            const searchInput = document.getElementById('searchInput');
            searchInput.value = decodedText;
            searchInput.dispatchEvent(new Event('input'));
            stopScanner();
            
            searchInput.focus();
            searchInput.style.backgroundColor = 'var(--success-bg)';
            setTimeout(() => { searchInput.style.backgroundColor = 'var(--bg-card)'; }, 1000);
        }

        function onScanFailure(error) {
            // ignorar
        }
    </script>
    <script src="/js/notifications.js"></script>
    <script>
        // Data para a impressão
        const dateStr = new Date().toLocaleString('pt-BR');
        document.querySelector('.topbar h2').setAttribute('data-date', dateStr);
    </script>
</body>
</html>
