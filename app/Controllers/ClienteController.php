<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\AuditHelper;
use App\Helpers\CryptHelper;

class ClienteController extends Controller {

    public function store() {
        $isJson = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
               || strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;

        if ($isJson && empty($_POST)) {
            $payload = json_decode(file_get_contents('php://input'), true) ?: [];
        } else {
            $payload = $_POST;
        }

        $nome = trim($payload['nome'] ?? '');
        $telefoneLimpo = preg_replace('/\D/', '', $payload['telefone'] ?? '');
        $email = trim($payload['email'] ?? '');

        if ($nome === '') {
            if ($isJson) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'O nome do cliente é obrigatório.']);
                return;
            }
            $this->redirect('/fiado?error=' . urlencode('O nome do cliente é obrigatório.'));
        }

        try {
            $db = Database::getConnection();
            // LGPD: Criptografa dados pessoais (telefone e e-mail) em repouso no banco
            $telEncrypted = $telefoneLimpo !== '' ? CryptHelper::encrypt($telefoneLimpo) : '';
            $emailEncrypted = $email !== '' ? CryptHelper::encrypt($email) : '';

            $stmt = $db->prepare("INSERT INTO clientes (nome, telefone, email, criado_em) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $telEncrypted, $emailEncrypted, date('Y-m-d H:i:s')]);
            $idCliente = (int)$db->lastInsertId();

            AuditHelper::log('clientes', $idCliente, 'CRIAR_CLIENTE', "Cliente '{$nome}' cadastrado com proteção LGPD");

            if ($isJson) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'cliente' => [
                        'id_cliente' => $idCliente,
                        'nome' => $nome,
                        'telefone' => $telefoneLimpo
                    ]
                ]);
                return;
            }

            $this->redirect('/fiado?success=' . urlencode('Cliente cadastrado com sucesso!'));
        } catch (\Exception $e) {
            error_log('Erro ao cadastrar cliente: ' . $e->getMessage());
            if ($isJson) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Erro interno ao cadastrar cliente.']);
                return;
            }
            $this->redirect('/fiado?error=' . urlencode('Erro ao cadastrar cliente.'));
        }
    }
}

