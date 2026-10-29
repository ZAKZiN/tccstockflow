<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <title>StockFlow - Gestão Comercial e Estoque</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/dark-mode.css">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2563eb">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>
</head>
<body>
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-theme');
        }
    </script>

    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand animate-fade-up d-flex justify-content-between align-items-center w-100">
                <span><i class="ph ph-package"></i> StockFlow</span>
                <div class="d-md-none d-flex align-items-center gap-3">
                    <a href="#" id="themeToggleMobile" style="color: var(--text-primary); text-decoration: none;" title="Alternar Tema"><i class="ph ph-moon fs-4"></i></a>
                    <form action="/logout" method="POST" style="display:inline; margin:0;" data-no-spinner="true">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" style="background:none; border:none; color: var(--danger); padding:0;" title="Sair"><i class="ph ph-sign-out fs-4"></i></button>
                    </form>
                </div>
            </div>
            
            <?php 
                $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'; 
                $nivel = $_SESSION['usuario_nivel'] ?? '';
            ?>
            <ul class="sidebar-menu">
                <?php if(in_array($nivel, ['Administrador', 'Gerente'], true)): ?>
                    <li class="animate-fade-up delay-100"><a href="/dashboard" class="<?= strpos($uri, '/dashboard') === 0 ? 'active' : '' ?>"><i class="ph ph-chart-pie-slice"></i> Dashboard</a></li>
                <?php endif; ?>

                <?php if(in_array($nivel, ['Administrador', 'Gerente', 'Operador de Caixa'], true)): ?>
                    <li class="animate-fade-up delay-150"><a href="/caixa" class="<?= strpos($uri, '/caixa') === 0 ? 'active' : '' ?>"><i class="ph ph-cash-register"></i> Caixa (Turno)</a></li>
                    <li class="animate-fade-up delay-200"><a href="/pdv" class="<?= strpos($uri, '/pdv') === 0 ? 'active' : '' ?>"><i class="ph ph-shopping-cart"></i> Frente de Caixa</a></li>
                    <li class="animate-fade-up delay-250"><a href="/fiado" class="<?= strpos($uri, '/fiado') === 0 ? 'active' : '' ?>"><i class="ph ph-notebook"></i> Fiado / Receber</a></li>
                <?php endif; ?>

                <?php if(in_array($nivel, ['Administrador', 'Gerente', 'Estoquista'], true)): ?>
                    <li class="animate-fade-up delay-300"><a href="/estoque" class="<?= strpos($uri, '/estoque') === 0 && strpos($uri, '/estoque/curva-abc') === false ? 'active' : '' ?>"><i class="ph ph-package"></i> Estoque</a></li>
                    <li class="animate-fade-up delay-350"><a href="/estoque/curva-abc" class="<?= strpos($uri, '/estoque/curva-abc') === 0 ? 'active' : '' ?>"><i class="ph ph-chart-line-up"></i> Curva ABC (BI)</a></li>
                    <li class="animate-fade-up delay-400"><a href="/fornecedores" class="<?= strpos($uri, '/fornecedores') === 0 ? 'active' : '' ?>"><i class="ph ph-truck"></i> Fornecedores</a></li>
                    <li class="animate-fade-up delay-400"><a href="/compras" class="<?= strpos($uri, '/compras') === 0 ? 'active' : '' ?>"><i class="ph ph-bag"></i> Compras</a></li>
                <?php endif; ?>

                <?php if(in_array($nivel, ['Administrador', 'Gerente', 'Estoquista', 'Coordenador', 'Solicitante'], true)): ?>
                    <li class="animate-fade-up delay-450"><a href="/requisicoes" class="<?= strpos($uri, '/requisicoes') === 0 ? 'active' : '' ?>"><i class="ph ph-clipboard-text"></i> Requisições</a></li>
                <?php endif; ?>

                <?php if(in_array($nivel, ['Administrador'], true)): ?>
                    <li class="animate-fade-up delay-450"><a href="/usuarios" class="<?= strpos($uri, '/usuarios') === 0 ? 'active' : '' ?>"><i class="ph ph-identification-card"></i> Usuários</a></li>
                    <li class="animate-fade-up delay-450"><a href="/auditoria" class="<?= strpos($uri, '/auditoria') === 0 ? 'active' : '' ?>"><i class="ph ph-shield-check"></i> Auditoria e Logs</a></li>
                <?php endif; ?>

                <li class="animate-fade-up delay-500 d-none d-md-block">
                    <a href="#" id="themeToggle"><i class="ph ph-moon"></i> Modo Escuro</a>
                </li>
                <li class="animate-fade-up delay-500 d-none d-md-block">
                    <form action="/logout" method="POST" style="margin:0;" data-no-spinner="true">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="sidebar-link-btn" style="color: var(--danger);"><i class="ph ph-sign-out"></i> Sair</button>
                    </form>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">

    <script>
        const themeToggle = document.getElementById('themeToggle');
        const themeToggleMobile = document.getElementById('themeToggleMobile');
        const body = document.body;
        
        function setDarkTheme(isDark) {
            if (isDark) {
                body.classList.add('dark-theme');
                localStorage.setItem('theme', 'dark');
                if (themeToggle) themeToggle.innerHTML = '<i class="ph ph-sun"></i> Modo Claro';
                if (themeToggleMobile) themeToggleMobile.innerHTML = '<i class="ph ph-sun fs-4"></i>';
            } else {
                body.classList.remove('dark-theme');
                localStorage.setItem('theme', 'light');
                if (themeToggle) themeToggle.innerHTML = '<i class="ph ph-moon"></i> Modo Escuro';
                if (themeToggleMobile) themeToggleMobile.innerHTML = '<i class="ph ph-moon fs-4"></i>';
            }
        }

        setDarkTheme(localStorage.getItem('theme') === 'dark');

        const toggleHandler = (e) => {
            if (e) e.preventDefault();
            setDarkTheme(!body.classList.contains('dark-theme'));
        };

        if (themeToggle) themeToggle.addEventListener('click', toggleHandler);
        if (themeToggleMobile) themeToggleMobile.addEventListener('click', toggleHandler);
    </script>
