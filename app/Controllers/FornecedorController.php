<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class FornecedorController extends Controller {
    
    public function index() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/dashboard');
        }
        
        $db = Database::getConnection();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = $_POST['nome_fantasia'] ?? '';
            $cnpj = $_POST['cnpj'] ?? '';
            $email = $_POST['email'] ?? '';
            $telefone = $_POST['telefone'] ?? '';
            
            try {
                $idEmpresa = $_SESSION['empresa_id'];
                $stmt = $db->prepare("INSERT INTO fornecedores (id_empresa, nome_fantasia, cnpj, email, telefone) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$idEmpresa, $nome, $cnpj, $email, $telefone]);
                $this->redirect('/fornecedores?success=Fornecedor cadastrado!');
            } catch (\Exception $e) {
                $this->redirect('/fornecedores?error=' . urlencode('Erro ao cadastrar fornecedor: ' . $e->getMessage()));
            }
        }
        
        $idEmpresa = $_SESSION['empresa_id'];
        $stmt = $db->prepare("SELECT * FROM fornecedores WHERE id_empresa = ? ORDER BY nome_fantasia ASC");
        $stmt->execute([$idEmpresa]);
        $fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('fornecedores/index', ['fornecedores' => $fornecedores]);
    }
}
