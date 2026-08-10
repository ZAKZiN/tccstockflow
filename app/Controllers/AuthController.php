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
            $login = $_POST['login'] ?? '';
            $senha = $_POST['senha'] ?? '';
            
            $user = Usuario::authenticate($login, $senha);
            
            if ($user) {
                // Regenerar ID da sessão para prevenir Session Fixation
                session_regenerate_id(true);
                
                $_SESSION['usuario_id'] = $user['id_usuario'];
                $_SESSION['usuario_nome'] = $user['nome'];
                $_SESSION['usuario_nivel'] = $user['nivel_acesso'];
                $_SESSION['usuario_setor'] = $user['nome_setor'];
                $_SESSION['usuario_id_setor'] = $user['id_setor'];
                
                $this->redirect('/dashboard');
            } else {
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
