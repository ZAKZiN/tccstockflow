<?php

namespace App\Controllers;

use App\Core\Database;
use PDO;

class UsuarioController {
    
    public function index() {
        $db = Database::getConnection();
        $idEmpresa = $_SESSION['empresa_id'];
        $stmt = $db->prepare("SELECT id_usuario, nome, login, nivel_acesso, criado_em FROM usuarios WHERE id_empresa = ? ORDER BY nome ASC");
        $stmt->execute([$idEmpresa]);
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
            header('Location: /usuarios?error=' . urlencode('Preencha todos os campos obrigatórios.'));
            exit;
        }

        // Cibersegurança: Política de Senha Forte
        if (strlen($senha) < 8 || !preg_match('/[A-Za-z]/', $senha) || !preg_match('/[0-9]/', $senha)) {
            header('Location: /usuarios?error=' . urlencode('A senha deve ter no mínimo 8 caracteres, incluindo letras e números.'));
            exit;
        }
        
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        $db = Database::getConnection();
        
        // Verifica se login já existe
        $idEmpresa = $_SESSION['empresa_id'];
        $stmtCheck = $db->prepare("SELECT id_usuario FROM usuarios WHERE login = ? AND id_empresa = ?");
        $stmtCheck->execute([$login, $idEmpresa]);
        if ($stmtCheck->fetch()) {
            header('Location: /usuarios?error=Nome de usuário (login) já está em uso.');
            exit;
        }
        
        try {
            $stmt = $db->prepare("INSERT INTO usuarios (id_empresa, nome, login, senha, nivel_acesso) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$idEmpresa, $nome, $login, $senhaHash, $nivel]);
            header('Location: /usuarios?success=Usuário cadastrado com sucesso!');
        } catch (\PDOException $e) {
            header('Location: /usuarios?error=' . urlencode('Erro no banco: ' . $e->getMessage()));
        } catch (\Exception $e) {
            header('Location: /usuarios?error=' . urlencode('Erro ao cadastrar usuário: ' . $e->getMessage()));
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
        $idEmpresa = $_SESSION['empresa_id'];
        $stmt = $db->prepare("DELETE FROM usuarios WHERE id_usuario = ? AND id_empresa = ?");
        $stmt->execute([$id, $idEmpresa]);
        
        header('Location: /usuarios?success=Usuário excluído com sucesso!');
        exit;
    }
}
