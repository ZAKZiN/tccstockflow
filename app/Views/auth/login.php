<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockFlow - Acesso ao Sistema</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/dark-mode.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-theme');
        }
    </script>

    <div class="auth-container">
        <div class="auth-box glass-panel animate-scale">
            <div class="auth-header animate-fade-up delay-100">
                <h1><i class="ph ph-package" style="color: var(--primary);"></i> StockFlow</h1>
                <p>Gestão Integrada de Estoque, Requisições e PDV</p>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-error">
                    <i class="ph ph-warning-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="/login" method="POST" class="animate-fade-up delay-200">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                
                <!-- Cibersegurança: Bot Protection (Honeypot) - Item 13 -->
                <div style="display:none;" aria-hidden="true">
                    <input type="text" name="website" tabindex="-1" autocomplete="off">
                </div>
                
                <div class="input-group">
                    <label for="login">Usuário</label>
                    <div class="input-icon-wrapper">
                        <i class="ph ph-user"></i>
                        <input type="text" id="login" name="login" placeholder="Seu nome de usuário" autocomplete="username" required autofocus>
                    </div>
                </div>
                
                <div class="input-group">
                    <label for="senha">Senha</label>
                    <div class="input-icon-wrapper">
                        <i class="ph ph-lock-key"></i>
                        <input type="password" id="senha" name="senha" placeholder="Sua senha" autocomplete="current-password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary animate-fade-up delay-300" style="width: 100%; margin-top: 0.75rem;">
                    Entrar no Sistema <i class="ph ph-sign-in"></i>
                </button>
            </form>
        </div>
    </div>

</body>
</html>
