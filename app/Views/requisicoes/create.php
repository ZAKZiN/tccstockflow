<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockFlow - Nova Requisição</title>
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
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <a href="/requisicoes" style="color: var(--text-secondary); text-decoration: none; transition: var(--transition);" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-secondary)'"><i class="ph ph-arrow-left" style="font-size: 1.5rem;"></i></a>
                    <h2>Nova Requisição</h2>
                </div>
            </header>

            <div class="glass-panel animate-fade-up delay-100" style="padding: 2.5rem; max-width: 800px;">
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
                    
                    <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
                        <a href="/requisicoes" class="btn" style="background-color: rgba(255,255,255,0.05); color: var(--text-primary); border: 1px solid var(--border-subtle);">Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="ph ph-paper-plane-tilt"></i> Enviar Requisição</button>
                    </div>

                </form>
            </div>
        </main>
    </div>

<script src="/js/notifications.js"></script>
</body>
</html>
