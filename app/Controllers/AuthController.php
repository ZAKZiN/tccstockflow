<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\AuditHelper;
use PDO;

class AuthController extends Controller {
    
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 300; // 5 minutos

    public function index() {
        if (isset($_SESSION['usuario_id'])) {
            $destino = \getHomeRouteByRole($_SESSION['usuario_nivel'] ?? '');
            if ($destino !== '/') {
                $this->redirect($destino);
            }
        }
        $data = [];
        if (isset($_GET['error'])) {
            $data['error'] = $_GET['error'];
        }
        $this->view('auth/login', $data);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/');
        }

        // Cibersegurança: Validação de Honeypot (Anti-Bot - Item 13)
        if (!empty($_POST['website'])) {
            $this->redirect('/?error=' . urlencode('Credenciais inválidas'));
        }

        $login = trim($_POST['login'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $db = Database::getConnection();

        // Limpa tentativas antigas (> 5 minutos)
        $janelaInicio = time() - self::LOCKOUT_SECONDS;
        $db->prepare("DELETE FROM login_attempts WHERE tentado_em < ?")->execute([$janelaInicio]);

        // Cibersegurança: Anti-Brute Force Persistente por IP (previne força bruta e password spraying)
        $stmtCheck = $db->prepare("SELECT COUNT(*) as total, MIN(tentado_em) as primeira FROM login_attempts WHERE ip_address = ?");
        $stmtCheck->execute([$ip]);
        $attemptInfo = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($attemptInfo && (int)$attemptInfo['total'] >= self::MAX_ATTEMPTS) {
            $restante = max(1, (int)ceil(((int)$attemptInfo['primeira'] + self::LOCKOUT_SECONDS - time()) / 60));
            $this->redirect('/?error=' . urlencode("Muitas tentativas falhas. Tente novamente em {$restante} minuto(s)."));
        }

        $stmt = $db->prepare("
            SELECT u.*, s.nome_setor 
            FROM usuarios u
            LEFT JOIN setores s ON u.id_setor = s.id_setor
            WHERE u.login = ?
        ");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($senha, $user['senha'])) {
            // Limpa tentativas falhas deste IP/usuário
            $db->prepare("DELETE FROM login_attempts WHERE ip_address = ?")->execute([$ip]);

            // Cibersegurança: Regenera ID da sessão para prevenir Session Fixation
            session_regenerate_id(true);

            $_SESSION['usuario_id'] = (int)$user['id_usuario'];
            $_SESSION['usuario_nome'] = $user['nome'];
            $_SESSION['usuario_nivel'] = $user['nivel_acesso'];
            $_SESSION['usuario_id_setor'] = $user['id_setor'] ? (int)$user['id_setor'] : null;
            $_SESSION['usuario_setor'] = $user['nome_setor'] ?? '';
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            AuditHelper::log('usuarios', (int)$user['id_usuario'], 'LOGIN', "Login efetuado com cargo {$user['nivel_acesso']} (IP: {$ip})");

            $destino = \getHomeRouteByRole($user['nivel_acesso']);
            $this->redirect($destino);
        }

        // Registra tentativa falha
        $stmtFail = $db->prepare("INSERT INTO login_attempts (ip_address, login, tentado_em) VALUES (?, ?, ?)");
        $stmtFail->execute([$ip, mb_substr($login, 0, 60), time()]);

        $novasTentativas = ((int)($attemptInfo['total'] ?? 0)) + 1;
        if ($novasTentativas >= self::MAX_ATTEMPTS) {
            AuditHelper::log('usuarios', 0, 'LOGIN_BLOQUEADO', "IP {$ip} bloqueado por excesso de tentativas para o login '{$login}'");
            $this->redirect('/?error=' . urlencode('Muitas tentativas falhas. Acesso bloqueado temporariamente por 5 minutos.'));
        }

        $this->redirect('/?error=' . urlencode('Credenciais inválidas'));
    }

    public function logout() {
        if (isset($_SESSION['usuario_id'])) {
            AuditHelper::log('usuarios', (int)$_SESSION['usuario_id'], 'LOGOUT', 'Sessão encerrada pelo usuário');
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'], $params['httponly']);
        }
        session_destroy();
        $this->redirect('/');
    }
}
