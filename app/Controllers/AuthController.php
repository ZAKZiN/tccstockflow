<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;
// use League\OAuth2\Client\Provider\Google;

class AuthController extends Controller {
    
    public function index() {
        if (isset($_SESSION['usuario_id'])) {
            $this->redirect('/dashboard');
        }
        $data = [];
        if (isset($_GET['error'])) {
            $data['error'] = $_GET['error'];
        }
        $this->view('auth/login', $data);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Cibersegurança: Anti-Brute Force (Rate Limiting)
            if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
                $wait_time = ceil(($_SESSION['lockout_time'] - time()) / 60);
                header('Location: /?error=' . urlencode("Muitas tentativas falhas. Tente novamente em {$wait_time} minuto(s)."));
                exit;
            }

            // Cibersegurança: Validação de Honeypot (Item 13)
            if (!empty($_POST['website'])) {
                // É um robô! Fingimos que falhou ou simplesmente ignoramos
                header('Location: /?error=Credenciais inválidas');
                exit;
            }

            $codigo_acesso = $_POST['codigo_acesso'] ?? '';
            $login = $_POST['login'] ?? '';
            $senha = $_POST['senha'] ?? '';
            
            $user = Usuario::authenticate($codigo_acesso, $login, $senha);
            
            if ($user) {
                // Reset attempts on success
                unset($_SESSION['login_attempts']);
                unset($_SESSION['lockout_time']);

                $_SESSION['usuario_id'] = $user['id_usuario'];
                $_SESSION['empresa_id'] = $user['id_empresa'];
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

    public function register() {
        if (isset($_SESSION['usuario_id'])) {
            $this->redirect('/dashboard');
        }
        $data = [];
        if (isset($_GET['error'])) {
            $data['error'] = $_GET['error'];
        }
        $this->view('auth/register', $data);
    }
    
    public function bloqueado() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/');
        }
        $this->view('auth/bloqueado');
    }

    public function storeRegister() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $razao_social = $_POST['razao_social'] ?? '';
            $nome = $_POST['nome'] ?? '';
            $login = $_POST['login'] ?? '';
            $senha = $_POST['senha'] ?? '';

            if (empty($razao_social) || empty($nome) || empty($login) || empty($senha)) {
                header('Location: /registro?error=' . urlencode('Preencha todos os campos'));
                exit;
            }

            $db = \App\Core\Database::getConnection();
            try {
                $db->beginTransaction();

                $codigo_acesso = strtoupper(substr(md5(uniqid()), 0, 6)); // E.g. A3F8E2

                // 1. Cria a empresa
                $stmt = $db->prepare("INSERT INTO empresas (razao_social, codigo_acesso) VALUES (?, ?) RETURNING id_empresa");
                $stmt->execute([$razao_social, $codigo_acesso]);
                $empresa = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if (!$empresa) {
                    throw new \Exception("Falha ao criar empresa");
                }
                
                $id_empresa = $empresa['id_empresa'];

                // 2. Cria o usuário administrador vinculado
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $stmtUser = $db->prepare("INSERT INTO usuarios (id_empresa, nome, login, senha, nivel_acesso) VALUES (?, ?, ?, ?, 'Administrador')");
                $stmtUser->execute([$id_empresa, $nome, $login, $senhaHash]);

                $db->commit();
                header('Location: /?msg=' . urlencode("Conta criada com sucesso! O Código da sua Empresa é: {$codigo_acesso}. Guarde-o para o Login!"));
                exit;
            } catch (\Exception $e) {
                $db->rollBack();
                header('Location: /registro?error=' . urlencode('Erro ao criar conta: ' . $e->getMessage()));
                exit;
            }
        }
    }

    /*
    private function getGoogleProvider() {
        return new Google([
            'clientId'     => $_ENV['GOOGLE_CLIENT_ID'] ?? '',
            'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? '',
            'redirectUri'  => $_ENV['GOOGLE_REDIRECT_URI'] ?? '',
        ]);
    }

    public function googleLogin() {
        $provider = $this->getGoogleProvider();
        $authUrl = $provider->getAuthorizationUrl();
        $_SESSION['oauth2state'] = $provider->getState();
        header('Location: ' . $authUrl);
        exit;
    }

    public function googleCallback() {
        if (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {
            if (isset($_SESSION['oauth2state'])) {
                unset($_SESSION['oauth2state']);
            }
            header('Location: /?error=' . urlencode('Estado inválido.'));
            exit;
        }

        $provider = $this->getGoogleProvider();

        try {
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);

            $ownerDetails = $provider->getResourceOwner($token);
            $googleId = $ownerDetails->getId();
            $nome = $ownerDetails->getName();
            $email = $ownerDetails->getEmail(); // opcional uso como login provisório se quiser

            $db = \App\Core\Database::getConnection();

            // Verifica se o usuário já existe pelo google_id
            $stmt = $db->prepare("SELECT * FROM usuarios WHERE google_id = ?");
            $stmt->execute([$googleId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                // Tenta verificar pelo login (email) para vincular, senão cria um novo
                $stmt = $db->prepare("SELECT * FROM usuarios WHERE login = ?");
                $stmt->execute([$email]);
                $userByEmail = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($userByEmail) {
                    // Vincula
                    $stmt = $db->prepare("UPDATE usuarios SET google_id = ? WHERE id_usuario = ?");
                    $stmt->execute([$googleId, $userByEmail['id_usuario']]);
                    $user = $userByEmail;
                } else {
                    // Cadastra um novo usuário com nível "Pendente"
                    // Usuários pendentes não têm acesso a nenhuma rota até o admin alterar
                    $senhaPadrao = password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT); // Senha aleatória
                    $loginBase = explode('@', $email)[0];
                    $login = $loginBase;

                    // Garante login único
                    $suffix = 1;
                    while (true) {
                        $check = $db->prepare("SELECT id_usuario FROM usuarios WHERE login = ?");
                        $check->execute([$login]);
                        if (!$check->fetch()) break;
                        $login = $loginBase . $suffix;
                        $suffix++;
                    }

                    $stmt = $db->prepare("INSERT INTO usuarios (nome, login, senha, google_id, nivel_acesso) VALUES (?, ?, ?, ?, 'Pendente')");
                    $stmt->execute([$nome, $login, $senhaPadrao, $googleId]);
                    $newId = $db->lastInsertId();

                    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
                    $stmt->execute([$newId]);
                    $user = $stmt->fetch(\PDO::FETCH_ASSOC);
                }
            }

            if ($user['nivel_acesso'] === 'Pendente') {
                header('Location: /?error=' . urlencode('Sua conta foi criada e está Pendente de aprovação pelo Administrador.'));
                exit;
            }

            // Realiza o login
            $_SESSION['usuario_id'] = $user['id_usuario'];
            $_SESSION['empresa_id'] = $user['id_empresa'];
            $_SESSION['usuario_nome'] = $user['nome'];
            $_SESSION['usuario_nivel'] = $user['nivel_acesso'];

            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }

            header('Location: /dashboard');
            exit;

        } catch (\Exception $e) {
            header('Location: /?error=' . urlencode('Falha ao autenticar com o Google.'));
            exit;
        }
    }
    */
}
