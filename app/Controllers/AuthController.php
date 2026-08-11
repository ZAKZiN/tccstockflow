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
            // Cibersegurança: Rate Limiting (Proteção contra Brute Force)
            if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 5) {
                if (time() - $_SESSION['last_attempt_time'] < 60) {
                    $this->view('auth/login', ['error' => 'Muitas tentativas falhas. Por segurança, aguarde 1 minuto.']);
                    return;
                } else {
                    $_SESSION['login_attempts'] = 0;
                }
            }

            $login = $_POST['login'] ?? '';
            $senha = $_POST['senha'] ?? '';
            
            $user = Usuario::authenticate($login, $senha);
            
            if ($user) {
                // Prevenção contra Session Fixation
                session_regenerate_id(true);
                $_SESSION['login_attempts'] = 0; // Reset
                
                $_SESSION['usuario_id'] = $user['id_usuario'];
                $_SESSION['usuario_nome'] = $user['nome'];
                $_SESSION['usuario_nivel'] = $user['nivel_acesso'];
                $_SESSION['usuario_setor'] = $user['nome_setor'];
                $_SESSION['usuario_id_setor'] = $user['id_setor'];
                
                $this->redirect('/dashboard');
            } else {
                $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                $_SESSION['last_attempt_time'] = time();
                $this->view('auth/login', ['error' => 'Login ou senha incorretos.']);
            }
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        $this->redirect('/');
    }
}
