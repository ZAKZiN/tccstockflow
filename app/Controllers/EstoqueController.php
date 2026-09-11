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
                if ($tipo === 'Transferência' || $tipo === 'Saída') { 
                    $sinal = '-';
                    // Check if stock is sufficient
                    $stmtCheck = $db->prepare("SELECT quantidade_estoque FROM produtos WHERE id_produto = ?");
                    $stmtCheck->execute([$idProduto]);
                    $qtdAtual = $stmtCheck->fetchColumn();
                    if ($qtdAtual < $quantidade) {
                        throw new \Exception("Estoque insuficiente para a operação. (Atual: {$qtdAtual})");
                    }
                }
                
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
    public function store() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigoBarras = trim($_POST['codigo_barras'] ?? '');
            if (empty($codigoBarras)) {
                $codigoBarras = 'CB' . strtoupper(substr(uniqid(), -8));
            }

            $dados = [
                'nome_produto' => $_POST['nome_produto'] ?? '',
                'codigo_barras' => $codigoBarras,
                'sku' => 'SKU' . strtoupper(substr(uniqid(), -8)),
                'id_categoria' => !empty($_POST['id_categoria']) ? $_POST['id_categoria'] : null,
                'preco_custo' => !empty($_POST['preco_custo']) ? $_POST['preco_custo'] : 0,
                'preco_venda' => !empty($_POST['preco_venda']) ? $_POST['preco_venda'] : 0,
                'quantidade_estoque' => 0,
                'estoque_minimo' => !empty($_POST['estoque_minimo']) ? $_POST['estoque_minimo'] : 0,
                'lote' => null,
                'data_validade' => !empty($_POST['data_validade']) ? $_POST['data_validade'] : null
            ];
            try {
                Produto::create($dados);
                $this->redirect('/estoque');
            } catch (\Exception $e) {
                $errorMsg = $e->getMessage();
                if (strpos($errorMsg, 'UNIQUE constraint failed: produtos.codigo_barras') !== false) {
                    $errorMsg = "Já existe um produto cadastrado com este Código de Barras.";
                }
                $this->redirect('/estoque?error=' . urlencode('Erro ao cadastrar produto: ' . $errorMsg));
            }
        }
    }

    public function curvaAbc() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/dashboard');
        }
        
        $db = \App\Core\Database::getConnection();
        
        // SQL para somar a receita por produto
        $sql = "
            SELECT 
                p.id_produto,
                p.nome_produto,
                SUM(vi.quantidade) as total_vendido,
                SUM(vi.quantidade * vi.preco_unitario) as receita_total
            FROM produtos p
            JOIN vendas_itens vi ON p.id_produto = vi.id_produto
            JOIN vendas v ON vi.id_venda = v.id_venda
            WHERE v.status = 'Concluída'
            GROUP BY p.id_produto
            ORDER BY receita_total DESC
        ";
        
        $stmt = $db->query($sql);
        $produtos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Calcula o total geral de receita
        $receitaGeral = array_sum(array_column($produtos, 'receita_total'));
        
        // Classifica em A, B e C
        $acumulado = 0;
        foreach ($produtos as &$prod) {
            $prod['percentual'] = $receitaGeral > 0 ? ($prod['receita_total'] / $receitaGeral) * 100 : 0;
            $acumulado += $prod['percentual'];
            $prod['percentual_acumulado'] = $acumulado;
            
            if ($acumulado <= 80) {
                $prod['curva'] = 'A';
            } elseif ($acumulado <= 95) {
                $prod['curva'] = 'B';
            } else {
                $prod['curva'] = 'C';
            }
        }
        
        $this->view('estoque/curva_abc', [
            'produtos' => $produtos,
            'receitaGeral' => $receitaGeral
        ]);
    }
}
