<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockFlow | O Sistema de PDV e Estoque que blinda o seu negócio</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased overflow-x-hidden">

    <!-- Navbar -->
    <nav class="fixed w-full bg-white/90 backdrop-blur-md z-50 border-b border-gray-100 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center gap-2">
                    <i class="ph-fill ph-package text-indigo-600 text-3xl"></i>
                    <span class="font-extrabold text-2xl tracking-tight text-indigo-900">StockFlow</span>
                </div>
                
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#funcionalidades" class="text-gray-600 hover:text-indigo-600 font-medium transition-colors">Funcionalidades</a>
                    <a href="#seguranca" class="text-gray-600 hover:text-indigo-600 font-medium transition-colors">Segurança</a>
                    <a href="#precos" class="text-gray-600 hover:text-indigo-600 font-medium transition-colors">Preços</a>
                    <a href="#contato" class="text-gray-600 hover:text-indigo-600 font-medium transition-colors">Contato</a>
                </div>

                <div class="flex items-center space-x-4">
                    <a href="/login" class="text-indigo-600 font-semibold hover:text-indigo-800 transition-colors hidden sm:block">Entrar</a>
                    <a href="/registro" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-6 rounded-lg transition-colors shadow-md hover:shadow-lg flex items-center gap-2">
                        Criar Conta <i class="ph ph-arrow-right font-bold"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 lg:pt-48 lg:pb-32 bg-gradient-to-b from-indigo-50 to-white overflow-hidden relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-4xl mx-auto">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-gray-900 tracking-tight mb-8 leading-tight">
                    O sistema de PDV e Estoque que <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-blue-500">blinda o seu negócio.</span>
                </h1>
                <p class="text-lg md:text-xl text-gray-600 mb-10 max-w-3xl mx-auto leading-relaxed">
                    Abandone o caderno e os sistemas lentos. Controle suas vendas, gerencie o fiado e nunca mais perca dinheiro no estoque. Rápido, seguro e nas nuvens.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="/registro" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-lg py-4 px-8 rounded-xl transition-all shadow-lg hover:shadow-indigo-500/30 transform hover:-translate-y-1">
                        Testar Grátis por 7 dias
                    </a>
                    <a href="#funcionalidades" class="w-full sm:w-auto bg-white hover:bg-gray-50 text-gray-800 font-semibold text-lg py-4 px-8 rounded-xl border border-gray-200 transition-all shadow-sm hover:shadow-md flex items-center justify-center gap-2">
                        <i class="ph ph-play-circle text-2xl text-indigo-600"></i> Ver como funciona
                    </a>
                </div>
            </div>
            
            <!-- Dashboard Mockup Placeholder -->
            <div class="mt-20 mx-auto max-w-5xl relative">
                <div class="absolute inset-0 bg-gradient-to-t from-white to-transparent z-10 h-32 bottom-0 top-auto"></div>
                <div class="bg-white rounded-2xl shadow-2xl shadow-indigo-900/10 border border-gray-100 p-2 overflow-hidden transform hover:scale-[1.01] transition-transform duration-500">
                    <div class="bg-gray-100 rounded-t-xl h-8 flex items-center px-4 gap-2">
                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                        <div class="w-3 h-3 rounded-full bg-green-400"></div>
                    </div>
                    <!-- Fake Dashboard UI -->
                    <div class="bg-gray-50 h-[400px] md:h-[500px] w-full p-6 flex flex-col gap-6">
                        <div class="flex justify-between items-center">
                            <div class="w-48 h-8 bg-gray-200 rounded-md animate-pulse"></div>
                            <div class="w-32 h-10 bg-indigo-200 rounded-md animate-pulse"></div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 h-24 flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center"><i class="ph ph-trend-up text-blue-500 text-xl"></i></div>
                                <div class="flex-1 space-y-2"><div class="w-20 h-3 bg-gray-200 rounded"></div><div class="w-32 h-5 bg-gray-300 rounded"></div></div>
                            </div>
                            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 h-24 flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center"><i class="ph ph-currency-dollar text-green-500 text-xl"></i></div>
                                <div class="flex-1 space-y-2"><div class="w-20 h-3 bg-gray-200 rounded"></div><div class="w-24 h-5 bg-gray-300 rounded"></div></div>
                            </div>
                            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 h-24 flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center"><i class="ph ph-package text-purple-500 text-xl"></i></div>
                                <div class="flex-1 space-y-2"><div class="w-20 h-3 bg-gray-200 rounded"></div><div class="w-16 h-5 bg-gray-300 rounded"></div></div>
                            </div>
                            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 h-24 flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center"><i class="ph ph-users text-orange-500 text-xl"></i></div>
                                <div class="flex-1 space-y-2"><div class="w-20 h-3 bg-gray-200 rounded"></div><div class="w-12 h-5 bg-gray-300 rounded"></div></div>
                            </div>
                        </div>
                        <div class="flex-1 bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-center">
                            <div class="text-gray-300 flex flex-col items-center gap-2">
                                <i class="ph ph-chart-line-up text-6xl"></i>
                                <span class="font-medium">Gráfico de Faturamento</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Blobs decorativos de fundo -->
            <div class="absolute top-1/4 left-0 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob -z-10"></div>
            <div class="absolute top-1/3 right-0 w-72 h-72 bg-indigo-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000 -z-10"></div>
            <div class="absolute -bottom-8 left-1/2 w-72 h-72 bg-blue-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000 -z-10"></div>
        </div>
    </section>

    <!-- Funcionalidades Grid -->
    <section id="funcionalidades" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="text-indigo-600 font-semibold tracking-wider uppercase text-sm">Operação Profissional</span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mt-2 mb-4">Tudo o que você precisa, em um só lugar.</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Desenvolvido para lojas físicas que precisam de agilidade no balcão e segurança na gestão administrativa.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <!-- Card 1 -->
                <div class="bg-gray-50 rounded-2xl p-8 border border-gray-100 hover:shadow-xl transition-shadow duration-300 group">
                    <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-indigo-600 transition-colors">
                        <i class="ph ph-barcode text-3xl text-indigo-600 group-hover:text-white transition-colors"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Frente de Caixa (PDV) Relâmpago</h3>
                    <p class="text-gray-600 leading-relaxed">Venda em segundos com leitor de código de barras ou teclado. Feito para a correria do balcão, garantindo que o seu cliente não espere na fila.</p>
                </div>
                
                <!-- Card 2 -->
                <div class="bg-gray-50 rounded-2xl p-8 border border-gray-100 hover:shadow-xl transition-shadow duration-300 group">
                    <div class="w-14 h-14 bg-emerald-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-emerald-600 transition-colors">
                        <i class="ph ph-handshake text-3xl text-emerald-600 group-hover:text-white transition-colors"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Controle de 'Fiado' (Crediário)</h3>
                    <p class="text-gray-600 leading-relaxed">Chega de esquecer quem te deve. Gestão inteligente de contas a receber integrada diretamente ao caixa, com limites por cliente e histórico.</p>
                </div>
                
                <!-- Card 3 -->
                <div class="bg-gray-50 rounded-2xl p-8 border border-gray-100 hover:shadow-xl transition-shadow duration-300 group">
                    <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-blue-600 transition-colors">
                        <i class="ph ph-boxes text-3xl text-blue-600 group-hover:text-white transition-colors"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Estoque e Kardex à Prova de Falhas</h3>
                    <p class="text-gray-600 leading-relaxed">Saiba exatamente o que entra e sai da sua loja. Alertas automáticos de estoque mínimo, controle de lotes, validades e inventário com curva ABC.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Segurança Section -->
    <section id="seguranca" class="py-24 bg-gray-900 text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-sm font-semibold mb-6 border border-indigo-500/30">
                        <i class="ph-fill ph-shield-check"></i> Padrão Bancário
                    </div>
                    <h2 class="text-3xl md:text-4xl font-bold mb-6 leading-tight">O Diferencial: Segurança de Dados nível TCC Acadêmico.</h2>
                    <p class="text-gray-400 text-lg mb-8 leading-relaxed">
                        Construímos o StockFlow não apenas para ser bonito e rápido, mas para ser uma <strong>fortaleza</strong>.
                        Com proteção Multi-Tenant isolada por Empresa, criptografia robusta (AES-256) em banco PostgreSQL e total aderência às diretrizes da LGPD, os dados da sua loja e dos seus clientes são invisíveis para invasores.
                    </p>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <i class="ph-fill ph-check-circle text-emerald-400 text-xl mt-1"></i>
                            <span class="text-gray-300">Auditoria completa de tudo que ocorre no sistema (Logs de Ação).</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="ph-fill ph-check-circle text-emerald-400 text-xl mt-1"></i>
                            <span class="text-gray-300">Isolamento rigoroso de informações: seus dados jamais vazam para concorrentes.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="ph-fill ph-check-circle text-emerald-400 text-xl mt-1"></i>
                            <span class="text-gray-300">Proteção ativa contra ataques DDoS, Injeções de SQL e CSRF.</span>
                        </li>
                    </ul>
                </div>
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-blue-600 transform skew-y-3 rounded-3xl opacity-20 filter blur-xl"></div>
                    <div class="bg-gray-800 border border-gray-700 rounded-2xl p-8 relative shadow-2xl">
                        <div class="flex justify-center mb-6">
                            <div class="w-24 h-24 bg-gray-700 rounded-full flex items-center justify-center border-4 border-indigo-500/30">
                                <i class="ph-fill ph-lock-key text-5xl text-indigo-400"></i>
                            </div>
                        </div>
                        <div class="space-y-4 font-mono text-sm">
                            <div class="bg-gray-900 p-3 rounded border border-gray-700 text-emerald-400">
                                [AUTH] ✓ Login successful (JWT validation passed)
                            </div>
                            <div class="bg-gray-900 p-3 rounded border border-gray-700 text-blue-400">
                                [QUERY] SELECT * FROM produtos WHERE id_empresa = ***
                            </div>
                            <div class="bg-gray-900 p-3 rounded border border-gray-700 text-green-400">
                                [ENCRYPT] AES-256 payload secured.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="precos" class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Planos Simples e Transparentes</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Sem taxas surpresa. Cancele quando quiser.</p>
            </div>
            
            <div class="flex flex-col md:flex-row justify-center gap-8 max-w-5xl mx-auto">
                <!-- Plano Essencial -->
                <div class="bg-white rounded-3xl p-8 border border-gray-200 shadow-sm flex-1 max-w-sm w-full mx-auto">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Essencial</h3>
                    <p class="text-gray-500 mb-6 h-12">Para pequenos comércios que estão começando a se organizar.</p>
                    <div class="mb-6">
                        <span class="text-4xl font-extrabold text-gray-900">R$ 67</span>
                        <span class="text-gray-500 font-medium">/mês</span>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center gap-3 text-gray-600">
                            <i class="ph-fill ph-check-circle text-indigo-600 text-xl"></i> PDV Rápido
                        </li>
                        <li class="flex items-center gap-3 text-gray-600">
                            <i class="ph-fill ph-check-circle text-indigo-600 text-xl"></i> Controle de Estoque
                        </li>
                        <li class="flex items-center gap-3 text-gray-600">
                            <i class="ph-fill ph-check-circle text-indigo-600 text-xl"></i> 1 Usuário (Caixa)
                        </li>
                        <li class="flex items-center gap-3 text-gray-400">
                            <i class="ph ph-x-circle text-gray-300 text-xl"></i> <span class="line-through">Módulo de Fiado</span>
                        </li>
                    </ul>
                    <a href="/registro" class="block w-full text-center bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold py-3 rounded-xl transition-colors">
                        Começar Essencial
                    </a>
                </div>

                <!-- Plano Profissional (Destaque) -->
                <div class="bg-indigo-900 rounded-3xl p-8 border border-indigo-700 shadow-2xl flex-1 max-w-sm w-full mx-auto relative transform md:-translate-y-4">
                    <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                        <span class="bg-gradient-to-r from-emerald-400 to-emerald-500 text-white text-xs font-bold uppercase tracking-wider py-1 px-4 rounded-full shadow-sm">
                            Mais Escolhido
                        </span>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">Profissional</h3>
                    <p class="text-indigo-200 mb-6 h-12">Para lojas que precisam de controle total e sem limitações.</p>
                    <div class="mb-6">
                        <span class="text-4xl font-extrabold text-white">R$ 127</span>
                        <span class="text-indigo-300 font-medium">/mês</span>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center gap-3 text-indigo-100">
                            <i class="ph-fill ph-check-circle text-emerald-400 text-xl"></i> Tudo do plano Essencial
                        </li>
                        <li class="flex items-center gap-3 text-white font-medium">
                            <i class="ph-fill ph-check-circle text-emerald-400 text-xl"></i> Módulo Completo de Fiado
                        </li>
                        <li class="flex items-center gap-3 text-white font-medium">
                            <i class="ph-fill ph-check-circle text-emerald-400 text-xl"></i> Usuários Ilimitados
                        </li>
                        <li class="flex items-center gap-3 text-indigo-100">
                            <i class="ph-fill ph-check-circle text-emerald-400 text-xl"></i> Suporte Prioritário (WhatsApp)
                        </li>
                    </ul>
                    <a href="/registro" class="block w-full text-center bg-indigo-500 hover:bg-indigo-400 text-white font-bold py-3 rounded-xl transition-colors shadow-lg shadow-indigo-600/30">
                        Começar Profissional
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contato" class="bg-white border-t border-gray-100 pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center md:items-start gap-8 mb-12">
                <div class="flex items-center gap-2">
                    <i class="ph-fill ph-package text-indigo-600 text-3xl"></i>
                    <span class="font-bold text-xl text-gray-900">StockFlow SaaS</span>
                </div>
                <div class="flex gap-8">
                    <a href="#" class="text-gray-500 hover:text-indigo-600"><i class="ph-fill ph-instagram-logo text-2xl"></i></a>
                    <a href="#" class="text-gray-500 hover:text-indigo-600"><i class="ph-fill ph-facebook-logo text-2xl"></i></a>
                    <a href="#" class="text-gray-500 hover:text-indigo-600"><i class="ph-fill ph-whatsapp-logo text-2xl"></i></a>
                </div>
            </div>
            <div class="border-t border-gray-100 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-gray-500">
                    &copy; <?= date('Y') ?> StockFlow SaaS. Todos os direitos reservados.
                </p>
                <div class="flex gap-6 text-sm text-gray-500">
                    <a href="#" class="hover:text-indigo-600">Termos de Uso</a>
                    <a href="#" class="hover:text-indigo-600">Política de Privacidade</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
