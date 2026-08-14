<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;

class AuthController extends Controller {
    
    public function index() {
        if (isset($_SESSION['usuario_id'])) {
            $this->redirect('/dashboard');
        }
        $this->view('auth/login');
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Cibersegurança: Anti-Brute Force (Rate Limiting)
            if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
                $wait_time = ceil(($_SESSION['lockout_time'] - time()) / 60);
                header('Location: /?error=' . urlencode("Muitas tentativas falhas. Tente novamente em {$wait_time} minuto(s)."));
                exit;
            }

            $login = $_POST['login'] ?? '';
            $senha = $_POST['senha'] ?? '';
            
            $db = \App\Core\Database::getConnection();
            
            $stmt = $db->prepare("SELECT * FROM usuarios WHERE login = ?");
            $stmt->execute([$login]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($user && password_verify($senha, $user['senha'])) {
                // Reset attempts on success
                unset($_SESSION['login_attempts']);
                unset($_SESSION['lockout_time']);

                $_SESSION['usuario_id'] = $user['id_usuario'];
                $_SESSION['usuario_nome'] = $user['nome'];
                $_SESSION['usuario_nivel'] = $user['nivel_acesso'];
                
                // Generate initial CSRF token if not exists
                if (empty($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
                
                header('Location: /dashboard');
                exit;
            } else {
                // Increment failed attempts
                $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                
                if ($_SESSION['login_attempts'] >= 5) {
                    $_SESSION['lockout_time'] = time() + (5 * 60); // 5 minutes lockout
                    header('Location: /?error=' . urlencode('Muitas tentativas falhas. Conta bloqueada por 5 minutos por segurança.'));
                    exit;
                }

                header('Location: /?error=Credenciais inválidas');
                exit;
            }
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        $this->redirect('/');
    }
}
