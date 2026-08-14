<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockFlow - Acesso ao Sistema</title>
    <!-- O caminho /css/style.css funciona bem no ambiente de produção e no dev se rodado da pasta public -->
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>

    <div class="auth-container">
        <div class="auth-box glass-panel animate-scale">
            <div class="auth-header animate-fade-up delay-100">
                <h1>StockFlow</h1>
                <p>Gestão de Requisições e Estoque</p>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-error">
                    <i class="ph ph-warning-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="/login" method="POST" class="animate-fade-up delay-200">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="input-group">
                    <label for="login">Usuário</label>
                    <div class="input-icon-wrapper">
                        <i class="ph ph-user"></i>
                        <input type="text" id="login" name="login" placeholder="Seu nome de usuário" required>
                    </div>
                </div>
                
                <div class="input-group">
                    <label for="senha">Senha</label>
                    <div class="input-icon-wrapper">
                        <i class="ph ph-lock-key"></i>
                        <input type="password" id="senha" name="senha" placeholder="Sua senha" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary animate-fade-up delay-300" style="width: 100%; margin-top: 1rem;">
                    Entrar no Sistema <i class="ph ph-sign-in"></i>
                </button>
            </form>
        </div>
    </div>

</body>
</html>
