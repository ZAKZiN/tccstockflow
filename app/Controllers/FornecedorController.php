<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\AuditHelper;
use App\Helpers\CryptHelper;
use PDO;

class FornecedorController extends Controller {
    
    public function index() {
        $db = Database::getConnection();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = trim($_POST['nome_fantasia'] ?? '');
            $cnpj = trim($_POST['cnpj'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telefone = trim($_POST['telefone'] ?? '');

            if ($nome === '') {
                $this->redirect('/fornecedores?error=' . urlencode('Informe a Razão Social ou Nome Fantasia do fornecedor.'));
            }
            
            try {
                $cnpjVal = $cnpj !== '' ? $cnpj : null;
                $emailEnc = $email !== '' ? CryptHelper::encrypt($email) : '';
                $telEnc = $telefone !== '' ? CryptHelper::encrypt($telefone) : '';

                $stmt = $db->prepare("INSERT INTO fornecedores (nome_fantasia, cnpj, email, telefone, criado_em) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nome, $cnpjVal, $emailEnc, $telEnc, date('Y-m-d H:i:s')]);
                $idForn = (int)$db->lastInsertId();

                AuditHelper::log('fornecedores', $idForn, 'CRIAR_FORNECEDOR', "Fornecedor '{$nome}' cadastrado");
                $this->redirect('/fornecedores?success=' . urlencode('Fornecedor cadastrado com sucesso!'));
            } catch (\Exception $e) {
                error_log('Erro ao cadastrar fornecedor: ' . $e->getMessage());
                $msg = strpos($e->getMessage(), 'fornecedores.cnpj') !== false
                    ? 'Já existe um fornecedor cadastrado com este CNPJ.'
                    : 'Não foi possível cadastrar o fornecedor.';
                $this->redirect('/fornecedores?error=' . urlencode($msg));
            }
        }
        
        $stmt = $db->query("SELECT * FROM fornecedores ORDER BY nome_fantasia ASC");
        $fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($fornecedores as &$f) {
            $f['email'] = CryptHelper::decrypt($f['email'] ?? '');
            $f['telefone'] = CryptHelper::decrypt($f['telefone'] ?? '');
        }
        unset($f);
        
        $this->view('fornecedores/index', ['fornecedores' => $fornecedores]);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/fornecedores');
        }

        $idForn = (int)($_POST['id_fornecedor'] ?? 0);
        $nome = trim($_POST['nome_fantasia'] ?? '');
        $cnpj = trim($_POST['cnpj'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');

        if ($idForn <= 0 || $nome === '') {
            $this->redirect('/fornecedores?error=' . urlencode('Informe os dados obrigatórios do fornecedor.'));
        }

        try {
            $db = Database::getConnection();
            $cnpjVal = $cnpj !== '' ? $cnpj : null;
            $emailEnc = $email !== '' ? CryptHelper::encrypt($email) : '';
            $telEnc = $telefone !== '' ? CryptHelper::encrypt($telefone) : '';

            $stmt = $db->prepare("UPDATE fornecedores SET nome_fantasia = ?, cnpj = ?, email = ?, telefone = ? WHERE id_fornecedor = ?");
            $stmt->execute([$nome, $cnpjVal, $emailEnc, $telEnc, $idForn]);

            AuditHelper::log('fornecedores', $idForn, 'EDITAR_FORNECEDOR', "Fornecedor '{$nome}' atualizado");
            $this->redirect('/fornecedores?success=' . urlencode('Fornecedor atualizado com sucesso!'));
        } catch (\Exception $e) {
            error_log('Erro ao editar fornecedor: ' . $e->getMessage());
            $msg = strpos($e->getMessage(), 'fornecedores.cnpj') !== false
                ? 'CNPJ já vinculado a outro fornecedor.'
                : 'Não foi possível atualizar o fornecedor.';
            $this->redirect('/fornecedores?error=' . urlencode($msg));
        }
    }

    public function destroy() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/fornecedores');
        }

        $idForn = (int)($_POST['id_fornecedor'] ?? 0);
        if ($idForn <= 0) {
            $this->redirect('/fornecedores');
        }

        try {
            $db = Database::getConnection();
            $stmtCheck = $db->prepare("SELECT COUNT(*) FROM compras WHERE id_fornecedor = ?");
            $stmtCheck->execute([$idForn]);
            if ((int)$stmtCheck->fetchColumn() > 0) {
                $this->redirect('/fornecedores?error=' . urlencode('Não é possível excluir um fornecedor com ordens de compra vinculadas.'));
            }

            $stmtNome = $db->prepare("SELECT nome_fantasia FROM fornecedores WHERE id_fornecedor = ?");
            $stmtNome->execute([$idForn]);
            $nome = $stmtNome->fetchColumn() ?: "ID {$idForn}";

            $stmtDel = $db->prepare("DELETE FROM fornecedores WHERE id_fornecedor = ?");
            $stmtDel->execute([$idForn]);

            AuditHelper::log('fornecedores', $idForn, 'EXCLUIR_FORNECEDOR', "Fornecedor '{$nome}' removido");
            $this->redirect('/fornecedores?success=' . urlencode('Fornecedor removido com sucesso!'));
        } catch (\Exception $e) {
            error_log('Erro ao excluir fornecedor: ' . $e->getMessage());
            $this->redirect('/fornecedores?error=' . urlencode('Não foi possível excluir o fornecedor.'));
        }
    }
}
