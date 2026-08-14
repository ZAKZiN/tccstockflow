<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockFlow - Gestão Comercial</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>

    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand animate-fade-up">
                <i class="ph ph-package"></i> StockFlow
            </div>
            
            <?php 
                $uri = $_SERVER['REQUEST_URI'] ?? '/'; 
                $nivel = $_SESSION['usuario_nivel'] ?? '';
            ?>
            <ul class="sidebar-menu">
                <?php if(in_array($nivel, ['Administrador', 'Gerente'])): ?>
                    <li class="animate-fade-up delay-100"><a href="/dashboard" class="<?= strpos($uri, '/dashboard') === 0 ? 'active' : '' ?>"><i class="ph ph-chart-pie-slice"></i> Dashboard</a></li>
                <?php endif; ?>

                <?php if(in_array($nivel, ['Administrador', 'Gerente', 'Operador de Caixa'])): ?>
                    <li class="animate-fade-up delay-150"><a href="/caixa" class="<?= strpos($uri, '/caixa') === 0 ? 'active' : '' ?>"><i class="ph ph-cash-register"></i> Caixa (Turno)</a></li>
                    <li class="animate-fade-up delay-200"><a href="/pdv" class="<?= strpos($uri, '/pdv') === 0 ? 'active' : '' ?>"><i class="ph ph-shopping-cart"></i> Frente de Caixa</a></li>
                    <li class="animate-fade-up delay-250"><a href="/fiado" class="<?= strpos($uri, '/fiado') === 0 ? 'active' : '' ?>"><i class="ph ph-notebook"></i> Fiado / Receber</a></li>
                <?php endif; ?>

                <?php if(in_array($nivel, ['Administrador', 'Gerente', 'Estoquista'])): ?>
                    <li class="animate-fade-up delay-300"><a href="/estoque" class="<?= strpos($uri, '/estoque') === 0 ? 'active' : '' ?>"><i class="ph ph-archive"></i> Estoque</a></li>
                    <li class="animate-fade-up delay-400"><a href="/fornecedores" class="<?= strpos($uri, '/fornecedores') === 0 ? 'active' : '' ?>"><i class="ph ph-truck"></i> Fornecedores</a></li>
                    <li class="animate-fade-up delay-400"><a href="/compras" class="<?= strpos($uri, '/compras') === 0 ? 'active' : '' ?>"><i class="ph ph-shopping-cart"></i> Compras</a></li>
                <?php endif; ?>

                <?php if($nivel === 'Administrador'): ?>
                    <li class="animate-fade-up delay-450"><a href="/usuarios" class="<?= strpos($uri, '/usuarios') === 0 ? 'active' : '' ?>"><i class="ph ph-users"></i> Usuários (Cargos)</a></li>
                <?php endif; ?>

                <li class="animate-fade-up delay-500"><a href="/logout"><i class="ph ph-sign-out"></i> Sair</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
