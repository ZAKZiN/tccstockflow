<?php

namespace App\Controllers;

use App\Core\Database;
use PDO;

class UsuarioController {
    
    public function index() {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT id_usuario, nome, login, nivel_acesso, criado_em FROM usuarios ORDER BY nome ASC");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $error = $_GET['error'] ?? null;
        $success = $_GET['success'] ?? null;
        
        require __DIR__ . '/../Views/usuarios/index.php';
    }
    
    public function store() {
        $nome = $_POST['nome_usuario'] ?? '';
        $login = $_POST['login'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $nivel = $_POST['nivel_acesso'] ?? 'Estoquista';
        
        if (empty($nome) || empty($login) || empty($senha)) {
            $this->redirect('/usuarios?error=' . urlencode('Preencha todos os campos obrigatórios.'));
            return;
        }

        // Cibersegurança: Política de Senha Forte
        if (strlen($senha) < 8 || !preg_match('/[A-Za-z]/', $senha) || !preg_match('/[0-9]/', $senha)) {
            $this->redirect('/usuarios?error=' . urlencode('A senha deve ter no mínimo 8 caracteres, incluindo letras e números.'));
            return;
        }
        
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        $db = Database::getConnection();
        
        // Verifica se login já existe
        $stmtCheck = $db->prepare("SELECT id_usuario FROM usuarios WHERE login = ?");
        $stmtCheck->execute([$login]);
        if ($stmtCheck->fetch()) {
            header('Location: /usuarios?error=Nome de usuário (login) já está em uso.');
            exit;
        }
        
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO usuarios (nome, login, senha, nivel_acesso) VALUES (?, ?, ?, ?)");
        
        try {
            $stmt->execute([$nome, $login, $senhaHash, $nivel]);
            header('Location: /usuarios?success=Usuário cadastrado com sucesso!');
        } catch (\Exception $e) {
            header('Location: /usuarios?error=Erro ao cadastrar usuário.');
        }
        exit;
    }
    
    public function destroy() {
        $id = $_POST['id_usuario'] ?? 0;
        
        if ($id == $_SESSION['usuario_id']) {
            header('Location: /usuarios?error=Você não pode excluir a sua própria conta.');
            exit;
        }
        
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$id]);
        
        header('Location: /usuarios?success=Usuário excluído com sucesso!');
        exit;
    }
}
