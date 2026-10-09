<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Bloqueado - StockFlow</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">

<div class="bg-white p-10 rounded-lg shadow-xl w-full max-w-lg text-center">
    <div class="flex justify-center mb-6">
        <i class="ph ph-lock-key text-red-500 text-6xl"></i>
    </div>
    <h1 class="text-3xl font-bold text-gray-800 mb-4">Acesso Suspenso</h1>
    <p class="text-gray-600 text-lg mb-8">
        Sua assinatura encontra-se suspensa. Para continuar utilizando o PDV, Caixa, Estoque e demais ferramentas operacionais, por favor regularize a sua mensalidade.
    </p>
    
    <div class="flex flex-col gap-4">
        <a href="#" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg transition-colors flex items-center justify-center gap-2">
            <i class="ph ph-credit-card text-xl"></i> Regularizar Pagamento
        </a>
        <a href="/logout" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-6 rounded-lg transition-colors flex items-center justify-center gap-2">
            <i class="ph ph-sign-out text-xl"></i> Sair do Sistema
        </a>
    </div>
</div>

</body>
</html>
