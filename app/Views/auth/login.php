<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockFlow - Acesso ao Sistema</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>

    <div class="auth-container">
        <div class="auth-box glass-panel">
            <div class="auth-header">
                <h1>StockFlow</h1>
                <p>Gestão de Requisições e Estoque</p>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-error">
                    <i class="ph ph-warning-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="/login" method="POST">
                <div class="input-group">
                    <label for="login">Usuário</label>
                    <div style="position: relative;">
                        <i class="ph ph-user" style="position: absolute; left: 12px; top: 12px; color: var(--text-secondary); font-size: 1.2rem;"></i>
                        <input type="text" id="login" name="login" placeholder="Seu nome de usuário" required style="padding-left: 2.5rem; width: 100%;">
                    </div>
                </div>
                
                <div class="input-group">
                    <label for="senha">Senha</label>
                    <div style="position: relative;">
                        <i class="ph ph-lock-key" style="position: absolute; left: 12px; top: 12px; color: var(--text-secondary); font-size: 1.2rem;"></i>
                        <input type="password" id="senha" name="senha" placeholder="Sua senha" required style="padding-left: 2.5rem; width: 100%;">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                    Entrar no Sistema <i class="ph ph-sign-in"></i>
                </button>
            </form>
        </div>
    </div>

</body>
</html>
