<?php

// Fuso horário oficial (Brasília) para consistência entre PHP e SQLite
date_default_timezone_set('America/Sao_Paulo');

// Cibersegurança: Prevenir vazamento de dados de erros internos (Item 15)
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Cibersegurança e LGPD: Configuração Segura de Sessões
$isSecure = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $isSecure = true;
} elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $isSecure = true;
}

session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'secure' => $isSecure,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

// Gerar CSRF Token se não existir
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Cibersegurança: Forçar HTTPS em produção (Item 19)
if (!$isSecure && ((isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production') || getenv('RENDER'))) {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

// Cibersegurança: Cabeçalhos HTTP Seguros (Compatíveis com Phosphor Icons, Bootstrap, Chart.js, Google Fonts e html2pdf)
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header(
    "Content-Security-Policy: " .
    "default-src 'self'; " .
    "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://unpkg.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
    "style-src 'self' 'unsafe-inline' https://unpkg.com https://cdn.jsdelivr.net https://fonts.googleapis.com; " .
    "font-src 'self' data: https://unpkg.com https://cdn.jsdelivr.net https://fonts.gstatic.com; " .
    "img-src 'self' data: blob: https:; " .
    "connect-src 'self' https://unpkg.com https://cdn.jsdelivr.net;"
);

// Carrega o autoloader do Composer
require_once __DIR__ . '/../vendor/autoload.php';

use Bramus\Router\Router;
use Dotenv\Dotenv;

// Carrega variáveis de ambiente
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

/**
 * Retorna a rota inicial adequada para cada cargo (evitando loops de redirecionamento)
 */
function getHomeRouteByRole(string $nivel): string {
    switch ($nivel) {
        case 'Administrador':
        case 'Gerente':
            return '/dashboard';
        case 'Operador de Caixa':
            return '/pdv';
        case 'Estoquista':
            return '/estoque';
        case 'Coordenador':
        case 'Solicitante':
            return '/requisicoes';
        default:
            return '/';
    }
}

/**
 * Controle de Acesso Baseado em Cargos (RBAC)
 */
function checkAccess(array $allowedRoles): void {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /');
        exit;
    }
    $nivel = $_SESSION['usuario_nivel'] ?? '';
    if (!in_array($nivel, $allowedRoles, true)) {
        $destino = getHomeRouteByRole($nivel);
        $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        if ($destino === '/' || strpos($currentPath, $destino) === 0) {
            session_unset();
            session_destroy();
            header('Location: /?error=' . urlencode('Sessão inválida ou cargo sem permissão atribuída.'));
            exit;
        }
        $msg = urlencode("Acesso negado para o cargo {$nivel}.");
        header('Location: ' . $destino . '?error=' . $msg);
        exit;
    }
}

$router = new Router();

// Cibersegurança: Middleware Global de Validação de CSRF Token em TODAS as requisições POST
$router->before('POST', '/.*', function() {
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $requestToken = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');

    if (empty($sessionToken) || !is_string($requestToken) || !hash_equals($sessionToken, $requestToken)) {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($accept, 'application/json') !== false || strpos($contentType, 'application/json') !== false) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Sessão expirada ou token CSRF inválido. Recarregue a página.']);
            exit;
        }
        http_response_code(403);
        die("<h1>403 Acesso Negado</h1><p>Token CSRF inválido ou ausente. Volte, atualize a página e tente novamente.</p>");
    }
});

// --- Rotas de Autenticação ---
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\RequisicaoController;
use App\Controllers\EstoqueController;
use App\Controllers\FornecedorController;
use App\Controllers\CompraController;
use App\Controllers\NotificacaoController;
use App\Controllers\UsuarioController;
use App\Controllers\CaixaController;
use App\Controllers\AuditoriaController;
use App\Controllers\VendaController;
use App\Controllers\FiadoController;
use App\Controllers\ClienteController;

$router->get('/', AuthController::class . '@index');
$router->post('/login', AuthController::class . '@login');
$router->post('/logout', AuthController::class . '@logout');
$router->get('/logout', AuthController::class . '@logout'); // Fallback seguro para links diretos

// --- Proteção RBAC por Prefixo de Rota ---
$router->before('GET|POST', '/dashboard.*', function() { checkAccess(['Administrador', 'Gerente']); });
$router->before('GET|POST', '/usuarios.*', function() { checkAccess(['Administrador']); });
$router->before('GET|POST', '/caixa.*', function() { checkAccess(['Administrador', 'Gerente', 'Operador de Caixa']); });
$router->before('GET|POST', '/estoque.*', function() { checkAccess(['Administrador', 'Gerente', 'Estoquista']); });
$router->before('GET|POST', '/fornecedores.*', function() { checkAccess(['Administrador', 'Gerente', 'Estoquista']); });
$router->before('GET|POST', '/compras.*', function() { checkAccess(['Administrador', 'Gerente', 'Estoquista']); });
$router->before('GET|POST', '/requisicoes.*', function() { checkAccess(['Administrador', 'Gerente', 'Estoquista', 'Coordenador', 'Solicitante']); });
$router->before('GET|POST', '/pdv.*', function() { checkAccess(['Administrador', 'Gerente', 'Operador de Caixa']); });
$router->before('GET|POST', '/fiado.*', function() { checkAccess(['Administrador', 'Gerente', 'Operador de Caixa']); });
$router->before('GET|POST', '/clientes.*', function() { checkAccess(['Administrador', 'Gerente', 'Operador de Caixa']); });
$router->before('GET|POST', '/auditoria.*', function() { checkAccess(['Administrador']); });
$router->before('GET|POST', '/api/notificacoes.*', function() {
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([]);
        exit;
    }
});

// Dashboard
$router->get('/dashboard', DashboardController::class . '@index');

// Caixa (Abertura/Fechamento)
$router->get('/caixa', CaixaController::class . '@index');
$router->post('/caixa/abrir', CaixaController::class . '@abrir');
$router->post('/caixa/fechar', CaixaController::class . '@fechar');
$router->post('/caixa/lancar', CaixaController::class . '@lancar');
$router->get('/caixa/relatorio/(\d+)', CaixaController::class . '@relatorio');

// Usuários (Gestão de Cargos e Setores)
$router->get('/usuarios', UsuarioController::class . '@index');
$router->post('/usuarios', UsuarioController::class . '@store');
$router->post('/usuarios/excluir', UsuarioController::class . '@destroy');

// Auditoria
$router->get('/auditoria', AuditoriaController::class . '@index');

// Clientes (Cadastro Rápido p/ PDV e Fiado)
$router->post('/clientes', ClienteController::class . '@store');

// PDV / Vendas
$router->get('/pdv', VendaController::class . '@index');
$router->post('/pdv/finalizar', VendaController::class . '@finalizar');
$router->get('/pdv/recibo/(\d+)', VendaController::class . '@recibo');

// Fiado / Contas a Receber
$router->get('/fiado', FiadoController::class . '@index');
$router->post('/fiado/pagar/(\d+)', FiadoController::class . '@pagar');

// Requisições
$router->get('/requisicoes', RequisicaoController::class . '@index');
$router->get('/requisicoes/nova', RequisicaoController::class . '@create');
$router->post('/requisicoes/nova', RequisicaoController::class . '@create');
$router->post('/requisicoes/aprovar/(\d+)', RequisicaoController::class . '@approve');
$router->post('/requisicoes/recusar/(\d+)', RequisicaoController::class . '@reject');
$router->post('/requisicoes/despachar/(\d+)', RequisicaoController::class . '@despachar');

// Estoque
$router->get('/estoque', EstoqueController::class . '@index');
$router->get('/estoque/curva-abc', EstoqueController::class . '@curvaAbc');
$router->post('/estoque/novo', EstoqueController::class . '@store');
$router->post('/estoque/editar', EstoqueController::class . '@update');
$router->get('/estoque/historico/(\d+)', EstoqueController::class . '@historico');
$router->post('/estoque/ajustar', EstoqueController::class . '@ajustar');

// Fornecedores
$router->get('/fornecedores', FornecedorController::class . '@index');
$router->post('/fornecedores', FornecedorController::class . '@index');
$router->post('/fornecedores/editar', FornecedorController::class . '@update');
$router->post('/fornecedores/excluir', FornecedorController::class . '@destroy');

// Compras
$router->get('/compras', CompraController::class . '@index');
$router->post('/compras', CompraController::class . '@index');

// API Notificações
$router->get('/api/notificacoes', NotificacaoController::class . '@getUnread');
$router->post('/api/notificacoes/ler/(\d+)', NotificacaoController::class . '@markAsRead');

$router->run();
