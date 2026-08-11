<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockFlow - Fornecedores</title>
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
                    <li class="animate-fade-up delay-400"><a href="/fornecedores" class="active"><i class="ph ph-truck"></i> Fornecedores</a></li>
                <?php endif; ?>
                <li class="animate-fade-up delay-400"><a href="/logout"><i class="ph ph-sign-out"></i> Sair</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="topbar animate-fade-up">
                <h2>Gestão de Fornecedores</h2>
                <button class="btn btn-primary animate-fade-up delay-100" onclick="document.getElementById('modal-novo').style.display='block'"><i class="ph ph-plus"></i> Novo Fornecedor</button>
            </header>

            <div class="glass-panel animate-fade-up delay-200" style="padding: 1.5rem;">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fornecedor</th>
                                <th>CNPJ</th>
                                <th>E-mail</th>
                                <th>Telefone</th>
                                <th>Data Cadastro</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($fornecedores)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                        Nenhum fornecedor cadastrado.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($fornecedores as $forn): ?>
                                    <tr>
                                        <td>#<?= str_pad($forn['id_fornecedor'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td style="font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($forn['nome_fantasia']) ?></td>
                                        <td><?= htmlspecialchars($forn['cnpj']) ?></td>
                                        <td><?= htmlspecialchars($forn['email']) ?></td>
                                        <td><?= htmlspecialchars($forn['telefone']) ?></td>
                                        <td style="color: var(--text-secondary);"><?= date('d/m/Y', strtotime($forn['criado_em'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Novo Fornecedor (Simples) -->
    <div id="modal-novo" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:50; align-items:center; justify-content:center;">
        <div class="glass-panel" style="width: 100%; max-width: 600px; padding: 2rem; margin: 5rem auto; background: var(--bg-card);">
            <div style="display:flex; justify-content:space-between; margin-bottom:1.5rem;">
                <h3 style="font-size: 1.25rem; font-weight:600;">Cadastrar Fornecedor</h3>
                <button onclick="document.getElementById('modal-novo').style.display='none'" style="background:none; border:none; cursor:pointer; font-size:1.5rem;"><i class="ph ph-x"></i></button>
            </div>
            
            <form action="/fornecedores" method="POST">
                <div class="form-grid">
                    <div class="input-group full-width">
                        <label>Nome Fantasia / Razão Social</label>
                        <input type="text" name="nome_fantasia" required>
                    </div>
                    <div class="input-group">
                        <label>CNPJ</label>
                        <input type="text" name="cnpj" placeholder="00.000.000/0000-00">
                    </div>
                    <div class="input-group">
                        <label>Telefone</label>
                        <input type="text" name="telefone" placeholder="(00) 0000-0000">
                    </div>
                    <div class="input-group full-width">
                        <label>E-mail de Contato</label>
                        <input type="email" name="email">
                    </div>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                    <button type="button" class="btn" onclick="document.getElementById('modal-novo').style.display='none'" style="background-color: var(--bg-base);">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Fornecedor</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Corrige exibição flex do modal quando acionado
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
