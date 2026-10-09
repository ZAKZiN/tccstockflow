<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockFlow | Sistema de Gestão e PDV</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Usando a mesma fonte do sistema -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; color: #111827; }
        .glass-panel {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body class="antialiased overflow-x-hidden">

    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-2">
                    <i class="ph-fill ph-package text-slate-900 text-2xl"></i>
                    <span class="font-bold text-xl text-slate-900">StockFlow</span>
                </div>
                
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#funcionalidades" class="text-gray-600 hover:text-slate-900 font-medium text-sm">Funcionalidades</a>
                    <a href="#seguranca" class="text-gray-600 hover:text-slate-900 font-medium text-sm">Segurança</a>
                    <a href="#precos" class="text-gray-600 hover:text-slate-900 font-medium text-sm">Preços</a>
                </div>

                <div class="flex items-center space-x-4">
                    <a href="/login" class="text-gray-600 font-medium hover:text-slate-900 text-sm hidden sm:block">Acessar Sistema</a>
                    <a href="/registro" class="bg-slate-900 hover:bg-slate-800 text-white font-medium py-2 px-4 rounded-md text-sm transition-colors shadow-sm flex items-center gap-2">
                        Criar Conta
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-16 pb-12 bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto">
                <h1 class="text-3xl md:text-5xl font-bold text-slate-900 mb-6 leading-tight">
                    Gestão de Estoque e PDV <br />para o seu negócio
                </h1>
                <p class="text-lg text-gray-600 mb-8">
                    Controle suas vendas, gerencie o fiado e o estoque em um único painel. Uma interface limpa, rápida e construída para a operação diária.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="/registro" class="w-full sm:w-auto bg-slate-900 hover:bg-slate-800 text-white font-medium py-3 px-6 rounded-md transition-colors shadow-sm">
                        Testar o Sistema
                    </a>
                    <a href="#funcionalidades" class="w-full sm:w-auto bg-white hover:bg-gray-50 text-slate-900 font-medium py-3 px-6 rounded-md border border-gray-300 transition-colors">
                        Ver Funcionalidades
                    </a>
                </div>
            </div>
            
            <!-- System Interface Preview -->
            <div class="mt-16 mx-auto max-w-4xl glass-panel p-1 rounded-t-xl overflow-hidden shadow-lg">
                <div class="bg-gray-50 border-b border-gray-200 h-10 flex items-center px-4 gap-2">
                    <div class="w-3 h-3 rounded-full bg-gray-300"></div>
                    <div class="w-3 h-3 rounded-full bg-gray-300"></div>
                    <div class="w-3 h-3 rounded-full bg-gray-300"></div>
                </div>
                <!-- Fake Dashboard -->
                <div class="bg-gray-100 flex p-4 gap-4 h-[300px] md:h-[400px]">
                    <!-- Sidebar Mock -->
                    <div class="hidden md:flex w-48 bg-white border border-gray-200 rounded-md flex-col p-4 gap-2">
                        <div class="h-6 w-24 bg-slate-200 rounded mb-4"></div>
                        <div class="h-8 w-full bg-slate-100 rounded"></div>
                        <div class="h-8 w-full bg-gray-50 rounded"></div>
                        <div class="h-8 w-full bg-gray-50 rounded"></div>
                    </div>
                    <!-- Main Mock -->
                    <div class="flex-1 flex flex-col gap-4">
                        <div class="flex justify-between items-center bg-white p-4 border border-gray-200 rounded-md h-16">
                            <div class="h-6 w-32 bg-gray-200 rounded"></div>
                            <div class="h-8 w-8 bg-slate-900 rounded-full"></div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-white p-4 border border-gray-200 rounded-md h-24 flex flex-col justify-center">
                                <div class="h-4 w-16 bg-gray-200 rounded mb-2"></div>
                                <div class="h-6 w-24 bg-slate-800 rounded"></div>
                            </div>
                            <div class="bg-white p-4 border border-gray-200 rounded-md h-24 flex flex-col justify-center">
                                <div class="h-4 w-16 bg-gray-200 rounded mb-2"></div>
                                <div class="h-6 w-24 bg-emerald-600 rounded"></div>
                            </div>
                            <div class="bg-white p-4 border border-gray-200 rounded-md h-24 flex flex-col justify-center">
                                <div class="h-4 w-16 bg-gray-200 rounded mb-2"></div>
                                <div class="h-6 w-24 bg-amber-600 rounded"></div>
                            </div>
                            <div class="bg-white p-4 border border-gray-200 rounded-md h-24 flex flex-col justify-center">
                                <div class="h-4 w-16 bg-gray-200 rounded mb-2"></div>
                                <div class="h-6 w-24 bg-blue-600 rounded"></div>
                            </div>
                        </div>
                        <div class="bg-white border border-gray-200 rounded-md flex-1 p-4">
                            <div class="h-4 w-32 bg-gray-200 rounded mb-4"></div>
                            <div class="space-y-2">
                                <div class="h-8 w-full bg-gray-50 rounded"></div>
                                <div class="h-8 w-full bg-gray-50 rounded"></div>
                                <div class="h-8 w-full bg-gray-50 rounded"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Funcionalidades Grid -->
    <section id="funcionalidades" class="py-16 bg-gray-50 border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-2xl font-bold text-slate-900 mb-2">Módulos do Sistema</h2>
                <p class="text-gray-600">Ferramentas focadas na operação real de balcão.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="glass-panel p-6">
                    <i class="ph ph-barcode text-3xl text-slate-900 mb-4"></i>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Frente de Caixa (PDV)</h3>
                    <p class="text-gray-600 text-sm">Venda ágil com suporte a leitor de código de barras. Interface simplificada para não atrasar a fila.</p>
                </div>
                
                <div class="glass-panel p-6">
                    <i class="ph ph-handshake text-3xl text-slate-900 mb-4"></i>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Controle de Fiado</h3>
                    <p class="text-gray-600 text-sm">Gestão integrada de crediário. Controle de contas a receber e limites por cliente, direto no momento da venda.</p>
                </div>
                
                <div class="glass-panel p-6">
                    <i class="ph ph-boxes text-3xl text-slate-900 mb-4"></i>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Gestão de Estoque</h3>
                    <p class="text-gray-600 text-sm">Kardex completo, inventário e controle de requisições. Saiba o que entra e sai da sua loja com precisão.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Segurança Section -->
    <section id="seguranca" class="py-16 bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 mb-4">Arquitetura de Segurança</h2>
                    <p class="text-gray-600 mb-6">
                        Construído com base em diretrizes acadêmicas de cibersegurança, o StockFlow protege os dados da sua empresa contra vazamentos e invasões.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <i class="ph-fill ph-check-circle text-emerald-600 text-xl"></i>
                            <span class="text-gray-700 text-sm"><strong>Multi-Tenant:</strong> Isolamento absoluto dos dados de cada empresa no banco.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="ph-fill ph-check-circle text-emerald-600 text-xl"></i>
                            <span class="text-gray-700 text-sm"><strong>Criptografia:</strong> Proteção de ponta a ponta e senhas em Hash seguro.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="ph-fill ph-check-circle text-emerald-600 text-xl"></i>
                            <span class="text-gray-700 text-sm"><strong>Auditoria:</strong> Logs de ação completos para rastreabilidade (Em conformidade com a LGPD).</span>
                        </li>
                    </ul>
                </div>
                <div class="glass-panel p-6 bg-slate-900 text-gray-300 font-mono text-xs md:text-sm">
                    <div class="flex gap-2 mb-4 border-b border-slate-700 pb-2">
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                        <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                    </div>
                    <p><span class="text-emerald-400">INFO</span> Inicializando middleware de isolamento...</p>
                    <p><span class="text-emerald-400">INFO</span> Tenant ID ativado e verificado.</p>
                    <p><span class="text-blue-400">QUERY</span> SELECT * FROM produtos WHERE empresa_id = $1;</p>
                    <p><span class="text-emerald-400">SUCCESS</span> Acesso concedido. Conexão segura estabelecida.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="precos" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-2xl font-bold text-slate-900 mb-2">Planos</h2>
                <p class="text-gray-600">Escolha a solução adequada para o seu comércio.</p>
            </div>
            
            <div class="flex flex-col md:flex-row justify-center gap-6 max-w-4xl mx-auto">
                <!-- Plano Mensal -->
                <div class="glass-panel p-8 flex-1">
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Plano Mensal</h3>
                    <p class="text-gray-500 text-sm mb-6 h-10">Flexibilidade sem fidelidade para o seu negócio.</p>
                    <div class="mb-6">
                        <span class="text-3xl font-bold text-slate-900">R$ 97</span>
                        <span class="text-gray-500 text-sm">/mês</span>
                    </div>
                    <ul class="space-y-3 mb-8 text-sm">
                        <li class="flex items-center gap-2 text-gray-700">
                            <i class="ph ph-check text-emerald-600"></i> Frente de Caixa (PDV) Rápido
                        </li>
                        <li class="flex items-center gap-2 text-gray-700">
                            <i class="ph ph-check text-emerald-600"></i> Controle de Estoque Completo
                        </li>
                        <li class="flex items-center gap-2 text-gray-700">
                            <i class="ph ph-check text-emerald-600"></i> Módulo de Fiado Integrado
                        </li>
                    </ul>
                    <a href="/registro?plano=mensal" class="block w-full text-center bg-white border border-gray-300 hover:bg-gray-50 text-slate-900 font-medium py-2 rounded-md transition-colors">
                        Começar Mensal
                    </a>
                </div>

                <!-- Plano Anual -->
                <div class="glass-panel p-8 flex-1 border-slate-900 relative">
                    <div class="absolute top-0 right-0 bg-slate-900 text-white text-xs font-bold px-3 py-1 rounded-bl-lg rounded-tr-md">
                        2 Meses Grátis
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Plano Anual</h3>
                    <p class="text-gray-500 text-sm mb-6 h-10">O melhor custo-benefício. Garanta estabilidade no preço.</p>
                    <div class="mb-6">
                        <span class="text-3xl font-bold text-slate-900">R$ 970</span>
                        <span class="text-gray-500 text-sm">/ano</span>
                    </div>
                    <ul class="space-y-3 mb-8 text-sm">
                        <li class="flex items-center gap-2 text-gray-700">
                            <i class="ph ph-check text-emerald-600"></i> Tudo do plano Mensal
                        </li>
                        <li class="flex items-center gap-2 text-gray-700 font-medium">
                            <i class="ph ph-check text-emerald-600"></i> Economia de quase R$ 200 no ano
                        </li>
                        <li class="flex items-center gap-2 text-gray-700">
                            <i class="ph ph-check text-emerald-600"></i> Suporte Prioritário
                        </li>
                    </ul>
                    <a href="/registro?plano=anual" class="block w-full text-center bg-slate-900 hover:bg-slate-800 text-white font-medium py-2 rounded-md transition-colors">
                        Começar Anual
                    </a>
                </div>
            </div>
            
            <div class="text-center mt-10">
                <p class="text-sm text-gray-500">
                    * Precisa de ajuda com cadastro de produtos e treinamento? <br class="md:hidden" /> Oferecemos um pacote VIP de Implantação por apenas <strong>R$ 497</strong> (Taxa Única).
                </p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contato" class="bg-white border-t border-gray-200 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2">
                <i class="ph-fill ph-package text-slate-900 text-xl"></i>
                <span class="font-bold text-slate-900">StockFlow</span>
            </div>
            <p class="text-xs text-gray-500">
                &copy; <?= date('Y') ?> StockFlow SaaS. Todos os direitos reservados.
            </p>
        </div>
    </footer>

</body>
</html>
