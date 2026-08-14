<?php

// Cibersegurança e LGPD: Configuração Segura de Sessões
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // Apenas HTTPS se disponível
    'httponly' => true, // Previne roubo de sessão via XSS
    'samesite' => 'Strict' // Previne CSRF cross-origin
]);
session_start();

// Gerar CSRF Token se não existir
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Cibersegurança: Cabeçalhos HTTP Seguros
header("X-Frame-Options: SAMEORIGIN"); // Previne Clickjacking
header("X-XSS-Protection: 1; mode=block"); // Proteção anti-XSS do navegador
header("X-Content-Type-Options: nosniff"); // Impede MIME-sniffing
header("Referrer-Policy: strict-origin-when-cross-origin");

// Carrega o autoloader do Composer
require_once __DIR__ . '/../vendor/autoload.php';

use Bramus\Router\Router;
use Dotenv\Dotenv;

// Carrega variáveis de ambiente
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Instancia o Router
$router = new Router();

// Definindo a base path (ajuste caso rode em subpasta no XAMPP, ex: /tcc v3/Site/public)
// Como o XAMPP acessa pastas dinamicamente, o Bramus Router geralmente pega a pasta atual
// Se necessário, descomente a linha abaixo e defina o subdiretório:
// $router->setBasePath('/TCC/tcc v3/Site/public');

// --- Rotas da Aplicação ---
use App\Controllers\AuthController;

// Rotas de Autenticação
$router->get('/', AuthController::class . '@index');
$router->post('/login', AuthController::class . '@login');
$router->get('/logout', AuthController::class . '@logout');

// --- Configuração de Permissões (RBAC) ---
function checkAccess($allowedRoles) {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /');
        exit;
    }
    if (!in_array($_SESSION['usuario_nivel'], $allowedRoles)) {
        $msg = urlencode("Acesso Negado. Você não tem permissão para acessar esta área.");
        header('Location: /pdv?error=' . $msg);
        exit;
    }
}

// Cibersegurança: Middleware de Validação de CSRF Token (Todas as requisições POST, exceto APIs específicas se houver)
$router->before('POST', '/.*', function() {
    // Ignorar webhook/api do pdv e login temporariamente ou forçar nelas tbm
    $uri = $_SERVER['REQUEST_URI'];
    if (strpos($uri, '/pdv/finalizar') !== false) {
        // PDV finalizar usa JSON POST, vamos validar no controller ou ignorar por enquanto
        return; 
    }
    
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        die("<h1>403 Acesso Negado</h1><p>Token CSRF inválido ou ausente. Isso pode ser uma tentativa de falsificação de solicitação. Volte e tente novamente.</p>");
    }
});

// Proteger Rotas por Cargo
$router->before('GET|POST', '/dashboard.*', function() { checkAccess(['Administrador', 'Gerente']); });
$router->before('GET|POST', '/usuarios.*', function() { checkAccess(['Administrador']); });
$router->before('GET|POST', '/caixa.*', function() { checkAccess(['Administrador', 'Gerente', 'Operador de Caixa']); });
$router->before('GET|POST', '/estoque.*', function() { checkAccess(['Administrador', 'Gerente', 'Estoquista']); });
$router->before('GET|POST', '/fornecedores.*', function() { checkAccess(['Administrador', 'Gerente', 'Estoquista']); });
$router->before('GET|POST', '/compras.*', function() { checkAccess(['Administrador', 'Gerente', 'Estoquista']); });
$router->before('GET|POST', '/requisicoes.*', function() { checkAccess(['Administrador', 'Gerente', 'Estoquista']); });
$router->before('GET|POST', '/pdv.*', function() { checkAccess(['Administrador', 'Gerente', 'Operador de Caixa']); });
$router->before('GET|POST', '/fiado.*', function() { checkAccess(['Administrador', 'Gerente', 'Operador de Caixa']); });

use App\Controllers\DashboardController;
use App\Controllers\RequisicaoController;
use App\Controllers\EstoqueController;
use App\Controllers\FornecedorController;
use App\Controllers\CompraController;
use App\Controllers\NotificacaoController;
use App\Controllers\UsuarioController;
use App\Controllers\CaixaController;

// Dashboard
$router->get('/dashboard', DashboardController::class . '@index');

// Caixa (Abertura/Fechamento)
$router->get('/caixa', CaixaController::class . '@index');
$router->post('/caixa/abrir', CaixaController::class . '@abrir');
$router->post('/caixa/fechar', CaixaController::class . '@fechar');
$router->post('/caixa/lancar', CaixaController::class . '@lancar');
$router->get('/caixa/relatorio/(\d+)', CaixaController::class . '@relatorio');

// Usuários (Gestão de Cargos)
$router->get('/usuarios', UsuarioController::class . '@index');
$router->post('/usuarios', UsuarioController::class . '@store');
$router->post('/usuarios/excluir', UsuarioController::class . '@destroy');

use App\Controllers\VendaController;

// PDV / Vendas
$router->get('/pdv', VendaController::class . '@index');
$router->post('/pdv/finalizar', VendaController::class . '@finalizar');
$router->get('/pdv/recibo/(\d+)', VendaController::class . '@recibo');

use App\Controllers\FiadoController;

// Fiado / Contas a Receber
$router->get('/fiado', FiadoController::class . '@index');
$router->get('/fiado/pagar/(\d+)', FiadoController::class . '@pagar');

// Requisições
$router->get('/requisicoes', RequisicaoController::class . '@index');
$router->get('/requisicoes/nova', RequisicaoController::class . '@create');
$router->post('/requisicoes/nova', RequisicaoController::class . '@create');
$router->get('/requisicoes/aprovar/(\d+)', RequisicaoController::class . '@approve');
$router->get('/requisicoes/recusar/(\d+)', RequisicaoController::class . '@reject');

// Estoque
$router->get('/estoque', EstoqueController::class . '@index');
$router->post('/estoque/novo', EstoqueController::class . '@store');
$router->get('/estoque/historico/(\d+)', EstoqueController::class . '@historico');
$router->post('/estoque/ajustar', EstoqueController::class . '@ajustar');

// Fornecedores
$router->get('/fornecedores', FornecedorController::class . '@index');
$router->post('/fornecedores', FornecedorController::class . '@index');

// Compras
$router->get('/compras', CompraController::class . '@index');
$router->post('/compras', CompraController::class . '@index');

// API Notificações
$router->get('/api/notificacoes', NotificacaoController::class . '@getUnread');
$router->post('/api/notificacoes/ler/(\d+)', NotificacaoController::class . '@markAsRead');

// Dispara as rotas
$router->run();
