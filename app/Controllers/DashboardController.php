<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class DashboardController extends Controller {
    
    public function index() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/');
        }
        
        $db = Database::getConnection();
        
        // Faturamento Hoje
        $stmtFaturamento = $db->query("
            SELECT SUM(valor_total) 
            FROM vendas 
            WHERE date(data_venda) = date('now', 'localtime')
        ");
        $faturamentoHoje = $stmtFaturamento->fetchColumn() ?: 0;
        
        // Lucro Hoje (Faturamento - Custo)
        $stmtLucro = $db->query("
            SELECT SUM((vi.preco_unitario - IFNULL(p.preco_custo, 0)) * vi.quantidade)
            FROM vendas_itens vi
            JOIN produtos p ON vi.id_produto = p.id_produto
            JOIN vendas v ON vi.id_venda = v.id_venda
            WHERE date(v.data_venda) = date('now', 'localtime')
        ");
        $lucroHoje = $stmtLucro->fetchColumn() ?: 0;
        
        // Vendas Hoje
        $stmtVendas = $db->query("
            SELECT COUNT(*) 
            FROM vendas 
            WHERE date(data_venda) = date('now', 'localtime')
        ");
        $vendasHoje = $stmtVendas->fetchColumn() ?: 0;
        
        // Estoque Crítico
        $stmtEstoque = $db->query("SELECT COUNT(*) FROM produtos WHERE quantidade_estoque <= IFNULL(estoque_minimo, 0)");
        $estoqueCritico = $stmtEstoque->fetchColumn() ?: 0;

        $stmtEstoqueList = $db->query("SELECT nome_produto, quantidade_estoque, estoque_minimo FROM produtos WHERE quantidade_estoque <= IFNULL(estoque_minimo, 0)");
        $estoqueCriticoList = $stmtEstoqueList->fetchAll(PDO::FETCH_ASSOC);
        
        // Chart 1: Faturamento por Mês (Current Year)
        $stmtChart1 = $db->query("
            SELECT cast(strftime('%m', data_venda) as integer) as mes, SUM(valor_total) as total 
            FROM vendas 
            WHERE strftime('%Y', data_venda) = strftime('%Y', 'now', 'localtime') 
            GROUP BY mes ORDER BY mes
        ");
        
        $mesesData = array_fill(1, 12, 0);
        while($row = $stmtChart1->fetch(PDO::FETCH_ASSOC)) {
            $mesesData[(int)$row['mes']] = (float)$row['total'];
        }
        $chartMensal = array_values($mesesData);
        
        // Chart 2: Produtos mais vendidos
        $stmtChart2 = $db->query("
            SELECT p.nome_produto, SUM(vi.quantidade) as total 
            FROM vendas_itens vi
            JOIN produtos p ON vi.id_produto = p.id_produto
            GROUP BY p.nome_produto
            ORDER BY total DESC
            LIMIT 5
        ");
        
        $produtosNomes = [];
        $produtosTotais = [];
        while($row = $stmtChart2->fetch(PDO::FETCH_ASSOC)) {
            $produtosNomes[] = $row['nome_produto'];
            $produtosTotais[] = (int)$row['total'];
        }
        
        // Lista detalhada dos produtos mais vendidos
        $stmtTopProdutos = $db->query("
            SELECT p.id_produto, p.nome_produto, SUM(vi.quantidade) as qtd_vendida, SUM(vi.quantidade * vi.preco_unitario) as valor_gerado
            FROM vendas_itens vi
            JOIN produtos p ON vi.id_produto = p.id_produto
            GROUP BY p.id_produto, p.nome_produto
            ORDER BY qtd_vendida DESC
            LIMIT 10
        ");
        $topProdutosList = $stmtTopProdutos->fetchAll(PDO::FETCH_ASSOC);
        
        // Produtos Vencendo em 7 dias
        $stmtVencendo = $db->query("SELECT nome_produto, data_validade, quantidade_estoque 
                                    FROM produtos 
                                    WHERE data_validade IS NOT NULL 
                                    AND data_validade != '' 
                                    AND data_validade <= date('now', '+7 days') 
                                    AND quantidade_estoque > 0
                                    ORDER BY data_validade ASC");
        $vencendo = $stmtVencendo->fetchAll(PDO::FETCH_ASSOC);
        
        $stats = [
            'faturamento_hoje' => $faturamentoHoje,
            'lucro_hoje' => $lucroHoje,
            'vendas_hoje' => $vendasHoje,
            'estoque_critico' => $estoqueCritico,
            'chart_mensal' => json_encode($chartMensal, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
            'chart_top_labels' => json_encode($produtosNomes, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
            'chart_top_data' => json_encode($produtosTotais, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
            'top_produtos_list' => $topProdutosList,
            'vencendo' => $vencendo,
            'estoque_critico_list' => $estoqueCriticoList
        ];
        
        $this->view('dashboard', ['stats' => $stats]);
    }
}
