<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Criar Conta - StockFlow</title>
    <!-- Tailwind CSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">

<div class="bg-white p-8 rounded shadow-md w-full max-w-md">
    <h1 class="text-2xl font-bold text-center mb-6 text-indigo-600">StockFlow SaaS</h1>
    <h2 class="text-xl text-center mb-6 text-gray-700">Registre sua Empresa</h2>

    <?php if (isset($error) && $error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/registro">
        <input type="hidden" name="plano" value="<?= htmlspecialchars($_GET['plano'] ?? 'mensal') ?>">
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Nome da Empresa</label>
            <input type="text" name="razao_social" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Minha Loja">
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Seu Nome (Administrador)</label>
            <input type="text" name="nome" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="João da Silva">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Login (Usuário)</label>
            <input type="text" name="login" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="admin.joao">
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">Senha</label>
            <input type="password" name="senha" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" placeholder="******">
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                Criar Conta
            </button>
        </div>
        <div class="text-center mt-4">
            <a href="/" class="text-sm text-indigo-600 hover:text-indigo-800">Já tem uma conta? Faça Login</a>
        </div>
    </form>
</div>

</body>
</html>
