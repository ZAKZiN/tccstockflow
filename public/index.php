<?php

// Front Controller - Ponto único de entrada da aplicação
session_start();

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

use App\Controllers\DashboardController;
use App\Controllers\RequisicaoController;
use App\Controllers\EstoqueController;
use App\Controllers\FornecedorController;
use App\Controllers\CompraController;

// Dashboard
$router->get('/dashboard', DashboardController::class . '@index');

// Requisições
$router->get('/requisicoes', RequisicaoController::class . '@index');
$router->get('/requisicoes/nova', RequisicaoController::class . '@create');
$router->post('/requisicoes/nova', RequisicaoController::class . '@create');
$router->get('/requisicoes/aprovar/(\d+)', RequisicaoController::class . '@approve');
$router->get('/requisicoes/recusar/(\d+)', RequisicaoController::class . '@reject');

// Estoque
$router->get('/estoque', EstoqueController::class . '@index');

// Fornecedores
$router->get('/fornecedores', FornecedorController::class . '@index');
$router->post('/fornecedores', FornecedorController::class . '@index');

// Compras
$router->get('/compras', CompraController::class . '@index');
$router->post('/compras', CompraController::class . '@index');

// Dispara as rotas
$router->run();
