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
            
            $stmt = $db->prepare("INSERT INTO fornecedores (nome_fantasia, cnpj, email, telefone) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $cnpj, $email, $telefone]);
            
            $this->redirect('/fornecedores');
        }
        
        $stmt = $db->query("SELECT * FROM fornecedores ORDER BY nome_fantasia ASC");
        $fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('fornecedores/index', ['fornecedores' => $fornecedores]);
    }
}
