<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Produto;

class EstoqueController extends Controller {
    
    public function index() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/dashboard');
        }
        
        $produtos = Produto::getAll();
        
        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=estoque_export.csv');
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF"); // BOM for Excel
            fputcsv($output, ['ID', 'Produto', 'Qtd Atual', 'Estoque Minimo', 'Atualizado Em'], ';');
            foreach ($produtos as $p) {
                fputcsv($output, [$p['id_produto'], $p['nome_produto'], $p['quantidade_estoque'], $p['estoque_minimo'], date('d/m/Y H:i', strtotime($p['atualizado_em']))], ';');
            }
            fclose($output);
            exit;
        }
        
        $this->view('estoque/index', ['produtos' => $produtos]);
    }
    
    public function historico($id) {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/dashboard');
        }
        
        $db = \App\Core\Database::getConnection();
        
        $stmtProd = $db->prepare("SELECT * FROM produtos WHERE id_produto = ?");
        $stmtProd->execute([$id]);
        $produto = $stmtProd->fetch(\PDO::FETCH_ASSOC);
        
        if (!$produto) {
            $this->redirect('/estoque');
        }
        
        $sql = "
            SELECT m.*, u.nome as usuario_nome 
            FROM movimentacoes_estoque m
            LEFT JOIN usuarios u ON m.id_usuario = u.id_usuario
            WHERE m.id_produto = ?
            ORDER BY m.data_movimentacao DESC
        ";
        $stmtMov = $db->prepare($sql);
        $stmtMov->execute([$id]);
        $movimentacoes = $stmtMov->fetchAll(\PDO::FETCH_ASSOC);
        
        $this->view('estoque/historico', [
            'produto' => $produto,
            'movimentacoes' => $movimentacoes
        ]);
    }
    
    public function ajustar() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idProduto = $_POST['id_produto'];
            $tipo = $_POST['tipo']; // 'Entrada' ou 'Transferência'
            $quantidade = (int)$_POST['quantidade'];
            $observacao = $_POST['observacao'];
            $idUsuario = $_SESSION['usuario_id'];
            
            if ($quantidade <= 0) {
                $this->redirect('/estoque'); // Quantidade inválida
                return;
            }
            
            $db = \App\Core\Database::getConnection();
            
            try {
                $db->beginTransaction();
                
                // Atualizar Estoque
                $sinal = ($tipo === 'Entrada') ? '+' : '-';
                if ($tipo === 'Transferência') { $sinal = '-'; } // Transferência = Saída para outro lugar
                
                $sqlEstoque = "UPDATE produtos SET quantidade_estoque = quantidade_estoque $sinal ? WHERE id_produto = ?";
                $stmtEstq = $db->prepare($sqlEstoque);
                $stmtEstq->execute([$quantidade, $idProduto]);
                
                // Registrar Kardex
                $sqlMov = "INSERT INTO movimentacoes_estoque (id_produto, id_usuario, tipo, quantidade, observacao) VALUES (?, ?, ?, ?, ?)";
                $stmtMov = $db->prepare($sqlMov);
                $stmtMov->execute([$idProduto, $idUsuario, $tipo, $quantidade, $observacao]);
                
                $db->commit();
            } catch (\Exception $e) {
                $db->rollBack();
            }
            
            $this->redirect('/estoque/historico/' . $idProduto);
        }
    }
}
