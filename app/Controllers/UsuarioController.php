<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\AuditHelper;
use PDO;

class UsuarioController extends Controller {
    
    private const ALLOWED_ROLES = [
        'Operador de Caixa',
        'Estoquista',
        'Solicitante',
        'Coordenador',
        'Gerente',
        'Administrador'
    ];

    public function index() {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT u.id_usuario, u.nome, u.login, u.nivel_acesso, u.id_setor, u.criado_em, s.nome_setor 
            FROM usuarios u
            LEFT JOIN setores s ON u.id_setor = s.id_setor
            ORDER BY u.nome ASC
        ");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $setores = $db->query("SELECT * FROM setores ORDER BY nome_setor ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('usuarios/index', [
            'usuarios' => $usuarios,
            'setores' => $setores,
            'error' => $_GET['error'] ?? null,
            'success' => $_GET['success'] ?? null
        ]);
    }
    
    public function store() {
        $nome = trim($_POST['nome_usuario'] ?? '');
        $login = trim($_POST['login'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $nivel = $_POST['nivel_acesso'] ?? '';
        $idSetor = !empty($_POST['id_setor']) ? (int)$_POST['id_setor'] : null;
        
        if ($nome === '' || $login === '' || $senha === '') {
            $this->redirect('/usuarios?error=' . urlencode('Preencha todos os campos obrigatórios.'));
        }

        if (!in_array($nivel, self::ALLOWED_ROLES, true)) {
            $this->redirect('/usuarios?error=' . urlencode('Cargo selecionado inválido.'));
        }

        // Cibersegurança: Política de Senha Forte
        if (strlen($senha) < 8 || !preg_match('/[A-Za-z]/', $senha) || !preg_match('/[0-9]/', $senha)) {
            $this->redirect('/usuarios?error=' . urlencode('A senha deve ter no mínimo 8 caracteres, incluindo letras e números.'));
        }
        
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $db = Database::getConnection();
        
        $stmtCheck = $db->prepare("SELECT id_usuario FROM usuarios WHERE login = ?");
        $stmtCheck->execute([$login]);
        if ($stmtCheck->fetch()) {
            $this->redirect('/usuarios?error=' . urlencode('Nome de usuário (login) já está em uso.'));
        }
        
        try {
            $stmt = $db->prepare("INSERT INTO usuarios (nome, login, senha, nivel_acesso, id_setor, criado_em) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $login, $senhaHash, $nivel, $idSetor, date('Y-m-d H:i:s')]);
            $novoId = (int)$db->lastInsertId();

            AuditHelper::log('usuarios', $novoId, 'CRIAR_USUARIO', "Usuário '{$login}' ({$nivel}) cadastrado");
            $this->redirect('/usuarios?success=' . urlencode('Usuário cadastrado com sucesso!'));
        } catch (\Exception $e) {
            error_log('Erro ao cadastrar usuário: ' . $e->getMessage());
            $this->redirect('/usuarios?error=' . urlencode('Não foi possível cadastrar o usuário.'));
        }
    }
    
    public function destroy() {
        $id = (int)($_POST['id_usuario'] ?? 0);
        
        if ($id <= 0) {
            $this->redirect('/usuarios?error=' . urlencode('Usuário inválido.'));
        }

        if ($id === (int)$_SESSION['usuario_id']) {
            $this->redirect('/usuarios?error=' . urlencode('Você não pode excluir a sua própria conta.'));
        }
        
        $db = Database::getConnection();
        $stmtInfo = $db->prepare("SELECT login, nivel_acesso FROM usuarios WHERE id_usuario = ?");
        $stmtInfo->execute([$id]);
        $alvo = $stmtInfo->fetch(PDO::FETCH_ASSOC);

        $stmt = $db->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$id]);
        
        if ($alvo) {
            AuditHelper::log('usuarios', $id, 'EXCLUIR_USUARIO', "Usuário '{$alvo['login']}' ({$alvo['nivel_acesso']}) removido");
        }

        $this->redirect('/usuarios?success=' . urlencode('Usuário excluído com sucesso!'));
    }
}
