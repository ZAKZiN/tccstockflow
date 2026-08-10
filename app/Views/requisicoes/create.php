<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockFlow - Nova Requisição</title>
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
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .full-width { grid-column: 1 / -1; }
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
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <a href="/requisicoes" style="color: var(--text-secondary); text-decoration: none;"><i class="ph ph-arrow-left" style="font-size: 1.5rem;"></i></a>
                    <h2>Nova Requisição</h2>
                </div>
            </header>

            <div class="glass-panel" style="padding: 2rem; max-width: 800px;">
                <form action="/requisicoes/nova" method="POST">
                    
                    <div class="form-grid">
                        <div class="input-group full-width">
                            <label for="material">Material/Produto</label>
                            <input type="text" id="material" name="material" placeholder="Ex: Pacote de Folha Sulfite A4" required>
                        </div>
                        
                        <div class="input-group">
                            <label for="quantidade">Quantidade</label>
                            <input type="number" id="quantidade" name="quantidade" min="1" value="1" required>
                        </div>
                        
                        <div class="input-group">
                            <label for="prioridade">Prioridade</label>
                            <select id="prioridade" name="prioridade">
                                <option value="Baixa">Baixa</option>
                                <option value="Média" selected>Média</option>
                                <option value="Alta">Alta</option>
                                <option value="Urgente">Urgente</option>
                            </select>
                        </div>
                        
                        <div class="input-group full-width">
                            <label for="justificativa">Justificativa (Opcional)</label>
                            <textarea id="justificativa" name="justificativa" rows="3" placeholder="Por que este material é necessário?"></textarea>
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                        <a href="/requisicoes" class="btn" style="background-color: var(--bg-secondary); color: var(--text-primary);">Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="ph ph-paper-plane-tilt"></i> Enviar Requisição</button>
                    </div>

                </form>
            </div>
        </main>
    </div>

</body>
</html>
