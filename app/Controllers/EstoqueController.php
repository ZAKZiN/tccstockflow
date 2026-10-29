<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\AuditHelper;
use App\Models\Produto;
use PDO;

class EstoqueController extends Controller {
    
    public function index() {
        $db = Database::getConnection();
        $produtos = Produto::getAll();
        $categorias = $db->query("SELECT * FROM categorias ORDER BY nome_categoria ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=estoque_export.csv');
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF");
            fputcsv($output, ['ID', 'Produto', 'Categoria', 'Cod. Barras', 'Qtd Atual', 'Estoque Minimo', 'Preco Custo', 'Preco Venda', 'Lote', 'Validade', 'Atualizado Em'], ';', '"', '');
            foreach ($produtos as $p) {
                fputcsv($output, $this->sanitizeCsvRow([
                    $p['id_produto'],
                    $p['nome_produto'],
                    $p['nome_categoria'] ?? '-',
                    $p['codigo_barras'] ?? '',
                    $p['quantidade_estoque'],
                    $p['estoque_minimo'],
                    number_format((float)$p['preco_custo'], 2, ',', '.'),
                    number_format((float)$p['preco_venda'], 2, ',', '.'),
                    $p['lote'] ?? '',
                    !empty($p['data_validade']) ? date('d/m/Y', strtotime($p['data_validade'])) : '',
                    date('d/m/Y H:i', strtotime($p['atualizado_em']))
                ]), ';', '"', '');
            }
            fclose($output);
            exit;
        }
        
        $this->view('estoque/index', [
            'produtos' => $produtos,
            'categorias' => $categorias
        ]);
    }
    
    public function historico($id) {
        $db = Database::getConnection();
        
        $stmtProd = $db->prepare("SELECT * FROM produtos WHERE id_produto = ?");
        $stmtProd->execute([(int)$id]);
        $produto = $stmtProd->fetch(PDO::FETCH_ASSOC);
        
        if (!$produto) {
            $this->redirect('/estoque?error=' . urlencode('Produto não encontrado.'));
        }
        
        $sql = "
            SELECT m.*, u.nome as usuario_nome 
            FROM movimentacoes_estoque m
            LEFT JOIN usuarios u ON m.id_usuario = u.id_usuario
            WHERE m.id_produto = ?
            ORDER BY m.id_movimentacao DESC
        ";
        $stmtMov = $db->prepare($sql);
        $stmtMov->execute([(int)$id]);
        $movimentacoes = $stmtMov->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('estoque/historico', [
            'produto' => $produto,
            'movimentacoes' => $movimentacoes
        ]);
    }
    
    public function ajustar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/estoque');
        }

        $idProduto = (int)($_POST['id_produto'] ?? 0);
        $tipo = $_POST['tipo'] ?? '';
        $quantidade = round(floatval(str_replace(',', '.', $_POST['quantidade'] ?? '0')), 3);
        $observacao = trim($_POST['observacao'] ?? '');
        $idUsuario = (int)$_SESSION['usuario_id'];

        if ($idProduto <= 0 || $quantidade <= 0 || !in_array($tipo, ['Entrada', 'Saída', 'Transferência'], true) || $observacao === '') {
            $this->redirect('/estoque/historico/' . $idProduto . '?error=' . urlencode('Informe um tipo válido, quantidade positiva e motivo do ajuste.'));
        }
        
        $db = Database::getConnection();
        
        try {
            $db->beginTransaction();
            
            $stmtCheck = $db->prepare("SELECT nome_produto, quantidade_estoque FROM produtos WHERE id_produto = ?");
            $stmtCheck->execute([$idProduto]);
            $prod = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$prod) {
                throw new \Exception("Produto não encontrado.");
            }

            $qtdAtual = (float)$prod['quantidade_estoque'];
            if ($tipo === 'Entrada') {
                $novaQtd = round($qtdAtual + $quantidade, 3);
            } else {
                if ($qtdAtual < $quantidade) {
                    throw new \Exception("Estoque insuficiente para a saída. Saldo atual: {$qtdAtual}.");
                }
                $novaQtd = round($qtdAtual - $quantidade, 3);
            }
            
            $stmtEstq = $db->prepare("UPDATE produtos SET quantidade_estoque = ?, atualizado_em = ? WHERE id_produto = ?");
            $stmtEstq->execute([$novaQtd, date('Y-m-d H:i:s'), $idProduto]);
            
            $sqlMov = "INSERT INTO movimentacoes_estoque (id_produto, id_usuario, tipo, quantidade, observacao, data_movimentacao) VALUES (?, ?, ?, ?, ?, ?)";
            $stmtMov = $db->prepare($sqlMov);
            $stmtMov->execute([$idProduto, $idUsuario, $tipo, $quantidade, $observacao, date('Y-m-d H:i:s')]);
            
            $db->commit();
            AuditHelper::log('produtos', $idProduto, 'AJUSTE_KARDEX', "{$tipo} manual de {$quantidade} un. em '{$prod['nome_produto']}' ({$observacao})");
            $this->redirect('/estoque/historico/' . $idProduto . '?success=' . urlencode('Ajuste de estoque registrado com sucesso!'));
        } catch (\Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            $this->redirect('/estoque/historico/' . $idProduto . '?error=' . urlencode($e->getMessage()));
        }
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/estoque');
        }

        $nomeProduto = trim($_POST['nome_produto'] ?? '');
        if ($nomeProduto === '') {
            $this->redirect('/estoque?error=' . urlencode('O nome do produto é obrigatório.'));
        }

        $codigoBarras = trim($_POST['codigo_barras'] ?? '');
        if ($codigoBarras === '') {
            $codigoBarras = 'CB' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        }

        $qtdInicial = max(0, round(floatval(str_replace(',', '.', $_POST['quantidade_estoque'] ?? '0')), 3));
        $precoCusto = max(0, round(floatval(str_replace(',', '.', $_POST['preco_custo'] ?? '0')), 2));
        $precoVenda = max(0, round(floatval(str_replace(',', '.', $_POST['preco_venda'] ?? '0')), 2));
        $estoqueMinimo = max(0, round(floatval(str_replace(',', '.', $_POST['estoque_minimo'] ?? '5')), 3));

        $dados = [
            'nome_produto' => $nomeProduto,
            'codigo_barras' => $codigoBarras,
            'sku' => 'SKU' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)),
            'id_categoria' => !empty($_POST['id_categoria']) ? (int)$_POST['id_categoria'] : null,
            'preco_custo' => $precoCusto,
            'preco_venda' => $precoVenda,
            'quantidade_estoque' => $qtdInicial,
            'estoque_minimo' => $estoqueMinimo,
            'lote' => !empty($_POST['lote']) ? trim($_POST['lote']) : null,
            'data_validade' => !empty($_POST['data_validade']) ? $_POST['data_validade'] : null
        ];

        try {
            $idProduto = Produto::create($dados);
            if ($idProduto && $qtdInicial > 0) {
                $db = Database::getConnection();
                $stmtMov = $db->prepare("INSERT INTO movimentacoes_estoque (id_produto, id_usuario, tipo, quantidade, observacao, data_movimentacao) VALUES (?, ?, 'Entrada', ?, 'Saldo Inicial (Cadastro)', ?)");
                $stmtMov->execute([$idProduto, $_SESSION['usuario_id'], $qtdInicial, date('Y-m-d H:i:s')]);
            }
            AuditHelper::log('produtos', (int)$idProduto, 'CRIAR_PRODUTO', "Produto '{$nomeProduto}' cadastrado (Saldo inicial: {$qtdInicial})");
            $this->redirect('/estoque?success=' . urlencode('Produto cadastrado com sucesso!'));
        } catch (\Exception $e) {
            error_log('Erro ao cadastrar produto: ' . $e->getMessage());
            $msg = 'Não foi possível cadastrar o produto.';
            if (strpos($e->getMessage(), 'produtos.codigo_barras') !== false) {
                $msg = 'Já existe um produto cadastrado com este Código de Barras.';
            }
            $this->redirect('/estoque?error=' . urlencode($msg));
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/estoque');
        }

        $idProduto = (int)($_POST['id_produto'] ?? 0);
        $nomeProduto = trim($_POST['nome_produto'] ?? '');
        $codigoBarras = trim($_POST['codigo_barras'] ?? '');

        if ($idProduto <= 0 || $nomeProduto === '' || $codigoBarras === '') {
            $this->redirect('/estoque?error=' . urlencode('Preencha os dados obrigatórios do produto.'));
        }

        $dados = [
            'nome_produto' => $nomeProduto,
            'codigo_barras' => $codigoBarras,
            'id_categoria' => !empty($_POST['id_categoria']) ? (int)$_POST['id_categoria'] : null,
            'preco_custo' => max(0, round(floatval(str_replace(',', '.', $_POST['preco_custo'] ?? '0')), 2)),
            'preco_venda' => max(0, round(floatval(str_replace(',', '.', $_POST['preco_venda'] ?? '0')), 2)),
            'estoque_minimo' => max(0, round(floatval(str_replace(',', '.', $_POST['estoque_minimo'] ?? '5')), 3)),
            'lote' => !empty($_POST['lote']) ? trim($_POST['lote']) : null,
            'data_validade' => !empty($_POST['data_validade']) ? $_POST['data_validade'] : null
        ];

        try {
            Produto::update($idProduto, $dados);
            AuditHelper::log('produtos', $idProduto, 'EDITAR_PRODUTO', "Dados cadastrais do produto '{$nomeProduto}' atualizados");
            $this->redirect('/estoque?success=' . urlencode('Produto atualizado com sucesso!'));
        } catch (\Exception $e) {
            error_log('Erro ao editar produto: ' . $e->getMessage());
            $msg = strpos($e->getMessage(), 'produtos.codigo_barras') !== false
                ? 'Código de barras já utilizado por outro produto.'
                : 'Não foi possível atualizar o produto.';
            $this->redirect('/estoque?error=' . urlencode($msg));
        }
    }

    public function curvaAbc() {
        $db = Database::getConnection();
        
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
            GROUP BY p.id_produto, p.nome_produto
            ORDER BY receita_total DESC
        ";
        
        $stmt = $db->query($sql);
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $receitaGeral = array_sum(array_column($produtos, 'receita_total'));
        
        $acumulado = 0;
        foreach ($produtos as &$prod) {
            $prod['percentual'] = $receitaGeral > 0 ? ($prod['receita_total'] / $receitaGeral) * 100 : 0;
            $acumulado += $prod['percentual'];
            $prod['percentual_acumulado'] = min(100, $acumulado);
            
            if ($acumulado <= 80 || count($produtos) === 1) {
                $prod['curva'] = 'A';
            } elseif ($acumulado <= 95) {
                $prod['curva'] = 'B';
            } else {
                $prod['curva'] = 'C';
            }
        }
        unset($prod);
        
        $this->view('estoque/curva_abc', [
            'produtos' => $produtos,
            'receitaGeral' => $receitaGeral
        ]);
    }
}
